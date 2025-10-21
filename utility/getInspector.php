<?php
    
  include_once 'db.php';
  $stmt = $conn->prepare("SELECT * FROM user WHERE role = 'inspector'");
  $stmt->execute();
  $result = $stmt->get_result();
  
  $res = [];
  while ($row = $result->fetch_assoc()) {
    $res[] = $row;
  }
  echo json_encode($res);

?>

