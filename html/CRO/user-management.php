<?php
session_start();
if (!isset($_SESSION['user']) || strtolower($_SESSION['role']) !== 'cro') {
    header('Location: ../index.php');
    exit;
}
$croName = htmlspecialchars($_SESSION['fullname'] ?? 'CRO Officer');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>BFP CRO - User Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../../assets/styles/components/sidebar.css"/>
    <style>
        :root { --bfp-red:#dc3545; --bfp-dark-red:#a02834; --bfp-gold:#ffc107; --bfp-dark:#1a1a1a; --bfp-light:#f8f9fa; }
        body { background:#f8f9fa; font-family:'Segoe UI',sans-serif; }
        .main-container { padding:30px 20px; padding-left:270px; }
        .welcome-section { background:linear-gradient(135deg,var(--bfp-red),var(--bfp-dark-red)); color:#fff; padding:30px; border-radius:10px; margin-bottom:30px; }
        .table-container { background:#fff; padding:25px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,.05); }
        .table thead { background:var(--bfp-red); color:#fff; }
        .modal-header { background:linear-gradient(135deg,var(--bfp-red),var(--bfp-dark-red)); color:#fff; }
        .modal-header .btn-close { filter:brightness(0) invert(1); }
    </style>
</head>
<body>
<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="logo-section">
        <div class="logo"><i class="fas fa-shield-alt" style="color:var(--bfp-red);font-size:24px"></i></div>
        <h5 class="mb-0">BFP SiteProfiler</h5>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-item"><a href="./dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></div>
        <div class="nav-item"><a href="./establishments.php" class="nav-link"><i class="fas fa-building"></i> Establishments</a></div>
        <div class="nav-item"><a href="./payment-verification.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Fee Management</a></div>
        <div class="nav-item"><a href="./user-management.php" class="nav-link active"><i class="fas fa-users"></i> User Management</a></div>
        <div class="nav-item"><a href="./inspection-history.php" class="nav-link"><i class="fas fa-history"></i> Inspection History</a></div>
        <div class="nav-item"><a href="./reports.php" class="nav-link"><i class="fas fa-chart-bar"></i> Reports</a></div>
        <div class="nav-item"><a href="./gis-map.php" class="nav-link"><i class="fas fa-map-marker-alt"></i> GIS Map</a></div>
    </nav>
    <div class="sidebar-logout"><a href="../../utility/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
</div>

<div class="container-fluid main-container">
    <div class="welcome-section">
        <h1><i class="fas fa-users"></i> User Management</h1>
        <p>Manage all user accounts registered in the system.</p>
    </div>

    <div class="table-container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex gap-2">
                <input type="text" id="searchOwner" class="form-control" style="max-width:250px" placeholder="Search name, email…"/>
                <select id="roleOwnerFilter" class="form-select" style="max-width:180px">
                    <option value="all">All Roles</option>
                    <option value="owner">Owner</option>
                    <option value="inspector">Inspector</option>
                    <option value="cro">CRO</option>
                    <option value="Chief">Chief</option>
                    <option value="admin">Admin</option>
                </select>
                <select id="statusOwnerFilter" class="form-select" style="max-width:180px">
                    <option value="all">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="pending">Pending</option>
                    <option value="inactive">Inactive</option>
                </select>
                <button class="btn btn-danger" onclick="loadOwners()"><i class="fas fa-search"></i></button>
            </div>
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addOwnerModal">
                <i class="fas fa-user-plus me-1"></i> Add User
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr><th>Name</th><th>Email</th><th>Phone</th><th>Address</th><th>Role</th><th>Actions</th></tr>
                </thead>
                <tbody id="ownersTable">
                    <tr><td colspan="6" class="text-center py-3"><span class="spinner-border spinner-border-sm me-2"></span>Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addOwnerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Add User Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="addOwnerError" class="alert alert-danger d-none"></div>
                <div class="mb-3"><label class="form-label">Full Name *</label><input type="text" id="ao_fullname" class="form-control"/></div>
                <div class="mb-3"><label class="form-label">Email *</label><input type="email" id="ao_email" class="form-control"/></div>
                <div class="mb-3"><label class="form-label">Username *</label><input type="text" id="ao_username" class="form-control"/></div>
                <div class="mb-3"><label class="form-label">Phone</label><input type="text" id="ao_phone" class="form-control"/></div>
                <div class="mb-3"><label class="form-label">Address</label><input type="text" id="ao_address" class="form-control"/></div>
                <div class="mb-3"><label class="form-label">Password *</label><input type="password" id="ao_password" class="form-control"/></div>
                <div class="mb-3">
                    <label class="form-label">Role *</label>
                    <select id="ao_role" class="form-select">
                        <option value="owner">Owner</option>
                        <option value="inspector">Inspector</option>
                        <option value="cro">CRO</option>
                        <option value="Chief">Chief</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger" onclick="addOwner()"><i class="fas fa-save me-1"></i>Save User</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editOwnerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit User Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="editOwnerError" class="alert alert-danger d-none"></div>
                <input type="hidden" id="eo_id"/>
                <div class="mb-3"><label class="form-label">Full Name</label><input type="text" id="eo_fullname" class="form-control"/></div>
                <div class="mb-3"><label class="form-label">Email</label><input type="email" id="eo_email" class="form-control"/></div>
                <div class="mb-3"><label class="form-label">Phone</label><input type="text" id="eo_phone" class="form-control"/></div>
                <div class="mb-3"><label class="form-label">Address</label><input type="text" id="eo_address" class="form-control"/></div>
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <select id="eo_role" class="form-select">
                        <option value="owner">Owner</option>
                        <option value="inspector">Inspector</option>
                        <option value="cro">CRO</option>
                        <option value="Chief">Chief</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select id="eo_status" class="form-select">
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger" onclick="saveEditOwner()"><i class="fas fa-save me-1"></i>Update</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
async function loadOwners() {
    const search = document.getElementById('searchOwner').value.trim();
    const status = document.getElementById('statusOwnerFilter').value;
    const tbody  = document.getElementById('ownersTable');
    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-2"><span class="spinner-border spinner-border-sm"></span></td></tr>';

    try {
        const res = await fetch('../../utility/getUserList.php');
        const d   = await res.json();
        // Filter to owners only
        let users = Array.isArray(d) ? d : (d.users || []);
        users = users.filter(u => u.role && u.role.toLowerCase() === 'owner');
        if (status !== 'all') users = users.filter(u => (u.status||'').toLowerCase() === status);
        if (search) {
            const q = search.toLowerCase();
            users = users.filter(u => (u.fullname||'').toLowerCase().includes(q) || (u.email||'').toLowerCase().includes(q));
        }

        if (!users.length) { tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-2">No owners found.</td></tr>'; return; }

        tbody.innerHTML = users.map(u => {
            return `<tr>
                <td><strong>${u.fullname||'—'}</strong></td>
                <td>${u.email||'—'}</td>
                <td>${u.phone_number||'—'}</td>
                <td>${u.address||'—'}</td>
                <td>${u.role||'—'}</td>
                <td>
                    <button class="btn btn-sm btn-outline-danger me-1" onclick='openEditOwner(${JSON.stringify(u).replace(/'/g,"&#39;")})'>
                        <i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger" onclick="deleteOwner(${u.id},'${(u.fullname||'').replace(/'/g,"\\'")}')">
                        <i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        }).join('');
    } catch(e) { tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading data.</td></tr>'; }
}

async function addOwner() {
    const errEl = document.getElementById('addOwnerError');
    errEl.classList.add('d-none');
    const body = {
        fullname: document.getElementById('ao_fullname').value.trim(),
        email:    document.getElementById('ao_email').value.trim(),
        username: document.getElementById('ao_username').value.trim(),
        phone_number: document.getElementById('ao_phone').value.trim(),
        address:  document.getElementById('ao_address').value.trim(),
        password: document.getElementById('ao_password').value,
        role:     document.getElementById('ao_role').value,
        status:   'active'
    };
    if (!body.fullname || !body.email || !body.username || !body.password) {
        errEl.textContent = 'Full name, email, username, and password are required.';
        errEl.classList.remove('d-none'); return;
    }
    try {
        const res = await fetch('../../utility/addNewUser.php', {
            method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body)
        });
        const d = await res.json();
        if (d.success || d.message === 'User added successfully') {
            bootstrap.Modal.getInstance(document.getElementById('addOwnerModal')).hide();
            showToast('User account created.', 'success');
            loadOwners();
        } else { errEl.textContent = d.message || 'Failed.'; errEl.classList.remove('d-none'); }
    } catch(e) { errEl.textContent = 'Network error.'; errEl.classList.remove('d-none'); }
}

function openEditOwner(u) {
    document.getElementById('eo_id').value       = u.id;
    document.getElementById('eo_fullname').value = u.fullname || '';
    document.getElementById('eo_email').value    = u.email || '';
    document.getElementById('eo_phone').value    = u.phone_number || '';
    document.getElementById('eo_address').value  = u.address || '';
    document.getElementById('eo_role').value     = u.role || 'owner';
    document.getElementById('eo_status').value   = u.status || 'active';
    new bootstrap.Modal(document.getElementById('editOwnerModal')).show();
}

async function saveEditOwner() {
    const errEl = document.getElementById('editOwnerError');
    errEl.classList.add('d-none');
    const body = {
        id:       parseInt(document.getElementById('eo_id').value),
        fullname: document.getElementById('eo_fullname').value.trim(),
        email:    document.getElementById('eo_email').value.trim(),
        phone_number: document.getElementById('eo_phone').value.trim(),
        address:  document.getElementById('eo_address').value.trim(),
        role:     document.getElementById('eo_role').value,
        contact:  document.getElementById('eo_phone').value.trim(),
        status:   document.getElementById('eo_status').value
    };
    try {
        const res = await fetch('../../utility/editUser.php', {
            method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body)
        });
        const d = await res.json();
        if (d.success || d.message === 'User updated successfully' || d.success === 'user has been updated') {
            bootstrap.Modal.getInstance(document.getElementById('editOwnerModal')).hide();
            showToast('User updated.', 'success');
            loadOwners();
        } else { errEl.textContent = d.message || 'Failed.'; errEl.classList.remove('d-none'); }
    } catch(e) { errEl.textContent = 'Network error.'; errEl.classList.remove('d-none'); }
}

async function deleteOwner(id, name) {
    if (!confirm(`Delete user account "${name}"? This cannot be undone.`)) return;
    try {
        const res = await fetch('../../utility/deleteUser.php', {
            method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({id})
        });
        const d = await res.json();
        if (d.success || d.message === 'User deleted successfully') {
            showToast('User deleted.', 'success'); loadOwners();
        } else { showToast(d.message || 'Failed.', 'danger'); }
    } catch(e) { showToast('Network error.', 'danger'); }
}

function showToast(msg, type) {
    const t = document.createElement('div');
    t.className = `alert alert-${type} position-fixed bottom-0 end-0 m-3`;
    t.style.zIndex = '9999';
    t.innerHTML = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3500);
}

document.getElementById('searchOwner').addEventListener('keydown', e => { if (e.key === 'Enter') loadOwners(); });
document.addEventListener('DOMContentLoaded', loadOwners);
</script>
</body>
</html>
