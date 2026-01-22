<?php
include 'db_connect.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = "Customer"; // Automatically set as Customer

    // Validate passwords match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long!";
    } else {
        // Check if email exists
        $check = $conn->query("SELECT * FROM User WHERE Email='$email'");
        if ($check->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user into database
            $sql = "INSERT INTO User (Name, Email, Phone_Number, User_Type, Password)
                    VALUES ('$name','$email','$phone','$user_type','$hashed_password')";
            
            if ($conn->query($sql)) {
                header("Location: login.php");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Crussader IO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Shared CSS from original file */
        :root {
            --primary: #1a1a2e;
            --secondary: #16213e;
            --accent: #e94560;
            --gold: #ffd700;
            --success: #38a169;
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

        /* Error Message */
        .alert {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
            animation: fadeIn 0.5s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .alert-error {
            background-color: #fed7d7;
            color: #c53030;
            border: 2px solid #fc8181;
        }

        .alert-error i {
            font-size: 1.2rem;
        }

        /* Password Strength */
        .password-strength {
            margin-top: 8px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .strength-weak {
            color: var(--danger);
        }

        .strength-medium {
            color: var(--warning);
        }

        .strength-strong {
            color: var(--success);
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
                <a href="index.php" class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-crown"></i>
                    </div>
                    <div class="logo-text">
                        <h1>Crussader <span>IO</span></h1>
                        <p>Premium Streetwear & Clothing</p>
                    </div>
                </a>
            </div>
            
            <div class="auth-section">
                <button class="btn btn-outline" onclick="window.location.href='login.php'">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div class="container">
            <div class="form-container">
                <h2><i class="fas fa-user-plus"></i> Register as Customer</h2>
                
                <?php if(!empty($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="register-form">
                    <div class="form-group">
                        <label for="name" class="form-label"><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" required 
                               placeholder="Enter your full name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label"><i class="fas fa-envelope"></i> Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required 
                               placeholder="Enter your email address" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="form-label"><i class="fas fa-phone"></i> Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" 
                               placeholder="Enter your phone number (e.g., 012-3456789)" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label"><i class="fas fa-lock"></i> Password</label>
                        <input type="password" id="password" name="password" class="form-control" required 
                               placeholder="Enter your password (min. 6 characters)">
                        <div id="password-strength" class="password-strength"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label"><i class="fas fa-lock"></i> Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required 
                               placeholder="Confirm your password">
                        <div id="password-match" class="password-strength"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-user-plus"></i> Register
                    </button>
                    
                    <div class="form-footer">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </form>
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
        // Client-side validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('register-form');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const passwordStrength = document.getElementById('password-strength');
            const passwordMatch = document.getElementById('password-match');

            // Password strength checker
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 'Weak';
                let colorClass = 'strength-weak';
                
                if (password.length >= 8) {
                    strength = 'Medium';
                    colorClass = 'strength-medium';
                    
                    if (password.length >= 12 && /[A-Z]/.test(password) && /[0-9]/.test(password) && /[^A-Za-z0-9]/.test(password)) {
                        strength = 'Strong';
                        colorClass = 'strength-strong';
                    }
                } else if (password.length === 0) {
                    strength = '';
                    colorClass = '';
                }
                
                if (strength) {
                    passwordStrength.innerHTML = `<i class="fas fa-shield-alt"></i> Password Strength: <span class="${colorClass}">${strength}</span>`;
                } else {
                    passwordStrength.innerHTML = '';
                }
            });

            // Password match checker
            confirmPasswordInput.addEventListener('input', function() {
                const password = passwordInput.value;
                const confirmPassword = this.value;
                
                if (confirmPassword.length === 0) {
                    passwordMatch.innerHTML = '';
                    return;
                }
                
                if (password === confirmPassword) {
                    passwordMatch.innerHTML = '<i class="fas fa-check-circle" style="color: var(--success);"></i> Passwords match';
                } else {
                    passwordMatch.innerHTML = '<i class="fas fa-times-circle" style="color: var(--danger);"></i> Passwords do not match';
                }
            });

         // Phone number formatting with only 1 dash
const phoneInput = document.getElementById('phone');
phoneInput.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, ''); // Remove non-digit characters
    
    if (value.length > 3) {
        value = value.slice(0, 3) + '-' + value.slice(3); // Insert a dash after the first 3 digits
    }

    e.target.value = value;
});


            // Form validation before submission
            form.addEventListener('submit', function(e) {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                const phone = phoneInput.value;
                
                // Check password length
                if (password.length < 6) {
                    e.preventDefault();
                    alert('Password must be at least 6 characters long!');
                    passwordInput.focus();
                    return false;
                }
                
                // Check password match
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                    confirmPasswordInput.focus();
                    return false;
                }
                
                // Validate phone number format (optional)
                if (phone && !/^\d{2,3}-\d{6,8}$/.test(phone)) {
                    e.preventDefault();
                    alert('Please enter a valid phone number (e.g., 012-3456789)');
                    phoneInput.focus();
                    return false;
                }
                
                return true;
            });

            // Auto-focus first input field
            const firstInput = document.querySelector('input[type="text"], input[type="email"]');
            if (firstInput) {
                firstInput.focus();
            }
        });

        // Show/hide password toggle (optional enhancement)
        function togglePasswordVisibility(inputId) {
            const input = document.getElementById(inputId);
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
        }
    </script>
</body>
</html>