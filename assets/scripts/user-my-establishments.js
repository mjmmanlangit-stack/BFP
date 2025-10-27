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
  const res = await fetch("../../utility/getMyEstablishment.php")
  const json = await res.json()

  json.forEach(e=>{
    establishments.push({...e, coordinates:{lat: parseFloat(e.lat), lng: parseFloat(e.lng)}, status:"active"})
  })
  console.log(establishments)
  updateEstablishmentsTable();
}

getEstablisment()

let map;
let selectedMarker;
let selectedCoords = null;
let isEditMode = false;

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
    document.getElementById("editEstablishmentName").value = establishment.name;
    document.getElementById("editOwnershipType").value = establishment.bfpRegNo;
    document.getElementById("editAddress").value = establishment.address;
    document.getElementById("editBusinessType").value = establishment.type;
    document.getElementById("editLongitude").textContent =
      establishment.coordinates.lng.toFixed(6);
    document.getElementById("editLatitude").textContent =
      establishment.coordinates.lat.toFixed(6);

       const editModalEl = new bootstrap.Modal(
      document.getElementById("editEstablishmentModal")
    );
    editModalEl.show();

    // Store the ID for updating
    document
      .getElementById("editEstablishmentForm")
      .setAttribute("data-establishment-id", id);
  }
}

async function saveEstablishment() {
  const form = document.getElementById("addEstablishmentForm");
  if (form.checkValidity()) {
    const businessName = document.getElementById("businessName").value;
    const bfpRegNo = document.getElementById("bfpRegNo").value;
    const address = document.getElementById("address").value;
    const longitude = document.getElementById("longitude").textContent;
    const latitude = document.getElementById("latitude").textContent;
    const business_type = document.getElementById("businessType").textContent;

    // Add to establishments array (in real app, this would be an API call)
    const newEstablishment = {
      id: establishments.length + 1,
      name: businessName,
      type: business_type, // Default type
      bfpRegNo: bfpRegNo,
      address: address,
      status: "Active",
      lastInspection: "Not yet",
      coordinates: {
        lat: longitude !== "Not selected" ? parseFloat(latitude) : 0,
        lng: longitude !== "Not selected" ? parseFloat(longitude) : 0,
      },
    };

    establishments.push(newEstablishment);
    updateEstablishmentsTable();
    const res =  await fetch('../../utility/addNewEstablishment.php',{
      method:"POST",
      headers:{'Content-Type': 'application/json'},
      body:JSON.stringify({
        business_name:businessName,
        registration_no: bfpRegNo,
        address: address,
        x_coordinate: longitude,
        y_coordinate: latitude,
        type: business_type,
        
      })
    })
    const json = await res.json()
    // console.log(json)
    // Close modal and reset form
    const addModalEl = bootstrap.Modal.getInstance(
      document.getElementById("addEstablishmentModal")
    );
    addModalEl.hide();
    form.reset();
    document.getElementById("longitude").textContent = "Not selected";
    document.getElementById("latitude").textContent = "Not selected";

    alert("Establishment added successfully!");
  } else {
    form.reportValidity();
  }
}

async function updateEstablishment() {
  const form = document.getElementById("editEstablishmentForm");
  const establishmentId = parseInt(form.getAttribute("data-establishment-id"));

  if (form.checkValidity()) {
    const businessName = document.getElementById("editBusinessName").value;
    const bfpRegNo = document.getElementById("editBfpRegNo").value;
    const address = document.getElementById("editAddress").value;
    const longitude = document.getElementById("editLongitude").textContent;
    const latitude = document.getElementById("editLatitude").textContent;
    const business_type = document.getElementById("editBusinessType").value;
    const res =  await fetch('../../utility/updateMyEstablishment.php',{
      method:"POST",
      headers:{'Content-Type': 'application/json'},
      body:JSON.stringify({
        business_name:businessName,
        registration_no: bfpRegNo,
        address: address,
        x_coordinate: longitude,
        y_coordinate: latitude,
        type: business_type,
        id: establishmentId
      })
    })
    const json = await res.json()
    console.log(json)
    // Update establishment in array (in real app, this would be an API call)
    const establishmentIndex = establishments.findIndex(
      (e) => e.id === establishmentId
    );
    if (establishmentIndex !== -1) {
      establishments[establishmentIndex].name = businessName;
      establishments[establishmentIndex].bfpRegNo = bfpRegNo;
      establishments[establishmentIndex].address = address;
      establishments[establishmentIndex].type = business_type;
      if (longitude !== "Not selected") {
        establishments[establishmentIndex].coordinates.lng =
          parseFloat(longitude);
        establishments[establishmentIndex].coordinates.lat =
          parseFloat(latitude);
      }

      updateEstablishmentsTable();

      // Close modal
      const editModalEl = bootstrap.Modal.getInstance(
        document.getElementById("editEstablishmentModal")
      );
      editModalEl.hide();

      alert("Establishment updated successfully!");
    }
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
