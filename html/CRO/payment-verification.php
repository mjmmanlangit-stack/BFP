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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BFP CRO - Fire Code Fee Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
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
      .main-content { padding: 1rem 20px; padding-left: 270px; }
      .header {
        background: linear-gradient(135deg, var(--bfp-red) 0%, var(--bfp-dark-red) 100%);
        color: white; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-radius: 10px;
      }
      .header h1 { font-weight: bold; margin: 0; }
      .header .logo { width:60px; height:60px; background:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:2rem; color:var(--bfp-red); }
      .search-box { background:white; padding:1.5rem; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.1); margin:2rem 0; }
      .table-container { background:white; padding:1.5rem; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
      .stats-row { display:flex; gap:1rem; margin-bottom:1.5rem; flex-wrap:wrap; }
      .stat-box { background:white; border-radius:10px; padding:1.25rem 1.5rem; box-shadow:0 2px 8px rgba(0,0,0,0.08); flex:1; min-width:140px; border-left:4px solid var(--bfp-red); }
      .stat-box h3 { font-size:2rem; margin:0; color:var(--bfp-dark); }
      .stat-box p { color:#6c757d; margin:0.3rem 0 0; }
      .btn-bfp { background-color: var(--bfp-red); color: white; border: none; }
      .btn-bfp:hover { background-color: var(--bfp-dark-red); color: white; }
      .btn-view { background-color: var(--bfp-gold); color: var(--bfp-dark); border: none; font-weight: 600; }
      .btn-view:hover { background-color: #e0a800; color: var(--bfp-dark); }
      .modal-header { background: linear-gradient(135deg, var(--bfp-red) 0%, var(--bfp-dark-red) 100%); color: white; }
      .modal-header .btn-close { filter: brightness(0) invert(1); }
      .info-label { font-weight: 600; color: var(--bfp-dark-red); margin-bottom: 0.25rem; }
      .info-value { color: var(--bfp-dark); margin-bottom: 1rem; padding: 0.5rem; background-color: var(--bfp-light); border-radius: 5px; }
      .defects-box { background-color: #fff3cd; border-left: 4px solid var(--bfp-gold); padding: 1rem; border-radius: 5px; }
      .grace-period-box { background-color: #f8d7da; border-left: 4px solid var(--bfp-red); padding: 1rem; border-radius: 5px; }
      .payment-section { background-color: #d1ecf1; border-left: 4px solid #17a2b8; padding: 1.5rem; border-radius: 5px; margin-top: 1rem; }
      .table-hover tbody tr:hover { background-color: rgba(220, 53, 69, 0.05); }
      .badge-status { font-size: 0.85rem; padding: 0.4rem 0.8rem; }
    </style>
  </head>
  <body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
      <div class="logo-section">
        <div class="logo"><i class="fas fa-shield-alt" style="color: var(--bfp-red); font-size: 24px"></i></div>
        <h5 class="mb-0">BFP SiteProfiler</h5>
      </div>
      <nav class="sidebar-nav">
        <div class="nav-item"><a href="./dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></div>
        <div class="nav-item"><a href="./establishments.php" class="nav-link"><i class="fas fa-building"></i> Establishments</a></div>
        <div class="nav-item"><a href="./payment-verification.php" class="nav-link active"><i class="fas fa-money-bill-wave"></i> Fee Management</a></div>
        <div class="nav-item"><a href="./user-management.php" class="nav-link"><i class="fas fa-users"></i> User Management</a></div>
        <div class="nav-item"><a href="./inspection-history.php" class="nav-link"><i class="fas fa-history"></i> Inspection History</a></div>
        <div class="nav-item"><a href="./reports.php" class="nav-link"><i class="fas fa-chart-bar"></i> Reports</a></div>
        <div class="nav-item"><a href="./gis-map.php" class="nav-link"><i class="fas fa-map-marker-alt"></i> GIS Map</a></div>
      </nav>
      <div class="sidebar-logout"><a href="../../utility/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
    </div>

    <!-- Main Content -->
    <div class="main-content my-4">
      <div class="header mb-4">
        <div class="row align-items-center">
          <div class="col-auto">
            <div class="logo"><i class="fas fa-money-bill-wave"></i></div>
          </div>
          <div class="col">
            <h1>Fire Code Fee Management</h1>
            <p class="mb-0">Review inspected establishments and confirm fire code fee payments.</p>
          </div>
          <div class="col-auto">
            <span class="badge bg-light text-dark fs-6"><i class="fas fa-user-circle me-1"></i><?= $croName ?></span>
          </div>
        </div>
      </div>

      <!-- Stats -->
      <div class="stats-row">
        <div class="stat-box">
          <h3 id="statTotal">—</h3>
          <p>Total Inspections</p>
        </div>
        <div class="stat-box" style="border-left-color:#ffc107;">
          <h3 id="statPending">—</h3>
          <p>Pending Payment</p>
        </div>
        <div class="stat-box" style="border-left-color:#28a745;">
          <h3 id="statPaid">—</h3>
          <p>Payment Confirmed</p>
        </div>
      </div>

      <!-- Search -->
      <div class="search-box">
        <div class="row align-items-center">
          <div class="col-md-8">
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-search"></i></span>
              <input type="text" class="form-control" id="searchInput" placeholder="Search by owner name or establishment name..." />
            </div>
          </div>
          <div class="col-md-2 mt-2 mt-md-0">
            <select class="form-select" id="paymentFilter">
              <option value="all">All</option>
              <option value="unpaid">Pending Payment</option>
              <option value="paid">Paid</option>
            </select>
          </div>
          <div class="col-md-2 mt-2 mt-md-0">
            <button class="btn btn-bfp w-100" onclick="applyFilter()"><i class="fas fa-filter"></i> Filter</button>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="table-container">
        <h4 class="mb-3"><i class="fas fa-clipboard-list"></i> Inspected Establishments — Fire Code Fee Status</h4>
        <div class="table-responsive">
          <table class="table table-hover" id="establishmentsTable">
            <thead class="table-dark">
              <tr>
                <th>Owner Name</th>
                <th>Business Name</th>
                <th>Business Type</th>
                <th>Date Inspected</th>
                <th>Compliance</th>
                <th>Payment Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="tableBody">
              <tr><td colspan="7" class="text-center py-3"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-file-alt"></i> Establishment Details &amp; Fee Assessment</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
                <div class="info-label">Owner Name:</div>
                <div class="info-value" id="modalOwnerName"></div>
              </div>
              <div class="col-md-6">
                <div class="info-label">Business Name:</div>
                <div class="info-value" id="modalBusinessName"></div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="info-label">Business Type:</div>
                <div class="info-value" id="modalBusinessType"></div>
              </div>
              <div class="col-md-6">
                <div class="info-label">Date of Inspection:</div>
                <div class="info-value" id="modalInspectionDate"></div>
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <div class="info-label">Address:</div>
                <div class="info-value" id="modalAddress"></div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="info-label">Inspector 1:</div>
                <div class="info-value" id="modalInspector1"></div>
              </div>
              <div class="col-md-6">
                <div class="info-label">Inspector 2:</div>
                <div class="info-value" id="modalInspector2"></div>
              </div>
            </div>
            <div class="row mt-3">
              <div class="col-12">
                <div class="defects-box">
                  <div class="info-label"><i class="fas fa-exclamation-triangle"></i> Defects/Deficiencies:</div>
                  <div id="modalDefects" class="mt-1"></div>
                </div>
              </div>
            </div>
            <div class="row mt-3">
              <div class="col-12">
                <div class="grace-period-box">
                  <div class="info-label"><i class="fas fa-clock"></i> Grace Period:</div>
                  <div id="modalGracePeriod" class="mt-1"></div>
                </div>
              </div>
            </div>
            <!-- Payment Section -->
            <div id="paymentSection" class="mt-3"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Receipt Modal -->
    <div class="modal fade" id="receiptModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-receipt"></i> Fire Code Fee Receipt</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div id="receiptContent" style="background-color: #f9f9f9; padding: 2rem; border-radius: 8px; font-family: 'Courier New', monospace; font-size: 0.95rem;">
              <!-- Receipt will be rendered here -->
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-bfp" onclick="printReceipt()"><i class="fas fa-print"></i> Print Receipt</button>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/scripts/components/sidebar.js"></script>
    <script>
      let inspections = [];
      let currentInspectionId = null;      let currentEstablishmentId = 0;      const detailsModal = new bootstrap.Modal(document.getElementById('detailsModal'));

      function showAlert(message, type = 'info') {
        const el = document.createElement('div');
        el.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
        el.style.zIndex = '9999';
        el.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 5000);
      }

      async function loadInspections() {
        try {
          const res  = await fetch('../../utility/getAccessorInspections.php');
          const data = await res.json();
          if (data.success) {
            inspections = data.inspections;
            renderTable(inspections);
            updateStats(inspections);
          } else {
            showAlert('Failed to load data: ' + (data.message || 'Unknown error'), 'danger');
          }
        } catch (e) {
          showAlert('Network error while loading inspections.', 'danger');
        }
      }

      function updateStats(data) {
        document.getElementById('statTotal').textContent   = data.length;
        document.getElementById('statPending').textContent = data.filter(i => i.payment === 'unpaid').length;
        document.getElementById('statPaid').textContent    = data.filter(i => i.payment === 'paid').length;
      }

      function renderTable(data) {
        const tbody = document.getElementById('tableBody');
        if (!data.length) {
          tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>No finalized inspections found.</td></tr>';
          return;
        }
        tbody.innerHTML = data.map((est, i) => {
          const payBadge = est.payment === 'paid'
            ? '<span class="badge bg-success badge-status">Paid</span>'
            : '<span class="badge bg-warning text-dark badge-status">Unpaid</span>';
          const compBadge = est.complianceStatus === 'compliant'
            ? '<span class="badge bg-success badge-status">Compliant</span>'
            : est.complianceStatus === 'partially_compliant'
              ? '<span class="badge bg-warning text-dark badge-status">Partial</span>'
              : '<span class="badge bg-danger badge-status">Non-Compliant</span>';
          return `<tr>
            <td>${est.ownerName || '—'}</td>
            <td>${est.businessName || '—'}</td>
            <td>${est.businessType || '—'}</td>
            <td>${est.inspectionDate ? est.inspectionDate.substring(0,10) : '—'}</td>
            <td>${compBadge}</td>
            <td>${payBadge}</td>
            <td><button class="btn btn-view btn-sm" onclick="viewDetails(${i})"><i class="fas fa-eye"></i> View</button></td>
          </tr>`;
        }).join('');
      }

      function viewDetails(index) {
        const est = inspections[index];
        currentInspectionId = est.inspectionId;
        currentEstablishmentId = est.establishmentId;

        document.getElementById('modalOwnerName').textContent       = est.ownerName || '—';
        document.getElementById('modalBusinessName').textContent    = est.businessName || '—';
        document.getElementById('modalBusinessType').textContent    = est.businessType || '—';
        document.getElementById('modalAddress').textContent         = est.address || '—';
        document.getElementById('modalInspector1').textContent      = est.inspector1 || '—';
        document.getElementById('modalInspector2').textContent      = est.inspector2 || '—';
        document.getElementById('modalInspectionDate').textContent  = est.inspectionDate ? est.inspectionDate.substring(0,10) : '—';
        document.getElementById('modalDefects').textContent         = est.defects || 'No defects found';
        document.getElementById('modalGracePeriod').textContent     = est.gracePeriod || 'N/A';

        const paymentSection = document.getElementById('paymentSection');
        if (est.payment === 'paid') {
          paymentSection.innerHTML = `
            <div class="alert alert-success">
              <i class="fas fa-check-circle"></i> <strong>Payment Completed</strong>
              <p class="mb-0 mt-2">This establishment has already paid the fire code fee.</p>
              <div class="mt-3">
                <button class="btn btn-warning btn-sm" onclick="viewPaymentReceipt(${currentInspectionId})">
                  <i class="fas fa-receipt"></i> View Receipt
                </button>
              </div>
            </div>`;
        } else {
          paymentSection.innerHTML = `
            <div class="payment-section">
              <h5 class="mb-3"><i class="fas fa-money-bill-wave"></i> Fire Code Fee Payment</h5>
              <div class="mb-3">
                <label for="totalPayment" class="form-label fw-bold">Total Payment (₱):</label>
                <input type="number" class="form-control" id="totalPayment" placeholder="Enter total payment amount" step="0.01" min="0" />
              </div>
              <button class="btn btn-bfp w-100" onclick="updatePayment()">
                <i class="fas fa-save"></i> Mark as Paid
              </button>
            </div>`;
        }

        detailsModal.show();
      }

      function applyFilter() {
        const search  = document.getElementById('searchInput').value.toLowerCase();
        const payment = document.getElementById('paymentFilter').value;
        const filtered = inspections.filter(i => {
          const matchSearch  = !search || (i.ownerName || '').toLowerCase().includes(search) || (i.businessName || '').toLowerCase().includes(search);
          const matchPayment = payment === 'all' || i.payment === payment;
          return matchSearch && matchPayment;
        });
        renderTable(filtered);
        updateStats(filtered);
      }

      document.getElementById('searchInput').addEventListener('input', applyFilter);

      async function updatePayment() {
        const payInput = document.getElementById('totalPayment');
        if (!payInput || !payInput.value || parseFloat(payInput.value) <= 0) {
          showAlert('Please enter a valid payment amount', 'warning');
          return;
        }
        const amount    = parseFloat(payInput.value);
        const ownerName = document.getElementById('modalOwnerName').textContent;

        if (!confirm(`Mark payment of ₱${amount.toFixed(2)} as paid for ${ownerName}?`)) return;

        try {
          const res  = await fetch('../../utility/updatePaymentStatus.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
              inspectionId: currentInspectionId, 
              establishmentId: currentEstablishmentId,
              paymentAmount: amount 
            })
          });
          const data = await res.json();
          if (data.success) {
            showAlert(`Payment of ₱${amount.toFixed(2)} successfully recorded for ${ownerName}!`, 'success');
            const insp = inspections.find(i => i.inspectionId === currentInspectionId || i.establishmentId === currentEstablishmentId);
            if (insp) insp.payment = 'paid';
            detailsModal.hide();
            loadInspections(); // Reload to reflect updated payment status
          } else {
            showAlert('Failed to update payment: ' + (data.message || 'Unknown error'), 'danger');
          }
        } catch (e) {
          showAlert('Network error while updating payment.', 'danger');
        }
      }

      const receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));

      async function viewPaymentReceipt(inspectionId) {
        try {
          const res = await fetch(`../../utility/getPaymentReceipt.php?inspectionId=${inspectionId}`);
          const data = await res.json();
          
          if (data.success && data.receipt) {
            const receipt = data.receipt;
            const receiptHTML = formatReceiptHTML(receipt);
            document.getElementById('receiptContent').innerHTML = receiptHTML;
            receiptModal.show();
          } else {
            showAlert('Failed to load receipt: ' + (data.message || 'Unknown error'), 'danger');
          }
        } catch (e) {
          showAlert('Network error while loading receipt.', 'danger');
          console.error(e);
        }
      }

      function formatReceiptHTML(receipt) {
        const hasValue = (value) => {
          return value !== null &&
                 value !== undefined &&
                 String(value).trim() !== '' &&
                 String(value).trim() !== '—' &&
                 String(value).trim().toLowerCase() !== 'null' &&
                 String(value).trim().toLowerCase() !== 'undefined';
        };

        const clean = (value, fallback = 'Not available') => {
          return hasValue(value) ? String(value).trim() : fallback;
        };

        const escapeHTML = (value, fallback = 'Not available') => {
          return clean(value, fallback)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
        };

        const formatCurrency = (amount) => {
          const parsed = parseFloat(amount);
          return !isNaN(parsed) && parsed > 0 ? '₱' + parsed.toFixed(2) : '₱0.00';
        };

        const formatDate = (dateStr) => {
          if (!hasValue(dateStr)) return 'Not available';

          const date = new Date(dateStr);
          if (isNaN(date.getTime())) return 'Not available';

          return date.toLocaleDateString('en-PH', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
          });
        };

        const formatTime = (dateStr) => {
          if (!hasValue(dateStr)) return '';

          const date = new Date(dateStr);
          if (isNaN(date.getTime())) return '';

          return date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
          });
        };

        const getYear = () => {
          const sourceDate = receipt.paymentConfirmedAt || receipt.paymentDate || receipt.inspectionDate;
          const date = new Date(sourceDate);
          return isNaN(date.getTime()) ? new Date().getFullYear() : date.getFullYear();
        };

        const getRecordId = () => {
          return receipt.inspectionId ||
                 receipt.inspection_id ||
                 receipt.establishmentId ||
                 receipt.establishment_id ||
                 '0000';
        };

        const receiptNumber = clean(
          receipt.receiptNumber ||
          receipt.receipt_number ||
          receipt.fireCodeReceiptNumber ||
          receipt.fire_code_receipt_number ||
          receipt.fireCodeCertificateNumber,
          `BFP-FCF-${getYear()}-${String(getRecordId()).padStart(5, '0')}`
        );

        const fireCodeReferenceNumber = clean(
          receipt.fireCodeReferenceNumber ||
          receipt.fire_code_reference_number ||
          receipt.fireCodeCertificateNumber ||
          receipt.certificate_number,
          `FCR-${getYear()}-${String(getRecordId()).padStart(5, '0')}`
        );

        const paymentStatus = clean(receipt.paymentStatus, 'Unpaid');
        const paymentStatusColor = paymentStatus.toLowerCase() === 'paid' ? '#d4edda' : '#fff3cd';
        const paymentDateSource = receipt.paymentDate || receipt.paymentConfirmedAt;

        let html = `
          <div style="text-align: center; margin-bottom: 2rem; border-bottom: 2px solid #333; padding-bottom: 1rem;">
            <h3 style="margin: 0; font-weight: bold;">BUREAU OF FIRE PROTECTION</h3>
            <p style="margin: 0.25rem 0; font-size: 0.9rem;">Fire Code Fee Payment Receipt</p>
          </div>

          <div style="margin-bottom: 1.5rem;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
              <div>
                <span style="font-weight: bold;">Receipt Number:</span><br/>
                <span>${escapeHTML(receiptNumber)}</span>
              </div>
              <div>
                <span style="font-weight: bold;">Receipt Date:</span><br/>
                <span>${formatDate(receipt.paymentConfirmedAt || receipt.paymentDate)}</span>
              </div>
            </div>
          </div>

          <div style="margin-bottom: 1.5rem; border-top: 1px solid #999; border-bottom: 1px solid #999; padding: 1rem 0;">
            <div style="margin-bottom: 0.8rem;">
              <span style="font-weight: bold;">Establishment Name:</span><br/>
              <span>${escapeHTML(receipt.establishmentName)}</span>
            </div>
            <div style="margin-bottom: 0.8rem;">
              <span style="font-weight: bold;">Address:</span><br/>
              <span>${escapeHTML(receipt.establishmentAddress)}</span>
            </div>
            <div style="margin-bottom: 0.8rem;">
              <span style="font-weight: bold;">Owner/Payer Name:</span><br/>
              <span>${escapeHTML(receipt.ownerName)}</span>
            </div>
            <div style="margin-bottom: 0.8rem;">
              <span style="font-weight: bold;">Email:</span><br/>
              <span>${escapeHTML(receipt.ownerEmail)}</span>
            </div>
            <div style="margin-bottom: 0.8rem;">
              <span style="font-weight: bold;">Contact:</span><br/>
              <span>${escapeHTML(receipt.ownerPhone)}</span>
            </div>
          </div>

          <div style="margin-bottom: 1.5rem;">
            <div style="margin-bottom: 0.8rem;">
              <span style="font-weight: bold;">Fire Code Reference Number:</span><br/>
              <span>${escapeHTML(fireCodeReferenceNumber)}</span>
            </div>
            <div style="margin-bottom: 0.8rem;">
              <span style="font-weight: bold;">Inspection Date:</span><br/>
              <span>${formatDate(receipt.inspectionDate)}</span>
            </div>
            <div style="margin-bottom: 0.8rem;">
              <span style="font-weight: bold;">Compliance Status:</span><br/>
              <span>${hasValue(receipt.complianceStatus) ? escapeHTML(receipt.complianceStatus.replace('_', ' ').toUpperCase()) : 'Not available'}</span>
            </div>
          </div>

          <div style="margin-bottom: 1.5rem; border-top: 1px solid #999; border-bottom: 1px solid #999; padding: 1rem 0;">
            <h4 style="margin: 0 0 0.5rem 0; font-size: 0.95rem;">PAYMENT DETAILS</h4>
            <table style="width: 100%; border-collapse: collapse;">
              <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 0.5rem 0; font-weight: bold;">Fire Code Fee</td>
                <td style="padding: 0.5rem 0; text-align: right;">${formatCurrency(receipt.paymentAmount)}</td>
              </tr>
              <tr style="border-bottom: 2px solid #333;">
                <td style="padding: 0.75rem 0; font-weight: bold; font-size: 1.1rem;">TOTAL AMOUNT DUE</td>
                <td style="padding: 0.75rem 0; text-align: right; font-weight: bold; font-size: 1.1rem;">${formatCurrency(receipt.paymentAmount)}</td>
              </tr>
            </table>
          </div>

          <div style="margin-bottom: 1.5rem;">
            <div style="margin-bottom: 0.8rem;">
              <span style="font-weight: bold;">Payment Status:</span><br/>
              <span style="background-color: ${paymentStatusColor}; padding: 0.3rem 0.6rem; border-radius: 3px;">${escapeHTML(paymentStatus)}</span>
            </div>
            <div style="margin-bottom: 0.8rem;">
              <span style="font-weight: bold;">Payment Date:</span><br/>
              <span>${formatDate(paymentDateSource)} ${formatTime(paymentDateSource)}</span>
            </div>
            <div style="margin-bottom: 0.8rem;">
              <span style="font-weight: bold;">Payment Confirmed By:</span><br/>
              <span>${escapeHTML(receipt.paymentConfirmedBy)}</span>
            </div>
          </div>

          ${receipt.defects && receipt.defects.length > 0 ? `
          <div style="margin-bottom: 1.5rem; background-color: #fff9e6; padding: 1rem; border-left: 3px solid #ffc107; border-radius: 3px;">
            <h4 style="margin: 0 0 0.5rem 0; font-size: 0.95rem;"><i class="fas fa-exclamation-triangle"></i> Noted Defects/Deficiencies:</h4>
            <ul style="margin: 0; padding-left: 1.5rem;">
              ${receipt.defects.map(d => `
                <li style="margin-bottom: 0.3rem; font-size: 0.9rem;">
                  ${escapeHTML(d.details)}
                  ${hasValue(d.grace_period) ? ' (Grace Period: ' + escapeHTML(d.grace_period) + ')' : ''}
                </li>
              `).join('')}
            </ul>
          </div>
          ` : ''}

          <div style="margin-bottom: 1.5rem;">
            <span style="font-weight: bold;">Remarks:</span><br/>
            <span style="font-size: 0.9rem;">${escapeHTML(receipt.remarks)}</span>
          </div>

          <div style="text-align: center; margin-top: 2rem; border-top: 1px solid #999; padding-top: 1rem; font-size: 0.85rem; color: #666;">
            <p style="margin: 0.3rem 0;">This is an official fire code fee receipt.<br/>Please retain for your records.</p>
          </div>
        `;

        return html;
      }

      function printReceipt() {
        const printContent = document.getElementById('receiptContent').innerHTML;
        const originalContent = document.body.innerHTML;
        const printHTML = `
          <html>
            <head>
              <title>Fire Code Fee Receipt - Print</title>
              <style>
                body { font-family: 'Courier New', monospace; font-size: 12pt; margin: 20px; line-height: 1.6; }
                table { width: 100%; border-collapse: collapse; }
                td { padding: 5px; }
              </style>
            </head>
            <body>
              ${printContent}
            </body>
          </html>
        `;
        const printWindow = window.open('', '', 'width=800,height=600');
        printWindow.document.write(printHTML);
        printWindow.document.close();
        printWindow.print();
        printWindow.close();
      }

      document.addEventListener('DOMContentLoaded', loadInspections);
    </script>
  </body>
</html>
