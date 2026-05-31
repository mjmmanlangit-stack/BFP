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
        $time_slot = isset($data['time_slot']) ? trim($data['time_slot']) : null;
        
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

        // Check inspector availability based on date and time slot
        $dateOnly = substr($inspection_date, 0, 10); // YYYY-MM-DD
        
        // If time_slot is provided in new 1-hour format (e.g., "09:00-10:00"), use that
        if ($time_slot && strpos($time_slot, '-') !== false) {
            $timeparts = explode('-', $time_slot);
            if (count($timeparts) === 2) {
                $start_time = trim($timeparts[0]);
                $end_time = trim($timeparts[1]);
                
                // Check for conflicts for both inspectors
                $availStmt = $conn->prepare(
                    "SELECT i.id, u.fullname, u.id as inspector_id FROM inspection i
                     JOIN user u ON (u.id = i.inspector1 OR u.id = i.inspector2)
                     WHERE (u.id = ? OR u.id = ?)
                       AND DATE(i.inspection_date) = ?
                       AND TIME(i.inspection_date) >= TIME(?) 
                       AND TIME(i.inspection_date) < TIME(?)
                       AND i.status NOT IN ('completed','cancelled','approved')
                     LIMIT 1"
                );
                $availStmt->bind_param('iisss', $inspector1_id, $inspector2_id, $dateOnly, $start_time, $end_time);
            } else {
                // Fallback to date-only check
                $availStmt = $conn->prepare(
                    "SELECT i.id, u.fullname FROM inspection i
                     JOIN user u ON (u.id = i.inspector1 OR u.id = i.inspector2)
                     WHERE (u.id = ? OR u.id = ?)
                       AND DATE(i.inspection_date) = ?
                       AND i.status NOT IN ('completed','cancelled','approved')
                     LIMIT 1"
                );
                $availStmt->bind_param('iis', $inspector1_id, $inspector2_id, $dateOnly);
            }
        } else {
            // Fallback to date-only check if no time_slot
            $availStmt = $conn->prepare(
                "SELECT i.id, u.fullname FROM inspection i
                 JOIN user u ON (u.id = i.inspector1 OR u.id = i.inspector2)
                 WHERE (u.id = ? OR u.id = ?)
                   AND DATE(i.inspection_date) = ?
                   AND i.status NOT IN ('completed','cancelled','approved')
                 LIMIT 1"
            );
            $availStmt->bind_param('iis', $inspector1_id, $inspector2_id, $dateOnly);
        }
        
        $availStmt->execute();
        $availResult = $availStmt->get_result();
        if ($availResult->num_rows > 0) {
            $conflict = $availResult->fetch_assoc();
            $availStmt->close();
            echo json_encode(['error' => 'Scheduling conflict: Inspector "' . $conflict['fullname'] . '" is already assigned during this time slot. Please choose a different time or inspector.']);
            exit;
        }
        $availStmt->close();
        
        // Combine date and time
        $datetime = $inspection_date . ' ' . $inspection_time;
        
        // Default values for inspection
        $inspection_type = 'routine';
        $priority_level = 'medium';
        $notes = 'Scheduled by Chief';
        // Use the actual time_slot passed from frontend (in 1-hour format like "09:00-10:00")
        if (!$time_slot) {
            $time_slot = 'full-day'; // Fallback for compatibility
        }
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

            // Notify both assigned inspectors by email
            include_once 'mailer.php';
            $inspIds = array_unique([$inspector1_id, $inspector2_id]);
            foreach ($inspIds as $inspId) {
                $inspStmt = $conn->prepare("SELECT email, fullname, phone_number FROM user WHERE id = ?");
                $inspStmt->bind_param('i', $inspId);
                $inspStmt->execute();
                $inspector = $inspStmt->get_result()->fetch_assoc();
                $inspStmt->close();
                if ($inspector) {
                    $estStmt = $conn->prepare("SELECT name, address, type FROM establishment WHERE id = ?");
                    $estStmt->bind_param('i', $establishment_id);
                    $estStmt->execute();
                    $est = $estStmt->get_result()->fetch_assoc();
                    $estStmt->close();
                    $emailBody = "
                    <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                        <div style='background:linear-gradient(135deg,#dc3545,#a02834);padding:20px;border-radius:10px 10px 0 0;color:white;'>
                            <h2 style='margin:0;'>New Inspection Assignment</h2>
                        </div>
                        <div style='background:#f8f9fa;padding:20px;border-radius:0 0 10px 10px;'>
                            <p>Dear " . htmlspecialchars($inspector['fullname']) . ",</p>
                            <p>You have been assigned a new fire inspection:</p>
                            <ul>
                                <li><strong>Establishment:</strong> " . htmlspecialchars($est['name'] ?? 'N/A') . "</li>
                                <li><strong>Type:</strong> " . htmlspecialchars($est['type'] ?? 'N/A') . "</li>
                                <li><strong>Address:</strong> " . htmlspecialchars($est['address'] ?? 'N/A') . "</li>
                                <li><strong>Date &amp; Time:</strong> " . htmlspecialchars($datetime) . "</li>
                                <li><strong>Type of Inspection:</strong> Routine</li>
                            </ul>
                            <p>Please log in to the BFP Site Profiler to view full details and prepare your inspection report.</p>
                        </div>
                    </div>";
                    sendEmail($inspector['email'], $emailBody, 'New Inspection Assignment — BFP Site Profiler');
                    if (!empty($inspector['phone_number'])) {
                        sendSMS($inspector['phone_number'],
                            "BFP Site Profiler: You have been assigned an inspection for " . ($est['name'] ?? 'an establishment') . " on {$datetime}. Please log in for details."
                        );
                    }
                }
            }
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
