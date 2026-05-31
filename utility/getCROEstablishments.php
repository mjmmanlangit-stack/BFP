<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || strtolower($_SESSION['role']) !== 'cro') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include_once 'db.php';

$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$search       = isset($_GET['search']) ? trim($_GET['search']) : '';

// Base query: establishments with owner, latest inspection payment, document counts
$whereClause = '';
$params      = [];
$types       = '';

if ($statusFilter !== 'all') {
    if ($statusFilter === 'pending') {
        $whereClause .= ' AND (i.payment IS NULL OR i.payment = 0)';
    } elseif ($statusFilter === 'paid') {
        $whereClause .= ' AND i.payment = 1';
    } elseif ($statusFilter === 'no_inspection') {
        $whereClause .= ' AND i.id IS NULL';
    }
}

if ($search !== '') {
    $whereClause .= ' AND (e.name LIKE ? OR e.registration_no LIKE ? OR u.fullname LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'sss';
}

$sql = "SELECT
    e.id,
    e.name,
    e.type,
    e.registration_no,
    e.address,
    e.x_coordinate,
    e.y_coordinate,
    u.id AS owner_id,
    u.fullname AS owner_name,
    u.email AS owner_email,
    u.phone_number AS owner_phone,
    i.id AS inspection_id,
    i.payment,
    i.status AS inspection_status,
    i.createdAt AS inspection_date,
    (SELECT COUNT(*) FROM documents d WHERE d.establishment_id = e.id AND d.status = 'pending') AS pending_docs,
    (SELECT COUNT(*) FROM documents d WHERE d.establishment_id = e.id) AS total_docs
FROM establishment e
JOIN user u ON e.owner_id = u.id
LEFT JOIN inspection i ON i.establishment_id = e.id
    AND i.id = (SELECT MAX(i2.id) FROM inspection i2 WHERE i2.establishment_id = e.id)
WHERE 1=1
" . $whereClause . "
ORDER BY e.id DESC
LIMIT 200";

try {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    echo json_encode(['success' => true, 'establishments' => $rows, 'count' => count($rows)]);
} catch (Exception $e) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
