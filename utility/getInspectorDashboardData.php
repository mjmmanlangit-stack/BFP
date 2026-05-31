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

    $inspectorId = $_SESSION['user'];
    $currentDate = date('Y-m-d');
    $currentDateTime = date('Y-m-d H:i:s');

    // 1. Get inspection counts
    $statsQuery = "SELECT 
                    COUNT(*) as total_inspections,
                    SUM(CASE WHEN status = 'pending' OR status = 'scheduled' THEN 1 ELSE 0 END) as scheduled,
                    SUM(CASE WHEN inspection_date = ? AND (status = 'pending' OR status = 'scheduled') THEN 1 ELSE 0 END) as today_inspections,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN inspection_date < ? AND status != 'completed' THEN 1 ELSE 0 END) as overdue
                   FROM inspection
                   WHERE inspector = ? OR inspector1 = ? OR inspector2 = ?";
    
    $stmt = $conn->prepare($statsQuery);
    $stmt->bind_param("ssiii", $currentDate, $currentDate, $inspectorId, $inspectorId, $inspectorId);
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc();

    // 2. Get reports statistics
    $reportsQuery = "SELECT 
                        COUNT(*) as total_reports,
                        SUM(CASE WHEN r.compliance_status IS NULL THEN 1 ELSE 0 END) as pending_finalization,
                        SUM(CASE WHEN r.compliance_status = 'compliant' THEN 1 ELSE 0 END) as compliant,
                        SUM(CASE WHEN r.compliance_status = 'partially_compliant' THEN 1 ELSE 0 END) as partially_compliant,
                        SUM(CASE WHEN r.compliance_status = 'non_compliant' THEN 1 ELSE 0 END) as non_compliant
                     FROM reports r
                     INNER JOIN inspection i ON r.inspection_id = i.id
                     WHERE i.inspector = ? OR i.inspector1 = ? OR i.inspector2 = ?";
    
    $stmt = $conn->prepare($reportsQuery);
    $stmt->bind_param("iii", $inspectorId, $inspectorId, $inspectorId);
    $stmt->execute();
    $reportsStats = $stmt->get_result()->fetch_assoc();

    // 3. Get defects statistics
    $defectsQuery = "SELECT 
                        COUNT(*) as total_defects,
                        SUM(CASE WHEN d.status = 'solved' THEN 1 ELSE 0 END) as solved_defects,
                        SUM(CASE WHEN d.status = 'pending' THEN 1 ELSE 0 END) as pending_defects,
                        SUM(CASE WHEN d.grace_period < ? AND d.status = 'pending' THEN 1 ELSE 0 END) as overdue_defects
                     FROM defects d
                     INNER JOIN reports r ON d.report_id = r.id
                     INNER JOIN inspection i ON r.inspection_id = i.id
                     WHERE i.inspector = ? OR i.inspector1 = ? OR i.inspector2 = ?";
    
    $stmt = $conn->prepare($defectsQuery);
    $stmt->bind_param("siii", $currentDate, $inspectorId, $inspectorId, $inspectorId);
    $stmt->execute();
    $defectsStats = $stmt->get_result()->fetch_assoc();

    // 4. Get upcoming inspections (next 7 days)
    $upcomingQuery = "SELECT 
                        i.id,
                        i.inspection_date,
                        i.time_slot,
                        i.inspection_type,
                        i.priority_level,
                        e.name as establishment_name,
                        e.address,
                        e.type as establishment_type
                      FROM inspection i
                      INNER JOIN establishment e ON i.establishment_id = e.id
                      WHERE (i.inspector = ? OR i.inspector1 = ? OR i.inspector2 = ?)
                      AND i.inspection_date BETWEEN ? AND DATE_ADD(?, INTERVAL 7 DAY)
                      AND i.status IN ('pending', 'scheduled')
                      ORDER BY i.inspection_date ASC, i.time_slot ASC
                      LIMIT 10";
    
    $stmt = $conn->prepare($upcomingQuery);
    $stmt->bind_param("iiiss", $inspectorId, $inspectorId, $inspectorId, $currentDate, $currentDate);
    $stmt->execute();
    $upcomingResult = $stmt->get_result();
    
    $upcomingInspections = [];
    while ($row = $upcomingResult->fetch_assoc()) {
        $upcomingInspections[] = $row;
    }

    // 5. Get recent completed inspections
    $recentQuery = "SELECT 
                        i.id,
                        i.inspection_date,
                        i.inspection_type,
                        e.name as establishment_name,
                        e.type as establishment_type,
                        r.compliance_status,
                        r.finalized_at
                    FROM inspection i
                    INNER JOIN establishment e ON i.establishment_id = e.id
                    LEFT JOIN reports r ON i.id = r.inspection_id
                    WHERE (i.inspector = ? OR i.inspector1 = ? OR i.inspector2 = ?)
                    AND i.status = 'completed'
                    ORDER BY i.inspection_date DESC
                    LIMIT 5";
    
    $stmt = $conn->prepare($recentQuery);
    $stmt->bind_param("iii", $inspectorId, $inspectorId, $inspectorId);
    $stmt->execute();
    $recentResult = $stmt->get_result();
    
    $recentCompleted = [];
    while ($row = $recentResult->fetch_assoc()) {
        $recentCompleted[] = $row;
    }

    // 6. Get monthly inspection trend (last 6 months)
    $trendQuery = "SELECT 
                        DATE_FORMAT(inspection_date, '%Y-%m') as month,
                        COUNT(*) as count
                   FROM inspection
                   WHERE (inspector = ? OR inspector1 = ? OR inspector2 = ?)
                   AND inspection_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                   GROUP BY DATE_FORMAT(inspection_date, '%Y-%m')
                   ORDER BY month ASC";
    
    $stmt = $conn->prepare($trendQuery);
    $stmt->bind_param("iii", $inspectorId, $inspectorId, $inspectorId);
    $stmt->execute();
    $trendResult = $stmt->get_result();
    
    $monthlyTrend = [];
    while ($row = $trendResult->fetch_assoc()) {
        $monthlyTrend[] = $row;
    }

    // 7. Get establishment types distribution
    $typesQuery = "SELECT 
                        e.type,
                        COUNT(*) as count
                   FROM inspection i
                   INNER JOIN establishment e ON i.establishment_id = e.id
                   WHERE (i.inspector = ? OR i.inspector1 = ? OR i.inspector2 = ?)
                   GROUP BY e.type
                   ORDER BY count DESC";
    
    $stmt = $conn->prepare($typesQuery);
    $stmt->bind_param("iii", $inspectorId, $inspectorId, $inspectorId);
    $stmt->execute();
    $typesResult = $stmt->get_result();
    
    $establishmentTypes = [];
    while ($row = $typesResult->fetch_assoc()) {
        $establishmentTypes[] = $row;
    }

    // 8. Get priority distribution
    $priorityQuery = "SELECT 
                        priority_level,
                        COUNT(*) as count
                      FROM inspection
                      WHERE (inspector = ? OR inspector1 = ? OR inspector2 = ?)
                      AND status IN ('pending', 'scheduled')
                      GROUP BY priority_level";
    
    $stmt = $conn->prepare($priorityQuery);
    $stmt->bind_param("iii", $inspectorId, $inspectorId, $inspectorId);
    $stmt->execute();
    $priorityResult = $stmt->get_result();
    
    $priorityDistribution = [];
    while ($row = $priorityResult->fetch_assoc()) {
        $priorityDistribution[] = $row;
    }

    // Compile all data
    $dashboardData = [
        'success' => true,
        'stats' => [
            'total_inspections' => (int)($stats['total_inspections'] ?? 0),
            'scheduled' => (int)($stats['scheduled'] ?? 0),
            'today_inspections' => (int)($stats['today_inspections'] ?? 0),
            'completed' => (int)($stats['completed'] ?? 0),
            'overdue' => (int)($stats['overdue'] ?? 0)
        ],
        'reports' => [
            'total' => (int)($reportsStats['total_reports'] ?? 0),
            'pending_finalization' => (int)($reportsStats['pending_finalization'] ?? 0),
            'compliant' => (int)($reportsStats['compliant'] ?? 0),
            'partially_compliant' => (int)($reportsStats['partially_compliant'] ?? 0),
            'non_compliant' => (int)($reportsStats['non_compliant'] ?? 0)
        ],
        'defects' => [
            'total' => (int)($defectsStats['total_defects'] ?? 0),
            'solved' => (int)($defectsStats['solved_defects'] ?? 0),
            'pending' => (int)($defectsStats['pending_defects'] ?? 0),
            'overdue' => (int)($defectsStats['overdue_defects'] ?? 0)
        ],
        'upcoming_inspections' => $upcomingInspections,
        'recent_completed' => $recentCompleted,
        'monthly_trend' => $monthlyTrend,
        'establishment_types' => $establishmentTypes,
        'priority_distribution' => $priorityDistribution
    ];

    echo json_encode($dashboardData);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
