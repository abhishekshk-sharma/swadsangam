<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swad Sangam - Restaurant Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .hero-section::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: moveBackground 20s linear infinite;
        }
        @keyframes moveBackground {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }
        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
            padding: 40px 20px;
        }
        .hero-logo {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        .hero-logo i {
            font-size: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero-title {
            font-size: 56px;
            font-weight: 700;
            margin-bottom: 20px;
            animation: slideInDown 1s ease;
        }
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .hero-subtitle {
            font-size: 24px;
            margin-bottom: 40px;
            opacity: 0.95;
            animation: slideInUp 1s ease 0.2s both;
        }
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeIn 1s ease 0.4s both;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .btn-hero {
            padding: 16px 40px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary-hero {
            background: white;
            color: #667eea;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }
        .btn-primary-hero:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.3);
            color: #667eea;
        }
        .btn-secondary-hero {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid white;
        }
        .btn-secondary-hero:hover {
            background: white;
            color: #667eea;
            transform: translateY(-4px);
        }
        .services-section {
            padding: 100px 20px;
            background: #f8f9fa;
        }
        .section-title {
            text-align: center;
            font-size: 42px;
            font-weight: 700;
            color: #232f3e;
            margin-bottom: 20px;
        }
        .section-subtitle {
            text-align: center;
            font-size: 18px;
            color: #666;
            margin-bottom: 60px;
        }
        .service-card {
            background: white;
            border-radius: 16px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            height: 100%;
            animation: fadeInUp 0.6s ease both;
        }
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.15);
        }
        .service-card:nth-child(1) { animation-delay: 0.1s; }
        .service-card:nth-child(2) { animation-delay: 0.2s; }
        .service-card:nth-child(3) { animation-delay: 0.3s; }
        .service-card:nth-child(4) { animation-delay: 0.4s; }
        .service-card:nth-child(5) { animation-delay: 0.5s; }
        .service-card:nth-child(6) { animation-delay: 0.6s; }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .service-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 36px;
            color: white;
        }
        .service-title {
            font-size: 22px;
            font-weight: 700;
            color: #232f3e;
            margin-bottom: 12px;
        }
        .service-desc {
            font-size: 15px;
            color: #666;
            line-height: 1.6;
        }
        .footer {
            background: #232f3e;
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .scroll-indicator {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            animation: bounce 2s infinite;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateX(-50%) translateY(0); }
            40% { transform: translateX(-50%) translateY(-10px); }
            60% { transform: translateX(-50%) translateY(-5px); }
        }
        .scroll-indicator i {
            font-size: 32px;
            color: white;
            opacity: 0.8;
        }
        .btn-download-hero {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
            box-shadow: 0 8px 24px rgba(67,233,123,0.4);
        }
        .btn-download-hero:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(67,233,123,0.5);
            color: white;
        }
        .download-badge {
            display: inline-block;
            background: rgba(255,255,255,0.25);
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 8px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <div class="hero-logo">
                <i class="fas fa-utensils"></i>
            </div>
            <h1 class="hero-title">Swad Sangam</h1>
            <p class="hero-subtitle">Complete Restaurant Management System</p>
            <div class="cta-buttons">
                <a href="/login" class="btn-hero btn-primary-hero">
                    <i class="fas fa-sign-in-alt me-2"></i>Staff Login
                </a>
                <a href="/swad-sangam.apk" download="SwadSangam.apk" class="btn-hero btn-download-hero">
                    <i class="fab fa-android me-2"></i>Download App<span class="download-badge">v1.0.0</span>
                </a>
                <a href="#services" class="btn-hero btn-secondary-hero">
                    <i class="fas fa-arrow-down me-2"></i>Explore Services
                </a>
            </div>
        </div>
        <div class="scroll-indicator">
            <i class="fas fa-chevron-down"></i>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services-section">
        <div class="container">
            <h2 class="section-title">Our Services</h2>
            <p class="section-subtitle">Comprehensive solutions for modern restaurant management</p>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fas fa-table"></i>
                        </div>
                        <h3 class="service-title">Table Management</h3>
                        <p class="service-desc">Efficiently manage table reservations, occupancy status, and QR code generation for seamless customer ordering.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <h3 class="service-title">Menu Management</h3>
                        <p class="service-desc">Create, update, and organize your menu items with categories, pricing, and availability controls.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h3 class="service-title">Order Processing</h3>
                        <p class="service-desc">Real-time order tracking from pending to served with status updates for kitchen and waiters.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                            <i class="fas fa-fire-burner"></i>
                        </div>
                        <h3 class="service-title">Kitchen Panel</h3>
                        <p class="service-desc">Dedicated chef interface to manage order preparation, mark items ready, and track cooking status.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                            <i class="fas fa-cash-register"></i>
                        </div>
                        <h3 class="service-title">Payment Processing</h3>
                        <p class="service-desc">Handle payments with multiple methods (cash, UPI, card) and automatic change calculation.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="service-title">Reports & Analytics</h3>
                        <p class="service-desc">Comprehensive reports with date filters, revenue tracking, and Excel export functionality.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <h4 style="margin-bottom: 20px;"><i class="fas fa-utensils me-2"></i>Swad Sangam</h4>
            <p style="opacity: 0.8; margin-bottom: 20px;">Restaurant Management System</p>
            <div style="font-size: 24px; margin-bottom: 20px;">
                <i class="fab fa-facebook me-3"></i>
                <i class="fab fa-instagram me-3"></i>
                <i class="fab fa-twitter me-3"></i>
                <i class="fab fa-youtube"></i>
            </div>
            <p style="opacity: 0.6; font-size: 14px;">&copy; 2024 Swad Sangam. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>
