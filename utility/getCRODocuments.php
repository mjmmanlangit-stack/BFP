<?php
session_start();
header('Content-Type: application/json');

try {
    // Check authentication
    if (!isset($_SESSION['user']) || !isset($_SESSION['role'])) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }
    
    $role = strtolower($_SESSION['role']);
    if ($role !== 'cro' && $role !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    include_once 'db.php';

    $estId = isset($_GET['establishment_id']) ? (int)$_GET['establishment_id'] : 0;

    if (!$estId) {
        echo json_encode(['success' => false, 'message' => 'establishment_id is required']);
        exit;
    }

    $stmt = $conn->prepare(
        "SELECT d.id, d.document_type, d.filename, d.original_name, d.file_size,
                d.status, d.review_notes, d.createdAt,
                u.fullname AS owner_name
         FROM documents d
         INNER JOIN establishment e ON d.establishment_id = e.id
         INNER JOIN user u ON d.owner_id = u.id
         WHERE d.establishment_id = ?
         ORDER BY d.createdAt DESC"
    );
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param('i', $estId);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Query execution failed: ' . $stmt->error]);
        $stmt->close();
        exit;
    }

    $result = $stmt->get_result();

    $docs = [];
    while ($row = $result->fetch_assoc()) {
        $docs[] = $row;
    }
    $stmt->close();

    echo json_encode(['success' => true, 'documents' => $docs]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
