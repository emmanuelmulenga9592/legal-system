<?php
session_start();

// 1. If the user successfully completed 2FA and is verified, route them directly to their dashboard
if (isset($_SESSION['user_role'])) {
    
    // Safety check: If you have an unverified account flag set up, catch it here
    if (isset($_SESSION['is_verified']) && $_SESSION['is_verified'] == 0) {
        header("Location: verification_pending.php");
        exit();
    }
    
    // Securely sanitized dynamic redirect
    $target_dashboard = preg_replace('/[^a-zA-Z0-9_]/', '', $_SESSION['user_role']);
    header("Location: " . $target_dashboard . "_dashboard.php");
    exit();
}

// 2. If they passed the password gate but are running away from the 2FA screen, force them back to complete it
if (isset($_SESSION['temp_tfa_user_id'])) {
    header("Location: verify_tfa.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M. CHUNGA & COMPANY | Modern Legal Management</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <nav class="site-nav">
        <div class="nav-inner">
            <a href="#" class="logo">M. CHUNGA & COMPANY</a>
            <div class="nav-links">
                <a href="#features">Features</a>
                <a href="#contact">Contact</a>
                <a href="./login.php" class="btn-login">Client Login</a>
            </div>
        </div>
    </nav>

    <main>
        <section class="hero">
            <div class="main-container hero-grid">
                <div class="hero-content">
                    <h1>Legal Management Made <span>Simple.</span></h1>
                    <p>M. CHUNGA & COMPANY provides a streamlined solution for managing your legal documents, communication, and case tracking in one secure portal.</p>
                    <a href="register.php" class="btn btn-primary btn-cta">Get Started Today</a>
                </div>
                <div class="hero-image">
                    <i class="fa-solid fa-scale-balanced hero-icon"></i>
                </div>
            </div>
        </section>

        <section id="features" class="features">
            <div class="main-container">
                <div class="section-title">
                    <h2>Why Choose M. CHUNGA & COMPANY?</h2>
                    <p>Built for efficiency, designed for clarity.</p>
                </div>
                <div class="feature-grid">
                    <div class="feature-card">
                        <i class="fas fa-folder-open"></i>
                        <h3>Secure Document Vault</h3>
                        <p>Upload and manage invoices, contracts, and legal filings with bank-grade encryption and easy searchability.</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-comments"></i>
                        <h3>Real-Time Updates</h3>
                        <p>Stay in sync with your legal team via our integrated case notes system. No more digging through emails.</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-shield-halved"></i>
                        <h3>Role-Based Access</h3>
                        <p>Strict permissions ensure that clients, lawyers, and administrators only see what they are supposed to.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="contact" class="contact">
            <div class="main-container">
                <div class="contact-card">
                    <div class="contact-meta">
                        <div>
                            <h2>Contact Our Team</h2>
                            <p>Have questions about a new case? Send us a message and our legal administrators will get back to you within 24 hours.</p>
                        </div>
                        <div class="contact-item">
                            <span class="contact-icon"><i class="fas fa-location-dot"></i></span>
                            <div>
                                <strong>Office Location</strong>
                                <p>Lusaka Business District, Zambia</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <span class="contact-icon"><i class="fas fa-phone"></i></span>
                            <div>
                                <strong>Call Us</strong>
                                <p>+260 970 000 000</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <div class="main-container">
                <div>
                    <h3>M. CHUNGA & COMPANY Legal Systems</h3>
                    <p>Modernizing the way law firms and businesses collaborate.</p>
                </div>
                <div class="social-links">
                    <i class="fab fa-linkedin"></i>
                    <i class="fab fa-twitter"></i>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; 2026 M. CHUNGA & COMPANY. All Rights Reserved.
        </div>
    </footer>

</body>
</html>