<?php
session_start();
if (!isset($_SESSION['user']) || strtolower($_SESSION['role']) !== 'chief') {
    header('Location: ../index.php');
    exit;
}
$chiefName = htmlspecialchars($_SESSION['fullname'] ?? 'Fire Chief');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>BFP Chief Dashboard - Site Profiler</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../../assets/styles/components/sidebar.css"/>
    <style>
        :root { --bfp-red:#dc3545; --bfp-dark-red:#a02834; --bfp-gold:#ffc107; --bfp-dark:#1a1a1a; --bfp-light:#f8f9fa; }
        body { background-color:var(--bfp-light); font-family:'Segoe UI',sans-serif; }
        .main-content { margin-left:250px; padding:20px; }
        .top-navbar { background:#fff; padding:15px 30px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,.05); margin-bottom:30px; }
        .stat-card { background:#fff; border-radius:10px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,.05); transition:transform .3s; }
        .stat-card:hover { transform:translateY(-5px); box-shadow:0 5px 20px rgba(0,0,0,.1); }
        .stat-icon { width:60px; height:60px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:24px; }
        .table-card { background:#fff; border-radius:10px; padding:25px; box-shadow:0 2px 10px rgba(0,0,0,.05); }
        .badge-pending   { background:var(--bfp-gold); color:var(--bfp-dark); }
        .badge-scheduled { background:#0dcaf0; color:#fff; }
        .badge-completed { background:#198754; color:#fff; }
        .badge-overdue   { background:var(--bfp-red); color:#fff; }
        .endorsement-badge { background:#6f42c1; color:#fff; }
        .btn-bfp { background:var(--bfp-red); color:#fff; border:none; transition:all .3s; }
        .btn-bfp:hover { background:var(--bfp-dark-red); color:#fff; transform:translateY(-2px); }
        .modal-header { background:linear-gradient(90deg,var(--bfp-red),var(--bfp-dark-red)); color:#fff; }
        .priority-high   { color:var(--bfp-red); font-weight:bold; }
        .priority-medium { color:var(--bfp-gold); font-weight:bold; }
        .priority-low    { color:#6c757d; }
        @media(max-width:768px){ .main-content{ margin-left:70px; } }
    </style>
</head>
<body>
<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="logo-section">
        <div class="logo"><i class="fas fa-shield-alt" style="color:var(--bfp-red);font-size:24px"></i></div>
        <h5 class="mb-0">BFP SiteProfiler</h5>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-item"><a href="./dashboard.php" class="nav-link active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></div>
        <div class="nav-item"><a href="./sched_inspection.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Schedule Inspection</a></div>
        <div class="nav-item"><a href="./review-reports.php" class="nav-link"><i class="fas fa-check-double"></i> Review Reports <span class="badge bg-danger ms-1" id="sidebarEndorseBadge"></span></a></div>
        <div class="nav-item"><a href="./reports.php" class="nav-link"><i class="fas fa-file-alt"></i> Inspection History</a></div>
        <div class="nav-item"><a href="./gis-map.php" class="nav-link"><i class="fas fa-map-marker-alt"></i> GIS Map</a></div>
    </nav>
    <div class="nav-item">
        <a href="../../utility/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="top-navbar d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0">Welcome, <?= $chiefName ?></h4>
            <small class="text-muted">Fire Chief - BFP Site Profiler</small>
        </div>
        <div class="d-flex gap-2">
            <a href="./sched_inspection.php" class="btn btn-bfp btn-sm"><i class="fas fa-calendar-plus me-1"></i>Schedule Inspection</a>
            <a href="./review-reports.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-check-double me-1"></i>Pending Reviews
                <span class="badge bg-danger ms-1" id="headerEndorseBadge"></span>
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4" id="statsRow">
        <div class="col-md-3 mb-3">
            <div class="stat-card"><div class="d-flex justify-content-between align-items-center">
                <div><p class="text-muted mb-1">Total Inspections</p><h3 class="mb-0" id="statTotal">—</h3></div>
                <div class="stat-icon" style="background:rgba(220,53,69,.1);color:var(--bfp-red)"><i class="fas fa-clipboard-check"></i></div>
            </div></div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card"><div class="d-flex justify-content-between align-items-center">
                <div><p class="text-muted mb-1">Pending</p><h3 class="mb-0" id="statPending">—</h3></div>
                <div class="stat-icon" style="background:rgba(255,193,7,.1);color:var(--bfp-gold)"><i class="fas fa-clock"></i></div>
            </div></div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card"><div class="d-flex justify-content-between align-items-center">
                <div><p class="text-muted mb-1">This Week</p><h3 class="mb-0" id="statWeek">—</h3></div>
                <div class="stat-icon" style="background:rgba(13,202,240,.1);color:#0dcaf0"><i class="fas fa-calendar-week"></i></div>
            </div></div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card"><div class="d-flex justify-content-between align-items-center">
                <div><p class="text-muted mb-1">Active Inspectors</p><h3 class="mb-0" id="statInspectors">—</h3></div>
                <div class="stat-icon" style="background:rgba(25,135,84,.1);color:#198754"><i class="fas fa-user-shield"></i></div>
            </div></div>
        </div>
    </div>

    <!-- Pending Endorsements Alert -->
    <div id="endorseAlert" class="alert alert-warning d-none mb-4">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong><span id="endorseCount">0</span> inspection report(s)</strong> are awaiting your review and endorsement.
        <a href="./review-reports.php" class="alert-link ms-2">Review now →</a>
    </div>

    <!-- Compliance overview row -->
    <div class="row mb-4" id="complianceRow">
        <div class="col-md-4 mb-3">
            <div class="table-card text-center">
                <p class="text-muted mb-1 small">Compliance Rate</p>
                <div class="fs-2 fw-bold text-success" id="compRate">—%</div>
                <canvas id="complianceChart" height="160" class="mt-2"></canvas>
            </div>
        </div>
        <div class="col-md-8 mb-3">
            <div class="table-card">
                <p class="text-muted mb-2 small"><i class="fas fa-chart-bar me-1"></i>Monthly Compliance Trend</p>
                <canvas id="complianceTrendChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <!-- Upcoming Inspections Table -->
    <div class="table-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><i class="fas fa-calendar-alt text-info me-2"></i>Upcoming Scheduled Inspections</h5>
            <a href="./sched_inspection.php" class="btn btn-sm btn-bfp"><i class="fas fa-plus me-1"></i>New</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr>
                    <th>Establishment</th><th>Inspector(s)</th><th>Scheduled Date</th><th>Time Slot</th><th>Priority</th><th>Status</th>
                </tr></thead>
                <tbody id="upcomingTable">
                    <tr><td colspan="6" class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
async function loadDashboard() {
    try {
        const res = await fetch('../../utility/getChiefData.php');
        const d   = await res.json();
        if (!d.success) { console.error(d.error); return; }

        document.getElementById('statTotal').textContent     = d.total;
        document.getElementById('statPending').textContent   = d.pending;
        document.getElementById('statWeek').textContent      = d.thisWeek;
        document.getElementById('statInspectors').textContent= d.activeInspectors;

        if (d.pendingEndorsements > 0) {
            document.getElementById('endorseAlert').classList.remove('d-none');
            document.getElementById('endorseCount').textContent = d.pendingEndorsements;
            document.getElementById('sidebarEndorseBadge').textContent = d.pendingEndorsements;
            document.getElementById('headerEndorseBadge').textContent  = d.pendingEndorsements;
        }

        const tbody = document.getElementById('upcomingTable');
        if (!d.upcoming || d.upcoming.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No upcoming inspections.</td></tr>';
            return;
        }

        tbody.innerHTML = d.upcoming.map(i => `
            <tr>
                <td><strong>${i.establishment_name}</strong><br><small class="text-muted">${i.establishment_type || ''}</small></td>
                <td>${[i.inspector1_name, i.inspector2_name].filter(Boolean).join(', ') || '<span class="text-muted">Unassigned</span>'}</td>
                <td>${i.inspection_date ? new Date(i.inspection_date).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'}) : '—'}</td>
                <td>${i.time_slot || '—'}</td>
                <td><span class="priority-${(i.priority_level||'').toLowerCase()}">${i.priority_level || '—'}</span></td>
                <td><span class="badge badge-${i.status}">${i.status}</span></td>
            </tr>`).join('');
    } catch (e) { console.error(e); }
}

async function loadComplianceStats() {
    try {
        const res = await fetch('../../utility/getComplianceStats.php');
        const d   = await res.json();
        if (!d.success) return;

        const oc = d.overallCompliance || {};
        document.getElementById('compRate').textContent = (oc.compliance_rate ?? '—') + '%';

        new Chart(document.getElementById('complianceChart'), {
            type: 'doughnut',
            data: {
                labels: ['Compliant','Partially Compliant','Non-Compliant'],
                datasets:[{ data:[oc.compliant||0, oc.partially_compliant||0, oc.non_compliant||0],
                    backgroundColor:['#198754','#ffc107','#dc3545'], borderWidth:0 }]
            },
            options:{ responsive:true, plugins:{ legend:{ position:'bottom' } } }
        });

        if (d.monthlyTrend && d.monthlyTrend.length) {
            new Chart(document.getElementById('complianceTrendChart'), {
                type: 'line',
                data: {
                    labels: d.monthlyTrend.map(t => t.month),
                    datasets:[{
                        label: 'Compliance Rate %',
                        data: d.monthlyTrend.map(t => t.compliance_rate || 0),
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25,135,84,.1)',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 3
                    }]
                },
                options:{
                    responsive:true,
                    plugins:{ legend:{ display:false } },
                    scales:{ y:{ beginAtZero:true, max:100,
                        ticks:{ callback: v => v+'%' } } }
                }
            });
        }
    } catch(e) { console.error(e); }
}

document.addEventListener('DOMContentLoaded', () => {
    loadDashboard();
    loadComplianceStats();
});
</script>
</body>
</html>
