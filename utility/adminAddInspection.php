<?php
// Start output buffering
ob_start();

// Disable error display
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Clean output buffer and set headers
ob_clean();
header('Content-Type: application/json');

session_start();
include_once 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    $establisment_id = $data['establishmentId'];
    $inspector       = $data['inspectorId'];
    $inspection_type = $data['type'];
    $inspection_date = $data['dateTime'];
    $priority_level  = $data['priority'];
    $notes           = $data['notes'];
    $time_slot       = $data['time_slot'];
    $status          = $data['status'];

    $stmt = $conn->prepare("
        INSERT INTO inspection
            (inspector, inspection_type, inspection_date, priority_level, notes, time_slot, status, establishment_id)
        VALUES (?,?,?,?,?,?,?,?)
    ");

    if (!$stmt) {
        die(json_encode(['error' => true, 'message' => $conn->error]));
    }

    $stmt->bind_param(
        "issssssi",
        $inspector,
        $inspection_type,
        $inspection_date,
        $priority_level,
        $notes,
        $time_slot,
        $status,
        $establisment_id
    );

    if ($stmt->execute()) {
        $inspection_id = $conn->insert_id;
        
        // Log the inspection creation
        $userId = isset($_SESSION['user']) ? $_SESSION['user'] : null;
        if ($userId) {
            $activityLogger->logCreate(
                $userId,
                'admin_inspection_added',
                'inspection',
                'Admin added inspection ID: ' . $inspection_id . ' for establishment ID: ' . $establisment_id,
                [
                    'inspection_id' => $inspection_id,
                    'establishment_id' => $establisment_id,
                    'inspector' => $inspector,
                    'inspection_type' => $inspection_type,
                    'inspection_date' => $inspection_date,
                    'priority_level' => $priority_level,
                    'status' => $status
                ]
            );
        }
        
        echo json_encode(['success' => true]);

        // Notify the assigned inspector by email and SMS
        include_once __DIR__ . '/mailer.php';
        $notifStmt = $conn->prepare(
            "SELECT u.email, u.fullname, u.phone_number, e.name AS est_name, e.address AS est_address
             FROM user u
             JOIN establishment e ON e.id = ?
             WHERE u.id = ?"
        );
        $notifStmt->bind_param('ii', $establisment_id, $inspector);
        $notifStmt->execute();
        $notifData = $notifStmt->get_result()->fetch_assoc();
        $notifStmt->close();
        if ($notifData) {
            $emailBody = "
            <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                <div style='background:linear-gradient(135deg,#dc3545,#a02834);padding:20px;border-radius:10px 10px 0 0;color:white;'>
                    <h2 style='margin:0;'>New Inspection Assignment</h2>
                </div>
                <div style='background:#f8f9fa;padding:20px;border-radius:0 0 10px 10px;'>
                    <p>Dear " . htmlspecialchars($notifData['fullname']) . ",</p>
                    <p>You have been assigned a new fire inspection:</p>
                    <ul>
                        <li><strong>Establishment:</strong> " . htmlspecialchars($notifData['est_name']) . "</li>
                        <li><strong>Address:</strong> " . htmlspecialchars($notifData['est_address']) . "</li>
                        <li><strong>Date &amp; Time:</strong> " . htmlspecialchars($inspection_date) . "</li>
                        <li><strong>Type:</strong> " . htmlspecialchars($inspection_type) . "</li>
                        <li><strong>Priority:</strong> " . htmlspecialchars($priority_level) . "</li>
                    </ul>
                    <p>Please log in to the BFP Site Profiler to view full details.</p>
                </div>
            </div>";
            sendEmail($notifData['email'], $emailBody, 'New Inspection Assignment — BFP Site Profiler');
            if (!empty($notifData['phone_number'])) {
                sendSMS($notifData['phone_number'],
                    "BFP Site Profiler: You have been assigned an inspection for {$notifData['est_name']} on {$inspection_date}. Please log in for details."
                );
            }
        }
    } else {
        echo json_encode(['error' => true, 'message' => $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => "no data"]);
}

$conn->close();
?>
