// Sample user data
let users = [
  // {
  //   id: 1,
  //   fullName: "Admin User",
  //   email: "admin@bfp-siteprofiler.com",
  //   role: "Administrator",
  //   status: "Active",
  //   lastActive: "Today, 10:45 AM",
  //   initials: "AD",
  //   avatar: "#dc3545",
  // },
  // {
  //   id: 2,
  //   fullName: "Juan Dela Cruz",
  //   email: "juan.delacruz@bfp.gov.ph",
  //   role: "Inspector",
  //   status: "Active",
  //   lastActive: "Today, 09:30 AM",
  //   initials: "JD",
  //   avatar: "#dc3545",
  // },
  // {
  //   id: 3,
  //   fullName: "Maria Santos",
  //   email: "maria.santos@bfp.gov.ph",
  //   role: "Inspector",
  //   status: "Active",
  //   lastActive: "Yesterday, 03:15 PM",
  //   initials: "MS",
  //   avatar: "#dc3545",
  // },
  // {
  //   id: 4,
  //   fullName: "Roberto Pacquiao",
  //   email: "roberto.pacquiao@bfp.gov.ph",
  //   role: "Inspector",
  //   status: "Inactive",
  //   lastActive: "June 10, 2025",
  //   initials: "RP",
  //   avatar: "#dc3545",
  // },
  // {
  //   id: 5,
  //   fullName: "Virac Town Center",
  //   email: "manager@viractowncenter.com",
  //   role: "Establishment",
  //   status: "Active",
  //   lastActive: "Today, 11:20 AM",
  //   initials: "VT",
  //   avatar: "#dc3545",
  // },
  // {
  //   id: 6,
  //   fullName: "Mark Johnson",
  //   email: "mark.johnson@bfp.gov.ph",
  //   role: "Inspector",
  //   status: "Active",
  //   lastActive: "Today, 08:15 AM",
  //   initials: "MJ",
  //   avatar: "#dc3545",
  // },
  // {
  //   id: 7,
  //   fullName: "Sarah Williams",
  //   email: "sarah.williams@establishment.com",
  //   role: "Establishment",
  //   status: "Inactive",
  //   lastActive: "Yesterday, 02:30 PM",
  //   initials: "SW",
  //   avatar: "#dc3545",
  // },
  // {
  //   id: 8,
  //   fullName: "David Brown",
  //   email: "david.brown@bfp.gov.ph",
  //   role: "Administrator",
  //   status: "Active",
  //   lastActive: "Today, 07:45 AM",
  //   initials: "DB",
  //   avatar: "#dc3545",
  // },
];
let filteredUsers = [...users];
let currentPage = 1;
let usersPerPage = 5;

async function getUserList(){
 
  renderTable()
}


async function renderTable() {
  const res = await fetch("../../utility/getUserList.php")
  const json = await res.json()
  console.log(json)
  users = json
  filteredUsers = [...json];
  currentPage = 1;
  usersPerPage = 5;
  const tableBody = document.getElementById("userTableBody");
  const startIndex = (currentPage - 1) * usersPerPage;
  const endIndex = startIndex + usersPerPage;
  const currentUsers = filteredUsers.slice(startIndex, endIndex);
  console.log(currentUsers)
  tableBody.innerHTML = currentUsers
    .map(
      (user) => `
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm" style="background-color: #dc3545;">${user.fullname[0]}</div>
                            <span>${user.fullname}</span>
                        </div>
                    </td>
                    <td>${user.email}</td>
                    <td>
                        <span class="badge ${getRoleBadgeClass(user.role)}">${
        user.role
      }</span>
                    </td>
                    <td>
                        <span class="badge ${
                          user.status === "active"
                            ? "bg-success"
                            : "bg-secondary"
                        }">${user.status}</span>
                    </td>
                    
                    <td>
                        <button class="action-btn btn btn-outline-primary btn-sm" onclick="editUser(${
                          user.id
                        })" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn btn btn-outline-danger btn-sm" onclick="deleteUser(${
                          user.id
                        })" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `
    )
    .join("");

  renderPagination();
}

function getRoleBadgeClass(role) {
  switch (role) {
    case "Administrator":
      return "bg-danger";
    case "Inspector":
      return "bg-secondary";
    case "Establishment":
      return "bg-warning text-dark";
    default:
      return "bg-secondary";
  }
}

function renderPagination() {
  const totalPages = Math.ceil(filteredUsers.length / usersPerPage);
  const pagination = document.getElementById("pagination");

  let paginationHTML = "";

  // Previous button
  paginationHTML += `
                <li class="page-item ${currentPage === 1 ? "disabled" : ""}">
                    <a class="page-link" href="#" onclick="changePage(${
                      currentPage - 1
                    })">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            `;

  // Page numbers
  for (let i = 1; i <= totalPages; i++) {
    paginationHTML += `
                    <li class="page-item ${i === currentPage ? "active" : ""}">
                        <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
                    </li>
                `;
  }

  // Next button
  paginationHTML += `
                <li class="page-item ${
                  currentPage === totalPages ? "disabled" : ""
                }">
                    <a class="page-link" href="#" onclick="changePage(${
                      currentPage + 1
                    })">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            `;

  pagination.innerHTML = paginationHTML;
}

function changePage(page) {
  const totalPages = Math.ceil(filteredUsers.length / usersPerPage);
  if (page >= 1 && page <= totalPages) {
    currentPage = page;
    renderTable();
  }
}

function searchUsers() {
  const searchTerm = document.getElementById("searchInput").value.toLowerCase();
  filteredUsers = users.filter(
    (user) =>
      user.fullName.toLowerCase().includes(searchTerm) ||
      user.email.toLowerCase().includes(searchTerm) ||
      user.role.toLowerCase().includes(searchTerm)
  );
  currentPage = 1;
  renderTable();
}

 async function saveUser() {
  const form = document.getElementById("addUserForm");
  const formData = new FormData(form);

  // Basic validation
  if (formData.get("password") !== formData.get("confirmPassword")) {
    alert("Passwords do not match!");
    return;
  }

  // Create new user
  const newUser = {
    id: users.length + 1,
    fullName: formData.get("fullName"),
    address: formData.get("address"),
    email: formData.get("email"),
    role: formData.get("role"),
    status: formData.get("active") ? "Active" : "Inactive",
    lastActive: "Just now",
    initials: formData
      .get("fullName")
      .split(" ")
      .map((name) => name[0])
      .join("")
      .toUpperCase(),
    avatar: "#dc3545",
  };
  const a = await fetch("../../utility/addNewUser.php",{
    method:"POST",
    headers:{'Content-Type': 'application/json'},
    body:JSON.stringify({
      fullname: formData.get("fullName"),
      password: formData.get("password"),
      username: formData.get("username"),
      role: formData.get("role"),
      address: formData.get("address"),
      role: formData.get("role"),
      email: formData.get("email"),
      status: formData.get("active") ? "active" : "inactive",
    })
  });
  const b = await a.json()
  console.log(b)
  users.push(newUser);
  filteredUsers = [...users];
  renderTable();

  // Close modal and reset form
  const modal = bootstrap.Modal.getInstance(
    document.getElementById("addUserModal")
  );
  modal.hide();
  form.reset();

  alert("User added successfully!");
}

function editUser(userId) {
  const user = users.find((u) => u.id === userId);
  if (!user) return;

  const form = document.getElementById("editUserForm");
  form.userId.value = user.id;
  form.fullName.value = user.fullname;
  form.email.value = user.email || "";
  form.role.value = user.role;
  form.address.value = user.address;
  form.active.checked = user.status === "Active";

  const modal = new bootstrap.Modal(document.getElementById("editUserModal"));
  modal.show();
}

 async function updateUser(){
  const form = document.getElementById("editUserForm");
  const formData = new FormData(form);
  const userId = parseInt(formData.get("userId"));

  const userIndex = users.findIndex((u) => u.id === userId);
  if (userIndex === -1) return;

  // Update user
  users[userIndex] = {
    ...users[userIndex],
    fullName: formData.get("fullName"),
    email: formData.get("email"),
    role: formData.get("role"),
    status: formData.get("active") ? "Active" : "Inactive",
    initials: formData
      .get("fullName")
      .split(" ")
      .map((name) => name[0])
      .join("")
      .toUpperCase(),
  };
const a = await fetch("../../utility/editUser.php",{
    method:"POST",
    headers:{'Content-Type': 'application/json'},
    body:JSON.stringify({
      fullname: formData.get("fullName"),
      password: formData.get("password"),
      username: formData.get("username"),
      role: formData.get("role"),
      address: formData.get("address"),
      role: formData.get("role"),
      id: userId,
      email: formData.get("email"),
      status: formData.get("active") ? "active" : "inactive",
    })
  });
  const j = await a.json()
  console.log(j)
  filteredUsers = [...users];
  renderTable();

  // Close modal
  const modal = bootstrap.Modal.getInstance(
    document.getElementById("editUserModal")
  );
  modal.hide();

  alert("User updated successfully!");
}

async function deleteUser(userId) {
  if (confirm("Are you sure you want to delete this user?")) {
    const d = await fetch(`../../utility/deleteUser.php?id=${userId}`)
    const j = await d.json()
    if(j.error){
      alert(j.error)
      return
    }
    users = users.filter((u) => u.id !== userId);
    filteredUsers = [...users];
    renderTable();
    alert("User deleted successfully!");
  }
}

// Event listeners
document.getElementById("searchInput").addEventListener("input", searchUsers);
document.getElementById("searchBtn").addEventListener("click", searchUsers);

// Initialize
renderTable();
