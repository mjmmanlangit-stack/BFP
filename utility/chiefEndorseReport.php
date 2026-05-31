<?php
ob_start();
ini_set('display_errors', 0);
session_start();
ob_clean();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || strtolower($_SESSION['role']) !== 'chief') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include_once 'db.php';

// Self-migrate: ensure endorsement columns exist
$conn->query("ALTER TABLE reports ADD COLUMN IF NOT EXISTS endorsement_status VARCHAR(20) DEFAULT 'pending'");
$conn->query("ALTER TABLE reports ADD COLUMN IF NOT EXISTS endorsement_notes TEXT DEFAULT NULL");
$conn->query("ALTER TABLE reports ADD COLUMN IF NOT EXISTS endorsed_by INT(11) DEFAULT NULL");
$conn->query("ALTER TABLE reports ADD COLUMN IF NOT EXISTS endorsed_at DATETIME DEFAULT NULL");

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['report_id'])) {
    echo json_encode(['success' => false, 'message' => 'Report ID required']);
    exit;
}

$reportId          = (int)$data['report_id'];
$action            = $data['action'] ?? 'endorse'; // 'endorse' or 'reject'
$endorsementNotes  = trim($data['notes'] ?? '');
$chiefId           = $_SESSION['user'];

$status = ($action === 'endorse') ? 'endorsed' : 'rejected';
$now    = date('Y-m-d H:i:s');

// Verify report exists
$check = $conn->prepare("SELECT r.id, r.inspection_id, r.compliance_status FROM reports r WHERE r.id = ?");
$check->bind_param("i", $reportId);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Report not found']);
    exit;
}

$report = $result->fetch_assoc();
$check->close();

// Enforce finalized_at prerequisite — inspector must have finalized the report
$finCheck = $conn->prepare("SELECT finalized_at FROM reports WHERE id = ?");
$finCheck->bind_param("i", $reportId);
$finCheck->execute();
$finRow = $finCheck->get_result()->fetch_assoc();
$finCheck->close();
if (empty($finRow['finalized_at'])) {
    echo json_encode(['success' => false, 'message' => 'Cannot endorse: Inspector has not yet finalized this report. Compliance status must be set first.']);
    exit;
}
$stmt = $conn->prepare(
    "UPDATE reports SET endorsement_status = ?, endorsement_notes = ?, endorsed_by = ?, endorsed_at = ?
     WHERE id = ?"
);
$stmt->bind_param("ssisi", $status, $endorsementNotes, $chiefId, $now, $reportId);

if ($stmt->execute()) {
    // If endorsed, update inspection status to 'approved'
    if ($status === 'endorsed') {
        $upd = $conn->prepare("UPDATE inspection SET status = 'approved' WHERE id = ?");
        $upd->bind_param("i", $report['inspection_id']);
        $upd->execute();
        $upd->close();
    }

    // If rejected, auto-create a follow-up inspection for the same establishment
    if ($status === 'rejected') {
        $estStmt = $conn->prepare(
            "SELECT establishment_id, inspector1, inspector2 FROM inspection WHERE id = ?"
        );
        $estStmt->bind_param("i", $report['inspection_id']);
        $estStmt->execute();
        $orig = $estStmt->get_result()->fetch_assoc();
        $estStmt->close();

        if ($orig) {
            $followupDate   = date('Y-m-d H:i:s', strtotime('+7 days'));
            $followupStatus = 'scheduled';
            $followupNotes  = 'Follow-up inspection — Report #' . $reportId . ' was rejected by Chief. Notes: ' . $endorsementNotes;
            $fuStmt = $conn->prepare(
                "INSERT INTO inspection (establishment_id, inspector1, inspector2, inspection_type, inspection_date, priority_level, notes, time_slot, status, payment)
                 VALUES (?, ?, ?, 'follow-up', ?, 'high', ?, 'full-day', ?, 0)"
            );
            $fuStmt->bind_param(
                "iiisss",
                $orig['establishment_id'], $orig['inspector1'], $orig['inspector2'],
                $followupDate, $followupNotes, $followupStatus
            );
            $fuStmt->execute();
            $fuStmt->close();
        }
    }

    // Log activity
    $activityLogger->log(
        $chiefId,
        $status === 'endorsed' ? 'report_endorsed' : 'report_rejected',
        'update',
        'inspection',
        ($status === 'endorsed' ? 'Chief endorsed' : 'Chief rejected') . ' report #' . $reportId,
        ['report_id' => $reportId],
        ['endorsement_status' => $status, 'notes' => $endorsementNotes],
        'success'
    );

    echo json_encode([
        'success' => true,
        'message' => 'Report ' . $status . ' successfully.'
    ]);

    // Notify relevant parties by email and SMS
    include_once __DIR__ . '/mailer.php';
    $partiesStmt = $conn->prepare(
        "SELECT e.name AS est_name,
                u_owner.email AS owner_email, u_owner.phone_number AS owner_phone, u_owner.fullname AS owner_name
         FROM inspection i
         JOIN establishment e ON i.establishment_id = e.id
         JOIN user u_owner ON e.owner_id = u_owner.id
         WHERE i.id = ?"
    );
    $partiesStmt->bind_param('i', $report['inspection_id']);
    $partiesStmt->execute();
    $parties = $partiesStmt->get_result()->fetch_assoc();
    $partiesStmt->close();
    if ($parties) {
        $estName = htmlspecialchars($parties['est_name']);
        if ($status === 'endorsed') {
            // Notify owner
            $ownerBody = "
            <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                <div style='background:linear-gradient(135deg,#dc3545,#a02834);padding:20px;border-radius:10px 10px 0 0;color:white;'>
                    <h2 style='margin:0;'>Inspection Report Endorsed</h2>
                </div>
                <div style='background:#f8f9fa;padding:20px;border-radius:0 0 10px 10px;'>
                    <p>Dear " . htmlspecialchars($parties['owner_name']) . ",</p>
                    <p>The inspection report for <strong>{$estName}</strong> has been <strong style='color:#28a745;'>endorsed</strong> by the Chief Fire Officer and is now being processed for certificate issuance.</p>
                    <p>Please log in to the BFP Site Profiler to check the status of your certificate.</p>
                </div>
            </div>";
            sendEmail($parties['owner_email'], $ownerBody, 'Inspection Report Endorsed — BFP Site Profiler');
            if (!empty($parties['owner_phone'])) {
                sendSMS($parties['owner_phone'],
                    "BFP Site Profiler: Your inspection report for {$parties['est_name']} has been endorsed by the Chief. Certificate issuance is being processed."
                );
            }
            // Notify all active CRO users
            $croCEStmt = $conn->prepare("SELECT email, phone_number, fullname FROM user WHERE role = 'cro' AND status = 'active'");
            $croCEStmt->execute();
            $croCEResult = $croCEStmt->get_result();
            while ($cro = $croCEResult->fetch_assoc()) {
                $croBody = "
                <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                    <div style='background:linear-gradient(135deg,#dc3545,#a02834);padding:20px;border-radius:10px 10px 0 0;color:white;'>
                        <h2 style='margin:0;'>Endorsed Report Ready for Processing</h2>
                    </div>
                    <div style='background:#f8f9fa;padding:20px;border-radius:0 0 10px 10px;'>
                        <p>Dear " . htmlspecialchars($cro['fullname']) . ",</p>
                        <p>The inspection report for <strong>{$estName}</strong> (Report #" . $reportId . ") has been endorsed by the Chief and is ready for certificate issuance. Please process the payment and issue the FSIC.</p>
                        <p>Log in to the BFP Site Profiler to proceed.</p>
                    </div>
                </div>";
                sendEmail($cro['email'], $croBody, 'Endorsed Report Ready for Certificate Processing — BFP Site Profiler');
                if (!empty($cro['phone_number'])) {
                    sendSMS($cro['phone_number'],
                        "BFP Site Profiler: Inspection report for {$parties['est_name']} (Report #{$reportId}) has been endorsed. Please process certificate issuance."
                    );
                }
            }
            $croCEStmt->close();
        } else {
            // Rejected — notify owner
            $ownerRejBody = "
            <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                <div style='background:linear-gradient(135deg,#dc3545,#a02834);padding:20px;border-radius:10px 10px 0 0;color:white;'>
                    <h2 style='margin:0;'>Inspection Report Not Endorsed</h2>
                </div>
                <div style='background:#f8f9fa;padding:20px;border-radius:0 0 10px 10px;'>
                    <p>Dear " . htmlspecialchars($parties['owner_name']) . ",</p>
                    <p>The inspection report for <strong>{$estName}</strong> was <strong style='color:#dc3545;'>not endorsed</strong> by the Chief Fire Officer.</p>"
                    . ($endorsementNotes ? "<p><strong>Reason:</strong> " . htmlspecialchars($endorsementNotes) . "</p>" : '')
                    . "<p>A follow-up inspection has been automatically scheduled. You will receive further instructions soon.</p>
                </div>
            </div>";
            sendEmail($parties['owner_email'], $ownerRejBody, 'Inspection Report Not Endorsed — BFP Site Profiler');
            if (!empty($parties['owner_phone'])) {
                sendSMS($parties['owner_phone'],
                    "BFP Site Profiler: Your inspection report for {$parties['est_name']} was not endorsed. A follow-up inspection has been scheduled. Please log in for details."
                );
            }
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
