// Mobile sidebar toggle
function toggleSidebar() {
  const sidebar = document.getElementById("sidebar");
  sidebar.classList.toggle("show");
}

// Close sidebar on outside click (mobile)
document.addEventListener("click", function (event) {
  const sidebar = document.getElementById("sidebar");
  const toggleBtn = document.querySelector(".mobile-menu-toggle");

  if (
    sidebar &&
    toggleBtn &&
    window.innerWidth <= 768 &&
    !sidebar.contains(event.target) &&
    !toggleBtn.contains(event.target)
  ) {
    sidebar.classList.remove("show");
  }
});
