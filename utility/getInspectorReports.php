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

    // Get all reports for inspections assigned to this inspector
    $query = "SELECT 
                r.id as report_id,
                r.inspection_order_number,
                r.createdAt as report_date,
                r.compliance_status,
                r.inspector_notes,
                r.finalized_at,
                i.id as inspection_id,
                i.inspection_date,
                i.time_slot,
                i.inspection_type,
                i.status as inspection_status,
                e.id as establishment_id,
                e.name as business_name,
                e.type as business_type,
                e.address,
                e.registration_no,
                e.x_coordinate as latitude,
                e.y_coordinate as longitude,
                u1.fullname as inspector1_name,
                u2.fullname as inspector2_name
              FROM reports r
              INNER JOIN inspection i ON r.inspection_id = i.id
              INNER JOIN establishment e ON i.establishment_id = e.id
              LEFT JOIN user u0 ON i.inspector = u0.id
              LEFT JOIN user u1 ON i.inspector1 = u1.id
              LEFT JOIN user u2 ON i.inspector2 = u2.id
              WHERE (i.inspector = ? OR i.inspector1 = ? OR i.inspector2 = ?)
              ORDER BY r.createdAt DESC, i.inspection_date DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $inspectorId, $inspectorId, $inspectorId);
    $stmt->execute();
    $result = $stmt->get_result();

    $reports = [];
    while ($row = $result->fetch_assoc()) {
        $reportId = $row['report_id'];
        
        // Get all defects for this report
        $defectsQuery = "SELECT 
                            id,
                            defects_details,
                            grace_period,
                            status,
                            createdAt
                         FROM defects 
                         WHERE report_id = ?
                         ORDER BY id ASC";
        
        $defectsStmt = $conn->prepare($defectsQuery);
        $defectsStmt->bind_param("i", $reportId);
        $defectsStmt->execute();
        $defectsResult = $defectsStmt->get_result();

        $defects = [];
        $totalDefects = 0;
        $solvedDefects = 0;
        $earliestGracePeriod = null;
        
        while ($defect = $defectsResult->fetch_assoc()) {
            $totalDefects++;
            if ($defect['status'] === 'solved') {
                $solvedDefects++;
            }
            
            // Track earliest grace period
            if ($earliestGracePeriod === null || $defect['grace_period'] < $earliestGracePeriod) {
                $earliestGracePeriod = $defect['grace_period'];
            }
            
            $defects[] = [
                'id' => $defect['id'],
                'details' => $defect['defects_details'],
                'gracePeriod' => $defect['grace_period'],
                'status' => $defect['status'],
                'createdAt' => $defect['createdAt']
            ];
        }
        
        // Calculate days left from earliest grace period
        $daysLeft = null;
        if ($earliestGracePeriod) {
            $today = new DateTime();
            $graceDate = new DateTime($earliestGracePeriod);
            $interval = $today->diff($graceDate);
            $daysLeft = $interval->days * ($today > $graceDate ? -1 : 1);
        }
        
        $reports[] = [
            'reportId' => $reportId,
            'inspectionId' => $row['inspection_id'],
            'inspectionOrderNo' => $row['inspection_order_number'],
            'reportDate' => $row['report_date'],
            'inspectionDate' => $row['inspection_date'],
            'timeSlot' => $row['time_slot'],
            'inspectionType' => $row['inspection_type'],
            'businessName' => $row['business_name'],
            'businessType' => $row['business_type'],
            'address' => $row['address'],
            'regNo' => $row['registration_no'] ?? 'Not Registered',
            'latitude' => floatval($row['latitude'] ?? 0),
            'longitude' => floatval($row['longitude'] ?? 0),
            'inspector1' => $row['inspector1_name'],
            'inspector2' => $row['inspector2_name'],
            'complianceStatus' => $row['compliance_status'],
            'inspectorNotes' => $row['inspector_notes'],
            'finalizedAt' => $row['finalized_at'],
            'defects' => $defects,
            'totalDefects' => $totalDefects,
            'solvedDefects' => $solvedDefects,
            'earliestGracePeriod' => $earliestGracePeriod,
            'daysLeft' => $daysLeft
        ];
    }

    echo json_encode([
        'success' => true,
        'reports' => $reports
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
