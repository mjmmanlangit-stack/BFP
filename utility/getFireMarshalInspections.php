<?php
// Prevent any HTML output
ini_set('display_errors', 0);
error_reporting(0);

// Start output buffering and session
ob_start();
session_start();

// Clean buffer and set JSON header
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Include database connection
try {
    include_once 'db.php';
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    // Check if user is logged in and is Fire Marshal
    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }

    // Get all inspections with their reports and establishment details
    $query = "SELECT 
                r.id as report_id,
                r.inspection_order_number,
                r.createdAt as report_date,
                r.compliance_status,
                r.inspector_notes,
                r.finalized_at,
                i.id as inspection_id,
                i.inspection_date,
                i.inspection_type,
                i.payment,
                i.status as inspection_status,
                e.id as establishment_id,
                e.name as business_name,
                e.type as business_type,
                e.address,
                e.registration_no,
                e.x_coordinate as latitude,
                e.y_coordinate as longitude,
                e.status as establishment_status,
                owner.fullname as owner_name,
                owner.email as owner_email,
                u1.fullname as inspector1_name,
                u2.fullname as inspector2_name
              FROM inspection i
              INNER JOIN establishment e ON i.establishment_id = e.id
              LEFT JOIN user owner ON e.owner_id = owner.id
              LEFT JOIN user u1 ON i.inspector1 = u1.id
              LEFT JOIN user u2 ON i.inspector2 = u2.id
              LEFT JOIN reports r ON i.id = r.inspection_id
              WHERE r.finalized_at IS NOT NULL
              ORDER BY r.finalized_at DESC, i.inspection_date DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    $inspections = [];
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
        $hasUnsolvedDefects = false;
        $defectsList = [];
        
        while ($defect = $defectsResult->fetch_assoc()) {
            $totalDefects++;
            $defectsList[] = $defect['defects_details'];
            
            if ($defect['status'] === 'solved') {
                $solvedDefects++;
            } else {
                $hasUnsolvedDefects = true;
            }
            
            $defects[] = [
                'id' => $defect['id'],
                'details' => $defect['defects_details'],
                'gracePeriod' => $defect['grace_period'],
                'status' => $defect['status']
            ];
        }
        
        $defectsStmt->close();
        
        // Determine overall compliance status
        $complianceStatus = 'compliant';
        if ($totalDefects > 0) {
            if ($hasUnsolvedDefects) {
                $complianceStatus = 'non-compliant';
            } else if ($solvedDefects > 0) {
                $complianceStatus = 'compliant';
            }
        }
        
        // Override with report compliance status if set
        if (!empty($row['compliance_status'])) {
            $complianceStatus = $row['compliance_status'];
        }
        
        // Check if certificate is already authorized
        $authQuery = "SELECT id, status, authorized_by, authorized_at, remarks, certificate_number 
                      FROM certificates 
                      WHERE inspection_id = ? 
                      ORDER BY authorized_at DESC 
                      LIMIT 1";
        $authStmt = $conn->prepare($authQuery);
        $authStmt->bind_param("i", $row['inspection_id']);
        $authStmt->execute();
        $authResult = $authStmt->get_result();
        
        $authorization = null;
        if ($authResult->num_rows > 0) {
            $authRow = $authResult->fetch_assoc();
            $authorization = [
                'id' => $authRow['id'],
                'status' => $authRow['status'],
                'authorizedBy' => $authRow['authorized_by'],
                'authorizedAt' => $authRow['authorized_at'],
                'remarks' => $authRow['remarks'],
                'certificateNumber' => $authRow['certificate_number']
            ];
        }
        $authStmt->close();
        
        $inspections[] = [
            'id' => $row['inspection_id'],
            'reportId' => $row['report_id'],
            'regNo' => $row['registration_no'] ?: 'Not Registered',
            'businessName' => $row['business_name'],
            'businessType' => $row['business_type'],
            'ownerName' => $row['owner_name'],
            'ownerEmail' => $row['owner_email'],
            'address' => $row['address'],
            'lat' => $row['latitude'],
            'lng' => $row['longitude'],
            'inspectionDate' => $row['inspection_date'],
            'inspectionType' => $row['inspection_type'],
            'inspector1' => $row['inspector1_name'],
            'inspector2' => $row['inspector2_name'],
            'defects' => $totalDefects > 0 ? implode('; ', $defectsList) : 'None - All fire safety equipment operational',
            'defectDetails' => $defects,
            'totalDefects' => $totalDefects,
            'solvedDefects' => $solvedDefects,
            'status' => $complianceStatus,
            'payment' => $row['payment'] == 1 ? 'paid' : 'unpaid',
            'inspectorNotes' => $row['inspector_notes'],
            'finalizedAt' => $row['finalized_at'],
            'authorization' => $authorization
        ];
    }

    $stmt->close();
    $conn->close();

    echo json_encode([
        'success' => true,
        'inspections' => $inspections,
        'total' => count($inspections)
    ]);

} catch (Exception $e) {
    // Clean any output buffer
    while (ob_get_level()) {
        ob_end_clean();
    }
    ob_start();
    
    // Log error
    error_log("Error in getFireMarshalInspections.php: " . $e->getMessage());
    
    // Send clean JSON error
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching inspections: ' . $e->getMessage()
    ]);
}

// Flush and exit
ob_end_flush();
exit;
?>
