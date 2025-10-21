<?php
  include '../../utility/db.php';

  $stmt = $conn->prepare("SELECT * FROM establishment");
  $stmt->execute();
  $result = $stmt->get_result();
  
  $row = [];
  while($r = $result->fetch_assoc()){
    $row[] = $r;
  }

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BFP SiteProfiler - Schedule Inspections</title>
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
      rel="stylesheet"
    />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" rel="stylesheet">

    <link
      rel="stylesheet"
      href="../../assets/styles/layout/admin-schedule-inspections.css"
    />

    <link rel="stylesheet" href="../../assets/styles/components/sidebar.css" />
    <link rel="stylesheet" href="../../assets/styles/components/header.css" />
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
          <a href="./establishments.php" class="nav-link">
            <i class="fas fa-building"></i>
            Establishments
          </a>
        </div>
        <div class="nav-item">
          <a href="./schedule-inspections.php" class="nav-link active">
            <i class="fas fa-calendar-check"></i>
            Schedule Inspections
          </a>
        </div>
        <div class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-map-marker-alt"></i>
            GIS Map
          </a>
        </div>
        <div class="nav-item">
          <a href="./reports.php" class="nav-link">
            <i class="fas fa-file-alt"></i>
            Reports
          </a>
        </div>
        <div class="nav-item">
          <a href="./user-management.php" class="nav-link">
            <i class="fas fa-users"></i>
            User Management
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
      <!-- Top Header -->
      <div class="top-header">
        <div class="d-flex align-items-center">
          <button class="mobile-menu-toggle me-3" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
          </button>
          <h4 class="mb-0">Schedule Inspections</h4>
        </div>
        <div class="admin-info">
          <i class="fas fa-bell text-danger"></i>
          <div class="admin-avatar">AD</div>
          <span class="ms-2">Admin User</span>
        </div>
      </div>

      <!-- Content -->
      <div class="content-wrapper">
        <h1 class="page-title">Schedule Inspections</h1>
        <p class="page-subtitle">
          Manage and assign fire safety inspections to inspectors
        </p>

        <div class="row mb-4">
          <!-- Left Column -->
          <div class="col-lg-8">
            <!-- New Inspection Schedule Form -->
            <div class="inspection-form-card">
              <h4>New Inspection Schedule</h4>
              <form id="newInspectionForm">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="establishment" class="form-label"
                      >Establishment</label
                    >
                    <select class="form-select" id="establishment" required>
                      <option disabled selected>-- select establishment --</option>
                     <?php foreach($row as $r){ ?>
                      <option value="<?php echo $r['id'] ?>"> <?php echo $r['name'] ?></option>
                    <?php } ?>
                    
                  </select>

                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="inspectionType" class="form-label"
                      >Inspection Type</label
                    >
                    <select class="form-select" id="inspectionType" required>
                      <option value="">Select Inspection Type</option>
                      <option value="routine">Routine</option>
                      <option value="follow-up">Follow-up</option>
                      <option value="initial">Initial</option>
                      <option value="complaint">Complaint Investigation</option>
                    </select>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="inspectionDate" class="form-label"
                      >Inspection Date</label
                    >
                    <input
                      type="date"
                      class="form-control"
                      id="inspectionDate"
                      required
                    />
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="timeSlot" class="form-label">Time Slot</label>
                    <select class="form-select" id="timeSlot" required>
                      <option value="">Select Time Slot</option>
                      <option value="morning">
                        Morning (8:00 AM - 12:00 PM)
                      </option>
                      <option value="afternoon">
                        Afternoon (1:00 PM - 5:00 PM)
                      </option>
                      <option value="full-day">
                        Full Day (8:00 AM - 5:00 PM)
                      </option>
                    </select>
                  </div>
                </div>
                <div class="mb-3">
                  <label for="priorityLevel" class="form-label"
                    >Priority Level</label
                  >
                  <select class="form-select" id="priorityLevel" required>
                    <option value="">Select Priority</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                  </select>
                </div>
                <div class="notes-section">
                  <label for="inspectionNotes" class="form-label">Notes</label>
                  <textarea
                    class="form-control"
                    id="inspectionNotes"
                    rows="3"
                    placeholder="Additional notes for the inspection..."
                  ></textarea>
                </div>

                <!-- Assigned Inspector Display -->
                <div
                  class="assigned-inspector"
                  id="assignedInspectorDisplay"
                  style="display: none"
                >
                  <h6>Assigned Inspector:</h6>
                  <div id="assignedInspectorInfo"></div>
                </div>

                <div class="d-flex gap-2 mt-3">
                  <button
                    type="button"
                    class="btn btn-bfp-primary"
                    id="scheduleInspectionBtn"
                  >
                    <i class="fas fa-calendar-plus"></i> Schedule Inspection
                  </button>
                  <button
                    type="button"
                    class="btn btn-bfp-secondary"
                    id="resetFormBtn"
                  >
                    <i class="fas fa-undo"></i> Reset Form
                  </button>
                </div>
              </form>
            </div>

            <!-- Inspector Assignment Map -->
          </div>

          <!-- Right Column -->
          <div class="col-lg-4">
            <!-- Available Inspectors -->
            <div class="right-panel">
              <div
                class="d-flex justify-content-between align-items-center mb-3"
              >
                <div class="inspector-header">
                  <h5>Available Inspectors</h5>
                  <div class="search-cont">
                    <i class="fas fa-search"></i>
                    <input type="search" id="inspectorSearch" placeholder="Search...">
                  </div>
                </div>
              </div>
              <div class="inspector-list" id="inspectorList">
                <!-- Inspector cards populated by JavaScript -->
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="map-container">
            <div class="map-header">
              <i class="fas fa-map-marker-alt"></i> Inspector Assignment
            </div>
            <div id="inspectionMap"></div>
          </div>

          <!-- Upcoming Inspections Table -->
          <div class="upcoming-inspections">
            <div class="upcoming-inspections-header">
              <i class="fas fa-calendar-alt"></i> Upcoming Inspections
            </div>
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th>Establishment</th>
                    <th>Type</th>
                    <th>Date & Time</th>
                    <th>Inspector</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="upcomingInspectionsBody">
                  <!-- Table content populated by JavaScript -->
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Edit Inspection Modal -->
    <div class="modal fade" id="editInspectionModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Inspection</h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <form id="editInspectionForm">
              <input type="hidden" id="editInspectionId" />
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="editEstablishment" class="form-label"
                    >Establishment</label
                  >
                  <input class="form-control" id="editEstablishment" required />
                </div>
                <div class="col-md-6 mb-3">
                  <label for="editInspectionType" class="form-label"
                    >Inspection Type</label
                  >
                  <select class="form-select" id="editInspectionType" required>
                    <option value="">Select Inspection Type</option>
                    <option value="routine">Routine</option>
                    <option value="follow-up">Follow-up</option>
                    <option value="initial">Initial</option>
                    <option value="complaint">Complaint Investigation</option>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="editInspectionDate" class="form-label"
                    >Inspection Date</label
                  >
                  <input
                    type="date"
                    class="form-control"
                    id="editInspectionDate"
                    required
                  />
                </div>
                <div class="col-md-6 mb-3">
                  <label for="editInspector" class="form-label"
                    >Inspector</label
                  >
                  <select class="form-select" id="editInspector" required>
                    <option value="">Select Inspector</option>
                    <option value="juan-cruz">Juan Dela Cruz</option>
                    <option value="maria-santos">Maria Santos</option>
                    <option value="roberto-pasquino">Roberto Pasquino</option>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="editPriority" class="form-label">Priority</label>
                  <select class="form-select" id="editPriority" required>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="editStatus" class="form-label">Status</label>
                  <select class="form-select" id="editStatus" required>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="completed">Completed</option>
                  </select>
                </div>
              </div>
              <div class="mb-3">
                <label for="editNotes" class="form-label">Notes</label>
                <textarea
                  class="form-control"
                  id="editNotes"
                  rows="3"
                ></textarea>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Cancel
            </button>
            <button
              type="button"
              class="btn btn-bfp-primary"
              id="updateInspectionBtn"
            >
              Update Inspection
            </button>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    
    <script src="../../assets/scripts/admin-schedule-inspections.js"></script>
    <script src="../../assets/scripts/components/sidebar.js"></script>
  </body>
</html>
