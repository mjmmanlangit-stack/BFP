<?php
 include '../../utility/checkingUser.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BFP Site Profiler - My Establishments</title>
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
      rel="stylesheet"
    />
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css"
      rel="stylesheet"
    />

    <link rel="stylesheet" href="../../assets/styles/components/sidebar.css" />
    <link rel="stylesheet" href="../../assets/styles/components/header.css" />

    <link
      rel="stylesheet"
      href="../../assets/styles/layout/user-my-establishments.css"
    />
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
          <a href="./my-establishments.php" class="nav-link">
            <i class="fas fa-building"></i>
            My Establishments
          </a>
        </div>
        <div class="nav-item">
          <a href="./certificates.php" class="nav-link active">
            <i class="fas fa-calendar-check"></i>
            Certificates
          </a>
        </div>
        <!-- <div class="nav-item">
          <a href="./gis-map.html" class="nav-link">
            <i class="fas fa-map-marker-alt"></i>
            Documents
          </a>
        </div> -->
      </nav>

      <div class="nav-item">
        <a href="../../utility/logout.php" class="nav-link">
          <i class="fas fa-sign-out-alt"></i>
          Logout
        </a>
      </div>
    </div>


    <div class="main-content">
      <!-- Top Header -->
      <div class="top-header">
        <div class="d-flex align-items-center">
          <button class="mobile-menu-toggle me-3" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
          </button>
          <h4 class="mb-0">My Establishments</h4>
        </div>
        <div class="admin-info">
          <div class="admin-avatar">JD</div>
          <span class="ms-2">Juan Dela Cruz</span>
        </div>
      </div>

      <div class="content-area">
        <!-- Filter Section -->
        <div class="filter-card">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">Status</label>
              <select class="form-select" id="statusFilter">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Business Type</label>
              <select class="form-select" id="typeFilter">
                <option value="">All Types</option>
                <option value="food">Food Service</option>
                <option value="retail">Retail</option>
                <option value="residential">Residential</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Registration Date</label>
              <input type="date" class="form-control" id="dateFilter" />
            </div>
            <div class="col-md-3 d-flex align-items-end">
              <button class="btn btn-bfp-red me-2" onclick="applyFilters()">
                <i class="fas fa-search"></i> Search
              </button>
              <button
                class="btn btn-outline-secondary"
                onclick="resetFilters()"
              >
                <i class="fas fa-undo"></i> Reset
              </button>
            </div>
          </div>
        </div>

        <!-- Establishments Table -->
        <div class="establishments-table">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 style="color: var(--bfp-red)">Registered Establishments</h4>
            <button
              class="btn btn-success"
              data-bs-toggle="modal"
              data-bs-target="#addEstablishmentModal"
            >
              <i class="fas fa-plus"></i> Add Establishment
            </button>
          </div>

          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Business Name</th>
                  <th>Type</th>
                  <th>BFP Reg. No.</th>
                  <th>Location</th>
                  <th>Status</th>
                  <th>Last Inspection</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="establishmentsTableBody">
                
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Establishment Modal -->
    <div class="modal fade" id="addEstablishmentModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div
            class="modal-header"
            style="background-color: var(--bfp-red); color: white"
          >
            <h5 class="modal-title">Add New Establishment</h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <form id="addEstablishmentForm">
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Business Name *</label>
                    <input
                      type="text"
                      class="form-control"
                      id="businessName"
                      required
                    />
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">BFP Registration No. *</label>
                    <input
                      type="text"
                      class="form-control"
                      id="bfpRegNo"
                      required
                    />
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Business Type *</label>
                    <input
                      type="text"
                      class="form-control"
                      id="businessType"
                      required
                    />
                  </div>
                </div>
              <div class="mb-3">
                <label class="form-label">Address *</label>
                <textarea
                  class="form-control"
                  id="address"
                  rows="3"
                  required
                ></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Coordinates</label>
                <div class="coordinates-display">
                  <p id="coordinatesDisplay" class="mb-0">
                    <strong>Longitude:</strong>
                    <span id="longitude">Not selected</span> |
                    <strong>Latitude:</strong>
                    <span id="latitude">Not selected</span>
                  </p>
                </div>
                <button
                  type="button"
                  class="btn btn-outline-primary"
                  onclick="openMapModal()"
                >
                  <i class="fas fa-map-marker-alt"></i> Select on Map
                </button>
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
              class="btn btn-bfp-red"
              onclick="saveEstablishment()"
            >
              <i class="fas fa-save"></i> Save Establishment
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- View Establishment Modal -->
    <div class="modal fade" id="viewEstablishmentModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div
            class="modal-header"
            style="background-color: var(--bfp-red); color: white"
          >
            <h5 class="modal-title">View Establishment Details</h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
                <h6>Business Information</h6>
                <p>
                  <strong>Name:</strong> <span id="viewBusinessName">-</span>
                </p>
                <p>
                  <strong>Type:</strong> <span id="viewBusinessType">-</span>
                </p>
                <p>
                  <strong>BFP Reg. No.:</strong>
                  <span id="viewBfpRegNo">-</span>
                </p>
                <p><strong>Status:</strong> <span id="viewStatus">-</span></p>
              </div>
              <div class="col-md-6">
                <h6>Location Details</h6>
                <p><strong>Address:</strong> <span id="viewAddress">-</span></p>
                <p>
                  <strong>Coordinates:</strong>
                  <span id="viewCoordinates">-</span>
                </p>
                <p>
                  <strong>Last Inspection:</strong>
                  <span id="viewLastInspection">-</span>
                </p>
              </div>
            </div>
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
    <div class="modal fade" id="editEstablishmentModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div
            class="modal-header"
            style="background-color: var(--bfp-gold); color: var(--bfp-dark)"
          >
            <h5 class="modal-title">Edit Establishment</h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <form id="editEstablishmentForm">
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Business Name *</label>
                    <input
                      type="text"
                      class="form-control"
                      id="editBusinessName"
                      required
                    />
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">BFP Registration No. *</label>
                    <input
                      type="text"
                      class="form-control"
                      id="editBfpRegNo"
                      required
                    />
                  </div>
                </div>
              </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Business Type. *</label>
                    <input
                      type="text"
                      class="form-control"
                      id="editBusinessType"
                      required
                    />
                  </div>
                </div>
              <div class="mb-3">
                <label class="form-label">Address *</label>
                <textarea
                  class="form-control"
                  id="editAddress"
                  rows="3"
                  required
                ></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Coordinates</label>
                <div class="coordinates-display">
                  <p id="editCoordinatesDisplay" class="mb-0">
                    <strong>Longitude:</strong>
                    <span id="editLongitude">Not selected</span> |
                    <strong>Latitude:</strong>
                    <span id="editLatitude">Not selected</span>
                  </p>
                </div>
                <button
                  type="button"
                  class="btn btn-outline-primary"
                  onclick="openMapModalForEdit()"
                >
                  <i class="fas fa-map-marker-alt"></i> Update Location
                </button>
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
              class="btn btn-bfp-gold"
              onclick="updateEstablishment()"
            >
              <i class="fas fa-save"></i> Update Establishment
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Map Modal -->
    <div class="modal fade" id="mapModal" tabindex="-1">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div
            class="modal-header"
            style="background-color: var(--bfp-red); color: white"
          >
            <h5 class="modal-title">Select Location on Map</h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <div id="map"></div>
            <div class="mt-3">
              <p class="mb-2"><strong>Selected Coordinates:</strong></p>
              <p id="selectedCoordinates" class="text-muted">
                Click on the map to select a location
              </p>
            </div>
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
              class="btn btn-bfp-red"
              id="selectLocationBtn"
              onclick="selectLocation()"
              disabled
            >
              <i class="fas fa-check"></i> Select Location
            </button>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
    
    <script src="../../assets/scripts/user-my-establishments.js"></script>
  </body>
</html>
