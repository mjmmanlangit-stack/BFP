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
    <title>BFP Inspector - Site Profiler</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
    <link
      rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    />
    <link rel="stylesheet" href="../../assets/styles/components/sidebar.css">
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
      .main-container {
        padding: 2rem 20px;
        padding-left: 270px;
      }

      .page-header {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
      }

      .page-header h2 {
        color: var(--bfp-dark-red);
        margin: 0;
        font-weight: bold;
      }

      .filter-section {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-bottom: 1.5rem;
      }

      .btn-bfp-primary {
        background-color: var(--bfp-red);
        border-color: var(--bfp-red);
        color: white;
      }

      .btn-bfp-primary:hover {
        background-color: var(--bfp-dark-red);
        border-color: var(--bfp-dark-red);
        color: white;
      }

      .btn-bfp-gold {
        background-color: var(--bfp-gold);
        border-color: var(--bfp-gold);
        color: var(--bfp-dark);
      }

      .btn-bfp-gold:hover {
        background-color: #e0a800;
        border-color: #e0a800;
        color: var(--bfp-dark);
      }

      .table-container {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }

      .table {
        margin-bottom: 0;
      }

      .table thead {
        background-color: var(--bfp-dark-red);
        color: white;
      }

      .table tbody tr {
        transition: all 0.3s ease;
      }

      .table tbody tr:hover {
        background-color: rgba(220, 53, 69, 0.05);
      }

      .unviewed-row {
        background-color: rgba(255, 193, 7, 0.15);
        font-weight: 500;
      }

      .unviewed-row:hover {
        background-color: rgba(255, 193, 7, 0.25);
      }

      .badge-unviewed {
        background-color: var(--bfp-gold);
        color: var(--bfp-dark);
      }

      .badge-viewed {
        background-color: #6c757d;
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
        border-radius: 8px;
        margin-top: 1rem;
      }

      .info-label {
        font-weight: bold;
        color: var(--bfp-dark-red);
        margin-bottom: 0.5rem;
      }

      .info-value {
        color: var(--bfp-dark);
        margin-bottom: 1rem;
      }

      .report-section {
        background-color: var(--bfp-light);
        padding: 1.5rem;
        border-radius: 8px;
        margin-top: 1rem;
      }

      .report-section h5 {
        color: var(--bfp-dark-red);
        margin-bottom: 1rem;
      }

      .defect-entry {
        background-color: white;
        padding: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        margin-bottom: 1rem;
        position: relative;
      }

      .defect-entry .remove-defect {
        position: absolute;
        top: 10px;
        right: 10px;
      }

      .defect-number {
        font-weight: bold;
        color: var(--bfp-dark-red);
        margin-bottom: 0.5rem;
      }

      @media (max-width: 768px) {
        .table-container {
          overflow-x: auto;
        }

        .page-header h2 {
          font-size: 1.5rem;
        }
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
          <a href="./assigned-inspections.php" class="nav-link active">
            <i class="fas fa-building"></i>
            Assigned Inspections
          </a>
        </div>
        <div class="nav-item">
          <a href="./report-findings.php" class="nav-link">
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

    <div class="container-fluid main-container">
      <div class="page-header">
        <h2><i class="fas fa-clipboard-check"></i> My Assigned Inspections</h2>
        <p class="text-muted mb-0">
          View and manage your inspection assignments
        </p>
      </div>

      <div class="filter-section">
        <div class="row align-items-center">
          <div class="col-md-6">
            <h5 class="mb-3 mb-md-0">
              <i class="fas fa-filter"></i> Filter Inspections
            </h5>
          </div>
          <div class="col-md-6">
            <div class="btn-group w-100" role="group">
              <input
                type="radio"
                class="btn-check"
                name="filter"
                id="filterAll"
                value="all"
                checked
              />
              <label class="btn btn-outline-secondary" for="filterAll">
                <i class="fas fa-list"></i> All
              </label>
              <input
                type="radio"
                class="btn-check"
                name="filter"
                id="filterUnviewed"
                value="unviewed"
              />
              <label class="btn btn-outline-warning" for="filterUnviewed">
                <i class="fas fa-eye-slash"></i> Unviewed
              </label>
              <input
                type="radio"
                class="btn-check"
                name="filter"
                id="filterViewed"
                value="viewed"
              />
              <label class="btn btn-outline-secondary" for="filterViewed">
                <i class="fas fa-eye"></i> Viewed
              </label>
            </div>
          </div>
        </div>
      </div>

      <div class="table-container">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Status</th>
                <th>Business Name</th>
                <th>Business Type</th>
                <th>Inspection Date</th>
                <th>Inspection Time</th>
                <th>Address</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="inspectionTableBody">
              <!-- Table rows will be populated by JavaScript -->
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Inspection Details Modal -->
    <div class="modal fade" id="inspectionModal" tabindex="-1">
      <div
        class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"
      >
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="fas fa-file-alt"></i> Inspection Details
            </h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
                <div class="info-label">Inspection Date:</div>
                <div class="info-value" id="modalInspectionDate"></div>
              </div>
              <div class="col-md-6">
                <div class="info-label">Inspection Time:</div>
                <div class="info-value" id="modalInspectionTime"></div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="info-label">Business Name:</div>
                <div class="info-value" id="modalBusinessName"></div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="info-label">BFP Registration No.:</div>
                <div class="info-value" id="modalRegNo"></div>
              </div>
              <div class="col-md-6">
                <div class="info-label">Business Type:</div>
                <div class="info-value" id="modalBusinessType"></div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="info-label">Address:</div>
                <div class="info-value" id="modalAddress"></div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="info-label">Latitude:</div>
                <div class="info-value" id="modalLatitude"></div>
              </div>
              <div class="col-md-6">
                <div class="info-label">Longitude:</div>
                <div class="info-value" id="modalLongitude"></div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="info-label">Location Map:</div>
                <div id="map"></div>
              </div>
            </div>

            <div class="report-section">
              <h5><i class="fas fa-edit"></i> Submit Inspection Report</h5>
              <form id="reportForm">
                <div class="mb-3">
                  <label for="inspectionOrderNo" class="form-label"
                    >Inspection Order No.</label
                  >
                  <input
                    type="text"
                    class="form-control bg-light"
                    id="inspectionOrderNo"
                    readonly
                  />
                </div>
                
                <div class="mb-3">
                  <label for="complianceStatus" class="form-label">Compliance Status <span class="text-danger">*</span></label>
                  <select class="form-select" id="complianceStatus" required>
                    <option value="">-- Select Compliance Status --</option>
                    <option value="compliant">Compliant</option>

                    <option value="non_compliant">Non-Compliant</option>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label">
                    Defects/Deficiencies
                  </label>
                  <div id="defectsContainer">
                    <!-- Defect entries will be added here -->
                  </div>
                  <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addDefectEntry()">
                    <i class="fas fa-plus"></i> Add Another Defect
                  </button>
                </div>

                <div class="d-grid gap-2">
                  <button type="submit" class="btn btn-bfp-primary">
                    <i class="fas fa-paper-plane"></i> Submit Report
                  </button>
                  <button type="button" class="btn btn-outline-secondary" id="viewExistingReport" style="display: none;">
                    <i class="fas fa-file-alt"></i> View Existing Report
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
      let inspections = [];
      let map;
      let marker;
      let currentFilter = "all";
      let currentInspectionId = null;
      let defectCounter = 0;

      // Load inspections from backend
      async function loadInspections() {
        try {
          const response = await fetch('../../utility/getInspectorAssignments.php');
          const data = await response.json();

          if (data.success) {
            inspections = data.inspections;
            renderTable();
          } else {
            showAlert('Error loading inspections: ' + data.message, 'danger');
          }
        } catch (error) {
          console.error('Error:', error);
          showAlert('Failed to load inspections', 'danger');
        }
      }

      function showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
          ${message}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.querySelector('.main-container').insertBefore(alertDiv, document.querySelector('.page-header').nextSibling);
        
        setTimeout(() => {
          alertDiv.remove();
        }, 5000);
      }

      function renderTable() {
        const tbody = document.getElementById("inspectionTableBody");
        tbody.innerHTML = "";

        const filteredInspections = inspections.filter((inspection) => {
          if (currentFilter === "all") return true;
          if (currentFilter === "viewed") return inspection.viewed;
          if (currentFilter === "unviewed") return !inspection.viewed;
          return true;
        });

        filteredInspections.forEach((inspection) => {
          const row = document.createElement("tr");
          row.className = inspection.viewed ? "" : "unviewed-row";
          row.innerHTML = `
                    <td>
                        <span class="badge ${
                          inspection.viewed ? "badge-viewed" : "badge-unviewed"
                        }">
                            ${
                              inspection.viewed
                                ? '<i class="fas fa-eye"></i> Viewed'
                                : '<i class="fas fa-eye-slash"></i> Unviewed'
                            }
                        </span>
                    </td>
                    <td>${inspection.businessName}</td>
                    <td>${inspection.businessType}</td>
                    <td>${inspection.inspectionDate}</td>
                    <td>${inspection.inspectionTime}</td>
                    <td>${inspection.address}</td>
                    <td>
                        <button class="btn btn-sm btn-bfp-gold" onclick="viewInspection(${
                          inspection.id
                        })">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </td>
                `;
          tbody.appendChild(row);
        });

        if (filteredInspections.length === 0) {
          tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>No inspections found</p>
                        </td>
                    </tr>
                `;
        }
      }

      function addDefectEntry(details = '', gracePeriod = '', evidencePath = '') {
        defectCounter++;
        const container = document.getElementById('defectsContainer');
        const entryDiv = document.createElement('div');
        entryDiv.className = 'defect-entry';
        entryDiv.id = `defect-${defectCounter}`;
        
        const existingEvidenceHtml = evidencePath
          ? `<div class="mt-1">
              <small class="text-muted">Current evidence: 
                <a href="../../${evidencePath}" target="_blank" class="text-primary">
                  <i class="fas fa-paperclip"></i> View file
                </a>
              </small>
             </div>`
          : '';

        entryDiv.innerHTML = `
          ${defectCounter > 1 ? `<button type="button" class="btn btn-sm btn-danger remove-defect" onclick="removeDefectEntry(${defectCounter})">
            <i class="fas fa-times"></i>
          </button>` : ''}
          <div class="defect-number">Defect #${defectCounter}</div>
          <div class="mb-2">
            <label class="form-label">Defect Details</label>
            <textarea class="form-control defect-details" rows="3" 
              placeholder="Describe the defect or deficiency found...">${details}</textarea>
          </div>
          <div class="mb-2">
            <label class="form-label">Grace Period Date</label>
            <input type="date" class="form-control defect-grace-period" value="${gracePeriod}">
          </div>
          <div class="mb-2">
            <label class="form-label">Evidence <small class="text-muted">(Photo/PDF, optional)</small></label>
            <input type="file" class="form-control defect-evidence" accept="image/*,.pdf">
            ${existingEvidenceHtml}
          </div>
        `;
        
        container.appendChild(entryDiv);
      }

      function removeDefectEntry(id) {
        const entry = document.getElementById(`defect-${id}`);
        if (entry) {
          entry.remove();
          updateDefectNumbers();
        }
      }

      function updateDefectNumbers() {
        const entries = document.querySelectorAll('.defect-entry');
        entries.forEach((entry, index) => {
          const numberDiv = entry.querySelector('.defect-number');
          if (numberDiv) {
            numberDiv.textContent = `Defect #${index + 1}`;
          }
        });
      }

      function clearDefects() {
        document.getElementById('defectsContainer').innerHTML = '';
        defectCounter = 0;
      }

      async function loadExistingReport(inspectionId) {
        try {
          const response = await fetch(`../../utility/getInspectionReport.php?inspectionId=${inspectionId}`);
          const data = await response.json();

          if (data.success && data.hasReport) {
            document.getElementById('inspectionOrderNo').value = data.inspectionOrderNo;

            const csSelect = document.getElementById('complianceStatus');
            csSelect.value = data.complianceStatus || '';
            
            clearDefects();
            if (data.defects && data.defects.length > 0) {
              data.defects.forEach(defect => {
                addDefectEntry(defect.details, defect.gracePeriod, defect.evidencePath || '');
              });
            } else {
              addDefectEntry();
            }
            
            document.getElementById('viewExistingReport').style.display = 'block';
            return true;
          } else {
            clearDefects();
            addDefectEntry();
            document.getElementById('viewExistingReport').style.display = 'none';
            return false;
          }
        } catch (error) {
          console.error('Error loading report:', error);
          clearDefects();
          addDefectEntry();
          return false;
        }
      }

      async function viewInspection(id) {
        const inspection = inspections.find((i) => i.id === id);
        if (!inspection) return;

        currentInspectionId = id;

        // Populate modal
        document.getElementById("modalInspectionDate").textContent =
          inspection.inspectionDate;
        document.getElementById("modalInspectionTime").textContent =
          inspection.inspectionTime;
        document.getElementById("modalBusinessName").textContent =
          inspection.businessName;
        document.getElementById("modalRegNo").textContent = inspection.regNo;
        document.getElementById("modalBusinessType").textContent =
          inspection.businessType;
        document.getElementById("modalAddress").textContent =
          inspection.address;
        document.getElementById("modalLatitude").textContent =
          inspection.latitude;
        document.getElementById("modalLongitude").textContent =
          inspection.longitude;

        // Load existing report if available
        const hasExisting = await loadExistingReport(id);

        // Auto-generate order number for new reports
        if (!hasExisting) {
          const year = new Date().getFullYear();
          const paddedId = String(id).padStart(6, '0');
          document.getElementById('inspectionOrderNo').value = `FSIC-${year}-${paddedId}`;
        }

        // Show modal
        const modal = new bootstrap.Modal(
          document.getElementById("inspectionModal")
        );
        modal.show();

        // Initialize map after modal is shown
        setTimeout(() => {
          initMap(inspection.latitude, inspection.longitude);
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

        marker = L.marker([lat, lng]).addTo(map);
        marker.bindPopup("<b>Inspection Location</b>").openPopup();

        setTimeout(() => {
          map.invalidateSize();
        }, 100);
      }

      // Filter functionality
      document.querySelectorAll('input[name="filter"]').forEach((radio) => {
        radio.addEventListener("change", (e) => {
          currentFilter = e.target.value;
          renderTable();
        });
      });

      // Report form submission
      document.getElementById("reportForm").addEventListener("submit", async (e) => {
        e.preventDefault();

        const orderNo = document.getElementById("inspectionOrderNo").value.trim();
        const complianceStatus = document.getElementById("complianceStatus").value;

        if (!complianceStatus) {
          showAlert('Please select a compliance status.', 'warning');
          return;
        }

        // Build FormData (supports file uploads)
        const defectEntries = document.querySelectorAll('.defect-entry');
        let hasError = false;
        let defectIndex = 0;

        const formData = new FormData();
        formData.append('inspectionId', currentInspectionId);
        formData.append('inspectionOrderNo', orderNo);
        formData.append('complianceStatus', complianceStatus);

        defectEntries.forEach(entry => {
          const details    = entry.querySelector('.defect-details').value.trim();
          const gracePeriod = entry.querySelector('.defect-grace-period').value;
          const fileInput  = entry.querySelector('.defect-evidence');



          formData.append(`defect_details_${defectIndex}`, details);
          formData.append(`defect_grace_${defectIndex}`, gracePeriod);
          if (fileInput && fileInput.files.length > 0) {
            formData.append(`defect_evidence_${defectIndex}`, fileInput.files[0]);
          }
          defectIndex++;
        });

        formData.append('defectCount', defectIndex);



        if (defectIndex === 0) {
          showAlert('Please add at least one defect.', 'warning');
          return;
        }

        try {
          const response = await fetch('../../utility/submitInspectionReport.php', {
            method: 'POST',
            body: formData   // browser sets multipart/form-data + boundary automatically
          });

          const data = await response.json();

          if (data.success) {
            showAlert('Inspection report submitted successfully!', 'success');
            
            // Reset form
            document.getElementById("reportForm").reset();
            clearDefects();
            addDefectEntry();

            // Close modal
            bootstrap.Modal.getInstance(
              document.getElementById("inspectionModal")
            ).hide();

            // Reload inspections
            await loadInspections();
          } else {
            showAlert('Error: ' + data.message, 'danger');
          }
        } catch (error) {
          console.error('Error:', error);
          showAlert('Failed to submit report', 'danger');
        }
      });

      // View existing report button
      document.getElementById('viewExistingReport').addEventListener('click', async () => {
        if (currentInspectionId) {
          await loadExistingReport(currentInspectionId);
        }
      });

      // Initial load
      loadInspections();
    </script>
  </body>
</html>
