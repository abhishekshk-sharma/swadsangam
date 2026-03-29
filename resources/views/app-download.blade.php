<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Download Swad Sangam App</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
        :root{--primary:#ff6b35;--secondary:#2563eb;--accent:#f59e0b;--dark:#1a1a2e;--green:#16a34a;--transition:all 0.4s ease;}
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
        .nav-login:hover{background:var(--secondary) !important;}
        .hamburger{display:none;cursor:pointer;}
        .bar{display:block;width:25px;height:3px;margin:5px auto;background:#333;transition:var(--transition);}

        /* HERO */
        .hero{padding:130px 0 80px;background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%);color:#fff;text-align:center;}
        .hero-badge{display:inline-block;background:rgba(255,107,53,0.2);color:#ff8c42;border:1px solid rgba(255,107,53,0.4);padding:6px 18px;border-radius:20px;font-size:13px;font-weight:600;margin-bottom:20px;}
        .hero h1{font-size:3rem;margin-bottom:16px;line-height:1.2;}
        .hero h1 span{color:var(--primary);}
        .hero p{font-size:1.1rem;max-width:600px;margin:0 auto 36px;color:#ccc;}
        .download-btn{display:inline-flex;align-items:center;gap:12px;background:linear-gradient(135deg,var(--green),#22c55e);color:#fff;padding:18px 40px;border-radius:12px;font-size:18px;font-weight:700;text-decoration:none;transition:var(--transition);box-shadow:0 8px 24px rgba(22,163,74,0.4);}
        .download-btn:hover{transform:translateY(-4px);box-shadow:0 14px 32px rgba(22,163,74,0.5);}
        .download-btn i{font-size:28px;}
        .version-info{margin-top:16px;font-size:13px;color:#aaa;}
        .hero-features{display:flex;justify-content:center;gap:32px;margin-top:48px;flex-wrap:wrap;}
        .hero-feat{display:flex;align-items:center;gap:8px;font-size:14px;color:#ccc;}
        .hero-feat i{color:var(--accent);}

        /* SECTIONS */
        section{padding:90px 0;}
        .section-title{text-align:center;margin-bottom:56px;}
        .section-title h2{font-size:2.3rem;color:var(--dark);position:relative;display:inline-block;padding-bottom:14px;}
        .section-title h2::after{content:'';position:absolute;width:56px;height:3px;background:var(--primary);bottom:0;left:50%;transform:translateX(-50%);}
        .section-title p{color:#666;margin-top:12px;font-size:1rem;max-width:580px;margin-left:auto;margin-right:auto;}

        /* ROLES */
        .roles-section{background:#fff;}
        .roles-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:28px;}
        .role-card{border-radius:14px;overflow:hidden;box-shadow:0 5px 20px rgba(0,0,0,0.08);transition:var(--transition);}
        .role-card:hover{transform:translateY(-8px);box-shadow:0 15px 35px rgba(0,0,0,0.14);}
        .role-header{padding:24px;display:flex;align-items:center;gap:16px;}
        .role-icon{width:56px;height:56px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:24px;color:#fff;flex-shrink:0;}
        .role-header h3{font-size:1.2rem;font-weight:700;color:#fff;margin-bottom:2px;}
        .role-header p{font-size:12px;color:rgba(255,255,255,0.8);}
        .role-body{background:#fff;padding:20px 24px;}
        .role-features{list-style:none;}
        .role-features li{display:flex;align-items:flex-start;gap:10px;padding:8px 0;border-bottom:1px solid #f3f4f6;font-size:13px;color:#444;}
        .role-features li:last-child{border-bottom:none;}
        .role-features li i{margin-top:2px;flex-shrink:0;}

        /* TECH */
        .tech-section{background:linear-gradient(135deg,#fff5f0 0%,#eff6ff 100%);}
        .tech-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;}
        .tech-card{background:#fff;padding:28px 20px;border-radius:12px;text-align:center;box-shadow:0 4px 15px rgba(0,0,0,0.06);transition:var(--transition);}
        .tech-card:hover{transform:translateY(-5px);box-shadow:0 12px 25px rgba(0,0,0,0.1);}
        .tech-card i{font-size:36px;margin-bottom:12px;display:block;}
        .tech-card h3{font-size:1rem;font-weight:700;color:var(--dark);margin-bottom:6px;}
        .tech-card p{font-size:12px;color:#777;}

        /* INSTALL */
        .install-section{background:#fff;}
        .steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:24px;margin-bottom:48px;}
        .step{text-align:center;padding:28px 20px;}
        .step-num{width:52px;height:52px;border-radius:50%;background:var(--primary);color:#fff;font-size:20px;font-weight:700;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;}
        .step h3{font-size:1rem;font-weight:700;color:var(--dark);margin-bottom:8px;}
        .step p{font-size:13px;color:#666;}
        .install-note{background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:20px 24px;display:flex;align-items:flex-start;gap:14px;max-width:700px;margin:0 auto;}
        .install-note i{color:var(--accent);font-size:20px;margin-top:2px;flex-shrink:0;}
        .install-note p{font-size:14px;color:#92400e;}

        /* CTA */
        .cta-section{background:linear-gradient(135deg,var(--dark),#0f3460);color:#fff;text-align:center;padding:80px 0;}
        .cta-section h2{font-size:2.4rem;margin-bottom:16px;}
        .cta-section p{font-size:1.1rem;color:#ccc;margin-bottom:36px;max-width:560px;margin-left:auto;margin-right:auto;}

        /* FOOTER */
        footer{background:var(--dark);color:#fff;padding:40px 0 24px;}
        .footer-bottom{text-align:center;color:#aaa;font-size:13px;}
        .footer-links-row{display:flex;justify-content:center;gap:24px;margin-bottom:16px;flex-wrap:wrap;}
        .footer-links-row a{color:#ccc;text-decoration:none;font-size:13px;transition:var(--transition);}
        .footer-links-row a:hover{color:var(--primary);}

        .hidden{opacity:0;transform:translateY(40px);transition:all 0.8s ease;}
        .show{opacity:1;transform:translateY(0);}

        @media(max-width:768px){
            .hamburger{display:block;}
            .hamburger.active .bar:nth-child(2){opacity:0;}
            .hamburger.active .bar:nth-child(1){transform:translateY(8px) rotate(45deg);}
            .hamburger.active .bar:nth-child(3){transform:translateY(-8px) rotate(-45deg);}
            .nav-links{position:fixed;left:-100%;top:68px;flex-direction:column;background:#fff;width:100%;text-align:center;transition:0.3s;box-shadow:0 10px 15px rgba(0,0,0,0.1);padding:20px 0;gap:0;}
            .nav-links.active{left:0;}
            .nav-links li{margin:12px 0;}
            .hero h1{font-size:2rem;}
        }
    </style>
</head>
<body>

<header>
    <div class="container">
        <nav class="navbar">
            <a href="/" class="logo"><i class="fas fa-utensils" style="color:var(--accent);"></i>Swad <span>Sangam</span></a>
            <ul class="nav-links" id="navLinks">
                <li><a href="/">Home</a></li>
                <li><a href="/#features">Features</a></li>
                <li><a href="/#services">Services</a></li>
                <li><a href="/app-download" style="color:var(--primary);">Download App</a></li>
                <li><a href="/login" class="nav-login"><i class="fas fa-sign-in-alt"></i> Staff Login</a></li>
            </ul>
            <div class="hamburger" id="hamburger">
                <span class="bar"></span><span class="bar"></span><span class="bar"></span>
            </div>
        </nav>
    </div>
</header>

<!-- HERO -->
<section class="hero">
    <div class="container">
        <div class="hero-badge hidden"><i class="fab fa-android" style="margin-right:6px;"></i>Android App — Free Download</div>
        <h1 class="hidden">Swad Sangam<br><span>Mobile App</span></h1>
        <p class="hidden">The complete restaurant management app for your team. Waiters, chefs and cashiers — all in one powerful Android app.</p>
        <a href="/swad_sangam.apk" download="SwadSangam.apk" class="download-btn hidden">
            <i class="fab fa-android"></i>
            <div style="text-align:left;">
                <div style="font-size:12px;font-weight:400;opacity:0.85;">Download for Android</div>
                <div>Get the APK</div>
            </div>
        </a>
        <div class="version-info hidden">v1.0.0 · Free · No account needed to download</div>
        <div class="hero-features hidden">
            <div class="hero-feat"><i class="fas fa-check-circle"></i> Waiter Panel</div>
            <div class="hero-feat"><i class="fas fa-check-circle"></i> Chef Panel</div>
            <div class="hero-feat"><i class="fas fa-check-circle"></i> Cashier Panel</div>
            <div class="hero-feat"><i class="fas fa-check-circle"></i> Real-time Orders</div>
            <div class="hero-feat"><i class="fas fa-check-circle"></i> Bill QR Code</div>
        </div>
    </div>
</section>

<!-- ROLE FEATURES -->
<section class="roles-section" id="features">
    <div class="container">
        <div class="section-title hidden">
            <h2>Features by Role</h2>
            <p>Each role gets a dedicated, focused experience built for their specific job on the restaurant floor.</p>
        </div>
        <div class="roles-grid">

            {{-- Waiter --}}
            <div class="role-card hidden">
                <div class="role-header" style="background:linear-gradient(135deg,#ea580c,#f97316);">
                    <div class="role-icon" style="background:rgba(255,255,255,0.2);"><i class="fas fa-concierge-bell"></i></div>
                    <div><h3>Waiter</h3><p>Order management on the floor</p></div>
                </div>
                <div class="role-body">
                    <ul class="role-features">
                        <li><i class="fas fa-check" style="color:#ea580c;"></i>View all active orders for today</li>
                        <li><i class="fas fa-check" style="color:#ea580c;"></i>Create new dine-in orders by selecting table</li>
                        <li><i class="fas fa-check" style="color:#ea580c;"></i>Add items to existing orders anytime</li>
                        <li><i class="fas fa-check" style="color:#ea580c;"></i>Cancel individual items or full orders</li>
                        <li><i class="fas fa-check" style="color:#ea580c;"></i>Mark orders as served to customer</li>
                        <li><i class="fas fa-check" style="color:#ea580c;"></i>Checkout orders for payment processing</li>
                        <li><i class="fas fa-check" style="color:#ea580c;"></i>Browse full menu with categories & search</li>
                        <li><i class="fas fa-check" style="color:#ea580c;"></i>View table availability in real time</li>
                    </ul>
                </div>
            </div>

            {{-- Chef --}}
            <div class="role-card hidden">
                <div class="role-header" style="background:linear-gradient(135deg,#dc2626,#ef4444);">
                    <div class="role-icon" style="background:rgba(255,255,255,0.2);"><i class="fas fa-fire-burner"></i></div>
                    <div><h3>Chef</h3><p>Kitchen operations & queue</p></div>
                </div>
                <div class="role-body">
                    <ul class="role-features">
                        <li><i class="fas fa-check" style="color:#dc2626;"></i>View all pending orders in kitchen queue</li>
                        <li><i class="fas fa-check" style="color:#dc2626;"></i>See order items with quantities & notes</li>
                        <li><i class="fas fa-check" style="color:#dc2626;"></i>Update individual item status (preparing/prepared)</li>
                        <li><i class="fas fa-check" style="color:#dc2626;"></i>Mark entire order as ready for service</li>
                        <li><i class="fas fa-check" style="color:#dc2626;"></i>View completed orders history</li>
                        <li><i class="fas fa-check" style="color:#dc2626;"></i>Cancel items or orders when needed</li>
                        <li><i class="fas fa-check" style="color:#dc2626;"></i>Real-time new order notifications</li>
                    </ul>
                </div>
            </div>

            {{-- Cashier --}}
            <div class="role-card hidden">
                <div class="role-header" style="background:linear-gradient(135deg,#16a34a,#22c55e);">
                    <div class="role-icon" style="background:rgba(255,255,255,0.2);"><i class="fas fa-cash-register"></i></div>
                    <div><h3>Cashier</h3><p>Payments & parcel management</p></div>
                </div>
                <div class="role-body">
                    <ul class="role-features">
                        <li><i class="fas fa-check" style="color:#16a34a;"></i>View all orders pending payment</li>
                        <li><i class="fas fa-check" style="color:#16a34a;"></i>Process cash payments with change calculation</li>
                        <li><i class="fas fa-check" style="color:#16a34a;"></i>Process UPI payments with QR code display</li>
                        <li><i class="fas fa-check" style="color:#16a34a;"></i>Generate bill QR code after payment</li>
                        <li><i class="fas fa-check" style="color:#16a34a;"></i>View payment history for today</li>
                        <li><i class="fas fa-check" style="color:#16a34a;"></i>Create & manage parcel (takeaway) orders</li>
                        <li><i class="fas fa-check" style="color:#16a34a;"></i>Add items to parcel orders</li>
                        <li><i class="fas fa-check" style="color:#16a34a;"></i>Cancel parcel orders or individual items</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- TECH STACK -->
<section class="tech-section">
    <div class="container">
        <div class="section-title hidden">
            <h2>Built with Modern Technology</h2>
            <p>The app is built with Flutter for a smooth, native Android experience.</p>
        </div>
        <div class="tech-grid">
            <div class="tech-card hidden">
                <i class="fab fa-flutter" style="color:#54c5f8;"></i>
                <h3>Flutter</h3>
                <p>Native Android performance with beautiful Material Design UI</p>
            </div>
            <div class="tech-card hidden">
                <i class="fas fa-bolt" style="color:var(--accent);"></i>
                <h3>Real-Time Polling</h3>
                <p>Orders update automatically — no manual refresh needed</p>
            </div>
            <div class="tech-card hidden">
                <i class="fas fa-lock" style="color:var(--secondary);"></i>
                <h3>Secure Auth</h3>
                <p>Token-based authentication with Laravel Sanctum</p>
            </div>
            <div class="tech-card hidden">
                <i class="fas fa-qrcode" style="color:var(--primary);"></i>
                <h3>QR Bill</h3>
                <p>Generate and display bill QR codes instantly after payment</p>
            </div>
            <div class="tech-card hidden">
                <i class="fas fa-mobile-alt" style="color:#16a34a;"></i>
                <h3>Offline Ready</h3>
                <p>Secure local storage for session persistence</p>
            </div>
            <div class="tech-card hidden">
                <i class="fas fa-user-shield" style="color:#9333ea;"></i>
                <h3>Role-Based</h3>
                <p>Each role sees only their relevant screens and data</p>
            </div>
        </div>
    </div>
</section>

<!-- INSTALL STEPS -->
<section class="install-section">
    <div class="container">
        <div class="section-title hidden">
            <h2>How to Install</h2>
            <p>Install the Swad Sangam app on your Android device in 4 simple steps.</p>
        </div>
        <div class="steps">
            <div class="step hidden">
                <div class="step-num">1</div>
                <h3>Download APK</h3>
                <p>Tap the download button and save the APK file to your Android device.</p>
            </div>
            <div class="step hidden">
                <div class="step-num">2</div>
                <h3>Allow Unknown Sources</h3>
                <p>Go to Settings → Security → enable "Install from Unknown Sources" or "Install Unknown Apps".</p>
            </div>
            <div class="step hidden">
                <div class="step-num">3</div>
                <h3>Open the APK</h3>
                <p>Find the downloaded file in your Downloads folder and tap to install.</p>
            </div>
            <div class="step hidden">
                <div class="step-num">4</div>
                <h3>Login & Start</h3>
                <p>Open the app, enter your staff credentials and start managing orders.</p>
            </div>
        </div>
        <div class="install-note hidden">
            <i class="fas fa-info-circle"></i>
            <p><strong>Note:</strong> You need a Swad Sangam staff account to use the app. Contact your restaurant admin for login credentials. The app works with your existing web panel account.</p>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container">
        <h2 class="hidden">Ready to Get Started?</h2>
        <p class="hidden">Download the app now and bring your restaurant management to the next level.</p>
        <a href="/swad_sangam.apk" download="SwadSangam.apk" class="download-btn hidden" style="display:inline-flex;">
            <i class="fab fa-android"></i>
            <div style="text-align:left;">
                <div style="font-size:12px;font-weight:400;opacity:0.85;">Free Download</div>
                <div>Download APK Now</div>
            </div>
        </a>
        <div style="margin-top:20px;" class="hidden">
            <a href="/login" style="color:#aaa;font-size:14px;text-decoration:none;margin-right:20px;"><i class="fas fa-desktop" style="margin-right:6px;"></i>Use Web Panel Instead</a>
            <a href="/" style="color:#aaa;font-size:14px;text-decoration:none;"><i class="fas fa-home" style="margin-right:6px;"></i>Back to Home</a>
        </div>
    </div>
</section>

<footer>
    <div class="container">
        <div class="footer-links-row">
            <a href="/">Home</a>
            <a href="/app-download">Download App</a>
            <a href="/login">Staff Login</a>
            <a href="/superadmin/login">Super Admin</a>
        </div>
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} Swad Sangam. All rights reserved.</p>
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
