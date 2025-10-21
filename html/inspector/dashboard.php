<?php
include '../../utility/inspectorDasboard.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>BFP Inspector Dashboard</title>
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css"
    rel="stylesheet" />
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    rel="stylesheet" />
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css"
    rel="stylesheet" />

  <link
    rel="stylesheet"
    href="../../assets/styles/layout/inspector-dashboard.css" />
  <link rel="stylesheet" href="../../assets/styles/components/sidebar.css" />
</head>

<body>
  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="logo-section">
      <div class="logo">
        <i
          class="fas fa-shield-alt"
          style="color: var(--bfp-red); font-size: 24px"></i>
      </div>
      <h5 class="mb-0">BFP SiteProfiler</h5>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-item">
        <a href="#" class="nav-link active">
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
        <a href="./report-findings.php" class="nav-link">
          <i class="fas fa-calendar-check"></i>
          Report Findings
        </a>
      </div>
      <div class="nav-item">
        <a href="./gis-map.php" class="nav-link">
          <i class="fas fa-map-marker-alt"></i>
          GIS Map
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

  <!-- Main Content -->
  <div class="main-content">
    <!-- Header -->
    <div class="header">
      <h2 class="text-dark mb-1">Inspector Dashboard</h2>
      <p class="text-muted mb-0">
        Welcome back, Inspector! Here's your inspection activity today.
      </p>
    </div>

    <!-- Stats Cards -->
    <div class="row">
      <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
        <div
          class="stats-card"
          data-bs-toggle="modal"
          data-bs-target="#scheduledModal">
          <div class="d-flex align-items-center">
            <div class="ms-3">
              <div class="stats-number text-primary"><?php echo $data['inspection'] ?? '0' ?></div>
              <div class="stats-label">Scheduled Inspections</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
        <div
          class="stats-card"
          data-bs-toggle="modal"
          data-bs-target="#ongoingModal">
          <div class="d-flex align-items-center">
            <div class="ms-3">
              <div class="stats-number text-secondary"><?php echo $data['ongoing'] ?? '0' ?></div>
              <div class="stats-label">Ongoing Now</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
        <div
          class="stats-card"
          data-bs-toggle="modal"
          data-bs-target="#completedModal">
          <div class="d-flex align-items-center">
            <div class="ms-3">
              <div class="stats-number text-success"><?php echo $data['completed'] ?? '0' ?></div>
              <div class="stats-label">Completed</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
        <div
          class="stats-card"
          data-bs-toggle="modal"
          data-bs-target="#pendingModal">
          <div class="d-flex align-items-center">
            <div class="ms-3">
              <div class="stats-number" style="color: #b8860b"><?php echo $data['pending'] ?? '0' ?></div>
              <div class="stats-label">Reports Pending</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- System Notice -->
    <div class="system-notice">
      <div class="system-notice-content">
        <strong>System Notice:</strong> Please submit your pending reports
        before 5:00 PM. Field inspections scheduled for today must be
        completed and synced.
      </div>
    </div>

    <!-- GIS Map -->
    <div class="map-container">
      <h4 class="mb-3">
        <i class="fas fa-map-marker-alt text-danger me-2"></i>
        GIS Map: Inspection Sites
      </h4>
      <div id="map"></div>
    </div>
  </div>

  <!-- Scheduled Inspections Modal -->
  <div class="modal fade" id="scheduledModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fas fa-calendar-alt me-2"></i>
            Scheduled Inspections (6)
          </h5>
          <button
            type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Site ID</th>
                  <th>Location</th>
                  <th>Date & Time</th>
                  <th>Type</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>BFP-001</td>
                  <td>SM Mall Marikina</td>
                  <td>Sept 7, 2025 - 9:00 AM</td>
                  <td>Fire Safety</td>
                  <td>
                    <span class="badge badge-scheduled">Scheduled</span>
                  </td>
                </tr>
                <tr>
                  <td>BFP-002</td>
                  <td>Robinsons Galleria</td>
                  <td>Sept 7, 2025 - 11:00 AM</td>
                  <td>Annual Inspection</td>
                  <td>
                    <span class="badge badge-scheduled">Scheduled</span>
                  </td>
                </tr>
                <tr>
                  <td>BFP-003</td>
                  <td>Ayala Malls Capitol Central</td>
                  <td>Sept 7, 2025 - 2:00 PM</td>
                  <td>Fire Safety</td>
                  <td>
                    <span class="badge badge-scheduled">Scheduled</span>
                  </td>
                </tr>
                <tr>
                  <td>BFP-004</td>
                  <td>Gateway Mall</td>
                  <td>Sept 7, 2025 - 3:30 PM</td>
                  <td>Follow-up</td>
                  <td>
                    <span class="badge badge-scheduled">Scheduled</span>
                  </td>
                </tr>
                <tr>
                  <td>BFP-005</td>
                  <td>Eastwood Mall</td>
                  <td>Sept 8, 2025 - 9:00 AM</td>
                  <td>Fire Safety</td>
                  <td>
                    <span class="badge badge-scheduled">Scheduled</span>
                  </td>
                </tr>
                <tr>
                  <td>BFP-006</td>
                  <td>Trinoma Mall</td>
                  <td>Sept 8, 2025 - 2:00 PM</td>
                  <td>Annual Inspection</td>
                  <td>
                    <span class="badge badge-scheduled">Scheduled</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Ongoing Inspections Modal -->
  <div class="modal fade" id="ongoingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fas fa-clock me-2"></i>
            Ongoing Inspections (2)
          </h5>
          <button
            type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Site ID</th>
                  <th>Location</th>
                  <th>Started</th>
                  <th>Type</th>
                  <th>Progress</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>BFP-007</td>
                  <td>UP Town Center</td>
                  <td>8:30 AM</td>
                  <td>Fire Safety</td>
                  <td>
                    <div class="progress" style="height: 20px">
                      <div
                        class="progress-bar"
                        role="progressbar"
                        style="width: 65%">
                        65%
                      </div>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td>BFP-008</td>
                  <td>Glorietta Mall</td>
                  <td>10:15 AM</td>
                  <td>Follow-up</td>
                  <td>
                    <div class="progress" style="height: 20px">
                      <div
                        class="progress-bar"
                        role="progressbar"
                        style="width: 40%">
                        40%
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Completed Inspections Modal -->
  <div class="modal fade" id="completedModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fas fa-check-circle me-2"></i>
            Completed Inspections (4)
          </h5>
          <button
            type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Site ID</th>
                  <th>Location</th>
                  <th>Completed</th>
                  <th>Type</th>
                  <th>Result</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>BFP-009</td>
                  <td>Greenhills Shopping Center</td>
                  <td>Sept 6, 2025 - 3:00 PM</td>
                  <td>Fire Safety</td>
                  <td><span class="badge bg-success">Passed</span></td>
                </tr>
                <tr>
                  <td>BFP-010</td>
                  <td>Powerplant Mall</td>
                  <td>Sept 6, 2025 - 11:30 AM</td>
                  <td>Annual Inspection</td>
                  <td>
                    <span class="badge bg-warning text-dark">Minor Issues</span>
                  </td>
                </tr>
                <tr>
                  <td>BFP-011</td>
                  <td>Mall of Asia</td>
                  <td>Sept 5, 2025 - 4:15 PM</td>
                  <td>Fire Safety</td>
                  <td><span class="badge bg-success">Passed</span></td>
                </tr>
                <tr>
                  <td>BFP-012</td>
                  <td>Megamall</td>
                  <td>Sept 5, 2025 - 1:20 PM</td>
                  <td>Follow-up</td>
                  <td><span class="badge bg-success">Passed</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Pending Reports Modal -->
  <div class="modal fade" id="pendingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Reports Pending (2)
          </h5>
          <button
            type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Site ID</th>
                  <th>Location</th>
                  <th>Inspection Date</th>
                  <th>Type</th>
                  <th>Days Pending</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>BFP-013</td>
                  <td>Landmark Makati</td>
                  <td>Sept 4, 2025</td>
                  <td>Fire Safety</td>
                  <td>
                    <span class="badge bg-warning text-dark">3 days</span>
                  </td>
                </tr>
                <tr>
                  <td>BFP-014</td>
                  <td>Century City Mall</td>
                  <td>Sept 3, 2025</td>
                  <td>Annual Inspection</td>
                  <td><span class="badge bg-danger">4 days</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
  <script>
    // Initialize Map
    var map = L.map("map").setView([13.5833, 124.2374], 11);


    // Add OpenStreetMap tiles
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution: "© OpenStreetMap contributors",
    }).addTo(map);

    // Sample inspection sites in Metro Manila
    var inspectionSites = [{
        lat: 13.5843,
        lng: 124.2397,
        name: "Virac Town Center",
        status: "scheduled",
      },
      {
        lat: 13.5967,
        lng: 124.2303,
        name: "Catanduanes State University",
        status: "scheduled",
      },
      {
        lat: 13.5951,
        lng: 124.2458,
        name: "Virac Cathedral",
        status: "scheduled",
      },
      {
        lat: 13.6537,
        lng: 124.3731,
        name: "Binurong Point, Baras",
        status: "ongoing",
      },
      {
        lat: 13.6444,
        lng: 124.3812,
        name: "Puraran Beach, Baras",
        status: "ongoing",
      },
      {
        lat: 13.6123,
        lng: 124.3288,
        name: "Bato Church",
        status: "completed",
      },
      {
        lat: 13.5767,
        lng: 124.2884,
        name: "Maribina Falls, Bato",
        status: "completed",
      },
      {
        lat: 13.6914,
        lng: 124.3837,
        name: "Balacay Point, Baras",
        status: "completed",
      },
      {
        lat: 13.7215,
        lng: 124.3089,
        name: "Padis Point View Deck, Gigmoto",
        status: "completed",
      },
      {
        lat: 13.7128,
        lng: 124.1071,
        name: "PAGASA Weather Radar Station, San Andres",
        status: "pending",
      },
      {
        lat: 13.8534,
        lng: 124.3097,
        name: "Pandan Beach Resort, Pandan",
        status: "pending",
      }
    ];

    // Add markers to map
    inspectionSites.forEach(function(site) {
      var iconColor = "red";
      var iconName = "exclamation";

      switch (site.status) {
        case "scheduled":
          iconColor = "blue";
          iconName = "calendar";
          break;
        case "ongoing":
          iconColor = "orange";
          iconName = "clock";
          break;
        case "completed":
          iconColor = "green";
          iconName = "check";
          break;
        case "pending":
          iconColor = "red";
          iconName = "exclamation-triangle";
          break;
      }

      var marker = L.marker([site.lat, site.lng]).addTo(map);
      marker.bindPopup(`
                <div class="text-center">
                    <h6>${site.name}</h6>
                    <p class="mb-1">Status: <span class="badge badge-${
                      site.status
                    }">${site.status.charAt(0).toUpperCase() + site.status.slice(1)}</span></p>
                    <button class="btn btn-sm btn-bfp mt-2">View Details</button>
                </div>
            `);
    });

    // Toggle sidebar for mobile
    function toggleSidebar() {
      document.getElementById("sidebar").classList.toggle("show");
    }

    // Add click handlers for stat cards
    document.addEventListener("DOMContentLoaded", function() {
      // Update time every second
      setInterval(function() {
        var now = new Date();
        var timeString = now.toLocaleTimeString();
        // You can display this somewhere if needed
      }, 1000);

      // Add animation to cards
      const cards = document.querySelectorAll(".stats-card");
      cards.forEach((card) => {
        card.addEventListener("mouseenter", function() {
          this.style.transform = "translateY(-5px)";
        });

        card.addEventListener("mouseleave", function() {
          this.style.transform = "translateY(0)";
        });
      });
    });
  </script>
</body>

</html>