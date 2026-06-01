// Sample establishments data
let establishments = [
  // {
  //   id: 1,
  //   name: "Delicious Restaurant",
  //   type: "Food Service",
  //   bfpRegNo: "BFP-VRC-2023-0456",
  //   address: "123 Rizal St., Virac",
  //   status: "Active",
  //   lastInspection: "May 15, 2023",
  //   coordinates: { lat: 13.5735, lng: 124.2307 },
  // },
  // {
  //   id: 2,
  //   name: "Juan's Hardware Store",
  //   type: "Retail",
  //   bfpRegNo: "BFP-VRC-2023-0789",
  //   address: "456 Quezon Ave., Virac",
  //   status: "Active",
  //   lastInspection: "April 10, 2023",
  //   coordinates: { lat: 13.575, lng: 124.232 },
  // },
  // {
  //   id: 3,
  //   name: "Cruz Family Residence",
  //   type: "Residential",
  //   bfpRegNo: "BFP-VRC-2023-0123",
  //   address: "789 Luna St., Virac",
  //   status: "Inactive",
  //   lastInspection: "December 5, 2022",
  //   coordinates: { lat: 13.572, lng: 124.229 },
  // },
];

async function getEstablisment(){
  try {
    const res  = await fetch("../../utility/getMyEstablishment.php");
    const json = await res.json();

    if (!Array.isArray(json)) {
      console.error('Failed to load establishments:', json);
      updateEstablishmentsTable();
      return;
    }

    // Reset before reload to prevent duplicates on re-fetch
    establishments = [];
    json.forEach(e => {
      establishments.push({
        ...e,
        coordinates: {
          lat: parseFloat(e.lat)  || 0,
          lng: parseFloat(e.lng) || 0
        }
      });
    });
    updateEstablishmentsTable();
  } catch (err) {
    console.error('Error fetching establishments:', err);
  }
}

getEstablisment()

let map;
let selectedMarker;
let selectedCoords = null;
let isEditMode = false;
let requiredDocuments = [];

const REQUIRED_DOCUMENT_TYPES = [
  'Fire Safety Evaluation Clearance (FSEC)',
  'Occupancy Permit',
  'Business Permit',
  'Valid ID (Owner/Representative)',
  'Building Plans/Floor Plan'
];

function onRequiredDocumentsSelected(input) {
  requiredDocuments = Array.from(input.files || []);
  renderRequiredDocumentsPreview();
}

function renderRequiredDocumentsPreview() {
  const preview = document.getElementById('requiredDocumentsPreview');
  if (!preview) {
    return;
  }

  if (!requiredDocuments.length) {
    preview.innerHTML = '';
    return;
  }

  const items = requiredDocuments.map((file, index) => {
    const docLabel = REQUIRED_DOCUMENT_TYPES[index] || `Additional Document ${index + 1}`;
    const sizeLabel = (file.size / 1024 / 1024).toFixed(2) + ' MB';
    return `
      <div class="d-flex justify-content-between align-items-center border rounded px-3 py-2 mb-2 bg-light">
        <div class="me-2">
          <div class="fw-semibold">${docLabel}</div>
          <div class="text-muted small text-truncate" style="max-width: 340px;">${file.name}</div>
        </div>
        <div class="text-muted small">${sizeLabel}</div>
      </div>`;
  }).join('');

  const warning = requiredDocuments.length < 5
    ? '<div class="alert alert-warning py-2 mb-0 small">Please select at least 5 files before saving.</div>'
    : '';

  preview.innerHTML = items + warning;
}

// Initialize map when modal is shown
document
  .getElementById("mapModal")
  .addEventListener("shown.bs.modal", function () {
    if (!map) {
      map = L.map("map").setView([13.5735, 124.2307], 13);

      L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "© OpenStreetMap contributors",
      }).addTo(map);

      map.on("click", function (e) {
        if (selectedMarker) {
          map.removeLayer(selectedMarker);
        }

        selectedMarker = L.marker(e.latlng).addTo(map);
        selectedCoords = e.latlng;

        document.getElementById(
          "selectedCoordinates"
        ).innerHTML = `Longitude: ${e.latlng.lng.toFixed(
          6
        )}, Latitude: ${e.latlng.lat.toFixed(6)}`;

        document.getElementById("selectLocationBtn").disabled = false;
      });
    }

    setTimeout(() => {
      map.invalidateSize();
    }, 300);
  });

function openMapModal() {
  isEditMode = false;
  const mapModalEl = new bootstrap.Modal(document.getElementById("mapModal"));
  mapModalEl.show();
}

function openMapModalForEdit() {
  isEditMode = true;
  const mapModalEl = new bootstrap.Modal(document.getElementById("mapModal"));
  mapModalEl.show();

      const addEstablishmentModal = document.getElementById('addEstablishmentModal');
      if (addEstablishmentModal) {
        addEstablishmentModal.addEventListener('hidden.bs.modal', function () {
          requiredDocuments = [];
          const input = document.getElementById('requiredDocuments');
          if (input) {
            input.value = '';
          }
          renderRequiredDocumentsPreview();
        });
      }
}

function selectLocation() {
  if (selectedCoords) {
    if (isEditMode) {
      document.getElementById("editLongitude").textContent =
        selectedCoords.lng.toFixed(6);
      document.getElementById("editLatitude").textContent =
        selectedCoords.lat.toFixed(6);
    } else {
      document.getElementById("longitude").textContent =
        selectedCoords.lng.toFixed(6);
      document.getElementById("latitude").textContent =
        selectedCoords.lat.toFixed(6);
    }

    const mapModalEl = bootstrap.Modal.getInstance(
      document.getElementById("mapModal")
    );
    mapModalEl.hide();

    // Reset modal state
    selectedCoords = null;
    if (selectedMarker) {
      map.removeLayer(selectedMarker);
      selectedMarker = null;
    }
    document.getElementById("selectedCoordinates").textContent =
      "Click on the map to select a location";
    document.getElementById("selectLocationBtn").disabled = true;
  }
}

function viewEstablishment(id) {
  const establishment = establishments.find((e) => e.id === id);
  if (establishment) {
    document.getElementById("viewBusinessName").textContent =
      establishment.name;
    document.getElementById("viewBusinessType").textContent =
      establishment.type;
    document.getElementById("viewBfpRegNo").textContent =
      establishment.bfpRegNo;
    document.getElementById(
      "viewStatus"
    ).innerHTML = `<span class="status-badge status-${establishment.status.toLowerCase()}">${
      establishment.status
    }</span>`;
    document.getElementById("viewAddress").textContent = establishment.address;
    document.getElementById(
      "viewCoordinates"
    ).textContent = `${establishment.coordinates.lng.toFixed(
      6
    )}, ${establishment.coordinates.lat.toFixed(6)}`;
    document.getElementById("viewLastInspection").textContent =
      establishment.lastInspection;

    const viewModalEl = new bootstrap.Modal(
      document.getElementById("viewEstablishmentModal")
    );
    viewModalEl.show();
  }
}

function editEstablishment(id) {
  const establishment = establishments.find((e) => e.id === id);
  if (establishment) {
    document.getElementById("editEstablishmentName").value = establishment.name  || '';
    document.getElementById("editBusinessType").value      = establishment.type  || '';
    document.getElementById("editOwnershipType").value     = establishment.ownership_type || '';
    document.getElementById("editTINNo").value             = establishment.tin_number     || '';
    document.getElementById("editContactNum").value        = establishment.contact_number || '';
    document.getElementById("editEmailAdd").value          = establishment.contact_email  || '';
    document.getElementById("editAddress").value           = establishment.address || '';

    const lng = establishment.coordinates?.lng;
    const lat = establishment.coordinates?.lat;
    document.getElementById("editLongitude").textContent = (lng && lng !== 0) ? lng.toFixed(6) : 'Not selected';
    document.getElementById("editLatitude").textContent  = (lat && lat !== 0) ? lat.toFixed(6) : 'Not selected';

    const editModalEl = new bootstrap.Modal(
      document.getElementById("editEstablishmentModal")
    );
    editModalEl.show();

    // Store the ID for the update call
    document
      .getElementById("editEstablishmentForm")
      .setAttribute("data-establishment-id", id);
  }
}

async function saveEstablishment() {
  const form = document.getElementById("addEstablishmentForm");
  if (form.checkValidity()) {
    const businessName   = document.getElementById("businessName").value;
    const businessType   = document.getElementById("businessType").value;
    const ownershipType  = document.getElementById("ownershipType").value;
    const tinNumber      = document.getElementById("tinNumber").value;
    const contactNum     = document.getElementById("contactNum").value;
    const emailAdd       = document.getElementById("emailAdd").value;
    const address        = document.getElementById("address").value;
    const longitude      = document.getElementById("longitude").textContent;
    const latitude       = document.getElementById("latitude").textContent;

    if (requiredDocuments.length < 5) {
      alert('Please select at least 5 required documents before saving.');
      return;
    }

    let res, json;
    try {
      const formData = new FormData();
      formData.append('business_name', businessName);
      formData.append('registration_no', '');
      formData.append('type', businessType);
      formData.append('ownership_type', ownershipType);
      formData.append('tin_number', tinNumber);
      formData.append('contact_number', contactNum);
      formData.append('contact_email', emailAdd);
      formData.append('address', address);
      formData.append('x_coordinate', longitude !== "Not selected" ? longitude : '');
      formData.append('y_coordinate', latitude !== "Not selected" ? latitude : '');
      requiredDocuments.forEach((file) => {
        formData.append('required_documents[]', file);
      });

      res  = await fetch('../../utility/addNewEstablishment.php', {
        method:  "POST",
        body: formData
      });
      json = await res.json();
    } catch (err) {
      alert("Network error: " + err.message);
      return;
    }

    if (json.error) {
      alert("Error: " + json.error);
      return;
    }

    // Close modal and reset form
    const addModalEl = bootstrap.Modal.getInstance(
      document.getElementById("addEstablishmentModal")
    );
    addModalEl.hide();
    form.reset();
    requiredDocuments = [];
    renderRequiredDocumentsPreview();
    document.getElementById("longitude").textContent = "Not selected";
    document.getElementById("latitude").textContent  = "Not selected";

    // Reload from server so IDs and data are always in sync with the DB
    await getEstablisment();
    alert("Establishment added successfully!");
  } else {
    form.reportValidity();
  }
}

async function updateEstablishment() {
  const form = document.getElementById("editEstablishmentForm");
  const establishmentId = parseInt(form.getAttribute("data-establishment-id"));

  if (form.checkValidity()) {
    const businessName  = document.getElementById("editEstablishmentName").value;
    const businessType  = document.getElementById("editBusinessType").value;
    const ownershipType = document.getElementById("editOwnershipType").value;
    const tinNumber     = document.getElementById("editTINNo").value;
    const contactNum    = document.getElementById("editContactNum").value;
    const emailAdd      = document.getElementById("editEmailAdd").value;
    const address       = document.getElementById("editAddress").value;
    const longitude     = document.getElementById("editLongitude").textContent;
    const latitude      = document.getElementById("editLatitude").textContent;

    let res, json;
    try {
      res  = await fetch('../../utility/updateMyEstablishment.php', {
        method:  "POST",
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          id:              establishmentId,
          business_name:  businessName,
          type:           businessType,
          ownership_type: ownershipType,
          tin_number:     tinNumber,
          contact_number: contactNum,
          contact_email:  emailAdd,
          address:        address,
          x_coordinate:  longitude !== "Not selected" ? longitude : '',
          y_coordinate:  latitude  !== "Not selected" ? latitude  : '',
          registration_no: '',
        })
      });
      json = await res.json();
    } catch (err) {
      alert("Network error: " + err.message);
      return;
    }

    if (json.error) {
      alert("Error: " + json.error);
      return;
    }

    // Close modal and reload from server
    const editModalEl = bootstrap.Modal.getInstance(
      document.getElementById("editEstablishmentModal")
    );
    editModalEl.hide();
    await getEstablisment();
    alert("Establishment updated successfully!");
  } else {
    form.reportValidity();
  }
}

function updateEstablishmentsTable() {
  const tbody = document.getElementById("establishmentsTableBody");
  tbody.innerHTML = "";

  establishments.forEach((establishment) => {
    const row = document.createElement("tr");
    row.innerHTML = `
                    <td>${establishment.name}</td>
                    <td>${establishment.type || "N/A"}</td>
                    <td>${establishment.bfpRegNo}</td>
                    <td>${establishment.address}</td>
                    <td><span class="status-badge status-${establishment.status.toLowerCase()}">${
      establishment.status
    }</span></td>
                    <td>${establishment.lastInspection}</td>
                    <td>
                      <div class="action-buttons">
                        <button class="btn btn-info btn-sm" onclick="viewEstablishment(${
                          establishment.id
                        })">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-bfp-gold btn-sm" onclick="editEstablishment(${
                          establishment.id
                        })">
                            <i class="fas fa-edit"></i>
                        </button>
                      </div>
                    </td>
                `;
    tbody.appendChild(row);
  });
}

function applyFilters() {
  const statusFilter = document.getElementById("statusFilter").value;
  const typeFilter = document.getElementById("typeFilter").value;
  const dateFilter = document.getElementById("dateFilter").value;

  let filteredEstablishments = establishments;

  if (statusFilter) {
    filteredEstablishments = filteredEstablishments.filter(
      (e) => e.status.toLowerCase() === statusFilter.toLowerCase()
    );
  }

  if (typeFilter) {
    filteredEstablishments = filteredEstablishments.filter((e) =>
      e.type.toLowerCase().includes(typeFilter.toLowerCase())
    );
  }

  if (dateFilter) {
    // Simple date filtering - in real app, would be more sophisticated
    filteredEstablishments = filteredEstablishments.filter(
      (e) => e.lastInspection.includes(dateFilter.split("-")[0]) // Filter by year
    );
  }

  // Update table with filtered data
  const tbody = document.getElementById("establishmentsTableBody");
  tbody.innerHTML = "";

  filteredEstablishments.forEach((establishment) => {
    const row = document.createElement("tr");
    row.innerHTML = `
                    <td>${establishment.name}</td>
                    <td>${establishment.type}</td>
                    <td>${establishment.bfpRegNo}</td>
                    <td>${establishment.address}</td>
                    <td><span class="status-badge status-${establishment.status.toLowerCase()}">${
      establishment.status
    }</span></td>
                    <td>${establishment.lastInspection}</td>
                    <td class="action-buttons">
                        <button class="btn btn-info btn-sm" onclick="viewEstablishment(${
                          establishment.id
                        })">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="btn btn-bfp-gold btn-sm" onclick="editEstablishment(${
                          establishment.id
                        })">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </td>
                `;
    tbody.appendChild(row);
  });
}

function resetFilters() {
  document.getElementById("statusFilter").value = "";
  document.getElementById("typeFilter").value = "";
  document.getElementById("dateFilter").value = "";
  updateEstablishmentsTable();
}

// Initialize page
document.addEventListener("DOMContentLoaded", function () {
  updateEstablishmentsTable();
});

// Reset map modal state when hidden
document
  .getElementById("mapModal")
  .addEventListener("hidden.bs.modal", function () {
    if (selectedMarker) {
      map.removeLayer(selectedMarker);
      selectedMarker = null;
    }
    selectedCoords = null;
    document.getElementById("selectedCoordinates").textContent =
      "Click on the map to select a location";
    document.getElementById("selectLocationBtn").disabled = true;
  });
