<?php
 include '../../utility/checkingUser.php';
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BFP Site Profiler - My Certificates</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../../assets/styles/components/sidebar.css">
    <style>
      :root {
        --bfp-red: #dc3545;
        --bfp-dark-red: #a02834;
        --bfp-gold: #c8a951;
        --bfp-dark: #1a1a1a;
        --bfp-light: #f8f9fa;
        --bfp-navy: #003087;
      }
      body { background-color: var(--bfp-light); font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }
      .main-content { padding: 0; padding-left: 250px; }
      .navbar { background: linear-gradient(135deg, var(--bfp-red) 0%, var(--bfp-dark-red) 100%); box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
      .navbar-brand { color: white !important; font-weight: bold; font-size: 1.5rem; }
      .page-header { background: linear-gradient(135deg, var(--bfp-red) 0%, var(--bfp-dark-red) 100%); color: white; padding: 2rem 0; margin-bottom: 2rem; }
      .table-container { background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 1.5rem; margin-bottom: 2rem; }
      .btn-view { background-color: var(--bfp-red); color: white; border: none; }
      .btn-view:hover { background-color: var(--bfp-dark-red); color: white; }
      .search-box { margin-bottom: 1.5rem; }

      /* â”€â”€â”€ Certificate Modal â”€â”€â”€ */
      .modal-dialog-certificate { max-width: 860px; }

      /* â”€â”€â”€ Official Certificate Design â”€â”€â”€ */
      .cert-wrap {
        background: #fff;
        font-family: "Times New Roman", Georgia, serif;
        color: #111;
        position: relative;
        padding: 0;
        user-select: none;
        -webkit-user-select: none;
      }

      /* Outer border frame */
      .cert-frame {
        border: 6px double #8b0000;
        margin: 12px;
        padding: 0;
        position: relative;
      }
      .cert-frame::before {
        content: '';
        position: absolute;
        inset: 4px;
        border: 1.5px solid #c8a951;
        pointer-events: none;
        z-index: 0;
      }

      /* Watermark */
      .cert-watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-30deg);
        font-size: 5rem;
        font-weight: 900;
        color: rgba(220,53,69,0.06);
        white-space: nowrap;
        pointer-events: none;
        z-index: 0;
        text-transform: uppercase;
        letter-spacing: 4px;
        line-height: 1.2;
        text-align: center;
      }

      .cert-inner {
        position: relative;
        z-index: 1;
        padding: 24px 32px 20px;
      }

      /* Header */
      .cert-header { border-bottom: 3px double #8b0000; padding-bottom: 12px; margin-bottom: 10px; }
      .cert-gov-title { font-size: 0.72rem; letter-spacing: 1px; text-transform: uppercase; color: #333; }
      .cert-dilg { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: #003087; line-height: 1.3; }
      .cert-bfp-name { font-size: 1rem; font-weight: 900; color: #8b0000; text-transform: uppercase; letter-spacing: 2px; }
      .cert-station-info { font-size: 0.7rem; color: #555; line-height: 1.6; }

      /* Seals */
      .cert-seal {
        width: 74px;
        height: 74px;
        border-radius: 50%;
        border: 3px solid #c8a951;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        font-size: 0.55rem;
        font-weight: 800;
        text-align: center;
        line-height: 1.2;
        background: radial-gradient(circle, #fff9ee 60%, #f5e6bb);
        color: #8b0000;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 0 0 2px #8b0000, 0 0 0 4px #c8a951;
        flex-shrink: 0;
      }
      .cert-seal i { font-size: 1.5rem; color: #8b0000; margin-bottom: 2px; }

      /* Title band */
      .cert-title-band {
        background: linear-gradient(135deg, #8b0000, #c0392b);
        color: #fff;
        text-align: center;
        padding: 8px 16px;
        margin: 10px -32px;
        letter-spacing: 3px;
        font-size: 1.15rem;
        font-weight: 900;
        text-transform: uppercase;
        font-family: "Times New Roman", serif;
        border-top: 2px solid #c8a951;
        border-bottom: 2px solid #c8a951;
      }

      /* FSIC number bar */
      .cert-fsic-bar {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin: 10px 0 6px;
        font-size: 0.82rem;
        font-weight: 700;
        color: #8b0000;
      }
      .cert-fsic-num { font-size: 1.05rem; letter-spacing: 1px; }

      /* Purpose checkboxes */
      .cert-purpose { font-size: 0.78rem; margin-bottom: 10px; color: #333; line-height: 2; }
      .cert-purpose span { margin-right: 16px; white-space: nowrap; }
      .cert-checkbox {
        display: inline-block;
        width: 13px; height: 13px;
        border: 1.5px solid #333;
        vertical-align: middle;
        margin-right: 4px;
        position: relative;
        top: -1px;
      }
      .cert-checkbox.checked::after {
        content: 'âœ“';
        position: absolute;
        top: -2px; left: 1px;
        font-size: 11px;
        font-weight: bold;
        color: #8b0000;
      }

      /* Body text */
      .cert-body { font-size: 0.82rem; line-height: 1.85; text-align: justify; }
      .cert-field {
        display: inline-block;
        border-bottom: 1.5px solid #333;
        min-width: 200px;
        text-align: center;
        font-weight: 700;
        color: #8b0000;
        padding: 0 4px;
        font-size: 0.88rem;
      }
      .cert-field.wide { min-width: 380px; }
      .cert-sub { font-size: 0.7rem; color: #555; font-style: italic; text-align: center; display: block; margin-top: -2px; margin-bottom: 4px; }

      /* Validity */
      .cert-validity-box {
        border: 1.5px solid #c8a951;
        background: #fffdf5;
        border-radius: 4px;
        padding: 8px 14px;
        margin: 10px 0;
        font-size: 0.83rem;
        display: flex;
        gap: 32px;
        flex-wrap: wrap;
      }
      .cert-validity-box strong { color: #8b0000; }

      /* Footer */
      .cert-footer { border-top: 3px double #8b0000; margin-top: 14px; padding-top: 12px; }
      .cert-fee-box { font-size: 0.78rem; line-height: 2; }
      .cert-fee-line { border-bottom: 1px solid #333; display: inline-block; width: 130px; }
      .cert-sig-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #333; text-align: center; }
      .cert-sig-title { font-size: 0.68rem; color: #555; text-align: center; font-style: italic; }
      .cert-sig-line { border-top: 1.5px solid #333; width: 100%; margin: 28px auto 4px; }
      .cert-stamp {
        width: 70px; height: 70px;
        border-radius: 50%;
        border: 2px dashed #8b0000;
        display: flex; align-items: center; justify-content: center;
        color: #8b0000; font-size: 0.5rem; font-weight: 700;
        text-align: center; text-transform: uppercase;
        opacity: 0.5;
        line-height: 1.3;
        padding: 6px;
      }

      /* Note */
      .cert-note { font-size: 0.68rem; font-style: italic; color: #555; border-top: 1px solid #ccc; margin-top: 10px; padding-top: 6px; }
      .cert-tagalog { color: #8b0000; font-size: 0.67rem; font-weight: 700; text-align: center; margin-top: 8px; line-height: 1.6; }
      .cert-formno { font-size: 0.65rem; color: #888; text-align: right; margin-top: 4px; }
      .cert-copy { font-size: 0.7rem; font-weight: 700; color: #333; }

      /* Status ribbon */
      .cert-ribbon {
        position: absolute;
        top: 18px; right: -6px;
        background: #28a745;
        color: white;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 3px 14px;
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
      }
      .cert-ribbon.expired { background: #dc3545; }
    </style>
  </head>
  <body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
      <div class="logo-section">
        <div class="logo"><i class="fas fa-shield-alt" style="color:var(--bfp-red);font-size:24px"></i></div>
        <h5 class="mb-0">BFP SiteProfiler</h5>
      </div>
      <nav class="sidebar-nav">
        <div class="nav-item"><a href="./dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></div>
        <div class="nav-item"><a href="./my-establishments.php" class="nav-link"><i class="fas fa-building"></i> My Establishments</a></div>
        <div class="nav-item"><a href="./certificates.php" class="nav-link active"><i class="fas fa-certificate"></i> Certificates</a></div>
        <div class="nav-item"><a href="./documents.php" class="nav-link"><i class="fas fa-file-alt"></i> Documents</a></div>
        <div class="nav-item"><a href="./inspection-history.php" class="nav-link"><i class="fas fa-history"></i> Inspection History</a></div>
      </nav>
      <div class="nav-item"><a href="../../utility/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
    </div>

    <div class="main-content">
      <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
          <a class="navbar-brand" href="#"><i class="fas fa-fire-extinguisher me-2"></i>BFP Site Profiler</a>
        </div>
      </nav>

      <div class="page-header">
        <div class="container">
          <h1 class="mb-0"><i class="fas fa-certificate me-2"></i>My Fire Safety Certificates</h1>
          <p class="mb-0 mt-2">Bureau of Fire Protection â€” Certificate Management System</p>
        </div>
      </div>

      <div class="container">
        <div class="table-container">
          <div class="search-box">
            <input type="text" id="searchInput" class="form-control" placeholder="Search by establishment name or FSIC numberâ€¦"/>
          </div>
          <div class="table-responsive">
            <table class="table table-hover" id="certsTable">
              <thead class="table-dark">
                <tr>
                  <th>FSIC No.</th>
                  <th>Establishment</th>
                  <th>Issued Date</th>
                  <th>Valid Until</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody id="certificatesTableBody">
                <tr><td colspan="6" class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span>Loading certificatesâ€¦</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Certificate View Modal -->
    <div class="modal fade" id="certificateModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-certificate modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header py-2" style="background:linear-gradient(135deg,#8b0000,#c0392b)">
            <h6 class="modal-title text-white fw-bold mb-0"><i class="fas fa-certificate me-2"></i>Fire Safety Inspection Certificate</h6>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-0">
            <!-- â•â• OFFICIAL CERTIFICATE â•â• -->
            <div class="cert-wrap" id="certificateContent">
              <div class="cert-frame">
                <div class="cert-watermark">BFP<br>FSIC<br>OFFICIAL</div>
                <div id="certRibbon" class="cert-ribbon">VALID</div>

                <div class="cert-inner">
                  <!-- â”€â”€ Government Header â”€â”€ -->
                  <div class="cert-header">
                    <div class="d-flex align-items-center gap-3">
                      <!-- Left Seal: DILG -->
                      <div class="cert-seal">
                        <i class="fas fa-landmark"></i>
                        <span>DILG</span>
                      </div>

                      <!-- Center Header Text -->
                      <div class="flex-grow-1 text-center">
                        <div class="cert-gov-title">Republic of the Philippines</div>
                        <div class="cert-dilg">Department of the Interior and Local Government</div>
                        <div class="cert-bfp-name">Bureau of Fire Protection</div>
                        <div class="cert-station-info" id="certRegion">National Capital Region</div>
                        <div class="cert-station-info">Civil Service Commission â€¢ Fire Safety Inspection Division</div>
                      </div>

                      <!-- Right Seal: BFP -->
                      <div class="cert-seal">
                        <i class="fas fa-fire-extinguisher"></i>
                        <span>BFP<br>Official</span>
                      </div>
                    </div>
                  </div>

                  <!-- â”€â”€ Title Band â”€â”€ -->
                  <div class="cert-title-band">Fire Safety Inspection Certificate</div>

                  <!-- â”€â”€ FSIC / Date Bar â”€â”€ -->
                  <div class="cert-fsic-bar">
                    <span>FSIC NO.: <span class="cert-fsic-num" id="certFsicNo">BFP-FSIC-â€”</span></span>
                    <span>Date Issued: <span id="certDate">â€”</span></span>
                  </div>

                  <!-- â”€â”€ Purpose â”€â”€ -->
                  <div class="cert-purpose">
                    <span><span class="cert-checkbox"></span>FOR CERTIFICATE OF OCCUPANCY</span>
                    <span><span class="cert-checkbox checked"></span>FOR BUSINESS PERMIT (NEW/RENEWAL)</span>
                    <span><span class="cert-checkbox"></span>OTHERS ___________</span>
                  </div>

                  <!-- â”€â”€ Body Text â”€â”€ -->
                  <div class="cert-body">
                    <p><strong>TO WHOM IT MAY CONCERN:</strong></p>
                    <p style="text-indent:2rem">
                      By virtue of the provisions of <strong>Republic Act No. 9514</strong>, otherwise known as the
                      <em>Fire Code of the Philippines of 2008</em>, the application for <strong>FIRE SAFETY INSPECTION</strong> of
                    </p>
                    <div class="text-center my-1">
                      <span class="cert-field" id="certEstablishment">â€”</span>
                      <span class="cert-sub">(Name of Establishment)</span>
                    </div>
                    <p>owned and/or represented by</p>
                    <div class="text-center my-1">
                      <span class="cert-field" id="certOwner">â€”</span>
                      <span class="cert-sub">(Name of Owner / Authorized Representative)</span>
                    </div>
                    <p>with postal address at</p>
                    <div class="text-center my-1">
                      <span class="cert-field wide" id="certAddress">â€”</span>
                      <span class="cert-sub">(Complete Address)</span>
                    </div>
                    <p style="text-indent:2rem;margin-top:8px">
                      is hereby <strong>GRANTED</strong> after said building, structure or facility has been duly inspected, with
                      the finding that it has <u>fully complied</u> with the fire safety and protection requirements of the
                      <em>Fire Code of the Philippines of 2008</em> and its Revised Implementing Rules and Regulations.
                    </p>
                  </div>

                  <!-- â”€â”€ Validity Box â”€â”€ -->
                  <div class="cert-validity-box">
                    <span><strong>Duration:</strong> ONE (1) YEAR</span>
                    <span><strong>Date Issued:</strong> <span id="certIssuedFull">â€”</span></span>
                    <span><strong>Valid Until:</strong> <span id="certValidUntil">â€”</span></span>
                  </div>

                  <p class="cert-body" style="font-size:0.76rem;font-style:italic;margin-bottom:10px">
                    <em>Violation of Fire Code provisions shall render this certificate null and void after appropriate proceeding
                    and shall hold the owner liable to the penalties provided for by the Fire Code.</em>
                  </p>

                  <!-- â”€â”€ Footer: Fees + Signatures â”€â”€ -->
                  <div class="cert-footer">
                    <div class="row">
                      <div class="col-md-5">
                        <div class="cert-fee-box">
                          <strong>Fire Code Fees:</strong><br/>
                          Amount Paid: <span class="cert-fee-line"></span><br/>
                          O.R. Number: <span class="cert-fee-line"></span><br/>
                          Date: <span class="cert-fee-line"></span>
                        </div>
                      </div>
                      <div class="col-md-4 text-center">
                        <div class="cert-sig-line"></div>
                        <div class="cert-sig-label" id="certAuthorizedBy">â€”</div>
                        <div class="cert-sig-title">Chief, Fire Safety Enforcement Section</div>
                        <div class="cert-sig-label mt-1" style="font-size:0.65rem;color:#555">(RECOMMEND APPROVAL)</div>
                      </div>
                      <div class="col-md-3 d-flex flex-column align-items-center justify-content-end">
                        <div class="cert-stamp">Official<br>Stamp</div>
                      </div>
                    </div>

                    <div class="text-center mt-3">
                      <div class="cert-sig-line" style="width:55%;margin:24px auto 4px;"></div>
                      <div class="cert-sig-label">CITY / MUNICIPAL FIRE MARSHAL</div>
                      <div class="cert-sig-title">(APPROVED)</div>
                    </div>

                    <div class="cert-note">
                      <strong>NOTE:</strong> "This Certificate does not take the place of any license required by law and is not
                      transferable. Any change in the use or occupancy of the premises shall require a new certificate."
                    </div>

                    <div class="cert-tagalog">
                      TUNAY NA KALAYAAN NG MAMAYAN<br/>
                      PAALALA: "IWASANG MAKAPINSALA â€” ANUMANG TAONG MAGBENTA O MAGREKOMENDA NG KAHIT SINONG KAWANI NG BUREAU OF FIRE PROTECTION.<br/>
                      <strong>"FIRE SAFETY IS OUR MAIN CONCERN"</strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-end mt-2">
                      <div class="cert-copy">Establishment Owner's Copy</div>
                      <div class="cert-formno">BFP-FSIF-FSED-005 Rev. 03 (03.02.20)</div>
                    </div>
                  </div><!-- /cert-footer -->
                </div><!-- /cert-inner -->
              </div><!-- /cert-frame -->
            </div><!-- /cert-wrap -->
          </div>
          <div class="modal-footer py-2">
            <small class="text-muted me-auto"><i class="fas fa-lock me-1"></i>This certificate is for viewing purposes only.</small>
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
      let certificates = [];

      async function loadCertificatesData() {
        const tbody = document.getElementById('certificatesTableBody');
        try {
          const res = await fetch('../../utility/getCertificatesByOwner.php');
          const d   = await res.json();
          if (!d.success || !d.certificates || !d.certificates.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-certificate fa-2x mb-2 d-block text-secondary"></i>No certificates found. Certificates will appear here after your payment is confirmed.</td></tr>';
            return;
          }
          certificates = d.certificates;
          renderTable(certificates);
        } catch(e) {
          console.error(e);
          tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-3">Error loading certificates.</td></tr>';
        }
      }

      function renderTable(data) {
        const tbody = document.getElementById('certificatesTableBody');
        if (!data.length) {
          tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">No matching certificates.</td></tr>';
          return;
        }
        tbody.innerHTML = data.map((c, i) => {
          const expired   = c.is_expired;
          const statusBadge = expired
            ? '<span class="badge bg-danger">Expired</span>'
            : '<span class="badge bg-success">Valid</span>';
          const issued  = c.authorized_at ? new Date(c.authorized_at).toLocaleDateString('en-US', {year:'numeric',month:'long',day:'numeric'}) : 'â€”';
          const expiry  = c.expiry_date   ? new Date(c.expiry_date).toLocaleDateString('en-US', {year:'numeric',month:'long',day:'numeric'}) : 'â€”';
          return `<tr>
            <td><strong>${c.certificate_number||'â€”'}</strong></td>
            <td>${c.establishment_name||'â€”'}</td>
            <td>${issued}</td>
            <td>${expiry}</td>
            <td>${statusBadge}</td>
            <td><button class="btn btn-view btn-sm" onclick="viewCertificate(${i})"><i class="fas fa-eye me-1"></i>View</button></td>
          </tr>`;
        }).join('');
      }

      function viewCertificate(idx) {
        const c = certificates[idx];
        if (!c) return;
        const expired = c.is_expired;
        const issued  = c.authorized_at ? new Date(c.authorized_at).toLocaleDateString('en-US',{year:'numeric',month:'long',day:'numeric'}) : 'â€”';
        const expiry  = c.expiry_date   ? new Date(c.expiry_date).toLocaleDateString('en-US',{year:'numeric',month:'long',day:'numeric'}) : 'â€”';

        document.getElementById('certFsicNo').textContent        = c.certificate_number || 'â€”';
        document.getElementById('certDate').textContent          = issued;
        document.getElementById('certIssuedFull').textContent    = issued;
        document.getElementById('certEstablishment').textContent = c.establishment_name  || 'â€”';
        document.getElementById('certOwner').textContent         = c.owner_name          || 'â€”';
        document.getElementById('certAddress').textContent       = c.address             || 'â€”';
        document.getElementById('certValidUntil').textContent    = expiry;
        document.getElementById('certAuthorizedBy').textContent  = c.authorized_by_name  || 'FIRE OFFICER';

        const ribbon = document.getElementById('certRibbon');
        ribbon.textContent = expired ? 'EXPIRED' : 'VALID';
        ribbon.className   = 'cert-ribbon' + (expired ? ' expired' : '');

        new bootstrap.Modal(document.getElementById('certificateModal')).show();
      }

      document.getElementById('searchInput').addEventListener('input', function() {
        const q = this.value.toLowerCase();
        renderTable(certificates.filter(c =>
          (c.certificate_number||'').toLowerCase().includes(q) ||
          (c.establishment_name||'').toLowerCase().includes(q)
        ));
      });

      document.addEventListener('DOMContentLoaded', loadCertificatesData);
    </script>
  </body>
</html>
