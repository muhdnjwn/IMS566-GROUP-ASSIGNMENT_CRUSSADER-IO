<?php
// Add this at the VERY TOP
session_start();
include 'db_connect.php';

// Only logged-in users
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch categories
$categories = $conn->query("SELECT * FROM Category");
// ... rest of your code ...


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $user_id = $_SESSION['user_id'];

    // Insert complaint into database
    $stmt = $conn->prepare("INSERT INTO Complaint (Complaint_Title, Complaint_Description, User_ID, Category_ID, Status_ID) 
                           VALUES (?, ?, ?, ?, 1)");
    $stmt->bind_param("ssii", $title, $description, $user_id, $category_id);
    $stmt->execute();
    $new_complaint_id = $conn->insert_id;
    $stmt->close();

    // Store complaint ID in session for success message
    $_SESSION['new_complaint_id'] = $new_complaint_id;
    
    // Update user info if provided
    if(isset($_POST['customer_name']) && isset($_POST['customer_email']) && isset($_POST['customer_phone'])) {
        $name = $_POST['customer_name'];
        $email = $_POST['customer_email'];
        $phone = $_POST['customer_phone'];
        
       $update_stmt = $conn->prepare("UPDATE User SET Name = ?, Email = ?, Phone_Number = ? WHERE User_ID = ?");
        $update_stmt->bind_param("sssi", $name, $email, $phone, $user_id);
        $update_stmt->execute();
        $update_stmt->close();
        
        // Update session info
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_phone'] = $phone;
    }

    header("Location: customer-dashboard.php");
    exit();
}

// Fetch current user info
$user_id = $_SESSION['user_id'];
$user_query = $conn->query("SELECT Name, Email, Phone_Number as Phone FROM User WHERE User_ID = $user_id");
$user_data = $user_query->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Complaint - Crussader IO</title>
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

        .nav-section {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .digital-clock {
            background: rgba(255, 255, 255, 0.1);
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1.1rem;
            letter-spacing: 1px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .clock-icon {
            margin-right: 8px;
            color: var(--gold);
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 5px;
            background: rgba(255, 255, 255, 0.1);
            padding: 5px;
            border-radius: 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 12px 24px;
            border-radius: 20px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
        }

        nav a i {
            font-size: 1rem;
        }

        nav a:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        nav a.active {
            background: linear-gradient(135deg, var(--accent), #ff6b81);
            box-shadow: 0 4px 20px rgba(233, 69, 96, 0.4);
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

        .btn-primary {
            background: linear-gradient(135deg, var(--accent), #ff6b81);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #ff6b81, var(--accent));
            box-shadow: 0 8px 25px rgba(233, 69, 96, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success), #2f855a);
            color: white;
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
            max-width: 700px;
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
            
            .nav-section {
                flex-direction: column;
                width: 100%;
                gap: 15px;
            }
            
            nav ul {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
            }
            
            .digital-clock {
                order: -1;
                width: 100%;
                text-align: center;
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
            
            <div class="nav-section">
                <div class="digital-clock">
                    <i class="fas fa-clock clock-icon"></i>
                    <span id="current-time">00:00:00</span>
                </div>
                
               <<nav>
    <ul>
        <li><a href="complaint-form.php" class="active"><i class="fas fa-exclamation-circle"></i> File Complaint</a></li>
        <li><a href="customer-dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="track-complaint.php"><i class="fas fa-search"></i> Track</a></li>
    </ul>
</nav>
            </div>
            
            <div class="auth-section">
                <button class="btn btn-success" id="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div class="container">
            <div class="form-container">
                <h2><i class="fas fa-exclamation-circle"></i> File a New Complaint</h2>
                <form id="complaint-form" method="POST" action="">
                    <div class="form-group">
                        <label for="customer-name" class="form-label"><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" id="customer-name" name="customer_name" class="form-control" value="<?php echo htmlspecialchars($user_data['Name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="customer-email" class="form-label"><i class="fas fa-envelope"></i> Email Address</label>
                        <input type="email" id="customer-email" name="customer_email" class="form-control" value="<?php echo htmlspecialchars($user_data['Email'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="customer-phone" class="form-label"><i class="fas fa-phone"></i> Phone Number</label>
                        <input type="tel" id="customer-phone" name="customer_phone" class="form-control" value="<?php echo htmlspecialchars($user_data['Phone'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="complaint-category" class="form-label"><i class="fas fa-tag"></i> Complaint Category</label>
                        <select id="complaint-category" name="category_id" class="form-control" required>
                            <option value="">Select a category</option>
                            <?php 
                            $categories->data_seek(0); // Reset pointer
                            while($cat = $categories->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $cat['Category_ID']; ?>"><?php echo htmlspecialchars($cat['Category_Name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="complaint-title" class="form-label"><i class="fas fa-heading"></i> Complaint Title</label>
                        <input type="text" id="complaint-title" name="title" class="form-control" placeholder="Brief description of the issue" required>
                    </div>
                    <div class="form-group">
                        <label for="complaint-description" class="form-label"><i class="fas fa-file-alt"></i> Detailed Description</label>
                        <textarea id="complaint-description" name="description" class="form-control" rows="5" placeholder="Please provide as much detail as possible about the issue..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-paper-plane"></i> Submit Complaint
                    </button>
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
                        <li><a href="complaint.php"><i class="fas fa-chevron-right"></i> File Complaint</a></li>
                        <li><a href="track-complaint.php"><i class="fas fa-chevron-right"></i> Track Complaint</a></li>
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
        // Digital Clock Function
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('current-time').textContent = timeString;
        }
        
        setInterval(updateClock, 1000);
        updateClock();

        // Logout functionality
        document.getElementById('logout-btn').addEventListener('click', function() {
            if(confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        });

        // Form validation
        document.getElementById('complaint-form').addEventListener('submit', function(e) {
            const phone = document.getElementById('customer-phone').value;
            const phonePattern = /^[\+]?[0-9\s\-\(\)]+$/;
            
            if (!phonePattern.test(phone)) {
                e.preventDefault();
                alert('Please enter a valid phone number.');
                document.getElementById('customer-phone').focus();
                return false;
            }
            
            const title = document.getElementById('complaint-title').value.trim();
            const description = document.getElementById('complaint-description').value.trim();
            
            if (title.length < 5) {
                e.preventDefault();
                alert('Please enter a more descriptive title (minimum 5 characters).');
                document.getElementById('complaint-title').focus();
                return false;
            }
            
            if (description.length < 20) {
                e.preventDefault();
                alert('Please provide a more detailed description (minimum 20 characters).');
                document.getElementById('complaint-description').focus();
                return false;
            }
            
            // Show submitting message
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            submitBtn.disabled = true;
            
            // Re-enable button after 3 seconds if submission fails
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 3000);
            
            return true;
        });

        // Show success message if redirected from successful submission
        <?php if(isset($_SESSION['new_complaint_id'])): ?>
        window.onload = function() {
            alert(`Complaint submitted successfully! Your Complaint ID is: <?php echo $_SESSION['new_complaint_id']; ?>\n\nPlease save this ID for tracking.`);
            <?php unset($_SESSION['new_complaint_id']); ?>
        };
        <?php endif; ?>
    </script>
</body>
</html>