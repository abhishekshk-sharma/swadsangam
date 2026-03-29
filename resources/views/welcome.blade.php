<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Swad Sangam - Restaurant Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
        :root{--primary:#ff6b35;--secondary:#2563eb;--accent:#f59e0b;--dark:#1a1a2e;--transition:all 0.4s ease;}
        html{scroll-behavior:smooth;}
        body{line-height:1.6;color:#333;background:#f9f9f9;overflow-x:hidden;}
        .container{width:90%;max-width:1200px;margin:0 auto;padding:0 15px;}

        /* HEADER */
        header{background:#fff;box-shadow:0 2px 15px rgba(0,0,0,0.1);position:fixed;width:100%;top:0;z-index:1000;}
        .navbar{display:flex;justify-content:space-between;align-items:center;padding:18px 0;}
        .logo{font-size:22px;font-weight:700;color:var(--primary);text-decoration:none;display:flex;align-items:center;gap:8px;}
        .logo span{color:var(--secondary);}
        .nav-links{display:flex;list-style:none;align-items:center;gap:28px;}
        .nav-links a{text-decoration:none;color:#333;font-weight:500;position:relative;padding:5px 0;transition:var(--transition);}
        .nav-links a::after{content:'';position:absolute;width:0;height:2px;bottom:0;left:0;background:var(--primary);transition:var(--transition);}
        .nav-links a:hover::after{width:100%;}
        .nav-links a:hover{color:var(--primary);}
        .nav-login{background:var(--primary) !important;color:#fff !important;padding:8px 20px !important;border-radius:25px;}
        .nav-login::after{display:none !important;}
        .nav-login:hover{background:var(--secondary) !important;transform:translateY(-2px);box-shadow:0 5px 15px rgba(255,107,53,0.4);}
        .hamburger{display:none;cursor:pointer;}
        .bar{display:block;width:25px;height:3px;margin:5px auto;background:#333;transition:var(--transition);}

        /* HERO */
        .hero{padding:140px 0 100px;text-align:center;background:linear-gradient(135deg,#fff5f0 0%,#fff 50%,#eff6ff 100%);}
        .hero-badge{display:inline-block;background:#fff5f0;color:var(--primary);border:1px solid #ffd5c2;padding:6px 18px;border-radius:20px;font-size:13px;font-weight:600;margin-bottom:20px;}
        .hero h1{font-size:3rem;margin-bottom:20px;color:var(--dark);line-height:1.2;}
        .hero h1 span{color:var(--primary);}
        .hero p{font-size:1.1rem;max-width:650px;margin:0 auto 36px;color:#555;}
        .hero-btns{display:flex;gap:16px;justify-content:center;flex-wrap:wrap;}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:13px 28px;border-radius:8px;font-weight:600;font-size:15px;text-decoration:none;transition:var(--transition);cursor:pointer;border:none;}
        .btn-primary{background:var(--primary);color:#fff;background-image:linear-gradient(to right,var(--primary) 0%,#ff8c42 100%);background-size:200% 100%;background-position:right bottom;}
        .btn-primary:hover{background-position:left bottom;transform:translateY(-3px);box-shadow:0 10px 20px rgba(255,107,53,0.3);}
        .btn-outline{background:#fff;color:var(--secondary);border:2px solid var(--secondary);}
        .btn-outline:hover{background:var(--secondary);color:#fff;transform:translateY(-3px);}
        .btn-green{background:linear-gradient(135deg,#16a34a,#22c55e);color:#fff;}
        .btn-green:hover{transform:translateY(-3px);box-shadow:0 10px 20px rgba(22,163,74,0.3);}
        .hero-stats{display:flex;justify-content:center;gap:48px;margin-top:60px;flex-wrap:wrap;}
        .stat-num{font-size:2rem;font-weight:700;color:var(--primary);}
        .stat-label{font-size:13px;color:#777;margin-top:2px;}

        /* SECTIONS */
        section{padding:100px 0;}
        .section-title{text-align:center;margin-bottom:60px;}
        .section-title h2{font-size:2.4rem;color:var(--dark);position:relative;display:inline-block;padding-bottom:15px;}
        .section-title h2::after{content:'';position:absolute;width:60px;height:3px;background:var(--primary);bottom:0;left:50%;transform:translateX(-50%);}
        .section-title p{color:#666;margin-top:14px;font-size:1rem;max-width:600px;margin-left:auto;margin-right:auto;}

        /* FEATURES */
        .features{background:#fff;}
        .features-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:30px;}
        .feature-card{background:#fff;padding:32px;border-radius:12px;box-shadow:0 5px 20px rgba(0,0,0,0.06);text-align:center;transition:var(--transition);border:1px solid #f0f0f0;}
        .feature-card:hover{transform:translateY(-10px);box-shadow:0 15px 30px rgba(0,0,0,0.12);}
        .feature-icon{width:70px;height:70px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:28px;}
        .feature-card h3{margin-bottom:12px;font-size:1.2rem;color:var(--dark);}
        .feature-card p{color:#666;font-size:14px;}

        /* ROLES */
        .roles{background:linear-gradient(135deg,#fff5f0 0%,#eff6ff 100%);}
        .roles-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:20px;}
        .role-card{background:#fff;padding:28px 20px;border-radius:12px;text-align:center;box-shadow:0 4px 15px rgba(0,0,0,0.06);transition:var(--transition);position:relative;overflow:hidden;}
        .role-card::before{content:'';position:absolute;top:0;left:0;width:100%;height:4px;transform:scaleX(0);transform-origin:left;transition:transform 0.4s ease;}
        .role-card:hover::before{transform:scaleX(1);}
        .role-card:hover{transform:translateY(-5px);box-shadow:0 12px 25px rgba(0,0,0,0.1);}
        .role-card i{font-size:36px;margin-bottom:14px;display:block;}
        .role-card h3{font-size:1rem;font-weight:700;color:var(--dark);margin-bottom:8px;}
        .role-card p{font-size:12px;color:#777;}

        /* APP SECTION */
        .app-section{background:#fff;}
        .app-grid{display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;}
        .app-content h2{font-size:2.2rem;color:var(--dark);margin-bottom:16px;line-height:1.3;}
        .app-content h2 span{color:var(--primary);}
        .app-content p{color:#555;margin-bottom:14px;font-size:15px;}
        .app-features-list{list-style:none;margin:20px 0 30px;}
        .app-features-list li{display:flex;align-items:center;gap:10px;margin-bottom:10px;font-size:14px;color:#444;}
        .app-features-list li i{color:#16a34a;font-size:16px;}
        .app-mockup{background:linear-gradient(135deg,var(--primary),var(--secondary));border-radius:20px;padding:50px;display:flex;align-items:center;justify-content:center;min-height:340px;box-shadow:0 20px 50px rgba(255,107,53,0.25);}
        .app-mockup i{font-size:120px;color:rgba(255,255,255,0.25);}

        /* SERVICES */
        .services{background:linear-gradient(135deg,#fff5f0 0%,#eff6ff 100%);}
        .services-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:28px;}
        .service-card{background:#fff;padding:36px 28px;border-radius:12px;box-shadow:0 5px 20px rgba(0,0,0,0.06);text-align:center;transition:var(--transition);position:relative;overflow:hidden;}
        .service-card::before{content:'';position:absolute;top:0;left:0;width:100%;height:4px;background:linear-gradient(to right,var(--primary),var(--secondary));transform:scaleX(0);transform-origin:left;transition:transform 0.5s ease;}
        .service-card:hover::before{transform:scaleX(1);}
        .service-card:hover{transform:translateY(-5px);box-shadow:0 15px 30px rgba(0,0,0,0.1);}
        .service-icon{font-size:46px;margin-bottom:18px;color:var(--primary);}
        .service-card h3{margin-bottom:12px;font-size:1.2rem;color:var(--dark);}
        .service-card p{color:#666;font-size:14px;}

        /* FOOTER */
        footer{background:var(--dark);color:#fff;padding:60px 0 30px;}
        .footer-content{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:40px;margin-bottom:40px;}
        .footer-section h3{font-size:1.2rem;margin-bottom:18px;position:relative;padding-bottom:10px;}
        .footer-section h3::after{content:'';position:absolute;width:36px;height:2px;background:var(--primary);bottom:0;left:0;}
        .footer-links{list-style:none;}
        .footer-links li{margin-bottom:10px;}
        .footer-links a{color:#ccc;text-decoration:none;transition:var(--transition);display:inline-block;position:relative;}
        .footer-links a::before{content:'→';position:absolute;left:-20px;opacity:0;transition:var(--transition);}
        .footer-links a:hover{color:#fff;padding-left:20px;}
        .footer-links a:hover::before{opacity:1;left:0;}
        .social-links{display:flex;gap:12px;margin-top:18px;}
        .social-links a{display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;background:rgba(255,255,255,0.1);color:#fff;border-radius:50%;text-decoration:none;transition:var(--transition);}
        .social-links a:hover{background:var(--primary);transform:translateY(-3px);}
        .footer-bottom{text-align:center;padding-top:28px;border-top:1px solid #333;color:#aaa;font-size:13px;}

        .hidden{opacity:0;transform:translateY(40px);transition:all 0.8s ease;}
        .show{opacity:1;transform:translateY(0);}

        @media(max-width:968px){.app-grid{grid-template-columns:1fr;}}
        @media(max-width:768px){
            .hamburger{display:block;}
            .hamburger.active .bar:nth-child(2){opacity:0;}
            .hamburger.active .bar:nth-child(1){transform:translateY(8px) rotate(45deg);}
            .hamburger.active .bar:nth-child(3){transform:translateY(-8px) rotate(-45deg);}
            .nav-links{position:fixed;left:-100%;top:68px;flex-direction:column;background:#fff;width:100%;text-align:center;transition:0.3s;box-shadow:0 10px 15px rgba(0,0,0,0.1);padding:20px 0;gap:0;}
            .nav-links.active{left:0;}
            .nav-links li{margin:12px 0;}
            .hero h1{font-size:2.2rem;}
            .hero-stats{gap:28px;}
        }
    </style>
</head>
<body>

<header>
    <div class="container">
        <nav class="navbar">
            <a href="/" class="logo"><i class="fas fa-utensils" style="color:var(--accent);"></i>Swad <span>Sangam</span></a>
            <ul class="nav-links" id="navLinks">
                <li><a href="/" style="color:var(--primary);">Home</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#app">Mobile App</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="/app-download">Download App</a></li>
                <li><a href="/login" class="nav-login"><i class="fas fa-sign-in-alt"></i> Staff Login</a></li>
            </ul>
            <div class="hamburger" id="hamburger">
                <span class="bar"></span><span class="bar"></span><span class="bar"></span>
            </div>
        </nav>
    </div>
</header>

<section class="hero">
    <div class="container">
        <div class="hero-badge hidden"><i class="fas fa-star" style="margin-right:6px;color:var(--accent);"></i>Complete Restaurant Management Platform</div>
        <h1 class="hidden">Smart Restaurant Management<br>with <span>Swad Sangam</span></h1>
        <p class="hidden">From QR-based ordering to real-time kitchen operations, payments, and analytics — everything your restaurant needs in one powerful platform.</p>
        <div class="hero-btns hidden">
            <a href="/login" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Staff Login</a>
            <a href="/app-download" class="btn btn-green"><i class="fab fa-android"></i> Download App</a>
            <a href="#features" class="btn btn-outline"><i class="fas fa-arrow-down"></i> Explore</a>
        </div>
        <div class="hero-stats hidden">
            <div class="stat"><div class="stat-num">6+</div><div class="stat-label">Staff Roles</div></div>
            <div class="stat"><div class="stat-num">QR</div><div class="stat-label">Table Ordering</div></div>
            <div class="stat"><div class="stat-num">Live</div><div class="stat-label">Kitchen Updates</div></div>
            <div class="stat"><div class="stat-num">GST</div><div class="stat-label">Billing Ready</div></div>
        </div>
    </div>
</section>

<section class="features" id="features">
    <div class="container">
        <div class="section-title hidden">
            <h2>Why Choose Swad Sangam</h2>
            <p>A complete ecosystem built for modern restaurants — from single outlets to multi-branch chains.</p>
        </div>
        <div class="features-grid">
            <div class="feature-card hidden">
                <div class="feature-icon" style="background:#fff5f0;color:var(--primary);"><i class="fas fa-qrcode"></i></div>
                <h3>QR Code Ordering</h3>
                <p>Customers scan the table QR, browse the menu and place orders directly — no app install needed.</p>
            </div>
            <div class="feature-card hidden">
                <div class="feature-icon" style="background:#eff6ff;color:var(--secondary);"><i class="fas fa-bolt"></i></div>
                <h3>Real-Time Updates</h3>
                <p>Orders flow instantly from table to kitchen. Status updates reach waiters and customers live.</p>
            </div>
            <div class="feature-card hidden">
                <div class="feature-icon" style="background:#f0fdf4;color:#16a34a;"><i class="fas fa-store"></i></div>
                <h3>Multi-Branch Support</h3>
                <p>Manage multiple branches under one account with isolated data and staff per branch.</p>
            </div>
            <div class="feature-card hidden">
                <div class="feature-icon" style="background:#fefce8;color:#ca8a04;"><i class="fas fa-percent"></i></div>
                <h3>GST Billing</h3>
                <p>Configurable GST slabs with included/excluded modes. Auto-calculated CGST & SGST breakdown.</p>
            </div>
            <div class="feature-card hidden">
                <div class="feature-icon" style="background:#fdf4ff;color:#9333ea;"><i class="fas fa-chart-bar"></i></div>
                <h3>Reports & Analytics</h3>
                <p>Daily, monthly and custom date range reports with Excel export for revenue tracking.</p>
            </div>
            <div class="feature-card hidden">
                <div class="feature-icon" style="background:#fff5f0;color:var(--primary);"><i class="fas fa-mobile-alt"></i></div>
                <h3>Mobile App</h3>
                <p>Native Android app for cashiers, chefs and waiters with real-time order management.</p>
            </div>
        </div>
    </div>
</section>

<section class="roles" id="roles">
    <div class="container">
        <div class="section-title hidden">
            <h2>Built for Every Role</h2>
            <p>Dedicated panels for each team member — everyone sees exactly what they need.</p>
        </div>
        <div class="roles-grid">
            <div class="role-card hidden" style="border-top:4px solid #9333ea;">
                <i class="fas fa-user-shield" style="color:#9333ea;"></i>
                <h3>Admin</h3><p>Full control over menu, staff, tables, reports & settings</p>
            </div>
            <div class="role-card hidden" style="border-top:4px solid #2563eb;">
                <i class="fas fa-user-tie" style="color:#2563eb;"></i>
                <h3>Manager</h3><p>Branch-level operations, orders, staff & reports</p>
            </div>
            <div class="role-card hidden" style="border-top:4px solid #ea580c;">
                <i class="fas fa-concierge-bell" style="color:#ea580c;"></i>
                <h3>Waiter</h3><p>Create & manage orders, assign tables, serve customers</p>
            </div>
            <div class="role-card hidden" style="border-top:4px solid #dc2626;">
                <i class="fas fa-fire-burner" style="color:#dc2626;"></i>
                <h3>Chef</h3><p>Kitchen queue, mark items ready, track preparation</p>
            </div>
            <div class="role-card hidden" style="border-top:4px solid #16a34a;">
                <i class="fas fa-cash-register" style="color:#16a34a;"></i>
                <h3>Cashier</h3><p>Process payments, parcels, cash handover reports</p>
            </div>
            <div class="role-card hidden" style="border-top:4px solid #0891b2;">
                <i class="fas fa-mobile-alt" style="color:#0891b2;"></i>
                <h3>Customer</h3><p>Scan QR, browse menu, order & track in real time</p>
            </div>
        </div>
    </div>
</section>

<section class="app-section" id="app">
    <div class="container">
        <div class="app-grid">
            <div class="app-content hidden">
                <h2>Manage Everything from Your <span>Phone</span></h2>
                <p>The Swad Sangam Android app brings the full power of the platform to your pocket. Designed for speed and simplicity on the restaurant floor.</p>
                <ul class="app-features-list">
                    <li><i class="fas fa-check-circle"></i> Waiter — create orders, add items, serve tables</li>
                    <li><i class="fas fa-check-circle"></i> Chef — view kitchen queue, mark orders ready</li>
                    <li><i class="fas fa-check-circle"></i> Cashier — process payments, manage parcels</li>
                    <li><i class="fas fa-check-circle"></i> Real-time polling for instant order updates</li>
                    <li><i class="fas fa-check-circle"></i> Bill QR code generation after payment</li>
                    <li><i class="fas fa-check-circle"></i> Secure login with role-based access</li>
                </ul>
                <div style="display:flex;gap:12px;flex-wrap:wrap;">
                    <a href="/app-download" class="btn btn-green"><i class="fab fa-android"></i> Download APK</a>
                    <a href="/app-download#features" class="btn btn-outline"><i class="fas fa-info-circle"></i> See All Features</a>
                </div>
            </div>
            <div class="hidden">
                <div class="app-mockup">
                    <i class="fas fa-mobile-alt"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="services" id="services">
    <div class="container">
        <div class="section-title hidden">
            <h2>Platform Services</h2>
            <p>Everything you need to run a modern, efficient restaurant operation.</p>
        </div>
        <div class="services-grid">
            <div class="service-card hidden">
                <div class="service-icon"><i class="fas fa-utensils"></i></div>
                <h3>Table Management</h3>
                <p>Create tables, generate QR codes, track occupancy and capacity in real time.</p>
            </div>
            <div class="service-card hidden">
                <div class="service-icon"><i class="fas fa-book-open"></i></div>
                <h3>Menu Management</h3>
                <p>Add items with images, categories, pricing. Import via Excel or OCR photo scan.</p>
            </div>
            <div class="service-card hidden">
                <div class="service-icon"><i class="fas fa-receipt"></i></div>
                <h3>Order Processing</h3>
                <p>Full order lifecycle from pending to served with real-time status for all roles.</p>
            </div>
            <div class="service-card hidden">
                <div class="service-icon"><i class="fas fa-fire"></i></div>
                <h3>Kitchen Display</h3>
                <p>Dedicated chef panel with order queue, preparation tracking and ready notifications.</p>
            </div>
            <div class="service-card hidden">
                <div class="service-icon"><i class="fas fa-rupee-sign"></i></div>
                <h3>Payment Processing</h3>
                <p>Cash and UPI payments with QR generation, change calculation and bill printing.</p>
            </div>
            <div class="service-card hidden">
                <div class="service-icon"><i class="fas fa-exchange-alt"></i></div>
                <h3>Cash Handover</h3>
                <p>Cashier submits denomination-wise cash count. Admin approves with full audit trail.</p>
            </div>
        </div>
    </div>
</section>

<footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-section hidden">
                <h3><i class="fas fa-utensils" style="margin-right:8px;color:var(--primary);"></i>Swad Sangam</h3>
                <p style="color:#ccc;font-size:14px;margin-bottom:12px;">Complete restaurant management system for modern food businesses.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-section hidden">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="/">Home</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#app">Mobile App</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="/app-download">Download App</a></li>
                </ul>
            </div>
            <div class="footer-section hidden">
                <h3>Staff Access</h3>
                <ul class="footer-links">
                    <li><a href="/login">Staff Login</a></li>
                    <li><a href="/superadmin/login">Super Admin</a></li>
                    <li><a href="/app-download">Android App</a></li>
                </ul>
            </div>
            <div class="footer-section hidden">
                <h3>Contact</h3>
                <p style="color:#ccc;font-size:14px;margin-bottom:8px;"><i class="fas fa-globe" style="margin-right:8px;color:var(--primary);"></i>swadsangam.store</p>
                <p style="color:#ccc;font-size:14px;"><i class="fas fa-envelope" style="margin-right:8px;color:var(--primary);"></i>support@swadsangam.store</p>
            </div>
        </div>
        <div class="footer-bottom hidden">
            <p>&copy; {{ date('Y') }} Swad Sangam. All rights reserved. Built with ❤️ for restaurants.</p>
        </div>
    </div>
</footer>

<script>
const hamburger = document.getElementById('hamburger');
const navLinks  = document.getElementById('navLinks');
hamburger.addEventListener('click', () => { hamburger.classList.toggle('active'); navLinks.classList.toggle('active'); });
document.querySelectorAll('.nav-links a').forEach(n => n.addEventListener('click', () => { hamburger.classList.remove('active'); navLinks.classList.remove('active'); }));
const observer = new IntersectionObserver(entries => { entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('show'); }); }, { threshold: 0.1 });
document.querySelectorAll('.hidden').forEach(el => observer.observe(el));
</script>
</body>
</html>
