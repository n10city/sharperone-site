<?php
/**
 * upload.php — Wall of Edge™ Photo Upload
 * The Sharper ONE™ · sharper.one/wall/
 *
 * Accepts multipart POST with:
 *   entry_id  — string, required
 *   before    — image file, optional
 *   after     — image file, optional
 *
 * Saves to /wall/data/photos/[entry-id]/before.jpg + after.jpg
 * Returns JSON { success, before_url, after_url }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://sharper.one');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// ── CONFIG ────────────────────────────────────────────────────────────────────
$PHOTOS_BASE_DIR = __DIR__ . '/data/photos';
$PHOTOS_BASE_URL = '/wall/data/photos';
$MAX_FILE_SIZE   = 8 * 1024 * 1024; // 8 MB
$ALLOWED_TYPES   = ['image/jpeg', 'image/png', 'image/webp', 'image/heic', 'image/heif'];

// ── VALIDATE ENTRY ID ─────────────────────────────────────────────────────────
$entry_id = isset($_POST['entry_id']) ? trim($_POST['entry_id']) : '';
if (!preg_match('/^woe_[a-zA-Z0-9_]+$/', $entry_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid entry_id']);
    exit;
}

// ── ENSURE PHOTOS DIR ─────────────────────────────────────────────────────────
$entry_dir = $PHOTOS_BASE_DIR . '/' . $entry_id;
if (!is_dir($entry_dir)) {
    if (!mkdir($entry_dir, 0775, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Could not create photo directory']);
        exit;
    }
}

// ── HELPER: save one upload ───────────────────────────────────────────────────
function save_photo(array $file, string $dest_path, int $max_size, array $allowed_types): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    if ($file['size'] > $max_size) return null;

    // Verify MIME via finfo (don't trust $_FILES['type'])
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed_types, true)) return null;

    // Re-encode through GD → always output JPEG, strip metadata
    $src = null;
    if (in_array($mime, ['image/jpeg', 'image/heic', 'image/heif'], true)) {
        $src = @imagecreatefromjpeg($file['tmp_name']);
    } elseif ($mime === 'image/png') {
        $src = @imagecreatefrompng($file['tmp_name']);
    } elseif ($mime === 'image/webp') {
        $src = @imagecreatefromwebp($file['tmp_name']);
    }

    // Fallback: if GD can't decode (e.g. HEIC without HEIC support), move raw
    if (!$src) {
        if (move_uploaded_file($file['tmp_name'], $dest_path)) {
            return $dest_path;
        }
        return null;
    }

    // Auto-rotate based on EXIF
    if (function_exists('exif_read_data') && in_array($mime, ['image/jpeg'], true)) {
        $exif = @exif_read_data($file['tmp_name']);
        if (!empty($exif['Orientation'])) {
            switch ($exif['Orientation']) {
                case 3: $src = imagerotate($src, 180, 0); break;
                case 6: $src = imagerotate($src, -90,  0); break;
                case 8: $src = imagerotate($src,  90,  0); break;
            }
        }
    }

    // Scale down if wider than 1600px
    $w = imagesx($src);
    $h = imagesy($src);
    if ($w > 1600) {
        $new_h = (int)round($h * 1600 / $w);
        $scaled = imagecreatetruecolor(1600, $new_h);
        imagecopyresampled($scaled, $src, 0, 0, 0, 0, 1600, $new_h, $w, $h);
        imagedestroy($src);
        $src = $scaled;
    }

    $ok = imagejpeg($src, $dest_path, 85);
    imagedestroy($src);

    return $ok ? $dest_path : null;
}

// ── PROCESS UPLOADS ───────────────────────────────────────────────────────────
$result = ['success' => true, 'before_url' => null, 'after_url' => null];

foreach (['before', 'after'] as $slot) {
    if (!isset($_FILES[$slot]) || $_FILES[$slot]['error'] === UPLOAD_ERR_NO_FILE) {
        continue;
    }
    $dest = $entry_dir . '/' . $slot . '.jpg';
    $saved = save_photo($_FILES[$slot], $dest, $MAX_FILE_SIZE, $ALLOWED_TYPES);
    if ($saved) {
        $result[$slot . '_url'] = $PHOTOS_BASE_URL . '/' . $entry_id . '/' . $slot . '.jpg';
    }
}

echo json_encode($result);
