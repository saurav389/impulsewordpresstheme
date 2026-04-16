<?php
if (!defined('ABSPATH')) {
    exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IMPULSE COMPUTER ACADEMY</title>
    <?php wp_head(); ?>
    
    <!-- Swiper.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    
    <!-- Custom Theme Styles -->
  
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
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary-menu',
                    'menu_class' => 'nav-list',
                    'container' => false,
                
                ));
                ?>
                <!-- <ul class="nav-list">
                    <li><a href="#home" class="nav-link">Home</a></li>
                    <li><a href="/wordpress/courses" class="nav-link">Courses</a></li>
                    <li><a href="#why-us" class="nav-link">Why Us</a></li>
                    <li><a href="#journey" class="nav-link">Journey</a></li>
                    <li><a href="#services" class="nav-link">Services</a></li>
                    <li><a href="#contact" class="nav-link">Contact</a></li>
                </ul> -->
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
