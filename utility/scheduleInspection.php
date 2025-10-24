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

try {
    $data = json_decode(file_get_contents("php://input"), true);

    if ($data) {
        $establishment_id = intval($data['establishment_id']);
        $inspector1_id = intval($data['inspector1_id']);
        $inspector2_id = intval($data['inspector2_id']);
        $inspection_date = trim($data['inspection_date']);
        $inspection_time = trim($data['inspection_time']);
        
        // Validate required fields
        if (!$establishment_id || !$inspector1_id || !$inspector2_id || !$inspection_date || !$inspection_time) {
            echo json_encode(['error' => 'All fields are required']);
            exit;
        }
        
        // Validate that inspectors are different
        if ($inspector1_id === $inspector2_id) {
            echo json_encode(['error' => 'Please select two different inspectors']);
            exit;
        }
        
        // Combine date and time
        $datetime = $inspection_date . ' ' . $inspection_time;
        
        // Default values for inspection
        $inspection_type = 'routine';
        $priority_level = 'medium';
        $notes = 'Scheduled by Chief';
        $time_slot = 'full-day';
        $status = 'status-pen'; // pending status
        
        // Insert inspection
        $stmt = $conn->prepare("
            INSERT INTO inspection
                (inspector1, inspector2, inspection_type, inspection_date, priority_level, notes, time_slot, status, establishment_id, payment)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
        ");

        if (!$stmt) {
            echo json_encode(['error' => 'Database error: ' . $conn->error]);
            exit;
        }

        $stmt->bind_param(
            "iissssssi",
            $inspector1_id,
            $inspector2_id,
            $inspection_type,
            $datetime,
            $priority_level,
            $notes,
            $time_slot,
            $status,
            $establishment_id
        );

        if ($stmt->execute()) {
            $inspection_id = $conn->insert_id;
            
            // Log the inspection scheduling activity
            $userId = isset($_SESSION['user']) ? $_SESSION['user'] : null;
            if ($userId) {
                $activityLogger->logCreate(
                    $userId,
                    'inspection_scheduled',
                    'inspection',
                    'Scheduled inspection ID: ' . $inspection_id . ' for establishment ID: ' . $establishment_id,
                    [
                        'inspection_id' => $inspection_id,
                        'establishment_id' => $establishment_id,
                        'inspector1_id' => $inspector1_id,
                        'inspector2_id' => $inspector2_id,
                        'inspection_date' => $datetime,
                        'priority_level' => $priority_level,
                        'status' => $status
                    ]
                );
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Inspection scheduled successfully',
                'inspection_id' => $inspection_id
            ]);
        } else {
            echo json_encode(['error' => 'Failed to schedule inspection: ' . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['error' => 'No data received']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
exit;
?>
