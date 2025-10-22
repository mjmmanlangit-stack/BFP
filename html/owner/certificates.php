<?php
 include '../../utility/checkingUser.php';
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BFP Site Profiler - Owner of Establishment Certificates</title>
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
      rel="stylesheet"
    />

    <link rel="stylesheet" href="../../assets/styles/components/sidebar.css">
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
        padding: 0;
        padding-left: 250px;
      }

      .navbar {
        background: linear-gradient(
          135deg,
          var(--bfp-red) 0%,
          var(--bfp-dark-red) 100%
        );
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }

      .navbar-brand {
        color: white !important;
        font-weight: bold;
        font-size: 1.5rem;
      }

      .page-header {
        background: linear-gradient(
          135deg,
          var(--bfp-red) 0%,
          var(--bfp-dark-red) 100%
        );
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
      }

      .table-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
        margin-bottom: 2rem;
      }

      .btn-view {
        background-color: var(--bfp-red);
        color: white;
        border: none;
      }

      .btn-view:hover {
        background-color: var(--bfp-dark-red);
        color: white;
      }

      .certificate {
        background: white;
        border: 3px solid var(--bfp-dark);
        padding: 2rem;
        max-width: 800px;
        margin: 0 auto;
        font-family: "Times New Roman", serif;
      }

      .certificate-header {
        text-align: center;
        border-bottom: 2px solid var(--bfp-dark);
        padding-bottom: 1rem;
        margin-bottom: 1.5rem;
      }

      .certificate-logo {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: var(--bfp-gold);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.5rem;
        margin: 0 1rem;
      }

      .fsic-number {
        color: var(--bfp-red);
        font-weight: bold;
        font-size: 1.2rem;
        margin: 1rem 0;
      }

      .certificate-title {
        color: var(--bfp-dark);
        font-weight: bold;
        font-size: 1.8rem;
        margin: 1rem 0;
      }

      .certificate-body {
        line-height: 1.8;
        text-align: justify;
      }

      .certificate-field {
        border-bottom: 1px solid var(--bfp-dark);
        display: inline-block;
        min-width: 200px;
        margin: 0 0.5rem;
      }

      .certificate-footer {
        margin-top: 2rem;
        border-top: 2px solid var(--bfp-dark);
        padding-top: 1rem;
      }

      .signature-line {
        border-top: 1px solid var(--bfp-dark);
        margin-top: 3rem;
        padding-top: 0.5rem;
        text-align: center;
      }

      .tagalog-text {
        color: var(--bfp-red);
        font-weight: bold;
        text-align: center;
        margin-top: 1rem;
      }

      .modal-dialog-certificate {
        max-width: 900px;
      }

      .btn-download {
        background-color: var(--bfp-gold);
        color: var(--bfp-dark);
        border: none;
        font-weight: bold;
      }

      .btn-download:hover {
        background-color: #e0a800;
        color: var(--bfp-dark);
      }

      .search-box {
        margin-bottom: 1.5rem;
      }

      @media print {
        body * {
          visibility: hidden;
        }
        .certificate,
        .certificate * {
          visibility: visible;
        }
        .certificate {
          position: absolute;
          left: 0;
          top: 0;
          width: 100%;
        }
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
          <a href="./dashboard.php" class="nav-link ">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard
          </a>
        </div>
        <div class="nav-item">
          <a href="./my-establishments.php" class="nav-link">
            <i class="fas fa-building"></i>
            My Establishments
          </a>
        </div>
        <div class="nav-item">
          <a href="./certificates.php" class="nav-link active">
            <i class="fas fa-calendar-check"></i>
            Certificates
          </a>
        </div>
        <!-- <div class="nav-item">
          <a href="./gis-map.html" class="nav-link">
            <i class="fas fa-map-marker-alt"></i>
            Documents
          </a>
        </div> -->
      </nav>

      <div class="nav-item">
        <a href="../../utility/logout.php" class="nav-link">
          <i class="fas fa-sign-out-alt"></i>
          Logout
        </a>
      </div>
    </div>

    <div class="main-content">

      <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
          <a class="navbar-brand" href="#">
            <i class="fas fa-fire-extinguisher me-2"></i>
            BFP Site Profiler
          </a>
        </div>
      </nav>
  
      <div class="page-header">
        <div class="container">
          <h1 class="mb-0">
            <i class="fas fa-certificate me-2"></i>Owner of Establishment
            Certificates
          </h1>
          <p class="mb-0 mt-2">
            Bureau of Fire Protection - Certificate Management System
          </p>
        </div>
      </div>
  
      <div class="container">
        <div class="table-container">
          <div class="search-box">
            <input
              type="text"
              id="searchInput"
              class="form-control"
              placeholder="Search establishments..."
            />
          </div>
          <div class="table-responsive">
            <table class="table table-hover" id="establishmentsTable">
              <thead class="table-dark">
                <tr>
                  <th>FSIC No.</th>
                  <th>Establishment Name</th>
                  <th>Owner/Representative</th>
                  <th>Address</th>
                  <th>Valid Until</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>R-2024-001</td>
                  <td>Grand Hotel Manila</td>
                  <td>Juan Dela Cruz</td>
                  <td>123 Rizal Avenue, Masbate City</td>
                  <td>December 31, 2025</td>
                  <td>
                    <button
                      class="btn btn-view btn-sm"
                      onclick="viewCertificate(0)"
                    >
                      <i class="fas fa-eye me-1"></i>View
                    </button>
                  </td>
                </tr>
                <tr>
                  <td>R-2024-002</td>
                  <td>ABC Shopping Center</td>
                  <td>Maria Santos</td>
                  <td>456 Bonifacio Street, Masbate City</td>
                  <td>November 30, 2025</td>
                  <td>
                    <button
                      class="btn btn-view btn-sm"
                      onclick="viewCertificate(1)"
                    >
                      <i class="fas fa-eye me-1"></i>View
                    </button>
                  </td>
                </tr>
                <tr>
                  <td>R-2024-003</td>
                  <td>Pacific Restaurant & Bar</td>
                  <td>Pedro Reyes</td>
                  <td>789 Mabini Avenue, Masbate City</td>
                  <td>October 31, 2025</td>
                  <td>
                    <button
                      class="btn btn-view btn-sm"
                      onclick="viewCertificate(2)"
                    >
                      <i class="fas fa-eye me-1"></i>View
                    </button>
                  </td>
                </tr>
                <tr>
                  <td>R-2024-004</td>
                  <td>Tech Solutions Inc.</td>
                  <td>Ana Mendoza</td>
                  <td>321 Luna Street, Masbate City</td>
                  <td>January 15, 2026</td>
                  <td>
                    <button
                      class="btn btn-view btn-sm"
                      onclick="viewCertificate(3)"
                    >
                      <i class="fas fa-eye me-1"></i>View
                    </button>
                  </td>
                </tr>
                <tr>
                  <td>R-2024-005</td>
                  <td>Sunrise Bakery</td>
                  <td>Carlos Bautista</td>
                  <td>654 Quezon Boulevard, Masbate City</td>
                  <td>December 15, 2025</td>
                  <td>
                    <button
                      class="btn btn-view btn-sm"
                      onclick="viewCertificate(4)"
                    >
                      <i class="fas fa-eye me-1"></i>View
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Certificate Modal -->
    <div class="modal fade" id="certificateModal" tabindex="-1">
      <div
        class="modal-dialog modal-dialog-certificate modal-dialog-centered modal-dialog-scrollable"
      >
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Fire Safety Inspection Certificate</h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <div class="certificate" id="certificateContent">
              <div class="certificate-header">
                <div
                  class="d-flex justify-content-between align-items-center mb-3"
                >
                  <div class="certificate-logo">DILG</div>
                  <div class="flex-grow-1">
                    <div style="font-size: 0.9rem">
                      Republic of the Philippines
                    </div>
                    <div style="font-weight: bold">
                      Department of the Interior and Local Government
                    </div>
                    <div
                      style="
                        color: var(--bfp-red);
                        font-weight: bold;
                        font-size: 1.1rem;
                      "
                    >
                      BUREAU OF FIRE PROTECTION
                    </div>
                    <div style="font-size: 0.85rem">(Region)</div>
                    <div style="font-size: 0.85rem">
                      (District/Provincial Office)
                    </div>
                    <div style="font-size: 0.85rem">(Station)</div>
                    <div style="font-size: 0.85rem">(Station Address)</div>
                    <div style="font-size: 0.85rem">
                      (Telephone No./Email Address)
                    </div>
                  </div>
                  <div class="certificate-logo">BFP</div>
                </div>
              </div>

              <div class="fsic-number">
                FSIC NO. <span id="certFsicNo">R-________</span>
                <div
                  style="
                    float: right;
                    font-size: 0.9rem;
                    color: var(--bfp-dark);
                  "
                >
                  Date: <span id="certDate"></span>
                </div>
              </div>

              <div class="certificate-title text-center">
                FIRE SAFETY INSPECTION CERTIFICATE
              </div>

              <div style="font-size: 0.9rem; margin-bottom: 1rem">
                <input type="checkbox" /> FOR CERTIFICATE OF OCCUPANCY<br />
                <input type="checkbox" /> FOR BUSINESS PERMIT (NEW/RENEWAL)<br />
                <input type="checkbox" /> OTHERS ___________________
              </div>

              <div class="certificate-body">
                <p><strong>TO WHOM IT MAY CONCERN:</strong></p>

                <p style="text-indent: 2rem">
                  By virtue of the provisions of RA 9514 otherwise known as the
                  Fire Code of the Philippines of 2008, the application for FIRE
                  SAFETY INSPECTION of
                  <span class="certificate-field" id="certEstablishment"></span>
                </p>

                <p style="margin-left: 2rem">(Name of Establishment)</p>

                <p>
                  owned and managed by
                  <span class="certificate-field" id="certOwner"></span> with
                  postal address at
                </p>

                <p style="margin-left: 2rem">(Name of Owner/Representative)</p>

                <p>
                  <span
                    class="certificate-field"
                    id="certAddress"
                    style="min-width: 400px"
                  ></span>
                </p>

                <p style="margin-left: 2rem">(Address)</p>

                <p style="text-indent: 2rem">
                  is hereby GRANTED after said building structure or facility
                  has been duly inspected with the finding that it has fully
                  complied with the fire safety and protection requirements of
                  the Fire Code of the Philippines of 2008 and its Revised
                  Implementing Rules and Regulations.
                </p>

                <p style="text-indent: 2rem">
                  This certification is valid for
                  <span class="certificate-field" id="certDescription"
                    >ONE (1) YEAR</span
                  >
                </p>

                <p style="margin-left: 2rem">(Description)</p>

                <p>
                  valid until
                  <span class="certificate-field" id="certValidUntil"></span>
                </p>

                <p
                  style="
                    font-style: italic;
                    font-size: 0.9rem;
                    margin-top: 1rem;
                  "
                >
                  Violation of Fire Code provisions shall cause this certificate
                  null and void after appropriate proceeding and shall hold the
                  owner liable to the penalties provided for by the Fire Code.
                </p>
              </div>

              <div class="certificate-footer">
                <div class="row">
                  <div class="col-6">
                    <strong>Fire Code Fees:</strong><br />
                    Amount Paid: _______________<br />
                    O.R. Number: _______________<br />
                    Date: _______________
                  </div>
                  <div class="col-6">
                    <strong>RECOMMEND APPROVAL:</strong><br /><br /><br />
                    <div class="signature-line">
                      CHIEF, Fire Safety Enforcement Section
                    </div>
                  </div>
                </div>

                <div class="text-center mt-4">
                  <strong>APPROVED:</strong>
                  <div
                    class="signature-line"
                    style="width: 50%; margin: 3rem auto 0"
                  >
                    CITY/MUNICIPAL FIRE MARSHAL
                  </div>
                </div>

                <p
                  style="
                    font-size: 0.85rem;
                    font-style: italic;
                    margin-top: 2rem;
                    text-align: center;
                  "
                >
                  <strong>NOTE:</strong> "This Certificate does not take the
                  place of any license required by law and is not transferable.
                  Any change in the use of occupancy of the premises shall
                  require a new certificate."
                </p>

                <div class="tagalog-text">
                  TUNAY NA KAALWAN NG MAMAYAN<br />
                  PAALALA: "IWASANG MAKAPINSALA NG PAMBIHIRANG NG BUREAU OF FIRE
                  PROTECTION SA MGA KAWANI NITO ANG MAGBENTA O MAGREKOMENDA NG
                  KAHUMAN NG FIRE EXTINGUISHER"<br />
                  <strong>"FIRE SAFETY IS OUR MAIN CONCERN"</strong>
                </div>

                <div style="margin-top: 1rem; font-size: 0.8rem">
                  <strong>Applicant/Owner's COPY</strong>
                </div>

                <div style="font-size: 0.7rem; margin-top: 0.5rem">
                  BFP-FSIF-FSED-005 Rev. 03 (03.02.20)
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Close
            </button>
            <button
              type="button"
              class="btn btn-download"
              onclick="downloadCertificate()"
            >
              <i class="fas fa-download me-1"></i>Download Certificate
            </button>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
      const establishments = [
        {
          fsicNo: "R-2024-001",
          name: "Grand Hotel Manila",
          owner: "Juan Dela Cruz",
          address: "123 Rizal Avenue, Masbate City, Bicol Region",
          validUntil: "December 31, 2025",
        },
        {
          fsicNo: "R-2024-002",
          name: "ABC Shopping Center",
          owner: "Maria Santos",
          address: "456 Bonifacio Street, Masbate City, Bicol Region",
          validUntil: "November 30, 2025",
        },
        {
          fsicNo: "R-2024-003",
          name: "Pacific Restaurant & Bar",
          owner: "Pedro Reyes",
          address: "789 Mabini Avenue, Masbate City, Bicol Region",
          validUntil: "October 31, 2025",
        },
        {
          fsicNo: "R-2024-004",
          name: "Tech Solutions Inc.",
          owner: "Ana Mendoza",
          address: "321 Luna Street, Masbate City, Bicol Region",
          validUntil: "January 15, 2026",
        },
        {
          fsicNo: "R-2024-005",
          name: "Sunrise Bakery",
          owner: "Carlos Bautista",
          address: "654 Quezon Boulevard, Masbate City, Bicol Region",
          validUntil: "December 15, 2025",
        },
      ];

      function viewCertificate(index) {
        const establishment = establishments[index];
        const today = new Date().toLocaleDateString("en-US", {
          year: "numeric",
          month: "long",
          day: "numeric",
        });

        document.getElementById("certFsicNo").textContent =
          establishment.fsicNo;
        document.getElementById("certDate").textContent = today;
        document.getElementById("certEstablishment").textContent =
          establishment.name;
        document.getElementById("certOwner").textContent = establishment.owner;
        document.getElementById("certAddress").textContent =
          establishment.address;
        document.getElementById("certValidUntil").textContent =
          establishment.validUntil;

        const modal = new bootstrap.Modal(
          document.getElementById("certificateModal")
        );
        modal.show();
      }

      function downloadCertificate() {
        const certificate = document.getElementById("certificateContent");

        html2canvas(certificate, {
          scale: 2,
          useCORS: true,
          backgroundColor: "#ffffff",
        }).then((canvas) => {
          const link = document.createElement("a");
          const fsicNo = document.getElementById("certFsicNo").textContent;
          link.download = `FSIC_${fsicNo}_Certificate.png`;
          link.href = canvas.toDataURL();
          link.click();
        });
      }

      // Search functionality
      document
        .getElementById("searchInput")
        .addEventListener("keyup", function () {
          const searchValue = this.value.toLowerCase();
          const tableRows = document.querySelectorAll(
            "#establishmentsTable tbody tr"
          );

          tableRows.forEach((row) => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchValue) ? "" : "none";
          });
        });
    </script>
  </body>
</html>
