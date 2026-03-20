<?php
/**
 * entries.php — Wall of Edge™ Entry Feed
 * The Sharper ONE™ · sharper.one/wall/
 *
 * Serves entries.json to wall-of-edge.html.
 * Strips internal-only entries for public mode.
 * Accepts ?mode=internal with secret header for operator access.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://sharper.one');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-WOE-Mode');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$DATA_FILE = __DIR__ . '/data/entries.json';

// ── OPERATOR MODE ─────────────────────────────────────────────────────────────
// Wall internal toggle sends X-WOE-Mode: internal header.
// Simple shared secret set in wall-of-edge.html JS — change this value and
// update it in the wall JS at the same time.
// This is lightweight protection; ops.sharper.one .htaccess is the stronger model
// for full admin lockdown when that's needed.
$WOE_SECRET = 'sharper1edge';  // ← Change this. Mirror in wall-of-edge.html.

$internalMode = false;
$requestedMode = $_SERVER['HTTP_X_WOE_MODE'] ?? '';
$requestedSecret = $_SERVER['HTTP_X_WOE_SECRET'] ?? '';

if ($requestedMode === 'internal' && $requestedSecret === $WOE_SECRET) {
    $internalMode = true;
}

// ── LOAD ENTRIES ──────────────────────────────────────────────────────────────
if (!file_exists($DATA_FILE)) {
    echo json_encode(['entries' => [], 'total' => 0]);
    exit;
}

$json    = file_get_contents($DATA_FILE);
$entries = json_decode($json, true) ?: [];

// ── FILTER FOR PUBLIC MODE ────────────────────────────────────────────────────
if (!$internalMode) {
    $entries = array_values(array_filter($entries, function($e) {
        return !empty($e['isPublic']);
    }));

    // Strip internal-only fields from public response
    $entries = array_map(function($e) {
        unset($e['notes']);
        return $e;
    }, $entries);
}

echo json_encode([
    'entries' => $entries,
    'total'   => count($entries),
    'mode'    => $internalMode ? 'internal' : 'public'
]);
