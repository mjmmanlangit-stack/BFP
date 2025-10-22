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
        <a href="../index.php" class="nav-link">
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
              data-filter="non-compliant"
            >
              <i class="fas fa-exclamation-circle me-1"></i>Non-Compliant
            </button>
          </div>
        </div>
  
        <div class="table-container">
          <div class="table-responsive">
            <table class="table table-hover" id="complianceTable">
              <thead>
                <tr>
                  <th>Inspection Date</th>
                  <th>Business Name</th>
                  <th>BFP Registration No.</th>
                  <th>Business Type</th>
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
                  <span class="info-label">Inspection Date:</span>
                  <span id="modalInspectionDate"></span>
                </p>
                <p>
                  <span class="info-label">Business Name:</span>
                  <span id="modalBusinessName"></span>
                </p>
                <p>
                  <span class="info-label">BFP Registration No.:</span>
                  <span id="modalRegNo"></span>
                </p>
              </div>
              <div class="col-md-6">
                <p>
                  <span class="info-label">Business Type:</span>
                  <span id="modalBusinessType"></span>
                </p>
                <p>
                  <span class="info-label">Grace Period:</span>
                  <span id="modalGracePeriod"></span>
                </p>
                <p>
                  <span class="info-label">Days Left:</span>
                  <span id="modalDaysLeft"></span>
                </p>
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
              <p class="info-label">Defects/Deficiencies:</p>
              <div class="defects-box" id="modalDefects"></div>
            </div>

            <div class="mb-3">
              <label for="inspectorNotes" class="form-label info-label"
                >Inspector Notes/Comments:</label
              >
              <textarea
                class="form-control"
                id="inspectorNotes"
                rows="4"
                placeholder="Enter your notes and comments here..."
              ></textarea>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
              <button
                type="button"
                class="btn btn-compliant"
                onclick="markAsCompliant()"
              >
                <i class="fas fa-check me-2"></i>Mark as Compliant
              </button>
              <button
                type="button"
                class="btn btn-non-compliant"
                onclick="markAsNonCompliant()"
              >
                <i class="fas fa-times me-2"></i>Mark as Non-Compliant
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

      // Sample data
      const complianceData = [
        {
          id: 1,
          inspectionDate: "2025-10-15",
          businessName: "Grand Hotel Manila",
          regNo: "BFP-2025-001",
          businessType: "Hotel",
          address: "123 Roxas Boulevard, Manila City",
          coordinates: { lat: 14.5995, lng: 120.9842 },
          defects:
            "Missing fire extinguisher in 3rd floor, Emergency exit blocked, Fire alarm system not functional",
          gracePeriod: "2025-10-23",
          status: "pending",
        },
        {
          id: 2,
          inspectionDate: "2025-10-18",
          businessName: "Tech Mall Center",
          regNo: "BFP-2025-002",
          businessType: "Shopping Mall",
          address: "456 EDSA, Quezon City",
          coordinates: { lat: 14.6488, lng: 121.0509 },
          defects:
            "Insufficient fire exits, Sprinkler system needs maintenance",
          gracePeriod: "2025-10-25",
          status: "pending",
        },
        {
          id: 3,
          inspectionDate: "2025-10-12",
          businessName: "Santos Restaurant",
          regNo: "BFP-2025-003",
          businessType: "Restaurant",
          address: "789 Tomas Morato, Quezon City",
          coordinates: { lat: 14.6354, lng: 121.0321 },
          defects: "Kitchen fire suppression system expired",
          gracePeriod: "2025-10-22",
          status: "pending",
        },
        {
          id: 4,
          inspectionDate: "2025-10-10",
          businessName: "ABC Manufacturing",
          regNo: "BFP-2025-004",
          businessType: "Factory",
          address: "321 Industrial Road, Caloocan City",
          coordinates: { lat: 14.6507, lng: 120.9838 },
          defects: "Fire safety training not conducted, Old fire extinguishers",
          gracePeriod: "2025-10-20",
          status: "compliant",
        },
        {
          id: 5,
          inspectionDate: "2025-10-08",
          businessName: "City Hospital",
          regNo: "BFP-2025-005",
          businessType: "Hospital",
          address: "555 España Boulevard, Manila",
          coordinates: { lat: 14.6091, lng: 120.9896 },
          defects: "Emergency lighting insufficient in basement area",
          gracePeriod: "2025-10-18",
          status: "non-compliant",
        },
      ];

      function calculateDaysLeft(gracePeriod) {
        const today = new Date("2025-10-22");
        const graceDate = new Date(gracePeriod);
        const diffTime = graceDate - today;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        return diffDays;
      }

      function getRowClass(daysLeft) {
        if (daysLeft === 0) return "highlight-critical";
        if (daysLeft === 1) return "highlight-warning";
        return "";
      }

      function renderTable(filter = "all") {
        const tbody = document.getElementById("tableBody");
        tbody.innerHTML = "";

        let filteredData = complianceData;
        if (filter !== "all") {
          filteredData = complianceData.filter(
            (item) => item.status === filter
          );
        }

        filteredData.forEach((item) => {
          const daysLeft = calculateDaysLeft(item.gracePeriod);
          const rowClass = getRowClass(daysLeft);
          const statusBadge =
            item.status === "compliant"
              ? "badge-compliant"
              : item.status === "non-compliant"
              ? "badge-non-compliant"
              : "bg-warning";
          const statusText =
            item.status === "compliant"
              ? "Compliant"
              : item.status === "non-compliant"
              ? "Non-Compliant"
              : "Pending";

          const row = `
                    <tr class="${rowClass}">
                        <td>${item.inspectionDate}</td>
                        <td>${item.businessName}</td>
                        <td>${item.regNo}</td>
                        <td>${item.businessType}</td>
                        <td>${item.gracePeriod}</td>
                        <td><strong>${daysLeft} days</strong></td>
                        <td><span class="badge ${statusBadge}">${statusText}</span></td>
                        <td>
                            <button class="btn btn-view btn-sm" onclick="viewDetails(${item.id})">
                                <i class="fas fa-eye me-1"></i>View
                            </button>
                        </td>
                    </tr>
                `;
          tbody.innerHTML += row;
        });
      }

      function viewDetails(id) {
        currentRecord = complianceData.find((item) => item.id === id);
        if (!currentRecord) return;

        const daysLeft = calculateDaysLeft(currentRecord.gracePeriod);

        document.getElementById("modalInspectionDate").textContent =
          currentRecord.inspectionDate;
        document.getElementById("modalBusinessName").textContent =
          currentRecord.businessName;
        document.getElementById("modalRegNo").textContent = currentRecord.regNo;
        document.getElementById("modalBusinessType").textContent =
          currentRecord.businessType;
        document.getElementById("modalGracePeriod").textContent =
          currentRecord.gracePeriod;
        document.getElementById(
          "modalDaysLeft"
        ).textContent = `${daysLeft} days`;
        document.getElementById("modalAddress").textContent =
          currentRecord.address;
        document.getElementById(
          "modalCoordinates"
        ).textContent = `${currentRecord.coordinates.lat}, ${currentRecord.coordinates.lng}`;
        document.getElementById("modalDefects").textContent =
          currentRecord.defects;
        document.getElementById("inspectorNotes").value = "";

        const modal = new bootstrap.Modal(
          document.getElementById("detailsModal")
        );
        modal.show();

        setTimeout(() => {
          initMap(currentRecord.coordinates.lat, currentRecord.coordinates.lng);
        }, 200);
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
      }

      function markAsCompliant() {
        const notes = document.getElementById("inspectorNotes").value;
        if (!notes.trim()) {
          alert("Please enter notes/comments before marking as compliant.");
          return;
        }

        if (currentRecord) {
          currentRecord.status = "compliant";
          alert(
            `${currentRecord.businessName} has been marked as COMPLIANT.\n\nNotes: ${notes}`
          );
          renderTable();
          bootstrap.Modal.getInstance(
            document.getElementById("detailsModal")
          ).hide();
        }
      }

      function markAsNonCompliant() {
        const notes = document.getElementById("inspectorNotes").value;
        if (!notes.trim()) {
          alert("Please enter notes/comments before marking as non-compliant.");
          return;
        }

        if (currentRecord) {
          currentRecord.status = "non-compliant";
          alert(
            `${currentRecord.businessName} has been marked as NON-COMPLIANT.\n\nNotes: ${notes}`
          );
          renderTable();
          bootstrap.Modal.getInstance(
            document.getElementById("detailsModal")
          ).hide();
        }
      }

      // Filter buttons
      document.querySelectorAll(".filter-btn").forEach((btn) => {
        btn.addEventListener("click", function () {
          document
            .querySelectorAll(".filter-btn")
            .forEach((b) => b.classList.remove("active"));
          this.classList.add("active");
          const filter = this.getAttribute("data-filter");
          renderTable(filter);
        });
      });

      // Initial render
      renderTable();
    </script>
  </body>
</html>
