// Initialize Map
var map = L.map("map").setView([13.5833, 124.2374], 11);

L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
  attribution: "© OpenStreetMap contributors",
}).addTo(map);

// Fetch establishment markers from database
async function loadMapMarkers() {
  try {
    const res = await fetch('../../utility/getGISData.php');
    const d = await res.json();
    if (!d.success || !d.markers) return;

    d.markers.forEach((est) => {
      let iconColor = est.markerColor || '#6c757d';

      let marker = L.marker([est.lat, est.lng], {
        icon: L.divIcon({
          html: `<div style="background: ${iconColor}; width: 15px; height: 15px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
          iconSize: [15, 15],
          className: "custom-marker",
        }),
      }).addTo(map);

      marker.bindPopup(`
        <strong>${est.name}</strong><br>
        Status: <span style="color: ${iconColor}; font-weight: bold;">${(est.complianceStatus || est.inspectionStatus || 'N/A').toUpperCase()}</span><br>
        Type: ${est.type || 'N/A'}<br>
        Owner: ${est.ownerName || 'N/A'}<br>
      `);
    });
  } catch (e) { console.error('Map markers load error:', e); }
}

// Initialize Compliance Trend Chart with live data
let complianceChart = null;

async function loadComplianceChart() {
  try {
    const res = await fetch('../../utility/getReportData.php');
    const d = await res.json();
    if (!d.success) return;

    const trend = d.monthlyTrend || [];
    const labels = trend.map(t => t.month);
    const completed = trend.map(t => parseInt(t.completed) || 0);
    const scheduled = trend.map(t => parseInt(t.scheduled) || 0);

    const ctx = document.getElementById("complianceChart");
    if (!ctx) return;

    if (complianceChart) complianceChart.destroy();
    complianceChart = new Chart(ctx.getContext("2d"), {
      type: "line",
      data: {
        labels: labels.length ? labels : ['No Data'],
        datasets: [
          {
            label: "Completed Inspections",
            data: completed.length ? completed : [0],
            borderColor: "#28a745",
            backgroundColor: "rgba(40, 167, 69, 0.1)",
            fill: true,
            tension: 0.4,
          },
          {
            label: "Scheduled Inspections",
            data: scheduled.length ? scheduled : [0],
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
          },
        },
      },
    });
  } catch (e) { console.error('Chart load error:', e); }
}

// Animate stats cards on load
document.addEventListener("DOMContentLoaded", function () {
  loadMapMarkers();
  loadComplianceChart();

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
