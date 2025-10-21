<?php
 include '../../utility/checkingUser.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BFP Site Profiler - Certificates</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
      rel="stylesheet"
    />

    <link rel="stylesheet" href="../../assets/styles/components/sidebar.css">
    <link rel="stylesheet" href="../../assets/styles/components/header.css">

    <link
      rel="stylesheet"
      href="../../assets/styles/layout/user-certificates.css"
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

    <!-- Main Content -->
    <div class="main-content">
      <div class="top-header">
        <div class="d-flex align-items-center">
          <button class="mobile-menu-toggle me-3" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
          </button>
          <h4 class="mb-0">My Certificates</h4>
        </div>
        <div class="admin-info">
          <div class="admin-avatar">JD</div>
          <span class="ms-2">Juan Dela Cruz</span>
        </div>
      </div>

      <div class="content-area">

        <!-- Header Section -->
        <div class="header-section">
          <div
            class="d-flex justify-content-between align-items-center flex-wrap"
          >
            <div>
              <h1 class="page-title">
                <i class="fas fa-certificate text-warning me-3"></i>
                Certificates
              </h1>
              <p class="page-subtitle">
                Manage your Fire Safety Certificates (FSIC)
              </p>
            </div>
          </div>
        </div>
  
        <!-- Filter Section -->
        <div class="filter-section">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label fw-bold">Establishment</label>
              <select class="form-select" id="establishmentFilter">
                <option value="">All Establishments</option>
                <option value="Delicious Restaurant">Delicious Restaurant</option>
                <option value="Juan's Hardware Store">
                  Juan's Hardware Store
                </option>
                <option value="Cruz Family Apartment">
                  Cruz Family Apartment
                </option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-bold">Status</label>
              <select class="form-select" id="statusFilter">
                <option value="">All Statuses</option>
                <option value="Active">Active</option>
                <option value="Expired">Expired</option>
                <option value="Pending">Pending</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-bold">Issued After</label>
              <input type="date" class="form-control" id="dateFilter" />
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
              <button
                class="btn btn-bfp-primary d-flex align-items-center"
                onclick="applyFilters()"
              >
                <i class="fas fa-search me-2"></i>
                Search
              </button>
              <button class="btn btn-outline-secondary" onclick="resetFilters()">
                Reset
              </button>
            </div>
          </div>
        </div>
  
        <!-- Certificates Table -->
        <div class="certificates-table">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead>
                <tr>
                  <th>Certificate No.</th>
                  <th>Establishment</th>
                  <th>Issued Date</th>
                  <th>Expiry Date</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="certificatesTableBody">
                <!-- Table rows will be populated by JavaScript -->
              </tbody>
            </table>
          </div>
          <div class="p-3 border-top bg-light">
            <div class="d-flex justify-content-between align-items-center">
              <span class="text-muted"
                >Showing <span id="showingCount">0</span> of
                <span id="totalCount">0</span> certificates</span
              >
              <nav>
                <ul class="pagination pagination-sm mb-0">
                  <li class="page-item">
                    <a class="page-link" href="#" onclick="changePage(-1)"
                      >Previous</a
                    >
                  </li>
                  <li class="page-item active">
                    <span class="page-link" id="currentPage">1</span>
                  </li>
                  <li class="page-item">
                    <a class="page-link" href="#" onclick="changePage(1)">Next</a>
                  </li>
                </ul>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Request New Certificate Modal -->
    <div class="modal fade" id="requestModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="fas fa-plus-circle me-2"></i>
              Request New Fire Safety Certificate
            </h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <form id="requestForm">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label fw-bold">Certificate Type</label>
                  <select class="form-select" required>
                    <option value="">Select Certificate Type</option>
                    <option value="FSIC">
                      Fire Safety Inspection Certificate (FSIC)
                    </option>
                    <option value="FSEC">
                      Fire Safety Evaluation Clearance (FSEC)
                    </option>
                    <option value="FSPC">
                      Fire Safety Permit Certificate (FSPC)
                    </option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold">Establishment</label>
                  <select class="form-select" required>
                    <option value="">Select Establishment</option>
                    <option value="Delicious Restaurant">
                      Delicious Restaurant
                    </option>
                    <option value="Juan's Hardware Store">
                      Juan's Hardware Store
                    </option>
                    <option value="Cruz Family Apartment">
                      Cruz Family Apartment
                    </option>
                  </select>
                </div>
                <div class="col-12">
                  <label class="form-label fw-bold">Purpose</label>
                  <textarea
                    class="form-control"
                    rows="3"
                    placeholder="Please specify the purpose of this certificate request..."
                    required
                  ></textarea>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold">Priority</label>
                  <select class="form-select" required>
                    <option value="Normal">Normal (14-21 days)</option>
                    <option value="Rush">Rush (7-10 days)</option>
                    <option value="Emergency">Emergency (3-5 days)</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold">Contact Number</label>
                  <input
                    type="tel"
                    class="form-control"
                    placeholder="+63 9XX XXX XXXX"
                    required
                  />
                </div>
                <div class="col-12">
                  <label class="form-label fw-bold">Additional Documents</label>
                  <input
                    type="file"
                    class="form-control"
                    multiple
                    accept=".pdf,.jpg,.jpeg,.png"
                  />
                  <div class="form-text">
                    Upload supporting documents (PDF, JPG, PNG only)
                  </div>
                </div>
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
              onclick="submitRequest()"
            >
              <i class="fas fa-paper-plane me-2"></i>
              Submit Request
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- View Certificate Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="fas fa-eye me-2"></i>
              Certificate Details
            </h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <div class="certificate-preview">
              <div class="certificate-content">
                <div
                  class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center certificate-logo"
                >
                  <i
                    class="fas fa-fire text-danger"
                    style="font-size: 3rem"
                  ></i>
                </div>
                <h2 class="certificate-title">
                  FIRE SAFETY INSPECTION CERTIFICATE
                </h2>
                <h4 class="certificate-subtitle">
                  Republic of the Philippines<br />Bureau of Fire Protection
                </h4>

                <div class="certificate-details">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <strong>Certificate No:</strong>
                      <span id="modalCertNo">-</span>
                    </div>
                    <div class="col-md-6">
                      <strong>Status:</strong>
                      <span id="modalStatus" class="status-badge">-</span>
                    </div>
                    <div class="col-md-6">
                      <strong>Establishment:</strong>
                      <span id="modalEstablishment">-</span>
                    </div>
                    <div class="col-md-6">
                      <strong>Address:</strong>
                      <span id="modalAddress">-</span>
                    </div>
                    <div class="col-md-6">
                      <strong>Issued Date:</strong>
                      <span id="modalIssuedDate">-</span>
                    </div>
                    <div class="col-md-6">
                      <strong>Expiry Date:</strong>
                      <span id="modalExpiryDate">-</span>
                    </div>
                    <div class="col-12">
                      <strong>Inspecting Officer:</strong>
                      <span id="modalOfficer">-</span>
                    </div>
                  </div>
                </div>

                <div class="mt-4">
                  <p class="text-muted">
                    This certificate is valid for the period specified above and
                    is subject to renewal.
                  </p>
                </div>
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
            <button
              type="button"
              class="btn btn-bfp-primary"
              onclick="downloadCertificate()"
            >
              <i class="fas fa-download me-2"></i>
              Download Certificate
            </button>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/scripts/user-certificates.js"></script>
  </body>
</html>
