// Live inspection data fetched from API
let inspectionData = [];
let filteredData   = [];
let map;

// Map API response to display format
function mapApiData(item) {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const iDate = item.inspectionDate ? new Date(item.inspectionDate) : null;

  let status = "scheduled";
  if (item.viewed) {
    status = "completed";
  } else if (iDate && iDate < today) {
    status = "overdue";
  } else if (iDate && iDate >= today) {
    status = "scheduled";
  } else {
    status = "pending";
  }

  return {
    id:             item.id,
    establishment:  item.businessName || "Unknown",
    type:           item.businessType || "—",
    address:        item.address || "—",
    inspectionDate: item.inspectionDate ? item.inspectionDate.split(" ")[0] : "",
    riskLevel:      (item.priorityLevel || "medium").toLowerCase(),
    status:         status,
    lat:            item.latitude  || 13.6248,
    lng:            item.longitude || 124.2363,
    inspectionType: item.inspectionType,
    notes:          item.notes,
    inspector1:     item.inspector1,
    inspector2:     item.inspector2,
    reportId:       item.reportId,
    orderNo:        item.inspectionOrderNo,
    regNo:          item.regNo
  };
}

// Fetch inspections from backend
async function loadInspections() {
  const tbody = document.getElementById("inspectionTableBody");
  if (tbody) tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span>Loading inspections...</td></tr>';

  try {
    const res = await fetch("../../utility/getInspectorAssignments.php");
    const j   = await res.json();

    if (j.success) {
      inspectionData = j.inspections.map(mapApiData);
      filteredData   = [...inspectionData];
    } else {
      console.error("API error:", j.message);
      inspectionData = [];
      filteredData   = [];
    }
  } catch (err) {
    console.error("Fetch error:", err);
    inspectionData = [];
    filteredData   = [];
  }

  renderTable();
}

// Initialize the page
document.addEventListener("DOMContentLoaded", function () {
  loadInspections();
  setupDateDefaults();
});

// Render table data
function renderTable() {
  const tbody = document.getElementById("inspectionTableBody");
  tbody.innerHTML = "";

  filteredData.forEach((item) => {
    const row = document.createElement("tr");
    row.innerHTML = `
                    <td><strong>${item.establishment}</strong></td>
                    <td>${item.type}</td>
                    <td>${item.address}</td>
                    <td>${formatDate(item.inspectionDate)}</td>
                    <td><span class="risk-${item.riskLevel}">${
      item.riskLevel.charAt(0).toUpperCase() + item.riskLevel.slice(1)
    }</span></td>
                    <td><span class="status-badge status-${item.status}">${
      item.status.charAt(0).toUpperCase() + item.status.slice(1)
    }</span></td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                        ${item.status === "completed" ? `` : `<button class="btn btn-action btn-report" onclick="openReportModal(${item.id})">
                                <i class="fas fa-file-alt"></i> Report
                            </button>`}
                            ${item.status === "completed" ? `` : `
                                <button class="btn btn-action btn-map" onclick="openMapModal(${
                              item.id
                            })">
                                <i class="fas fa-map"></i> Map
                            </button>`}
                            ${item.status === "completed" ? `<button class="btn btn-action btn-view" onclick="openReportModal(${
                              item.id
                            })">
                                <i class="fas fa-file-alt"></i> View
                            </button>` : ''}
                            
                            
                        </div>
                    </td>
                `;
    tbody.appendChild(row);
  });
}

// Format date
function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
  });
}

// Setup default date values
function setupDateDefaults() {
  const today = new Date();
  const fromDate = new Date(today);
  fromDate.setDate(today.getDate() - 30);

  document.getElementById("fromDate").value = fromDate
    .toISOString()
    .split("T")[0];
  document.getElementById("toDate").value = today.toISOString().split("T")[0];
}

// Apply filters
function applyFilters() {
  const statusFilter = document.getElementById("statusFilter").value;
  const riskFilter = document.getElementById("riskFilter").value;
  const fromDate = document.getElementById("fromDate").value;
  const toDate = document.getElementById("toDate").value;

  filteredData = inspectionData.filter((item) => {
    const matchesStatus = !statusFilter || item.status === statusFilter;
    const matchesRisk = !riskFilter || item.riskLevel === riskFilter;
    const matchesDate =
      (!fromDate || item.inspectionDate >= fromDate) &&
      (!toDate || item.inspectionDate <= toDate);

    return matchesStatus && matchesRisk && matchesDate;
  });

  renderTable();
}

// Export data to CSV
function exportData() {
  const headers = [
    "Establishment",
    "Type",
    "Address",
    "Inspection Date",
    "Risk Level",
    "Status",
  ];
  const csvContent = [
    headers.join(","),
    ...filteredData.map((item) =>
      [
        `"${item.establishment}"`,
        `"${item.type}"`,
        `"${item.address}"`,
        item.inspectionDate,
        item.riskLevel,
        item.status,
      ].join(",")
    ),
  ].join("\n");

  const blob = new Blob([csvContent], { type: "text/csv" });
  const url = window.URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = url;
  link.download = `BFP_Inspections_${
    new Date().toISOString().split("T")[0]
  }.csv`;
  link.click();
  window.URL.revokeObjectURL(url);
}

// Open map modal
function openMapModal(id) {
  const item = inspectionData.find((i) => i.id === id);
  if (!item) return;

  document.getElementById("mapModalTitle").textContent = item.establishment;
  document.getElementById("locationInfo").innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Address:</strong> ${item.address}</p>
                        <p><strong>Type:</strong> ${item.type}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Risk Level:</strong> <span class="risk-${
                          item.riskLevel
                        }">${item.riskLevel.toUpperCase()}</span></p>
                        <p><strong>Status:</strong> <span class="status-badge status-${
                          item.status
                        }">${
    item.status.charAt(0).toUpperCase() + item.status.slice(1)
  }</span></p>
                    </div>
                </div>
            `;

  const modal = new bootstrap.Modal(document.getElementById("mapModal"));
  modal.show();

  // Initialize map when modal is shown
  document
    .getElementById("mapModal")
    .addEventListener("shown.bs.modal", function () {
      if (map) {
        map.remove();
      }

      map = L.map("inspectionMap").setView([item.lat, item.lng], 15);

      L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "© OpenStreetMap contributors",
      }).addTo(map);

      const marker = L.marker([item.lat, item.lng]).addTo(map);
      marker
        .bindPopup(
          `
                    <div class="text-center">
                        <h6>${item.establishment}</h6>
                        <p>${item.address}</p>
                    </div>
                `
        )
        .openPopup();
    });
}

// Open report modal
function openReportModal(id) {
  const item = inspectionData.find((i) => i.id === id);
  if (!item) return;

  document.getElementById(
    "reportModalTitle"
  ).textContent = `${item.establishment} - Inspection Report`;
  document.getElementById("reportContent").innerHTML =
    generateReportContent(item);

  const modal = new bootstrap.Modal(document.getElementById("reportModal"));
  modal.show();
}

// Generate report content
function generateReportContent(item) {
  return `
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <h5>Inspection Details</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Establishment:</strong> ${
                                  item.establishment
                                }</p>
                                <p><strong>Type:</strong> ${item.type}</p>
                                <p><strong>Address:</strong> ${item.address}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Inspection Date:</strong> ${formatDate(
                                  item.inspectionDate
                                )}</p>
                                <p><strong>Risk Level:</strong> <span class="risk-${
                                  item.riskLevel
                                }">${item.riskLevel.toUpperCase()}</span></p>
                                <p><strong>Status:</strong> <span class="status-badge status-${
                                  item.status
                                }">${
    item.status.charAt(0).toUpperCase() + item.status.slice(1)
  }</span></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <h5>Inspection Checklist</h5>
                        <hr>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" ${
                              item.status === "completed"
                                ? "checked disabled"
                                : ""
                            }>
                            <label class="form-check-label">Fire exits properly marked and accessible</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" ${
                              item.status === "completed"
                                ? "checked disabled"
                                : ""
                            }>
                            <label class="form-check-label">Fire extinguishers in place and functional</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" ${
                              item.status === "completed"
                                ? "checked disabled"
                                : ""
                            }>
                            <label class="form-check-label">Emergency lighting system operational</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" ${
                              item.status === "completed"
                                ? "checked disabled"
                                : ""
                            }>
                            <label class="form-check-label">Fire alarm system tested and working</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" ${
                              item.status === "completed"
                                ? "checked disabled"
                                : ""
                            }>
                            <label class="form-check-label">Electrical installations comply with safety standards</label>
                        </div>
                    </div>
                    ${
                      item.status === "completed"
                        ? `
                        <div class="col-md-12 mt-3">
                            <h6>Inspector Notes</h6>
                            <div class="alert alert-info">
                                Inspection completed successfully. All fire safety requirements met.
                            </div>
                        </div>
                    `
                        : ""
                    }
                </div>
            `;
}

// Download report
function downloadReport() {
  // Simulate report download
  const link = document.createElement("a");
  link.href =
    "data:text/plain;charset=utf-8,BFP%20Inspection%20Report%0A%0AThis%20is%20a%20sample%20inspection%20report.";
  link.download = "BFP_Inspection_Report.txt";
  link.click();
}

// View inspection (for completed inspections)
function viewInspection(id) {
  const item = inspectionData.find((i) => i.id === id);
  alert(`Viewing detailed inspection for: ${item.establishment}`);
}
