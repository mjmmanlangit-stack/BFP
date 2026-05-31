<?php
// Start output buffering
ob_start();

// Disable error display
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();

// Clean output buffer and set headers
ob_clean();
header('Content-Type: application/json');

include_once 'db.php';

try {
    // Check if user is logged in
    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }

    $inspectorId = $_SESSION['user'];

    // Get all inspections assigned to this inspector
    $query = "SELECT 
                i.id,
                i.inspection_date,
                i.time_slot,
                i.inspection_type,
                i.priority_level,
                i.notes,
                i.status as inspection_status,
                e.id as establishment_id,
                e.name as business_name,
                e.type as business_type,
                e.address,
                e.registration_no,
                e.x_coordinate as latitude,
                e.y_coordinate as longitude,
                COALESCE(u0.fullname, u1.fullname) as inspector1_name,
                u2.fullname as inspector2_name,
                r.id as report_id,
                r.inspection_order_number
              FROM inspection i
              INNER JOIN establishment e ON i.establishment_id = e.id
              LEFT JOIN user u0 ON i.inspector = u0.id
              LEFT JOIN user u1 ON i.inspector1 = u1.id
              LEFT JOIN user u2 ON i.inspector2 = u2.id
              LEFT JOIN reports r ON i.id = r.inspection_id
              WHERE (i.inspector = ? OR i.inspector1 = ? OR i.inspector2 = ?)
              ORDER BY 
                CASE 
                    WHEN r.id IS NULL THEN 0
                    ELSE 1
                END,
                i.inspection_date DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $inspectorId, $inspectorId, $inspectorId);
    $stmt->execute();
    $result = $stmt->get_result();

    $inspections = [];
    while ($row = $result->fetch_assoc()) {
        // Check if report exists (viewed status)
        $viewed = !empty($row['report_id']);
        
        $inspections[] = [
            'id' => $row['id'],
            'viewed' => $viewed,
            'businessName' => $row['business_name'],
            'businessType' => $row['business_type'],
            'inspectionDate' => $row['inspection_date'],
            'inspectionTime' => $row['time_slot'],
            'address' => $row['address'],
            'regNo' => $row['registration_no'] ?? 'Not Registered',
            'latitude' => floatval($row['latitude'] ?? 0),
            'longitude' => floatval($row['longitude'] ?? 0),
            'inspectionType' => $row['inspection_type'],
            'priorityLevel' => $row['priority_level'],
            'notes' => $row['notes'],
            'inspector1' => $row['inspector1_name'],
            'inspector2' => $row['inspector2_name'],
            'reportId' => $row['report_id'],
            'inspectionOrderNo' => $row['inspection_order_number'] ?? ''
        ];
    }

    echo json_encode([
        'success' => true,
        'inspections' => $inspections
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
