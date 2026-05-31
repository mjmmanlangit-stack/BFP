/* =========================================================
   reports.js  –  Live reports dashboard (admin)
   Fetches data from ../../utility/getReportData.php
   ========================================================= */

let complianceChartInst = null;
let inspectionChartInst = null;
let violationsChartInst = null;

/* ---------- CSV export helper ---------- */
function exportCSV(rows, filename) {
  const csv = rows.map(r => r.map(c => `"${String(c).replace(/"/g,'""')}"`).join(',')).join('\n');
  const blob = new Blob([csv], { type: 'text/csv' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = filename;
  a.click();
}

/* ---------- Load & render live data ---------- */
async function loadReportData() {
  try {
    const res = await fetch('../../utility/getReportData.php');
    const d   = await res.json();
    if (!d.success) { console.error('getReportData:', d.message); return; }

    renderComplianceChart(d.complianceDist);
    renderInspectionChart(d.monthlyTrend);
    renderViolationsChart(d.monthlyTrend);
  } catch (e) { console.error('Reports load error:', e); }
}

/* ---------- Compliance doughnut ---------- */
function renderComplianceChart(dist) {
  const ctx = document.getElementById('complianceChart');
  if (!ctx) return;
  if (complianceChartInst) complianceChartInst.destroy();
  complianceChartInst = new Chart(ctx.getContext('2d'), {
    type: 'doughnut',
    data: {
      labels: ['Compliant', 'Partially Compliant', 'Non-Compliant'],
      datasets: [{
        data: [
          dist.compliant            || 0,
          dist.partially_compliant  || 0,
          dist.non_compliant        || 0
        ],
        backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      aspectRatio: 2,
      plugins: { legend: { position: 'bottom' } }
    }
  });
}

/* ---------- Inspection activity bar ---------- */
function renderInspectionChart(trend) {
  const ctx = document.getElementById('inspectionChart');
  if (!ctx) return;
  if (inspectionChartInst) inspectionChartInst.destroy();

  const labels    = trend.map(t => t.month);
  const completed = trend.map(t => parseInt(t.completed) || 0);

  inspectionChartInst = new Chart(ctx.getContext('2d'), {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Completed Inspections',
        data: completed,
        backgroundColor: '#dc3545',
        borderRadius: 5
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      aspectRatio: 2,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } }
    }
  });
}

/* ---------- Stacked monthly totals bar ---------- */
function renderViolationsChart(trend) {
  const ctx = document.getElementById('violationsChart');
  if (!ctx) return;
  if (violationsChartInst) violationsChartInst.destroy();

  const labels    = trend.map(t => t.month);
  const completed = trend.map(t => parseInt(t.completed) || 0);
  const scheduled = trend.map(t => parseInt(t.scheduled) || 0);

  violationsChartInst = new Chart(ctx.getContext('2d'), {
    type: 'bar',
    data: {
      labels,
      datasets: [
        { label: 'Completed', data: completed, backgroundColor: '#28a745' },
        { label: 'Scheduled', data: scheduled, backgroundColor: '#ffc107' }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      aspectRatio: 2,
      plugins: { legend: { display: true, position: 'bottom' } },
      scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } }
    }
  });
}

/* ---------- Modal detail – live data from API ---------- */
// Dynamic establishment data loaded from backend
let establishmentData = [];

// Fetch establishment data for modal details
async function loadEstablishmentData() {
  try {
    const res = await fetch('../../utility/adminGetEstablishment.php');
    const data = await res.json();
    if (Array.isArray(data)) {
      establishmentData = data.map((e, idx) => ({
        id: e.id || idx + 1,
        name: e.name || 'N/A',
        type: e.type || 'N/A',
        lastInspection: e.lastInspection ? new Date(e.lastInspection).toLocaleDateString('en-US', {year:'numeric', month:'long', day:'numeric'}) : 'N/A',
        inspector: 'N/A',
        status: e.status || 'Pending',
        violations: 0,
        address: e.location || e.address || 'N/A',
        contact: e.contact || 'N/A',
        violationsList: []
      }));
    }
  } catch (e) { console.error('Error loading establishment data:', e); }
}

let currentPage = 1;
const recordsPerPage = 5;
let filteredData = [...establishmentData];

// Function to show establishment details in modal
function showDetails(establishmentName) {
  const data = establishmentData[establishmentName];
  if (!data) return;

  document.getElementById("modalEstablishmentName").textContent =
    establishmentName;
  document.getElementById("modalType").textContent = data.type;
  document.getElementById("modalAddress").textContent = data.address;
  document.getElementById("modalOwner").textContent = data.owner;
  document.getElementById("modalContact").textContent = data.contact;
  document.getElementById("modalLastInspection").textContent =
    data.lastInspection;
  document.getElementById("modalInspector").textContent = data.inspector;
  document.getElementById("modalNextInspection").textContent =
    data.nextInspection;
  document.getElementById("modalPermitExpiry").textContent = data.permitExpiry;

  // Status with appropriate styling
  const statusElement = document.getElementById("modalStatus");
  statusElement.innerHTML = `<span class="status-badge status-${data.status
    .toLowerCase()
    .replace("-", "-")}">${data.status}</span>`;

  // Violations
  const violationsContainer = document.getElementById("modalViolations");
  if (data.violations.length === 0) {
    violationsContainer.innerHTML =
      '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>No violations found</div>';
  } else {
    let violationsHTML = "";
    data.violations.forEach((violation, index) => {
      const severityClass =
        violation.severity === "High"
          ? "danger"
          : violation.severity === "Medium"
          ? "warning"
          : "info";
      violationsHTML += `
                        <div class="alert alert-${severityClass}">
                            <strong>${violation.type}:</strong> ${violation.description}
                            <span class="badge bg-${severityClass} float-end">${violation.severity}</span>
                        </div>
                    `;
    });
    violationsContainer.innerHTML = violationsHTML;
  }
}

/* ---------- Bootstrap ---------- */
document.addEventListener("DOMContentLoaded", function () {
  if (typeof Chart === "undefined") {
    console.error("Chart.js not loaded");
    return;
  }
  loadReportData();
  loadEstablishmentData();
});

// Filter functionality
// document.getElementById("reportType").addEventListener("change", function () {
  // Simulate report generation based on selected type
  // console.log("Report type changed to:", this.value);
// });

// document.getElementById("timePeriod").addEventListener("change", function () {
  // Update charts based on time period
  // console.log("Time period changed to:", this.value);
// });

// Export functionality
// document.querySelector(".btn-success").addEventListener("click", function () {
  // alert("Exporting report to PDF...");
// });

// Reset filters functionality
// document.querySelector(".btn-secondary").addEventListener("click", function () {
  // document.getElementById("reportType").selectedIndex = 0;
  // document.getElementById("timePeriod").selectedIndex = 0;
  // document.getElementById("establishmentType").selectedIndex = 0;
  // document.getElementById("complianceStatus").selectedIndex = 0;
  // document.getElementById("inspector").selectedIndex = 0;
// });

// Generate report functionality
// document.querySelector(".btn-primary").addEventListener("click", function () {
//   alert("Generating report with current filters...");
// });

// Populate table
// function populateTable() {
//   const tbody = document.getElementById("tableBody");
//   const start = (currentPage - 1) * recordsPerPage;
//   const end = start + recordsPerPage;
//   const pageData = filteredData.slice(start, end);
//   tbody.innerHTML = "";

//   pageData.forEach((item) => {
//     const row = document.createElement("tr");

//     let statusClass = "";
//     let statusText = item.status;
//     switch (item.status) {
//       case "Compliant":
//         statusClass = "status-compliant";
//         break;
//       case "Non-Compliant":
//         statusClass = "status-non-compliant";
//         break;
//       case "Pending":
//         statusClass = "status-pending";
//         break;
//     }

//     row.innerHTML = `
//                     <td>${item.name}</td>
//                     <td>${item.type}</td>
//                     <td>${item.lastInspection}</td>
//                     <td>${item.inspector}</td>
//                     <td><span class="status-badge ${statusClass}">${statusText}</span></td>
//                     <td><span class="badge bg-danger">${item.violations}</span></td>
//                     <td>
//                         <button class="btn btn-sm btn-outline-primary" onclick="viewDetails(${item.id})">
//                             <i class="fas fa-eye"></i> View
//                         </button>
//                     </td>
//                 `;

//     tbody.appendChild(row);
//   });

//   updatePaginationInfo();
//   generatePagination();
// }

// View details modal
      function viewDetails(id) {
        const item = establishmentData.find((data) => data.id === id);
        if (!item) return;

        document.getElementById("modalEstablishmentName").textContent =
          item.name;
        document.getElementById("modalEstablishmentType").textContent =
          item.type;
        document.getElementById("modalAddress").textContent = item.address;
        document.getElementById("modalContact").textContent = item.contact;
        document.getElementById("modalLastInspection").textContent =
          item.lastInspection;
        document.getElementById("modalInspector").textContent = item.inspector;
        document.getElementById("modalViolations").textContent =
          item.violations;

        // Status with styling
        const statusSpan = document.getElementById("modalStatus");
        let statusClass = "";
        switch (item.status) {
          case "Compliant":
            statusClass = "status-compliant";
            break;
          case "Non-Compliant":
            statusClass = "status-non-compliant";
            break;
          case "Pending":
            statusClass = "status-pending";
            break;
        }
        statusSpan.innerHTML = `<span class="status-badge ${statusClass}">${item.status}</span>`;

        // Violations list
        const violationsList = document.getElementById("modalViolationsList");
        violationsList.innerHTML = "";

        if (item.violationsList.length === 0) {
          violationsList.innerHTML =
            '<li class="list-group-item text-muted">No violations found</li>';
        } else {
          item.violationsList.forEach((violation) => {
            const li = document.createElement("li");
            li.className =
              "list-group-item d-flex justify-content-between align-items-center";
            li.innerHTML = `
                        ${violation}
                        <span class="badge bg-danger">High</span>
                    `;
            violationsList.appendChild(li);
          });
        }

        const modal = new bootstrap.Modal(
          document.getElementById("viewDetailsModal")
        );
        modal.show();
      }

function updatePaginationInfo() {
  const start = (currentPage - 1) * recordsPerPage + 1;
  const end = Math.min(currentPage * recordsPerPage, filteredData.length);

  document.getElementById("showingStart").textContent = start;
  document.getElementById("showingEnd").textContent = end;
  document.getElementById("totalRecords").textContent = filteredData.length;
}

// Generate pagination
function generatePagination() {
  const totalPages = Math.ceil(filteredData.length / recordsPerPage);
  const pagination = document.getElementById("pagination");

  pagination.innerHTML = "";

  // Previous button
  const prevLi = document.createElement("li");
  prevLi.className = `page-item ${currentPage === 1 ? "disabled" : ""}`;
  prevLi.innerHTML = `<button class="page-link" onclick="changePage(${
    currentPage - 1
  })">&laquo;</button>`;
  pagination.appendChild(prevLi);

  // Page numbers
  for (let i = 1; i <= totalPages; i++) {
    const li = document.createElement("li");
    li.className = `page-item ${i === currentPage ? "active" : ""}`;
    li.innerHTML = `<button class="page-link" href="#" onclick="changePage(${i})">${i}</button>`;
    pagination.appendChild(li);
  }

  // Next button
  const nextLi = document.createElement("li");
  nextLi.className = `page-item ${
    currentPage === totalPages ? "disabled" : ""
  }`;
  nextLi.innerHTML = `<button class="page-link" href="#" onclick="changePage(${
    currentPage + 1
  })">&raquo;</button>`;
  pagination.appendChild(nextLi);
}

// Change page
function changePage(page) {
  const totalPages = Math.ceil(filteredData.length / recordsPerPage);
  if (page < 1 || page > totalPages) return;

  currentPage = page;
  populateTable();
}
