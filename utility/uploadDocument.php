<?php
// Start session FIRST before any output
session_start();

// Then start output buffering
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set JSON header
header('Content-Type: application/json');

try {
    include_once 'db.php';

    // Verify user is authenticated and is an owner
    if (!isset($_SESSION['user']) || strtolower($_SESSION['role']) !== 'owner') {
        throw new Exception('Unauthorized');
    }

    $ownerId = $_SESSION['user'];

    // ============================================================
    // GET: Fetch documents for owner
    // ============================================================
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $estId = isset($_GET['establishment_id']) ? (int)$_GET['establishment_id'] : null;

        $sql = "SELECT d.id, d.filename, d.document_type, d.original_name, d.file_size, d.status,
                       d.review_notes, d.createdAt, e.name as establishment_name, d.establishment_id
                FROM documents d
                INNER JOIN establishment e ON d.establishment_id = e.id
                WHERE d.owner_id = ?";

        $params = [$ownerId];
        $types  = 'i';

        if ($estId) {
            $sql    .= ' AND d.establishment_id = ?';
            $params[] = $estId;
            $types  .= 'i';
        }

        $sql .= ' ORDER BY d.createdAt DESC';

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Database error: ' . $conn->error);
        }

        $stmt->bind_param($types, ...$params);
        if (!$stmt->execute()) {
            throw new Exception('Query failed: ' . $stmt->error);
        }

        $result = $stmt->get_result();
        $docs = [];
        while ($row = $result->fetch_assoc()) {
            $docs[] = $row;
        }
        $stmt->close();

        ob_clean();
        echo json_encode(['success' => true, 'documents' => $docs]);
        exit;
    }

    // ============================================================
    // POST: Upload new document
    // ============================================================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $estId    = (int)($_POST['establishment_id'] ?? 0);
        $docType  = trim($_POST['document_type'] ?? '');

        if (!$estId || !$docType) {
            throw new Exception('Establishment and document type are required');
        }

        // Verify this establishment belongs to the owner
        $check = $conn->prepare("SELECT id FROM establishment WHERE id = ? AND owner_id = ?");
        if (!$check) {
            throw new Exception('Database error: ' . $conn->error);
        }
        $check->bind_param("ii", $estId, $ownerId);
        if (!$check->execute()) {
            throw new Exception('Query failed: ' . $check->error);
        }
        $check->store_result();
        if ($check->num_rows === 0) {
            throw new Exception('Establishment not found or does not belong to you');
        }
        $check->close();

        // Validate file upload
        if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            $errorMsg = 'File upload failed';
            if (isset($_FILES['document']['error'])) {
                $uploadErrors = [
                    UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                    UPLOAD_ERR_FORM_SIZE => 'File exceeds max size in form',
                    UPLOAD_ERR_PARTIAL => 'File upload was incomplete',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Temporary folder missing',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
                ];
                $errorMsg = $uploadErrors[$_FILES['document']['error']] ?? $errorMsg;
            }
            throw new Exception($errorMsg);
        }

        $file         = $_FILES['document'];
        $originalName = basename($file['name']);
        $fileSize     = $file['size'];
        $tmpPath      = $file['tmp_name'];

        // Validate file type via MIME
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg',
                         'application/msword',
                         'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if (!$finfo) {
            throw new Exception('Unable to determine file type');
        }
        $mimeType = finfo_file($finfo, $tmpPath);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception('Invalid file type. Allowed: PDF, JPEG, PNG, DOC, DOCX. Detected: ' . $mimeType);
        }

        // Max 10 MB
        if ($fileSize > 10 * 1024 * 1024) {
            throw new Exception('File size must not exceed 10 MB');
        }

        // Create upload directory if it doesn't exist
        $uploadDir = __DIR__ . '/../uploads/documents/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                throw new Exception('Unable to create upload directory');
            }
        }

        // Verify directory is writable
        if (!is_writable($uploadDir)) {
            throw new Exception('Upload directory is not writable');
        }

        // Generate unique filename
        $ext      = pathinfo($originalName, PATHINFO_EXTENSION);
        $filename = 'doc_' . $ownerId . '_' . $estId . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($tmpPath, $destPath)) {
            throw new Exception('Failed to save file to disk');
        }

        // Insert into database
        $stmt = $conn->prepare(
            "INSERT INTO documents (establishment_id, owner_id, document_type, filename, original_name, file_size, status)
             VALUES (?, ?, ?, ?, ?, ?, 'pending')"
        );
        if (!$stmt) {
            @unlink($destPath); // Clean up uploaded file on error
            throw new Exception('Database error: ' . $conn->error);
        }

        $stmt->bind_param("iisssi", $estId, $ownerId, $docType, $filename, $originalName, $fileSize);
        if (!$stmt->execute()) {
            @unlink($destPath); // Clean up uploaded file on error
            throw new Exception('Failed to record document: ' . $stmt->error);
        }

        $docId = $conn->insert_id;
        $stmt->close();

        // Log activity (wrapped in try-catch to not break response)
        try {
            if (isset($activityLogger)) {
                $activityLogger->logCreate(
                    $ownerId,
                    'document_uploaded',
                    'documents',
                    "Uploaded document: $originalName ($docType) for establishment ID $estId",
                    ['document_id' => $docId, 'document_type' => $docType, 'file_size' => $fileSize]
                );
            }
        } catch (Exception $logErr) {
            error_log('Activity logging failed: ' . $logErr->getMessage());
        }

        ob_clean();
        echo json_encode(['success' => true, 'message' => 'Document uploaded successfully', 'id' => $docId]);
        exit;
    }

    // Method not allowed
    throw new Exception('Method not allowed');

} catch (Exception $e) {
    // Catch any errors and return as JSON
    ob_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
