<?php
/**
 * submit.php — Wall of Edge™ Entry Submission
 * The Sharper ONE™ · sharper.one/wall/
 *
 * Receives POST from /edge/ capture page.
 * Appends validated entry to entries.json flat store.
 * Returns JSON response.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://sharper.one');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// ── ENTRY STORE PATH ──────────────────────────────────────────────────────────
// Sits outside public_html — not web-accessible directly
// Adjust path if your Enhance doc root differs
$DATA_FILE = __DIR__ . '/data/entries.json';
$DATA_DIR  = __DIR__ . '/data';

// ── READ BODY ─────────────────────────────────────────────────────────────────
$raw = file_get_contents('php://input');
$entry = json_decode($raw, true);

if (!$entry || !is_array($entry)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON body']);
    exit;
}

// ── VALIDATE ─────────────────────────────────────────────────────────────────
$quote = isset($entry['quote']) ? trim($entry['quote']) : '';
$blade = isset($entry['blade']) ? trim($entry['blade']) : '';

if (strlen($quote) < 5) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Quote required (min 5 chars)']);
    exit;
}

if (strlen($quote) > 500) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Quote too long (max 500 chars)']);
    exit;
}

// ── SANITIZE & BUILD CANONICAL ENTRY ─────────────────────────────────────────
$allowed_segments = ['Homeowner', 'Barber', 'Landscaper', 'Restaurant', 'Hobbyist', 'Other'];
$segment = in_array($entry['segment'] ?? '', $allowed_segments) ? $entry['segment'] : 'Homeowner';

$clean = [
    'id'       => 'woe_' . time() . '_' . bin2hex(random_bytes(4)),
    'name'     => substr(trim($entry['name'] ?? 'Anonymous'), 0, 80),
    'segment'  => $segment,
    'blade'    => substr(strip_tags($blade), 0, 120),
    'quote'    => substr(strip_tags($quote), 0, 500),
    'date'     => date('Y-m-d'),
    'notes'    => '',           // Only operator-assigned via Wall internal mode
    'featured' => false,        // Only operator-flagged via Wall internal mode
    'isPublic' => isset($entry['isPublic']) ? (bool)$entry['isPublic'] : true,
    'source'   => 'customer-capture',
    'before'   => null,         // Photos handled via separate upload endpoint (future)
    'after'    => null,
];

// ── PHOTO HANDLING ────────────────────────────────────────────────────────────
// Base64 photos from edge.html are stripped here to protect server storage.
// Future: wire a dedicated photo upload endpoint that stores to /data/photos/
// and returns a URL — then store the URL here instead of base64.
// For now: photos stored in entry are null; wall shows placeholder.

// ── LOAD + APPEND + SAVE ──────────────────────────────────────────────────────
if (!is_dir($DATA_DIR)) {
    mkdir($DATA_DIR, 0750, true);
}

$entries = [];
if (file_exists($DATA_FILE)) {
    $json = file_get_contents($DATA_FILE);
    $entries = json_decode($json, true) ?: [];
}

array_unshift($entries, $clean);

// Cap at 500 entries to prevent unbounded growth
$entries = array_slice($entries, 0, 500);

$written = file_put_contents(
    $DATA_FILE,
    json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
    LOCK_EX
);

if ($written === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Storage write failed']);
    exit;
}

echo json_encode([
    'success' => true,
    'id'      => $clean['id'],
    'message' => 'Entry sealed.'
]);
