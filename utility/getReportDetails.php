<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);
session_start();
ob_clean();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$allowedRoles = ['chief', 'admin', 'cro'];
if (!in_array(strtolower($_SESSION['role']), $allowedRoles)) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

include_once 'db.php';

// Self-migrate: ensure endorsement columns exist
$conn->query("ALTER TABLE reports ADD COLUMN IF NOT EXISTS endorsement_status VARCHAR(20) DEFAULT 'pending'");
$conn->query("ALTER TABLE reports ADD COLUMN IF NOT EXISTS endorsement_notes TEXT DEFAULT NULL");
$conn->query("ALTER TABLE reports ADD COLUMN IF NOT EXISTS endorsed_by INT(11) DEFAULT NULL");
$conn->query("ALTER TABLE reports ADD COLUMN IF NOT EXISTS endorsed_at DATETIME DEFAULT NULL");
// Self-migrate: ensure evidence_path exists on defects
$conn->query("ALTER TABLE defects ADD COLUMN IF NOT EXISTS evidence_path VARCHAR(500) DEFAULT NULL");

$reportId = intval($_GET['report_id'] ?? 0);
if (!$reportId) {
    echo json_encode(['success' => false, 'message' => 'report_id required']);
    exit;
}

try {
    // Full report + inspection + establishment info
    $sql = "SELECT
                r.id                      AS report_id,
                r.inspection_order_number,
                r.compliance_status,
                r.inspector_notes,
                r.finalized_at,
                r.endorsement_status,
                r.endorsement_notes,
                r.endorsed_at,
                i.id                      AS inspection_id,
                i.inspection_date,
                i.time_slot,
                i.inspection_type,
                i.priority_level,
                i.status                  AS inspection_status,
                e.id                      AS establishment_id,
                e.name                    AS establishment_name,
                e.type                    AS establishment_type,
                e.address,
                e.registration_no,
                u_owner.fullname          AS owner_name,
                u_owner.email             AS owner_email,
                u_owner.phone_number      AS owner_phone,
                COALESCE(u0.fullname, u1.fullname) AS inspector1_name,
                u2.fullname               AS inspector2_name,
                u_chief.fullname          AS endorsed_by_name
            FROM reports r
            INNER JOIN inspection i       ON i.id = r.inspection_id
            INNER JOIN establishment e    ON e.id = i.establishment_id
            INNER JOIN user u_owner       ON u_owner.id = e.owner_id
            LEFT JOIN user u0             ON u0.id = i.inspector
            LEFT JOIN user u1             ON u1.id = i.inspector1
            LEFT JOIN user u2             ON u2.id = i.inspector2
            LEFT JOIN user u_chief        ON u_chief.id = r.endorsed_by
            WHERE r.id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $reportId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Report not found']);
        exit;
    }

    // Fetch all defects for this report
    $dSql = "SELECT id, defects_details, grace_period, status, evidence_path, createdAt
             FROM defects
             WHERE report_id = ?
             ORDER BY id ASC";
    $dStmt = $conn->prepare($dSql);
    $dStmt->bind_param("i", $reportId);
    $dStmt->execute();
    $dRes = $dStmt->get_result();

    $defects = [];
    while ($d = $dRes->fetch_assoc()) {
        $defects[] = [
            'id'           => $d['id'],
            'details'      => $d['defects_details'],
            'gracePeriod'  => $d['grace_period'],
            'status'       => $d['status'],
            'evidencePath' => $d['evidence_path'],
            'createdAt'    => $d['createdAt']
        ];
    }
    $dStmt->close();

    echo json_encode([
        'success'              => true,
        'reportId'             => $row['report_id'],
        'inspectionOrderNo'    => $row['inspection_order_number'],
        'complianceStatus'     => $row['compliance_status'],
        'inspectorNotes'       => $row['inspector_notes'],
        'finalizedAt'          => $row['finalized_at'],
        'endorsementStatus'    => $row['endorsement_status'] ?? 'pending',
        'endorsementNotes'     => $row['endorsement_notes'],
        'endorsedAt'           => $row['endorsed_at'],
        'endorsedByName'       => $row['endorsed_by_name'],
        'inspectionId'         => $row['inspection_id'],
        'inspectionDate'       => $row['inspection_date'],
        'timeSlot'             => $row['time_slot'],
        'inspectionType'       => $row['inspection_type'],
        'priorityLevel'        => $row['priority_level'],
        'inspectionStatus'     => $row['inspection_status'],
        'establishmentName'    => $row['establishment_name'],
        'establishmentType'    => $row['establishment_type'],
        'address'              => $row['address'],
        'registrationNo'       => $row['registration_no'] ?? 'Not Registered',
        'ownerName'            => $row['owner_name'],
        'ownerEmail'           => $row['owner_email'],
        'ownerPhone'           => $row['owner_phone'],
        'inspector1Name'       => $row['inspector1_name'],
        'inspector2Name'       => $row['inspector2_name'],
        'defects'              => $defects
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
