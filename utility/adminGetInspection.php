<?php
    include_once 'db.php';
    $stmt = $conn->prepare("SELECT 
     i.id,
     e.name as establishment,
     e.id as establismentId,
     i.inspection_type as type,
     i.inspection_date as dateTime,
     u.fullname as inspector,
     i.priority_level as priority,
     i.notes,
     i.status
     FROM inspection i JOIN establishment e ON i.establishment_id = e.id JOIN user u ON u.id = i.inspector");
    if($stmt->execute()){
        $result = $stmt->get_result();
        $row = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($row);
    }else{
        echo json_encode(['error' => $stmt->error]);
    }
    
?>