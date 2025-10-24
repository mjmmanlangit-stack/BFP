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
    
    $reportId = $data['reportId'] ?? null;
    $complianceStatus = $data['complianceStatus'] ?? null; // 'compliant', 'partially_compliant', 'non_compliant'
    $inspectorNotes = trim($data['inspectorNotes'] ?? '');

    // Validate required fields
    if (empty($reportId) || empty($complianceStatus)) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields'
        ]);
        exit;
    }

    // Validate compliance status value
    if (!in_array($complianceStatus, ['compliant', 'partially_compliant', 'non_compliant'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid compliance status'
        ]);
        exit;
    }

    // Validate that notes are provided
    if (empty($inspectorNotes)) {
        echo json_encode([
            'success' => false,
            'message' => 'Inspector notes are required'
        ]);
        exit;
    }
    
    // Get current report status before updating for logging
    $getReportQuery = "SELECT compliance_status, inspector_notes FROM reports WHERE id = ?";
    $getStmt = $conn->prepare($getReportQuery);
    $getStmt->bind_param("i", $reportId);
    $getStmt->execute();
    $result = $getStmt->get_result();
    $reportData = $result->fetch_assoc();
    $oldComplianceStatus = $reportData['compliance_status'] ?? null;
    $oldNotes = $reportData['inspector_notes'] ?? null;

    // Update report with compliance status and notes
    $updateQuery = "UPDATE reports 
                    SET compliance_status = ?, 
                        inspector_notes = ?,
                        finalized_at = NOW()
                    WHERE id = ?";
    
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ssi", $complianceStatus, $inspectorNotes, $reportId);
    $updateStmt->execute();

    if ($updateStmt->affected_rows > 0) {
        // Log the report finalization
        $userId = $_SESSION['user'];
        $activityLogger->logUpdate(
            $userId,
            'report_finalized',
            'report',
            'Finalized report ID: ' . $reportId . ' with compliance status: ' . $complianceStatus,
            [
                'report_id' => $reportId,
                'compliance_status' => $oldComplianceStatus,
                'inspector_notes' => $oldNotes
            ],
            [
                'report_id' => $reportId,
                'compliance_status' => $complianceStatus,
                'inspector_notes' => $inspectorNotes
            ]
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Report finalized successfully',
            'complianceStatus' => $complianceStatus
        ]);
    } else {
        // Check if report exists
        $checkQuery = "SELECT id FROM reports WHERE id = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("i", $reportId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Report not found'
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'No changes needed - report already finalized with these values'
            ]);
        }
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
