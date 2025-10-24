<?php
// Clean output buffer and set headers
ob_clean();
header('Content-Type: application/json');

include_once 'db.php';

$stmt = $conn->prepare("SELECT * FROM user");
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
