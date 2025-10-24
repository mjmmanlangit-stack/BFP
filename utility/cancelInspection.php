<?php
// Start output buffering and clean any previous output
ob_start();

// Disable error display (log errors instead)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Clean output buffer and set headers
ob_clean();
header('Content-Type: application/json');

session_start();
include_once 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

if ($data && isset($data['inspection_id'])) {
    $inspection_id = intval($data['inspection_id']);
    
    if ($inspection_id <= 0) {
        echo json_encode(['error' => 'Invalid inspection ID']);
        exit;
    }
    
    // First, get inspection details before deleting for logging
    $getStmt = $conn->prepare("SELECT * FROM inspection WHERE id = ?");
    $getStmt->bind_param("i", $inspection_id);
    $getStmt->execute();
    $result = $getStmt->getResult();
    $inspectionData = $result->fetch_assoc();
    $getStmt->close();
    
    // Delete the inspection
    $stmt = $conn->prepare("DELETE FROM inspection WHERE id = ?");
    
    if (!$stmt) {
        echo json_encode(['error' => 'Database error: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("i", $inspection_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Log the inspection cancellation activity
            $userId = isset($_SESSION['user']) ? $_SESSION['user'] : null;
            if ($userId && $inspectionData) {
                $activityLogger->logDelete(
                    $userId,
                    'inspection_cancelled',
                    'inspection',
                    'Cancelled inspection ID: ' . $inspection_id . ' for establishment ID: ' . $inspectionData['establishment_id'],
                    $inspectionData
                );
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Inspection cancelled successfully'
            ]);
        } else {
            echo json_encode(['error' => 'Inspection not found or already cancelled']);
        }
    } else {
        echo json_encode(['error' => 'Failed to cancel inspection: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'No inspection ID provided']);
}

$conn->close();
exit;
?>
