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
        <a href="../index.php" class="nav-link">
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
                    >Inspection Order No.
                    <span class="text-danger">*</span></label
                  >
                  <input
                    type="text"
                    class="form-control"
                    id="inspectionOrderNo"
                    required
                  />
                </div>
                <div class="mb-3">
                  <label for="defects" class="form-label"
                    >Defects/Deficiencies
                    <span class="text-danger">*</span></label
                  >
                  <textarea
                    class="form-control"
                    id="defects"
                    rows="4"
                    required
                    placeholder="List all defects and deficiencies found during inspection..."
                  ></textarea>
                </div>
                <div class="mb-3">
                  <label for="gracePeriod" class="form-label"
                    >Grace Period (in days)
                    <span class="text-danger">*</span></label
                  >
                  <input
                    type="number"
                    class="form-control"
                    id="gracePeriod"
                    required
                    min="1"
                  />
                </div>
                <button type="submit" class="btn btn-bfp-primary w-100">
                  <i class="fas fa-paper-plane"></i> Submit Report
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
      // Sample data for inspections
      let inspections = [
        {
          id: 1,
          viewed: false,
          businessName: "ABC Shopping Mall",
          businessType: "Commercial",
          inspectionDate: "2025-10-25",
          inspectionTime: "09:00 AM",
          address: "123 Main Street, Masbate City, Masbate",
          regNo: "BFP-2025-001",
          latitude: 12.3685,
          longitude: 123.6208,
        },
        {
          id: 2,
          viewed: false,
          businessName: "XYZ Hotel & Resort",
          businessType: "Hospitality",
          inspectionDate: "2025-10-26",
          inspectionTime: "02:00 PM",
          address: "456 Beach Road, Masbate City, Masbate",
          regNo: "BFP-2025-002",
          latitude: 12.3705,
          longitude: 123.6228,
        },
        {
          id: 3,
          viewed: true,
          businessName: "DEF Manufacturing Corp",
          businessType: "Industrial",
          inspectionDate: "2025-10-23",
          inspectionTime: "10:30 AM",
          address: "789 Industrial Park, Masbate City, Masbate",
          regNo: "BFP-2025-003",
          latitude: 12.3665,
          longitude: 123.6188,
        },
        {
          id: 4,
          viewed: false,
          businessName: "GHI Restaurant",
          businessType: "Food Service",
          inspectionDate: "2025-10-27",
          inspectionTime: "11:00 AM",
          address: "321 Food Street, Masbate City, Masbate",
          regNo: "BFP-2025-004",
          latitude: 12.3695,
          longitude: 123.6218,
        },
      ];

      let map;
      let marker;
      let currentFilter = "all";
      let currentInspectionId = null;

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

      function viewInspection(id) {
        const inspection = inspections.find((i) => i.id === id);
        if (!inspection) return;

        currentInspectionId = id;

        // Mark as viewed
        inspection.viewed = true;
        renderTable();

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
      document.getElementById("reportForm").addEventListener("submit", (e) => {
        e.preventDefault();

        const orderNo = document.getElementById("inspectionOrderNo").value;
        const defects = document.getElementById("defects").value;
        const gracePeriod = document.getElementById("gracePeriod").value;

        // Here you would typically send this data to your backend
        alert(
          `Report Submitted!\n\nInspection Order No: ${orderNo}\nDefects: ${defects}\nGrace Period: ${gracePeriod} days`
        );

        // Reset form
        document.getElementById("reportForm").reset();

        // Close modal
        bootstrap.Modal.getInstance(
          document.getElementById("inspectionModal")
        ).hide();
      });

      // Initial render
      renderTable();
    </script>
  </body>
</html>
