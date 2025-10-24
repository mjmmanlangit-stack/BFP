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

    $inspectionId = $_GET['inspectionId'] ?? null;

    if (empty($inspectionId)) {
        echo json_encode(['success' => false, 'message' => 'Inspection ID required']);
        exit;
    }

    // Get report for this inspection
    $reportQuery = "SELECT id, inspection_order_number FROM reports WHERE inspection_id = ?";
    $reportStmt = $conn->prepare($reportQuery);
    $reportStmt->bind_param("i", $inspectionId);
    $reportStmt->execute();
    $reportResult = $reportStmt->get_result();

    if ($reportResult->num_rows === 0) {
        echo json_encode([
            'success' => true,
            'hasReport' => false,
            'defects' => []
        ]);
        exit;
    }

    $reportRow = $reportResult->fetch_assoc();
    $reportId = $reportRow['id'];
    $inspectionOrderNo = $reportRow['inspection_order_number'];

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
    while ($defect = $defectsResult->fetch_assoc()) {
        $defects[] = [
            'id' => $defect['id'],
            'details' => $defect['defects_details'],
            'gracePeriod' => $defect['grace_period'],
            'status' => $defect['status'],
            'createdAt' => $defect['createdAt']
        ];
    }

    echo json_encode([
        'success' => true,
        'hasReport' => true,
        'reportId' => $reportId,
        'inspectionOrderNo' => $inspectionOrderNo,
        'defects' => $defects
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
