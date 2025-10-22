// Enhanced form handling with animations
document.getElementById("loginForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const button = this.querySelector(".login-btn");
  const username = document.getElementById("username").value;
  const password = document.getElementById("password").value;

  if (username && password) {
    button.classList.add("loading");
    button.innerHTML =
      '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Signing In...';

    // Simulate login process (replace with actual login logic)
    setTimeout(async() => {
      const res = await fetch("../utility/login.php",{
        method:"POST",
        headers:{'Content-Type': 'application/json'},
        body:JSON.stringify({
          "username":username,
          "password":password
        })
      })
      const j = await res.json()
      console.log(j)
      button.classList.remove("loading");
      if (j.success && j.role == "admin") {
        button.innerHTML = '<i class="fas fa-check me-2"></i>Success!';
        button.style.background =
          "linear-gradient(135deg, #28a745 0%, #1e7e34 100%)";
        setTimeout(() => {
          window.location.href = "./admin/dashboard.php";
        }, 1000);
      } else if (j.success && j.role == "owner") {
        button.innerHTML = '<i class="fas fa-check me-2"></i>Success!';
        button.style.background =
          "linear-gradient(135deg, #28a745 0%, #1e7e34 100%)";
          setTimeout(() => {
            window.location.href = "./owner/dashboard.php";
          }, 1000)
        
      } else if (j.success && j.role.toLowerCase() == "inspector") {
        button.innerHTML = '<i class="fas fa-check me-2"></i>Success!';
        button.style.background =
          "linear-gradient(135deg, #28a745 0%, #1e7e34 100%)";
          setTimeout(() => {
            window.location.href = "./inspector/dashboard.php";
          }, 1000)
      } else {
        button.innerHTML =
          '<i class="fa-solid fa-xmark me-2"></i>Login Failed!';
        button.style.background = "linear-gradient(135deg, red 0%, red 100%)";
        alert("Invalid username or password!");
        setTimeout(() => {
          button.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Login';
          button.style.background =
            "linear-gradient(135deg, var(--bfp-red) 0%, var(--bfp-dark-red) 100%)";
        }, 2000);
      }
    }, 1500);
  }
});
