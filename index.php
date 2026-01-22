<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crussader IO - Premium Clothing Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            --gradient-accent: linear-gradient(135deg, var(--accent), #ff6b81);
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
            background: var(--gradient-accent);
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            box-shadow: 0 4px 15px rgba(233, 69, 96, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .logo-text h1 {
            font-size: 2rem;
            font-weight: 900;
            letter-spacing: -0.5px;
            text-transform: uppercase;
            background: linear-gradient(to right, #fff, var(--gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .logo-text span {
            color: var(--accent);
            font-weight: 900;
        }

        .logo-text p {
            font-size: 0.8rem;
            opacity: 0.9;
            margin-top: 2px;
            color: #cbd5e0;
            letter-spacing: 1px;
            font-weight: 300;
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
            font-size: 1rem;
            letter-spacing: 1px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            font-family: 'Courier New', monospace;
        }

        .clock-icon {
            margin-right: 8px;
            color: var(--gold);
        }

        .store-nav ul {
            display: flex;
            list-style: none;
            gap: 5px;
            background: rgba(255, 255, 255, 0.1);
            padding: 5px;
            border-radius: 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .store-nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 20px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .store-nav a:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .store-nav a.active {
            background: var(--gradient-accent);
            box-shadow: 0 4px 20px rgba(233, 69, 96, 0.4);
        }

        .auth-section {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 10px 24px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            letter-spacing: 0.3px;
            text-transform: uppercase;
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
            background: var(--gradient-accent);
            color: white;
            border: none;
            font-weight: 700;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #ff6b81, var(--accent));
            box-shadow: 0 8px 25px rgba(233, 69, 96, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success), #2f855a);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger), #c53030);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning), #b7791f);
            color: white;
        }

        /* Hero Section */
        .hero {
            background: var(--gradient);
            color: white;
            text-align: center;
            padding: 80px 40px;
            border-radius: 20px;
            margin: 40px 0;
            position: relative;
            overflow: hidden;
            border: 3px solid var(--accent);
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1558769132-cb1f7d239b6f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center/cover;
            opacity: 0.1;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 0 auto;
        }

        .hero h2 {
            font-size: 2.8rem;
            font-weight: 900;
            margin-bottom: 20px;
            line-height: 1.2;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.3);
        }

        .hero p {
            font-size: 1.2rem;
            opacity: 0.95;
            margin-bottom: 30px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            font-weight: 300;
            letter-spacing: 0.5px;
        }

        .hero-tagline {
            background: rgba(255, 255, 255, 0.1);
            display: inline-block;
            padding: 8px 25px;
            border-radius: 30px;
            margin-bottom: 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 0.9rem;
        }

        /* Featured Collections */
        .collections-header {
            text-align: center;
            margin: 60px 0 40px;
        }

        .collections-header h3 {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 2px;
            position: relative;
            display: inline-block;
        }

        .collections-header h3::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--gradient-accent);
            border-radius: 2px;
        }

        .collections-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }

        .collection-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .collection-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .collection-image {
            height: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3.5rem;
            position: relative;
            overflow: hidden;
        }

        .collection-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.2);
        }

        .collection-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--gradient-accent);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            z-index: 2;
        }

        .collection-info {
            padding: 20px;
        }

        .collection-info h4 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: var(--primary);
            font-weight: 700;
        }

        .collection-info p {
            color: var(--gray);
            font-size: 0.85rem;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .collection-price {
            color: var(--accent);
            font-weight: 800;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .collection-price .old-price {
            color: var(--gray);
            text-decoration: line-through;
            font-size: 0.9rem;
            font-weight: 400;
        }

        /* Complaint System Section */
        .complaint-system {
            background: var(--gradient);
            color: white;
            padding: 60px 40px;
            border-radius: 20px;
            margin: 60px 0;
            text-align: center;
            border: 3px solid var(--accent);
            position: relative;
            overflow: hidden;
        }

        .complaint-system::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1553062407-98eeb64c6a62?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center/cover;
            opacity: 0.1;
        }

        .complaint-system h3 {
            font-size: 2.2rem;
            margin-bottom: 20px;
            color: var(--gold);
            text-transform: uppercase;
            letter-spacing: 2px;
            position: relative;
            z-index: 1;
        }

        .complaint-system p {
            font-size: 1.1rem;
            max-width: 800px;
            margin: 0 auto 30px;
            opacity: 0.95;
            position: relative;
            z-index: 1;
        }

        .system-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 40px;
            position: relative;
            z-index: 1;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px) scale(1.05);
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--gold);
        }

        .feature-card i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: var(--gold);
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .feature-card h4 {
            font-size: 1.3rem;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .feature-card p {
            font-size: 0.95rem;
            opacity: 0.9;
            margin: 0;
        }

        /* Store Stats */
        .store-stats {
            background: white;
            padding: 60px 40px;
            border-radius: 20px;
            margin: 40px 0;
            text-align: center;
            border: 3px solid var(--accent);
        }

        .store-stats h3 {
            font-size: 2rem;
            margin-bottom: 40px;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 30px;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 30px;
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
            background: var(--gradient-accent);
        }

        .stat-card:hover {
            transform: translateY(-8px) scale(1.05);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .stat-card i {
            font-size: 2.8rem;
            margin-bottom: 20px;
            background: var(--gradient-accent);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-card h3 {
            font-size: 2.8rem;
            color: var(--primary);
            margin-bottom: 10px;
            font-weight: 900;
        }

        .stat-card p {
            color: var(--gray);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.85rem;
        }

        /* Store Banner */
        .store-banner {
            background: linear-gradient(rgba(22, 33, 62, 0.95), rgba(26, 26, 46, 0.95)), url('https://images.unsplash.com/photo-1445205170230-053b83016050?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            padding: 80px 40px;
            border-radius: 20px;
            margin: 60px 0;
            text-align: center;
            border: 3px solid var(--accent);
            position: relative;
            overflow: hidden;
        }

        .store-banner h3 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: var(--gold);
            text-transform: uppercase;
            letter-spacing: 3px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .store-banner p {
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto 40px;
            opacity: 0.95;
            line-height: 1.8;
        }

        .brand-tagline {
            background: rgba(255, 255, 255, 0.1);
            display: inline-block;
            padding: 12px 35px;
            border-radius: 30px;
            margin-top: 30px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 215, 0, 0.3);
            font-size: 1.1rem;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .action-buttons .btn {
            min-width: 200px;
            padding: 16px 30px;
            font-size: 1rem;
            border-radius: 30px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Footer */
        footer {
            background: var(--gradient);
            color: white;
            padding: 60px 0 30px;
            margin-top: 80px;
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
            margin-bottom: 15px;
            background: linear-gradient(to right, #fff, var(--gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .footer-logo span {
            color: var(--accent);
        }

        .footer-logo p {
            color: #cbd5e0;
            line-height: 1.6;
            font-size: 0.9rem;
        }

        .footer-links h4 {
            font-size: 1.2rem;
            margin-bottom: 25px;
            color: var(--gold);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: transform 0.3s ease;
            font-size: 0.9rem;
        }

        .footer-links li:hover {
            transform: translateX(5px);
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
            margin-top: 25px;
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
            font-size: 1.1rem;
        }

        .social-icon:hover {
            background: var(--accent);
            transform: translateY(-3px) scale(1.1);
            box-shadow: 0 5px 15px rgba(233, 69, 96, 0.4);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #cbd5e0;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .hero h2 {
                font-size: 2.4rem;
            }
            
            .store-banner h3 {
                font-size: 2.2rem;
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
            
            .store-nav ul {
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
            
            .hero h2 {
                font-size: 2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .page-content {
                padding: 25px;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .action-buttons .btn {
                width: 100%;
                max-width: 300px;
            }
            
            .store-banner h3 {
                font-size: 1.8rem;
            }
            
            .store-banner p {
                font-size: 1rem;
            }
        }

        /* Section Header */
        .section-header {
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .section-header h3 {
            font-size: 1.8rem;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .section-header h3 i {
            color: var(--accent);
            font-size: 2.2rem;
        }

        /* Newsletter Signup */
        .newsletter {
            background: var(--gradient);
            padding: 50px 40px;
            border-radius: 20px;
            margin: 40px 0;
            text-align: center;
            color: white;
            border: 3px solid var(--accent);
        }

        .newsletter h3 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: var(--gold);
        }

        .newsletter-form {
            max-width: 500px;
            margin: 30px auto 0;
            display: flex;
            gap: 10px;
        }

        .newsletter-form input {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 30px;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .newsletter-form input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .newsletter-form input:focus {
            outline: none;
            border-color: var(--gold);
            background: rgba(255, 255, 255, 0.15);
        }

        /* New Arrivals Badge */
        .new-arrival {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--gradient-accent);
            color: white;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            z-index: 2;
            transform: rotate(-5deg);
            box-shadow: 0 4px 10px rgba(233, 69, 96, 0.3);
        }

        /* Sale Badge */
        .sale-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--gold);
            color: var(--primary);
            padding: 8px 15px;
            border-radius: 50%;
            font-size: 0.75rem;
            font-weight: 900;
            text-transform: uppercase;
            z-index: 2;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(255, 215, 0, 0.3);
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
                        <p>PREMIUM STREETWEAR & APPAREL</p>
                    </div>
                </div>
            </div>
            
            <div class="nav-section">
                <div class="digital-clock">
                    <i class="fas fa-clock clock-icon"></i>
                    <span id="current-time">00:00:00</span>
                </div>
                
                <nav class="store-nav">
                    <ul>
                        <li><a href="index.php"><i class="fas fa-store"></i> Store</a></li>
                        <li><a href="#collections"><i class="fas fa-tshirt"></i> Collections</a></li>
                        <li><a href="#support"><i class="fas fa-headset"></i> Support</a></li>
                    </ul>
                </nav>
            </div>
            
            <div class="auth-section">
                <button class="btn btn-outline" onclick="window.location.href='login.php'">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
                <button class="btn btn-primary" onclick="window.location.href='register.php'">
                    <i class="fas fa-user-plus"></i> Join Now
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div class="container">
            <!-- Hero Section -->
            <div class="hero">
                <div class="hero-content">
                    <div class="hero-tagline">
                        <i class="fas fa-star"></i> YOUR DAILY CLOTHING STORE <i class="fas fa-star"></i>
                    </div>
                    <h2>WELCOME TO CRUSSADER IO CUSTOMER COMPLAINT PAGE </h2>
                    <p>“Your Satisfaction, Our Priority”</p>
                    <div class="action-buttons">
                        <button class="btn btn-primary" onclick="window.location.href='register.php'">
                            <i class="fas fa-shopping-bag"></i> MAKE COMPLAINTS
                        </button>
                        <button class="btn btn-outline" style="background: rgba(255, 255, 255, 0.1); color: white; border-color: white;" onclick="window.location.href='#collections'">
                            <i class="fas fa-eye"></i> VIEW COLLECTION
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Featured Collections -->
            <div id="collections" class="collections-header">
                <h3>FEATURED COLLECTIONS</h3>
                <p style="color: var(--gray); max-width: 600px; margin: 0 auto; font-size: 1rem;">Limited edition drops and exclusive streetwear</p>
            </div>
            
            <div class="collections-grid">
                <div class="collection-card">
                    <div class="new-arrival">NEW</div>
                    <div class="sale-badge">-30%</div>
                    <div class="collection-image">
                        <i class="fas fa-tshirt"></i>
                    </div>
                    <div class="collection-info">
                        <h4>URBAN WARRIOR HOODIE</h4>
                        <p>Premium cotton blend with embroidered details. Oversized fit for maximum comfort.</p>
                        <div class="collection-price">
                            RM 189.99 <span class="old-price">RM 249.99</span>
                        </div>
                    </div>
                </div>
                
                <div class="collection-card">
                    <div class="new-arrival">HOT</div>
                    <div class="collection-image">
                        <i class="fas fa-hat-cowboy"></i>
                    </div>
                    <div class="collection-info">
                        <h4>CRUSADER CARGO PANTS</h4>
                        <p>Military-inspired cargo pants with multiple pockets. Durable and stylish for urban exploration.</p>
                        <div class="collection-price">
                            RM 159.99
                        </div>
                    </div>
                </div>
                
                <div class="collection-card">
                    <div class="collection-badge">LIMITED</div>
                    <div class="collection-image">
                        <i class="fas fa-shoe-prints"></i>
                    </div>
                    <div class="collection-info">
                        <h4>STREET KING SNEAKERS</h4>
                        <p>High-top sneakers with premium leather and custom sole. Limited to 500 pairs worldwide.</p>
                        <div class="collection-price">
                            RM 299.99
                        </div>
                    </div>
                </div>
                
                <div class="collection-card">
                    <div class="collection-image">
                        <i class="fas fa-glasses"></i>
                    </div>
                    <div class="collection-info">
                        <h4>RETRO SUNGLASSES PACK</h4>
                        <p>Vintage-inspired sunglasses with UV protection. Complete your streetwear look.</p>
                        <div class="collection-price">
                            RM 89.99
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Store Stats -->
            <div class="store-stats">
                <h3>CRUSSADER IMPACT</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="fas fa-truck"></i>
                        <h3>5K+</h3>
                        <p>Orders Delivered</p>
                    </div>
                    
                    <div class="stat-card">
                        <i class="fas fa-heart"></i>
                        <h3>98%</h3>
                        <p>Customer Satisfaction</p>
                    </div>
                    
                    <div class="stat-card">
                        <i class="fas fa-globe-asia"></i>
                        <h3>50+</h3>
                        <p>Cities Served</p>
                    </div>
                    
                    <div class="stat-card">
                        <i class="fas fa-award"></i>
                        <h3>12</h3>
                        <p>Design Awards</p>
                    </div>
                </div>
            </div>
            
            <!-- Complaint System Section -->
            <div id="support" class="complaint-system">
                <h3>CUSTOMER SUPPORT</h3>
                <p>Premium service for premium customers. Our dedicated support team ensures your satisfaction.</p>
                
                <div class="system-features">
                    <div class="feature-card">
                        <i class="fas fa-comment-dots"></i>
                        <h4>24/7 Support</h4>
                        <p>Round-the-clock assistance for all your queries and concerns.</p>
                    </div>
                    
                    <div class="feature-card">
                        <i class="fas fa-history"></i>
                        <h4>Quick Resolution</h4>
                        <p>Fast complaint resolution with real-time status updates.</p>
                    </div>
                    
                    <div class="feature-card">
                        <i class="fas fa-headset"></i>
                        <h4>Live Chat</h4>
                        <p>Instant support through our live chat system.</p>
                    </div>
                    
                    <div class="feature-card">
                        <i class="fas fa-chart-line"></i>
                        <h4>Track Orders</h4>
                        <p>Real-time tracking for all your orders and complaints.</p>
                    </div>
                </div>
                
              
            </div>
            
            <!-- Newsletter -->
            <div class="newsletter">
                <h3>JOIN THE CRUSADE</h3>
                <p style="font-size: 1rem;">Subscribe to get exclusive offers, early access to new drops, and streetwear tips.</p>
                <div class="newsletter-form">
                    <input type="email" placeholder="Enter your email address">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-paper-plane"></i> Subscribe
                    </button>
                </div>
            </div>
            
            <!-- Store Banner -->
            <div class="store-banner">
                <h3>PREMIUM STREETWEAR SINCE 2020</h3>
                <p>Crussader IO brings you the finest streetwear fashion with attention to detail and quality craftsmanship. Our mission is to redefine urban fashion for the modern warrior.</p>
                <button class="btn btn-primary" onclick="window.location.href='register.php'" style="margin: 20px auto 0; padding: 16px 40px; font-size: 1.1rem; display: block;">
                    <i class="fas fa-crown"></i> JOIN THE CRUSADE
                </button>
                <div class="brand-tagline">
                    <i class="fas fa-fire"></i> STREETWEAR REDEFINED <i class="fas fa-fire"></i>
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
                        <a href="https://www.instagram.com/accounts/login/" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="https://web.facebook.com/?_rdc=1&_rdr#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://www.tiktok.com/en/" class="social-icon"><i class="fab fa-tiktok"></i></a>
                        <a href="https://x.com/" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.youtube.com/" class="social-icon"><i class="fab fa-youtube"></i></a>
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
                    <h4>Store Information</h4>
                    <ul>
                        <li><i class="fas fa-store"></i> Crussader IO Flagship Store</li>
                        <li><i class="fas fa-map-marker-alt"></i> Pavilion KL, Kuala Lumpur</li>
                        <li><i class="fas fa-clock"></i> 10AM - 10PM Daily</li>
                        <li><i class="fas fa-truck"></i> Free Shipping above RM 200</li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h4>Contact Info</h4>
                    <ul>
                        <li><i class="fas fa-envelope"></i> support@crussaderio.com</li>
                        <li><i class="fas fa-phone"></i> 1-800-700600</li>
                        
                        <li><i class="fas fa-globe"></i> www.crussaderio.com</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2026 Crussader IO Premium Streetwear. All rights reserved. </p>
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

        // Navigation Active State Management
        function updateActiveNav() {
            const navLinks = document.querySelectorAll('.store-nav a');
            const currentHash = window.location.hash;
            const currentPage = window.location.pathname.split('/').pop();
            
            // Remove active class from all links
            navLinks.forEach(link => {
                link.classList.remove('active');
            });
            
            // Check if we're on a specific section (hash) or on index.php
            if (currentHash) {
                // Find the link that matches the hash
                const activeLink = document.querySelector(`.store-nav a[href="${currentHash}"]`);
                if (activeLink) {
                    activeLink.classList.add('active');
                }
            } else if (currentPage === 'index.php' || currentPage === '') {
                // If we're on the homepage (index.php), activate the Store link
                const storeLink = document.querySelector('.store-nav a[href="index.php"]');
                if (storeLink) {
                    storeLink.classList.add('active');
                }
            }
        }
        
        // Initialize active state on page load
        document.addEventListener('DOMContentLoaded', updateActiveNav);
        
        // Update active state when hash changes (when clicking anchor links)
        window.addEventListener('hashchange', updateActiveNav);
        
        // Also update when page is scrolled (for visibility)
        window.addEventListener('scroll', function() {
            const scrollPosition = window.scrollY + 100;
            const sections = document.querySelectorAll('[id]');
            const navLinks = document.querySelectorAll('.store-nav a');
            
            let currentSection = '';
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                
                if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                    currentSection = '#' + section.id;
                }
            });
            
            // If we're at the top of the page, show Store as active
            if (scrollPosition < 100) {
                currentSection = 'index.php';
            }
            
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === currentSection) {
                    link.classList.add('active');
                }
            });
        });

        // Navigation click handler
        document.querySelectorAll('.store-nav a').forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                
                // If it's an anchor link (starts with #), smooth scroll to it
                if (href.startsWith('#')) {
                    e.preventDefault();
                    const targetId = href.substring(1);
                    const targetElement = document.getElementById(targetId);
                    
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 100,
                            behavior: 'smooth'
                        });
                        
                        // Update URL hash without triggering full page navigation
                        history.pushState(null, null, href);
                        
                        // Update active state
                        document.querySelectorAll('.store-nav a').forEach(navLink => {
                            navLink.classList.remove('active');
                        });
                        this.classList.add('active');
                    }
                }
                // If it's index.php, make it active
                else if (href === 'index.php') {
                    document.querySelectorAll('.store-nav a').forEach(navLink => {
                        navLink.classList.remove('active');
                    });
                    this.classList.add('active');
                }
            });
        });

        // Add animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, observerOptions);

        // Observe elements for animation
        document.querySelectorAll('.collection-card, .feature-card, .stat-card').forEach(el => {
            observer.observe(el);
        });

        // Newsletter form submission
        document.querySelector('.newsletter-form button').addEventListener('click', function() {
            const email = document.querySelector('.newsletter-form input').value;
            if (email) {
                alert('Thank you for subscribing to Crussader IO! You\'ll receive exclusive updates soon.');
                document.querySelector('.newsletter-form input').value = '';
            } else {
                alert('Please enter your email address.');
            }
        });

        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effect to collection cards
            const cards = document.querySelectorAll('.collection-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-10px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
            
            // Update stats with animation
            const statNumbers = document.querySelectorAll('.stat-card h3');
            statNumbers.forEach(stat => {
                const originalText = stat.textContent;
                stat.textContent = '0';
                
                setTimeout(() => {
                    let current = 0;
                    const target = parseInt(originalText);
                    const increment = target / 50;
                    
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= target) {
                            stat.textContent = originalText;
                            clearInterval(timer);
                        } else {
                            stat.textContent = Math.floor(current).toLocaleString();
                        }
                    }, 30);
                }, 1000);
            });
        });
    </script>
</body>
</html>