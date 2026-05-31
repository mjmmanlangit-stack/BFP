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
    
    // First, get inspection details + related parties before deleting
    include_once __DIR__ . '/mailer.php';
    $getStmt = $conn->prepare(
        "SELECT i.*,
                e.name AS est_name, e.address AS est_address,
                u_owner.email AS owner_email, u_owner.phone_number AS owner_phone, u_owner.fullname AS owner_name,
                u_i1.email AS i1_email, u_i1.phone_number AS i1_phone, u_i1.fullname AS i1_name,
                u_i2.email AS i2_email, u_i2.phone_number AS i2_phone, u_i2.fullname AS i2_name,
                u_insp.email AS insp_email, u_insp.phone_number AS insp_phone, u_insp.fullname AS insp_name
         FROM inspection i
         LEFT JOIN establishment e ON i.establishment_id = e.id
         LEFT JOIN user u_owner ON e.owner_id = u_owner.id
         LEFT JOIN user u_i1 ON i.inspector1 = u_i1.id
         LEFT JOIN user u_i2 ON i.inspector2 = u_i2.id
         LEFT JOIN user u_insp ON i.inspector = u_insp.id
         WHERE i.id = ?"
    );
    $getStmt->bind_param("i", $inspection_id);
    $getStmt->execute();
    $result = $getStmt->get_result();
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

            // Notify assigned inspectors and the establishment owner
            if ($inspectionData) {
                $estName     = $inspectionData['est_name'] ?? 'your establishment';
                $inspDate    = $inspectionData['inspection_date'] ?? '';
                $cancelMsg   = "BFP Site Profiler: Your inspection assignment for {$estName} on {$inspDate} has been cancelled. Please log in for details.";
                $cancelBody  = function(string $name, string $estHtml, string $dateHtml) {
                    return "
                    <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                        <div style='background:linear-gradient(135deg,#dc3545,#a02834);padding:20px;border-radius:10px 10px 0 0;color:white;'>
                            <h2 style='margin:0;'>Inspection Cancelled</h2>
                        </div>
                        <div style='background:#f8f9fa;padding:20px;border-radius:0 0 10px 10px;'>
                            <p>Dear {$name},</p>
                            <p>The fire inspection for <strong>{$estHtml}</strong> scheduled on <strong>{$dateHtml}</strong> has been <strong style='color:#dc3545;'>cancelled</strong>.</p>
                            <p>Please log in to the BFP Site Profiler for further information.</p>
                        </div>
                    </div>";
                };
                $estHtml  = htmlspecialchars($estName);
                $dateHtml = htmlspecialchars($inspDate);
                // Notify inspector (single-inspector admin flow)
                if (!empty($inspectionData['insp_email'])) {
                    sendEmail($inspectionData['insp_email'],
                        $cancelBody(htmlspecialchars($inspectionData['insp_name']), $estHtml, $dateHtml),
                        'Inspection Assignment Cancelled — BFP Site Profiler');
                    if (!empty($inspectionData['insp_phone'])) {
                        sendSMS($inspectionData['insp_phone'], $cancelMsg);
                    }
                }
                // Notify inspector1 (chief flow)
                if (!empty($inspectionData['i1_email'])) {
                    sendEmail($inspectionData['i1_email'],
                        $cancelBody(htmlspecialchars($inspectionData['i1_name']), $estHtml, $dateHtml),
                        'Inspection Assignment Cancelled — BFP Site Profiler');
                    if (!empty($inspectionData['i1_phone'])) {
                        sendSMS($inspectionData['i1_phone'], $cancelMsg);
                    }
                }
                // Notify inspector2 (chief flow, skip if same as inspector1)
                if (!empty($inspectionData['i2_email']) && $inspectionData['i2_email'] !== $inspectionData['i1_email']) {
                    sendEmail($inspectionData['i2_email'],
                        $cancelBody(htmlspecialchars($inspectionData['i2_name']), $estHtml, $dateHtml),
                        'Inspection Assignment Cancelled — BFP Site Profiler');
                    if (!empty($inspectionData['i2_phone'])) {
                        sendSMS($inspectionData['i2_phone'], $cancelMsg);
                    }
                }
                // Notify owner
                if (!empty($inspectionData['owner_email'])) {
                    $ownerCancelMsg = "BFP Site Profiler: An inspection for your establishment {$estName} on {$inspDate} has been cancelled. Please log in for details.";
                    sendEmail($inspectionData['owner_email'],
                        $cancelBody(htmlspecialchars($inspectionData['owner_name']), $estHtml, $dateHtml),
                        'Inspection Cancelled — BFP Site Profiler');
                    if (!empty($inspectionData['owner_phone'])) {
                        sendSMS($inspectionData['owner_phone'], $ownerCancelMsg);
                    }
                }
            }
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
