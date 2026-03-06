<?php
if (!defined('ABSPATH')) {
    exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php bloginfo('name'); ?> - <?php bloginfo('description'); ?></title>
    <?php wp_head(); ?>
    
    <!-- Swiper.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    
    <!-- Custom Theme Styles -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap');

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            color: #e2e8f0;
            background-color: #0f172a;
            font-family: Poppins, sans-serif;
            overflow-x: hidden;
            line-height: 1.5;
        }

        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #1e293b;
        }

        ::-webkit-scrollbar-thumb {
            background: #2563eb;
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #1d4ed8;
        }

        .glass {
            -webkit-backdrop-filter: blur(10px);
            backdrop-filter: blur(10px);
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .glass-premium {
            -webkit-backdrop-filter: blur(16px);
            backdrop-filter: blur(16px);
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(0, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 255, 255, 0.1);
        }

        .glass-premium:hover {
            border-color: rgba(0, 255, 255, 0.4);
            box-shadow: 0 8px 32px rgba(0, 255, 255, 0.2);
        }

        .gradient-text {
            background: linear-gradient(135deg, #2563eb, #f59e0b);
            -webkit-text-fill-color: transparent;
            -webkit-background-clip: text;
            background-clip: text;
        }

        .gradient-text-neon {
            background: linear-gradient(135deg, #00ffff, #0099ff);
            -webkit-text-fill-color: transparent;
            -webkit-background-clip: text;
            background-clip: text;
            animation: neon-pulse 3s ease-in-out infinite;
        }

        @keyframes neon-pulse {
            0%, 100% {
                filter: drop-shadow(0 0 10px rgba(0, 255, 255, 0.3));
            }
            50% {
                filter: drop-shadow(0 0 20px rgba(0, 255, 255, 0.6));
            }
        }

        .gradient-bg {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(245, 158, 11, 0.1));
        }

        .btn-primary {
            cursor: pointer;
            background: linear-gradient(to right, #2563eb, #f59e0b);
            color: rgb(255, 255, 255);
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-primary:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .btn-secondary {
            cursor: pointer;
            border: 2px solid #2563eb;
            color: #2563eb;
            background: transparent;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-secondary:hover {
            background-color: #2563eb;
            color: rgb(255, 255, 255);
        }

        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-hover:hover {
            transform: scale(1.05);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        /* Header Styles */
        .header {
            background: rgba(15, 23, 42, 0.8);
            -webkit-backdrop-filter: blur(10px);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }

        .logo h1 {
            font-size: 1.875rem;
            font-weight: 700;
            background: linear-gradient(135deg, #2563eb, #f59e0b);
            -webkit-text-fill-color: transparent;
            -webkit-background-clip: text;
            background-clip: text;
        }

        .nav-list {
            list-style: none;
            display: flex;
            gap: 2rem;
        }

        .nav-link {
            color: #e2e8f0;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: #2563eb;
        }

        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            gap: 6px;
        }

        .hamburger span {
            width: 25px;
            height: 3px;
            background-color: #e2e8f0;
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .hamburger.active span:nth-child(1) {
            transform: rotate(45deg) translate(10px, 10px);
        }

        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }

        .hamburger.active span:nth-child(3) {
            transform: rotate(-45deg) translate(8px, -8px);
        }

        .nav.mobile-open {
            display: flex !important;
            flex-direction: column;
            position: absolute;
            top: 70px;
            left: 0;
            right: 0;
            background: rgba(15, 23, 42, 0.95);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem 0;
            gap: 0;
        }

        .nav.mobile-open .nav-list {
            flex-direction: column;
            gap: 0;
        }

        .nav.mobile-open .nav-list li {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        @media (max-width: 768px) {
            .hamburger {
                display: flex;
            }

            .nav {
                display: none;
            }

            .nav-list {
                gap: 0;
            }
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .section-padding {
            padding: 3rem 1rem;
        }

        .flex {
            display: flex;
        }

        .flex-col {
            flex-direction: column;
        }

        .justify-between {
            justify-content: space-between;
        }

        .gap-4 {
            gap: 1rem;
        }

        .gap-6 {
            gap: 1.5rem;
        }

        .text-white {
            color: white;
        }

        .text-primary {
            color: #2563eb;
        }

        .text-lg {
            font-size: 1.125rem;
        }

        .mb-4 {
            margin-bottom: 1rem;
        }

        .mb-6 {
            margin-bottom: 1.5rem;
        }

        .p-4 {
            padding: 1rem;
        }

        .p-6 {
            padding: 1.5rem;
        }

        .w-full {
            width: 100%;
        }

        .rounded-lg {
            border-radius: 0.5rem;
        }

        @media (max-width: 768px) {
            .nav-list {
                display: none;
            }
        }
    </style>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'impulse-academy-clone'); ?></a>

<!-- Header Navigation -->
<header class="header">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <h1><?php bloginfo('name'); ?></h1>
            </div>
            <div class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <nav class="nav" id="navMenu">
                <ul class="nav-list">
                    <li><a href="#home" class="nav-link">Home</a></li>
                    <li><a href="#courses" class="nav-link">Courses</a></li>
                    <li><a href="#why-us" class="nav-link">Why Us</a></li>
                    <li><a href="#journey" class="nav-link">Journey</a></li>
                    <li><a href="#services" class="nav-link">Services</a></li>
                    <li><a href="#contact" class="nav-link">Contact</a></li>
                </ul>
            </nav>
        </div>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('navMenu');

    // Toggle mobile menu
    hamburger.addEventListener('click', function() {
        hamburger.classList.toggle('active');
        navMenu.classList.toggle('mobile-open');
    });

    // Close menu when link is clicked
    const navLinks = navMenu.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            hamburger.classList.remove('active');
            navMenu.classList.remove('mobile-open');
        });
    });

    // Smooth scroll behavior
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const target = this.getAttribute('href');
            if (target.startsWith('#')) {
                e.preventDefault();
                const element = document.querySelector(target);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    });
});
</script>

<div id="page" class="site">
