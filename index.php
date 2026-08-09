<?php
// ================================================================
// index.php - Complete Portfolio with Professional About Section
// ================================================================

require_once 'config.php';

// ===== GET ABOUT INFO =====
$about = getAboutInfo($conn);

// ===== FETCH DATA FROM DATABASE =====
$skills = getSkills($conn);
$projects = getFeaturedProjects($conn, 6);
$services = getServices($conn);

// ===== GET ADMIN INFO =====
$isLoggedIn = isAdminLoggedIn();
$adminInfo = getAdminInfo($conn);

// ===== GET PROFILE PHOTO =====
$currentPhoto = 'profile.jpg';
if ($adminInfo && !empty($adminInfo['profile_image'])) {
    $currentPhoto = $adminInfo['profile_image'];
}
$photoPath = __DIR__ . '/uploads/' . $currentPhoto;
if (!file_exists($photoPath)) {
    $currentPhoto = 'default.jpg';
}

// ===== CHECK RESUME =====
$resumePath = __DIR__ . '/uploads/resume.pdf';
$resumeExists = file_exists($resumePath);

// ===== CONTACT FORM MESSAGES =====
$successMessage = "";
$errorMessage = "";

if (isset($_GET['success'])) {
    $successMessage = "✅ Your message has been sent successfully! I will get back to you soon.";
}
if (isset($_GET['error'])) {
    $errorMessage = "❌ Failed to send message. Please try again!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($about['name'] ?? 'Suvash Baskota'); ?> - Professional Portfolio</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        /* ============================================================
           COMPLETE CSS - PROFESSIONAL STYLE
           ============================================================ */
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary: #6C63FF;
            --primary-dark: #5a52d5;
            --secondary: #FF6584;
            --dark: #1a1a2e;
            --dark-light: #16213e;
            --gray: #8c8c9e;
            --light-gray: #f0f0f5;
            --white: #ffffff;
            --gradient: linear-gradient(135deg, #6C63FF 0%, #FF6584 100%);
            --shadow: 0 10px 40px rgba(0,0,0,0.08);
            --radius: 12px;
            --transition: all 0.3s ease;
            --font: 'Inter', -apple-system, sans-serif;
        }
        
        html {
            scroll-behavior: smooth;
        }
        
        body {
            font-family: var(--font);
            color: var(--dark);
            line-height: 1.7;
            background: var(--white);
            overflow-x: hidden;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }
        
        /* ============================================================
           ALERTS
           ============================================================ */
        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            animation: slideDown 0.4s ease;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* ============================================================
           HEADER
           ============================================================ */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            background: #0a0a1a;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            transition: var(--transition);
        }
        
        .header.scrolled {
            background: rgba(10, 10, 26, 0.95);
            backdrop-filter: blur(20px);
        }
        
        .navbar {
            padding: 16px 0;
        }
        
        .nav-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo a {
            font-size: 24px;
            font-weight: 800;
            color: var(--white);
            text-decoration: none;
            letter-spacing: 2px;
        }
        
        .logo a span {
            color: var(--primary);
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            gap: 30px;
        }
        
        .nav-menu a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: var(--transition);
            position: relative;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .nav-menu a::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: var(--transition);
        }
        
        .nav-menu a:hover::after,
        .nav-menu a.active::after {
            width: 100%;
        }
        
        .nav-menu a:hover {
            color: var(--white);
        }
        
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .btn-nav {
            padding: 8px 24px;
            background: var(--gradient);
            color: var(--white);
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition);
            font-family: var(--font);
        }
        
        .btn-nav:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(108, 99, 255, 0.4);
        }
        
        .btn-nav-outline {
            padding: 8px 20px;
            background: transparent;
            color: var(--white);
            border: 2px solid rgba(255,255,255,0.2);
            border-radius: 50px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition);
            font-family: var(--font);
        }
        
        .btn-nav-outline:hover {
            border-color: var(--primary);
            background: var(--primary);
            transform: translateY(-2px);
        }
        
        .btn-resume {
            padding: 8px 24px;
            background: #28a745;
            color: var(--white);
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition);
            font-family: var(--font);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-resume:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(40, 167, 69, 0.4);
        }
        
        .btn-resume-missing {
            padding: 8px 24px;
            background: #dc3545;
            color: var(--white);
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition);
            font-family: var(--font);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-resume-missing:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        
        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            padding: 5px;
        }
        
        .hamburger span {
            width: 25px;
            height: 2px;
            background: var(--white);
            transition: var(--transition);
            border-radius: 50px;
        }
        
        .hamburger.active span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }
        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }
        .hamburger.active span:nth-child(3) {
            transform: rotate(-45deg) translate(5px, -5px);
        }
        
        /* ============================================================
           HERO SECTION
           ============================================================ */
        .hero {
            padding: 140px 0 80px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(180deg, #0a0a1a 0%, #1a1a2e 100%);
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            right: -200px;
            top: -200px;
            width: 600px;
            height: 600px;
            background: var(--gradient);
            opacity: 0.05;
            border-radius: 50%;
            pointer-events: none;
        }
        
        .hero::after {
            content: '';
            position: absolute;
            left: -200px;
            bottom: -200px;
            width: 500px;
            height: 500px;
            background: var(--gradient);
            opacity: 0.03;
            border-radius: 50%;
            pointer-events: none;
        }
        
        .hero-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            position: relative;
            z-index: 1;
        }
        
        .hero-text {
            color: var(--white);
        }
        
        .hero-badge {
            display: inline-block;
            background: rgba(108, 99, 255, 0.15);
            color: var(--primary);
            padding: 6px 18px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            border: 1px solid rgba(108, 99, 255, 0.2);
        }
        
        .hero-title {
            font-size: 56px;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 8px;
        }
        
        .hero-title .highlight {
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero-role {
            font-size: 22px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 20px;
            min-height: 40px;
        }
        
        .typed-text {
            display: inline-block;
        }
        .typed-text::after {
            content: '|';
            animation: blink 0.7s infinite;
        }
        
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }
        
        .hero-description {
            font-size: 17px;
            color: rgba(255,255,255,0.6);
            max-width: 500px;
            margin-bottom: 30px;
            line-height: 1.9;
        }
        
        .hero-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }
        
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 32px;
            background: var(--gradient);
            color: var(--white);
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 15px;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
            font-family: var(--font);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 40px rgba(108, 99, 255, 0.4);
        }
        
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 32px;
            background: transparent;
            color: var(--white);
            border: 2px solid rgba(255,255,255,0.2);
            border-radius: 50px;
            font-weight: 600;
            font-size: 15px;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
            font-family: var(--font);
        }
        
        .btn-secondary:hover {
            border-color: var(--primary);
            background: var(--primary);
            transform: translateY(-3px);
        }
        
        .hero-social {
            display: flex;
            gap: 12px;
        }
        
        .hero-social a {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            color: rgba(255,255,255,0.6);
            font-size: 18px;
            border: 1px solid rgba(255,255,255,0.05);
        }
        
        .hero-social a:hover {
            background: var(--gradient);
            color: var(--white);
            transform: translateY(-5px);
            border-color: transparent;
        }
        
        /* ===== PHOTO ===== */
        .hero-image {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .hero-image-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
        }
        
        .photo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }
        
        .photo-container img {
            width: 320px;
            height: 320px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid rgba(108, 99, 255, 0.3);
            box-shadow: 0 20px 60px rgba(108, 99, 255, 0.15);
            transition: var(--transition);
        }
        
        .photo-container img:hover {
            transform: scale(1.03);
            box-shadow: 0 30px 80px rgba(108, 99, 255, 0.25);
        }
        
        .photo-badge {
            margin-top: 20px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            padding: 18px 32px;
            border-radius: 16px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.08);
            width: 100%;
            max-width: 360px;
            transition: var(--transition);
        }
        
        .photo-badge:hover {
            border-color: rgba(108, 99, 255, 0.3);
        }
        
        .badge-name {
            font-size: 22px;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 4px;
        }
        
        .badge-title {
            font-size: 16px;
            font-weight: 500;
            color: var(--primary);
            margin-bottom: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .badge-location {
            font-size: 14px;
            color: rgba(255,255,255,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        
        .floating-card {
            position: absolute;
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(20px);
            padding: 12px 18px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: float 3s ease-in-out infinite;
            border: 1px solid rgba(255,255,255,0.08);
            z-index: 2;
        }
        
        .floating-card i {
            color: var(--primary);
            font-size: 16px;
            width: 32px;
            height: 32px;
            background: rgba(108, 99, 255, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .floating-card span {
            font-weight: 600;
            color: var(--white);
            font-size: 13px;
        }
        
        .floating-card-1 {
            top: 10px;
            right: -5px;
            animation-delay: 0s;
        }
        
        .floating-card-2 {
            bottom: 90px;
            left: -5px;
            animation-delay: 1s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        /* ============================================================
           ABOUT SECTION - PROFESSIONAL STYLES
           ============================================================ */
        .about {
            padding: 100px 0;
            background: #f8f9ff;
            position: relative;
            overflow: hidden;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .section-subtitle {
            display: inline-block;
            font-size: 13px;
            font-weight: 600;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 3px;
            background: rgba(108, 99, 255, 0.08);
            padding: 6px 20px;
            border-radius: 50px;
            margin-bottom: 12px;
        }
        
        .section-title {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 12px;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .section-description {
            color: var(--gray);
            font-size: 18px;
            max-width: 600px;
            margin: 0 auto;
        }
        
        /* ===== ABOUT WRAPPER ===== */
        .about-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }
        
        /* ===== LEFT: IMAGE ===== */
        .about-image-box {
            position: relative;
        }
        
        .image-container {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(108, 99, 255, 0.15);
        }
        
        .image-container img {
            width: 100%;
            height: 500px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .image-container:hover img {
            transform: scale(1.03);
        }
        
        /* ===== IMAGE OVERLAY ===== */
        .image-overlay {
            position: absolute;
            bottom: 30px;
            left: 30px;
            right: 30px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 20px 25px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.5);
        }
        
        .image-overlay .number {
            font-size: 32px;
            font-weight: 800;
            color: var(--primary);
            line-height: 1;
        }
        
        .image-overlay .text {
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
        }
        
        /* ===== FLOATING BADGES ===== */
        .floating-badge {
            position: absolute;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 12px 18px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            border: 1px solid rgba(255,255,255,0.5);
            animation: float 3s ease-in-out infinite;
            z-index: 2;
        }
        
        .floating-badge i {
            color: var(--primary);
            font-size: 18px;
            width: 36px;
            height: 36px;
            background: rgba(108, 99, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .floating-badge span {
            font-size: 13px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .floating-badge-1 {
            top: 30px;
            right: -10px;
            animation-delay: 0s;
        }
        
        .floating-badge-2 {
            bottom: 120px;
            left: -10px;
            animation-delay: 1s;
        }
        
        /* ===== RIGHT: CONTENT ===== */
        .about-content-box {
            padding: 20px 0;
        }
        
        .about-heading {
            margin-bottom: 25px;
        }
        
        .about-subtitle {
            display: inline-block;
            font-size: 14px;
            font-weight: 600;
            color: var(--primary);
            background: rgba(108, 99, 255, 0.08);
            padding: 4px 16px;
            border-radius: 50px;
            margin-bottom: 10px;
        }
        
        .about-heading h3 {
            font-size: 32px;
            font-weight: 800;
            color: var(--dark);
            line-height: 1.2;
        }
        
        .about-description {
            color: var(--gray);
            font-size: 16px;
            line-height: 1.9;
            margin-bottom: 16px;
        }
        
        /* ===== INFO GRID ===== */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin: 25px 0 30px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }
        
        .info-item:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(108, 99, 255, 0.08);
        }
        
        .info-icon {
            width: 40px;
            height: 40px;
            background: rgba(108, 99, 255, 0.08);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 16px;
            flex-shrink: 0;
        }
        
        .info-item div {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 11px;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .info-value {
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
        }
        
        /* ===== BUTTONS ===== */
        .about-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        
        /* ============================================================
           SKILLS
           ============================================================ */
        .skills {
            padding: 100px 0;
            background: var(--white);
        }
        
        .skills-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .skill-item {
            background: var(--white);
            padding: 20px 24px;
            border-radius: var(--radius);
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: var(--transition);
        }
        
        .skill-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        
        .skill-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .skill-name {
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 15px;
        }
        
        .skill-name i {
            color: var(--primary);
            font-size: 16px;
            width: 28px;
            height: 28px;
            background: rgba(108, 99, 255, 0.08);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .skill-percentage {
            font-weight: 700;
            color: var(--primary);
        }
        
        .skill-bar {
            width: 100%;
            height: 6px;
            background: var(--light-gray);
            border-radius: 50px;
            overflow: hidden;
        }
        
        .skill-progress {
            height: 100%;
            border-radius: 50px;
            background: var(--gradient);
            transition: width 1.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* ============================================================
           PORTFOLIO
           ============================================================ */
        .portfolio {
            padding: 100px 0;
            background: #f8f9ff;
        }
        
        .portfolio-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 30px;
        }
        
        .portfolio-item {
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            transition: var(--transition);
            background: var(--white);
        }
        
        .portfolio-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
        }
        
        .portfolio-image {
            position: relative;
            overflow: hidden;
            height: 240px;
        }
        
        .portfolio-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .portfolio-item:hover .portfolio-image img {
            transform: scale(1.05);
        }
        
        .portfolio-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(10, 10, 26, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: var(--transition);
        }
        
        .portfolio-item:hover .portfolio-overlay {
            opacity: 1;
        }
        
        .portfolio-actions {
            display: flex;
            gap: 15px;
        }
        
        .portfolio-actions a {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--dark);
            font-size: 18px;
            transition: var(--transition);
            transform: translateY(20px);
        }
        
        .portfolio-item:hover .portfolio-actions a {
            transform: translateY(0);
        }
        
        .portfolio-actions a:hover {
            background: var(--primary);
            color: var(--white);
        }
        
        .portfolio-info {
            padding: 20px 24px;
        }
        
        .portfolio-info h4 {
            font-size: 18px;
            font-weight: 700;
        }
        
        .portfolio-info p {
            color: var(--gray);
            font-size: 14px;
        }
        
        /* ============================================================
           SERVICES
           ============================================================ */
        .services {
            padding: 100px 0;
            background: var(--white);
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 30px;
        }
        
        .service-card {
            background: var(--white);
            padding: 35px 28px;
            border-radius: var(--radius);
            text-align: center;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.08);
        }
        
        .service-icon {
            width: 70px;
            height: 70px;
            background: rgba(108, 99, 255, 0.08);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 28px;
            color: var(--primary);
            transition: var(--transition);
        }
        
        .service-card:hover .service-icon {
            background: var(--gradient);
            color: var(--white);
        }
        
        .service-card h3 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .service-card p {
            color: var(--gray);
            font-size: 15px;
            line-height: 1.7;
        }
        
        /* ============================================================
           CONTACT
           ============================================================ */
        .contact {
            padding: 100px 0;
            background: #f8f9ff;
        }
        
        .contact-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 50px;
        }
        
        .contact-info-item {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 18px 20px;
            border-radius: var(--radius);
            background: var(--white);
            margin-bottom: 16px;
            transition: var(--transition);
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        }
        
        .contact-info-item:hover {
            transform: translateX(6px);
        }
        
        .contact-icon {
            width: 48px;
            height: 48px;
            background: rgba(108, 99, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 18px;
            flex-shrink: 0;
        }
        
        .contact-info-item h4 {
            font-size: 14px;
            font-weight: 600;
            color: var(--gray);
        }
        
        .contact-info-item p {
            font-size: 15px;
            font-weight: 500;
        }
        
        .contact-form-wrapper {
            background: var(--white);
            padding: 40px;
            border-radius: var(--radius);
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 15px;
            background: var(--white);
            transition: var(--transition);
            font-family: var(--font);
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(108, 99, 255, 0.08);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        /* ============================================================
           FOOTER
           ============================================================ */
        .footer {
            background: #0a0a1a;
            color: var(--white);
            padding: 40px 0 20px;
        }
        
        .footer-content {
            text-align: center;
        }
        
        .footer-logo h3 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 8px;
        }
        
        .footer-logo h3 span {
            color: var(--primary);
        }
        
        .footer-logo p {
            color: rgba(255,255,255,0.4);
        }
        
        .footer-social {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin: 20px 0;
        }
        
        .footer-social a {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            font-size: 18px;
            color: rgba(255,255,255,0.6);
            border: 1px solid rgba(255,255,255,0.05);
        }
        
        .footer-social a:hover {
            background: var(--gradient);
            color: var(--white);
            transform: translateY(-4px);
            border-color: transparent;
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.05);
            padding-top: 20px;
            margin-top: 20px;
        }
        
        .footer-bottom p {
            font-size: 14px;
            color: rgba(255,255,255,0.3);
        }
        
        /* ============================================================
           BACK TO TOP
           ============================================================ */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--gradient);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 4px 20px rgba(108, 99, 255, 0.3);
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
            z-index: 999;
            text-decoration: none;
        }
        
        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
        }
        
        .back-to-top:hover {
            transform: translateY(-4px);
        }
        
        /* ============================================================
           RESPONSIVE
           ============================================================ */
        @media (max-width: 992px) {
            .hero-content {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 40px;
            }
            
            .hero-text {
                order: 2;
            }
            
            .hero-image {
                order: 1;
            }
            
            .hero-description {
                max-width: 100%;
                margin-left: auto;
                margin-right: auto;
            }
            
            .hero-buttons {
                justify-content: center;
            }
            
            .hero-social {
                justify-content: center;
            }
            
            .photo-container img {
                width: 260px;
                height: 260px;
            }
            
            .about-wrapper {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            
            .about-image-box {
                max-width: 500px;
                margin: 0 auto;
            }
            
            .image-container img {
                height: 400px;
            }
            
            .floating-badge-1 {
                right: 10px;
                top: 20px;
            }
            
            .floating-badge-2 {
                left: 10px;
                bottom: 130px;
            }
            
            .contact-content {
                grid-template-columns: 1fr;
            }
            
            .floating-card-1 {
                right: 10px;
                top: 5px;
            }
            
            .floating-card-2 {
                left: 10px;
                bottom: 70px;
            }
        }
        
        @media (max-width: 768px) {
            .nav-menu {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background: #0a0a1a;
                flex-direction: column;
                padding: 20px;
                gap: 16px;
                border-top: 1px solid rgba(255,255,255,0.05);
            }
            
            .nav-menu.active {
                display: flex;
            }
            
            .hamburger {
                display: flex;
            }
            
            .hero-title {
                font-size: 36px;
            }
            
            .hero-role {
                font-size: 18px;
            }
            
            .photo-container img {
                width: 220px;
                height: 220px;
            }
            
            .photo-badge {
                max-width: 280px;
                padding: 14px 20px;
            }
            
            .badge-name {
                font-size: 18px;
            }
            
            .badge-title {
                font-size: 14px;
            }
            
            .about {
                padding: 60px 0;
            }
            
            .section-title {
                font-size: 30px;
            }
            
            .about-heading h3 {
                font-size: 26px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .image-container img {
                height: 320px;
            }
            
            .image-overlay {
                padding: 15px 20px;
                bottom: 20px;
                left: 20px;
                right: 20px;
            }
            
            .image-overlay .number {
                font-size: 24px;
            }
            
            .floating-badge {
                display: none;
            }
            
            .about-buttons {
                flex-direction: column;
            }
            
            .about-buttons .btn-primary,
            .about-buttons .btn-secondary {
                width: 100%;
                justify-content: center;
            }
            
            .skills-grid {
                grid-template-columns: 1fr;
            }
            
            .portfolio-grid {
                grid-template-columns: 1fr;
            }
            
            .services-grid {
                grid-template-columns: 1fr;
            }
            
            .contact-form-wrapper {
                padding: 24px;
            }
            
            .floating-card {
                display: none;
            }
            
            .hero {
                padding: 120px 0 60px;
            }
        }
        
        @media (max-width: 480px) {
            .hero-title {
                font-size: 28px;
            }
            
            .photo-container img {
                width: 170px;
                height: 170px;
            }
            
            .photo-badge {
                max-width: 220px;
                padding: 12px 16px;
            }
            
            .badge-name {
                font-size: 16px;
            }
            
            .badge-title {
                font-size: 13px;
            }
            
            .section-title {
                font-size: 24px;
            }
            
            .about-heading h3 {
                font-size: 22px;
            }
            
            .image-container img {
                height: 260px;
            }
            
            .image-overlay {
                flex-direction: column;
                text-align: center;
                gap: 5px;
            }
            
            .image-overlay .number {
                font-size: 20px;
            }
            
            .nav-actions .btn-nav-outline {
                display: none;
            }
            
            .hero-description {
                font-size: 15px;
            }
            
            .hero-buttons .btn-primary,
            .hero-buttons .btn-secondary {
                padding: 10px 20px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>

<!-- ============================================================
HEADER
============================================================ -->
<header class="header" id="header">
    <nav class="navbar">
        <div class="container">
            <div class="nav-content">
                <div class="logo">
                    <a href="index.php">SB<span>.</span></a>
                </div>
                
                <ul class="nav-menu" id="navMenu">
                    <li><a href="index.php" class="active">Home</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#portfolio">Portfolio</a></li>
                    <li><a href="gallery.php">Gallery</a></li>
                    <li><a href="blog.php">Blog</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
                
                <div class="nav-actions">
                    <?php if ($isLoggedIn): ?>
                        <a href="admin/dashboard.php" class="btn-nav-outline" style="border-color:rgba(108,99,255,0.5);">
                            <i class="fas fa-user-shield"></i> Dashboard
                        </a>
                    <?php else: ?>
                        <a href="admin/login.php" class="btn-nav-outline">
                            <i class="fas fa-lock"></i> Admin
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($resumeExists): ?>
                        <a href="uploads/resume.pdf" class="btn-resume" download>
                            <i class="fas fa-download"></i> Resume
                        </a>
                    <?php else: ?>
                        <a href="#contact" class="btn-resume-missing">
                            <i class="fas fa-exclamation-circle"></i> Resume
                        </a>
                    <?php endif; ?>
                    
                    <div class="hamburger" id="hamburger">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>

<!-- ============================================================
HERO SECTION
============================================================ -->
<section class="hero" id="home">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <span class="hero-badge">👋 Welcome to my portfolio</span>
                <h1 class="hero-title">
                    I'm <span class="highlight"><?php echo htmlspecialchars($about['name'] ?? 'Suvash Baskota'); ?></span>
                </h1>
                <div class="hero-role">
                    <span class="typed-text" id="typed-text"></span>
                </div>
                <p class="hero-description">
                    <?php echo htmlspecialchars($about['about_text'] ?? 'Experienced microfinance professional with practical exposure in branch management and internal audit.'); ?>
                </p>
                <div class="hero-buttons">
                    <a href="#contact" class="btn-primary">
                        <i class="fas fa-paper-plane"></i> Contact Me
                    </a>
                    <a href="#portfolio" class="btn-secondary">
                        <i class="fas fa-briefcase"></i> View Portfolio
                    </a>
                </div>
                <div class="hero-social">
                    <?php if (!empty($about['facebook'])): ?>
                        <a href="<?php echo htmlspecialchars($about['facebook']); ?>" target="_blank"><i class="fab fa-facebook"></i></a>
                    <?php endif; ?>
                    <?php if (!empty($about['linkedin'])): ?>
                        <a href="<?php echo htmlspecialchars($about['linkedin']); ?>" target="_blank"><i class="fab fa-linkedin"></i></a>
                    <?php endif; ?>
                    <?php if (!empty($about['twitter'])): ?>
                        <a href="<?php echo htmlspecialchars($about['twitter']); ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                    <?php endif; ?>
                    <?php if (!empty($about['youtube'])): ?>
                        <a href="<?php echo htmlspecialchars($about['youtube']); ?>" target="_blank"><i class="fab fa-youtube"></i></a>
                    <?php endif; ?>
                    <?php if (!empty($about['instagram'])): ?>
                        <a href="<?php echo htmlspecialchars($about['instagram']); ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="hero-image">
                <div class="hero-image-wrapper">
                    <div class="photo-container">
                        <img src="uploads/<?php echo $currentPhoto; ?>" alt="<?php echo htmlspecialchars($about['name'] ?? 'Suvash Baskota'); ?>">
                        <div class="photo-badge">
                            <h3 class="badge-name"><?php echo htmlspecialchars($about['name'] ?? 'Suvash Baskota'); ?></h3>
                            <p class="badge-title">
                                <i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($about['designation'] ?? 'Microfinance Officer'); ?>
                            </p>
                            <p class="badge-location">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($about['location'] ?? 'Kalaiya, Bara, Nepal'); ?>
                            </p>
                        </div>
                    </div>
                    <div class="floating-card floating-card-1">
                        <i class="fas fa-briefcase"></i>
                        <span><?php echo htmlspecialchars($about['experience'] ?? '3+ Years'); ?></span>
                    </div>
                    <div class="floating-card floating-card-2">
                        <i class="fas fa-building"></i>
                        <span>2 Companies</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
ABOUT SECTION - PROFESSIONAL LOOK
============================================================ -->
<section class="about" id="about">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">About Me</span>
            <h2 class="section-title">Who I Am</h2>
            <p class="section-description">Professional profile summary</p>
        </div>
        
        <div class="about-wrapper">
            <!-- LEFT: Image -->
            <div class="about-image-box">
                <div class="image-container">
                    <img src="uploads/<?php echo $about['profile_image'] ?? 'about.jpg'; ?>" alt="<?php echo htmlspecialchars($about['name'] ?? 'Suvash Baskota'); ?>">
                    <div class="image-overlay">
                        <span class="number"><?php echo htmlspecialchars($about['experience'] ?? '3+'); ?></span>
                        <span class="text">Years of Experience</span>
                    </div>
                    <div class="floating-badge floating-badge-1">
                        <i class="fas fa-award"></i>
                        <span>Certified Professional</span>
                    </div>
                    <div class="floating-badge floating-badge-2">
                        <i class="fas fa-users"></i>
                        <span>50+ Clients</span>
                    </div>
                </div>
            </div>
            
            <!-- RIGHT: Content -->
            <div class="about-content-box">
                <div class="about-heading">
                    <span class="about-subtitle">👋 Hello, I'm <?php echo htmlspecialchars($about['name'] ?? 'Suvash Baskota'); ?></span>
                    <h3><?php echo htmlspecialchars($about['designation'] ?? 'Microfinance Professional & Auditor'); ?></h3>
                </div>
                
                <p class="about-description">
                    <?php echo nl2br(htmlspecialchars($about['about_text'] ?? 'Experienced microfinance professional with practical exposure in branch management and internal audit. Proven ability to lead branch operations, maintain portfolio quality, ensure policy compliance and strengthen internal control systems.')); ?>
                </p>
                
                <p class="about-description">
                    With a strong background in credit and portfolio auditing, fraud detection, and internal control assessment, I bring a comprehensive approach to financial management and operational excellence.
                </p>
                
                <!-- ===== INFO GRID ===== -->
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-icon"><i class="fas fa-user"></i></span>
                        <div>
                            <span class="info-label">Full Name</span>
                            <span class="info-value"><?php echo htmlspecialchars($about['name'] ?? 'Suvash Baskota'); ?></span>
                        </div>
                    </div>
                    <div class="info-item">
                        <span class="info-icon"><i class="fas fa-briefcase"></i></span>
                        <div>
                            <span class="info-label">Designation</span>
                            <span class="info-value"><?php echo htmlspecialchars($about['designation'] ?? 'Microfinance Officer'); ?></span>
                        </div>
                    </div>
                    <div class="info-item">
                        <span class="info-icon"><i class="fas fa-map-marker-alt"></i></span>
                        <div>
                            <span class="info-label">Location</span>
                            <span class="info-value"><?php echo htmlspecialchars($about['location'] ?? 'Kalaiya, Bara, Nepal'); ?></span>
                        </div>
                    </div>
                    <div class="info-item">
                        <span class="info-icon"><i class="fas fa-calendar-check"></i></span>
                        <div>
                            <span class="info-label">Experience</span>
                            <span class="info-value"><?php echo htmlspecialchars($about['experience'] ?? '3+ Years'); ?></span>
                        </div>
                    </div>
                    <div class="info-item">
                        <span class="info-icon"><i class="fas fa-envelope"></i></span>
                        <div>
                            <span class="info-label">Email</span>
                            <span class="info-value"><?php echo htmlspecialchars($about['email'] ?? 'suvashbaskota456@gmail.com'); ?></span>
                        </div>
                    </div>
                    <div class="info-item">
                        <span class="info-icon"><i class="fas fa-phone"></i></span>
                        <div>
                            <span class="info-label">Phone</span>
                            <span class="info-value"><?php echo htmlspecialchars($about['phone'] ?? '+977-9861173924'); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- ===== BUTTONS ===== -->
                <div class="about-buttons">
                    <?php 
                    $cvPath = 'uploads/' . ($about['cv_file'] ?? 'resume.pdf');
                    if (file_exists($cvPath)): 
                    ?>
                        <a href="<?php echo $cvPath; ?>" class="btn-primary" download>
                            <i class="fas fa-download"></i> Download CV
                        </a>
                    <?php else: ?>
                        <a href="#contact" class="btn-primary">
                            <i class="fas fa-envelope"></i> Contact Me
                        </a>
                    <?php endif; ?>
                    <a href="#contact" class="btn-secondary">
                        <i class="fas fa-paper-plane"></i> Contact Me
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
SKILLS SECTION
============================================================ -->
<section class="skills" id="skills">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">My Skills</span>
            <h2 class="section-title">Professional Skills</h2>
            <p class="section-description">Technologies and competencies</p>
        </div>
        
        <div class="skills-grid">
            <?php if ($skills && $skills->num_rows > 0): ?>
                <?php while($skill = $skills->fetch_assoc()): ?>
                <div class="skill-item">
                    <div class="skill-header">
                        <span class="skill-name">
                            <i class="fas <?php echo $skill['icon'] ?? 'fa-star'; ?>"></i>
                            <?php echo htmlspecialchars($skill['name']); ?>
                        </span>
                        <span class="skill-percentage"><?php echo $skill['percentage']; ?>%</span>
                    </div>
                    <div class="skill-bar">
                        <div class="skill-progress" style="width: <?php echo $skill['percentage']; ?>%;"></div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align:center;padding:40px;color:#888;grid-column:1/-1;">
                    <i class="fas fa-plus-circle" style="font-size:48px;display:block;margin-bottom:15px;color:#667eea;"></i>
                    <p>No skills added yet.</p>
                    <?php if ($isLoggedIn): ?>
                        <a href="admin/dashboard.php" style="color:#667eea;font-weight:600;">Add from Admin Panel →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ============================================================
PORTFOLIO SECTION
============================================================ -->
<section class="portfolio" id="portfolio">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Portfolio</span>
            <h2 class="section-title">My Projects</h2>
            <p class="section-description">Featured work</p>
        </div>
        
        <div class="portfolio-grid">
            <?php if ($projects && $projects->num_rows > 0): ?>
                <?php while($project = $projects->fetch_assoc()): ?>
                <div class="portfolio-item">
                    <div class="portfolio-image">
                        <img src="uploads/projects/<?php echo $project['image'] ?? 'default.jpg'; ?>" alt="<?php echo $project['title']; ?>">
                        <div class="portfolio-overlay">
                            <div class="portfolio-actions">
                                <?php if ($project['project_url']): ?>
                                    <a href="<?php echo $project['project_url']; ?>" target="_blank"><i class="fas fa-link"></i></a>
                                <?php endif; ?>
                                <?php if ($project['github_url']): ?>
                                    <a href="<?php echo $project['github_url']; ?>" target="_blank"><i class="fab fa-github"></i></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="portfolio-info">
                        <h4><?php echo htmlspecialchars($project['title']); ?></h4>
                        <p><?php echo htmlspecialchars($project['category']); ?></p>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align:center;padding:40px;color:#888;grid-column:1/-1;">
                    <i class="fas fa-folder-open" style="font-size:48px;display:block;margin-bottom:15px;color:#667eea;"></i>
                    <p>No projects added yet.</p>
                    <?php if ($isLoggedIn): ?>
                        <a href="admin/dashboard.php" style="color:#667eea;font-weight:600;">Add from Admin Panel →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ============================================================
SERVICES SECTION
============================================================ -->
<section class="services" id="services">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Services</span>
            <h2 class="section-title">What I Offer</h2>
            <p class="section-description">Professional services</p>
        </div>
        
        <div class="services-grid">
            <?php if ($services && $services->num_rows > 0): ?>
                <?php while($service = $services->fetch_assoc()): ?>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas <?php echo $service['icon'] ?? 'fa-star'; ?>"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                    <p><?php echo htmlspecialchars($service['description']); ?></p>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align:center;padding:40px;color:#888;grid-column:1/-1;">
                    <i class="fas fa-tools" style="font-size:48px;display:block;margin-bottom:15px;color:#667eea;"></i>
                    <p>No services added yet.</p>
                    <?php if ($isLoggedIn): ?>
                        <a href="admin/dashboard.php" style="color:#667eea;font-weight:600;">Add from Admin Panel →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ============================================================
CONTACT SECTION
============================================================ -->
<section class="contact" id="contact">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Contact</span>
            <h2 class="section-title">Get In Touch</h2>
            <p class="section-description">Let's connect and collaborate</p>
        </div>
        
        <div class="contact-content">
            <div class="contact-info">
                <div class="contact-info-item">
                    <div class="contact-icon"><i class="fas fa-phone"></i></div>
                    <div>
                        <h4>Phone</h4>
                        <p><?php echo htmlspecialchars($about['phone'] ?? '+977-9861173924'); ?></p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                    <div>
                        <h4>Email</h4>
                        <p><?php echo htmlspecialchars($about['email'] ?? 'suvashbaskota456@gmail.com'); ?></p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div>
                        <h4>Address</h4>
                        <p><?php echo htmlspecialchars($about['location'] ?? 'Kalaiya, Bara, Nepal'); ?></p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-icon"><i class="fas fa-clock"></i></div>
                    <div>
                        <h4>Working Hours</h4>
                        <p>Mon-Fri: 9AM - 6PM</p>
                    </div>
                </div>
            </div>
            
            <div class="contact-form-wrapper">
                
                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success"><?php echo $successMessage; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($errorMessage)): ?>
                    <div class="alert alert-error"><?php echo $errorMessage; ?></div>
                <?php endif; ?>
                
                <form id="contactForm" method="POST" action="send-message.php">
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Your Name" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Your Email" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="subject" placeholder="Subject" required>
                    </div>
                    <div class="form-group">
                        <textarea name="message" rows="5" placeholder="Your Message" required></textarea>
                    </div>
                    <button type="submit" class="btn-primary" style="width:100%;justify-content:center;">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
FOOTER
============================================================ -->
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-logo">
                <h3>SB<span>.</span></h3>
                <p><?php echo htmlspecialchars($about['designation'] ?? 'Microfinance Professional & Auditor'); ?></p>
            </div>
            <div class="footer-social">
                <?php if (!empty($about['facebook'])): ?>
                    <a href="<?php echo htmlspecialchars($about['facebook']); ?>" target="_blank"><i class="fab fa-facebook"></i></a>
                <?php endif; ?>
                <?php if (!empty($about['linkedin'])): ?>
                    <a href="<?php echo htmlspecialchars($about['linkedin']); ?>" target="_blank"><i class="fab fa-linkedin"></i></a>
                <?php endif; ?>
                <?php if (!empty($about['twitter'])): ?>
                    <a href="<?php echo htmlspecialchars($about['twitter']); ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                <?php endif; ?>
                <?php if (!empty($about['youtube'])): ?>
                    <a href="<?php echo htmlspecialchars($about['youtube']); ?>" target="_blank"><i class="fab fa-youtube"></i></a>
                <?php endif; ?>
                <?php if (!empty($about['instagram'])): ?>
                    <a href="<?php echo htmlspecialchars($about['instagram']); ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                <?php endif; ?>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($about['name'] ?? 'Suvash Baskota'); ?>. All rights reserved.</p>
                <?php if ($isLoggedIn): ?>
                    <p style="font-size:12px;color:rgba(255,255,255,0.3);margin-top:5px;">
                        🔒 Logged in as <?php echo htmlspecialchars($adminInfo['username'] ?? 'Admin'); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</footer>

<!-- ============================================================
BACK TO TOP
============================================================ -->
<a href="#home" class="back-to-top" id="backToTop">
    <i class="fas fa-arrow-up"></i>
</a>

<!-- ============================================================
JAVASCRIPT
============================================================ -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // ====== TYPING EFFECT ======
    const roles = [
        '<?php echo htmlspecialchars($about['designation'] ?? 'Microfinance Professional'); ?>',
        'Branch Manager',
        'Internal Auditor',
        'Officer'
    ];
    
    let roleIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
    const typedText = document.getElementById('typed-text');
    
    if (typedText) {
        function typeEffect() {
            const currentRole = roles[roleIndex];
            
            if (isDeleting) {
                typedText.textContent = currentRole.substring(0, charIndex - 1);
                charIndex--;
            } else {
                typedText.textContent = currentRole.substring(0, charIndex + 1);
                charIndex++;
            }
            
            let speed = isDeleting ? 50 : 100;
            
            if (!isDeleting && charIndex === currentRole.length) {
                speed = 2000;
                isDeleting = true;
            } else if (isDeleting && charIndex === 0) {
                isDeleting = false;
                roleIndex = (roleIndex + 1) % roles.length;
                speed = 500;
            }
            
            setTimeout(typeEffect, speed);
        }
        typeEffect();
    }
    
    // ====== NAVIGATION HAMBURGER ======
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('navMenu');
    
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', function() {
            this.classList.toggle('active');
            navMenu.classList.toggle('active');
        });
    }
    
    // ====== ACTIVE NAV LINK ======
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-menu a');
    
    window.addEventListener('scroll', function() {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop - 100;
            if (window.scrollY >= sectionTop) {
                current = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current || link.getAttribute('href') === 'index.php') {
                link.classList.add('active');
            }
        });
    });
    
    // ====== BACK TO TOP ======
    const backToTop = document.getElementById('backToTop');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 500) {
            backToTop.classList.add('visible');
        } else {
            backToTop.classList.remove('visible');
        }
    });
    
    // ====== SKILL ANIMATION ======
    const skillItems = document.querySelectorAll('.skill-item');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const progress = entry.target.querySelector('.skill-progress');
                if (progress) {
                    const width = progress.style.width;
                    progress.style.width = '0';
                    setTimeout(() => {
                        progress.style.width = width;
                    }, 300);
                }
            }
        });
    }, { threshold: 0.3 });
    
    skillItems.forEach(item => observer.observe(item));
});
</script>

</body>
</html>