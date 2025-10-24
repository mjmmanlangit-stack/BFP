// Enhanced form handling with animations
document.getElementById("loginForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const button = this.querySelector(".login-btn");
  const username = document.getElementById("username").value.trim();
  const password = document.getElementById("password").value;

  if (username && password) {
    button.classList.add("loading");
    button.disabled = true;
    button.innerHTML =
      '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Signing In...';

    // Login process
    setTimeout(async() => {
      try {
        const res = await fetch("../utility/login.php",{
          method:"POST",
          headers:{'Content-Type': 'application/json'},
          body:JSON.stringify({
            "username":username,
            "password":password
          })
        });
        
        const j = await res.json();
        console.log('Login Response:', j);
        console.log('User Role:', j.role);
        
        button.classList.remove("loading");
        
        if (j.success) {
          // Role-based redirect mapping (supports multiple variations)
          const roleRedirects = {
            'admin': './admin/dashboard.php',
            'Admin': './admin/dashboard.php',
            'owner': './owner/dashboard.php',
            'Owner': './owner/dashboard.php',
            'inspector': './inspector/dashboard.php',
            'Inspector': './inspector/dashboard.php',
            'chief': './Chief/dashboard.html',
            'Chief': './Chief/dashboard.html',
            'fire marshal': './FireMarshal/inspections.html',
            'Fire Marshal': './FireMarshal/inspections.html',
            'Fire Marsh': './FireMarshal/inspections.html',  // Handles database typo
            'fire marsh': './FireMarshal/inspections.html',
            'FireMarshal': './FireMarshal/inspections.html',
            'firemarshal': './FireMarshal/inspections.html',
            'cro': './CRO/dashboard.html',
            'CRO': './CRO/dashboard.html',
            'Cro': './CRO/dashboard.html',
            'accessor': './Accessor/dashboard.html',
            'Accessor': './Accessor/dashboard.html'
          };
          
          const redirectUrl = roleRedirects[j.role];
          
          console.log('Redirect URL for role "' + j.role + '":', redirectUrl);
          
          if (redirectUrl) {
            button.innerHTML = '<i class="fas fa-check me-2"></i>Success!';
            button.style.background =
              "linear-gradient(135deg, #28a745 0%, #1e7e34 100%)";
            
            setTimeout(() => {
              window.location.href = redirectUrl;
            }, 1000);
          } else {
            // Unknown role
            button.innerHTML =
              '<i class="fa-solid fa-xmark me-2"></i>Invalid Role!';
            button.style.background = "linear-gradient(135deg, #ff9800 0%, #f57c00 100%)";
            showError("Your account role is not configured. Contact administrator.");
            resetButton(button);
          }
        } else {
          // Login failed
          button.innerHTML =
            '<i class="fa-solid fa-xmark me-2"></i>Login Failed!';
          button.style.background = "linear-gradient(135deg, red 0%, red 100%)";
          showError(j.error || "Invalid username or password!");
          resetButton(button);
        }
      } catch (error) {
        console.error('Login error:', error);
        button.classList.remove("loading");
        button.innerHTML =
          '<i class="fa-solid fa-xmark me-2"></i>Error!';
        button.style.background = "linear-gradient(135deg, red 0%, red 100%)";
        showError("Connection error. Please check your internet connection.");
        resetButton(button);
      }
    }, 1500);
  }
});

// Helper function to show error messages
function showError(message) {
  const alertDiv = document.createElement('div');
  alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed';
  alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
  alertDiv.innerHTML = `
    <strong>Error!</strong> ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;
  
  document.body.appendChild(alertDiv);
  
  // Auto remove after 5 seconds
  setTimeout(() => {
    alertDiv.remove();
  }, 5000);
}

// Helper function to reset button state
function resetButton(button) {
  button.disabled = false;
  setTimeout(() => {
    button.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Login';
    button.style.background =
      "linear-gradient(135deg, var(--bfp-red) 0%, var(--bfp-dark-red) 100%)";
  }, 2000);
}
