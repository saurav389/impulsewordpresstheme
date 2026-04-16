<?php get_header(); ?>

<main id="primary" class="site-main" role="main">
    <h1 class="screen-reader-text">IMPULSE COMPUTER ACADEMY</h1>

  

    <!-- Announcement Banner -->
  

    <!-- Premium Hero Section -->
    <section class="premium-hero" id="home">
        <!-- Animated Background -->
        <div class="hero-background">
            <div class="gradient-mesh"></div>
            <div class="floating-orb orb-1"></div>
            <div class="floating-orb orb-2"></div>
            <div class="floating-orb orb-3"></div>
            <canvas id="particleCanvas"></canvas>
        </div>

        <!-- Floating Tech Icons -->
        <div class="floating-icons">
            <div class="tech-icon icon-1">⚛️</div>
            <div class="tech-icon icon-2">🐍</div>
            <div class="tech-icon icon-3">☕</div>
            <div class="tech-icon icon-4">⬜</div>
        </div>

        <div class="hero-container">
            <!-- Left Content -->
            <div class="hero-left">
                <!-- Top Badges -->
                <div class="hero-badges-top">
                    <span class="badge-item badge-green">🟢 Admissions Open 2026!</span>
                    <span class="badge-item badge-orange">⚡ Limited Seats</span>
                    <span class="badge-item badge-purple">🎓 Free Demo Class</span>
                </div>

                <!-- Main Heading -->
                <h1 class="hero-main-title">
                    <span>Impulse Computer</span>
                    <span class="text-cyan">Academy</span>
                </h1>

                <!-- Tagline -->
                <p class="hero-tagline">
                    Learn Coding, Tally, Full Stack & AI Skills<br>
                    <span class="text-cyan">With Internship + 100% Placement</span>
                </p>

                <!-- Learning Text -->
                <p class="hero-learn-text"><span class="text-cyan">Learn:</span> Data Science</p>

                <!-- Feature Boxes Grid -->
                <div class="feature-boxes-grid">
                    <div class="feature-box">
                        <span class="feature-icon">👨‍💼</span>
                        <span class="feature-text">Expert Mentors</span>
                    </div>
                    <div class="feature-box">
                        <span class="feature-icon">✅</span>
                        <span class="feature-text">100% Placement</span>
                    </div>
                    <div class="feature-box">
                        <span class="feature-icon">💼</span>
                        <span class="feature-text">Internship Programs</span>
                    </div>
                    <div class="feature-box">
                        <span class="feature-icon">🛠️</span>
                        <span class="feature-text">Hands-on Projects</span>
                    </div>
                </div>

                <!-- CTA Buttons -->
                <div class="hero-buttons-new">
                    <button class="btn-enroll" onclick="document.getElementById('whatsapp-widget').click();">
                        Enroll Now →
                    </button>
                    <button class="btn-explore" onclick="document.getElementById('courses').scrollIntoView({behavior: 'smooth'});">
                        Explore Courses →
                    </button>
                </div>

                <!-- Modern CTA Section -->
                <div class="hero-cta-section">
                    <p class="cta-tagline">Questions? Reach out to us!</p>
                    <div class="cta-buttons-group">
                        <a href="tel:7979815545" class="cta-btn cta-phone">
                            <span class="cta-icon">📞</span>
                            <div class="cta-content">
                                <span class="cta-label">Call Now</span>
                                <span class="cta-value">7979815545</span>
                            </div>
                        </a>
                        <a href="https://wa.me/919709034301" target="_blank" class="cta-btn cta-whatsapp">
                            <span class="cta-icon">💬</span>
                            <div class="cta-content">
                                <span class="cta-label">WhatsApp</span>
                                <span class="cta-value">9709034301</span>
                            </div>
                        </a>
                        <a href="mailto:impulsebirsanagar@gmail.com" class="cta-btn cta-email">
                            <span class="cta-icon">📧</span>
                            <div class="cta-content">
                                <span class="cta-label">Email Us</span>
                                <span class="cta-value">Send Message</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Side - Premium Slider -->
            <div class="hero-right">
                <div class="slider-container glass-effect">
                    <!-- Swiper Slider -->
                    <div class="swiper hero-swiper">
                        <div class="swiper-wrapper">
                            <!-- Slide 1: Coding Classroom -->
                            <div class="swiper-slide hero-slide">
                                <div class="slide-background" style="background: linear-gradient(135deg, rgba(37, 99, 235, 0.3), rgba(0, 255, 255, 0.2));">
                                    <img src="https://images.pexels.com/photos/3184325/pexels-photo-3184325.jpeg" alt="Modern Classroom" class="slide-image">
                                    <div class="slide-overlay"></div>
                                </div>
                                <div class="slide-content">
                                    <h3 class="slide-title">Modern Classroom</h3>
                                    <p class="slide-desc">State-of-the-art training facilities with latest technology</p>
                                </div>
                            </div>

                            <!-- Slide 2: Students Learning -->
                            <div class="swiper-slide hero-slide">
                                <div class="slide-background" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.3), rgba(0, 255, 255, 0.2));">
                                    <img src="https://images.pexels.com/photos/5483248/pexels-photo-5483248.jpeg" alt="Expert Training" class="slide-image">
                                    <div class="slide-overlay"></div>
                                </div>
                                <div class="slide-content">
                                    <h3 class="slide-title">Expert Training</h3>
                                    <p class="slide-desc">Learn from industry professionals with years of experience</p>
                                </div>
                            </div>

                            <!-- Slide 3: Placement Success -->
                            <div class="swiper-slide hero-slide">
                                <div class="slide-background" style="background: linear-gradient(135deg, rgba(34, 197, 94, 0.3), rgba(0, 255, 255, 0.2));">
                                    <img src="https://images.pexels.com/photos/6289065/pexels-photo-6289065.jpeg" alt="Career Growth" class="slide-image">
                                    <div class="slide-overlay"></div>
                                </div>
                                <div class="slide-content">
                                    <h3 class="slide-title">Career Growth</h3>
                                    <p class="slide-desc">100% placement assistance with top companies</p>
                                </div>
                            </div>

                            <!-- Slide 4: Internship Projects -->
                            <div class="swiper-slide hero-slide">
                                <div class="slide-background" style="background: linear-gradient(135deg, rgba(168, 85, 247, 0.3), rgba(0, 255, 255, 0.2));">
                                    <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=800&h=600&fit=crop" alt="Internship Programs" class="slide-image">
                                    <div class="slide-overlay"></div>
                                </div>
                                <div class="slide-content">
                                    <h3 class="slide-title">Internship Programs</h3>
                                    <p class="slide-desc">Real-world projects with hands-on experience</p>
                                </div>
                            </div>

                            <!-- Slide 5: Tech Lab -->
                            <div class="swiper-slide hero-slide">
                                <div class="slide-background" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.3), rgba(0, 255, 255, 0.2));">
                                    <img src="https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=800&h=600&fit=crop" alt="Advanced Lab" class="slide-image">
                                    <div class="slide-overlay"></div>
                                </div>
                                <div class="slide-content">
                                    <h3 class="slide-title">Advanced Lab</h3>
                                    <p class="slide-desc">Cutting-edge technology and tools for modern development</p>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation -->
                        <div class="swiper-button-prev hero-slider-prev"></div>
                        <div class="swiper-button-next hero-slider-next"></div>
                        <div class="swiper-pagination hero-slider-dots"></div>
                    </div>

                    <!-- Slider Overlay -->
                    <div class="slider-overlay"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- WhatsApp Float Button -->
    <a href="https://wa.me/919709034301" target="_blank" class="whatsapp-float" id="whatsapp-widget" title="Chat on WhatsApp">
        <span class="whatsapp-icon">💬</span>
    </a>

    <!-- Courses Section -->
    <section class="courses-section section-padding" id="courses">
        <div class="container">
            <h2 class="section-title">Master In-Demand Skills</h2>
            <div class="courses-grid">
                <?php
                // Query all courses from custom post type
                $args = array(
                    'post_type' => 'courses',
                    'posts_per_page' => -1,
                    'orderby' => 'date',
                    'order' => 'DESC'
                );
                
                $courses_query = new WP_Query($args);
                
                if ($courses_query->have_posts()) {
                    while ($courses_query->have_posts()) {
                        $courses_query->the_post();
                        
                        // Get ACF Fields
                        $course_image = get_field('course_image');
                        $course_level = get_field('course_level');
                        $duration = get_field('course_duration');
                        $fee = get_field('course_fee');
                        $students = get_field('students');
                        $course_title = get_the_title();
                        
                        // Get course URL
                        $course_url = get_permalink();
                ?>
                    <div class="course-card">
                        <div class="course-image-container">
                            <?php if ($course_image): ?>
                                <img src="<?php echo esc_url(is_array($course_image) ? $course_image['url'] : $course_image); ?>" alt="<?php echo esc_attr($course_title); ?>" class="course-image">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/500x300?text=<?php echo urlencode($course_title); ?>" alt="<?php echo esc_attr($course_title); ?>" class="course-image">
                            <?php endif; ?>
                            <?php if ($course_level): ?>
                                <span class="course-badge"><?php echo esc_html($course_level); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="course-card-header">
                            <h3><?php echo esc_html($course_title); ?></h3>
                            <span class="course-icon">💻</span>
                        </div>
                        <div class="course-meta">
                            <?php if ($duration): ?>
                                <div class="meta-item">
                                    <span class="meta-label">Duration</span>
                                    <span class="meta-value"><?php echo esc_html($duration); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($fee): ?>
                                <div class="meta-item">
                                    <span class="meta-label">Fee</span>
                                    <span class="meta-value"><?php echo esc_html($fee); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <p class="course-description"><?php echo wp_trim_words(get_the_excerpt() ? get_the_excerpt() : get_the_content(), 15, '...'); ?></p>
                        <div class="course-stats">
                            <?php if ($students): ?>
                                <span class="student-count">👥 <?php echo esc_html($students); ?> students</span>
                            <?php endif; ?>
                            <?php if ($course_level): ?>
                                <span class="course-level"><?php echo esc_html($course_level); ?></span>
                            <?php endif; ?>
                        </div>
                        <a href="<?php echo esc_url($course_url); ?>" class="btn-primary">View Details →</a>
                    </div>
                <?php
                    }
                    wp_reset_postdata();
                } else {
                    echo '<p style="grid-column: 1 / -1; text-align: center; color: #cbd5e1; padding: 2rem;">No courses available at the moment.</p>';
                }
                ?>
            </div>

            <div class="view-all-btn">
                <a href="<?php echo site_url('/courses'); ?>" class="btn-secondary" style="text-decoration: none; display: inline-block;">View All Courses</a>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-us-section section-padding" id="why-us">
        <div class="container">
            <h2 class="section-title">What Makes Us Different</h2>
            
            <div class="features-grid">
                <div class="feature-card">
                    <span class="feature-emoji">👨‍🏫</span>
                    <h3>Industry Expert Mentors</h3>
                    <p>Learn from professionals with 10+ years of industry experience</p>
                </div>

                <div class="feature-card">
                    <span class="feature-emoji">🚀</span>
                    <h3>100% Placement Guarantee</h3>
                    <p>We guarantee job placement or full money back</p>
                </div>

                <div class="feature-card">
                    <span class="feature-emoji">💡</span>
                    <h3>Internship Opportunities</h3>
                    <p>Get real-world experience before graduation</p>
                </div>

                <div class="feature-card">
                    <span class="feature-emoji">💰</span>
                    <h3>Practical Training</h3>
                    <p>80% practical, 20% theory approach for hands-on learning</p>
                </div>

                <div class="feature-card">
                    <span class="feature-emoji">💵</span>
                    <h3>Affordable Fees</h3>
                    <p>Premium education at budget-friendly prices</p>
                </div>

                <div class="feature-card">
                    <span class="feature-emoji">🏆</span>
                    <h3>Certification Program</h3>
                    <p>Industry-recognized certificates valued by top companies</p>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">5000+</div>
                    <div class="stat-label">Students Trained</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">4500+</div>
                    <div class="stat-label">Placements Done</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">10+</div>
                    <div class="stat-label">Years in Business</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">98%</div>
                    <div class="stat-label">Satisfaction Rate</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Journey Section -->
    <section class="journey-section section-padding" id="journey">
        <div class="container">
            <h2 class="section-title">Your Tech Career Journey</h2>
            <p style="text-align: center; color: #cbd5e1; margin-bottom: 3rem; max-width: 600px; margin-left: auto; margin-right: auto;">Follow our proven 5-step process to transform from a beginner into a job-ready professional</p>
            
            <div class="journey-steps">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <span class="step-emoji">📝</span>
                    <h3>Enrollment</h3>
                    <p>Start your tech journey with us. Select your course and begin your transformation.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">2</div>
                    <span class="step-emoji">📚</span>
                    <h3>Learning</h3>
                    <p>Learn from industry experts with hands-on projects, real-world case studies, and mentorship.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">3</div>
                    <span class="step-emoji">💻</span>
                    <h3>Projects</h3>
                    <p>Build portfolio-ready projects that showcase your skills to potential employers.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">4</div>
                    <span class="step-emoji">💼</span>
                    <h3>Internship</h3>
                    <p>Gain real-world experience through internship programs with leading companies.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">5</div>
                    <span class="step-emoji">🚀</span>
                    <h3>Placement</h3>
                    <p>Get placed in your dream job with our 100% placement guarantee and career support.</p>
                </div>
            </div>

            <div class="journey-cta">
                <h3>Ready to start your transformation journey?</h3>
                <button class="btn-primary" style="padding: 0.9rem 2rem; font-size: 1rem; margin-top: 1rem;">Begin Your Journey Today</button>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services-section section-padding" id="services">
        <div class="container">
            <h2 class="section-title">Complete Learning Ecosystem</h2>
            
            <div class="services-grid">
                <div class="service-card">
                    <span class="service-emoji">📚</span>
                    <h3>Computer Courses</h3>
                    <p>Comprehensive training in DCA, ADCA, and specialized programming languages</p>
                    <a href="#courses" class="btn-text">Learn More →</a>
                </div>

                <div class="service-card">
                    <span class="service-emoji">🤝</span>
                    <h3>Internship Program</h3>
                    <p>Get hands-on experience with real projects in leading companies</p>
                    <a href="#" class="btn-text">Learn More →</a>
                </div>

                <div class="service-card">
                    <span class="service-emoji">💼</span>
                    <h3>Placement Support</h3>
                    <p>100% placement guarantee with interview preparation and job assistance</p>
                    <a href="#" class="btn-text">Learn More →</a>
                </div>

                <div class="service-card">
                    <span class="service-emoji">📜</span>
                    <h3>Certification</h3>
                    <p>Industry-recognized certificates that boost your resume</p>
                    <a href="#" class="btn-text">Learn More →</a>
                </div>

                <div class="service-card">
                    <span class="service-emoji">🔬</span>
                    <h3>Live Projects</h3>
                    <p>Work on real-world projects to build a strong portfolio</p>
                    <a href="#" class="btn-text">Learn More →</a>
                </div>

                <div class="service-card">
                    <span class="service-emoji">🎓</span>
                    <h3>Flexible Learning</h3>
                    <p>Online, offline, or hybrid courses tailored to your schedule</p>
                    <a href="#" class="btn-text">Learn More →</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section section-padding">
        <div class="container">
            <h2 class="section-title">What Our Students Say</h2>
            
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-rating">★★★★★</div>
                    <p class="testimonial-text">"The quality of instruction and hands-on projects were exceptional. The mentors are never in a rush to explain concepts. Best investment I made for my career!"</p>
                    <div class="testimonial-author">
                        <img src="https://i.pravatar.cc/150?img=2" alt="Prya Singh" class="author-avatar">
                        <div class="author-info">
                            <h4>Prya Singh</h4>
                            <p class="author-course">MERN Stack</p>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-rating">★★★★★</div>
                    <p class="testimonial-text">"Coming from a non-tech background, I was scared. But the trainers made everything so easy to understand. Now I am freelancing as a Python developer."</p>
                    <div class="testimonial-author">
                        <img src="https://i.pravatar.cc/150?img=3" alt="Raj Patel" class="author-avatar">
                        <div class="author-info">
                            <h4>Raj Patel</h4>
                            <p class="author-course">Python</p>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-rating">★★★★★</div>
                    <p class="testimonial-text">"The placement support team was incredible. They helped me prepare for interviews and connected me with job opportunities. I got placed within 2 months of completion!"</p>
                    <div class="testimonial-author">
                        <img src="https://i.pravatar.cc/150?img=5" alt="Anjali Verma" class="author-avatar">
                        <div class="author-info">
                            <h4>Anjali Verma</h4>
                            <p class="author-course">Full Stack Development</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section section-padding" id="contact">
        <div class="container">
            <h2 class="section-title">Contact Us</h2>
            
            <div class="contact-form">
                <form method="post" onsubmit="handleContactForm(event)">
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Full Name" class="form-input" required>
                        <input type="email" name="email" placeholder="Email Address" class="form-input" required>
                        <input type="tel" name="phone" placeholder="Phone Number" class="form-input" required>
                        <select name="course" class="form-input" required>
                            <option value="" disabled selected>Select Course</option>
                            <option value="dca">DCA</option>
                            <option value="adca">ADCA</option>
                            <option value="python">Python</option>
                            <option value="java">Java</option>
                            <option value="mern">MERN Stack</option>
                            <option value="full-stack">Full Stack Development</option>
                            <option value="c">C Programming</option>
                            <option value="cpp">C++ Programming</option>
                            <option value="dtp">DTP</option>
                            <option value="tally">Tally Prime</option>
                        </select>
                        <textarea name="message" placeholder="Message" class="form-input" required></textarea>
                    </div>
                    <button type="submit" class="btn-primary">Send Message</button>
                </form>
            </div>

            
        </div>
    </section>

</main>

<script>
// Form Validation and Submission
function handleContactForm(event) {
    event.preventDefault();
    
    const form = event.target;
    const name = form.querySelector('input[name="name"]').value.trim();
    const email = form.querySelector('input[name="email"]').value.trim();
    const phone = form.querySelector('input[name="phone"]').value.trim();
    const course = form.querySelector('select[name="course"]').value;
    const message = form.querySelector('textarea[name="message"]').value.trim();
    
    // Validation
    if (!name || !email || !phone || !course || !message) {
        alert('Please fill in all fields');
        return;
    }
    
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Please enter a valid email address');
        return;
    }
    
    // Phone validation (basic Indian format)
    const phoneRegex = /^\d{10}$/;
    if (!phoneRegex.test(phone.replace(/[\s-]/g, ''))) {
        alert('Please enter a valid 10-digit phone number');
        return;
    }
    
    // Success message
    alert('Thank you for your inquiry! Our team will contact you within 24 hours.');
    form.reset();
}

// Intersection Observer for scroll animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

// Observe all course cards and feature cards on load
document.addEventListener('DOMContentLoaded', function() {
    const courseCards = document.querySelectorAll('.course-card');
    const featureCards = document.querySelectorAll('.feature-card, .service-card, .testimonial-card, .stat-card, .step-card');
    
    courseCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
    
    featureCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });

    // Animate stats counters
    animateCounters();
});

// Counter animation for statistics
function animateCounters() {
    const statNumbers = document.querySelectorAll('.stat-number');
    
    statNumbers.forEach(stat => {
        const target = parseInt(stat.textContent);
        const increment = target / 100;
        let current = 0;
        
        const counter = setInterval(() => {
            current += increment;
            if (current >= target) {
                stat.textContent = stat.textContent.replace(/\d+/g, target);
                clearInterval(counter);
            } else {
                const displayValue = Math.floor(current);
                stat.textContent = stat.textContent.replace(/\d+/, displayValue);
            }
        }, 20);
    });
}

// Enhanced button interactions
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.btn-primary, .btn-secondary');
    
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
        
        button.addEventListener('click', function() {
            // Ripple effect
            const ripple = document.createElement('span');
            ripple.style.position = 'absolute';
            ripple.style.pointerEvents = 'none';
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = event.clientX - rect.left - size / 2;
            const y = event.clientY - rect.top - size / 2;
            
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.borderRadius = '50%';
            ripple.style.background = 'rgba(255, 255, 255, 0.5)';
            ripple.style.animation = 'ripple 0.6s ease-out';
            
            this.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    });
});

// Add ripple animation
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        0% {
            transform: scale(0);
            opacity: 1;
        }
        100% {
            transform: scale(4);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Smooth scroll for all anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

// Scroll to top functionality
document.addEventListener('scroll', function() {
    const scrollToTopBtn = document.getElementById('scrollToTop');
    if (scrollToTopBtn) {
        if (window.pageYOffset > 300) {
            scrollToTopBtn.style.display = 'block';
        } else {
            scrollToTopBtn.style.display = 'none';
        }
    }
});

// Add scroll-to-top button functionality if element exists
function createScrollToTopButton() {
    const btn = document.createElement('button');
    btn.id = 'scrollToTop';
    btn.style.cssText = `
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2563eb, #f59e0b);
        color: white;
        border: none;
        cursor: pointer;
        font-size: 1.5rem;
        display: none;
        z-index: 999;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
    `;
    btn.innerHTML = '↑';
    btn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    btn.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.1)';
    });
    btn.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
    });
    document.body.appendChild(btn);
}

createScrollToTopButton();

// Hover effect for cards
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.course-card, .feature-card, .service-card, .testimonial-card');
    
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.borderColor = 'rgba(0, 255, 255, 0.5)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.borderColor = 'rgba(255, 255, 255, 0.1)';
        });
    });
});

// Handle form select styling
document.addEventListener('DOMContentLoaded', function() {
    const selects = document.querySelectorAll('.form-input[type="select"], select.form-input');
    selects.forEach(select => {
        select.addEventListener('focus', function() {
            this.style.borderColor = '#2563eb';
            this.style.boxShadow = '0 0 0 3px rgba(37, 99, 235, 0.1)';
        });
        
        select.addEventListener('blur', function() {
            this.style.borderColor = 'rgba(255, 255, 255, 0.1)';
            this.style.boxShadow = 'none';
        });
    });
});
</script>

<?php get_footer(); ?>
