<?php
include '../../utility/checkingUser.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>BFP Site Profiler - My Establishments</title>
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css"
    rel="stylesheet" />
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    rel="stylesheet" />
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css"
    rel="stylesheet" />

  <link rel="stylesheet" href="../../assets/styles/components/sidebar.css" />
  <link rel="stylesheet" href="../../assets/styles/components/header.css" />

  <link
    rel="stylesheet"
    href="../../assets/styles/layout/user-my-establishments.css" />
</head>

<body>
  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="logo-section">
      <div class="logo">
        <i
          class="fas fa-shield-alt"
          style="color: var(--bfp-red); font-size: 24px"></i>
      </div>
      <h5 class="mb-0">BFP SiteProfiler</h5>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-item">
        <a href="./dashboard.php" class="nav-link">
          <i class="fas fa-tachometer-alt"></i>
          Dashboard
        </a>
      </div>
      <div class="nav-item">
        <a href="./my-establishments.php" class="nav-link active">
          <i class="fas fa-building"></i>
          My Establishments
        </a>
      </div>
      <div class="nav-item">
        <a href="./certificates.php" class="nav-link ">
          <i class="fas fa-certificate"></i>
          Certificates
        </a>
      </div>
      <div class="nav-item">
        <a href="./documents.php" class="nav-link">
          <i class="fas fa-file-alt"></i>
          Documents
        </a>
      </div>
      <div class="nav-item">
        <a href="./inspection-history.php" class="nav-link">
          <i class="fas fa-history"></i>
          Inspection History
        </a>
      </div>
    </nav>

    <div class="nav-item">
      <a href="../../utility/logout.php" class="nav-link">
        <i class="fas fa-sign-out-alt"></i>
        Logout
      </a>
    </div>
  </div>


  <div class="main-content">
    <!-- Top Header -->
    <div class="top-header">
      <div class="d-flex align-items-center">
        <button class="mobile-menu-toggle me-3" onclick="toggleSidebar()">
          <i class="fas fa-bars"></i>
        </button>
        <h4 class="mb-0">My Establishments</h4>
      </div>
      <div class="admin-info">
        <div class="admin-avatar"><?php echo strtoupper(substr($row['fullname'] ?? 'U', 0, 1)) . strtoupper(substr(strstr($row['fullname'] ?? '', ' '), 1, 1)); ?></div>
        <span class="ms-2"><?php echo htmlspecialchars($row['fullname'] ?? 'Owner'); ?></span>
      </div>
    </div>

    <div class="content-area">
      <!-- Filter Section -->
      <div class="filter-card">
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Status</label>
            <select class="form-select" id="statusFilter">
              <option value="">All Statuses</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Business Type</label>
            <select class="form-select" id="typeFilter">
              <option value="">All Types</option>
              <optgroup label="Residential">
                <option value="Single-Family Residential">Single-Family Residential</option>
                <option value="Multi-Family Residential">Multi-Family Residential / Apartment</option>
                <option value="Condominium">Condominium</option>
                <option value="Boarding House">Boarding House / Dormitory</option>
              </optgroup>
              <optgroup label="Commercial">
                <option value="Shopping Mall">Shopping Mall / Commercial Center</option>
                <option value="Supermarket / Grocery">Supermarket / Grocery</option>
                <option value="Retail Store">Retail Store</option>
                <option value="Convenience Store">Convenience Store</option>
                <option value="Public Market">Public Market / Palengke</option>
                <option value="Ukay-Ukay / Thrift Shop">Ukay-Ukay / Thrift Shop</option>
              </optgroup>
              <optgroup label="Food &amp; Beverage">
                <option value="Restaurant">Restaurant / Eatery</option>
                <option value="Fast Food">Fast Food</option>
                <option value="Bakery / Pastry Shop">Bakery / Pastry Shop</option>
                <option value="Catering Service">Catering Service</option>
                <option value="Bar / Night Club">Bar / Night Club / KTV</option>
                <option value="Coffee Shop">Coffee Shop / Café</option>
                <option value="Food Stall / Canteen">Food Stall / Canteen</option>
              </optgroup>
              <optgroup label="Lodging">
                <option value="Hotel">Hotel</option>
                <option value="Motel / Inn">Motel / Inn</option>
                <option value="Pension House">Pension House / Hostel</option>
                <option value="Resort">Resort</option>
              </optgroup>
              <optgroup label="Educational">
                <option value="Elementary School">Elementary School</option>
                <option value="High School">High School / Senior High School</option>
                <option value="College / University">College / University</option>
                <option value="Vocational / Technical School">Vocational / Technical School</option>
                <option value="Daycare / Pre-school">Daycare / Pre-school</option>
                <option value="Tutorial / Review Center">Tutorial / Review Center</option>
              </optgroup>
              <optgroup label="Health Care">
                <option value="Hospital">Hospital</option>
                <option value="Clinic">Clinic / Medical Center</option>
                <option value="Pharmacy / Drugstore">Pharmacy / Drugstore</option>
                <option value="Dental Clinic">Dental Clinic</option>
                <option value="Veterinary Clinic">Veterinary Clinic</option>
                <option value="Optical Shop">Optical Shop</option>
              </optgroup>
              <optgroup label="Industrial">
                <option value="Factory / Manufacturing">Factory / Manufacturing Plant</option>
                <option value="Warehouse / Storage">Warehouse / Storage Facility</option>
                <option value="LPG / Gas Depot">LPG / Gas Depot</option>
                <option value="Gasoline Station">Gasoline Station</option>
                <option value="Printing Press">Printing Press</option>
                <option value="Cold Storage">Cold Storage / Ice Plant</option>
              </optgroup>
              <optgroup label="Automotive">
                <option value="Auto Repair Shop">Auto Repair Shop / Vulcanizing</option>
                <option value="Car Wash">Car Wash</option>
                <option value="Auto Parts Store">Auto Parts Store</option>
                <option value="Motorcycle Shop">Motorcycle Shop</option>
              </optgroup>
              <optgroup label="Financial &amp; Professional Services">
                <option value="Bank">Bank / Savings Bank</option>
                <option value="Pawnshop">Pawnshop</option>
                <option value="Money Changer / Remittance">Money Changer / Remittance</option>
                <option value="Insurance Office">Insurance Office</option>
                <option value="Law / Accounting Office">Law / Accounting Office</option>
                <option value="Real Estate Office">Real Estate Office</option>
                <option value="BPO / Call Center">BPO / Call Center</option>
              </optgroup>
              <optgroup label="Personal Services">
                <option value="Salon / Barbershop">Salon / Barbershop</option>
                <option value="Laundry Shop">Laundry Shop</option>
                <option value="Gym / Fitness Center">Gym / Fitness Center</option>
                <option value="Spa / Massage">Spa / Massage</option>
                <option value="Photography Studio">Photography Studio</option>
              </optgroup>
              <optgroup label="Entertainment">
                <option value="Cinema / Theater">Cinema / Theater</option>
                <option value="Arcade / Game Center">Arcade / Game Center</option>
                <option value="Sports Facility">Sports Facility / Gym / Coliseum</option>
                <option value="Events Venue">Events Venue / Reception Hall</option>
                <option value="Internet Cafe">Internet Café / Computer Shop</option>
              </optgroup>
              <optgroup label="Religious &amp; Institutional">
                <option value="Church / Religious Institution">Church / Religious Institution</option>
                <option value="Government Office">Government Office</option>
                <option value="Non-Government Organization">NGO / Non-Profit Organization</option>
                <option value="Jail / Detention Center">Jail / Detention Center</option>
              </optgroup>
              <optgroup label="Transportation &amp; Logistics">
                <option value="Bus / Jeepney Terminal">Bus / Jeepney Terminal</option>
                <option value="Logistics / Courier">Logistics / Courier Facility</option>
                <option value="Parking Facility">Parking Facility</option>
              </optgroup>
              <optgroup label="Agriculture">
                <option value="Rice Mill">Rice Mill</option>
                <option value="Poultry / Livestock">Poultry / Livestock Farm</option>
                <option value="Slaughterhouse">Slaughterhouse</option>
                <option value="Agri-Supply Store">Agri-Supply Store</option>
              </optgroup>
              <optgroup label="Other">
                <option value="Other">Other</option>
              </optgroup>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Registration Date</label>
            <input type="date" class="form-control" id="dateFilter" />
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-bfp-red me-2" onclick="applyFilters()">
              <i class="fas fa-search"></i> Search
            </button>
            <button
              class="btn btn-outline-secondary"
              onclick="resetFilters()">
              <i class="fas fa-undo"></i> Reset
            </button>
          </div>
        </div>
      </div>

      <!-- Establishments Table -->
      <div class="establishments-table">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 style="color: var(--bfp-red)">Registered Establishments</h4>
          <button
            class="btn btn-success"
            data-bs-toggle="modal"
            data-bs-target="#addEstablishmentModal">
            <i class="fas fa-plus"></i> Add Establishment
          </button>
        </div>

        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Establishment Name</th>
                <th>Business Type</th>
                <th>Contact Number</th>
                <th>Ownership Type</th>
                <th>Status</th>
                <th>Last Inspection</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="establishmentsTableBody">

            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Establishment Modal -->
  <div class="modal fade" id="addEstablishmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div
          class="modal-header"
          style="background-color: var(--bfp-red); color: white">
          <h5 class="modal-title">Add New Establishment</h5>
          <button
            type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="addEstablishmentForm">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Establishment Name *</label>
                  <input
                    type="text"
                    class="form-control"
                    id="businessName"
                    required />
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Business Type *</label>
                  <select class="form-select" id="businessType" required>
                    <option value="" disabled selected>-- Select Business Type --</option>
                    <optgroup label="Residential">
                      <option value="Single-Family Residential">Single-Family Residential</option>
                      <option value="Multi-Family Residential">Multi-Family Residential / Apartment</option>
                      <option value="Condominium">Condominium</option>
                      <option value="Boarding House">Boarding House / Dormitory</option>
                    </optgroup>
                    <optgroup label="Commercial">
                      <option value="Shopping Mall">Shopping Mall / Commercial Center</option>
                      <option value="Supermarket / Grocery">Supermarket / Grocery</option>
                      <option value="Retail Store">Retail Store</option>
                      <option value="Convenience Store">Convenience Store</option>
                      <option value="Public Market">Public Market / Palengke</option>
                      <option value="Ukay-Ukay / Thrift Shop">Ukay-Ukay / Thrift Shop</option>
                    </optgroup>
                    <optgroup label="Food &amp; Beverage">
                      <option value="Restaurant">Restaurant / Eatery</option>
                      <option value="Fast Food">Fast Food</option>
                      <option value="Bakery / Pastry Shop">Bakery / Pastry Shop</option>
                      <option value="Catering Service">Catering Service</option>
                      <option value="Bar / Night Club">Bar / Night Club / KTV</option>
                      <option value="Coffee Shop">Coffee Shop / Café</option>
                      <option value="Food Stall / Canteen">Food Stall / Canteen</option>
                    </optgroup>
                    <optgroup label="Lodging">
                      <option value="Hotel">Hotel</option>
                      <option value="Motel / Inn">Motel / Inn</option>
                      <option value="Pension House">Pension House / Hostel</option>
                      <option value="Resort">Resort</option>
                    </optgroup>
                    <optgroup label="Educational">
                      <option value="Elementary School">Elementary School</option>
                      <option value="High School">High School / Senior High School</option>
                      <option value="College / University">College / University</option>
                      <option value="Vocational / Technical School">Vocational / Technical School</option>
                      <option value="Daycare / Pre-school">Daycare / Pre-school</option>
                      <option value="Tutorial / Review Center">Tutorial / Review Center</option>
                    </optgroup>
                    <optgroup label="Health Care">
                      <option value="Hospital">Hospital</option>
                      <option value="Clinic">Clinic / Medical Center</option>
                      <option value="Pharmacy / Drugstore">Pharmacy / Drugstore</option>
                      <option value="Dental Clinic">Dental Clinic</option>
                      <option value="Veterinary Clinic">Veterinary Clinic</option>
                      <option value="Optical Shop">Optical Shop</option>
                    </optgroup>
                    <optgroup label="Industrial">
                      <option value="Factory / Manufacturing">Factory / Manufacturing Plant</option>
                      <option value="Warehouse / Storage">Warehouse / Storage Facility</option>
                      <option value="LPG / Gas Depot">LPG / Gas Depot</option>
                      <option value="Gasoline Station">Gasoline Station</option>
                      <option value="Printing Press">Printing Press</option>
                      <option value="Cold Storage">Cold Storage / Ice Plant</option>
                    </optgroup>
                    <optgroup label="Automotive">
                      <option value="Auto Repair Shop">Auto Repair Shop / Vulcanizing</option>
                      <option value="Car Wash">Car Wash</option>
                      <option value="Auto Parts Store">Auto Parts Store</option>
                      <option value="Motorcycle Shop">Motorcycle Shop</option>
                    </optgroup>
                    <optgroup label="Financial &amp; Professional Services">
                      <option value="Bank">Bank / Savings Bank</option>
                      <option value="Pawnshop">Pawnshop</option>
                      <option value="Money Changer / Remittance">Money Changer / Remittance</option>
                      <option value="Insurance Office">Insurance Office</option>
                      <option value="Law / Accounting Office">Law / Accounting Office</option>
                      <option value="Real Estate Office">Real Estate Office</option>
                      <option value="BPO / Call Center">BPO / Call Center</option>
                    </optgroup>
                    <optgroup label="Personal Services">
                      <option value="Salon / Barbershop">Salon / Barbershop</option>
                      <option value="Laundry Shop">Laundry Shop</option>
                      <option value="Gym / Fitness Center">Gym / Fitness Center</option>
                      <option value="Spa / Massage">Spa / Massage</option>
                      <option value="Photography Studio">Photography Studio</option>
                    </optgroup>
                    <optgroup label="Entertainment">
                      <option value="Cinema / Theater">Cinema / Theater</option>
                      <option value="Arcade / Game Center">Arcade / Game Center</option>
                      <option value="Sports Facility">Sports Facility / Gym / Coliseum</option>
                      <option value="Events Venue">Events Venue / Reception Hall</option>
                      <option value="Internet Cafe">Internet Café / Computer Shop</option>
                    </optgroup>
                    <optgroup label="Religious &amp; Institutional">
                      <option value="Church / Religious Institution">Church / Religious Institution</option>
                      <option value="Government Office">Government Office</option>
                      <option value="Non-Government Organization">NGO / Non-Profit Organization</option>
                      <option value="Jail / Detention Center">Jail / Detention Center</option>
                    </optgroup>
                    <optgroup label="Transportation &amp; Logistics">
                      <option value="Bus / Jeepney Terminal">Bus / Jeepney Terminal</option>
                      <option value="Logistics / Courier">Logistics / Courier Facility</option>
                      <option value="Parking Facility">Parking Facility</option>
                    </optgroup>
                    <optgroup label="Agriculture">
                      <option value="Rice Mill">Rice Mill</option>
                      <option value="Poultry / Livestock">Poultry / Livestock Farm</option>
                      <option value="Slaughterhouse">Slaughterhouse</option>
                      <option value="Agri-Supply Store">Agri-Supply Store</option>
                    </optgroup>
                    <optgroup label="Other">
                      <option value="Other">Other</option>
                    </optgroup>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Ownership Type *</label>
                  <input
                    type="text"
                    class="form-control"
                    id="ownershipType"
                    required />
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">TIN (Tax Identification Number) *</label>
                  <input
                    type="text"
                    class="form-control"
                    id="tinNumber"
                    required />
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Contact Number *</label>
                  <input
                    type="text"
                    class="form-control"
                    id="contactNum"
                    required />
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Email Address *</label>
                  <input
                    type="email"
                    class="form-control"
                    id="emailAdd"
                    required />
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Address *</label>
              <textarea
                class="form-control"
                id="address"
                rows="3"
                required></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Coordinates</label>
              <div class="coordinates-display">
                <p id="coordinatesDisplay" class="mb-0">
                  <strong>Longitude:</strong>
                  <span id="longitude">Not selected</span> |
                  <strong>Latitude:</strong>
                  <span id="latitude">Not selected</span>
                </p>
              </div>
              <button
                type="button"
                class="btn btn-outline-primary"
                onclick="openMapModal()">
                <i class="fas fa-map-marker-alt"></i> Select on Map
              </button>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Fire Safety Evaluation Clearance (FSEC) *</label>
                  <input type="file" class="form-control" id="FSEC" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Occupancy Permit *</label>
                  <input type="file" class="form-control" id="occupancyPermit" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Business Permit *</label>
                  <input type="file" class="form-control" id="businessPermit" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Valid ID (Owner/Representative) *</label>
                  <input type="file" class="form-control" id="businessPermit" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Building Plans/Floor Plan *</label>
                  <input type="file" class="form-control" id="plans" required>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal">
            Cancel
          </button>
          <button
            type="button"
            class="btn btn-bfp-red"
            onclick="saveEstablishment()">
            <i class="fas fa-save"></i> Save Establishment
          </button>
        </div>
      </div>
    </div>
  </div>
  <!-- View Establishment Modal -->
  <div class="modal fade" id="viewEstablishmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div
          class="modal-header"
          style="background-color: var(--bfp-red); color: white">
          <h5 class="modal-title">View Establishment Details</h5>
          <button
            type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <h6>Business Information</h6>
              <p>
                <strong>Name:</strong> <span id="viewBusinessName">-</span>
              </p>
              <p>
                <strong>Type:</strong> <span id="viewBusinessType">-</span>
              </p>
              <p>
                <strong>BFP Reg. No.:</strong>
                <span id="viewBfpRegNo">-</span>
              </p>
              <p><strong>Status:</strong> <span id="viewStatus">-</span></p>
            </div>
            <div class="col-md-6">
              <h6>Location Details</h6>
              <p><strong>Address:</strong> <span id="viewAddress">-</span></p>
              <p>
                <strong>Coordinates:</strong>
                <span id="viewCoordinates">-</span>
              </p>
              <p>
                <strong>Last Inspection:</strong>
                <span id="viewLastInspection">-</span>
              </p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Establishment Modal -->
  <div class="modal fade" id="editEstablishmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div
          class="modal-header"
          style="background-color: var(--bfp-gold); color: var(--bfp-dark)">
          <h5 class="modal-title">Edit Establishment</h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="editEstablishmentForm">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Establishment Name *</label>
                  <input
                    type="text"
                    class="form-control"
                    id="editEstablishmentName"
                    required />
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Ownership Type</label>
                  <input
                    type="text"
                    class="form-control"
                    id="editOwnershipType" />
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Business Type *</label>
                  <select class="form-select" id="editBusinessType" required>
                    <option value="" disabled>-- Select Business Type --</option>
                    <optgroup label="Residential">
                      <option value="Single-Family Residential">Single-Family Residential</option>
                      <option value="Multi-Family Residential">Multi-Family Residential / Apartment</option>
                      <option value="Condominium">Condominium</option>
                      <option value="Boarding House">Boarding House / Dormitory</option>
                    </optgroup>
                    <optgroup label="Commercial">
                      <option value="Shopping Mall">Shopping Mall / Commercial Center</option>
                      <option value="Supermarket / Grocery">Supermarket / Grocery</option>
                      <option value="Retail Store">Retail Store</option>
                      <option value="Convenience Store">Convenience Store</option>
                      <option value="Public Market">Public Market / Palengke</option>
                      <option value="Ukay-Ukay / Thrift Shop">Ukay-Ukay / Thrift Shop</option>
                    </optgroup>
                    <optgroup label="Food &amp; Beverage">
                      <option value="Restaurant">Restaurant / Eatery</option>
                      <option value="Fast Food">Fast Food</option>
                      <option value="Bakery / Pastry Shop">Bakery / Pastry Shop</option>
                      <option value="Catering Service">Catering Service</option>
                      <option value="Bar / Night Club">Bar / Night Club / KTV</option>
                      <option value="Coffee Shop">Coffee Shop / Café</option>
                      <option value="Food Stall / Canteen">Food Stall / Canteen</option>
                    </optgroup>
                    <optgroup label="Lodging">
                      <option value="Hotel">Hotel</option>
                      <option value="Motel / Inn">Motel / Inn</option>
                      <option value="Pension House">Pension House / Hostel</option>
                      <option value="Resort">Resort</option>
                    </optgroup>
                    <optgroup label="Educational">
                      <option value="Elementary School">Elementary School</option>
                      <option value="High School">High School / Senior High School</option>
                      <option value="College / University">College / University</option>
                      <option value="Vocational / Technical School">Vocational / Technical School</option>
                      <option value="Daycare / Pre-school">Daycare / Pre-school</option>
                      <option value="Tutorial / Review Center">Tutorial / Review Center</option>
                    </optgroup>
                    <optgroup label="Health Care">
                      <option value="Hospital">Hospital</option>
                      <option value="Clinic">Clinic / Medical Center</option>
                      <option value="Pharmacy / Drugstore">Pharmacy / Drugstore</option>
                      <option value="Dental Clinic">Dental Clinic</option>
                      <option value="Veterinary Clinic">Veterinary Clinic</option>
                      <option value="Optical Shop">Optical Shop</option>
                    </optgroup>
                    <optgroup label="Industrial">
                      <option value="Factory / Manufacturing">Factory / Manufacturing Plant</option>
                      <option value="Warehouse / Storage">Warehouse / Storage Facility</option>
                      <option value="LPG / Gas Depot">LPG / Gas Depot</option>
                      <option value="Gasoline Station">Gasoline Station</option>
                      <option value="Printing Press">Printing Press</option>
                      <option value="Cold Storage">Cold Storage / Ice Plant</option>
                    </optgroup>
                    <optgroup label="Automotive">
                      <option value="Auto Repair Shop">Auto Repair Shop / Vulcanizing</option>
                      <option value="Car Wash">Car Wash</option>
                      <option value="Auto Parts Store">Auto Parts Store</option>
                      <option value="Motorcycle Shop">Motorcycle Shop</option>
                    </optgroup>
                    <optgroup label="Financial &amp; Professional Services">
                      <option value="Bank">Bank / Savings Bank</option>
                      <option value="Pawnshop">Pawnshop</option>
                      <option value="Money Changer / Remittance">Money Changer / Remittance</option>
                      <option value="Insurance Office">Insurance Office</option>
                      <option value="Law / Accounting Office">Law / Accounting Office</option>
                      <option value="Real Estate Office">Real Estate Office</option>
                      <option value="BPO / Call Center">BPO / Call Center</option>
                    </optgroup>
                    <optgroup label="Personal Services">
                      <option value="Salon / Barbershop">Salon / Barbershop</option>
                      <option value="Laundry Shop">Laundry Shop</option>
                      <option value="Gym / Fitness Center">Gym / Fitness Center</option>
                      <option value="Spa / Massage">Spa / Massage</option>
                      <option value="Photography Studio">Photography Studio</option>
                    </optgroup>
                    <optgroup label="Entertainment">
                      <option value="Cinema / Theater">Cinema / Theater</option>
                      <option value="Arcade / Game Center">Arcade / Game Center</option>
                      <option value="Sports Facility">Sports Facility / Gym / Coliseum</option>
                      <option value="Events Venue">Events Venue / Reception Hall</option>
                      <option value="Internet Cafe">Internet Café / Computer Shop</option>
                    </optgroup>
                    <optgroup label="Religious &amp; Institutional">
                      <option value="Church / Religious Institution">Church / Religious Institution</option>
                      <option value="Government Office">Government Office</option>
                      <option value="Non-Government Organization">NGO / Non-Profit Organization</option>
                      <option value="Jail / Detention Center">Jail / Detention Center</option>
                    </optgroup>
                    <optgroup label="Transportation &amp; Logistics">
                      <option value="Bus / Jeepney Terminal">Bus / Jeepney Terminal</option>
                      <option value="Logistics / Courier">Logistics / Courier Facility</option>
                      <option value="Parking Facility">Parking Facility</option>
                    </optgroup>
                    <optgroup label="Agriculture">
                      <option value="Rice Mill">Rice Mill</option>
                      <option value="Poultry / Livestock">Poultry / Livestock Farm</option>
                      <option value="Slaughterhouse">Slaughterhouse</option>
                      <option value="Agri-Supply Store">Agri-Supply Store</option>
                    </optgroup>
                    <optgroup label="Other">
                      <option value="Other">Other</option>
                    </optgroup>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">TIN (Tax Identification Number)</label>
                  <input
                    type="text"
                    class="form-control"
                    id="editTINNo" />
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Contact Number</label>
                  <input
                    type="text"
                    class="form-control"
                    id="editContactNum" />
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Email Address</label>
                  <input
                    type="text"
                    class="form-control"
                    id="editEmailAdd" />
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Address *</label>
              <textarea
                class="form-control"
                id="editAddress"
                rows="3"
                required></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Coordinates</label>
              <div class="coordinates-display">
                <p id="editCoordinatesDisplay" class="mb-0">
                  <strong>Longitude:</strong>
                  <span id="editLongitude">Not selected</span> |
                  <strong>Latitude:</strong>
                  <span id="editLatitude">Not selected</span>
                </p>
              </div>
              <button
                type="button"
                class="btn btn-outline-primary"
                onclick="openMapModalForEdit()">
                <i class="fas fa-map-marker-alt"></i> Update Location
              </button>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal">
            Cancel
          </button>
          <button
            type="button"
            class="btn btn-bfp-gold"
            onclick="updateEstablishment()">
            <i class="fas fa-save"></i> Update Establishment
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Map Modal -->
  <div class="modal fade" id="mapModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div
          class="modal-header"
          style="background-color: var(--bfp-red); color: white">
          <h5 class="modal-title">Select Location on Map</h5>
          <button
            type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div id="map"></div>
          <div class="mt-3">
            <p class="mb-2"><strong>Selected Coordinates:</strong></p>
            <p id="selectedCoordinates" class="text-muted">
              Click on the map to select a location
            </p>
          </div>
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal">
            Cancel
          </button>
          <button
            type="button"
            class="btn btn-bfp-red"
            id="selectLocationBtn"
            onclick="selectLocation()"
            disabled>
            <i class="fas fa-check"></i> Select Location
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>

  <script src="../../assets/scripts/user-my-establishments.js"></script>
</body>

</html>