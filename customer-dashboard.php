<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$user_query = $conn->query("SELECT * FROM User WHERE User_ID = $user_id");
$user = $user_query->fetch_assoc();

// Fetch user complaints
$complaints_query = $conn->query("
    SELECT c.Complaint_ID, c.Complaint_Title, cat.Category_Name, s.Status_Name, c.Date_Submitted
    FROM Complaint c
    JOIN Category cat ON c.Category_ID = cat.Category_ID
    JOIN Status s ON c.Status_ID = s.Status_ID
    WHERE c.User_ID = $user_id
    ORDER BY c.Date_Submitted DESC
");

// Count complaints
$count_query = $conn->query("SELECT COUNT(*) as total FROM Complaint WHERE User_ID = $user_id");
$complaint_count = $count_query->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Crussader IO</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Font Awesome (keeping your existing) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Shared CSS from original file - KEEPING ALL YOUR EXISTING STYLES */
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
            border: none;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #2f855a, var(--success));
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

        /* Customer Dashboard */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }

        .profile-card {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 15px;
            box-shadow: var(--shadow);
            text-align: center;
            border-top: 5px solid var(--accent);
        }

        .profile-header {
            margin-bottom: 25px;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--accent), var(--primary));
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            box-shadow: 0 8px 25px rgba(233, 69, 96, 0.3);
        }

        .profile-header h3 {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .profile-header p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .profile-info {
            text-align: left;
            margin-top: 20px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            color: var(--gray);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-value {
            color: var(--dark);
            font-weight: 600;
        }

        .complaints-card {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 15px;
            box-shadow: var(--shadow);
            border-top: 5px solid var(--accent);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(0, 0, 0, 0.05);
        }

        .section-header h3 {
            font-size: 1.5rem;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .complaint-id-display {
            background: linear-gradient(135deg, #f6f9fc, #e3ecf7);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 4px solid var(--success);
        }

        .complaint-id-display h4 {
            color: var(--primary);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .complaint-id {
            font-size: 2rem;
            font-weight: 800;
            color: var(--accent);
            text-align: center;
            padding: 15px;
            background: rgba(233, 69, 96, 0.1);
            border-radius: 10px;
            margin: 10px 0;
        }

        .complaint-id-note {
            color: var(--gray);
            font-size: 0.9rem;
            text-align: center;
        }

        /* Tables */
        .table-container {
            background: var(--card-bg);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            overflow-x: auto;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: var(--gradient);
            color: white;
        }

        th {
            padding: 18px 20px;
            text-align: left;
            font-weight: 600;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
        }

        td {
            padding: 18px 20px;
            border-bottom: 1px solid #edf2f7;
        }

        tbody tr {
            transition: background-color 0.3s ease;
        }

        tbody tr:hover {
            background-color: #f7fafc;
        }

        .status {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
            min-width: 100px;
            text-align: center;
        }

        .status-pending {
            background: #fff5f5;
            color: #c53030;
            border: 1px solid #fed7d7;
        }

        .status-in-progress {
            background: #fffaf0;
            color: #b7791f;
            border: 1px solid #feebc8;
        }

        .status-resolved {
            background: #f0fff4;
            color: #276749;
            border: 1px solid #c6f6d5;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .action-btn.view {
            background: #ebf8ff;
            color: var(--accent);
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

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #e2e8f0;
        }

        .empty-state h4 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: var(--dark);
        }

        .empty-state p {
            font-size: 1rem;
            max-width: 500px;
            margin: 0 auto 30px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
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
            
            .page-content {
                padding: 25px;
            }
            
            .section-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
        
        /* Bootstrap utility overrides to maintain your design */
        .btn.btn-bs-primary {
            background: linear-gradient(135deg, var(--accent), #ff6b81);
            color: white;
            border: none;
            border-radius: 30px;
            padding: 12px 28px;
            font-weight: 600;
        }
        
        .btn.btn-bs-success {
            background: linear-gradient(135deg, var(--success), #2f855a);
            color: white;
            border: none;
            border-radius: 30px;
            padding: 12px 28px;
            font-weight: 600;
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
                        <li><a href="complaint-form.php"><i class="fas fa-exclamation-circle"></i> File Complaint</a></li>
                        <li><a href="customer-dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="track-complaint.php"><i class="fas fa-search"></i> Track</a></li>
                    </ul>
                </nav>
            </div>
            
            <div class="auth-section">
                <!-- Using Bootstrap button classes -->
                <button class="btn btn-success btn-bs-success" id="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div class="container">
            <!-- Customer Dashboard -->
            <div class="page-content">
                <div class="dashboard-grid">
                    <!-- Profile Section -->
                    <div class="profile-card">
                        <div class="profile-header">
                            <div class="profile-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <h3 id="customer-profile-name"><?php echo htmlspecialchars($user['Name']); ?></h3>
                            <p id="customer-profile-email"><?php echo htmlspecialchars($user['Email']); ?></p>
                            <!-- Bootstrap badge example -->
                            <span class="status status-resolved d-inline-block mt-2">
                                <i class="fas fa-star"></i> Premium Member
                            </span>
                        </div>
                        
                        <div class="profile-info">
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-phone"></i> Phone</span>
                                <span class="info-value" id="customer-profile-phone"><?php echo htmlspecialchars($user['Phone_Number'] ?? 'Not provided'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-id-card"></i> Customer ID</span>
                                <span class="info-value" id="customer-profile-id"><?php echo htmlspecialchars($user['User_ID']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-file-alt"></i> Total Complaints</span>
                                <span class="info-value" id="customer-total-complaints"><?php echo $complaint_count; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-calendar"></i> Member Since</span>
                                <span class="info-value" id="member-since">
                                    <?php 
                                    if (isset($user['Registration_Date'])) {
                                        echo date('Y', strtotime($user['Registration_Date']));
                                    } else {
                                        echo date('Y');
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Complaints Section -->
                    <div class="complaints-card">
                        <div class="section-header">
                            <h3><i class="fas fa-list-alt"></i> Your Complaints</h3>
                            <!-- Using Bootstrap button -->
                            <button class="btn btn-primary btn-bs-primary" onclick="window.location.href='complaint-form.php'">
                                <i class="fas fa-plus"></i> New Complaint
                            </button>
                        </div>
                        
                        <!-- Complaint ID Display (shown after filing) -->
                        <?php if (isset($_SESSION['new_complaint_id'])): ?>
                        <div id="complaint-id-display">
                            <div class="complaint-id-display">
                                <h4><i class="fas fa-check-circle" style="color: var(--success);"></i> Complaint Submitted Successfully!</h4>
                                <p>Your complaint has been registered. Please save your Complaint ID for tracking:</p>
                                <div class="complaint-id" id="new-complaint-id"><?php echo $_SESSION['new_complaint_id']; ?></div>
                                <p class="complaint-id-note">
                                    <i class="fas fa-info-circle"></i> Use this ID to track your complaint status
                                </p>
                            </div>
                        </div>
                        <?php 
                            unset($_SESSION['new_complaint_id']);
                        endif; ?>
                        
                        <div class="table-container">
                            <?php if ($complaints_query->num_rows > 0): ?>
                            <!-- Using Bootstrap table classes while keeping your styling -->
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Category</th>
                                        <th>Title</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="customer-complaints-table">
                                    <?php while($row = $complaints_query->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['Complaint_ID']); ?></td>
                                        <td><?php echo htmlspecialchars($row['Category_Name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['Complaint_Title']); ?></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($row['Date_Submitted'])); ?></td>
                                        <td>
                                            <?php 
                                            $status_class = '';
                                            switch($row['Status_Name']) {
                                                case 'Pending': $status_class = 'status-pending'; break;
                                                case 'In Progress': $status_class = 'status-in-progress'; break;
                                                case 'Resolved': $status_class = 'status-resolved'; break;
                                                default: $status_class = 'status-pending';
                                            }
                                            ?>
                                            <span class="status <?php echo $status_class; ?>">
                                                <?php echo htmlspecialchars($row['Status_Name']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <!-- Using Bootstrap button style -->
                                            <button class="action-btn view btn btn-sm btn-outline-primary" onclick="viewComplaint(<?php echo $row['Complaint_ID']; ?>)">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <h4>No Complaints Yet</h4>
                                <p>You haven't filed any complaints yet. Click the button above to submit your first complaint.</p>
                                <!-- Using Bootstrap button -->
                                <button class="btn btn-primary btn-bs-primary" onclick="window.location.href='complaint-form.php'">
                                    <i class="fas fa-plus"></i> File Your First Complaint
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
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
                        <!-- Using Bootstrap icons -->
                        <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-icon"><i class="bi bi-tiktok"></i></a>
                        <a href="#" class="social-icon"><i class="bi bi-twitter"></i></a>
                    </div>
                </div>
                
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php"><i class="fas fa-chevron-right"></i> Home</a></li>
                        <li><a href="complaint-form.php"><i class="fas fa-chevron-right"></i> File Complaint</a></li>
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

    <!-- Bootstrap 5.3 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

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
            // Using Bootstrap modal for confirmation (optional enhancement)
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        });

        // View complaint details
        function viewComplaint(complaintId) {
            // Redirect to track complaint page with the ID
            window.location.href = 'track-complaint.php?complaint_id=' + complaintId;
        }

        // Auto-hide success message after 10 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = document.getElementById('complaint-id-display');
            if (successMessage) {
                setTimeout(() => {
                    successMessage.style.opacity = '0';
                    successMessage.style.transition = 'opacity 0.5s ease';
                    setTimeout(() => {
                        successMessage.style.display = 'none';
                    }, 500);
                }, 10000);
            }

            // Add fade-in animation to table rows
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach((row, index) => {
                row.style.animationDelay = `${index * 0.05}s`;
            });

            // Refresh page every 5 minutes to update complaint status
            setTimeout(() => {
                window.location.reload();
            }, 300000); // 5 minutes
            
            // Bootstrap tooltip example (if you want to add tooltips)
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>