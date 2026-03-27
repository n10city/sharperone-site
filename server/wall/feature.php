<?php
// Wall of Edge™ — feature.php
// Accepts POST JSON { id, featured }; sets featured flag on an entry in entries.json

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
$body     = json_decode(file_get_contents('php://input'), true);
$id       = $body['id'] ?? '';
$featured = isset($body['featured']) ? (bool)$body['featured'] : false;

if (!preg_match('/^woe_[a-zA-Z0-9_]+$/', $id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid id']);
    exit;
}

// ── LOAD / UPDATE / WRITE ─────────────────────────────────────────────────────
$dataFile = __DIR__ . '/data/entries.json';

if (!file_exists($dataFile)) {
    http_response_code(404);
    echo json_encode(['error' => 'Entry not found']);
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
$found   = false;

foreach ($entries as &$entry) {
    if (($entry['id'] ?? '') === $id) {
        $entry['featured'] = $featured;
        $found = true;
        break;
    }
}
unset($entry);

if (!$found) {
    flock($lock, LOCK_UN);
    fclose($lock);
    http_response_code(404);
    echo json_encode(['error' => 'Entry not found']);
    exit;
}

ftruncate($lock, 0);
rewind($lock);
fwrite($lock, json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
fflush($lock);
flock($lock, LOCK_UN);
fclose($lock);

echo json_encode(['success' => true, 'id' => $id, 'featured' => $featured]);
