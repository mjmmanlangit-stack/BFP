<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BFP Site Profiler - Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="../assets/styles/index.css">
</head>
<body>
    <div class="login-container">
        <div class="card login-card">
            <div class="login-header">
                <div class="bfp-logo">
                    <img src="../assets/images/BFP-OFFICIAL-LOGO.png" alt="">
                </div>
                <h1 class="login-title">BFP Site Profiler</h1>
                <p class="login-subtitle">Bureau of Fire Protection</p>
            </div>
            
            <div class="login-body">
                <form id="loginForm">
                    <div class="form-floating">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" class="form-control" id="username" placeholder="Email" required>
                        <label for="username">Email</label>
                    </div>
                    
                    <div class="form-floating">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-control" id="password" placeholder="Password" required>
                        <label for="password">Password</label>
                    </div>
                    
                    <button type="submit" class="btn login-btn">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Login
                    </button>
                </form>
            </div>
            
            <div class="login-footer">
                <p class="mb-1">New establishment owner? <a href="./owner/register.php" style="font-weight:bold;">Register here</a></p>
                <p class="mb-0">&copy; 2025 Bureau of Fire Protection. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/scripts/index.js"></script>
</body>
</html>