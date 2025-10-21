<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BFP SiteProfiler - Fire Safety Inspection Report</title>
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
      rel="stylesheet"
    />

    <link
      rel="stylesheet"
      href="../../assets/styles/layout/inspector-report-findings.css"
    />

    <link rel="stylesheet" href="../../assets/styles/components/sidebar.css" />
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
          <a href="#" class="nav-link active">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard
          </a>
        </div>
        <div class="nav-item">
          <a href="./assigned-inspections.php" class="nav-link">
            <i class="fas fa-building"></i>
            Assigned Inspections
          </a>
        </div>
        <div class="nav-item">
          <a href="./report-findings.php" class="nav-link">
            <i class="fas fa-calendar-check"></i>
            Report Findings
          </a>
        </div>
        <div class="nav-item">
          <a href="./gis-map.php" class="nav-link">
            <i class="fas fa-map-marker-alt"></i>
            GIS Map
          </a>
        </div>
        
      </nav>

      <div class="nav-item">
        <a href="../index.php" class="nav-link">
          <i class="fas fa-sign-out-alt"></i>
          Logout
        </a>
      </div>
    </div>

    <div class="main-content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <h2 class="text-danger mb-4">Report Inspection Findings</h2>

            <div class="form-section">
              <h3 class="section-title">Fire Safety Inspection Report</h3>

              <div class="row">
                <div class="col-md-6">
                  <label class="form-label">Establishment</label>
                  <input
                    type="text"
                    class="form-control"
                    value="Vista Public Market"
                    id="establishment"
                  />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Type</label>
                  <select class="form-control" id="establishmentType">
                    <option selected>Commercial</option>
                    <option>Residential</option>
                    <option>Industrial</option>
                    <option>Institutional</option>
                  </select>
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-md-4">
                  <label class="form-label">Inspection Date</label>
                  <input
                    type="date"
                    class="form-control"
                    value="2015-07-13"
                    id="inspectionDate"
                  />
                </div>
                <div class="col-md-4">
                  <label class="form-label">Time</label>
                  <input
                    type="time"
                    class="form-control"
                    value="10:30"
                    id="inspectionTime"
                  />
                </div>
                <div class="col-md-4">
                  <label class="form-label">Risk Level</label>
                  <select class="form-control" id="riskLevel">
                    <option>Low</option>
                    <option selected>Medium</option>
                    <option>High</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="form-section">
              <h4 class="section-title">Overall Compliance Status</h4>
              <div class="compliance-status">
                <div
                  class="status-card compliant"
                  onclick="selectStatus('compliant')"
                >
                  <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                  <div class="fw-bold">Compliant</div>
                </div>
                <div
                  class="status-card partially-compliant selected"
                  onclick="selectStatus('partially-compliant')"
                >
                  <i
                    class="fas fa-exclamation-triangle fa-2x text-danger mb-2"
                  ></i>
                  <div class="fw-bold">Partially Compliant</div>
                </div>
                <div
                  class="status-card non-compliant"
                  onclick="selectStatus('non-compliant')"
                >
                  <i class="fas fa-times-circle fa-2x text-info mb-2"></i>
                  <div class="fw-bold">Non-Compliant</div>
                </div>
              </div>
            </div>

            <div class="form-section">
              <div
                class="d-flex justify-content-between align-items-center mb-3"
              >
                <h4 class="section-title mb-0">Violations Found</h4>
                <button class="btn btn-bfp btn-sm" onclick="addViolation()">
                  <i class="fas fa-plus me-1"></i> Add Violation
                </button>
              </div>

              <div id="violations-container">
                <div class="violation-item">
                  <div
                    class="violation-header d-flex align-items-center justify-content-between"
                  >
                    <div class="violation-title">
                      <i
                        class="fas fa-exclamation-triangle text-danger me-2"
                      ></i>
                      Blocked Fire Exits
                    </div>
                    <button
                      class="btn btn-outline-danger btn-sm"
                      onclick="removeViolation(this)"
                    >
                      <i class="fas fa-times"></i>
                    </button>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" rows="3">
Main fire exit on the east side is partially blocked by storage boxes and equipment. This violates Section 5.2 of the Fire Code.</textarea
                    >
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Corrective Action</label>
                    <textarea class="form-control" rows="2">
Remove all obstructions from fire exit pathways. Ensure clear access at all times.</textarea
                    >
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Deadline for Compliance</label>
                    <input
                      type="date"
                      class="form-control"
                      value="2015-07-27"
                    />
                  </div>
                </div>
              </div>
            </div>

            <div class="form-section">
              <h4 class="section-title">Inspection Photos</h4>
              <div
                class="photo-upload-area"
                onclick="document.getElementById('photoInput').click()"
              >
                <i class="fas fa-camera fa-3x text-muted mb-3"></i>
                <div class="text-muted">
                  Click to upload or drag and drop photos
                </div>
                <input
                  type="file"
                  id="photoInput"
                  multiple
                  accept="image/*"
                  style="display: none"
                  onchange="handlePhotoUpload(this)"
                />
              </div>
              <div id="uploaded-photos" class="row mt-3"></div>
            </div>

            <div class="form-section">
              <h4 class="section-title">Inspector's Notes</h4>
              <textarea
                class="form-control"
                rows="4"
                placeholder="The establishment has improved since last inspection but still has critical violations that need immediate attention. The owner was cooperative during inspection."
              >
The establishment has improved since last inspection but still has critical violations that need immediate attention. The owner was cooperative during inspection.</textarea
              >
            </div>

            <div class="form-section">
              <h4 class="section-title">Recommendations</h4>
              <div class="mb-3">
                <ol>
                  <li class="mb-2">
                    Conduct fire safety training for all employees
                  </li>
                  <li class="mb-2">
                    Install additional fire extinguishers in storage areas
                  </li>
                  <li class="mb-2">
                    Schedule regular fire exit pathway checks
                  </li>
                </ol>
              </div>
              <button
                class="btn btn-outline-secondary btn-sm"
                ogit nclick="addRecommendation()"
              >
                <i class="fas fa-plus me-1"></i> Add Recommendation
              </button>
            </div>

            <div class="form-section">
              <h4 class="section-title">Owner/Representative Signature</h4>
              <div class="signature-area">
                <canvas class="signature-canvas" id="signatureCanvas"></canvas>
                <button class="clear-signature" onclick="clearSignature()">
                  <i class="fas fa-eraser me-1"></i> Clear
                </button>
              </div>
              <div class="text-danger mt-2">
                <i class="fas fa-exclamation-triangle me-1"></i>
                Draw Signature
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <button class="btn btn-bfp" onclick="printReport()">
                <i class="fas fa-print me-2"></i>Print
              </button>
              <button class="btn btn-success" onclick="submitReport()">
                <i class="fas fa-paper-plane me-2"></i>Submit Report
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <script src="../../assets/scripts/inspector-reports-finding.js"></script>
  </body>
</html>
