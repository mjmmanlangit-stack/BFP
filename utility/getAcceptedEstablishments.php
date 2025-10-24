<?php
// Start output buffering and clean any previous output
ob_start();

// Disable error display (log errors instead)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Clean output buffer and set headers
ob_clean();
header('Content-Type: application/json');

include_once 'db.php';

// Get establishments with 'active' status (accepted establishments)
// Also check if they have pending or approved inspections
$stmt = $conn->prepare('SELECT e.id, e.name, e.type, e.registration_no, e.address, e.x_coordinate as lng, e.y_coordinate as lat, e.status, 
u.fullname as owner_name, u.phone_number as owner_contact,
i.id as inspection_id, i.inspection_date, i.status as inspection_status, i.inspector1, i.inspector2,
u1.fullname as inspector1_name, u2.fullname as inspector2_name
FROM establishment e 
JOIN user u ON e.owner_id = u.id 
LEFT JOIN inspection i ON e.id = i.establishment_id AND (i.status = "status-pen" OR i.status = "approved")
LEFT JOIN user u1 ON i.inspector1 = u1.id
LEFT JOIN user u2 ON i.inspector2 = u2.id
WHERE e.status = "active"
ORDER BY e.createdAt DESC');

$stmt->execute();
$result = $stmt->get_result();

$establishments = [];
while ($row = $result->fetch_assoc()) {
    $hasInspection = !empty($row['inspection_id']);
    
    $establishments[] = [
        "id" => $row['id'],
        "name" => $row['name'],
        "type" => !empty($row['type']) ? $row['type'] : "N/A",
        "registration_no" => !empty($row['registration_no']) ? $row['registration_no'] : "Not Registered",
        "address" => $row['address'],
        "lat" => !empty($row['lat']) ? floatval($row['lat']) : 14.5995,
        "lng" => !empty($row['lng']) ? floatval($row['lng']) : 120.9842,
        "status" => $row['status'],
        "owner_name" => $row['owner_name'],
        "owner_contact" => $row['owner_contact'],
        "has_inspection" => $hasInspection,
        "inspection_id" => $row['inspection_id'],
        "inspection_date" => $row['inspection_date'],
        "inspection_status" => $row['inspection_status'],
        "inspector1_name" => $row['inspector1_name'],
        "inspector2_name" => $row['inspector2_name']
    ];
}

echo json_encode($establishments);

$stmt->close();
$conn->close();
exit;
?>
