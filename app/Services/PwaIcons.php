<?php

namespace App\Services;

use App\Models\WidgetSetting;
use Throwable;

/**
 * Installed-app (PWA) icons, built from the favicon uploaded in Widget Settings.
 *
 * Generated PNGs live in public/uploads/branding/pwa/. If there is no favicon,
 * or it's a format GD can't read (.ico / .svg), the default icons in public/pwa/ are used.
 */
class PwaIcons
{
    private const DIR = 'public/uploads/branding/pwa';

    /** file => [size, padding ratio, opaque background?] */
    private const TARGETS = [
        'icon-192.png'         => [192, 0.0,  false],
        'icon-512.png'         => [512, 0.0,  false],
        'maskable-512.png'     => [512, 0.16, true],   // Android crops to circle/squircle: keep logo in the safe zone
        'apple-touch-icon.png' => [180, 0.08, true],   // iOS shows transparency as black
    ];

    /** Public URL for an icon (branded if available, default otherwise), cache-busted. */
    public static function url(string $file, bool $relative = false): string
    {
        self::ensure();

        $branded = base_path(self::DIR . '/' . $file);
        if (is_file($branded)) {
            $path = self::DIR . '/' . $file . '?v=' . filemtime($branded);
        } else {
            $path = 'pwa/' . $file;
        }

        return $relative ? $path : url($path);
    }

    /** (Re)build the icons if the favicon changed since they were last generated. */
    public static function ensure(): void
    {
        $favicon = (string) WidgetSetting::get('favicon', '');
        $marker  = base_path(self::DIR . '/source.txt');
        $current = is_file($marker) ? trim((string) file_get_contents($marker)) : null;

        if ($current === $favicon) {
            return;
        }
        self::generate($favicon);
    }

    /** Build icons from the given favicon path (relative to the project root). */
    public static function generate(string $favicon): bool
    {
        $dir = base_path(self::DIR);
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        // Start clean so a removed/unsupported favicon falls back to the defaults.
        foreach (array_keys(self::TARGETS) as $file) {
            @unlink($dir . '/' . $file);
        }
        @file_put_contents($dir . '/source.txt', $favicon);

        $source = $favicon ? base_path($favicon) : null;
        if (! $source || ! is_file($source) || ! function_exists('imagecreatefromstring')) {
            return false;
        }

        try {
            $src = @imagecreatefromstring((string) file_get_contents($source));   // png / jpg / gif / webp
            if (! $src) {
                return false;                                                     // .ico / .svg etc.
            }

            // drop empty margins (white / transparent) around the logo, then centre-crop to a square
            [$bx, $by, $bw, $bh] = self::contentBox($src);
            $side = max($bw, $bh);
            $sx = max(0, min(imagesx($src) - $side, $bx - intdiv($side - $bw, 2)));
            $sy = max(0, min(imagesy($src) - $side, $by - intdiv($side - $bh, 2)));
            $side = min($side, imagesx($src) - $sx, imagesy($src) - $sy);
            $bg = self::backgroundColor($src, $bx, $by, $bw);

            foreach (self::TARGETS as $file => [$size, $pad, $opaque]) {
                $dst = imagecreatetruecolor($size, $size);
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                $fill = $opaque
                    ? imagecolorallocate($dst, $bg[0], $bg[1], $bg[2])
                    : imagecolorallocatealpha($dst, 0, 0, 0, 127);
                imagefilledrectangle($dst, 0, 0, $size, $size, $fill);
                imagealphablending($dst, true);

                $inner = (int) round($size * (1 - 2 * $pad));
                $off = intdiv($size - $inner, 2);
                imagecopyresampled($dst, $src, $off, $off, $sx, $sy, $inner, $inner, $side, $side);

                imagepng($dst, $dir . '/' . $file, 9);
                imagedestroy($dst);
            }
            imagedestroy($src);

            return true;
        } catch (Throwable $e) {
            report($e);
            return false;
        }
    }

    /** Bounding box [x, y, w, h] of pixels that differ from the corner (margin) colour. */
    private static function contentBox($img): array
    {
        $w = imagesx($img);
        $h = imagesy($img);
        $corner = imagecolorsforindex($img, imagecolorat($img, 0, 0));
        $isMargin = function (int $x, int $y) use ($img, $corner): bool {
            $c = imagecolorsforindex($img, imagecolorat($img, $x, $y));
            if ($c['alpha'] > 100 && $corner['alpha'] > 100) {
                return true;                                   // both transparent
            }
            return abs($c['red'] - $corner['red']) + abs($c['green'] - $corner['green'])
                 + abs($c['blue'] - $corner['blue']) + abs($c['alpha'] - $corner['alpha']) < 40;
        };

        $minX = $w; $minY = $h; $maxX = -1; $maxY = -1;
        $step = max(1, intdiv(max($w, $h), 512));            // keep big images fast
        for ($y = 0; $y < $h; $y += $step) {
            for ($x = 0; $x < $w; $x += $step) {
                if (! $isMargin($x, $y)) {
                    $minX = min($minX, $x); $maxX = max($maxX, $x);
                    $minY = min($minY, $y); $maxY = max($maxY, $y);
                }
            }
        }
        if ($maxX < 0) {
            return [0, 0, $w, $h];                             // blank / single colour image
        }
        return [$minX, $minY, $maxX - $minX + 1, $maxY - $minY + 1];
    }

    /**
     * Background for opaque icons (Android maskable / Apple): the logo's own
     * edge colour (e.g. the blue of a blue tile) if solid, otherwise white.
     */
    private static function backgroundColor($img, int $x, int $y, int $w): array
    {
        $px = imagecolorsforindex($img, imagecolorat($img, $x + intdiv($w, 2), $y + 1));
        if ($px['alpha'] > 20) {
            return [255, 255, 255];
        }
        return [$px['red'], $px['green'], $px['blue']];
    }
}
