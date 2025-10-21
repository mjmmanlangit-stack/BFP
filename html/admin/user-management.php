
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>User Management - BFP SiteProfiler</title>
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
      rel="stylesheet"
    />

    <link
      rel="stylesheet"
      href="../../assets/styles/layout/admin-user-management.css"
    />

    <link rel="stylesheet" href="../../assets/styles/components/sidebar.css">
    <link rel="stylesheet" href="../../assets/styles/components/header.css">
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
      <div class="top-header">
        <div class="d-flex align-items-center">
          <button class="mobile-menu-toggle me-3" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
          </button>
          <h4 class="mb-0">User Management</h4>
        </div>
        <div class="admin-info">
          <i class="fas fa-bell text-danger"></i>
          <div class="admin-avatar">AD</div>
          <span class="ms-2">Admin User</span>
        </div>
      </div>

      <!-- Content Area -->
      <div class="content-area">
        <!-- Search Section -->
        <div class="search-section">
          <div class="row">
            <div class="col-md-8">
              <div class="input-group">
                <input
                  type="text"
                  class="form-control"
                  id="searchInput"
                  placeholder="Search users..."
                />
                <button class="btn btn-bfp" type="button" id="searchBtn">
                  <i class="fas fa-search"></i>
                </button>
              </div>
            </div>
            <div class="col-md-4 text-end">
              <button
                class="btn btn-bfp"
                data-bs-toggle="modal"
                data-bs-target="#addUserModal"
              >
                <i class="fas fa-user-plus"></i> Add New User
              </button>
            </div>
          </div>
        </div>

        <!-- Table Section -->
        <div class="table-section">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Status</th>
                
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="userTableBody">
                <!-- Table content will be populated by JavaScript -->
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="d-flex justify-content-center p-3">
            <nav>
              <ul class="pagination mb-0" id="pagination">
                <!-- Pagination will be populated by JavaScript -->
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Add New User</h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <form id="addUserForm">
              <div class="mb-3">
                <label class="form-label">Full Name *</label>
                <input
                  type="text"
                  class="form-control"
                  name="fullName"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Email Address *</label>
                <input
                  type="email"
                  class="form-control"
                  name="email"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Address</label>
                <input
                  type="text"
                  class="form-control"
                  name="address"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Role *</label>
                <select class="form-select" name="role" required>
                  <option value="">Select Role</option>
                  <option value="Administrator">Administrator</option>
                  <option value="Inspector">Inspector</option>
                  <option value="Establishment">Establishment</option>
                  <option value="owner">owner</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Password *</label>
                <input
                  type="password"
                  class="form-control"
                  name="password"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Confirm Password *</label>
                <input
                  type="password"
                  class="form-control"
                  name="confirmPassword"
                  required
                />
              </div>
              <div class="mb-3">
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    name="active"
                    id="activeCheck"
                    checked
                  />
                  <label class="form-check-label" for="activeCheck">
                    Active User
                  </label>
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
            <button type="button" class="btn btn-bfp" onclick="saveUser()">
              Save User
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit User</h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <form id="editUserForm">
              <input type="hidden" name="userId" />
              <div class="mb-3">
                <label class="form-label">Full Name *</label>
                <input
                  type="text"
                  class="form-control"
                  name="fullName"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Email Address *</label>
                <input
                  type="email"
                  class="form-control"
                  name="email"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Address </label>
                <input
                  type="text"
                  class="form-control"
                  name="address"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Role *</label>
                <select class="form-select" name="role" required>
                  <option value="">Select Role</option>
                  <option value="admin">Administrator</option>
                  <option value="inspector">Inspector</option>
                  <!-- <option value="Establishment">Establishment</option> -->
                </select>
              </div>
              <div class="mb-3">
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    name="active"
                    id="editActiveCheck"
                  />
                  <label class="form-check-label" for="editActiveCheck">
                    Active User
                  </label>
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
            <button type="button" class="btn btn-bfp" onclick="updateUser()">
              Update User
            </button>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script src="../../assets/scripts/admin-user-management.js"></script>
    <script src="../../assets/scripts/components/sidebar.js"></script>
  </body>
</html>
