<?php
include 'db_connect.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get current page (dashboard or complaints)
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Update status if posted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle status update
    if (isset($_POST['complaint_id'], $_POST['status_id'])) {
        $complaint_id = $_POST['complaint_id'];
        $status_id = $_POST['status_id'];
        $admin_remarks = $_POST['admin_remarks'] ?? '';
        
        $stmt = $conn->prepare("UPDATE Complaint SET Status_ID = ?, Admin_Remarks = ? WHERE Complaint_ID = ?");
        $stmt->bind_param("isi", $status_id, $admin_remarks, $complaint_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Complaint #$complaint_id updated successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to update complaint.";
        }
        $stmt->close();
        
        header("Location: admin-dashboard.php?page=complaints");
        exit();
    }
    
    // Handle delete
    if (isset($_POST['delete_complaint_id'])) {
        $complaint_id = $_POST['delete_complaint_id'];
        
        $stmt = $conn->prepare("DELETE FROM Complaint WHERE Complaint_ID = ?");
        $stmt->bind_param("i", $complaint_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Complaint #$complaint_id deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to delete complaint.";
        }
        $stmt->close();
        
        header("Location: admin-dashboard.php?page=complaints");
        exit();
    }
}

// Fetch admin details
$admin_id = $_SESSION['admin_id'];
$admin_query = $conn->query("SELECT * FROM Admin WHERE Admin_ID = $admin_id");
$admin = $admin_query->fetch_assoc();

// Get statistics (always fetched for dashboard)
$stats_query = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM User WHERE User_Type = 'Customer') as total_customers,
        (SELECT COUNT(*) FROM Complaint) as total_complaints,
        (SELECT COUNT(*) FROM Complaint WHERE Status_ID = 1) as pending_complaints,
        (SELECT COUNT(*) FROM Complaint WHERE Status_ID = 2) as inprogress_complaints,
        (SELECT COUNT(*) FROM Complaint WHERE Status_ID = 3) as resolved_complaints
");
$stats = $stats_query->fetch_assoc();

// Get complaints data if on complaints page
if ($current_page === 'complaints') {
    // Get current filter
    $current_filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
    $where_clause = '';

    switch($current_filter) {
        case 'pending':
            $where_clause = "WHERE c.Status_ID = 1";
            break;
        case 'in-progress':
            $where_clause = "WHERE c.Status_ID = 2";
            break;
        case 'resolved':
            $where_clause = "WHERE c.Status_ID = 3";
            break;
        default:
            $where_clause = "";
    }

    // Fetch complaints with filter
    $complaints_query = "
        SELECT c.Complaint_ID, c.Complaint_Title, c.Complaint_Description, 
               c.Admin_Remarks, u.Name AS UserName, u.Email AS UserEmail, 
               u.Phone_Number AS UserPhone, cat.Category_Name, 
               s.Status_ID, s.Status_Name, c.Date_Submitted
        FROM Complaint c
        JOIN User u ON c.User_ID = u.User_ID
        JOIN Category cat ON c.Category_ID = cat.Category_ID
        JOIN Status s ON c.Status_ID = s.Status_ID
        $where_clause
        ORDER BY c.Date_Submitted DESC
    ";

    $complaints = $conn->query($complaints_query);
    
    // Store complaints data for JavaScript PDF export
    $complaints_data = [];
    if ($complaints && $complaints->num_rows > 0) {
        while($row = $complaints->fetch_assoc()) {
            $complaints_data[] = $row;
        }
        // Reset pointer for table display
        $complaints->data_seek(0);
    }
}

// Get status options for modal
$statuses = $conn->query("SELECT * FROM Status ORDER BY Status_ID");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Crussader IO</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Font Awesome (keeping your existing) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Include jsPDF from CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <!-- Include html2canvas from CDN for better PDF generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <!-- Include jsPDF-autotable plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    
    <style>
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

        /* Header */
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

        /* Main Navigation */
        .main-nav {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 5px;
            display: flex;
            gap: 5px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin: 0 20px;
        }

        .main-nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 12px 28px;
            border-radius: 10px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.95rem;
        }

        .main-nav a i {
            font-size: 1rem;
        }

        .main-nav a:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .main-nav a.active {
            background: linear-gradient(135deg, var(--accent), #ff6b81);
            box-shadow: 0 4px 15px rgba(233, 69, 96, 0.3);
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

        /* Sub Navigation (for Complaint Management) */
        .sub-nav {
            background: white;
            border-radius: 12px;
            padding: 5px;
            display: flex;
            gap: 5px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
            overflow-x: auto;
        }

        .sub-nav a {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            padding: 12px 24px;
            border-radius: 10px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            white-space: nowrap;
        }

        .sub-nav a i {
            font-size: 0.9rem;
        }

        .sub-nav a:hover {
            background: #f7fafc;
        }

        .sub-nav a.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            box-shadow: 0 4px 15px rgba(26, 26, 46, 0.2);
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

        /* Admin Info Card */
        .admin-info-card {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
            border-left: 5px solid var(--accent);
        }

        .admin-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .admin-info-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .admin-info-label {
            color: var(--gold);
            font-size: 0.9rem;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .admin-info-value {
            font-size: 1.1rem;
            font-weight: 600;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(to right, var(--accent), #ff6b81);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            background: linear-gradient(135deg, var(--accent), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-card h3 {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 10px;
            font-weight: 800;
        }

        .stat-card p {
            color: var(--gray);
            font-weight: 500;
        }

        /* Section Header */
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

        /* Table */
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
            min-width: 900px;
        }

        thead {
            background: var(--gradient);
            color: white;
        }

        th {
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #edf2f7;
            vertical-align: top;
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
            flex-wrap: wrap;
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
            border: 1px solid #bee3f8;
        }

        .action-btn.edit {
            background: #fffaf0;
            color: var(--warning);
            border: 1px solid #feebc8;
        }

        .action-btn.delete {
            background: #fff5f5;
            color: var(--danger);
            border: 1px solid #fed7d7;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1001;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(3px);
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }

        .modal-header h3 {
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(233, 69, 96, 0.1);
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        /* View Modal */
        .complaint-details {
            margin-bottom: 20px;
        }

        .detail-item {
            margin-bottom: 15px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid var(--accent);
        }

        .detail-label {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-value {
            color: var(--dark);
            line-height: 1.6;
        }

        /* Alerts */
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: fadeIn 0.5s ease;
        }

        .alert-success {
            background: #f0fff4;
            color: #276749;
            border: 2px solid #c6f6d5;
        }

        .alert-error {
            background: #fff5f5;
            color: #c53030;
            border: 2px solid #fed7d7;
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
            
            .main-nav {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
                margin: 0;
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
            
            .sub-nav {
                flex-direction: column;
            }
            
            .sub-nav a {
                min-width: 100%;
            }
            
            .actions {
                flex-direction: column;
                width: 100%;
            }
            
            .action-btn {
                width: 100%;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            table {
                min-width: 700px;
            }
        }
        
        /* PDF Export Loading */
        .pdf-loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            flex-direction: column;
            color: white;
        }
        
        .pdf-loading-content {
            text-align: center;
            background: var(--primary);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }
        
        .pdf-loading i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: var(--accent);
        }
        
        .pdf-loading h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
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
        
        .btn.btn-bs-danger {
            background: linear-gradient(135deg, var(--danger), #c53030);
            color: white;
            border: none;
            border-radius: 30px;
            padding: 12px 28px;
            font-weight: 600;
        }
        
        /* Bootstrap form styling integration */
        .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            font-size: 0.95rem;
        }
        
        .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(233, 69, 96, 0.1);
        }
    </style>
</head>
<body>
    <!-- PDF Loading Overlay -->
    <div id="pdfLoading" class="pdf-loading">
        <div class="pdf-loading-content">
            <i class="fas fa-spinner fa-spin"></i>
            <h3>Generating PDF Report</h3>
            <p>Please wait while we prepare your report...</p>
            <p style="font-size: 0.9rem; opacity: 0.8; margin-top: 10px;">This may take a few moments</p>
        </div>
    </div>

    <!-- Header -->
    <header>
        <div class="container header-content">
            <div class="logo-section">
                <a href="admin-dashboard.php?page=dashboard" class="logo">
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
                <div class="main-nav">
                    <a href="admin-dashboard.php?page=dashboard" class="<?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="admin-dashboard.php?page=complaints" class="<?php echo $current_page === 'complaints' ? 'active' : ''; ?>">
                        <i class="fas fa-tasks"></i> Complaint Management
                    </a>
                </div>
                
                <div class="digital-clock">
                    <i class="fas fa-clock clock-icon"></i>
                    <span id="current-time">00:00:00</span>
                </div>
            </div>
            
            <div class="auth-section">
                <!-- Using Bootstrap button -->
                <button class="btn btn-success btn-bs-success" id="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div class="container">
            <div class="page-content">
                <?php if (isset($_SESSION['success_message'])): ?>
                    <!-- Bootstrap alert -->
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <!-- Bootstrap alert -->
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
                
                <?php if ($current_page === 'dashboard'): ?>
                    <!-- DASHBOARD PAGE CONTENT -->
                    
                    <!-- Admin Profile Info -->
                    <div class="admin-info-card">
                        <div class="section-header" style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                            <h3><i class="fas fa-user-shield"></i> Admin Dashboard</h3>
                            <!-- Bootstrap badge -->
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-crown"></i> Administrator
                            </span>
                        </div>
                        
                        <div class="admin-info-grid">
                            <div class="admin-info-item">
                                <div class="admin-info-label"><i class="fas fa-user"></i> Admin Name</div>
                                <div class="admin-info-value"><?php echo htmlspecialchars($admin['Name'] ?? 'Admin'); ?></div>
                            </div>
                            <div class="admin-info-item">
                                <div class="admin-info-label"><i class="fas fa-id-card"></i> Admin ID</div>
                                <div class="admin-info-value"><?php echo htmlspecialchars($admin_id); ?></div>
                            </div>
                            <div class="admin-info-item">
                                <div class="admin-info-label"><i class="fas fa-envelope"></i> Email</div>
                                <div class="admin-info-value"><?php echo htmlspecialchars($admin['Email'] ?? 'admin@crussaderio.com'); ?></div>
                            </div>
                            <div class="admin-info-item">
                                <div class="admin-info-label"><i class="fas fa-phone"></i> Phone</div>
                                <div class="admin-info-value"><?php echo htmlspecialchars($admin['Phone_Number'] ?? '+6016-9158066'); ?></div>
                            </div>
                            <div class="admin-info-item">
                                <div class="admin-info-label"><i class="fas fa-user-tag"></i> Role</div>
                                <div class="admin-info-value">Administrator</div>
                            </div>
                            <div class="admin-info-item">
                                <div class="admin-info-label"><i class="fas fa-calendar"></i> Last Login</div>
                                <div class="admin-info-value"><?php echo date('Y-m-d H:i'); ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Stats -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <i class="fas fa-users"></i>
                            <h3 id="admin-total-customers"><?php echo $stats['total_customers'] ?? 0; ?></h3>
                            <p>Total Customers</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-comments"></i>
                            <h3 id="admin-total-complaints"><?php echo $stats['total_complaints'] ?? 0; ?></h3>
                            <p>Total Complaints</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-clock"></i>
                            <h3 id="admin-pending"><?php echo $stats['pending_complaints'] ?? 0; ?></h3>
                            <p>Pending</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-spinner"></i>
                            <h3 id="admin-inprogress"><?php echo $stats['inprogress_complaints'] ?? 0; ?></h3>
                            <p>In Progress</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-check-circle"></i>
                            <h3 id="admin-resolved"><?php echo $stats['resolved_complaints'] ?? 0; ?></h3>
                            <p>Resolved</p>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="section-header">
                        <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                    </div>
                    
                    <div class="stats-grid">
                        <div class="stat-card" onclick="window.location.href='admin-dashboard.php?page=complaints&filter=pending'" style="cursor: pointer;">
                            <i class="fas fa-clock"></i>
                            <h3><?php echo $stats['pending_complaints'] ?? 0; ?></h3>
                            <p>View Pending Complaints</p>
                        </div>
                        <div class="stat-card" onclick="window.location.href='admin-dashboard.php?page=complaints&filter=in-progress'" style="cursor: pointer;">
                            <i class="fas fa-spinner"></i>
                            <h3><?php echo $stats['inprogress_complaints'] ?? 0; ?></h3>
                            <p>View In Progress</p>
                        </div>
                        <div class="stat-card" onclick="window.location.href='admin-dashboard.php?page=complaints'" style="cursor: pointer;">
                            <i class="fas fa-list"></i>
                            <h3><?php echo $stats['total_complaints'] ?? 0; ?></h3>
                            <p>View All Complaints</p>
                        </div>
                        <div class="stat-card" onclick="exportToPDF('dashboard')" style="cursor: pointer;">
                            <i class="fas fa-file-pdf"></i>
                            <h3>PDF</h3>
                            <p>Export Report</p>
                        </div>
                    </div>
                    
                <?php elseif ($current_page === 'complaints'): ?>
                    <!-- COMPLAINT MANAGEMENT PAGE CONTENT -->
                    
                    <div class="section-header">
                        <h3><i class="fas fa-tasks"></i> Complaint Management</h3>
                        <div style="display: flex; gap: 10px;">
                            <!-- Using Bootstrap buttons -->
                            <button class="btn btn-primary btn-bs-primary" onclick="exportToPDF('complaints')" id="export-pdf-btn">
                                <i class="fas fa-file-pdf"></i> Export to PDF
                            </button>
                            <button class="btn btn-primary btn-bs-primary" id="refresh-btn">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    
                    <!-- Filter Tabs -->
                    <div class="sub-nav">
                        <a href="admin-dashboard.php?page=complaints&filter=all" class="<?php echo $current_filter == 'all' ? 'active' : ''; ?>">
                            <i class="fas fa-list"></i> All Complaints
                        </a>
                        <a href="admin-dashboard.php?page=complaints&filter=pending" class="<?php echo $current_filter == 'pending' ? 'active' : ''; ?>">
                            <i class="fas fa-clock"></i> Pending
                        </a>
                        <a href="admin-dashboard.php?page=complaints&filter=in-progress" class="<?php echo $current_filter == 'in-progress' ? 'active' : ''; ?>">
                            <i class="fas fa-spinner"></i> In Progress
                        </a>
                        <a href="admin-dashboard.php?page=complaints&filter=resolved" class="<?php echo $current_filter == 'resolved' ? 'active' : ''; ?>">
                            <i class="fas fa-check-circle"></i> Resolved
                        </a>
                    </div>
                    
                    <!-- Statistics Summary -->
                    <div class="stats-grid" style="margin-bottom: 30px;">
                        <div class="stat-card">
                            <i class="fas fa-users"></i>
                            <h3><?php echo $stats['total_customers'] ?? 0; ?></h3>
                            <p>Total Customers</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-comments"></i>
                            <h3><?php echo $stats['total_complaints'] ?? 0; ?></h3>
                            <p>Total Complaints</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-clock"></i>
                            <h3><?php echo $stats['pending_complaints'] ?? 0; ?></h3>
                            <p>Pending</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-spinner"></i>
                            <h3><?php echo $stats['inprogress_complaints'] ?? 0; ?></h3>
                            <p>In Progress</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-check-circle"></i>
                            <h3><?php echo $stats['resolved_complaints'] ?? 0; ?></h3>
                            <p>Resolved</p>
                        </div>
                    </div>
                    
                    <!-- Complaints Table with Bootstrap classes -->
                    <div class="table-container">
                        <table id="complaintsTable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Category</th>
                                    <th>Title</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Admin Remarks</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($complaints) && $complaints->num_rows > 0): ?>
                                    <?php while($row = $complaints->fetch_assoc()): ?>
                                        <?php 
                                        $status_class = '';
                                        switch($row['Status_Name']) {
                                            case 'Pending': $status_class = 'status-pending'; break;
                                            case 'In Progress': $status_class = 'status-in-progress'; break;
                                            case 'Resolved': $status_class = 'status-resolved'; break;
                                            default: $status_class = 'status-pending';
                                        }
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['Complaint_ID']); ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($row['UserName']); ?></strong><br>
                                                <small><?php echo htmlspecialchars($row['UserEmail']); ?></small><br>
                                                <small><?php echo htmlspecialchars($row['UserPhone'] ?? 'N/A'); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['Category_Name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['Complaint_Title']); ?></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($row['Date_Submitted'])); ?></td>
                                            <td><span class="status <?php echo $status_class; ?>"><?php echo htmlspecialchars($row['Status_Name']); ?></span></td>
                                            <td><?php echo nl2br(htmlspecialchars($row['Admin_Remarks'] ?? 'No remarks')); ?></td>
                                            <td>
                                                <div class="actions">
                                                    <!-- Using Bootstrap button styling -->
                                                    <button class="action-btn view btn btn-sm btn-outline-primary" onclick="viewComplaint(<?php echo $row['Complaint_ID']; ?>, '<?php echo htmlspecialchars(addslashes($row['Complaint_Title'])); ?>', '<?php echo htmlspecialchars(addslashes($row['UserName'])); ?>', '<?php echo htmlspecialchars(addslashes($row['UserEmail'])); ?>', '<?php echo htmlspecialchars(addslashes($row['UserPhone'] ?? 'N/A')); ?>', '<?php echo htmlspecialchars(addslashes($row['Category_Name'])); ?>', '<?php echo htmlspecialchars(addslashes($row['Complaint_Description'])); ?>', '<?php echo htmlspecialchars(addslashes($row['Status_Name'])); ?>', '<?php echo htmlspecialchars(addslashes($row['Admin_Remarks'] ?? '')); ?>', '<?php echo date('Y-m-d H:i', strtotime($row['Date_Submitted'])); ?>')">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                    <button class="action-btn edit btn btn-sm btn-outline-warning" onclick="openUpdateModal(<?php echo $row['Complaint_ID']; ?>, <?php echo $row['Status_ID']; ?>, '<?php echo htmlspecialchars(addslashes($row['Admin_Remarks'] ?? '')); ?>')">
                                                        <i class="fas fa-edit"></i> Update
                                                    </button>
                                                    <button class="action-btn delete btn btn-sm btn-outline-danger" onclick="deleteComplaint(<?php echo $row['Complaint_ID']; ?>)">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <i class="fas fa-inbox fs-1 text-secondary mb-3"></i><br>
                                            <span class="text-muted">No complaints found.</span>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Update Status Modal -->
    <div id="updateModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Update Complaint Status</h3>
                <button onclick="closeModal()" style="background: none; border: none; color: var(--accent); font-size: 1.5rem; cursor: pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="updateForm" method="POST">
                <input type="hidden" id="modal-complaint-id" name="complaint_id">
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <!-- Using Bootstrap form-select -->
                    <select name="status_id" id="modal-status-id" class="form-select" required>
                        <?php 
                        $statuses_result = $conn->query("SELECT * FROM Status ORDER BY Status_ID");
                        while($status = $statuses_result->fetch_assoc()): ?>
                            <option value="<?php echo $status['Status_ID']; ?>">
                                <?php echo htmlspecialchars($status['Status_Name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Admin Remarks</label>
                    <textarea name="admin_remarks" id="modal-admin-remarks" class="form-control" 
                              placeholder="Add remarks about this complaint..." rows="4"></textarea>
                </div>
                
                <div class="actions" style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary btn-bs-primary">
                        <i class="fas fa-save"></i> Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Complaint Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-eye"></i> Complaint Details</h3>
                <button onclick="closeViewModal()" style="background: none; border: none; color: var(--accent); font-size: 1.5rem; cursor: pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="complaint-details">
                <div class="detail-item">
                    <div class="detail-label"><i class="fas fa-hashtag"></i> Complaint ID</div>
                    <div class="detail-value" id="view-complaint-id"></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label"><i class="fas fa-user"></i> Customer Information</div>
                    <div class="detail-value">
                        <strong id="view-customer-name"></strong><br>
                        <small id="view-customer-email"></small><br>
                        <small id="view-customer-phone"></small>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label"><i class="fas fa-tag"></i> Category</div>
                    <div class="detail-value" id="view-category"></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label"><i class="fas fa-heading"></i> Title</div>
                    <div class="detail-value" id="view-title"></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label"><i class="fas fa-align-left"></i> Description</div>
                    <div class="detail-value" id="view-description"></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label"><i class="fas fa-calendar"></i> Date Submitted</div>
                    <div class="detail-value" id="view-date"></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label"><i class="fas fa-tasks"></i> Status</div>
                    <div class="detail-value">
                        <span id="view-status" class="status"></span>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label"><i class="fas fa-comment"></i> Admin Remarks</div>
                    <div class="detail-value" id="view-remarks"></div>
                </div>
            </div>
            <div class="actions" style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeViewModal()">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-trash"></i> Delete Complaint</h3>
                <button onclick="closeDeleteModal()" style="background: none; border: none; color: var(--accent); font-size: 1.5rem; cursor: pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="deleteForm" method="POST">
                <input type="hidden" id="delete-complaint-id" name="delete_complaint_id">
                
                <div class="form-group">
                    <p style="color: var(--dark); font-size: 1.1rem; text-align: center; margin: 20px 0;">
                        <i class="fas fa-exclamation-triangle text-danger fs-1 mb-3 d-block"></i>
                        Are you sure you want to delete this complaint?
                    </p>
                    <p style="color: var(--gray); text-align: center; font-size: 0.95rem;">
                        This action cannot be undone. All complaint data will be permanently removed.
                    </p>
                </div>
                
                <div class="actions" style="margin-top: 20px; display: flex; gap: 10px; justify-content: center;">
                    <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger btn-bs-danger">
                        <i class="fas fa-trash"></i> Delete Complaint
                    </button>
                </div>
            </form>
        </div>
    </div>

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
                        <li><a href="admin-dashboard.php?page=dashboard"><i class="fas fa-chevron-right"></i> Dashboard</a></li>
                        <li><a href="admin-dashboard.php?page=complaints"><i class="fas fa-chevron-right"></i> Complaint Management</a></li>
                        <li><a href="index.php"><i class="fas fa-chevron-right"></i> Home Page</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h4>Admin Contact</h4>
                    <ul>
                        <li><i class="fas fa-envelope"></i> admin@crussaderio.com</li>
                        <li><i class="fas fa-phone"></i> +6016-9158066</li>
                        <li><i class="fas fa-user"></i> Muhammad Najwan</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Crussader IO Premium Streetwear. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Store PHP data for JavaScript
        const complaintsData = <?php echo isset($complaints_data) ? json_encode($complaints_data) : '[]'; ?>;
        const stats = <?php echo json_encode($stats); ?>;
        const adminInfo = <?php echo json_encode($admin); ?>;
        const currentFilter = '<?php echo $current_filter ?? "all"; ?>';
        const currentPage = '<?php echo $current_page; ?>';

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

        // Export to PDF function
        async function exportToPDF(pageType) {
            try {
                // Show loading overlay
                document.getElementById('pdfLoading').style.display = 'flex';
                
                // Wait a moment for UI to update
                await new Promise(resolve => setTimeout(resolve, 100));
                
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF('landscape');
                
                const pageWidth = doc.internal.pageSize.getWidth();
                const pageHeight = doc.internal.pageSize.getHeight();
                
                // Add header with gradient background
                doc.setFillColor(26, 26, 46); // Dark blue from your theme
                doc.rect(0, 0, pageWidth, 40, 'F');
                
                // Add title
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(24);
                doc.setTextColor(255, 255, 255);
                doc.text('Crussader IO - Complaint Management System', pageWidth / 2, 15, { align: 'center' });
                
                doc.setFontSize(14);
                doc.text('Customer Complaints Report', pageWidth / 2, 25, { align: 'center' });
                
                // Add report details
                doc.setFontSize(10);
                doc.setTextColor(200, 200, 200);
                doc.text(`Generated on: ${new Date().toLocaleString()}`, 15, 35);
                doc.text(`Filter: ${currentFilter.charAt(0).toUpperCase() + currentFilter.slice(1).replace('-', ' ')}`, pageWidth - 15, 35, { align: 'right' });
                
                // Add admin info
                doc.setTextColor(0, 0, 0);
                doc.setFont('helvetica', 'normal');
                doc.setFontSize(10);
                doc.text(`Generated by: ${adminInfo?.Name || 'Admin'}`, 15, 45);
                doc.text(`Admin ID: ${adminInfo?.Admin_ID || 'N/A'}`, 15, 50);
                
                // Add statistics section
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(12);
                doc.text('Statistics Summary', 15, 65);
                
                doc.setFont('helvetica', 'normal');
                doc.setFontSize(10);
                let yPos = 72;
                
                const statItems = [
                    `Total Customers: ${stats.total_customers || 0}`,
                    `Total Complaints: ${stats.total_complaints || 0}`,
                    `Pending Complaints: ${stats.pending_complaints || 0}`,
                    `In Progress Complaints: ${stats.inprogress_complaints || 0}`,
                    `Resolved Complaints: ${stats.resolved_complaints || 0}`
                ];
                
                statItems.forEach((item, index) => {
                    const xPos = 15 + (index % 2) * 90;
                    const y = yPos + Math.floor(index / 2) * 6;
                    doc.text(item, xPos, y);
                });
                
                yPos += 25;
                
                // Add complaints table if we have data
                if (complaintsData.length > 0 && pageType === 'complaints') {
                    doc.setFont('helvetica', 'bold');
                    doc.setFontSize(12);
                    doc.text('Complaints List', 15, yPos);
                    yPos += 10;
                    
                    // Prepare table data
                    const tableData = complaintsData.map(complaint => [
                        complaint.Complaint_ID,
                        complaint.UserName,
                        complaint.Category_Name,
                        complaint.Complaint_Title.length > 30 ? complaint.Complaint_Title.substring(0, 30) + '...' : complaint.Complaint_Title,
                        new Date(complaint.Date_Submitted).toLocaleDateString(),
                        complaint.Status_Name,
                        complaint.Admin_Remarks ? (complaint.Admin_Remarks.length > 40 ? complaint.Admin_Remarks.substring(0, 40) + '...' : complaint.Admin_Remarks) : 'No remarks'
                    ]);
                    
                    // Create table using autoTable plugin
                    doc.autoTable({
                        startY: yPos,
                        head: [['ID', 'Customer', 'Category', 'Title', 'Date', 'Status', 'Remarks']],
                        body: tableData,
                        theme: 'grid',
                        headStyles: {
                            fillColor: [26, 26, 46], // Dark blue
                            textColor: 255,
                            fontStyle: 'bold'
                        },
                        alternateRowStyles: {
                            fillColor: [240, 240, 245]
                        },
                        columnStyles: {
                            0: { cellWidth: 15 }, // ID
                            1: { cellWidth: 30 }, // Customer
                            2: { cellWidth: 25 }, // Category
                            3: { cellWidth: 40 }, // Title
                            4: { cellWidth: 25 }, // Date
                            5: { cellWidth: 25 }, // Status
                            6: { cellWidth: 50 }  // Remarks
                        },
                        margin: { left: 15, right: 15 },
                        styles: {
                            fontSize: 8,
                            cellPadding: 3,
                            overflow: 'linebreak'
                        }
                    });
                    
                    // Add status distribution chart on a new page if needed
                    const finalY = doc.lastAutoTable.finalY;
                    if (finalY > pageHeight - 50) {
                        doc.addPage();
                        yPos = 20;
                    } else {
                        yPos = finalY + 10;
                    }
                } else if (pageType === 'dashboard') {
                    doc.setFont('helvetica', 'bold');
                    doc.setFontSize(12);
                    doc.text('Dashboard Summary Report', 15, yPos);
                    yPos += 10;
                    
                    doc.setFont('helvetica', 'normal');
                    doc.setFontSize(10);
                    doc.text('No detailed complaints data available. Switch to Complaints page for full report.', 15, yPos);
                }
                
                // Add footer
                const pageCount = doc.internal.getNumberOfPages();
                for (let i = 1; i <= pageCount; i++) {
                    doc.setPage(i);
                    doc.setFontSize(8);
                    doc.setTextColor(128, 128, 128);
                    doc.text(`Page ${i} of ${pageCount}`, pageWidth / 2, pageHeight - 10, { align: 'center' });
                    doc.text(`© ${new Date().getFullYear()} Crussader IO - Confidential`, pageWidth / 2, pageHeight - 5, { align: 'center' });
                }
                
                // Save PDF
                const filename = `Crussader_IO_Complaints_Report_${new Date().toISOString().slice(0, 10)}_${currentFilter}.pdf`;
                doc.save(filename);
                
            } catch (error) {
                console.error('PDF generation error:', error);
                alert('Error generating PDF. Please try again.');
            } finally {
                // Hide loading overlay
                document.getElementById('pdfLoading').style.display = 'none';
                
                // Reset export button
                const exportBtn = document.getElementById('export-pdf-btn');
                if (exportBtn) {
                    exportBtn.innerHTML = '<i class="fas fa-file-pdf"></i> Export to PDF';
                    exportBtn.disabled = false;
                }
            }
        }

        // Simple HTML table export as alternative
        function exportTableToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('landscape');
            
            // Get table HTML
            const table = document.getElementById('complaintsTable');
            if (!table) {
                alert('No table found to export');
                return;
            }
            
            // Simple table export
            doc.text('Crussader IO - Complaints Report', 15, 15);
            doc.autoTable({
                html: table,
                startY: 25,
                theme: 'grid',
                styles: { fontSize: 8 },
                headStyles: { fillColor: [26, 26, 46] }
            });
            
            doc.save('Complaints_Report.pdf');
        }

        // Logout functionality
        document.getElementById('logout-btn').addEventListener('click', function() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        });

        // Refresh button (only on complaints page)
        const refreshBtn = document.getElementById('refresh-btn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function() {
                window.location.reload();
            });
        }

        // View Complaint Modal
        function viewComplaint(id, title, name, email, phone, category, description, status, remarks, date) {
            document.getElementById('view-complaint-id').textContent = '#' + id;
            document.getElementById('view-customer-name').textContent = name;
            document.getElementById('view-customer-email').textContent = email;
            document.getElementById('view-customer-phone').textContent = phone;
            document.getElementById('view-category').textContent = category;
            document.getElementById('view-title').textContent = title;
            document.getElementById('view-description').textContent = description || 'No description provided';
            document.getElementById('view-date').textContent = date;
            document.getElementById('view-remarks').textContent = remarks || 'No remarks';
            
            // Set status with appropriate class
            const statusElement = document.getElementById('view-status');
            statusElement.textContent = status;
            statusElement.className = 'status ';
            
            if (status === 'Pending') {
                statusElement.classList.add('status-pending');
            } else if (status === 'In Progress') {
                statusElement.classList.add('status-in-progress');
            } else if (status === 'Resolved') {
                statusElement.classList.add('status-resolved');
            }
            
            document.getElementById('viewModal').style.display = 'flex';
        }

        function closeViewModal() {
            document.getElementById('viewModal').style.display = 'none';
        }

        // Update Status Modal
        function openUpdateModal(complaintId, statusId, adminRemarks = '') {
            document.getElementById('modal-complaint-id').value = complaintId;
            document.getElementById('modal-status-id').value = statusId;
            document.getElementById('modal-admin-remarks').value = adminRemarks;
            document.getElementById('updateModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('updateModal').style.display = 'none';
        }

        // Delete Modal
        function deleteComplaint(complaintId) {
            if (confirm('Are you sure you want to delete this complaint? This action cannot be undone.')) {
                document.getElementById('delete-complaint-id').value = complaintId;
                document.getElementById('deleteModal').style.display = 'flex';
            }
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const updateModal = document.getElementById('updateModal');
            const viewModal = document.getElementById('viewModal');
            const deleteModal = document.getElementById('deleteModal');
            
            if (event.target === updateModal) {
                closeModal();
            }
            if (event.target === viewModal) {
                closeViewModal();
            }
            if (event.target === deleteModal) {
                closeDeleteModal();
            }
        }

        // Auto-fade alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 0.5s ease';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 500);
                }, 5000);
            });
            
            // Add animations to table rows (only on complaints page)
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach((row, index) => {
                row.style.animationDelay = `${index * 0.05}s`;
                row.style.animation = 'fadeIn 0.3s ease forwards';
                row.style.opacity = '0';
            });
            
            // Initialize Bootstrap tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Esc to close any modal
            if (e.key === 'Escape') {
                closeModal();
                closeViewModal();
                closeDeleteModal();
            }
            // Ctrl+P to export PDF (only on complaints page)
            if ((e.ctrlKey || e.metaKey) && e.key === 'p' && window.location.search.includes('page=complaints')) {
                e.preventDefault();
                exportToPDF('complaints');
            }
            // F5 to refresh
            if (e.key === 'F5') {
                e.preventDefault();
                window.location.reload();
            }
        });

        // Auto-refresh page every 5 minutes
        setTimeout(() => {
            window.location.reload();
        }, 300000);
    </script>
</body>
</html>