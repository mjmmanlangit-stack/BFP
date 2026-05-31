<?php
ob_start();
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

include_once 'db.php';

$role   = strtolower($_SESSION['role']);
$userId = (int) $_SESSION['user'];

// Build WHERE clause based on role
$extraWhere = '';
$params     = [];
$types      = '';

if ($role === 'inspector') {
    // Inspector sees only their assigned establishments
    $extraWhere = ' AND EXISTS (
        SELECT 1 FROM inspection ins
        WHERE ins.establishment_id = e.id
          AND (ins.inspector1 = ? OR ins.inspector2 = ? OR ins.inspector_id = ?)
    )';
    $params = [$userId, $userId, $userId];
    $types  = 'iii';
}
// CRO, Chief, Admin see all

$sql = "SELECT
    e.id,
    e.name,
    e.type,
    e.registration_no,
    e.address,
    e.x_coordinate AS lng,
    e.y_coordinate AS lat,
    e.status AS establishment_status,
    u_owner.fullname  AS owner_name,
    u_owner.phone_number AS owner_phone,
    i.id              AS inspection_id,
    i.inspection_date,
    i.status          AS inspection_status,
    r.compliance_status,
    r.endorsement_status,
    r.finalized_at,
    c.certificate_number,
    c.status          AS cert_status,
    c.expiry_date
FROM establishment e
JOIN user u_owner ON e.owner_id = u_owner.id
LEFT JOIN inspection i ON i.establishment_id = e.id
    AND i.id = (
        SELECT id FROM inspection WHERE establishment_id = e.id
        ORDER BY inspection_date DESC LIMIT 1
    )
LEFT JOIN reports r ON r.inspection_id = i.id
LEFT JOIN certificates c ON c.establishment_id = e.id
    AND c.id = (SELECT id FROM certificates WHERE establishment_id = e.id ORDER BY authorized_at DESC LIMIT 1)
WHERE e.status = 'active'
$extraWhere
ORDER BY e.name ASC";

try {
    if ($types && count($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }

    $markers = [];
    while ($row = $result->fetch_assoc()) {
        // Determine marker color / compliance color
        $compColor = '#6c757d'; // grey = no inspection
        $compStatus = $row['compliance_status'] ?? null;
        if ($compStatus === 'compliant')           $compColor = '#28a745'; // green
        elseif ($compStatus === 'partially_compliant') $compColor = '#ffc107'; // yellow
        elseif ($compStatus === 'non_compliant')   $compColor = '#dc3545'; // red

        $markers[] = [
            'id'                 => (int) $row['id'],
            'name'               => $row['name'],
            'type'               => $row['type'] ?: 'N/A',
            'registrationNo'     => $row['registration_no'] ?: 'N/A',
            'address'            => $row['address'],
            'lat'                => floatval($row['lat'] ?: 14.5995),
            'lng'                => floatval($row['lng'] ?: 120.9842),
            'ownerName'          => $row['owner_name'],
            'ownerPhone'         => $row['owner_phone'],
            'lastInspectionDate' => $row['inspection_date'],
            'inspectionStatus'   => $row['inspection_status'],
            'complianceStatus'   => $compStatus,
            'endorsementStatus'  => $row['endorsement_status'],
            'certificateNumber'  => $row['certificate_number'],
            'certStatus'         => $row['cert_status'],
            'certExpiry'         => $row['expiry_date'],
            'markerColor'        => $compColor,
        ];
    }

    echo json_encode(['success' => true, 'markers' => $markers, 'total' => count($markers)]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
