// Sample data
let establishments = [
  // {
  //   id: "EST-001",
  //   name: "Catanduanes State University",
  //   type: "Educational",
  //   location: "Virac, Catanduanes",
  //   owner: "Dr. Patrick Alain Azanza",
  //   contact: "09271234567",
  //   status: "Compliant",
  //   lastInspection: "2023-05-15",
  //   notes: "Regular educational facility inspection completed successfully.",
  // },
  // {
  //   id: "EST-002",
  //   name: "Virac Public Market",
  //   type: "Public Market",
  //   location: "Virac, Catanduanes",
  //   owner: "Municipality of Virac",
  //   contact: "09271234568",
  //   status: "Non-Compliant",
  //   lastInspection: "2023-04-28",
  //   notes: "Fire exits blocked, needs immediate attention.",
  // },
  // {
  //   id: "EST-003",
  //   name: "Virac Town Center",
  //   type: "Commercial",
  //   location: "Virac, Catanduanes",
  //   owner: "Juan Dela Cruz",
  //   contact: "09271234569",
  //   status: "Compliant",
  //   lastInspection: "2023-06-01",
  //   notes: "All fire safety measures in place.",
  // },
  // {
  //   id: "EST-004",
  //   name: "Catanduanes Hotel",
  //   type: "Hospitality",
  //   location: "Virac, Catanduanes",
  //   owner: "Maria Santos",
  //   contact: "09271234570",
  //   status: "Pending Review",
  //   lastInspection: "",
  //   notes: "New establishment, pending initial inspection.",
  // },
];

let currentSort = { field: null, direction: "asc" };
let filteredEstablishments = [...establishments];

// Initialize page
document.addEventListener("DOMContentLoaded",  function () {
  renderTable();
  setupEventListeners();
});

function setupEventListeners() {
  // Search functionality
  document
    .getElementById("searchGeneral")
    .addEventListener("input", handleSearch);
  document
    .getElementById("searchSpecific")
    .addEventListener("input", handleSearch);

  // Sorting
  document.querySelectorAll(".sortable").forEach((th) => {
    th.addEventListener("click", handleSort);
  });

  // Modal handlers
  document
    .getElementById("saveEstablishmentBtn")
    .addEventListener("click", saveEstablishment);
  document
    .getElementById("updateEstablishmentBtn")
    .addEventListener("click", updateEstablishment);

  // Export functionality
  document.getElementById("exportBtn").addEventListener("click", exportData);
}

async function renderTable() {
  
  const res = await fetch("../../utility/adminGetEstablishment.php");
  const json = await res.json()
  console.log(json)
  establishments = json
  filteredEstablishments = [...establishments]
  const tbody = document.getElementById("establishmentsTableBody");
  tbody.innerHTML = "";

  filteredEstablishments.forEach((establishment) => {
    const row = createTableRow(establishment);
    tbody.appendChild(row);
  });

  // Update total count
  document.getElementById("totalCount").textContent =
    filteredEstablishments.length;
}

function createTableRow(establishment) {
  const row = document.createElement("tr");
  establishment.status = establishment.status ? establishment.status : "pending-review"
  const statusClass =
    {
      Compliant: "status-compliant",
      "non-compliant": "status-non-compliant",
      "pending-review": "status-pending",
    }[establishment.status] || "status-pending";

  row.innerHTML = `
                <td><strong>${establishment.id}</strong></td>
                <td>${establishment.name}</td>
                <td>${establishment.type}</td>
                <td>${establishment.location}</td>
                <td>${establishment.owner}</td>
                <td>${establishment.contact}</td>
                <td><span class="status-badge ${statusClass}">${
    establishment.status
  }</span></td>
                <td>${establishment.lastInspection || "-"}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-info btn-sm" onclick="viewEstablishment('${
                          establishment.id
                        }')" title="View">
                            <i class="fas fa-eye"></i> View
                        </button>
                       
                    </div>
                </td>
            `;

  return row;
}

function handleSearch(e) {
  const searchTerm = e.target.value.toLowerCase();

  filteredEstablishments = establishments.filter(
    (establishment) =>
      establishment.id.toLowerCase().includes(searchTerm) ||
      establishment.name.toLowerCase().includes(searchTerm) ||
      establishment.type.toLowerCase().includes(searchTerm) ||
      establishment.location.toLowerCase().includes(searchTerm) ||
      establishment.owner.toLowerCase().includes(searchTerm) ||
      establishment.contact.includes(searchTerm) ||
      establishment.status.toLowerCase().includes(searchTerm)
  );

  renderTable();
}

function handleSort(e) {
  const field = e.target.closest("th").dataset.sort;

  if (currentSort.field === field) {
    currentSort.direction = currentSort.direction === "asc" ? "desc" : "asc";
  } else {
    currentSort.field = field;
    currentSort.direction = "asc";
  }

  // Update sort icons
  document.querySelectorAll(".sort-icon").forEach((icon) => {
    icon.className = "fas fa-sort sort-icon";
  });

  const activeIcon = e.target.closest("th").querySelector(".sort-icon");
  activeIcon.className = `fas fa-sort-${
    currentSort.direction === "asc" ? "up" : "down"
  } sort-icon active`;

  // Sort data
  filteredEstablishments.sort((a, b) => {
    let aVal = a[field] || "";
    let bVal = b[field] || "";

    if (field === "lastInspection") {
      aVal = aVal || "1970-01-01";
      bVal = bVal || "1970-01-01";
    }

    if (currentSort.direction === "asc") {
      return aVal.localeCompare(bVal);
    } else {
      return bVal.localeCompare(aVal);
    }
  });

  renderTable();
}

function saveEstablishment() {
  const form = document.getElementById("addEstablishmentForm");

  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  const newEstablishment = {
    id: `EST-${String(establishments.length + 1).padStart(3, "0")}`,
    name: document.getElementById("addEstablishmentName").value,
    type: document.getElementById("addEstablishmentType").value,
    location: document.getElementById("addLocation").value,
    owner: document.getElementById("addOwner").value,
    contact: document.getElementById("addContact").value,
    status: document.getElementById("addStatus").value,
    lastInspection: "",
    notes: document.getElementById("addNotes").value,
  };

  establishments.push(newEstablishment);
  filteredEstablishments = [...establishments];
  renderTable();

  // Close modal and reset form
  bootstrap.Modal.getInstance(
    document.getElementById("addEstablishmentModal")
  ).hide();
  form.reset();

  // Show success message
  showAlert("Establishment added successfully!", "success");
}

function viewEstablishment(id) {
  const establishment = establishments.find((e) => e.id == id);
  if (!establishment) return;
  const content = document.getElementById("viewEstablishmentContent");
  const statusClass =
    {
      Compliant: "status-compliant",
      "Non-Compliant": "status-non-compliant",
      "Pending Review": "status-pending",
    }[establishment.status] || "status-pending";

  content.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-muted">CONTACT & STATUS</h6>
                        <table class="table table-borderless">
                            <tr><td class="fw-bold">Owner:</td><td>${
                              establishment.owner
                            }</td></tr>
                            <tr><td class="fw-bold">Contact:</td><td>${
                              establishment.contact
                            }</td></tr>
                            <tr><td class="fw-bold">Status:</td><td><span class="status-badge ${statusClass}">${
    establishment.status
  }</span></td></tr>
                            <tr><td class="fw-bold">Last Inspection:</td><td>${
                              establishment.lastInspection ||
                              "Not inspected yet"
                            }</td></tr>
                        </table>
                    </div>
                </div>
                ${
                  establishment.notes
                    ? `
                <div class="mt-3">
                    <h6 class="fw-bold text-muted">NOTES</h6>
                    <p class="text-muted">${establishment.notes}</p>
                </div>
                `
                    : ""
                }
            `;

  new bootstrap.Modal(document.getElementById("viewEstablishmentModal")).show();
}

function editEstablishment(id) {
  const establishment = establishments.find((e) => e.id === id);
  if (!establishment) return;

  // Populate form
  document.getElementById("editEstablishmentId").value = establishment.id;
  document.getElementById("editEstablishmentName").value = establishment.name;
  document.getElementById("editEstablishmentType").value = establishment.type;
  document.getElementById("editLocation").value = establishment.location;
  document.getElementById("editOwner").value = establishment.owner;
  document.getElementById("editContact").value = establishment.contact;
  document.getElementById("editStatus").value = establishment.status;
  document.getElementById("editNotes").value = establishment.notes || "";

  new bootstrap.Modal(document.getElementById("editEstablishmentModal")).show();
}

function updateEstablishment() {
  const form = document.getElementById("editEstablishmentForm");

  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  const id = document.getElementById("editEstablishmentId").value;
  const establishmentIndex = establishments.findIndex((e) => e.id === id);

  if (establishmentIndex === -1) return;

  establishments[establishmentIndex] = {
    ...establishments[establishmentIndex],
    name: document.getElementById("editEstablishmentName").value,
    type: document.getElementById("editEstablishmentType").value,
    location: document.getElementById("editLocation").value,
    owner: document.getElementById("editOwner").value,
    contact: document.getElementById("editContact").value,
    status: document.getElementById("editStatus").value,
    notes: document.getElementById("editNotes").value,
  };

  filteredEstablishments = [...establishments];
  renderTable();

  // Close modal
  bootstrap.Modal.getInstance(
    document.getElementById("editEstablishmentModal")
  ).hide();

  // Show success message
  showAlert("Establishment updated successfully!", "success");
}

function exportData() {
  const csvContent = [
    [
      "ID",
      "Establishment",
      "Type",
      "Location",
      "Owner",
      "Contact",
      "Status",
      "Last Inspection",
      "Notes",
    ],
    ...filteredEstablishments.map((est) => [
      est.id,
      est.name,
      est.type,
      est.location,
      est.owner,
      est.contact,
      est.status,
      est.lastInspection || "",
      est.notes || "",
    ]),
  ]
    .map((row) => row.map((field) => `"${field}"`).join(","))
    .join("\n");

  const blob = new Blob([csvContent], { type: "text/csv" });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = "establishments_export.csv";
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  window.URL.revokeObjectURL(url);

  showAlert("Data exported successfully!", "success");
}

function showAlert(message, type) {
  // Create alert element
  const alertDiv = document.createElement("div");
  alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
  alertDiv.style.cssText =
    "top: 20px; right: 20px; z-index: 9999; min-width: 300px;";
  alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

  document.body.appendChild(alertDiv);

  // Auto remove after 5 seconds
  setTimeout(() => {
    if (alertDiv.parentNode) {
      alertDiv.remove();
    }
  }, 5000);
}
