<?php
ob_start();
session_start();
ob_clean();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include_once 'db.php';

// Select safe fields only (exclude password)
$stmt = $conn->prepare("SELECT id, fullname, email, username, role, address, phone_number, status FROM user");
$stmt->execute();
$result = $stmt->get_result();

$res = [];
while ($row = $result->fetch_assoc()) {
  $res[] = $row;
}

echo json_encode($res);

$stmt->close();
$conn->close();
exit;
?>
