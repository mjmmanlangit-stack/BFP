<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BFP Site Profiler</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/styles/index.css">
    <style>
        .register-card {
            max-width: 520px;
            width: 100%;
        }
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            z-index: 10;
        }
        .form-floating .input-icon { top: 50%; }
        .form-floating .form-control { padding-left: 40px; }
        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 20px;
        }
        .step-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255,255,255,0.4);
        }
        .step-dot.active { background: white; }
        #successPanel { display: none; }
    </style>
</head>
<body>
<div class="login-container">
    <div class="card login-card register-card">
        <div class="login-header">
            <div class="bfp-logo">
                <img src="../../assets/images/BFP-OFFICIAL-LOGO.png" alt="">
            </div>
            <h1 class="login-title">Owner Registration</h1>
            <p class="login-subtitle">Bureau of Fire Protection</p>
        </div>

        <div class="login-body">
            <!-- Registration Form -->
            <div id="registerPanel">
                <div id="alertBox" class="alert d-none" role="alert"></div>

                <form id="registerForm">
                    <div class="form-floating mb-3 position-relative">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" class="form-control" id="fullname" placeholder="Full Name" required>
                        <label for="fullname">Full Name</label>
                    </div>

                    <div class="form-floating mb-3 position-relative">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" class="form-control" id="email" placeholder="Email Address" required>
                        <label for="email">Email Address</label>
                    </div>

                    <div class="form-floating mb-3 position-relative">
                        <i class="fas fa-phone input-icon"></i>
                        <input type="text" class="form-control" id="contact" placeholder="Contact Number">
                        <label for="contact">Contact Number</label>
                    </div>

                    <div class="form-floating mb-3 position-relative">
                        <i class="fas fa-map-marker-alt input-icon"></i>
                        <input type="text" class="form-control" id="address" placeholder="Address">
                        <label for="address">Address</label>
                    </div>

                    <div class="form-floating mb-3 position-relative">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-control" id="password" placeholder="Password" required minlength="8">
                        <label for="password">Password (min. 8 characters)</label>
                    </div>

                    <div class="form-floating mb-3 position-relative">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm Password" required>
                        <label for="confirmPassword">Confirm Password</label>
                    </div>

                    <button type="submit" class="btn login-btn w-100 mb-3" id="registerBtn">
                        <i class="fas fa-user-plus me-2"></i> Create Account
                    </button>
                </form>
            </div>

            <!-- Success Panel -->
            <div id="successPanel" class="text-center py-3">
                <div style="font-size: 4rem; color: #28a745; margin-bottom: 15px;">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <h5 class="mb-2">Check Your Email</h5>
                <p class="text-muted">We sent a verification link to <strong id="sentToEmail"></strong>. Click the link to activate your account.</p>
                <p class="text-muted small">The link expires in 24 hours. Check your spam folder if you don't see it.</p>
            </div>
        </div>

        <div class="login-footer">
            <p class="mb-0">Already have an account? <a href="../index.php" style="color: white; font-weight: bold;">Sign In</a></p>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
    const form = document.getElementById('registerForm');
    const btn  = document.getElementById('registerBtn');
    const alertBox = document.getElementById('alertBox');

    function showAlert(msg, type = 'danger') {
        alertBox.className = `alert alert-${type}`;
        alertBox.textContent = msg;
        alertBox.classList.remove('d-none');
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const fullname = document.getElementById('fullname').value.trim();
        const email    = document.getElementById('email').value.trim();
        const contact  = document.getElementById('contact').value.trim();
        const address  = document.getElementById('address').value.trim();
        const password = document.getElementById('password').value;
        const confirm  = document.getElementById('confirmPassword').value;

        if (password !== confirm) {
            showAlert('Passwords do not match.'); return;
        }
        if (password.length < 8) {
            showAlert('Password must be at least 8 characters.'); return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating Account...';
        alertBox.classList.add('d-none');

        try {
            const res = await fetch('../../utility/ownerRegister.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ fullname, email, contact, address, password })
            });
            const j = await res.json();

            if (j.success) {
                document.getElementById('sentToEmail').textContent = email;
                document.getElementById('registerPanel').style.display = 'none';
                document.getElementById('successPanel').style.display = 'block';
            } else {
                showAlert(j.error || 'Registration failed. Please try again.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-user-plus me-2"></i> Create Account';
            }
        } catch (err) {
            showAlert('Network error. Please try again.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-user-plus me-2"></i> Create Account';
        }
    });
</script>
</body>
</html>
