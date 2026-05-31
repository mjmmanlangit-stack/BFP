<?php
// ── Session guard ─────────────────────────────────────────────────────────────
session_start();
if (empty($_SESSION['user'])) {
    header("Location: /BFP-Site-Profiler/html/index.php");
    exit;
}
// Restrict to FireMarshal role only
if (strtolower($_SESSION['role']) !== 'firemarshal') {
    header("Location: /BFP-Site-Profiler/html/index.php?error=unauthorized");
    exit;
}
$currentUser = htmlspecialchars($_SESSION['fullname'] ?? 'Fire Marshal');
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BFP Fire Marshal - Certificate Authorization</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../../assets/styles/components/sidebar.css" />
    <style>
      :root {
        --bfp-red: #dc3545;
        --bfp-dark-red: #a02834;
        --bfp-gold: #ffc107;
        --bfp-dark: #1a1a1a;
        --bfp-light: #f8f9fa;
      }
      body { background-color: var(--bfp-light); font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }
      .header { background: linear-gradient(135deg, var(--bfp-red) 0%, var(--bfp-dark-red) 100%); color: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
      .header h1 { font-weight: 700; margin: 0; }
      .header p { margin: 0.5rem 0 0 0; opacity: 0.9; }
      .main-content { padding: 2rem 20px; padding-left: 270px; }
      .stats-card { background: white; border-radius: 10px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-left: 4px solid var(--bfp-red); }
      .stats-card h3 { color: var(--bfp-dark); font-size: 2rem; margin: 0; }
      .stats-card p { color: #6c757d; margin: 0.5rem 0 0 0; }
      .table-container { background: white; border-radius: 10px; padding: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
      .table thead { background-color: var(--bfp-red); color: white; }
      .table thead th { border: none; font-weight: 600; }
      .badge-compliant { background-color: #28a745; }
      .badge-non-compliant { background-color: var(--bfp-red); }
      .badge-paid { background-color: var(--bfp-gold); color: var(--bfp-dark); }
      .badge-unpaid { background-color: #6c757d; }
      .btn-view { background-color: var(--bfp-red); color: white; border: none; }
      .btn-view:hover { background-color: var(--bfp-dark-red); color: white; }
      .modal-header { background: linear-gradient(135deg, var(--bfp-red) 0%, var(--bfp-dark-red) 100%); color: white; }
      .modal-header .btn-close { filter: brightness(0) invert(1); }
      .info-group { margin-bottom: 1.5rem; }
      .info-label { font-weight: 600; color: var(--bfp-dark); margin-bottom: 0.5rem; }
      .info-value { color: #495057; padding: 0.5rem; background-color: var(--bfp-light); border-radius: 5px; }
      #map { height: 300px; border-radius: 10px; margin-top: 1rem; }
      .btn-authorize { background-color: #28a745; color: white; border: none; padding: 0.75rem 2rem; font-weight: 600; }
      .btn-authorize:hover { background-color: #218838; color: white; }
      .btn-deny { background-color: var(--bfp-red); color: white; border: none; padding: 0.75rem 2rem; font-weight: 600; }
      .btn-deny:hover { background-color: var(--bfp-dark-red); color: white; }
      .action-buttons { margin-top: 2rem; padding-top: 1.5rem; border-top: 2px solid var(--bfp-light); }
      .search-box { margin-bottom: 1.5rem; }
      .filter-badge { cursor: pointer; margin: 0.25rem; }
      @media (max-width: 768px) { .table-responsive { font-size: 0.875rem; } .main-content { padding-left: 20px; } }
    </style>
  </head>
  <body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
      <div class="logo-section">
        <div class="logo">
          <i class="fas fa-shield-alt" style="color: var(--bfp-red); font-size: 24px"></i>
        </div>
        <h5 class="mb-0">BFP SiteProfiler</h5>
      </div>
      <nav class="sidebar-nav">
        <div class="nav-item">
          <a href="inspections.php" class="nav-link active">
            <i class="fas fa-certificate"></i>
            Certificate Authorization
          </a>
        </div>
      </nav>
      <div class="nav-item mt-auto">
        <a href="../../utility/logout.php" class="nav-link">
          <i class="fas fa-sign-out-alt"></i>
          Logout
        </a>
      </div>
    </div>

    <div class="main-content">
      <div class="header mb-4">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h1><i class="fas fa-fire-alt"></i> BFP Fire Marshal Dashboard</h1>
            <p>Certificate Release Authorization</p>
          </div>
          <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <span class="badge bg-light text-dark fs-6">
              <i class="fas fa-user-circle me-1"></i><?= $currentUser ?>
            </span>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-4">
          <div class="stats-card">
            <h3 id="totalInspections">0</h3>
            <p>Total Inspections</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stats-card">
            <h3 id="pendingAuth">0</h3>
            <p>Pending Authorization</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stats-card">
            <h3 id="compliantCount">0</h3>
            <p>Compliant Establishments</p>
          </div>
        </div>
      </div>

      <div class="table-container">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="mb-0">Inspection Records</h4>
          <div>
            <span class="badge filter-badge badge-compliant" onclick="filterTable('compliant')">Compliant</span>
            <span class="badge filter-badge badge-non-compliant" onclick="filterTable('non-compliant')">Non-Compliant</span>
            <span class="badge filter-badge bg-secondary" onclick="filterTable('all')">All</span>
          </div>
        </div>
        <div class="search-box">
          <input type="text" class="form-control" id="searchInput" placeholder="Search by business name, owner, or registration number..." />
        </div>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Registration No.</th>
                <th>Business Name</th>
                <th>Owner Name</th>
                <th>Compliance</th>
                <th>Payment</th>
                <th>Authorization</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="inspectionTable"></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Inspection Details Modal -->
    <div class="modal fade" id="inspectionModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-file-alt"></i> Inspection Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
                <div class="info-group">
                  <div class="info-label">Owner Name</div>
                  <div class="info-value" id="modalOwnerName"></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-group">
                  <div class="info-label">Business Name</div>
                  <div class="info-value" id="modalBusinessName"></div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="info-group">
                  <div class="info-label">BFP Registration No.</div>
                  <div class="info-value" id="modalRegNo"></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-group">
                  <div class="info-label">Business Type</div>
                  <div class="info-value" id="modalBusinessType"></div>
                </div>
              </div>
            </div>
            <div class="info-group">
              <div class="info-label">Address</div>
              <div class="info-value" id="modalAddress"></div>
            </div>
            <div class="info-group">
              <div class="info-label">Inspector Notes</div>
              <div class="info-value" id="modalInspectorNotes"></div>
            </div>
            <div class="info-group">
              <div class="info-label">Coordinates &amp; Location</div>
              <div class="info-value">
                <strong>Latitude:</strong> <span id="modalLat"></span> |
                <strong>Longitude:</strong> <span id="modalLng"></span>
              </div>
              <div id="map"></div>
            </div>
            <div class="info-group">
              <div class="info-label">Defects / Deficiencies</div>
              <div class="info-value" id="modalDefects"></div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="info-group">
                  <div class="info-label">Compliance Status</div>
                  <div class="info-value" id="modalStatus"></div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="info-group">
                  <div class="info-label">Payment Status</div>
                  <div class="info-value" id="modalPayment"></div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="info-group">
                  <div class="info-label">Endorsement Status</div>
                  <div class="info-value" id="modalEndorsement"></div>
                </div>
              </div>
            </div>
            <div class="action-buttons text-center">
              <h5 class="mb-3">Certificate Release Authorization</h5>
              <div id="authorizationStatus"></div>
              <div id="authorizationButtons">
                <button class="btn btn-authorize me-2" onclick="authorizeCertificate()">
                  <i class="fas fa-check-circle"></i> Authorize Certificate Release
                </button>
                <button class="btn btn-deny" onclick="denyCertificate()">
                  <i class="fas fa-times-circle"></i> Deny Authorization
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script src="../../assets/scripts/components/sidebar.js"></script>
    <script>
      let map, marker, currentInspectionId;
      let inspections = [];

      async function loadInspections() {
        try {
          const response = await fetch('../../utility/getFireMarshalInspections.php');
          const data = await response.json();
          if (data.success) {
            inspections = data.inspections.map(insp => ({
              id: insp.id,
              reportId: insp.reportId,
              regNo: insp.regNo,
              businessName: insp.businessName,
              ownerName: insp.ownerName,
              businessType: insp.businessType,
              address: insp.address,
              lat: parseFloat(insp.lat) || 12.3685,
              lng: parseFloat(insp.lng) || 123.6174,
              defects: insp.defects,
              defectDetails: insp.defectDetails || [],
              status: insp.status,
              payment: insp.payment,
              inspectorNotes: insp.inspectorNotes || 'No notes recorded.',
              endorsementStatus: insp.endorsementStatus || 'pending',
              authorized: insp.authorization
                ? (insp.authorization.status === 'authorized' ? true : insp.authorization.status === 'denied' ? false : null)
                : null,
              authorizationData: insp.authorization
            }));
            renderTable();
          } else {
            showAlert('Failed to load inspections: ' + (data.message || 'Unknown error'), 'danger');
          }
        } catch (err) {
          showAlert('Network error while loading inspections.', 'danger');
        }
      }

      function showAlert(message, type = 'info') {
        const el = document.createElement('div');
        el.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
        el.style.zIndex = '9999';
        el.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 5000);
      }

      function renderTable() {
        const tbody = document.getElementById('inspectionTable');
        tbody.innerHTML = '';
        inspections.forEach(insp => {
          const statusBadge = insp.status === 'compliant'
            ? '<span class="badge badge-compliant">Compliant</span>'
            : insp.status === 'partially_compliant'
              ? '<span class="badge bg-warning text-dark">Partial</span>'
              : '<span class="badge badge-non-compliant">Non-Compliant</span>';

          const paymentBadge = insp.payment === 'paid' || insp.payment == 1
            ? '<span class="badge badge-paid">Paid</span>'
            : '<span class="badge badge-unpaid">Unpaid</span>';

          let authBadge = '<span class="badge bg-secondary">Pending</span>';
          if (insp.authorized === true)  authBadge = '<span class="badge bg-success">Authorized</span>';
          if (insp.authorized === false) authBadge = '<span class="badge bg-danger">Denied</span>';

          tbody.innerHTML += `
            <tr>
              <td>${insp.regNo || 'N/A'}</td>
              <td>${insp.businessName}</td>
              <td>${insp.ownerName}</td>
              <td>${statusBadge}</td>
              <td>${paymentBadge}</td>
              <td>${authBadge}</td>
              <td>
                <button class="btn btn-view btn-sm" onclick="viewInspection(${insp.id})">
                  <i class="fas fa-eye"></i> View
                </button>
              </td>
            </tr>`;
        });
        updateStats();
      }

      function updateStats() {
        document.getElementById('totalInspections').textContent = inspections.length;
        document.getElementById('pendingAuth').textContent   = inspections.filter(i => i.authorized === null).length;
        document.getElementById('compliantCount').textContent = inspections.filter(i => i.status === 'compliant').length;
      }

      function viewInspection(id) {
        const insp = inspections.find(i => i.id === id);
        currentInspectionId = id;

        document.getElementById('modalOwnerName').textContent    = insp.ownerName;
        document.getElementById('modalBusinessName').textContent = insp.businessName;
        document.getElementById('modalRegNo').textContent        = insp.regNo || 'N/A';
        document.getElementById('modalBusinessType').textContent = insp.businessType;
        document.getElementById('modalAddress').textContent      = insp.address;
        document.getElementById('modalLat').textContent          = insp.lat;
        document.getElementById('modalLng').textContent          = insp.lng;
        document.getElementById('modalInspectorNotes').textContent = insp.inspectorNotes;

        // Defects list
        const defectsEl = document.getElementById('modalDefects');
        if (insp.defectDetails && insp.defectDetails.length > 0) {
          defectsEl.innerHTML = '<ul class="mb-0">' + insp.defectDetails.map(d =>
            `<li><strong>${d.details}</strong> — Grace period: ${d.gracePeriod} 
             <span class="badge ${d.status === 'solved' ? 'bg-success' : 'bg-warning text-dark'}">${d.status}</span></li>`
          ).join('') + '</ul>';
        } else {
          defectsEl.textContent = 'No defects recorded.';
        }

        const statusBadge = insp.status === 'compliant'
          ? '<span class="badge badge-compliant">Compliant</span>'
          : insp.status === 'partially_compliant'
            ? '<span class="badge bg-warning text-dark">Partially Compliant</span>'
            : '<span class="badge badge-non-compliant">Non-Compliant</span>';
        document.getElementById('modalStatus').innerHTML = statusBadge;

        document.getElementById('modalPayment').innerHTML = (insp.payment === 'paid' || insp.payment == 1)
          ? '<span class="badge badge-paid">Paid</span>'
          : '<span class="badge badge-unpaid">Unpaid</span>';

        const endMap = { pending: 'bg-secondary', endorsed: 'bg-success', rejected: 'bg-danger' };
        document.getElementById('modalEndorsement').innerHTML =
          `<span class="badge ${endMap[insp.endorsementStatus] || 'bg-secondary'}">${insp.endorsementStatus || 'Pending'}</span>`;

        // Authorization panel
        const authStatus  = document.getElementById('authorizationStatus');
        const authButtons = document.getElementById('authorizationButtons');

        if (insp.authorized === true) {
          const certNo = insp.authorizationData?.certificateNumber || 'N/A';
          const expiry = insp.authorizationData?.expiry_date || 'N/A';
          authStatus.innerHTML = `
            <div class="alert alert-success">
              <i class="fas fa-check-circle"></i> <strong>Certificate Authorized</strong><br>
              Certificate No: <strong>${certNo}</strong><br>
              Valid Until: <strong>${expiry}</strong>
            </div>`;
          authButtons.style.display = 'none';
        } else if (insp.authorized === false) {
          authStatus.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Certificate Release Denied</div>';
          authButtons.style.display = 'none';
        } else {
          // Eligibility checks
          if (insp.endorsementStatus !== 'endorsed') {
            authStatus.innerHTML = `<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Report has not been endorsed by the Chief yet. Authorization is not allowed until the report is endorsed.</div>`;
            authButtons.style.display = 'none';
          } else if (insp.payment != 1 && insp.payment !== 'paid') {
            authStatus.innerHTML = `<div class="alert alert-info"><i class="fas fa-info-circle"></i> Payment has not been confirmed. Verify payment before authorizing.</div>`;
            authButtons.style.display = 'block';
          } else if (insp.status === 'compliant' || insp.status === 'partially_compliant') {
            authStatus.innerHTML = `<div class="alert alert-success"><i class="fas fa-check-circle"></i> Eligible for authorization. All prerequisites met.</div>`;
            authButtons.style.display = 'block';
          } else {
            authStatus.innerHTML = `<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Non-compliant establishment. Authorize only if all defects are resolved.</div>`;
            authButtons.style.display = 'block';
          }
        }

        const modal = new bootstrap.Modal(document.getElementById('inspectionModal'));
        modal.show();

        setTimeout(() => {
          if (map) { map.remove(); map = null; }
          map = L.map('map').setView([insp.lat, insp.lng], 15);
          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
          }).addTo(map);
          L.marker([insp.lat, insp.lng]).addTo(map)
            .bindPopup(`<b>${insp.businessName}</b><br>${insp.address}`).openPopup();
        }, 300);
      }

      async function authorizeCertificate() {
        const authButtons = document.getElementById('authorizationButtons');
        authButtons.innerHTML = '<div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div>';
        try {
          const res  = await fetch('../../utility/authorizeCertificate.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ inspectionId: currentInspectionId, action: 'authorize', remarks: 'Authorized by Fire Marshal' })
          });
          const data = await res.json();
          if (data.success) {
            const insp = inspections.find(i => i.id === currentInspectionId);
            if (insp) { insp.authorized = true; insp.authorizationData = { status: 'authorized', certificateNumber: data.certificateNumber, expiry_date: data.expiry_date }; }
            document.getElementById('authorizationStatus').innerHTML = `
              <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <strong>Certificate Authorized!</strong><br>
                Certificate No: <strong>${data.certificateNumber}</strong><br>
                Valid Until: <strong>${data.expiry_date || 'N/A'}</strong>
              </div>`;
            authButtons.style.display = 'none';
            renderTable();
            showAlert('Certificate authorized successfully!', 'success');
          } else { throw new Error(data.message || 'Failed'); }
        } catch (err) {
          document.getElementById('authorizationStatus').innerHTML = `<div class="alert alert-danger">${err.message}</div>`;
          authButtons.innerHTML = `
            <button class="btn btn-authorize me-2" onclick="authorizeCertificate()"><i class="fas fa-check-circle"></i> Authorize</button>
            <button class="btn btn-deny" onclick="denyCertificate()"><i class="fas fa-times-circle"></i> Deny</button>`;
        }
      }

      async function denyCertificate() {
        const remarks = prompt('Enter reason for denial:');
        if (remarks === null) return;
        const authButtons = document.getElementById('authorizationButtons');
        authButtons.innerHTML = '<div class="spinner-border text-danger" role="status"><span class="visually-hidden">Loading...</span></div>';
        try {
          const res  = await fetch('../../utility/authorizeCertificate.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ inspectionId: currentInspectionId, action: 'deny', remarks: remarks || 'Denied by Fire Marshal' })
          });
          const data = await res.json();
          if (data.success) {
            const insp = inspections.find(i => i.id === currentInspectionId);
            if (insp) { insp.authorized = false; insp.authorizationData = { status: 'denied' }; }
            document.getElementById('authorizationStatus').innerHTML = '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Certificate Release Denied.</div>';
            authButtons.style.display = 'none';
            renderTable();
            showAlert('Certificate denied.', 'warning');
          } else { throw new Error(data.message || 'Failed'); }
        } catch (err) {
          document.getElementById('authorizationStatus').innerHTML = `<div class="alert alert-danger">${err.message}</div>`;
          authButtons.innerHTML = `
            <button class="btn btn-authorize me-2" onclick="authorizeCertificate()"><i class="fas fa-check-circle"></i> Authorize</button>
            <button class="btn btn-deny" onclick="denyCertificate()"><i class="fas fa-times-circle"></i> Deny</button>`;
        }
      }

      function filterTable(filter) {
        document.querySelectorAll('#inspectionTable tr').forEach(row => {
          if (filter === 'all') { row.style.display = ''; }
          else if (filter === 'compliant')     { row.style.display = row.innerHTML.includes('badge-compliant') ? '' : 'none'; }
          else if (filter === 'non-compliant') { row.style.display = row.innerHTML.includes('badge-non-compliant') ? '' : 'none'; }
        });
      }

      document.getElementById('searchInput').addEventListener('input', e => {
        const term = e.target.value.toLowerCase();
        document.querySelectorAll('#inspectionTable tr').forEach(row => {
          row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
        });
      });

      document.addEventListener('DOMContentLoaded', loadInspections);
    </script>
  </body>
</html>
