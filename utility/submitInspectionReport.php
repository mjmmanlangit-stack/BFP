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

    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    $inspectionId = $data['inspectionId'] ?? null;
    $inspectionOrderNo = trim($data['inspectionOrderNo'] ?? '');
    $defects = $data['defects'] ?? []; // Array of defects with details and grace periods

    // Validate required fields
    if (empty($inspectionId) || empty($inspectionOrderNo) || empty($defects)) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields'
        ]);
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    // Check if report already exists for this inspection
    $checkQuery = "SELECT id FROM reports WHERE inspection_id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("i", $inspectionId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $reportRow = $checkResult->fetch_assoc();
        $reportId = $reportRow['id'];
        
        // Delete existing defects for this report
        $deleteDefectsQuery = "DELETE FROM defects WHERE report_id = ?";
        $deleteStmt = $conn->prepare($deleteDefectsQuery);
        $deleteStmt->bind_param("i", $reportId);
        $deleteStmt->execute();
        
        // Update inspection order number
        $updateReportQuery = "UPDATE reports SET inspection_order_number = ? WHERE id = ?";
        $updateReportStmt = $conn->prepare($updateReportQuery);
        $updateReportStmt->bind_param("si", $inspectionOrderNo, $reportId);
        $updateReportStmt->execute();
    } else {
        // Insert new report
        $reportQuery = "INSERT INTO reports (inspection_id, inspection_order_number) VALUES (?, ?)";
        $reportStmt = $conn->prepare($reportQuery);
        $reportStmt->bind_param("is", $inspectionId, $inspectionOrderNo);
        $reportStmt->execute();
        $reportId = $conn->insert_id;
    }

    // Insert all defects
    $defectQuery = "INSERT INTO defects (defects_details, grace_period, report_id, status) VALUES (?, ?, ?, 'pending')";
    $defectStmt = $conn->prepare($defectQuery);

    foreach ($defects as $defect) {
        $defectDetails = trim($defect['details'] ?? '');
        $gracePeriod = $defect['gracePeriod'] ?? '';
        
        if (empty($defectDetails) || empty($gracePeriod)) {
            continue; // Skip invalid entries
        }

        $defectStmt->bind_param("ssi", $defectDetails, $gracePeriod, $reportId);
        $defectStmt->execute();
    }

    // Update inspection status to completed
    $updateInspectionQuery = "UPDATE inspection SET status = 'completed' WHERE id = ?";
    $updateInspectionStmt = $conn->prepare($updateInspectionQuery);
    $updateInspectionStmt->bind_param("i", $inspectionId);
    $updateInspectionStmt->execute();

    // Commit transaction
    $conn->commit();
    
    // Log the report submission activity
    $userId = $_SESSION['user'];
    $activityLogger->logCreate(
        $userId,
        'inspection_report_submitted',
        'report',
        'Submitted inspection report ID: ' . $reportId . ' for inspection ID: ' . $inspectionId,
        [
            'report_id' => $reportId,
            'inspection_id' => $inspectionId,
            'inspection_order_number' => $inspectionOrderNo,
            'defects_count' => count($defects)
        ]
    );

    echo json_encode([
        'success' => true,
        'message' => 'Inspection report submitted successfully',
        'reportId' => $reportId
    ]);

} catch (Exception $e) {
    // Rollback on error
    if (isset($conn)) {
        $conn->rollback();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
