document.addEventListener("DOMContentLoaded", function () {
  const mobileToggle = document.getElementById("mobileToggle");
  const sidebar = document.getElementById("sidebar");
  const mainContent = document.getElementById("mainContent");
  const mobileOverlay = document.getElementById("mobileOverlay");
  const sidebarLinks = document.querySelectorAll(".sidebar-nav a");

  // Mobile toggle functionality
  mobileToggle.addEventListener("click", function () {
    sidebar.classList.toggle("show");
    mobileOverlay.classList.toggle("show");
  });

  // Close sidebar when overlay is clicked
  mobileOverlay.addEventListener("click", function () {
    sidebar.classList.remove("show");
    mobileOverlay.classList.remove("show");
  });

  // Sidebar navigation
  sidebarLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();

      // Remove active class from all links
      sidebarLinks.forEach((l) => l.classList.remove("active"));

      // Add active class to clicked link
      this.classList.add("active");

      // Close mobile sidebar
      if (window.innerWidth <= 768) {
        sidebar.classList.remove("show");
        mobileOverlay.classList.remove("show");
      }

      // Here you can add navigation logic
      console.log("Navigating to:", this.textContent.trim());
    });
  });

  // Responsive handling
  window.addEventListener("resize", function () {
    if (window.innerWidth > 768) {
      sidebar.classList.remove("show");
      mobileOverlay.classList.remove("show");
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
      const title = this.querySelector(".stat-title").textContent;
      console.log("Clicked on:", title);
      // Add your navigation logic here
    });
  });

  // Logout functionality
  document.querySelector(".logout-btn").addEventListener("click", function () {
    if (confirm("Are you sure you want to logout?")) {
      console.log("Logging out...");
      // Add logout logic here
    }
  });

  // Auto-refresh data every 5 minutes (300000 ms)
  setInterval(function () {
    console.log("Refreshing dashboard data...");
    // Add data refresh logic here
  }, 300000);
});
