// Initialize Map
var map = L.map("map").setView([13.5833, 124.2374], 11);

L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
  attribution: "© OpenStreetMap contributors",
}).addTo(map);

// Add markers for establishments
const establishments = [
  {
   lat: 13.5843,
        lng: 124.2397,
        name: "Virac Town Center",
        status: "scheduled",
      },
      {
        lat: 13.5967,
        lng: 124.2303,
        name: "Catanduanes State University",
        status: "scheduled",
      },
      {
        lat: 13.5951,
        lng: 124.2458,
        name: "Virac Cathedral",
        status: "scheduled",
      },
      {
        lat: 13.6537,
        lng: 124.3731,
        name: "Binurong Point, Baras",
        status: "ongoing",
      },
      {
        lat: 13.6444,
        lng: 124.3812,
        name: "Puraran Beach, Baras",
        status: "ongoing",
      },
      {
        lat: 13.6123,
        lng: 124.3288,
        name: "Bato Church",
        status: "completed",
      },
      {
        lat: 13.5767,
        lng: 124.2884,
        name: "Maribina Falls, Bato",
        status: "completed",
      },
      {
        lat: 13.6914,
        lng: 124.3837,
        name: "Balacay Point, Baras",
        status: "completed",
      },
      {
        lat: 13.7215,
        lng: 124.3089,
        name: "Padis Point View Deck, Gigmoto",
        status: "completed",
      },
      {
        lat: 13.7128,
        lng: 124.1071,
        name: "PAGASA Weather Radar Station, San Andres",
        status: "pending",
      },
      {
        lat: 13.8534,
        lng: 124.3097,
        name: "Pandan Beach Resort, Pandan",
        status: "pending",
      }
];

establishments.forEach((est) => {
  let iconColor =
    est.status === "compliant"
      ? "green"
      : est.status === "non-compliant"
      ? "red"
      : "orange";

  let marker = L.marker([est.lat, est.lng], {
    icon: L.divIcon({
      html: `<div style="background: ${iconColor}; width: 15px; height: 15px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
      iconSize: [15, 15],
      className: "custom-marker",
    }),
  }).addTo(map);

  marker.bindPopup(`
                <strong>${est.name}</strong><br>
                Status: <span style="color: ${iconColor}; font-weight: bold;">${est.status.toUpperCase()}</span><br>
                Type: ${est.type}<br>
                <button class="btn btn-sm btn-outline-primary mt-2" onclick="viewDetails('${
                  est.name
                }')">View Details</button>
            `);
});

// Initialize Compliance Trend Chart
const ctx = document.getElementById("complianceChart").getContext("2d");
const complianceChart = new Chart(ctx, {
  type: "line",
  data: {
    labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
    datasets: [
      {
        label: "Compliant Establishments",
        data: [85, 90, 88, 95, 102, 110],
        borderColor: "#28a745",
        backgroundColor: "rgba(40, 167, 69, 0.1)",
        fill: true,
        tension: 0.4,
      },
      {
        label: "Non-Compliant",
        data: [25, 20, 18, 15, 16, 14],
        borderColor: "#dc3545",
        backgroundColor: "rgba(220, 53, 69, 0.1)",
        fill: true,
        tension: 0.4,
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
    scales: {
      y: {
        beginAtZero: true,
        max: 120,
      },
    },
  },
});

// Animate stats cards on load
document.addEventListener("DOMContentLoaded", function () {
  const statsCards = document.querySelectorAll(".stats-card");
  statsCards.forEach((card, index) => {
    setTimeout(() => {
      card.style.opacity = "0";
      card.style.transform = "translateY(20px)";
      card.style.transition = "all 0.5s ease";

      setTimeout(() => {
        card.style.opacity = "1";
        card.style.transform = "translateY(0)";
      }, 50);
    }, index * 100);
  });
});

// Update stats in real-time (demo)
setInterval(() => {
  const pendingElement = document.querySelector(".stats-card.pending h3");
  const currentValue = parseInt(pendingElement.textContent);

  // Simulate random updates
  if (Math.random() > 0.7) {
    const change = Math.random() > 0.5 ? 1 : -1;
    const newValue = Math.max(0, currentValue + change);
    pendingElement.textContent = newValue;
  }
}, 10000);
