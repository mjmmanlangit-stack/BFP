// Sample data for establishments
const establishmentData = [
  {
    id: 1,
    name: "Virac Public Market",
    type: "Commercial",
    lastInspection: "June 15, 2025",
    inspector: "Juan Dela Cruz",
    status: "Non-Compliant",
    violations: 3,
    address: "Public Market St., Virac, Catanduanes",
    contact: "(054) 123-4567",
    violationsList: [
      "Missing fire extinguisher in meat section",
      "Blocked emergency exit",
      "Inadequate fire alarm system",
    ],
  },
  {
    id: 2,
    name: "Catanduanes State University",
    type: "Institutional",
    lastInspection: "June 10, 2025",
    inspector: "Maria Santos",
    status: "Compliant",
    violations: 0,
    address: "Virac, Catanduanes",
    contact: "(054) 987-6543",
    violationsList: [],
  },
  {
    id: 3,
    name: "Virac Town Center",
    type: "Commercial",
    lastInspection: "June 5, 2025",
    inspector: "Roberto Pacquiao",
    status: "Compliant",
    violations: 0,
    address: "Rizal Street, Virac, Catanduanes",
    contact: "(054) 456-7890",
    violationsList: [],
  },
  {
    id: 4,
    name: "BFP Catanduanes",
    type: "Government",
    lastInspection: "May 28, 2025",
    inspector: "Juan Dela Cruz",
    status: "Pending",
    violations: 1,
    address: "Provincial Capitol Complex, Virac",
    contact: "(054) 321-0987",
    violationsList: ["Expired fire extinguisher in storage room"],
  },
  {
    id: 5,
    name: "Virac Municipal Hall",
    type: "Government",
    lastInspection: "May 20, 2025",
    inspector: "Maria Santos",
    status: "Compliant",
    violations: 0,
    address: "Municipal Complex, Virac",
    contact: "(054) 654-3210",
    violationsList: [],
  },
  {
    id: 6,
    name: "SM Savemore Virac",
    type: "Commercial",
    lastInspection: "May 15, 2025",
    inspector: "Roberto Pacquiao",
    status: "Non-Compliant",
    violations: 2,
    address: "Maharlika Highway, Virac",
    contact: "(054) 789-0123",
    violationsList: [
      "Improper storage of flammable materials",
      "Non-functional smoke detector",
    ],
  },
  {
    id: 7,
    name: "Catanduanes General Hospital",
    type: "Institutional",
    lastInspection: "May 10, 2025",
    inspector: "Juan Dela Cruz",
    status: "Compliant",
    violations: 0,
    address: "Hospital Road, Virac",
    contact: "(054) 234-5678",
    violationsList: [],
  },
  {
    id: 8,
    name: "Virac Central School",
    type: "Institutional",
    lastInspection: "May 5, 2025",
    inspector: "Maria Santos",
    status: "Pending",
    violations: 1,
    address: "Education St., Virac",
    contact: "(054) 567-8901",
    violationsList: ["Missing fire drill documentation"],
  },
  {
    id: 9,
    name: "Petron Gas Station",
    type: "Commercial",
    lastInspection: "April 28, 2025",
    inspector: "Roberto Pacquiao",
    status: "Non-Compliant",
    violations: 4,
    address: "National Highway, Virac",
    contact: "(054) 890-1234",
    violationsList: [
      "Faulty fire suppression system",
      "Inadequate safety signage",
      "Missing spill containment",
      "Expired safety permits",
    ],
  },
  {
    id: 10,
    name: "Gaisano Grand Mall",
    type: "Commercial",
    lastInspection: "April 25, 2025",
    inspector: "Juan Dela Cruz",
    status: "Compliant",
    violations: 0,
    address: "Real St., Virac",
    contact: "(054) 345-6789",
    violationsList: [],
  },
  {
    id: 11,
    name: "Gaisano Grand Mall",
    type: "Commercial",
    lastInspection: "April 25, 2025",
    inspector: "Juan Dela Cruz",
    status: "Compliant",
    violations: 0,
    address: "Real St., Virac",
    contact: "(054) 345-6789",
    violationsList: [],
  },
  {
    id: 12,
    name: "Gaisano Grand Mall",
    type: "Commercial",
    lastInspection: "April 25, 2025",
    inspector: "Juan Dela Cruz",
    status: "Compliant",
    violations: 0,
    address: "Real St., Virac",
    contact: "(054) 345-6789",
    violationsList: [],
  },
];

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

// Initialize Charts
document.addEventListener("DOMContentLoaded", function () {
  // Wait for Chart.js to load
  // populateTable();
  if (typeof Chart === "undefined") {
    console.error("Chart.js not loaded");
    return;
  }

  // Compliance Overview Pie Chart
  const complianceCtx = document
    .getElementById("complianceChart")
    .getContext("2d");
  new Chart(complianceCtx, {
    type: "doughnut",
    data: {
      labels: ["Compliant", "Non-Compliant", "Pending"],
      datasets: [
        {
          data: [65, 20, 15],
          backgroundColor: ["#28a745", "#dc3545", "#ffc107"],
          borderWidth: 0,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      aspectRatio: 2,
      plugins: {
        legend: {
          position: "bottom",
        },
      },
    },
  });

  // Inspection Activity Bar Chart
  const inspectionCtx = document
    .getElementById("inspectionChart")
    .getContext("2d");
  new Chart(inspectionCtx, {
    type: "bar",
    data: {
      labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
      datasets: [
        {
          label: "Completed Inspections",
          data: [45, 52, 48, 61, 55, 67],
          backgroundColor: "#dc3545",
          borderRadius: 5,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      aspectRatio: 2,
      plugins: {
        legend: {
          display: false,
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          max: 80,
        },
      },
    },
  });

  // Violations by Type Bar Chart
  const violationsCtx = document
    .getElementById("violationsChart")
    .getContext("2d");
  new Chart(violationsCtx, {
    type: "bar",
    data: {
      labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
      datasets: [
        {
          label: "Fire Exit Violations",
          data: [8, 12, 6, 15, 10, 12],
          backgroundColor: "#dc3545",
        },
        {
          label: "Equipment Violations",
          data: [5, 8, 4, 10, 7, 8],
          backgroundColor: "#ffc107",
        },
        {
          label: "Electrical Violations",
          data: [3, 5, 2, 7, 4, 5],
          backgroundColor: "#17a2b8",
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      aspectRatio: 2,
      plugins: {
        legend: {
          display: false,
        },
      },
      scales: {
        x: {
          stacked: true,
        },
        y: {
          stacked: true,
          beginAtZero: true,
          max: 40,
        },
      },
    },
  });
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
