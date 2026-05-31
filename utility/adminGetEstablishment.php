<?php
ob_start();
ini_set('display_errors', 0);
session_start();
ob_clean();
header('Content-Type: application/json');

include_once 'db.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $query = 'SELECT 
        e.id,
        e.name,
        e.type,
        e.registration_no,
        e.address AS location,
        e.status,
        u.fullname AS owner,
        u.phone_number AS contact,
        (SELECT MAX(i2.createdAt) FROM inspection i2 WHERE i2.establishment_id = e.id) AS lastInspection,
        (SELECT i3.notes FROM inspection i3 WHERE i3.establishment_id = e.id ORDER BY i3.createdAt DESC LIMIT 1) AS notes
    FROM establishment e 
    JOIN user u ON e.owner_id = u.id
    ORDER BY e.id DESC';

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode($rows);

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>