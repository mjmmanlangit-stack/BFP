// ── Owner Dashboard – real backend wiring ──────────────────────────────────
async function loadOwnerDashboard() {
  try {
    const res = await fetch("../../utility/getOwnerDashboard.php");
    const d   = await res.json();
    if (!d.success) return;

    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    set("stat-establishments", d.total_establishments ?? "—");
    set("stat-upcoming",       d.scheduled_inspections ?? "—");
    set("stat-certs",          d.active_certificates ?? "—");
    set("stat-completed",      d.completed_inspections ?? "—");

    // Populate upcoming inspections table
    const tbody = document.getElementById("upcomingTbody");
    if (tbody) {
      const rows = d.upcoming || [];
      if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-2">No upcoming inspections.</td></tr>';
      } else {
        tbody.innerHTML = rows.map(r => {
          const date = r.scheduled_date ? new Date(r.scheduled_date).toLocaleDateString("en-US",{year:"numeric",month:"long",day:"numeric"}) : "—";
          const inspectors = [r.inspector1_name, r.inspector2_name].filter(Boolean).join(", ") || "—";
          const badge = r.status === "scheduled" ? "bg-warning" : "bg-info";
          return `<tr>
            <td>Fire Safety Inspection</td>
            <td>${date}</td>
            <td>${inspectors}</td>
            <td><span class="badge ${badge} badge-status">${r.status}</span></td>
          </tr>`;
        }).join("");
      }
    }

    // Populate notifications dynamically
    loadNotifications(d.notifications || []);
  } catch (e) { console.error("Dashboard load error:", e); }
}

// ── Notifications from DB ────────────────────────────────────────────────
function loadNotifications(notifications) {
  const notifList = document.getElementById("notificationList");
  const notifCount = document.getElementById("notificationCount");
  if (!notifList) return;

  if (!notifications.length) {
    notifList.innerHTML = '<div class="text-center py-3 text-muted"><i class="fas fa-check-circle me-2"></i>No new notifications</div>';
    if (notifCount) { notifCount.style.display = 'none'; }
    return;
  }

  // Show count
  if (notifCount) {
    notifCount.textContent = notifications.length;
    notifCount.style.display = 'flex';
  }

  notifList.innerHTML = notifications.map((n, idx) => {
    const iconMap = {
      'Payment Confirmed': { icon: 'fas fa-check-circle text-success', msg: `Payment confirmed for ${n.establishment_name}.` },
      'Payment Approved':  { icon: 'fas fa-money-bill text-success', msg: `FSIC payment approved for ${n.establishment_name}.` },
      'Inspection Scheduled': { icon: 'fas fa-calendar-check text-info', msg: `Inspection scheduled for ${n.establishment_name}.` },
      'Inspection Completed': { icon: 'fas fa-clipboard-check text-primary', msg: `Inspection completed for ${n.establishment_name}.` },
      'Inspection Pending':   { icon: 'fas fa-exclamation-circle text-warning', msg: `Inspection pending for ${n.establishment_name}.` }
    };
    const info = iconMap[n.title] || { icon: 'fas fa-info-circle text-secondary', msg: `${n.title} – ${n.establishment_name}` };
    const timeAgo = getRelativeTime(n.event_date);
    return `<div class="notification-item unread" data-id="${idx}">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <h6 class="mb-1"><i class="${info.icon}"></i> ${n.title}</h6>
          <p class="mb-1 small">${info.msg}</p>
          <small class="text-muted">${timeAgo}</small>
        </div>
      </div>
    </div>`;
  }).join("");

  // Click-to-read handlers
  document.querySelectorAll('#notificationList .notification-item').forEach(item => {
    item.addEventListener('click', function() {
      if (this.classList.contains('unread')) {
        this.classList.remove('unread');
        const current = parseInt(notifCount.textContent) || 0;
        const newCount = Math.max(0, current - 1);
        notifCount.textContent = newCount;
        if (newCount === 0) notifCount.style.display = 'none';
      }
    });
  });
}

function getRelativeTime(dateStr) {
  if (!dateStr) return '';
  const now = new Date();
  const then = new Date(dateStr);
  const diffMs = now - then;
  const mins = Math.floor(diffMs / 60000);
  const hrs  = Math.floor(diffMs / 3600000);
  const days = Math.floor(diffMs / 86400000);
  if (mins < 1) return 'Just now';
  if (mins < 60) return `${mins} minute${mins > 1 ? 's' : ''} ago`;
  if (hrs < 24) return `${hrs} hour${hrs > 1 ? 's' : ''} ago`;
  if (days < 30) return `${days} day${days > 1 ? 's' : ''} ago`;
  return then.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

document.addEventListener("DOMContentLoaded", function () {
  loadOwnerDashboard();

  const mobileToggle = document.getElementById("mobileToggle");
  const sidebar = document.getElementById("sidebar");
  const mainContent = document.getElementById("mainContent");
  const mobileOverlay = document.getElementById("mobileOverlay");
  const sidebarLinks = document.querySelectorAll(".sidebar-nav a");

  // Mobile toggle functionality
  if (mobileToggle) {
    mobileToggle.addEventListener("click", function () {
      if (sidebar) sidebar.classList.toggle("show");
      if (mobileOverlay) mobileOverlay.classList.toggle("show");
    });
  }

  // Close sidebar when overlay is clicked
  if (mobileOverlay) {
    mobileOverlay.addEventListener("click", function () {
      if (sidebar) sidebar.classList.remove("show");
      mobileOverlay.classList.remove("show");
    });
  }

  // Sidebar navigation
  sidebarLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      // Remove active class from all links
      sidebarLinks.forEach((l) => l.classList.remove("active"));

      // Add active class to clicked link
      this.classList.add("active");

      // Close mobile sidebar
      if (window.innerWidth <= 768) {
        if (sidebar) sidebar.classList.remove("show");
        if (mobileOverlay) mobileOverlay.classList.remove("show");
      }
    });
  });

  // Responsive handling
  window.addEventListener("resize", function () {
    if (window.innerWidth > 768) {
      if (sidebar) sidebar.classList.remove("show");
      if (mobileOverlay) mobileOverlay.classList.remove("show");
    }
  });

  // Animate stat cards on scroll
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  };

  const observer = new IntersectionObserver(function (entries) {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = "1";
        entry.target.style.transform = "translateY(0)";
      }
    });
  }, observerOptions);

  // Initially hide stat cards for animation
  document.querySelectorAll(".stat-card").forEach((card) => {
    card.style.opacity = "0";
    card.style.transform = "translateY(20px)";
    card.style.transition = "opacity 0.6s ease, transform 0.6s ease";
    observer.observe(card);
  });

  // Add click handlers for interactive elements
  document.querySelectorAll(".stat-card").forEach((card) => {
    card.addEventListener("click", function () {
      const titleEl = this.querySelector(".stat-title");
      if (titleEl) console.log("Clicked on:", titleEl.textContent);
    });
  });

  // Logout functionality
  const logoutBtn = document.querySelector(".logout-btn");
  if (logoutBtn) {
    logoutBtn.addEventListener("click", function () {
      if (confirm("Are you sure you want to logout?")) {
        window.location.href = "../../utility/logout.php";
      }
    });
  }

  // Auto-refresh data every 5 minutes (300000 ms)
  setInterval(function () {
    console.log("Refreshing dashboard data...");
    // Add data refresh logic here
  }, 300000);
});
