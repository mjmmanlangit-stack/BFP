<?php
ob_start();
ini_set('display_errors', 0);
session_start();
ob_clean();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || strtolower($_SESSION['role']) !== 'cro') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include_once 'db.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['document_id']) || !isset($data['action'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$docId  = (int) $data['document_id'];
$action = $data['action']; // 'approve' or 'reject'
$notes  = isset($data['notes']) ? trim($data['notes']) : '';
$userId = (int) $_SESSION['user'];

if (!in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

if ($action === 'reject' && $notes === '') {
    echo json_encode(['success' => false, 'message' => 'Rejection notes are required']);
    exit;
}

// Verify document exists
$stmt = $conn->prepare("SELECT d.id, d.establishment_id, d.document_type, e.name AS est_name
                        FROM documents d
                        JOIN establishment e ON d.establishment_id = e.id
                        WHERE d.id = ?");
$stmt->bind_param('i', $docId);
$stmt->execute();
$doc = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$doc) {
    echo json_encode(['success' => false, 'message' => 'Document not found']);
    exit;
}

$newStatus = ($action === 'approve') ? 'approved' : 'rejected';
$now       = date('Y-m-d H:i:s');

$upd = $conn->prepare("UPDATE documents SET status = ?, review_notes = ?, reviewed_by = ?, reviewed_at = ? WHERE id = ?");
$upd->bind_param('ssisi', $newStatus, $notes, $userId, $now, $docId);

if (!$upd->execute()) {
    $upd->close();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}
$upd->close();

// Log activity
if (isset($activityLogger)) {
    $actionLabel = ucfirst($action) . 'd document';
    $activityLogger->log(
        $userId,
        $actionLabel,
        'documents',
        "CRO {$actionLabel}: {$doc['document_type']} for {$doc['est_name']}",
        'success'
    );
}

// Notify the establishment owner by email
include_once 'mailer.php';
$ownerStmt = $conn->prepare(
    "SELECT u.email, u.fullname, u.phone_number FROM user u
     JOIN establishment e ON e.owner_id = u.id
     WHERE e.id = ?"
);
$ownerStmt->bind_param('i', $doc['establishment_id']);
$ownerStmt->execute();
$owner = $ownerStmt->get_result()->fetch_assoc();
$ownerStmt->close();

if ($owner) {
    $statusLabel  = $newStatus === 'approved' ? 'Approved ✔' : 'Rejected ✖';
    $statusColor  = $newStatus === 'approved' ? '#28a745' : '#dc3545';
    $notesSection = $notes ? "<p><strong>Notes from CRO:</strong> " . htmlspecialchars($notes) . "</p>" : '';
    $emailBody = "
    <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
        <div style='background:linear-gradient(135deg,#dc3545,#a02834);padding:20px;border-radius:10px 10px 0 0;color:white;'>
            <h2 style='margin:0;'>Document Review Update</h2>
        </div>
        <div style='background:#f8f9fa;padding:20px;border-radius:0 0 10px 10px;'>
            <p>Dear " . htmlspecialchars($owner['fullname']) . ",</p>
            <p>Your document <strong>" . htmlspecialchars($doc['document_type']) . "</strong> for establishment <strong>" . htmlspecialchars($doc['est_name']) . "</strong> has been reviewed:</p>
            <p style='font-size:1.2em;color:{$statusColor};font-weight:bold;'>{$statusLabel}</p>
            {$notesSection}
            <p>Please log in to the BFP Site Profiler to view details or upload corrected documents.</p>
        </div>
    </div>";
    sendEmail($owner['email'], $emailBody, 'Document Review Update — BFP Site Profiler');
    if (!empty($owner['phone_number'])) {
        $smsVerb = $newStatus === 'approved' ? 'approved' : 'rejected';
        $smsNote = $notes ? " Notes: {$notes}" : '';
        sendSMS($owner['phone_number'],
            "BFP Site Profiler: Your document \"{$doc['document_type']}\" for {$doc['est_name']} has been {$smsVerb}.{$smsNote}"
        );
    }
}

echo json_encode(['success' => true, 'message' => "Document {$action}d successfully"]);
