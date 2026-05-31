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

    // Ensure evidence_path column exists (self-migrating)
    $conn->query("ALTER TABLE defects ADD COLUMN IF NOT EXISTS evidence_path VARCHAR(500) DEFAULT NULL");

    // Accept both multipart/form-data (with files) and JSON
    $isFormData = !empty($_POST);
    if ($isFormData) {
        $inspectionId    = $_POST['inspectionId'] ?? null;
        $inspectionOrderNo = trim($_POST['inspectionOrderNo'] ?? '');
        $complianceStatus  = $_POST['complianceStatus'] ?? null;
        $defectCount     = intval($_POST['defectCount'] ?? 0);

        // Build defects array from indexed POST fields
        $defects = [];
        $uploadDir = dirname(__DIR__) . '/uploads/defect-evidence/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        for ($i = 0; $i < $defectCount; $i++) {
            $details     = trim($_POST["defect_details_{$i}"] ?? '');
            $gracePeriod = $_POST["defect_grace_{$i}"] ?? '';
            
            // Still skip completely empty entries to avoid cluttering DB
            if (empty($details) && empty($gracePeriod)) continue;

            $evidencePath = null;
            $fileKey = "defect_evidence_{$i}";
            if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                $ext     = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
                if (in_array($ext, $allowed)) {
                    $filename = 'evidence_' . time() . '_' . $i . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $dest     = $uploadDir . $filename;
                    if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $dest)) {
                        $evidencePath = 'uploads/defect-evidence/' . $filename;
                    }
                }
            }

            $defects[] = ['details' => $details, 'gracePeriod' => $gracePeriod, 'evidencePath' => $evidencePath];
        }
    } else {
        $data = json_decode(file_get_contents('php://input'), true);
        $inspectionId    = $data['inspectionId'] ?? null;
        $inspectionOrderNo = trim($data['inspectionOrderNo'] ?? '');
        $complianceStatus  = $data['complianceStatus'] ?? null;
        $defects = $data['defects'] ?? [];
    }

    // Auto-generate order number if not provided
    if (empty($inspectionOrderNo) && !empty($inspectionId)) {
        $inspectionOrderNo = 'FSIC-' . date('Y') . '-' . str_pad($inspectionId, 6, '0', STR_PAD_LEFT);
    }

    // Validate required fields (defects are now optional, especially for compliant ones)
    if (empty($inspectionId) || empty($inspectionOrderNo)) {
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
        
        // Update inspection order number and compliance status
        $updateReportQuery = "UPDATE reports SET inspection_order_number = ?, compliance_status = ? WHERE id = ?";
        $updateReportStmt = $conn->prepare($updateReportQuery);
        $updateReportStmt->bind_param("ssi", $inspectionOrderNo, $complianceStatus, $reportId);
        $updateReportStmt->execute();
    } else {
        // Insert new report
        $reportQuery = "INSERT INTO reports (inspection_id, inspection_order_number, compliance_status) VALUES (?, ?, ?)";
        $reportStmt = $conn->prepare($reportQuery);
        $reportStmt->bind_param("iss", $inspectionId, $inspectionOrderNo, $complianceStatus);
        $reportStmt->execute();
        $reportId = $conn->insert_id;
    }

    // Insert defects (if any)
    if (!empty($defects)) {
        $defectQuery = "INSERT INTO defects (defects_details, grace_period, report_id, status, evidence_path) VALUES (?, ?, ?, 'pending', ?)";
        $defectStmt = $conn->prepare($defectQuery);

        foreach ($defects as $defect) {
            $defectDetails = trim($defect['details'] ?? '');
            $gracePeriod   = $defect['gracePeriod'] ?? '';
            $evidencePath  = $defect['evidencePath'] ?? null;

            // Only insert if at least one field has content to avoid empty rows
            if ($defectDetails === '' && $gracePeriod === '' && $evidencePath === null) {
                continue;
            }

            $defectStmt->bind_param("ssis", $defectDetails, $gracePeriod, $reportId, $evidencePath);
            $defectStmt->execute();
        }
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

    // Notify owner and all active CRO users
    include_once __DIR__ . '/mailer.php';
    $notifStmt = $conn->prepare(
        "SELECT e.name AS est_name,
                u.email AS owner_email, u.phone_number AS owner_phone, u.fullname AS owner_name
         FROM inspection i
         JOIN establishment e ON i.establishment_id = e.id
         JOIN user u ON e.owner_id = u.id
         WHERE i.id = ?"
    );
    $notifStmt->bind_param('i', $inspectionId);
    $notifStmt->execute();
    $notifData = $notifStmt->get_result()->fetch_assoc();
    $notifStmt->close();
    if ($notifData) {
        $estName     = htmlspecialchars($notifData['est_name']);
        $statusLabel = ucwords(str_replace('_', ' ', $complianceStatus ?? ''));
        // Email owner
        $ownerEmailBody = "
        <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
            <div style='background:linear-gradient(135deg,#dc3545,#a02834);padding:20px;border-radius:10px 10px 0 0;color:white;'>
                <h2 style='margin:0;'>Inspection Report Submitted</h2>
            </div>
            <div style='background:#f8f9fa;padding:20px;border-radius:0 0 10px 10px;'>
                <p>Dear " . htmlspecialchars($notifData['owner_name']) . ",</p>
                <p>The fire inspection report for <strong>{$estName}</strong> has been submitted.</p>
                <ul>
                    <li><strong>Report No:</strong> " . htmlspecialchars($inspectionOrderNo) . "</li>
                    <li><strong>Compliance Status:</strong> {$statusLabel}</li>
                </ul>
                <p>Please log in to the BFP Site Profiler to view the full report and any listed defects.</p>
            </div>
        </div>";
        sendEmail($notifData['owner_email'], $ownerEmailBody, 'Inspection Report Submitted — BFP Site Profiler');
        if (!empty($notifData['owner_phone'])) {
            sendSMS($notifData['owner_phone'],
                "BFP Site Profiler: The inspection report for {$notifData['est_name']} has been submitted. Status: {$statusLabel}. Please log in for details."
            );
        }
        // Email + SMS each active CRO user
        $croNotifStmt = $conn->prepare("SELECT email, phone_number, fullname FROM user WHERE role = 'cro' AND status = 'active'");
        $croNotifStmt->execute();
        $croNotifResult = $croNotifStmt->get_result();
        while ($cro = $croNotifResult->fetch_assoc()) {
            $croEmailBody = "
            <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                <div style='background:linear-gradient(135deg,#dc3545,#a02834);padding:20px;border-radius:10px 10px 0 0;color:white;'>
                    <h2 style='margin:0;'>New Inspection Report for Review</h2>
                </div>
                <div style='background:#f8f9fa;padding:20px;border-radius:0 0 10px 10px;'>
                    <p>Dear " . htmlspecialchars($cro['fullname']) . ",</p>
                    <p>An inspection report for <strong>{$estName}</strong> has been submitted and is pending endorsement by the Chief.</p>
                    <ul>
                        <li><strong>Report No:</strong> " . htmlspecialchars($inspectionOrderNo) . "</li>
                        <li><strong>Compliance Status:</strong> {$statusLabel}</li>
                    </ul>
                    <p>Please log in to the BFP Site Profiler to monitor progress.</p>
                </div>
            </div>";
            sendEmail($cro['email'], $croEmailBody, 'New Inspection Report Submitted — BFP Site Profiler');
            if (!empty($cro['phone_number'])) {
                sendSMS($cro['phone_number'],
                    "BFP Site Profiler: An inspection report for {$notifData['est_name']} has been submitted. Status: {$statusLabel}. Log in to monitor."
                );
            }
        }
        $croNotifStmt->close();
    }

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
