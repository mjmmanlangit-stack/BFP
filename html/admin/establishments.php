<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BFP SiteProfiler - Establishments Directory</title>
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
      href="../../assets/styles/layout/admin-establishments.css"
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
          <h4 class="mb-0">Establishments</h4>
        </div>
        <div class="admin-info">
          <i class="fas fa-bell text-danger"></i>
          <div class="admin-avatar">AD</div>
          <span class="ms-2">Admin User</span>
        </div>
      </div>

      <!-- Content -->
      <div class="content-wrapper">
        <h1 class="page-title">Registered Establishments</h1>
        <p class="page-subtitle">
          View and manage all registered establishments in the system
        </p>

        <!-- Controls Bar -->
        <div class="controls-bar">
          <div class="controls-left">
            <h5>All Establishments (<span id="totalCount">142</span>)</h5>
          </div>
          <!-- <div class="controls-right">
            <button class="btn btn-bfp-secondary" id="filterBtn">
              <i class="fas fa-filter"></i> Filter
            </button> -->
            <button class="btn btn-bfp-secondary" id="exportBtn">
              <i class="fas fa-download"></i> Export
            </button>
            <!-- <button
              class="btn btn-bfp-success"
              id="addNewBtn"
              data-bs-toggle="modal"
              data-bs-target="#addEstablishmentModal"
            >
              <i class="fas fa-plus"></i> Add New
            </button> -->
          </div>
        </div>

        <!-- Search Controls -->
        <div class="search-controls">
          <!-- <input
            type="text"
            class="form-control"
            id="searchGeneral"
            placeholder="Search establishments..."
            style="max-width: 300px"
          /> -->
          <input
            type="text"
            class="form-control"
            id="searchSpecific"
            placeholder="Search establishments..."
            style="max-width: 300px"
          />
        </div>

        <!-- Table Container -->
        <div class="table-container">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th class="sortable" data-sort="id">
                    ID <i class="fas fa-sort sort-icon"></i>
                  </th>
                  <th class="sortable" data-sort="establishment">
                    Establishment <i class="fas fa-sort sort-icon"></i>
                  </th>
                  <th class="sortable" data-sort="type">
                    Type <i class="fas fa-sort sort-icon"></i>
                  </th>
                  <th class="sortable" data-sort="location">
                    Location <i class="fas fa-sort sort-icon"></i>
                  </th>
                  <th class="sortable" data-sort="owner">
                    Owner <i class="fas fa-sort sort-icon"></i>
                  </th>
                  <th class="sortable" data-sort="contact">
                    Contact <i class="fas fa-sort sort-icon"></i>
                  </th>
                  <th class="sortable" data-sort="status">
                    Status <i class="fas fa-sort sort-icon"></i>
                  </th>
                  <th class="sortable" data-sort="inspection">
                    Last Inspection <i class="fas fa-sort sort-icon"></i>
                  </th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody id="establishmentsTableBody">
                <!-- Table rows will be populated by JavaScript -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Add New Establishment Modal -->
    <!-- <div class="modal fade" id="addEstablishmentModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Add New Establishment</h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <form id="addEstablishmentForm">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="addEstablishmentName" class="form-label"
                    >Establishment Name *</label
                  >
                  <input
                    type="text"
                    class="form-control"
                    id="addEstablishmentName"
                    required
                  />
                </div>
                <div class="col-md-6 mb-3">
                  <label for="addEstablishmentType" class="form-label"
                    >Type *</label
                  >
                  <select
                    class="form-select"
                    id="addEstablishmentType"
                    required
                  >
                    <option value="">Select Type</option>
                    <option value="Educational">Educational</option>
                    <option value="Public Market">Public Market</option>
                    <option value="Commercial">Commercial</option>
                    <option value="Hospitality">Hospitality</option>
                    <option value="Industrial">Industrial</option>
                    <option value="Residential">Residential</option>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="addLocation" class="form-label">Location *</label>
                  <input
                    type="text"
                    class="form-control"
                    id="addLocation"
                    required
                  />
                </div>
                <div class="col-md-6 mb-3">
                  <label for="addOwner" class="form-label">Owner *</label>
                  <input
                    type="text"
                    class="form-control"
                    id="addOwner"
                    required
                  />
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="addContact" class="form-label"
                    >Contact Number *</label
                  >
                  <input
                    type="tel"
                    class="form-control"
                    id="addContact"
                    required
                  />
                </div>
                <div class="col-md-6 mb-3">
                  <label for="addStatus" class="form-label">Status</label>
                  <select class="form-select" id="addStatus">
                    <option value="Pending Review">Pending Review</option>
                    <option value="Compliant">Compliant</option>
                    <option value="Non-Compliant">Non-Compliant</option>
                  </select>
                </div>
              </div>
              <div class="mb-3">
                <label for="addNotes" class="form-label"
                  >Additional Notes</label
                >
                <textarea
                  class="form-control"
                  id="addNotes"
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
              id="saveEstablishmentBtn"
            >
              Save Establishment
            </button>
          </div>
        </div>
      </div>
    </div> -->

    <!-- View Establishment Modal -->
    <div class="modal fade" id="viewEstablishmentModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Establishment Details</h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body" id="viewEstablishmentContent">
            <!-- Content populated by JavaScript -->
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Establishment Modal -->
    <!-- <div class="modal fade" id="editEstablishmentModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Establishment</h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <form id="editEstablishmentForm">
              <input type="hidden" id="editEstablishmentId" />
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="editEstablishmentName" class="form-label"
                    >Establishment Name *</label
                  >
                  <input
                    type="text"
                    class="form-control"
                    id="editEstablishmentName"
                    required
                  />
                </div>
                <div class="col-md-6 mb-3">
                  <label for="editEstablishmentType" class="form-label"
                    >Type *</label
                  >
                  <select
                    class="form-select"
                    id="editEstablishmentType"
                    required
                  >
                    <option value="">Select Type</option>
                    <option value="Educational">Educational</option>
                    <option value="Public Market">Public Market</option>
                    <option value="Commercial">Commercial</option>
                    <option value="Hospitality">Hospitality</option>
                    <option value="Industrial">Industrial</option>
                    <option value="Residential">Residential</option>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="editLocation" class="form-label"
                    >Location *</label
                  >
                  <input
                    type="text"
                    class="form-control"
                    id="editLocation"
                    required
                  />
                </div>
                <div class="col-md-6 mb-3">
                  <label for="editOwner" class="form-label">Owner *</label>
                  <input
                    type="text"
                    class="form-control"
                    id="editOwner"
                    required
                  />
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="editContact" class="form-label"
                    >Contact Number *</label
                  >
                  <input
                    type="tel"
                    class="form-control"
                    id="editContact"
                    required
                  />
                </div>
                <div class="col-md-6 mb-3">
                  <label for="editStatus" class="form-label">Status</label>
                  <select class="form-select" id="editStatus">
                    <option value="Pending Review">Pending Review</option>
                    <option value="Compliant">Compliant</option>
                    <option value="Non-Compliant">Non-Compliant</option>
                  </select>
                </div>
              </div>
              <div class="mb-3">
                <label for="editNotes" class="form-label"
                  >Additional Notes</label
                >
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
              id="updateEstablishmentBtn"
            >
              Update Establishment
            </button>
          </div>
        </div>
      </div>
    </div> -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <script src="../../assets/scripts/admin-establishments.js"></script>
    <script src="../../assets/scripts/components/sidebar.js"></script>
  </body>
</html>
