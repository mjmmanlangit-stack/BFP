<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BFP Site Profiler - Admin Dashboard</title>
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
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
        margin-left: 250px;
        padding: 20px;
      }

      .bg-bfp-red {
        background-color: var(--bfp-red);
      }

      .bg-bfp-gold {
        background-color: var(--bfp-gold);
      }

      .bg-bfp-dark {
        background-color: var(--bfp-dark);
      }

      .btn-bfp-red {
        background-color: var(--bfp-red);
        border-color: var(--bfp-red);
        color: white;
      }

      .btn-bfp-red:hover {
        background-color: var(--bfp-dark-red);
        border-color: var(--bfp-dark-red);
        color: white;
      }

      .table-container {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      }

      .activity-log {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        max-height: 600px;
        overflow-y: auto;
      }

      .activity-item {
        padding: 1rem;
        border-left: 3px solid var(--bfp-red);
        margin-bottom: 1rem;
        background-color: var(--bfp-light);
        border-radius: 5px;
      }

      .activity-item:hover {
        background-color: #e9ecef;
      }

      .badge-role {
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
      }

      .modal-header {
        background: linear-gradient(
          135deg,
          var(--bfp-red) 0%,
          var(--bfp-dark-red) 100%
        );
        color: white;
      }

      .search-filter-bar {
        background: white;
        padding: 1rem;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 1.5rem;
      }

      .table thead {
        background-color: var(--bfp-red);
        color: white;
      }

      .action-btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
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
          <a href="./user-management.php" class="nav-link active">
            <i class="fas fa-user"></i>
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

    <div class="main-content mt-2">
      

      <div class="row">
        <!-- Statistics Cards -->
        <div class="col-md-12">
          <!-- User Management Table -->
          <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="mb-0"><i class="fas fa-users"></i> User Management</h5>
              <button
                class="btn btn-bfp-red"
                data-bs-toggle="modal"
                data-bs-target="#addUserModal"
              >
                <i class="fas fa-plus"></i> Add User
              </button>
            </div>

            <!-- Search and Filter Bar -->
            <div class="search-filter-bar">
              <div class="row">
                <div class="col-md-6">
                  <input
                    type="text"
                    class="form-control"
                    id="searchUser"
                    placeholder="Search by name..."
                  />
                </div>
                <div class="col-md-6">
                  <select class="form-select" id="filterRole">
                    <option value="">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="Inspector">Inspector</option>
                    <option value="Chief">Chief</option>
                    <option value="Fire Marshal">Fire Marshal</option>
                    <option value="CRO">Customer Relationship Officer</option>
                    <option value="Accessor">Accessor</option>
                    <option value="owner">Owner</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-hover" id="userTable">
                <thead>
                  <tr>
                    <th>Full Name</th>
                    <th>Role</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="userTableBody">
                  <!-- Users will be populated here -->
                </tbody>
              </table>
            </div>
          </div>
        </div>

        
      </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="fas fa-user-plus"></i> Add New User
            </h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <form id="addUserForm">
              <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input
                  type="text"
                  class="form-control"
                  id="addFullName"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Address</label>
                <input
                  type="text"
                  class="form-control"
                  id="addAddress"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Contact No.</label>
                <input
                  type="tel"
                  class="form-control"
                  id="addContact"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input
                  type="email"
                  class="form-control"
                  id="addEmail"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Role</label>
                <select class="form-select" id="addRole" required>
                  <option value="">Select Role</option>
                  <option value="admin">Admin</option>
                  <option value="Inspector">Inspector</option>
                  <option value="Chief">Chief</option>
                  <option value="Fire Marshal">Fire Marshal</option>
                  <option value="CRO">Customer Relationship Officer</option>
                  <option value="Accessor">Accessor</option>
                  <option value="owner">Owner</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" id="addStatus" required>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
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
            <button type="button" class="btn btn-bfp-red" onclick="addUser()">
              Add User
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
            <h5 class="modal-title"><i class="fas fa-edit"></i> Edit User</h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <form id="editUserForm">
              <input type="hidden" id="editUserId" />
              <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input
                  type="text"
                  class="form-control"
                  id="editFullName"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Address</label>
                <input
                  type="text"
                  class="form-control"
                  id="editAddress"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Contact No.</label>
                <input
                  type="tel"
                  class="form-control"
                  id="editContact"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input
                  type="email"
                  class="form-control"
                  id="editEmail"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Role</label>
                <select class="form-select" id="editRole" required>
                  <option value="admin">Admin</option>
                  <option value="Inspector">Inspector</option>
                  <option value="Chief">Chief</option>
                  <option value="Fire Marshal">Fire Marshal</option>
                  <option value="CRO">Customer Relationship Officer</option>
                  <option value="Accessor">Accessor</option>
                  <option value="owner">Owner</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" id="editStatus" required>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
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
              onclick="saveEditUser()"
            >
              Save Changes
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- View User Modal -->
    <div class="modal fade" id="viewUserModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-eye"></i> User Details</h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <table class="table">
              <tr>
                <th>Full Name:</th>
                <td id="viewFullName"></td>
              </tr>
              <tr>
                <th>Address:</th>
                <td id="viewAddress"></td>
              </tr>
              <tr>
                <th>Contact No.:</th>
                <td id="viewContact"></td>
              </tr>
              <tr>
                <th>Email:</th>
                <td id="viewEmail"></td>
              </tr>
              <tr>
                <th>Role:</th>
                <td id="viewRole"></td>
              </tr>
              <tr>
                <th>Status:</th>
                <td id="viewStatus"></td>
              </tr>
            </table>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
      // Data storage
      let users = [];
      let nextId = 1;

      // Initialize
      document.addEventListener("DOMContentLoaded", function () {
        loadUsers();
        setupEventListeners();
      });

      function setupEventListeners() {
        document
          .getElementById("searchUser")
          .addEventListener("input", filterUsers);
        document
          .getElementById("filterRole")
          .addEventListener("change", filterUsers);
      }

      // Load users from backend
      async function loadUsers() {
        try {
          const response = await fetch('../../utility/getUserList.php');
          const data = await response.json();
          
          if (Array.isArray(data)) {
            users = data;
            renderUsers();
          } else {
            console.error('Invalid data format received');
            showAlert('Error loading users', 'danger');
          }
        } catch (error) {
          console.error('Error loading users:', error);
          showAlert('Failed to load users', 'danger');
        }
      }

      function getRoleBadgeClass(role) {
        const badges = {
          Inspector: "bg-danger",
          Chief: "bg-warning text-dark",
          "Fire Marshal": "bg-dark",
          CRO: "bg-info",
          Accessor: "bg-success",
          admin: "bg-primary",
          owner: "bg-secondary",
          inspector: "bg-danger"
        };
        return badges[role] || "bg-secondary";
      }

      function renderUsers(filteredUsers = null) {
        const usersToRender = filteredUsers || users;
        const tbody = document.getElementById("userTableBody");
        tbody.innerHTML = "";

        if (usersToRender.length === 0) {
          tbody.innerHTML = `
            <tr>
              <td colspan="5" class="text-center">No users found</td>
            </tr>
          `;
          return;
        }

        usersToRender.forEach((user) => {
          const tr = document.createElement("tr");
          const contact = user.phone_number || 'N/A';
          const status = user.status || 'active';
          
          tr.innerHTML = `
                    <td>${user.fullname}</td>
                    <td><span class="badge ${getRoleBadgeClass(
                      user.role
                    )} badge-role">${user.role}</span></td>
                    <td>${contact}</td>
                    <td><span class="badge ${
                      status.toLowerCase() === "active" ? "bg-success" : "bg-secondary"
                    }">${status}</span></td>
                    <td>
                        <button class="btn btn-sm btn-info action-btn" onclick="viewUser(${
                          user.id
                        })">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="btn btn-sm btn-warning action-btn" onclick="editUser(${
                          user.id
                        })">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </td>
                `;
          tbody.appendChild(tr);
        });
      }

      function filterUsers() {
        const searchTerm = document
          .getElementById("searchUser")
          .value.toLowerCase();
        const roleFilter = document.getElementById("filterRole").value.toLowerCase();

        const filtered = users.filter((user) => {
          const matchesSearch = user.fullname
            .toLowerCase()
            .includes(searchTerm);
          const matchesRole = !roleFilter || user.role.toLowerCase() === roleFilter.toLowerCase();
          return matchesSearch && matchesRole;
        });

        renderUsers(filtered);
      }

      async function addUser() {
        const fullName = document.getElementById("addFullName").value.trim();
        const address = document.getElementById("addAddress").value.trim();
        const contact = document.getElementById("addContact").value.trim();
        const email = document.getElementById("addEmail").value.trim();
        const role = document.getElementById("addRole").value;
        const status = document.getElementById("addStatus").value;

        if (!fullName || !address || !contact || !email || !role || !status) {
          showAlert("Please fill in all fields", "warning");
          return;
        }

        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
          showAlert("Please enter a valid email address", "warning");
          return;
        }

        // Phone validation (Philippine format)
        const phoneRegex = /^(09|\+639)\d{9}$/;
        if (!phoneRegex.test(contact.replace(/[\s-]/g, ''))) {
          showAlert("Please enter a valid Philippine phone number (e.g., 09171234567)", "warning");
          return;
        }

        const userData = {
          fullname: fullName,
          address: address,
          contact: contact,
          email: email,
          role: role,
          status: status
        };

        try {
          const response = await fetch('../../utility/addNewUser.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify(userData)
          });

          const result = await response.json();
          
          if (result.success) {
            showAlert("User added successfully!", "success");
            
            // Close modal and reset form
            const modal = bootstrap.Modal.getInstance(
              document.getElementById("addUserModal")
            );
            modal.hide();
            document.getElementById("addUserForm").reset();
            
            // Reload users
            await loadUsers();
          } else {
            showAlert(result.error || "Failed to add user", "danger");
          }
        } catch (error) {
          console.error('Error adding user:', error);
          showAlert("Failed to add user. Please try again.", "danger");
        }
      }

      async function editUser(id) {
        try {
          const response = await fetch(`../../utility/getUserById.php?id=${id}`);
          const user = await response.json();
          
          if (user.error) {
            showAlert("User not found", "danger");
            return;
          }

          document.getElementById("editUserId").value = user.id;
          document.getElementById("editFullName").value = user.fullname;
          document.getElementById("editAddress").value = user.address;
          document.getElementById("editContact").value = user.phone_number || '';
          document.getElementById("editEmail").value = user.email;
          document.getElementById("editRole").value = user.role;
          document.getElementById("editStatus").value = user.status;

          const modal = new bootstrap.Modal(
            document.getElementById("editUserModal")
          );
          modal.show();
        } catch (error) {
          console.error('Error loading user:', error);
          showAlert("Failed to load user details", "danger");
        }
      }

      async function saveEditUser() {
        const id = parseInt(document.getElementById("editUserId").value);
        const fullName = document.getElementById("editFullName").value.trim();
        const address = document.getElementById("editAddress").value.trim();
        const contact = document.getElementById("editContact").value.trim();
        const email = document.getElementById("editEmail").value.trim();
        const role = document.getElementById("editRole").value;
        const status = document.getElementById("editStatus").value;

        if (!fullName || !address || !email || !role || !status) {
          showAlert("Please fill in all required fields", "warning");
          return;
        }

        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
          showAlert("Please enter a valid email address", "warning");
          return;
        }

        // Phone validation (Philippine format) - only if contact is provided
        if (contact) {
          const phoneRegex = /^(09|\+639)\d{9}$/;
          if (!phoneRegex.test(contact.replace(/[\s-]/g, ''))) {
            showAlert("Please enter a valid Philippine phone number (e.g., 09171234567)", "warning");
            return;
          }
        }

        const userData = {
          id: id,
          fullname: fullName,
          address: address,
          contact: contact,
          email: email,
          role: role,
          status: status
        };

        try {
          const response = await fetch('../../utility/editUser.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify(userData)
          });

          const result = await response.json();
          
          if (result.success) {
            showAlert("User updated successfully!", "success");
            
            const modal = bootstrap.Modal.getInstance(
              document.getElementById("editUserModal")
            );
            modal.hide();
            
            // Reload users
            await loadUsers();
          } else {
            showAlert(result.error || "Failed to update user", "danger");
          }
        } catch (error) {
          console.error('Error updating user:', error);
          showAlert("Failed to update user. Please try again.", "danger");
        }
      }

      async function viewUser(id) {
        try {
          const response = await fetch(`../../utility/getUserById.php?id=${id}`);
          const user = await response.json();
          
          if (user.error) {
            showAlert("User not found", "danger");
            return;
          }

          document.getElementById("viewFullName").textContent = user.fullname;
          document.getElementById("viewAddress").textContent = user.address;
          document.getElementById("viewContact").textContent = user.phone_number || 'N/A';
          document.getElementById("viewEmail").textContent = user.email;
          document.getElementById(
            "viewRole"
          ).innerHTML = `<span class="badge ${getRoleBadgeClass(user.role)}">${
            user.role
          }</span>`;
          document.getElementById("viewStatus").innerHTML = `<span class="badge ${
            user.status.toLowerCase() === "active" ? "bg-success" : "bg-secondary"
          }">${user.status}</span>`;

          const modal = new bootstrap.Modal(
            document.getElementById("viewUserModal")
          );
          modal.show();
        } catch (error) {
          console.error('Error loading user:', error);
          showAlert("Failed to load user details", "danger");
        }
      }

      function showAlert(message, type = 'info') {
        // Create alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
          ${message}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
          alertDiv.remove();
        }, 5000);
      }
    </script>
  </body>
</html>
