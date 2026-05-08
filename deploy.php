<?php

$secret = 'mySecretKey123'; // GitHub webhook secret yahan likho
$branch = 'main';
$logFile = __DIR__ . '/deploy.log';

function logMsg($msg) {
    global $logFile;
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $msg . PHP_EOL, FILE_APPEND);
}

logMsg('Webhook received');

// Verify GitHub signature
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$payload   = file_get_contents('php://input');

if (empty($signature)) {
    logMsg('FAIL: No signature');
    http_response_code(403);
    die('Forbidden: No signature');
}

$hash = 'sha256=' . hash_hmac('sha256', $payload, $secret);
if (!hash_equals($hash, $signature)) {
    logMsg('FAIL: Invalid signature. Got: ' . $signature . ' Expected: ' . $hash);
    http_response_code(403);
    die('Forbidden: Invalid signature');
}

// Check branch
$data = json_decode($payload, true);
$ref  = $data['ref'] ?? 'unknown';
if ($ref !== "refs/heads/{$branch}") {
    logMsg('Ignored: ref=' . $ref);
    http_response_code(200);
    die('Ignored: Not target branch');
}

logMsg('Running git pull in: ' . __DIR__);

// Check if shell_exec is available
if (!function_exists('shell_exec')) {
    logMsg('FAIL: shell_exec is disabled');
    http_response_code(500);
    die('shell_exec disabled');
}

$cmd = 'cd ' . escapeshellarg(__DIR__)
    . ' && git fetch origin ' . escapeshellarg($branch)
    . ' && git reset --hard origin/' . escapeshellarg($branch)
    . ' 2>&1';
$output = shell_exec($cmd);
logMsg('Deploy output: ' . $output);

http_response_code(200);
echo "<pre>$output</pre>";
