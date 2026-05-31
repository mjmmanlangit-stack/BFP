<?php
ob_start();
ini_set('display_errors', 0);
session_start();
ob_clean();

// Allow CRO and owner to access documents
$allowedRoles = ['cro', 'owner', 'admin', 'chief', 'inspector'];
if (!isset($_SESSION['user']) || !in_array(strtolower($_SESSION['role']), $allowedRoles)) {
    http_response_code(403);
    echo 'Unauthorized';
    exit;
}

include_once 'db.php';

$docId    = isset($_GET['id'])       ? (int)$_GET['id']       : 0;
$download = isset($_GET['download']) && $_GET['download'] === '1';

if (!$docId) {
    http_response_code(400);
    echo 'Missing document ID';
    exit;
}

// Fetch document record
$stmt = $conn->prepare("SELECT d.filename, d.original_name FROM documents d WHERE d.id = ?");
$stmt->bind_param('i', $docId);
$stmt->execute();
$doc = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$doc) {
    http_response_code(404);
    echo 'Document not found';
    exit;
}

$filePath = __DIR__ . '/../uploads/documents/' . $doc['filename'];

if (!file_exists($filePath)) {
    http_response_code(404);
    echo 'File not found on server';
    exit;
}

// Determine MIME type
$finfo    = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $filePath);
finfo_close($finfo);

// Fallback MIME map
$ext = strtolower(pathinfo($doc['filename'], PATHINFO_EXTENSION));
$mimeMap = [
    'pdf'  => 'application/pdf',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
];
if (!$mimeType || $mimeType === 'application/octet-stream') {
    $mimeType = $mimeMap[$ext] ?? 'application/octet-stream';
}

header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($filePath));

if ($download) {
    header('Content-Disposition: attachment; filename="' . addslashes($doc['original_name']) . '"');
} else {
    header('Content-Disposition: inline; filename="' . addslashes($doc['original_name']) . '"');
}

header('Cache-Control: private, max-age=3600');
readfile($filePath);
exit;
