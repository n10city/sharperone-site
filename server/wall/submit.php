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
    'source'     => 'customer-capture',
    'before_url' => null,
    'after_url'  => null,
];

// ── PHOTO URL HANDLING ────────────────────────────────────────────────────────
// upload.php handles multipart image upload and returns paths under /wall/data/photos/.
// The client POSTs images there first, then includes the returned URLs here.
// Accept before_url / after_url as plain strings; reject anything that isn't
// a relative path under /wall/data/photos/ to prevent open redirect / injection.
foreach (['before', 'after'] as $slot) {
    $url_key = $slot . '_url';
    if (!empty($entry[$url_key])) {
        $url = trim($entry[$url_key]);
        // Only allow relative paths we own
        if (preg_match('#^/wall/data/photos/woe_[a-zA-Z0-9_]+/(before|after)\.jpg$#', $url)) {
            $clean[$url_key] = $url;
        }
    }
}

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
