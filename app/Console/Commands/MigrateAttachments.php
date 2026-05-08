<?php

namespace App\Console\Commands;

use App\Models\Message;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateAttachments extends Command
{
    protected $signature   = 'attachments:migrate';
    protected $description = 'Move old attachments from storage/app/public to public/attachments';

    public function handle(): void
    {
        $messages = Message::whereNotNull('attachment_path')
            ->where('attachment_path', 'like', 'attachments/%')
            ->get();

        $this->info("Found {$messages->count()} messages with old-style paths.");
        $moved = 0;

        foreach ($messages as $msg) {
            $oldPath = $msg->attachment_path; // e.g. attachments/1/file.jpg
            $newPath = substr($oldPath, strlen('attachments/')); // e.g. 1/file.jpg

            $src = storage_path('app/public/' . $oldPath);
            $destDir = public_path('attachments/' . dirname($newPath));
            $dest = public_path('attachments/' . $newPath);

            if (!file_exists($src)) {
                $this->warn("  MISSING src: {$src}");
                continue;
            }

            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }

            if (copy($src, $dest)) {
                $msg->update(['attachment_path' => $newPath]);
                $moved++;
                $this->line("  Moved: {$oldPath} → {$newPath}");
            } else {
                $this->error("  FAILED: {$oldPath}");
            }
        }

        $this->info("Done. Moved {$moved} / {$messages->count()} files.");
    }
}
