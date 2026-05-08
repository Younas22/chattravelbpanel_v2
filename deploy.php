<?php

$secret = 'mySecretKey123'; // GitHub webhook secret yahan likho
$branch = 'main';

// Verify GitHub signature
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$payload   = file_get_contents('php://input');

if (empty($signature)) {
    http_response_code(403);
    die('Forbidden: No signature');
}

$hash = 'sha256=' . hash_hmac('sha256', $payload, $secret);
if (!hash_equals($hash, $signature)) {
    http_response_code(403);
    die('Forbidden: Invalid signature');
}

// Check branch
$data = json_decode($payload, true);
if (($data['ref'] ?? '') !== "refs/heads/{$branch}") {
    http_response_code(200);
    die('Ignored: Not target branch');
}

// Run git pull
$output = shell_exec('cd ' . escapeshellarg(__DIR__) . ' && git pull origin ' . escapeshellarg($branch) . ' 2>&1');

http_response_code(200);
echo "<pre>$output</pre>";
