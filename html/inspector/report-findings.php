<?php
session_start();

// Check if user is logged in and is an inspector
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'inspector') {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BFP Inspector - Notice of Compliance</title>
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
      rel="stylesheet"
    />
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="../../assets/styles/components/sidebar.css" />
    <style>
      :root {
        --bfp-red: #dc3545;
        --bfp-dark-red: #a02834;
        --bfp-gold: #ffc107;
        --bfp-dark: #1a1a1a;
        --bfp-light: #f8f9fa;
      }

      body {
        background-color: var(--bfp-light);
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      }
      .main-content {
        padding: 2rem 20px;
        padding-left: 270px;
      }
      .header-section {
        background: linear-gradient(
          135deg,
          var(--bfp-red) 0%,
          var(--bfp-dark-red) 100%
        );
        color: white;
        padding: 30px;
        margin-bottom: 30px;
        border-radius: 10px;
      }

      .filter-btn {
        margin: 5px;
      }

      .filter-btn.active {
        background-color: var(--bfp-red) !important;
        border-color: var(--bfp-red) !important;
      }

      .table-container {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
      }

      .table thead {
        background-color: var(--bfp-dark);
        color: white;
      }

      .highlight-warning {
        background-color: #fff3cd !important;
        border-left: 4px solid var(--bfp-gold);
      }

      .highlight-critical {
        background-color: #f8d7da !important;
        border-left: 4px solid var(--bfp-red);
      }

      .badge-compliant {
        background-color: #28a745;
      }

      .badge-non-compliant {
        background-color: var(--bfp-red);
      }

      .btn-view {
        background-color: var(--bfp-red);
        color: white;
        border: none;
      }

      .btn-view:hover {
        background-color: var(--bfp-dark-red);
        color: white;
      }

      .modal-header {
        background: linear-gradient(
          135deg,
          var(--bfp-red) 0%,
          var(--bfp-dark-red) 100%
        );
        color: white;
      }

      .modal-header .btn-close {
        filter: brightness(0) invert(1);
      }

      #map {
        height: 300px;
        width: 100%;
        border-radius: 8px;
        margin: 15px 0;
      }

      .info-label {
        font-weight: bold;
        color: var(--bfp-dark);
      }

      .btn-compliant {
        background-color: #28a745;
        color: white;
        border: none;
      }

      .btn-compliant:hover {
        background-color: #218838;
        color: white;
      }

      .btn-non-compliant {
        background-color: var(--bfp-red);
        color: white;
        border: none;
      }

      .btn-non-compliant:hover {
        background-color: var(--bfp-dark-red);
        color: white;
      }

      .defects-box {
        background-color: var(--bfp-light);
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid var(--bfp-red);
      }

      .defect-item {
        background: white;
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        position: relative;
      }

      .defect-item.solved {
        border-left: 4px solid #28a745;
        background-color: #f0f9f4;
      }

      .defect-item.pending {
        border-left: 4px solid var(--bfp-gold);
      }

      .defect-status-badge {
        position: absolute;
        top: 10px;
        right: 10px;
      }



      .compliance-summary {
        background: var(--bfp-light);
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
      }

      .finalized-badge {
        display: inline-block;
        padding: 8px 15px;
        border-radius: 6px;
        font-weight: bold;
        margin-top: 10px;
      }
    </style>
  </head>
  <body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
      <div class="logo-section">
        <div class="logo">
          <i
            class="fas fa-shield-alt"
            style="color: var(--bfp-red); font-size: 24px"
          ></i>
        </div>
        <h5 class="mb-0">BFP SiteProfiler</h5>
      </div>

      <nav class="sidebar-nav">
        <div class="nav-item">
          <a href="./dashboard.php" class="nav-link">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard
          </a>
        </div>
        <div class="nav-item">
          <a href="./assigned-inspections.php" class="nav-link">
            <i class="fas fa-building"></i>
            Assigned Inspections
          </a>
        </div>
        <div class="nav-item">
          <a href="./report-findings.php" class="nav-link active">
            <i class="fas fa-calendar-check"></i>
            Report Findings
          </a>
        </div>
      </nav>

      <div class="nav-item">
        <a href="../../utility/logout.php" class="nav-link">
          <i class="fas fa-sign-out-alt"></i>
          Logout
        </a>
      </div>
    </div>

    <div class="main-content">

      <div class="header-section">
        <div class="container">
          <h2>
            <i class="fas fa-clipboard-list me-2"></i>Notice of Compliance Reports
          </h2>
          <p class="mb-0">Monitor and track establishment compliance status</p>
        </div>
      </div>
  
      <div class="container mb-5">
        <div class="row mb-3">
          <div class="col-12">
            <button
              class="btn btn-outline-dark filter-btn active"
              data-filter="all"
            >
              <i class="fas fa-list me-1"></i>All Reports
            </button>
            <button
              class="btn btn-outline-success filter-btn"
              data-filter="compliant"
            >
              <i class="fas fa-check-circle me-1"></i>Compliant
            </button>

            <button
              class="btn btn-outline-danger filter-btn"
              data-filter="non_compliant"
            >
              <i class="fas fa-exclamation-circle me-1"></i>Non-Compliant
            </button>
            <button
              class="btn btn-outline-secondary filter-btn"
              data-filter="pending"
            >
              <i class="fas fa-clock me-1"></i>Pending
            </button>
          </div>
        </div>
  
        <div class="table-container">
          <div class="table-responsive">
            <table class="table table-hover" id="complianceTable">
              <thead>
                <tr>
                  <th>Report Date</th>
                  <th>Order No.</th>
                  <th>Business Name</th>
                  <th>Business Type</th>
                  <th>Defects Progress</th>
                  <th>Grace Period</th>
                  <th>Days Left</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody id="tableBody"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- View Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="fas fa-file-alt me-2"></i>Compliance Report Details
            </h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <div class="row mb-3">
              <div class="col-md-6">
                <p>
                  <span class="info-label">Report Date:</span>
                  <span id="modalReportDate"></span>
                </p>
                <p>
                  <span class="info-label">Inspection Order No.:</span>
                  <span id="modalOrderNo"></span>
                </p>
                <p>
                  <span class="info-label">Inspection Date:</span>
                  <span id="modalInspectionDate"></span>
                </p>
                <p>
                  <span class="info-label">Business Name:</span>
                  <span id="modalBusinessName"></span>
                </p>
              </div>
              <div class="col-md-6">
                <p>
                  <span class="info-label">BFP Registration No.:</span>
                  <span id="modalRegNo"></span>
                </p>
                <p>
                  <span class="info-label">Business Type:</span>
                  <span id="modalBusinessType"></span>
                </p>
                <p>
                  <span class="info-label">Inspectors:</span>
                  <span id="modalInspectors"></span>
                </p>
              </div>
            </div>

            <div class="compliance-summary">
              <div class="row">
                <div class="col-md-4">
                  <strong>Total Defects:</strong>
                  <span id="summaryTotalDefects" class="badge bg-secondary ms-2">0</span>
                </div>
                <div class="col-md-4">
                  <strong>Solved:</strong>
                  <span id="summarySolvedDefects" class="badge bg-success ms-2">0</span>
                </div>
                <div class="col-md-4">
                  <strong>Pending:</strong>
                  <span id="summaryPendingDefects" class="badge bg-warning ms-2">0</span>
                </div>
              </div>
              <div id="currentComplianceStatus" style="display: none;" class="mt-2">
                <strong>Current Status:</strong>
                <span id="currentStatusBadge" class="finalized-badge"></span>
              </div>
            </div>

            <div class="mb-3">
              <p class="info-label">Address:</p>
              <p id="modalAddress"></p>
            </div>

            <div class="mb-3">
              <p class="info-label">Coordinates:</p>
              <p>
                <i class="fas fa-map-marker-alt me-2"></i
                ><span id="modalCoordinates"></span>
              </p>
              <div id="map"></div>
            </div>

            <div class="mb-3">
              <p class="info-label">Defects/Deficiencies Status:</p>
              <div class="defects-box" id="modalDefects"></div>
            </div>

            <div class="mb-3">
              <label for="inspectorNotes" class="form-label info-label"
                >Inspector Notes/Comments: <span class="text-danger">*</span></label
              >
              <textarea
                class="form-control"
                id="inspectorNotes"
                rows="4"
                placeholder="Enter your final assessment notes and comments here..."
              ></textarea>
            </div>

            <div class="alert alert-info">
              <i class="fas fa-info-circle me-2"></i>
              <strong>Note:</strong> Update each defect status (Solved/Pending) above, then finalize the overall compliance status below.
            </div>

            <div class="d-grid gap-2">
              <button
                type="button"
                class="btn btn-compliant"
                onclick="finalizeCompliance('compliant')"
              >
                <i class="fas fa-check-circle me-2"></i>Mark as COMPLIANT (All defects solved)
              </button>

              <button
                type="button"
                class="btn btn-non-compliant"
                onclick="finalizeCompliance('non_compliant')"
              >
                <i class="fas fa-times-circle me-2"></i>Mark as NON-COMPLIANT (Major issues)
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script>
      let map;
      let marker;
      let currentRecord = null;
      let allReports = [];
      let currentFilter = 'all';

      // Load all reports from backend
      async function loadReports() {
        try {
          const response = await fetch('../../utility/getInspectorReports.php');
          const data = await response.json();

          if (data.success) {
            allReports = data.reports;
            renderTable();
          } else {
            showAlert('Error loading reports: ' + data.message, 'danger');
          }
        } catch (error) {
          console.error('Error:', error);
          showAlert('Failed to load reports', 'danger');
        }
      }

      function showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
          ${message}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.querySelector('.main-content').insertBefore(
          alertDiv, 
          document.querySelector('.header-section').nextSibling
        );
        
        setTimeout(() => {
          alertDiv.remove();
        }, 5000);
      }

      function getRowClass(daysLeft) {
        if (daysLeft !== null && daysLeft <= 0) return "highlight-critical";
        if (daysLeft !== null && daysLeft <= 2) return "highlight-warning";
        return "";
      }

      function getStatusBadge(status) {
        if (status === 'compliant') {
          return '<span class="badge badge-compliant">Compliant</span>';

        } else if (status === 'non_compliant') {
          return '<span class="badge badge-non-compliant">Non-Compliant</span>';
        } else {
          return '<span class="badge bg-secondary">Pending Review</span>';
        }
      }

      function renderTable() {
        const tbody = document.getElementById("tableBody");
        tbody.innerHTML = "";

        let filteredData = allReports;
        if (currentFilter !== "all") {
          if (currentFilter === 'pending') {
            filteredData = allReports.filter(r => !r.complianceStatus);
          } else {
            filteredData = allReports.filter(r => r.complianceStatus === currentFilter);
          }
        }

        if (filteredData.length === 0) {
          tbody.innerHTML = `
            <tr>
              <td colspan="9" class="text-center text-muted py-4">
                <i class="fas fa-inbox fa-3x mb-3"></i>
                <p>No reports found</p>
              </td>
            </tr>
          `;
          return;
        }

        filteredData.forEach((report) => {
          const rowClass = getRowClass(report.daysLeft);
          const statusBadge = getStatusBadge(report.complianceStatus);
          const daysLeftText = report.daysLeft !== null 
            ? `${report.daysLeft} days` 
            : 'N/A';
          const gracePeriodText = report.earliestGracePeriod || 'N/A';
          const progressText = `${report.solvedDefects}/${report.totalDefects}`;
          const progressPercent = report.totalDefects > 0 
            ? Math.round((report.solvedDefects / report.totalDefects) * 100) 
            : 0;

          const row = `
            <tr class="${rowClass}">
              <td>${report.reportDate}</td>
              <td>${report.inspectionOrderNo}</td>
              <td>${report.businessName}</td>
              <td>${report.businessType}</td>
              <td>
                <div class="progress" style="height: 20px;">
                  <div class="progress-bar ${progressPercent === 100 ? 'bg-success' : 'bg-warning'}" 
                       role="progressbar" 
                       style="width: ${progressPercent}%"
                       aria-valuenow="${progressPercent}" 
                       aria-valuemin="0" 
                       aria-valuemax="100">
                    ${progressText}
                  </div>
                </div>
              </td>
              <td>${gracePeriodText}</td>
              <td><strong>${daysLeftText}</strong></td>
              <td>${statusBadge}</td>
              <td>
                <button class="btn btn-view btn-sm" onclick="viewDetails(${report.reportId})">
                  <i class="fas fa-eye me-1"></i>View
                </button>
              </td>
            </tr>
          `;
          tbody.innerHTML += row;
        });
      }

      function updateDefectSummary() {
        if (!currentRecord) return;
        
        const total = currentRecord.defects.length;
        const solved = currentRecord.defects.filter(d => d.status === 'solved').length;
        const pending = total - solved;
        
        document.getElementById('summaryTotalDefects').textContent = total;
        document.getElementById('summarySolvedDefects').textContent = solved;
        document.getElementById('summaryPendingDefects').textContent = pending;
        
        // Show current status if finalized
        if (currentRecord.complianceStatus) {
          const statusDiv = document.getElementById('currentComplianceStatus');
          const statusBadge = document.getElementById('currentStatusBadge');
          statusDiv.style.display = 'block';
          
          let statusText = '';
          let statusClass = '';
          if (currentRecord.complianceStatus === 'compliant') {
            statusText = 'COMPLIANT';
            statusClass = 'bg-success text-white';

          } else {
            statusText = 'NON-COMPLIANT';
            statusClass = 'bg-danger text-white';
          }
          
          statusBadge.textContent = statusText;
          statusBadge.className = 'finalized-badge ' + statusClass;
        } else {
          document.getElementById('currentComplianceStatus').style.display = 'none';
        }
      }

      async function toggleDefectStatus(defectId, currentStatus) {
        const newStatus = currentStatus === 'solved' ? 'pending' : 'solved';
        
        try {
          const response = await fetch('../../utility/updateDefectStatus.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              defectId: defectId,
              status: newStatus
            })
          });

          const data = await response.json();

          if (data.success) {
            // Update local data
            const defect = currentRecord.defects.find(d => d.id === defectId);
            if (defect) {
              defect.status = newStatus;
            }
            
            // Re-render defects and update summary
            renderDefects();
            updateDefectSummary();
            
            showAlert(`Defect marked as ${newStatus}`, 'success');
          } else {
            showAlert('Error: ' + data.message, 'danger');
          }
        } catch (error) {
          console.error('Error:', error);
          showAlert('Failed to update defect status', 'danger');
        }
      }

      function renderDefects() {
        const container = document.getElementById('modalDefects');
        container.innerHTML = '';
        
        if (!currentRecord || !currentRecord.defects || currentRecord.defects.length === 0) {
          container.innerHTML = '<p class="text-muted">No defects recorded</p>';
          return;
        }
        
        currentRecord.defects.forEach((defect, index) => {
          const itemClass = defect.status === 'solved' ? 'defect-item solved' : 'defect-item pending';
          const statusBadge = defect.status === 'solved'
            ? '<span class="badge bg-success defect-status-badge">Solved</span>'
            : '<span class="badge bg-warning defect-status-badge">Pending</span>';
          
          const defectDiv = document.createElement('div');
          defectDiv.className = itemClass;
          defectDiv.innerHTML = `
            ${statusBadge}
            <h6><i class="fas fa-exclamation-circle me-2"></i>Defect #${index + 1}</h6>
            <p class="mb-2"><strong>Details:</strong> ${defect.details}</p>
            <p class="mb-2"><strong>Grace Period:</strong> ${defect.gracePeriod}</p>
            <button class="btn btn-sm ${defect.status === 'solved' ? 'btn-warning' : 'btn-success'}" 
                    onclick="toggleDefectStatus(${defect.id}, '${defect.status}')">
              <i class="fas ${defect.status === 'solved' ? 'fa-undo' : 'fa-check'}"></i>
              Mark as ${defect.status === 'solved' ? 'Pending' : 'Solved'}
            </button>
          `;
          
          container.appendChild(defectDiv);
        });
      }

      async function viewDetails(reportId) {
        currentRecord = allReports.find((r) => r.reportId === reportId);
        if (!currentRecord) return;

        // Populate modal fields
        document.getElementById("modalReportDate").textContent = currentRecord.reportDate;
        document.getElementById("modalOrderNo").textContent = currentRecord.inspectionOrderNo;
        document.getElementById("modalInspectionDate").textContent = currentRecord.inspectionDate;
        document.getElementById("modalBusinessName").textContent = currentRecord.businessName;
        document.getElementById("modalRegNo").textContent = currentRecord.regNo;
        document.getElementById("modalBusinessType").textContent = currentRecord.businessType;
        document.getElementById("modalInspectors").textContent = 
          `${currentRecord.inspector1}, ${currentRecord.inspector2}`;
        document.getElementById("modalAddress").textContent = currentRecord.address;
        document.getElementById("modalCoordinates").textContent = 
          `${currentRecord.latitude}, ${currentRecord.longitude}`;
        
        // Set notes if already finalized
        document.getElementById("inspectorNotes").value = currentRecord.inspectorNotes || '';
        
        // Render defects with status controls
        renderDefects();
        
        // Update summary
        updateDefectSummary();

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById("detailsModal"));
        modal.show();

        // Initialize map after modal is shown
        setTimeout(() => {
          initMap(currentRecord.latitude, currentRecord.longitude);
        }, 300);
      }

      function initMap(lat, lng) {
        if (map) {
          map.remove();
        }

        map = L.map("map").setView([lat, lng], 15);

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
          attribution: "© OpenStreetMap contributors",
        }).addTo(map);

        marker = L.marker([lat, lng])
          .addTo(map)
          .bindPopup(currentRecord.businessName)
          .openPopup();

        setTimeout(() => {
          map.invalidateSize();
        }, 100);
      }

      async function finalizeCompliance(complianceStatus) {
        const notes = document.getElementById("inspectorNotes").value.trim();
        
        if (!notes) {
          showAlert("Please enter inspector notes before finalizing", "warning");
          return;
        }

        if (!currentRecord) return;

        // Confirmation
        let statusText = complianceStatus === 'compliant' ? 'COMPLIANT' 
          : 'NON-COMPLIANT';
        
        if (!confirm(`Are you sure you want to mark this report as ${statusText}?`)) {
          return;
        }

        try {
          const response = await fetch('../../utility/finalizeReport.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              reportId: currentRecord.reportId,
              complianceStatus: complianceStatus,
              inspectorNotes: notes
            })
          });

          const data = await response.json();

          if (data.success) {
            showAlert(`Report finalized as ${statusText}`, 'success');
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById("detailsModal")).hide();
            
            // Reload reports
            await loadReports();
          } else {
            showAlert('Error: ' + data.message, 'danger');
          }
        } catch (error) {
          console.error('Error:', error);
          showAlert('Failed to finalize report', 'danger');
        }
      }

      // Filter buttons
      document.querySelectorAll(".filter-btn").forEach((btn) => {
        btn.addEventListener("click", function () {
          document.querySelectorAll(".filter-btn").forEach((b) => b.classList.remove("active"));
          this.classList.add("active");
          currentFilter = this.getAttribute("data-filter");
          renderTable();
        });
      });

      // Initial load
      loadReports();
    </script>
  </body>
</html>
