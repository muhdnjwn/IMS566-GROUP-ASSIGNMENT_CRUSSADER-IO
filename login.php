<?php
// Add session_start at the VERY TOP
session_start();
include 'db_connect.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if it's a CUSTOMER login (has email field)
    if (isset($_POST['email']) && !empty($_POST['email'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Check User table for customer
        $stmt = $conn->prepare("SELECT * FROM User WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user && password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['User_ID'];
            $_SESSION['user_name'] = $user['Name'];
            $_SESSION['role'] = 'user';
            header("Location: customer-dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password!";
        }
        $stmt->close();
    } 
    // Check if it's an ADMIN login (has username field)
    elseif (isset($_POST['username']) && !empty($_POST['username'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Check Admin table
        $stmt = $conn->prepare("SELECT * FROM Admin WHERE Username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        
        if ($admin && password_verify($password, $admin['Password'])) {
            $_SESSION['admin_id'] = $admin['Admin_ID'];
            $_SESSION['admin_name'] = $admin['Name'];
            $_SESSION['role'] = 'admin';
            header("Location: admin-dashboard.php");
            exit();
        } else {
            $error = "Invalid admin credentials!";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Crussader IO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Shared CSS from original file */
        :root {
            --primary: #1a1a2e;
            --secondary: #16213e;
            --accent: #e94560;
            --gold: #ffd700;
            --success: #38a169;
            --warning: #d69e2e;
            --danger: #e53e3e;
            --light: #f8f9fa;
            --dark: #0f3460;
            --gray: #a0aec0;
            --card-bg: #ffffff;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --gradient: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background: var(--gradient);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 3px solid var(--accent);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .logo:hover {
            transform: translateY(-2px);
        }

        .logo-icon {
            background: linear-gradient(135deg, var(--accent), #ff6b81);
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            box-shadow: 0 4px 15px rgba(233, 69, 96, 0.3);
        }

        .logo-text h1 {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(to right, #fff, var(--gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .logo-text span {
            color: var(--accent);
            font-weight: 900;
        }

        .logo-text p {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-top: 2px;
            color: #cbd5e0;
        }

        .auth-section {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 12px 28px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
            letter-spacing: 0.3px;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .btn:active {
            transform: translateY(-1px);
        }

        .btn-outline {
            background: transparent;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent), #ff6b81);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #ff6b81, var(--accent));
            box-shadow: 0 8px 25px rgba(233, 69, 96, 0.4);
        }

        /* Main Content */
        main {
            padding: 40px 0;
            min-height: calc(100vh - 200px);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-container {
            max-width: 500px;
            margin: 0 auto;
            background: var(--card-bg);
            padding: 40px;
            border-radius: 20px;
            box-shadow: var(--shadow);
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-container h2 {
            color: var(--primary);
            margin-bottom: 30px;
            font-size: 2rem;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-control {
            width: 100%;
            padding: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            background: white;
            box-shadow: 0 0 0 3px rgba(233, 69, 96, 0.2);
        }

        .btn-block {
            width: 100%;
            padding: 16px;
            font-size: 1.1rem;
            border-radius: 12px;
            margin-top: 10px;
        }

        /* Tabs */
        .tabs {
            display: flex;
            background: #f7fafc;
            border-radius: 12px;
            padding: 5px;
            margin-bottom: 25px;
            border: 1px solid #e2e8f0;
        }

        .tab {
            flex: 1;
            padding: 15px;
            text-align: center;
            background: none;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            color: var(--gray);
            transition: all 0.3s ease;
        }

        .tab.active {
            background: white;
            color: var(--primary);
            box-shadow: var(--shadow);
        }

        .login-form {
            display: block;
        }

        .login-form.hidden {
            display: none;
        }

        .form-footer {
            text-align: center;
            margin-top: 20px;
            color: var(--gray);
            font-size: 0.9rem;
        }

        .form-footer a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        /* Footer */
        footer {
            background: var(--gradient);
            color: white;
            padding: 60px 0 20px;
            margin-top: 60px;
            border-top: 3px solid var(--accent);
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 30px;
        }

        .footer-logo h3 {
            font-size: 1.8rem;
            margin-bottom: 10px;
            background: linear-gradient(to right, #fff, var(--gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .footer-logo span {
            color: var(--accent);
        }

        .footer-logo p {
            color: #cbd5e0;
            line-height: 1.6;
        }

        .footer-links h4 {
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: var(--gold);
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer-links a {
            color: #cbd5e0;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: white;
        }

        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-icon:hover {
            background: var(--accent);
            transform: translateY(-3px);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #cbd5e0;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 20px;
            }
            
            .auth-section {
                width: 100%;
                justify-content: center;
            }
            
            .form-container {
                padding: 25px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-content">
            <div class="logo-section">
                <div class="logo" onclick="window.location.href='index.php'">
                    <div class="logo-icon">
                        <i class="fas fa-crown"></i>
                    </div>
                    <div class="logo-text">
                        <h1>Crussader <span>IO</span></h1>
                        <p>Premium Streetwear & Clothing</p>
                    </div>
                </div>
            </div>
            
            <div class="auth-section">
                <button class="btn btn-primary" onclick="window.location.href='register.php'">
                    <i class="fas fa-user-plus"></i> Register
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div class="container">
            <div class="form-container">
                <h2><i class="fas fa-sign-in-alt"></i> Login</h2>
                
                <div class="tabs">
                    <button class="tab active" id="customer-tab">Customer Login</button>
                    <button class="tab" id="admin-tab">Admin Login</button>
                </div>
                
                               <div id="customer-login-form" class="login-form">
                    <form method="POST" action="login.php">
                        <div class="form-group">
                            <label for="customer-login-email" class="form-label"><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" id="customer-login-email" name="email" class="form-control" placeholder="Enter your email" required>
                        </div>
                        <div class="form-group">
                            <label for="customer-login-password" class="form-label"><i class="fas fa-lock"></i> Password</label>
                            <input type="password" id="customer-login-password" name="password" class="form-control" placeholder="Enter your password" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block" id="customer-login-btn">
                            <i class="fas fa-sign-in-alt"></i> Login as Customer
                        </button>
                        <?php if(isset($error) && !isset($_POST['username'])): ?>
                        <div style="color: red; margin-top: 10px; text-align: center;">
                            <?php echo $error; ?>
                        </div>
                        <?php endif; ?>
                        <div class="form-footer">
                            <p>Don't have an account? <a href="register.php">Register here</a></p>
                        </div>
                    </form>
                </div>
                
                               <div id="admin-login-form" class="login-form hidden">
                    <form method="POST" action="login.php">
                        <div class="form-group">
                            <label for="admin-login-username" class="form-label"><i class="fas fa-user"></i> Username</label>
                            <input type="text" id="admin-login-username" name="username" class="form-control" placeholder="Enter admin username" required>
                        </div>
                        <div class="form-group">
                            <label for="admin-login-password" class="form-label"><i class="fas fa-lock"></i> Password</label>
                            <input type="password" id="admin-login-password" name="password" class="form-control" placeholder="Enter admin password" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block" id="admin-login-btn">
                            <i class="fas fa-user-shield"></i> Login as Admin
                        </button>
                        <?php if(isset($error) && isset($_POST['username'])): ?>
                        <div style="color: red; margin-top: 10px; text-align: center;">
                            <?php echo $error; ?>
                        </div>
                        <?php endif; ?>
                        <div class="form-footer">
                            
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <h3>Crussader <span>IO</span></h3>
                    <p>Premium streetwear & clothing store with quality apparel and exceptional customer service. Redefining urban fashion since 2020.</p>
                    <div class="social-icons">
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-tiktok"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php"><i class="fas fa-chevron-right"></i> Home</a></li>
                        <li><a href="login.php"><i class="fas fa-chevron-right"></i> Login</a></li>
                        <li><a href="register.php"><i class="fas fa-chevron-right"></i> Register</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h4>Contact Info</h4>
                    <ul>
                        <li><i class="fas fa-envelope"></i> support@crussaderio.com</li>
                        <li><i class="fas fa-phone"></i> 1-800-700600</li>
                        
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2026 Crussader IO Premium Streetwear. All rights reserved.</p>
            </div>
        </div>
    </footer>

        <script>
        // Tab switching only
        document.addEventListener('DOMContentLoaded', function() {
            // Tab switching
            document.getElementById('customer-tab').addEventListener('click', () => {
                document.getElementById('customer-tab').classList.add('active');
                document.getElementById('admin-tab').classList.remove('active');
                document.getElementById('customer-login-form').classList.remove('hidden');
                document.getElementById('admin-login-form').classList.add('hidden');
            });

            document.getElementById('admin-tab').addEventListener('click', () => {
                document.getElementById('admin-tab').classList.add('active');
                document.getElementById('customer-tab').classList.remove('active');
                document.getElementById('admin-login-form').classList.remove('hidden');
                document.getElementById('customer-login-form').classList.add('hidden');
            });
        });
        </script>
</body>
</html>
