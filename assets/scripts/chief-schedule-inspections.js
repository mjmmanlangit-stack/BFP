// Chief Module - Schedule Inspections with Two Inspectors
let inspectors = [];
let upcomingInspections = [];
let selectedInspector1 = null;
let selectedInspector2 = null;
let map = null;

// Initialize page
document.addEventListener("DOMContentLoaded", function () {
  initializeMap();
  renderInspectors();
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

  const establishments = j;
  establishments.forEach((est) => {
    const marker = L.marker([est.lat, est.lng]).addTo(map);
    marker.bindPopup(`<strong>${est.name}</strong><br>Type: ${est.type}`);
  });
}

async function renderInspectors() {
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
  console.log("Available Inspectors:", json);
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
  // Determine which inspector slot to fill
  if (!selectedInspector1) {
    // Select first inspector
    selectedInspector1 = inspector;
    document
      .querySelector(`[data-inspector-id="${inspector.id}"]`)
      .classList.add("selected");
  } else if (selectedInspector1.id === inspector.id) {
    // Deselect first inspector
    selectedInspector1 = null;
    document
      .querySelector(`[data-inspector-id="${inspector.id}"]`)
      .classList.remove("selected");
  } else if (!selectedInspector2) {
    // Select second inspector
    selectedInspector2 = inspector;
    document
      .querySelector(`[data-inspector-id="${inspector.id}"]`)
      .classList.add("selected");
  } else if (selectedInspector2.id === inspector.id) {
    // Deselect second inspector
    selectedInspector2 = null;
    document
      .querySelector(`[data-inspector-id="${inspector.id}"]`)
      .classList.remove("selected");
  } else {
    // Both slots filled, replace second inspector
    document
      .querySelector(`[data-inspector-id="${selectedInspector2.id}"]`)
      .classList.remove("selected");
    selectedInspector2 = inspector;
    document
      .querySelector(`[data-inspector-id="${inspector.id}"]`)
      .classList.add("selected");
  }

  // Update assigned inspectors display
  updateAssignedInspectorsDisplay();
}

function updateAssignedInspectorsDisplay() {
  const assignedDisplay = document.getElementById("assignedInspectorsDisplay");
  const assignedInfo = document.getElementById("assignedInspectorsInfo");

  if (selectedInspector1 || selectedInspector2) {
    assignedDisplay.style.display = "block";
    let html = "<div class='d-flex flex-column gap-2'>";
    
    if (selectedInspector1) {
      html += `<div class="d-flex align-items-center gap-2 p-2 border rounded" style="background:#f0f8ff;">
                <div class="inspector-avatar" style="width: 30px; height: 30px; font-size: 0.8em; background: #007bff; color: white;">${selectedInspector1.fullname[0]}</div>
                <div>
                  <strong>Inspector 1: ${selectedInspector1.fullname}</strong>
                  <div class="text-muted small">${selectedInspector1.status || 'Available'}</div>
                </div>
              </div>`;
    }
    
    if (selectedInspector2) {
      html += `<div class="d-flex align-items-center gap-2 p-2 border rounded" style="background:#f0fff0;">
                <div class="inspector-avatar" style="width: 30px; height: 30px; font-size: 0.8em; background: #28a745; color: white;">${selectedInspector2.fullname[0]}</div>
                <div>
                  <strong>Inspector 2: ${selectedInspector2.fullname}</strong>
                  <div class="text-muted small">${selectedInspector2.status || 'Available'}</div>
                </div>
              </div>`;
    }
    
    html += "</div>";
    assignedInfo.innerHTML = html;
  } else {
    assignedDisplay.style.display = "none";
  }
}

async function renderUpcomingInspections() {
  const tbody = document.getElementById("upcomingInspectionsBody");
  tbody.innerHTML = "";
  const res = await fetch("../../utility/adminGetInspection.php");
  const j = await res.json();
  console.log("Upcoming Inspections:", j);
  upcomingInspections = j;
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

  // Add listeners for date and time slot changes to update inspector list
  const inspectionDateInput = document.getElementById("inspectionDate");
  const timeSlotInput = document.getElementById("timeSlot");
  
  if (inspectionDateInput) {
    inspectionDateInput.addEventListener("change", () => {
      // Clear previous selections when date changes
      selectedInspector1 = null;
      selectedInspector2 = null;
      updateAssignedInspectorsDisplay();
      renderInspectors();
    });
  }
  
  if (timeSlotInput) {
    timeSlotInput.addEventListener("change", () => {
      // Clear previous selections when time slot changes
      selectedInspector1 = null;
      selectedInspector2 = null;
      updateAssignedInspectorsDisplay();
      renderInspectors();
    });
  }
}

async function scheduleInspection() {
  const form = document.getElementById("newInspectionForm");

  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  // Validate that both inspectors are selected
  if (!selectedInspector1 || !selectedInspector2) {
    showAlert("Please select both inspectors.", "warning");
    return;
  }

  const establishmentSelect = document.getElementById("establishment");
  const establishmentText =
    establishmentSelect.options[establishmentSelect.selectedIndex].text;

  // Extract time from the time slot (e.g., "09:00-10:00" -> use start time "09:00")
  const timeSlot = document.getElementById("timeSlot").value;
  const inspectionTime = timeSlot ? timeSlot.split('-')[0] : "08:00";

  const newInspection = {
    establishment_id: document.getElementById("establishment").value,
    inspector1_id: selectedInspector1.id,
    inspector2_id: selectedInspector2.id,
    inspection_date: document.getElementById("inspectionDate").value,
    inspection_time: inspectionTime,
    time_slot: timeSlot,
    inspection_type: document.getElementById("inspectionType").value,
    priority_level: document.getElementById("priorityLevel").value,
    notes: document.getElementById("inspectionNotes").value,
  };

  try {
    const res = await fetch("../../utility/scheduleInspection.php", {
      method: "POST",
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(newInspection)
    });
    
    const j = await res.json();
    console.log("Schedule Response:", j);
    
    if (j.error) {
      showAlert(j.error, "danger");
      return;
    }
    
    // Success
    renderUpcomingInspections();
    renderInspectorSchedule();
    resetForm();
    showAlert("Inspection scheduled successfully!", "success");
    initializeMap();
  } catch (error) {
    console.error("Error:", error);
    showAlert("Error scheduling inspection: " + error.message, "danger");
  }
}

function resetForm() {
  document.getElementById("newInspectionForm").reset();
  selectedInspector1 = null;
  selectedInspector2 = null;
  updateAssignedInspectorsDisplay();

  // Remove inspector selections
  document.querySelectorAll(".inspector-card").forEach((card) => {
    card.classList.remove("selected");
  });
}

function editInspection(id) {
  const inspection = upcomingInspections.find((i) => i.id === id);
  if (!inspection) return;

  // Populate edit form
  document.getElementById("editInspectionId").value = inspection.id;
  document.getElementById("editEstablishment").value = inspection.establishment;
  document.getElementById("editInspectionType").value = inspection.type.toLowerCase();
  document.getElementById("editInspectionDate").value = inspection.dateTime;
  document.getElementById("editPriority").value = inspection.priority.toLowerCase();
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

  const insp = upcomingInspections[inspectionIndex];

  const json = {
    establishment: document.getElementById("editEstablishment").value,
    type: document.getElementById("editInspectionType").value,
    dateTime: document.getElementById("editInspectionDate").value,
    priority: document.getElementById("editPriority").value,
    status: document.getElementById("editStatus").value,
    notes: document.getElementById("editNotes").value,
    inspectionId: id
  };

  const r = await fetch("../../utility/adminUpdateInspection.php", {
    method: "POST",
    headers: { 'Content-Type': "application/json" },
    body: JSON.stringify(json)
  });
  
  const c = await r.json();
  console.log(c);
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

// Search filter
document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("inspectorSearch");
  if (searchInput) {
    searchInput.addEventListener("input", (e) => {
      const query = e.target.value.toLowerCase();
      console.log("Search Query:", query);

      const filtered = inspectors.filter((inspector) =>
        inspector.fullname.toLowerCase().includes(query)
      );
      console.log("Filtered Inspectors:", filtered);
      
      // Re-render inspector list with filtered results
      const inspectorList = document.getElementById("inspectorList");
      inspectorList.innerHTML = "";

      if (filtered.length === 0) {
        inspectorList.innerHTML = "<p>No inspectors found.</p>";
        return;
      }

      filtered.forEach((inspector) => {
        const inspectorCard = document.createElement("div");
        inspectorCard.className = "inspector-card";
        
        // Add 'selected' class if this inspector is already selected
        if ((selectedInspector1 && selectedInspector1.id === inspector.id) ||
            (selectedInspector2 && selectedInspector2.id === inspector.id)) {
          inspectorCard.classList.add("selected");
        }
        
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
    });
  }
});
