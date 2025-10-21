// Sample data
const certificatesData = [
  {
    id: 1,
    certificateNo: "FSIC-2023-0456",
    establishment: "Delicious Restaurant",
    address: "123 Main St, Calamba City",
    issuedDate: "2023-05-20",
    expiryDate: "2024-05-20",
    status: "Active",
    officer: "Fire Inspector John D. Santos",
  },
  {
    id: 2,
    certificateNo: "FSIC-2022-0891",
    establishment: "Juan's Hardware Store",
    address: "456 Commerce Ave, Calamba City",
    issuedDate: "2022-06-15",
    expiryDate: "2023-06-15",
    status: "Expired",
    officer: "Fire Inspector Maria C. Reyes",
  },
  {
    id: 3,
    certificateNo: "FSIC-2023-1023",
    establishment: "Cruz Family Apartment",
    address: "789 Residential St, Calamba City",
    issuedDate: "Pending",
    expiryDate: "Pending",
    status: "Pending",
    officer: "Fire Inspector Roberto L. Garcia",
  },
  {
    id: 4,
    certificateNo: "FSIC-2023-1024",
    establishment: "Cruz Family Apartment",
    address: "789 Residential St, Calamba City",
    issuedDate: "Pending",
    expiryDate: "Pending",
    status: "Pending",
    officer: "Fire Inspector Roberto L. Garcia",
  },
  {
    id: 4,
    certificateNo: "FSIC-2023-1024",
    establishment: "Cruz Family Apartment",
    address: "789 Residential St, Calamba City",
    issuedDate: "Pending",
    expiryDate: "Pending",
    status: "Pending",
    officer: "Fire Inspector Roberto L. Garcia",
  },
  {
    id: 4,
    certificateNo: "FSIC-2023-1024",
    establishment: "Cruz Family Apartment",
    address: "789 Residential St, Calamba City",
    issuedDate: "Pending",
    expiryDate: "Pending",
    status: "Pending",
    officer: "Fire Inspector Roberto L. Garcia",
  },
  {
    id: 4,
    certificateNo: "FSIC-2023-1024",
    establishment: "Cruz Family Apartment",
    address: "789 Residential St, Calamba City",
    issuedDate: "Pending",
    expiryDate: "Pending",
    status: "Pending",
    officer: "Fire Inspector Roberto L. Garcia",
  },
  {
    id: 4,
    certificateNo: "FSIC-2023-1024",
    establishment: "Cruz Family Apartment",
    address: "789 Residential St, Calamba City",
    issuedDate: "Pending",
    expiryDate: "Pending",
    status: "Pending",
    officer: "Fire Inspector Roberto L. Garcia",
  },
  {
    id: 4,
    certificateNo: "FSIC-2023-1024",
    establishment: "Cruz Family Apartment",
    address: "789 Residential St, Calamba City",
    issuedDate: "Pending",
    expiryDate: "Pending",
    status: "Pending",
    officer: "Fire Inspector Roberto L. Garcia",
  },
  {
    id: 4,
    certificateNo: "FSIC-2023-1024",
    establishment: "Cruz Family Apartment",
    address: "789 Residential St, Calamba City",
    issuedDate: "Pending",
    expiryDate: "Pending",
    status: "Pending",
    officer: "Fire Inspector Roberto L. Garcia",
  },
  {
    id: 4,
    certificateNo: "FSIC-2023-1024",
    establishment: "Cruz Family Apartment",
    address: "789 Residential St, Calamba City",
    issuedDate: "Pending",
    expiryDate: "Pending",
    status: "Pending",
    officer: "Fire Inspector Roberto L. Garcia",
  },
  {
    id: 4,
    certificateNo: "FSIC-2023-1024",
    establishment: "Cruz Family Apartment",
    address: "789 Residential St, Calamba City",
    issuedDate: "Pending",
    expiryDate: "Pending",
    status: "Pending",
    officer: "Fire Inspector Roberto L. Garcia",
  },
];

let filteredData = [...certificatesData];
let currentPageNum = 1;
const itemsPerPage = 10;

// Initialize the page
document.addEventListener("DOMContentLoaded", function () {
  renderTable();
  updatePagination();
});

// Render table
function renderTable() {
  const tbody = document.getElementById("certificatesTableBody");
  const startIndex = (currentPageNum - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  const pageData = filteredData.slice(startIndex, endIndex);

  tbody.innerHTML = "";

  pageData.forEach((cert) => {
    const row = document.createElement("tr");
    row.className = "fade-in";

    const statusClass =
      cert.status === "Active"
        ? "status-active"
        : cert.status === "Expired"
        ? "status-expired"
        : "status-pending";

    row.innerHTML = `
                    <td><strong>${cert.certificateNo}</strong></td>
                    <td>${cert.establishment}</td>
                    <td>${cert.issuedDate}</td>
                    <td>${cert.expiryDate}</td>
                    <td><span class="status-badge ${statusClass}">${
      cert.status
    }</span></td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-download btn-sm" onclick="downloadCertificate('${
                              cert.certificateNo
                            }')" 
                                    ${
                                      cert.status === "Pending"
                                        ? "disabled"
                                        : ""
                                    }>
                                <i class="fas fa-download"></i>
                            </button>
                            <button class="btn btn-view btn-sm" onclick="viewCertificate(${
                              cert.id
                            })">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </td>
                `;

    tbody.appendChild(row);
  });

  // Update counts
  document.getElementById("showingCount").textContent = pageData.length;
  document.getElementById("totalCount").textContent = filteredData.length;
}

// Apply filters
function applyFilters() {
  const establishmentFilter = document.getElementById(
    "establishmentFilter"
  ).value;
  const statusFilter = document.getElementById("statusFilter").value;
  const dateFilter = document.getElementById("dateFilter").value;

  filteredData = certificatesData.filter((cert) => {
    let matches = true;

    if (establishmentFilter && cert.establishment !== establishmentFilter) {
      matches = false;
    }

    if (statusFilter && cert.status !== statusFilter) {
      matches = false;
    }

    if (dateFilter && cert.issuedDate !== "Pending") {
      const certDate = new Date(cert.issuedDate);
      const filterDate = new Date(dateFilter);
      if (certDate < filterDate) {
        matches = false;
      }
    }

    return matches;
  });

  currentPageNum = 1;
  renderTable();
  updatePagination();
}

// Reset filters
function resetFilters() {
  document.getElementById("establishmentFilter").value = "";
  document.getElementById("statusFilter").value = "";
  document.getElementById("dateFilter").value = "";

  filteredData = [...certificatesData];
  currentPageNum = 1;
  renderTable();
  updatePagination();
}

// Change page
function changePage(direction) {
  const maxPages = Math.ceil(filteredData.length / itemsPerPage);

  if (direction === 1 && currentPageNum < maxPages) {
    currentPageNum++;
  } else if (direction === -1 && currentPageNum > 1) {
    currentPageNum--;
  }

  renderTable();
  updatePagination();
}

// Update pagination
function updatePagination() {
  document.getElementById("currentPage").textContent = currentPageNum;
  const maxPages = Math.ceil(filteredData.length / itemsPerPage);

  const prevBtn = document.querySelector(".pagination .page-item:first-child");
  const nextBtn = document.querySelector(".pagination .page-item:last-child");

  prevBtn.classList.toggle("disabled", currentPageNum === 1);
  nextBtn.classList.toggle(
    "disabled",
    currentPageNum === maxPages || maxPages === 0
  );
}

// View certificate
function viewCertificate(id) {
  const cert = certificatesData.find((c) => c.id === id);
  if (!cert) return;

  document.getElementById("modalCertNo").textContent = cert.certificateNo;
  document.getElementById("modalEstablishment").textContent =
    cert.establishment;
  document.getElementById("modalAddress").textContent = cert.address;
  document.getElementById("modalIssuedDate").textContent = cert.issuedDate;
  document.getElementById("modalExpiryDate").textContent = cert.expiryDate;
  document.getElementById("modalOfficer").textContent = cert.officer;

  const statusSpan = document.getElementById("modalStatus");
  const statusClass =
    cert.status === "Active"
      ? "status-active"
      : cert.status === "Expired"
      ? "status-expired"
      : "status-pending";
  statusSpan.textContent = cert.status;
  statusSpan.className = `status-badge ${statusClass}`;

  new bootstrap.Modal(document.getElementById("viewModal")).show();
}

// Download certificate
function downloadCertificate(certNo) {
  // Show loading state
  const btn = event.target.closest("button");
  const originalText = btn.innerHTML;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Downloading...';
  btn.disabled = true;

  // Simulate download process
  setTimeout(() => {
    // Create a simple text file for demo purposes
    const content = `FIRE SAFETY INSPECTION CERTIFICATE\n\nCertificate No: ${
      certNo || "Current Certificate"
    }\nIssued by: Bureau of Fire Protection\nDate: ${new Date().toLocaleDateString()}\n\nThis is a demo download.`;
    const blob = new Blob([content], { type: "text/plain" });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `${certNo || "Certificate"}.txt`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);

    // Reset button
    btn.innerHTML = originalText;
    btn.disabled = false;

    // Show success message
    showNotification("Certificate downloaded successfully!", "success");
  }, 2000);
}

// Show notification
function showNotification(message, type = "info") {
  // Create notification element
  const notification = document.createElement("div");
  notification.className = `alert alert-${
    type === "success" ? "success" : type === "error" ? "danger" : "info"
  } alert-dismissible fade show`;
  notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                min-width: 300px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            `;

  notification.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-${
                      type === "success"
                        ? "check-circle"
                        : type === "error"
                        ? "exclamation-triangle"
                        : "info-circle"
                    } me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

  document.body.appendChild(notification);

  // Auto remove after 5 seconds
  setTimeout(() => {
    if (notification.parentNode) {
      notification.remove();
    }
  }, 5000);
}

// Handle form validation styling
document.addEventListener("DOMContentLoaded", function () {
  const forms = document.querySelectorAll("form");
  forms.forEach((form) => {
    form.addEventListener("submit", function (event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add("was-validated");
    });
  });
});

// Add smooth scrolling and other UI enhancements
document.addEventListener("DOMContentLoaded", function () {
  // Smooth scrolling for anchor links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute("href"));
      if (target) {
        target.scrollIntoView({
          behavior: "smooth",
        });
      }
    });
  });

  // Add ripple effect to buttons
  document.querySelectorAll(".btn").forEach((button) => {
    button.addEventListener("click", function (e) {
      const ripple = document.createElement("span");
      const rect = this.getBoundingClientRect();
      const size = Math.max(rect.width, rect.height);
      const x = e.clientX - rect.left - size / 2;
      const y = e.clientY - rect.top - size / 2;

      ripple.style.cssText = `
                        position: absolute;
                        border-radius: 50%;
                        background: rgba(255, 255, 255, 0.3);
                        width: ${size}px;
                        height: ${size}px;
                        left: ${x}px;
                        top: ${y}px;
                        pointer-events: none;
                        transform: scale(0);
                        animation: ripple 0.6s ease-out;
                    `;

      this.style.position = "relative";
      this.style.overflow = "hidden";
      this.appendChild(ripple);

      setTimeout(() => {
        ripple.remove();
      }, 600);
    });
  });

  // Add CSS for ripple animation
  const style = document.createElement("style");
  style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
            `;
  document.head.appendChild(style);
});


// Initialize tooltips if Bootstrap tooltips are available
document.addEventListener("DOMContentLoaded", function () {
  if (typeof bootstrap !== "undefined" && bootstrap.Tooltip) {
    const tooltipTriggerList = [].slice.call(
      document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  }
});

// Add keyboard navigation support
document.addEventListener("keydown", function (event) {
  // ESC key closes modals
  if (event.key === "Escape") {
    const openModals = document.querySelectorAll(".modal.show");
    openModals.forEach((modal) => {
      bootstrap.Modal.getInstance(modal)?.hide();
    });
  }

  // Enter key on buttons triggers click
  if (event.key === "Enter" && event.target.tagName === "BUTTON") {
    event.target.click();
  }
});

// Search functionality (can be enhanced further)
function searchCertificates(query) {
  if (!query) {
    filteredData = [...certificatesData];
  } else {
    filteredData = certificatesData.filter(
      (cert) =>
        cert.certificateNo.toLowerCase().includes(query.toLowerCase()) ||
        cert.establishment.toLowerCase().includes(query.toLowerCase()) ||
        cert.status.toLowerCase().includes(query.toLowerCase())
    );
  }
  currentPageNum = 1;
  renderTable();
  updatePagination();
}

// Export functionality
function exportCertificates(format = "csv") {
  let content = "";
  let filename = `certificates_${new Date().toISOString().split("T")[0]}`;

  if (format === "csv") {
    content = "Certificate No,Establishment,Issued Date,Expiry Date,Status\n";
    filteredData.forEach((cert) => {
      content += `${cert.certificateNo},"${cert.establishment}",${cert.issuedDate},${cert.expiryDate},${cert.status}\n`;
    });
    filename += ".csv";
  } else if (format === "json") {
    content = JSON.stringify(filteredData, null, 2);
    filename += ".json";
  }

  const blob = new Blob([content], {
    type: format === "csv" ? "text/csv" : "application/json",
  });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = filename;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  window.URL.revokeObjectURL(url);

  showNotification(`Data exported successfully as ${filename}`, "success");
}

// Print certificate
function printCertificate() {
  const printContent = document.querySelector(".certificate-preview").innerHTML;
  const printWindow = window.open("", "_blank");
  printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Fire Safety Certificate</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        .certificate-preview { border: 3px solid #dc3545; padding: 40px; text-align: center; }
                        .certificate-title { color: #dc3545; font-size: 2rem; font-weight: bold; margin: 20px 0; }
                        .certificate-subtitle { font-size: 1.2rem; margin: 20px 0; }
                        .certificate-details { background: #f8f9fa; padding: 20px; margin: 20px 0; }
                        @media print { 
                            body { margin: 0; }
                            .certificate-preview { border: 2px solid #000; }
                        }
                    </style>
                </head>
                <body>
                    <div class="certificate-preview">${printContent}</div>
                </body>
                </html>
            `);
  printWindow.document.close();
  printWindow.print();
}
