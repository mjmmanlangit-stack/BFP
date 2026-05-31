<?php
session_start();
if (!isset($_SESSION['user']) || strtolower($_SESSION['role']) !== 'cro') {
    header('Location: ../index.php');
    exit;
}
$croName = htmlspecialchars($_SESSION['fullname'] ?? 'CRO Officer');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>BFP CRO Dashboard - Site Profiler</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../../assets/styles/components/sidebar.css"/>
    <style>
        :root { --bfp-red:#dc3545; --bfp-dark-red:#a02834; --bfp-gold:#ffc107; --bfp-dark:#1a1a1a; --bfp-light:#f8f9fa; }
        body { background:var(--bfp-light); font-family:'Segoe UI',sans-serif; }
        .main-container { padding:30px 20px; padding-left:270px; }
        .welcome-section { background:linear-gradient(135deg,var(--bfp-red),var(--bfp-dark-red)); color:#fff; padding:30px; border-radius:10px; box-shadow:0 5px 15px rgba(0,0,0,.1); margin-bottom:30px; }
        .stat-card { background:#fff; border-radius:10px; padding:25px; box-shadow:0 2px 10px rgba(0,0,0,.05); transition:all .3s; height:100%; border-left:4px solid; position:relative; overflow:hidden; }
        .stat-card:hover { transform:translateY(-5px); box-shadow:0 5px 20px rgba(0,0,0,.1); }
        .stat-card.total  { border-left-color:#007bff; }
        .stat-card.pending{ border-left-color:var(--bfp-gold); }
        .stat-card.confirmed{ border-left-color:#28a745; }
        .stat-card.docs   { border-left-color:var(--bfp-red); }
        .stat-icon { width:60px; height:60px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:24px; color:#fff; margin-bottom:15px; }
        .stat-card.total   .stat-icon { background:linear-gradient(135deg,#007bff,#0056b3); }
        .stat-card.pending .stat-icon { background:linear-gradient(135deg,var(--bfp-gold),#e0a800); }
        .stat-card.confirmed .stat-icon { background:linear-gradient(135deg,#28a745,#1e7e34); }
        .stat-card.docs    .stat-icon { background:linear-gradient(135deg,var(--bfp-red),var(--bfp-dark-red)); }
        .stat-number { font-size:2.5rem; font-weight:bold; margin-bottom:5px; }
        .stat-label  { color:#6c757d; font-size:.95rem; font-weight:500; }
        .chart-container { background:#fff; border-radius:10px; padding:25px; box-shadow:0 2px 10px rgba(0,0,0,.05); margin-bottom:30px; }
        .table-container { background:#fff; padding:25px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,.05); }
        .table thead { background:var(--bfp-red); color:#fff; }
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
        <div class="nav-item"><a href="./establishments.php" class="nav-link"><i class="fas fa-building"></i> Establishments</a></div>
        <div class="nav-item"><a href="./payment-verification.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Fee Management</a></div>
        <div class="nav-item"><a href="./user-management.php" class="nav-link"><i class="fas fa-users"></i> User Management</a></div>
        <div class="nav-item"><a href="./inspection-history.php" class="nav-link"><i class="fas fa-history"></i> Inspection History</a></div>
        <div class="nav-item"><a href="./reports.php" class="nav-link"><i class="fas fa-chart-bar"></i> Reports</a></div>
        <div class="nav-item"><a href="./gis-map.php" class="nav-link"><i class="fas fa-map-marker-alt"></i> GIS Map</a></div>
    </nav>
    <div class="sidebar-logout"><a href="../../utility/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
</div>

<div class="container-fluid main-container">
    <div class="welcome-section animate__animated animate__fadeInDown">
        <h1><i class="fas fa-chart-line"></i> Customer Relationship Officer</h1>
        <p>Welcome back, <strong><?= $croName ?></strong>! Monitor and verify establishment registrations efficiently.</p>
        <small><i class="fas fa-calendar-day"></i> Today: <span id="currentDate"></span></small>
    </div>

    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card total">
                <div class="stat-icon"><i class="fas fa-building"></i></div>
                <div class="stat-number" id="statTotal">—</div>
                <div class="stat-label">Total Establishments</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card pending">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-number" id="statPending">—</div>
                <div class="stat-label">Pending Payment</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card confirmed">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-number" id="statConfirmed">—</div>
                <div class="stat-label">Payment Confirmed</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card docs">
                <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
                <div class="stat-number" id="statDocs">—</div>
                <div class="stat-label">Pending Documents</div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-4 mb-4">
            <div class="chart-container">
                <h6 class="mb-3"><i class="fas fa-industry text-danger me-2"></i>Establishments by Business Type</h6>
                <canvas id="typeChart" height="200"></canvas>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="chart-container">
                <h6 class="mb-3"><i class="fas fa-chart-pie text-danger me-2"></i>Compliance Overview</h6>
                <div id="overdueAlert" class="alert alert-danger d-none py-2 mb-2">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    <strong id="overdueCount">0</strong> overdue defects require attention.
                    <a href="./inspection-history.php" class="alert-link ms-1">Review →</a>
                </div>
                <canvas id="complianceChart" height="180"></canvas>
                <div class="text-center mt-2">
                    <span class="fw-bold text-success fs-5" id="compRate">—</span>
                    <span class="text-muted small"> compliance rate</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="chart-container">
                <h6 class="mb-3"><i class="fas fa-tasks text-danger me-2"></i>Quick Actions</h6>
                <a href="./establishments.php" class="btn btn-danger w-100 mb-2"><i class="fas fa-building me-2"></i>Verify Establishments & Payments</a>
                <a href="./user-management.php" class="btn btn-outline-danger w-100 mb-2"><i class="fas fa-user-plus me-2"></i>Manage Owner Accounts</a>
                <a href="./inspection-history.php" class="btn btn-outline-secondary w-100 mb-2"><i class="fas fa-history me-2"></i>View Inspection History</a>
                <a href="./reports.php" class="btn btn-outline-primary w-100 mb-2"><i class="fas fa-chart-bar me-2"></i>Compliance Reports</a>
                <a href="./gis-map.php" class="btn btn-outline-success w-100"><i class="fas fa-map-marker-alt me-2"></i>GIS Map</a>
            </div>
        </div>
    </div>

    <!-- Recent Establishments -->
    <div class="table-container">
        <h6 class="mb-3"><i class="fas fa-list text-danger me-2"></i>Recent Establishments</h6>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>Name</th><th>Owner</th><th>Type</th><th>Payment</th><th>Action</th></tr></thead>
                <tbody id="recentTable">
                    <tr><td colspan="5" class="text-center py-3"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
document.getElementById('currentDate').textContent = new Date().toLocaleDateString('en-US',{weekday:'long',year:'numeric',month:'long',day:'numeric'});

async function loadDashboard() {
    try {
        const res = await fetch('../../utility/getCROData.php');
        const d   = await res.json();
        if (!d.success) return;

        document.getElementById('statTotal').textContent    = d.total;
        document.getElementById('statPending').textContent  = d.pending;
        document.getElementById('statConfirmed').textContent= d.confirmed;
        document.getElementById('statDocs').textContent     = d.pendingDocs;

        // Recent table
        const tbody = document.getElementById('recentTable');
        if (!d.recent || !d.recent.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No data yet.</td></tr>';
        } else {
            tbody.innerHTML = d.recent.map(r => `
                <tr>
                    <td><strong>${r.name}</strong></td>
                    <td>${r.owner_name||'—'}<br><small class="text-muted">${r.owner_email||''}</small></td>
                    <td>${r.type||'—'}</td>
                    <td>${r.inspection_id ? `<span class="badge ${r.payment=='1'?'bg-success':'bg-warning text-dark'}">${r.payment=='1'?'Paid':'Pending'}</span>` : '—'}</td>
                    <td><a href="./establishments.php" class="btn btn-sm btn-outline-danger">Review</a></td>
                </tr>`).join('');
        }

        // Establishment type chart
        if (d.byType && d.byType.length) {
            new Chart(document.getElementById('typeChart'), {
                type:'doughnut',
                data: {
                    labels: d.byType.map(t => t.type || 'Unknown'),
                    datasets:[{ data: d.byType.map(t => t.cnt),
                        backgroundColor:['#dc3545','#ffc107','#28a745','#007bff','#6c757d','#17a2b8'] }]
                },
                options:{ responsive:true, plugins:{ legend:{ position:'bottom' } } }
            });
        }
    } catch (e) { console.error(e); }
}

async function loadComplianceStats() {
    try {
        const res = await fetch('../../utility/getComplianceStats.php');
        const d   = await res.json();
        if (!d.success) return;

        const oc = d.overallCompliance || {};
        document.getElementById('compRate').textContent = (oc.compliance_rate ?? '—') + '%';

        // Overdue defect alert
        if (d.overdueDefects > 0) {
            document.getElementById('overdueAlert').classList.remove('d-none');
            document.getElementById('overdueCount').textContent = d.overdueDefects;
        }

        // Compliance doughnut
        new Chart(document.getElementById('complianceChart'), {
            type:'doughnut',
            data:{
                labels:['Compliant','Partially Compliant','Non-Compliant'],
                datasets:[{ data:[oc.compliant||0, oc.partially_compliant||0, oc.non_compliant||0],
                    backgroundColor:['#28a745','#ffc107','#dc3545'], borderWidth:0 }]
            },
            options:{ responsive:true, plugins:{ legend:{ position:'bottom' } } }
        });
    } catch(e) { console.error(e); }
}

document.addEventListener('DOMContentLoaded', () => {
    loadDashboard();
    loadComplianceStats();
});
</script>
</body>
</html>
