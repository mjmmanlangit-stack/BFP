<?php
    include_once 'db.php';
    session_start();
    $user = $_SESSION['user'];
    
    $stmt = $conn->prepare('SELECT * FROM establishment WHERE owner_id = ?');
    $stmt->bind_param('i', $user);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while($row = $result->fetch_assoc()){
        $data[] = [
            "id"             => $row['id'],
            "name"           => $row['name'],
            "type"           => !empty($row['type']) ? $row['type'] : "N/A",
            "bfpRegNo"       => !empty($row['registration_no']) ? $row['registration_no'] : "Not Registered",
            "ownership_type" => $row['ownership_type'] ?? '',
            "tin_number"     => $row['tin_number'] ?? '',
            "contact_number" => $row['contact_number'] ?? '',
            "contact_email"  => $row['contact_email'] ?? '',
            "lat"            => $row['y_coordinate'],
            "lng"            => $row['x_coordinate'],
            "address"        => $row['address'],
            "status"         => !empty($row['status']) ? $row['status'] : "active",
            "lastInspection" => !empty($row['last_inspection']) ? $row['last_inspection'] : "No inspection yet",
        ];
    }
    echo json_encode($data); // always an array — empty [] when no rows
    $stmt->close();
    $conn->close();





?>