<?php
// Wall of Edge™ — delete.php
// Accepts POST JSON { id }, verifies secret, removes entry from entries.json

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-WOE-Secret');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST')    { http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit; }

// ── AUTH ──────────────────────────────────────────────────────────────────────
$WOE_SECRET = 'sharper1edge';
$given = $_SERVER['HTTP_X_WOE_SECRET'] ?? '';
if (!hash_equals($WOE_SECRET, $given)) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

// ── PARSE BODY ────────────────────────────────────────────────────────────────
$body = json_decode(file_get_contents('php://input'), true);
$id   = $body['id'] ?? '';

if (!preg_match('/^woe_[a-zA-Z0-9_]+$/', $id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid id']);
    exit;
}

// ── LOAD / FILTER / WRITE ─────────────────────────────────────────────────────
$dataFile = __DIR__ . '/data/entries.json';

if (!file_exists($dataFile)) {
    echo json_encode(['success' => true, 'removed' => 0]);
    exit;
}

$lock = fopen($dataFile, 'c+');
if (!flock($lock, LOCK_EX)) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not acquire lock']);
    exit;
}

$raw     = stream_get_contents($lock);
$entries = json_decode($raw, true) ?: [];
$before  = count($entries);
$entries = array_values(array_filter($entries, fn($e) => ($e['id'] ?? '') !== $id));
$removed = $before - count($entries);

ftruncate($lock, 0);
rewind($lock);
fwrite($lock, json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
fflush($lock);
flock($lock, LOCK_UN);
fclose($lock);

echo json_encode(['success' => true, 'removed' => $removed]);
