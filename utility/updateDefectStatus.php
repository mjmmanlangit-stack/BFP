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
    
    $defectId = $data['defectId'] ?? null;
    $status = $data['status'] ?? null; // 'solved' or 'pending'

    // Validate required fields
    if (empty($defectId) || empty($status)) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields'
        ]);
        exit;
    }

    // Validate status value
    if (!in_array($status, ['pending', 'solved'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid status value'
        ]);
        exit;
    }
    
    // Get current defect status before updating for logging
    $getStatusQuery = "SELECT status, defects_details FROM defects WHERE id = ?";
    $getStmt = $conn->prepare($getStatusQuery);
    $getStmt->bind_param("i", $defectId);
    $getStmt->execute();
    $result = $getStmt->get_result();
    $defectData = $result->fetch_assoc();
    $oldStatus = $defectData['status'] ?? null;

    // Update defect status
    $updateQuery = "UPDATE defects SET status = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("si", $status, $defectId);
    $updateStmt->execute();

    if ($updateStmt->affected_rows > 0) {
        // Log the defect status update
        $userId = $_SESSION['user'];
        $activityLogger->logUpdate(
            $userId,
            'defect_status_updated',
            'defect',
            'Updated defect ID: ' . $defectId . ' status from ' . $oldStatus . ' to ' . $status,
            ['status' => $oldStatus, 'defect_id' => $defectId, 'defect_details' => $defectData['defects_details']],
            ['status' => $status, 'defect_id' => $defectId, 'defect_details' => $defectData['defects_details']]
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Defect status updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No changes made or defect not found'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
