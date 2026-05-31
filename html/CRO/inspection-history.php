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
    <title>BFP CRO - Inspection History</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../../assets/styles/components/sidebar.css"/>
    <style>
        :root { --bfp-red:#dc3545; --bfp-dark-red:#a02834; --bfp-gold:#ffc107; --bfp-dark:#1a1a1a; --bfp-light:#f8f9fa; }
        body { background:#f8f9fa; font-family:'Segoe UI',sans-serif; }
        .main-container { padding:30px 20px; padding-left:270px; }
        .welcome-section { background:linear-gradient(135deg,var(--bfp-red),var(--bfp-dark-red)); color:#fff; padding:30px; border-radius:10px; margin-bottom:30px; }
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
        <div class="nav-item"><a href="./dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></div>
        <div class="nav-item"><a href="./establishments.php" class="nav-link"><i class="fas fa-building"></i> Establishments</a></div>
        <div class="nav-item"><a href="./payment-verification.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Fee Management</a></div>
        <div class="nav-item"><a href="./user-management.php" class="nav-link"><i class="fas fa-users"></i> User Management</a></div>
        <div class="nav-item"><a href="./inspection-history.php" class="nav-link active"><i class="fas fa-history"></i> Inspection History</a></div>
        <div class="nav-item"><a href="./reports.php" class="nav-link"><i class="fas fa-chart-bar"></i> Reports</a></div>
        <div class="nav-item"><a href="./gis-map.php" class="nav-link"><i class="fas fa-map-marker-alt"></i> GIS Map</a></div>
    </nav>
    <div class="sidebar-logout"><a href="../../utility/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
</div>

<div class="container-fluid main-container">
    <div class="welcome-section">
        <h1><i class="fas fa-history"></i> Inspection History</h1>
        <p>View all inspection records across all establishments.</p>
    </div>

    <div class="table-container">
        <div class="d-flex gap-2 flex-wrap mb-3">
            <input type="text" id="searchInput" class="form-control" style="max-width:250px" placeholder="Search establishment, owner…"/>
            <select id="complianceFilter" class="form-select" style="max-width:180px">
                <option value="">All Compliance</option>
                <option value="compliant">Compliant</option>
                <option value="non-compliant">Non-Compliant</option>
                <option value="conditionally compliant">Conditional</option>
            </select>
            <select id="statusFilter" class="form-select" style="max-width:180px">
                <option value="">All Statuses</option>
                <option value="scheduled">Scheduled</option>
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
                <option value="approved">Approved</option>
            </select>
            <button class="btn btn-danger" onclick="loadHistory()"><i class="fas fa-search"></i> Filter</button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Establishment</th>
                        <th>Owner</th>
                        <th>Inspector(s)</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Compliance</th>
                        <th>Certificate</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="historyTable">
                    <tr><td colspan="8" class="text-center py-3"><span class="spinner-border spinner-border-sm me-2"></span>Loading…</td></tr>
                </tbody>
            </table>
        </div>
        <div id="historyCount" class="text-muted small mt-2"></div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

<!-- Inspection Detail Modal -->
<div class="modal fade" id="inspectionModal" tabindex="-1" aria-labelledby="inspectionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="inspectionModalLabel"><i class="fas fa-clipboard-list me-2"></i>Inspection Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <h6 class="text-danger border-bottom pb-1 mb-3">Establishment Info</h6>
        <div class="row g-2 mb-3">
          <div class="col-md-6"><strong>Name:</strong> <span id="modalEstName"></span></div>
          <div class="col-md-6"><strong>Registration No.:</strong> <span id="modalRegNo"></span></div>
          <div class="col-md-6"><strong>Type:</strong> <span id="modalEstType"></span></div>
          <div class="col-md-6"><strong>Owner:</strong> <span id="modalOwner"></span></div>
          <div class="col-12"><strong>Address:</strong> <span id="modalAddress"></span></div>
        </div>
        <h6 class="text-danger border-bottom pb-1 mb-3">Inspection Info</h6>
        <div class="row g-2 mb-3">
          <div class="col-md-6"><strong>Date:</strong> <span id="modalDate"></span></div>
          <div class="col-md-6"><strong>Time Slot:</strong> <span id="modalTimeSlot"></span></div>
          <div class="col-md-6"><strong>Type:</strong> <span id="modalType"></span></div>
          <div class="col-md-6"><strong>Priority:</strong> <span id="modalPriority"></span></div>
          <div class="col-md-6"><strong>Status:</strong> <span id="modalStatus"></span></div>
          <div class="col-md-6"><strong>Inspector(s):</strong> <span id="modalInspectors"></span></div>
        </div>
        <h6 class="text-danger border-bottom pb-1 mb-3">Report &amp; Certificate</h6>
        <div class="row g-2">
          <div class="col-md-6"><strong>Compliance:</strong> <span id="modalCompliance"></span></div>
          <div class="col-md-6"><strong>Endorsement:</strong> <span id="modalEndorsement"></span></div>
          <div class="col-md-6"><strong>Finalized At:</strong> <span id="modalFinalized"></span></div>
          <div class="col-md-6"><strong>Certificate No.:</strong> <span id="modalCertNo"></span></div>
          <div class="col-md-6"><strong>Cert. Status:</strong> <span id="modalCertStatus"></span></div>
          <div class="col-md-6"><strong>Cert. Expiry:</strong> <span id="modalCertExpiry"></span></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
let allHistory = [];

async function loadHistory() {
    const tbody = document.getElementById('historyTable');
    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-3"><span class="spinner-border spinner-border-sm me-2"></span>Loading…</td></tr>';
    try {
        const res = await fetch('../../utility/getInspectionHistory.php');
        const d   = await res.json();
        if (!d.success) { tbody.innerHTML = `<tr><td colspan="8" class="text-danger text-center">${d.message}</td></tr>`; return; }
        allHistory = d.history || [];
        applyFiltersAndSave();
    } catch(e) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error loading history.</td></tr>';
    }
}

let filteredRows = [];

function applyFiltersAndSave() {
    const search  = document.getElementById('searchInput').value.toLowerCase();
    const compF   = document.getElementById('complianceFilter').value.toLowerCase();
    const statF   = document.getElementById('statusFilter').value.toLowerCase();
    const tbody   = document.getElementById('historyTable');

    filteredRows = allHistory;
    if (search) filteredRows = filteredRows.filter(r =>
        (r.establishment_name||'').toLowerCase().includes(search) ||
        (r.owner_name||'').toLowerCase().includes(search));
    if (compF)  filteredRows = filteredRows.filter(r => (r.compliance_status||'').toLowerCase() === compF);
    if (statF)  filteredRows = filteredRows.filter(r => (r.inspection_status||'').toLowerCase() === statF);

    document.getElementById('historyCount').textContent = `Showing ${filteredRows.length} of ${allHistory.length} records`;

    if (!filteredRows.length) { tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-2">No records found.</td></tr>'; return; }

    tbody.innerHTML = filteredRows.map((r, idx) => {
        const inspectors = [r.inspector1_name, r.inspector2_name].filter(Boolean).join(', ') || '—';
        const compBadge = r.compliance_status
            ? `<span class="badge ${r.compliance_status.toLowerCase()==='compliant'?'bg-success':r.compliance_status.toLowerCase().includes('conditional')?'bg-warning text-dark':'bg-danger'}">${r.compliance_status}</span>`
            : '—';
        const statBadge = `<span class="badge bg-secondary">${r.inspection_status||'—'}</span>`;
        return `<tr>
            <td><strong>${r.establishment_name||'—'}</strong><br><small class="text-muted">${r.registration_no||''}</small></td>
            <td>${r.owner_name||'—'}</td>
            <td>${inspectors}</td>
            <td>${r.inspection_date ? new Date(r.inspection_date).toLocaleDateString() : '—'}</td>
            <td>${statBadge}</td>
            <td>${compBadge}</td>
            <td>${r.certificate_number ? `<span class="badge bg-info text-dark">${r.certificate_number}</span>` : '—'}</td>
            <td><button class="btn btn-sm btn-outline-danger" onclick="viewInspection(${idx})"><i class="fas fa-eye"></i> View</button></td>
        </tr>`;
    }).join('');
}

function viewInspection(idx) {
    const r = filteredRows[idx];
    if (!r) return;
    const inspectors = [r.inspector1_name, r.inspector2_name].filter(Boolean).join(', ') || '—';
    const fmt = d => d ? new Date(d).toLocaleDateString() : '—';
    const fmtDt = d => d ? new Date(d).toLocaleString() : '—';

    document.getElementById('modalEstName').textContent   = r.establishment_name || '—';
    document.getElementById('modalRegNo').textContent     = r.registration_no    || '—';
    document.getElementById('modalEstType').textContent   = r.establishment_type  || '—';
    document.getElementById('modalAddress').textContent   = r.address            || '—';
    document.getElementById('modalOwner').textContent     = r.owner_name         || '—';
    document.getElementById('modalInspectors').textContent= inspectors;
    document.getElementById('modalDate').textContent      = fmt(r.inspection_date);
    document.getElementById('modalTimeSlot').textContent  = r.time_slot          || '—';
    document.getElementById('modalType').textContent      = r.inspection_type    || '—';
    document.getElementById('modalPriority').textContent  = r.priority_level     || '—';
    document.getElementById('modalStatus').textContent    = r.inspection_status  || '—';
    document.getElementById('modalCompliance').textContent= r.compliance_status  || '—';
    document.getElementById('modalEndorsement').textContent= r.endorsement_status || '—';
    document.getElementById('modalFinalized').textContent = fmtDt(r.finalized_at);
    document.getElementById('modalCertNo').textContent    = r.certificate_number || '—';
    document.getElementById('modalCertStatus').textContent= r.cert_status        || '—';
    document.getElementById('modalCertExpiry').textContent= fmt(r.expiry_date);

    const modal = new bootstrap.Modal(document.getElementById('inspectionModal'));
    modal.show();
}

document.getElementById('searchInput').addEventListener('input', applyFiltersAndSave);
document.getElementById('complianceFilter').addEventListener('change', applyFiltersAndSave);
document.getElementById('statusFilter').addEventListener('change', applyFiltersAndSave);
document.addEventListener('DOMContentLoaded', loadHistory);
</script>
</body>
</html>
