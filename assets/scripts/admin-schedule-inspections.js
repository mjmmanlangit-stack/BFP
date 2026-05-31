// Sample data
let inspectors = [
  // {
  //   id: "juan-cruz",
  //   name: "Juan Dela Cruz",
  //   initials: "JC",
  //   status: "2 inspections scheduled today",
  //   available: true,
  // },
  // {
  //   id: "maria-santos",
  //   name: "Maria Santos",
  //   initials: "MS",
  //   status: "1 inspection scheduled today",
  //   available: true,
  // },
  // {
  //   id: "roberto-pasquino",
  //   name: "Roberto Pasquino",
  //   initials: "RP",
  //   status: "Available all day",
  //   available: true,
  // },
];


let upcomingInspections = [
  // {
  //   id: 1,
  //   establishment: "Virac Public Market",
  //   establishmentId: "est-002",
  //   type: "Routine",
  //   dateTime: "2023-06-15 09:00",
  //   inspector: "Juan Dela Cruz",
  //   inspectorId: "juan-cruz",
  //   priority: "High",
  //   status: "Pending",
  //   notes: "Regular market inspection",
  // },
  // {
  //   id: 2,
  //   establishment: "Catanduanes State University",
  //   establishmentId: "est-001",
  //   type: "Follow-up",
  //   dateTime: "2023-06-16 02:00",
  //   inspector: "Maria Santos",
  //   inspectorId: "maria-santos",
  //   priority: "Medium",
  //   status: "Approved",
  //   notes: "Follow-up on previous findings",
  // },
  // {
  //   id: 3,
  //   establishment: "Virac Town Center",
  //   establishmentId: "est-003",
  //   type: "Initial",
  //   dateTime: "2023-06-17 10:00",
  //   inspector: "Roberto Pasquino",
  //   inspectorId: "roberto-pasquino",
  //   priority: "Medium",
  //   status: "Approved",
  //   notes: "Initial inspection for new business",
  // },
  // {
  //   id: 4,
  //   establishment: "BFP Catanduanes",
  //   establishmentId: "est-004",
  //   type: "Routine",
  //   dateTime: "2023-06-18 08:30",
  //   inspector: "Juan Dela Cruz",
  //   inspectorId: "juan-cruz",
  //   priority: "Low",
  //   status: "Completed",
  //   notes: "Internal facility inspection",
  // },
];

let selectedInspector = null;
let map = null;

// Initialize page
document.addEventListener("DOMContentLoaded", function () {
  initializeMap();
  renderInspectors(inspectors);
  renderUpcomingInspections();
  renderInspectorSchedule();
  setupEventListeners();
});

async function initializeMap() {
  // Initialize map centered on Virac, Catanduanes
  const res = await fetch("../../utility/inspectionAssignment.php");
  const j = await res.json();
  map = L.map("inspectionMap").setView([j[0].lat, j[0].lng], 13);

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "© OpenStreetMap contributors",
  }).addTo(map);

  // Add sample establishment markers
  let establishments = [
    {
      name: "Catanduanes State University",
      lat: 13.582,
      lng: 124.241,
      type: "Educational",
    },
    {
      name: "Virac Public Market",
      lat: 13.5732,
      lng: 124.235,
      type: "Public Market",
    },
    {
      name: "Virac Town Center",
      lat: 13.575,
      lng: 124.238,
      type: "Commercial",
    },
    {
      name: "BFP Catanduanes",
      lat: 13.57,
      lng: 124.232,
      type: "Government",
    },
    {
      name: "Catanduanes Hotel",
      lat: 13.578,
      lng: 124.237,
      type: "Hospitality",
    },
  ];
  
  establishments = j
  establishments.forEach((est) => {
    const marker = L.marker([est.lat, est.lng]).addTo(map);
    marker.bindPopup(`<strong>${est.name}</strong><br>Type: ${est.type}`);
  });
}

async function renderInspectors(list) {
  // Get the selected date and time slot if available (for filtering)
  const inspectionDateInput = document.getElementById("inspectionDate");
  const timeSlotInput = document.getElementById("timeSlot");
  
  let url = "../../utility/getInspector.php";
  
  // If both date and time slot are selected, pass them as query parameters for filtering
  if (inspectionDateInput && inspectionDateInput.value && timeSlotInput && timeSlotInput.value) {
    url += "?inspection_date=" + encodeURIComponent(inspectionDateInput.value) + 
           "&time_slot=" + encodeURIComponent(timeSlotInput.value);
  }
  
  const res = await fetch(url);
  const json = await res.json();
  console.log(json);
  inspectors = json;
  const inspectorList = document.getElementById("inspectorList");
  inspectorList.innerHTML = "";

  if (inspectors.length === 0) {
    inspectorList.innerHTML = "<p>No available inspectors for the selected date and time.</p>";
    return;
  }

  inspectors.forEach((inspector) => {
    const inspectorCard = document.createElement("div");
    inspectorCard.className = "inspector-card";
    inspectorCard.dataset.inspectorId = inspector.id;

    inspectorCard.innerHTML = `
      <div class="inspector-avatar">${inspector.fullname[0]}</div>
      <div class="inspector-info">
        <h6>${inspector.fullname}</h6>
        <small>${inspector.status || 'Available'}</small>
      </div>
    `;

    inspectorCard.addEventListener("click", () => selectInspector(inspector));
    inspectorList.appendChild(inspectorCard);
  });
}


function selectInspector(inspector) {
  // Remove previous selection
  document.querySelectorAll(".inspector-card").forEach((card) => {
    card.classList.remove("selected");
  });

  // Add selection to clicked card
  document
    .querySelector(`[data-inspector-id="${inspector.id}"]`)
    .classList.add("selected");

  selectedInspector = inspector;

  // Update assigned inspector display
  const assignedDisplay = document.getElementById("assignedInspectorDisplay");
  const assignedInfo = document.getElementById("assignedInspectorInfo");

  assignedDisplay.style.display = "block";
  assignedInfo.innerHTML = `
                <div class="d-flex align-items-center gap-2">
                    <div class="inspector-avatar" style="width: 30px; height: 30px; font-size: 0.8em;">${inspector.fullname[0]}</div>
                    <div>
                        <strong>${inspector.fullname}</strong>
                        <div class="text-muted small">${inspector.status}</div>
                    </div>
                </div>
            `;
}

async function renderUpcomingInspections() {
  const tbody = document.getElementById("upcomingInspectionsBody");
  tbody.innerHTML = "";
  const res = await fetch("../../utility/adminGetInspection.php")
  const j = await res.json()
  console.log(j)
  upcomingInspections = j
  upcomingInspections.forEach((inspection) => {
    const row = createInspectionRow(inspection);
    tbody.appendChild(row);
  });
}

function createInspectionRow(inspection) {
  const row = document.createElement("tr");

  const priorityClass =
    {
      high: "priority-high",
      medium: "priority-medium",
      low: "priority-low",
    }[inspection.priority] || "priority-medium";

  const statusClass =
    {
      Pending: "status-pending",
      Approved: "status-approved",
      Completed: "status-completed",
    }[inspection.status] || "status-pending";

  // Format date and time
  const dateTime = new Date(inspection.dateTime);
  const formattedDateTime =
    dateTime.toLocaleDateString() +
    " - " +
    dateTime.toLocaleTimeString([], {
      hour: "2-digit",
      minute: "2-digit",
    });

  row.innerHTML = `
                <td>${inspection.establishment}</td>
                <td>${inspection.type}</td>
                <td>${formattedDateTime}</td>
                <td>${inspection.inspector}</td>
                <td><span class="priority-badge ${priorityClass}">${inspection.priority}</span></td>
                <td><span class="status-badge ${statusClass}">${inspection.status}</span></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-warning btn-sm" onclick="editInspection(${inspection.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="deleteInspection(${inspection.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;

  return row;
}

function renderInspectorSchedule() {
  // Filter inspections for today
  const today = new Date().toISOString().split("T")[0];
  const todayInspections = upcomingInspections.filter((inspection) =>
    inspection.dateTime.startsWith(today)
  );
}

function setupEventListeners() {
  // Schedule inspection button
  document
    .getElementById("scheduleInspectionBtn")
    .addEventListener("click", scheduleInspection);

  // Reset form button
  document.getElementById("resetFormBtn").addEventListener("click", resetForm);

  // Update inspection button
  document
    .getElementById("updateInspectionBtn")
    .addEventListener("click", updateInspection);
  
  // Add listeners for date and time slot changes to update inspector list
  const inspectionDateInput = document.getElementById("inspectionDate");
  const timeSlotInput = document.getElementById("timeSlot");
  
  if (inspectionDateInput) {
    inspectionDateInput.addEventListener("change", () => {
      renderInspectors(inspectors);
    });
  }
  
  if (timeSlotInput) {
    timeSlotInput.addEventListener("change", () => {
      renderInspectors(inspectors);
    });
  }
}

async function scheduleInspection() {
  const form = document.getElementById("newInspectionForm");

  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  if (!selectedInspector) {
    showAlert("Please select an inspector first.", "warning");
    return;
  }

  const establishmentSelect = document.getElementById("establishment");
  const establishmentText =
    establishmentSelect.options[establishmentSelect.selectedIndex].text;

  const newInspection = {
    id: upcomingInspections.length + 1,
    establishment: establishmentText,
    establishmentId: document.getElementById("establishment").value,
    type: document.getElementById("inspectionType").value,
    dateTime: document.getElementById("inspectionDate").value,
    time_slot: document.getElementById("timeSlot").value,
    inspector: selectedInspector.name,
    inspectorId: selectedInspector.id,
    priority: document.getElementById("priorityLevel").value,
    status: "status-pending",
    notes: document.getElementById("inspectionNotes").value,
  };
  const res = await fetch("../../utility/adminAddInspection.php",{
    method:"POST",
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify(newInspection)
  })
  const j = await res.json()
  console.log(j)
  upcomingInspections.unshift(newInspection);
  renderUpcomingInspections();
  renderInspectorSchedule();
  resetForm();

  showAlert("Inspection scheduled successfully!", "success"); 
  initializeMap()
}

function resetForm() {
  document.getElementById("newInspectionForm").reset();
  selectedInspector = null;
  document.getElementById("assignedInspectorDisplay").style.display = "none";

  // Remove inspector selections
  document.querySelectorAll(".inspector-card").forEach((card) => {
    card.classList.remove("selected");
  });
}


function editInspection(id) {
  const inspection = upcomingInspections.find((i) => i.id === id);
  if (!inspection) return;
  let inspectorSelect = document.getElementById("editInspector");
  inspectorSelect.innerHTML = ""
  inspectors.forEach(e=>{
    const node = document.createElement("option")
    if(e.fullname == inspection.inspector) node.selected = true
    node.value = e.id
    node.textContent = e.fullname
    inspectorSelect.appendChild(node)
  })
  // Populate edit form
  document.getElementById("editInspectionId").value = inspection.id;
  document.getElementById("editEstablishment").value =
    inspection.establishment;
  document.getElementById("editInspectionType").value =
    inspection.type.toLowerCase();
  document.getElementById("editInspectionDate").value = inspection.dateTime;
  document.getElementById("editPriority").value =
    inspection.priority.toLowerCase();
  document.getElementById("editStatus").value = inspection.status.toLowerCase();
  document.getElementById("editNotes").value = inspection.notes || "";

  // Show modal
  new bootstrap.Modal(document.getElementById("editInspectionModal")).show();
}

async function updateInspection() {
  const form = document.getElementById("editInspectionForm");

  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  const id = parseInt(document.getElementById("editInspectionId").value);
  
  const inspectionIndex = upcomingInspections.findIndex((i) => i.id == id);

  if (inspectionIndex === -1) return;

  const establishmentSelect = document.getElementById("editEstablishment");
  let s = upcomingInspections.filter(e=> e.id == id )
  console.log('thisis')
  const establishmentText =
    s.establishment

  const inspectorSelect = document.getElementById("editInspector");
  inspectorSelect.innerHTML = ""
  inspectors.forEach(e=>{
    const node = document.createElement("option")
    node.value = e.id
    node.textContent = e.fullname
    inspectorSelect.appendChild(node)
  })
  const inspectorText =
    inspectorSelect.options[inspectorSelect.selectedIndex].text;

  upcomingInspections[inspectionIndex] = {
    ...upcomingInspections[inspectionIndex],
    establishment: establishmentText,
    establishmentId: id,
    type: document.getElementById("editInspectionType").value,
    dateTime: document.getElementById("editInspectionDate").value,
    inspector: inspectorText,
    inspectorId: document.getElementById("editInspector").value,
    priority: document.getElementById("editPriority").value,
    status: document.getElementById("editStatus").value,
    notes: document.getElementById("editNotes").value,
  };
  const json = {
     establishment: establishmentText,
    type: document.getElementById("editInspectionType").value,
    dateTime: document.getElementById("editInspectionDate").value,
    inspector: inspectorText,
    inspectorId: document.getElementById("editInspector").value,
    priority: document.getElementById("editPriority").value,
    status: document.getElementById("editStatus").value,
    notes: document.getElementById("editNotes").value,
    inspectionId : id
  }

  const r = await fetch("../../utility/adminUpdateInspection.php",{
    method:"POST",
    headers:{'Content-Type':"application/json"},
    body:JSON.stringify(json)
  })
  const c = await r.json()
  console.log(c)
  renderUpcomingInspections();
  renderInspectorSchedule();

  // Close modal
  bootstrap.Modal.getInstance(
    document.getElementById("editInspectionModal")
  ).hide();

  showAlert("Inspection updated successfully!", "success");
}

function deleteInspection(id) {
  if (confirm("Are you sure you want to delete this inspection?")) {
    const inspectionIndex = upcomingInspections.findIndex((i) => i.id === id);
    if (inspectionIndex !== -1) {
      upcomingInspections.splice(inspectionIndex, 1);
      renderUpcomingInspections();
      renderInspectorSchedule();
      showAlert("Inspection deleted successfully!", "success");
    }
  }
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

// Set default date to today
document.addEventListener("DOMContentLoaded", function () {
  const now = new Date();
  const tomorrow = new Date(now);
  tomorrow.setDate(tomorrow.getDate() + 1);
  tomorrow.setHours(9, 0, 0, 0);

  const dateTimeString = tomorrow.toISOString().slice(0, 16);
  document.getElementById("inspectionDate").value = dateTimeString;
});

// Search filter
document.getElementById("inspectorSearch").addEventListener("input", (e) => {
  const query = e.target.value.toLowerCase();
  console.log("Query:", query);

  const filtered = inspectors.filter((inspector) =>
    inspector.name.toLowerCase().includes(query)
  );
  console.log("Filtered:", filtered);
  renderInspectors(filtered);
});
