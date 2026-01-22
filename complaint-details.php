<?php
// complaint-details.php
session_start();
include 'db_connect.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get complaint ID
$complaint_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch complaint details
$query = $conn->prepare("
    SELECT c.*, u.Name AS UserName, u.Email, u.Phone_Number,
           cat.Category_Name, s.Status_Name
    FROM Complaint c
    JOIN User u ON c.User_ID = u.User_ID
    JOIN Category cat ON c.Category_ID = cat.Category_ID
    JOIN Status s ON c.Status_ID = s.Status_ID
    WHERE c.Complaint_ID = ?
");
$query->bind_param("i", $complaint_id);
$query->execute();
$result = $query->get_result();
$complaint = $result->fetch_assoc();

if (!$complaint) {
    echo "Complaint not found!";
    exit();
}

// Get admin details for header
$admin_id = $_SESSION['admin_id'];
$admin_query = $conn->query("SELECT * FROM Admin WHERE Admin_ID = $admin_id");
$admin = $admin_query->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaint Details - Crussader IO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Shared CSS from your website */
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
        }

        .page-content {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 40px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            animation: fadeIn 0.5s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Complaint Details Specific */
        .details-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(0, 0, 0, 0.1);
        }

        .details-header h1 {
            color: var(--primary);
            font-size: 2rem;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .complaint-id-badge {
            background: var(--gradient);
            color: white;
            padding: 8px 20px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1.1rem;
            letter-spacing: 1px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .detail-card {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 15px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .detail-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(to right, var(--accent), #ff6b81);
        }

        .detail-card h3 {
            color: var(--primary);
            margin-bottom: 20px;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(0, 0, 0, 0.05);
        }

        .detail-item {
            margin-bottom: 15px;
        }

        .detail-label {
            color: var(--dark);
            font-weight: 600;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .detail-value {
            color: var(--gray);
            font-size: 0.95rem;
            line-height: 1.6;
            padding-left: 24px;
        }

        .detail-value strong {
            color: var(--dark);
            font-weight: 600;
        }

        .description-box {
            background: #f8fafc;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid var(--accent);
            margin-top: 10px;
            line-height: 1.8;
            white-space: pre-line;
        }

        .customer-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }

        .customer-info-item {
            background: #f8fafc;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        .customer-info-label {
            color: var(--gray);
            font-size: 0.85rem;
            margin-bottom: 5px;
        }

        .customer-info-value {
            color: var(--dark);
            font-weight: 600;
            font-size: 0.95rem;
        }

        /* Status Styles */
        .status {
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-block;
            min-width: 120px;
            text-align: center;
        }

        .status-pending {
            background: #fff5f5;
            color: #c53030;
            border: 2px solid #fed7d7;
        }

        .status-in-progress {
            background: #fffaf0;
            color: #b7791f;
            border: 2px solid #feebc8;
        }

        .status-resolved {
            background: #f0fff4;
            color: #276749;
            border: 2px solid #c6f6d5;
        }

        /* Admin Remarks */
        .admin-remarks-box {
            background: #fffaf0;
            padding: 25px;
            border-radius: 15px;
            border: 2px solid #feebc8;
            margin-top: 30px;
            position: relative;
        }

        .admin-remarks-box::before {
            content: 'Admin Remarks';
            position: absolute;
            top: -12px;
            left: 20px;
            background: #fffaf0;
            padding: 0 10px;
            color: var(--warning);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .remarks-content {
            color: #744210;
            line-height: 1.8;
            white-space: pre-line;
            margin-top: 10px;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid rgba(0, 0, 0, 0.05);
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

        /* Responsive */
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
            
            .page-content {
                padding: 25px;
            }
            
            .details-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
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
            
            <div class="nav-section">
                <div class="digital-clock">
                    <i class="fas fa-clock clock-icon"></i>
                    <span id="current-time">00:00:00</span>
                </div>
                
                <nav>
                    <ul>
                        <li><a href="admin-dashboard.php"><i class="fas fa-user-shield"></i> Admin Dashboard</a></li>
                        <li><a href="complaint-details.php?id=<?php echo $complaint_id; ?>" class="active"><i class="fas fa-eye"></i> Complaint Details</a></li>
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
            <div class="page-content">
                <!-- Complaint Details Header -->
                <div class="details-header">
                    <h1>
                        <i class="fas fa-file-alt" style="color: var(--accent);"></i>
                        Complaint Details
                        <span class="complaint-id-badge">#<?php echo $complaint['Complaint_ID']; ?></span>
                    </h1>
                    <div class="status <?php echo strtolower(str_replace(' ', '-', $complaint['Status_Name'])); ?>">
                        <?php echo htmlspecialchars($complaint['Status_Name']); ?>
                    </div>
                </div>
                
                <!-- Complaint Details Grid -->
                <div class="details-grid">
                    <!-- Complaint Information -->
                    <div class="detail-card">
                        <h3><i class="fas fa-info-circle"></i> Complaint Information</h3>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-heading"></i> Title</div>
                            <div class="detail-value"><?php echo htmlspecialchars($complaint['Complaint_Title']); ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-align-left"></i> Description</div>
                            <div class="description-box">
                                <?php echo nl2br(htmlspecialchars($complaint['Complaint_Description'])); ?>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-tag"></i> Category</div>
                            <div class="detail-value"><?php echo htmlspecialchars($complaint['Category_Name']); ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-calendar"></i> Date Submitted</div>
                            <div class="detail-value">
                                <i class="far fa-clock"></i> <?php echo date('F j, Y', strtotime($complaint['Date_Submitted'])); ?>
                                <br>
                                <small><?php echo date('h:i A', strtotime($complaint['Date_Submitted'])); ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Customer Information -->
                    <div class="detail-card">
                        <h3><i class="fas fa-user"></i> Customer Information</h3>
                        <div class="customer-info-grid">
                            <div class="customer-info-item">
                                <div class="customer-info-label">Name</div>
                                <div class="customer-info-value"><?php echo htmlspecialchars($complaint['UserName']); ?></div>
                            </div>
                            
                            <div class="customer-info-item">
                                <div class="customer-info-label">Email</div>
                                <div class="customer-info-value"><?php echo htmlspecialchars($complaint['Email']); ?></div>
                            </div>
                            
                            <div class="customer-info-item">
                                <div class="customer-info-label">Phone</div>
                                <div class="customer-info-value"><?php echo htmlspecialchars($complaint['Phone_Number']); ?></div>
                            </div>
                            
                            <div class="customer-info-item">
                                <div class="customer-info-label">User ID</div>
                                <div class="customer-info-value">#<?php echo htmlspecialchars($complaint['User_ID']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Admin Remarks -->
                <?php if (!empty($complaint['Admin_Remarks'])): ?>
                <div class="admin-remarks-box">
                    <div class="detail-label"><i class="fas fa-comment-dots"></i> Admin Remarks</div>
                    <div class="remarks-content">
                        <?php echo nl2br(htmlspecialchars($complaint['Admin_Remarks'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="window.location.href='admin-dashboard.php'">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </button>
                    <button class="btn" onclick="window.history.back()" style="background: #e2e8f0; color: var(--dark);">
                        <i class="fas fa-undo"></i> Go Back
                    </button>
                    <button class="btn btn-success" onclick="window.location.href='admin-dashboard.php?edit=<?php echo $complaint_id; ?>'">
                        <i class="fas fa-edit"></i> Update Status
                    </button>
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
                        <li><a href="admin-dashboard.php"><i class="fas fa-chevron-right"></i> Admin Dashboard</a></li>
                        <li><a href="complaint-details.php?id=<?php echo $complaint_id; ?>"><i class="fas fa-chevron-right"></i> Current Complaint</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h4>Admin Contact</h4>
                    <ul>
                        <li><i class="fas fa-envelope"></i> admin@crussaderio.com</li>
                        <li><i class="fas fa-phone"></i> +6016-9158066</li>
                        <li><i class="fas fa-user"></i> <?php echo htmlspecialchars($admin['Name'] ?? 'Administrator'); ?></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2023 Crussader IO Premium Streetwear. All rights reserved.</p>
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
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        });

        // Fade in animation
        document.addEventListener('DOMContentLoaded', function() {
            const detailCards = document.querySelectorAll('.detail-card');
            detailCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.style.animation = 'fadeIn 0.5s ease forwards';
                card.style.opacity = '0';
            });
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Escape to go back
            if (e.key === 'Escape') {
                window.history.back();
            }
            // Ctrl + D for dashboard
            if (e.ctrlKey && e.key === 'd') {
                e.preventDefault();
                window.location.href = 'admin-dashboard.php';
            }
        });
    </script>
</body>
</html>