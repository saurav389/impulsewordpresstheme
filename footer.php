</div><!-- #page -->

<!-- Footer Section -->
<footer class="footer-section">
    <div class="footer-content">
        <!-- About Section -->
        <div class="footer-section-item">
            <h4><?php bloginfo('name'); ?></h4>
            <p><?php bloginfo('description'); ?></p>
            <div class="footer-contact-info">
                <div class="contact-item">
                    <h5>📧 Email</h5>
                    <a href="mailto:impulsebirsanagar@gmail.com">impulsebirsanagar@gmail.com</a>
                </div>
                <div class="contact-item">
                    <h5>📱 Phone</h5>
                    <div>
                        <a href="tel:7979815545">7979815545</a> / <a href="tel:9709034301">9709034301</a>
                    </div>
                </div>
                <div class="contact-item">
                    <h5>📍 Location</h5>
                    <p>Birsa Nagar, Ranchi<br>Jharkhand, India</p>
                </div>
            </div>
        </div>
     
        <!-- Quick Links - WordPress Menu -->
        <div class="footer-section-item">
            <h4><?php esc_html_e('Quick Links', 'impulse-academy-clone'); ?></h4>
            <?php
            wp_nav_menu(array(
                'theme_location' => 'footer-menu',
                'menu' => 'Footer Menu',
                'container' => false,
                'fallback_cb' => function() {
                    echo '<ul>';
                    echo '<li><a href="' . esc_url(home_url('/')) . '">' . esc_html_e('Home', 'impulse-academy-clone') . '</a></li>';
                    echo '<li><a href="' . esc_url(home_url('/courses')) . '">' . esc_html_e('Courses', 'impulse-academy-clone') . '</a></li>';
                    echo '<li><a href="' . esc_url(home_url('/about')) . '">' . esc_html_e('About', 'impulse-academy-clone') . '</a></li>';
                    echo '<li><a href="' . esc_url(home_url('/contact')) . '">' . esc_html_e('Contact', 'impulse-academy-clone') . '</a></li>';
                    echo '</ul>';
                },
                'depth' => 2
            ));
            ?>
        </div>

        <!-- Follow Us -->
        <div class="footer-section-item">
            <h4><?php esc_html_e('Follow Us', 'impulse-academy-clone'); ?></h4>
            <ul>
                <li><a href="#twitter" target="_blank" rel="noopener"><?php esc_html_e('Twitter', 'impulse-academy-clone'); ?></a></li>
                <li><a href="#linkedin" target="_blank" rel="noopener"><?php esc_html_e('LinkedIn', 'impulse-academy-clone'); ?></a></li>
                <li><a href="#facebook" target="_blank" rel="noopener"><?php esc_html_e('Facebook', 'impulse-academy-clone'); ?></a></li>
            </ul>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. <?php esc_html_e('All rights reserved.', 'impulse-academy-clone'); ?></p>
    </div>
</footer>

<!-- Swiper.js JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<!-- GSAP Animation Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

<!-- Premium Hero Initialization -->
<script>
    // Initialize Swiper
    const heroSwiper = new Swiper('.hero-swiper', {
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.hero-slider-dots',
            clickable: true,
            dynamicBullets: true,
        },
        navigation: {
            nextEl: '.hero-slider-next',
            prevEl: '.hero-slider-prev',
        },
        effect: 'fade',
        fadeEffect: {
            crossFade: true,
        },
        speed: 800,
        preventClicks: false,
    });

    // Particle Canvas Animation
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('particleCanvas');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const particles = [];
        const particleCount = 50;

        class Particle {
            constructor() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height;
                this.size = Math.random() * 2 + 1;
                this.speedX = Math.random() * 0.5 - 0.25;
                this.speedY = Math.random() * 0.5 - 0.25;
                this.opacity = Math.random() * 0.5 + 0.2;
                this.color = ['rgba(37, 99, 235', 'rgba(0, 255, 255'][Math.floor(Math.random() * 2)];
            }

            update() {
                this.x += this.speedX;
                this.y += this.speedY;
                
                if (this.x > canvas.width) this.x = 0;
                if (this.x < 0) this.x = canvas.width;
                if (this.y > canvas.height) this.y = 0;
                if (this.y < 0) this.y = canvas.height;

                this.opacity += Math.sin(Date.now() * 0.001) * 0.01;
                if (this.opacity > 0.7) this.opacity = 0.7;
                if (this.opacity < 0.1) this.opacity = 0.1;
            }

            draw() {
                ctx.fillStyle = this.color + ', ' + this.opacity + ')';
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        for (let i = 0; i < particleCount; i++) {
            particles.push(new Particle());
        }

        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            particles.forEach(particle => {
                particle.update();
                particle.draw();
            });

            // Draw connections between particles
            for (let i = 0; i < particles.length; i++) {
                for (let j = i + 1; j < particles.length; j++) {
                    const dx = particles[i].x - particles[j].x;
                    const dy = particles[i].y - particles[j].y;
                    const distance = Math.sqrt(dx * dx + dy * dy);

                    if (distance < 100) {
                        ctx.strokeStyle = 'rgba(0, 255, 255, ' + (0.15 - distance / 700) + ')';
                        ctx.lineWidth = 1;
                        ctx.beginPath();
                        ctx.moveTo(particles[i].x, particles[i].y);
                        ctx.lineTo(particles[j].x, particles[j].y);
                        ctx.stroke();
                    }
                }
            }

            requestAnimationFrame(animate);
        }

        animate();

        // Handle window resize
        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        });
    });

    // Typing Text Animation
    document.addEventListener('DOMContentLoaded', function() {
        const typingItems = document.querySelectorAll('.typing-item');
        let currentIndex = 0;

        function nextText() {
            typingItems.forEach((item, index) => {
                item.classList.remove('active');
            });

            typingItems[currentIndex].classList.add('active');
            currentIndex = (currentIndex + 1) % typingItems.length;

            setTimeout(nextText, 4000);
        }

        nextText();
    });

    // Mouse Parallax Effect for Hero
    document.addEventListener('DOMContentLoaded', function() {
        const heroLeft = document.querySelector('.hero-left');
        const heroRight = document.querySelector('.hero-right');

        if (heroLeft && heroRight) {
            document.addEventListener('mousemove', (e) => {
                const x = e.clientX / window.innerWidth;
                const y = e.clientY / window.innerHeight;

                const moveX = (x - 0.5) * 20;
                const moveY = (y - 0.5) * 20;

                heroLeft.style.transform = `translateY(${moveY * 0.5}px)`;
                heroRight.style.transform = `translateY(${moveY * 0.3}px)`;
            });
        }
    });

    // Smooth scroll behavior enhancements
    document.addEventListener('DOMContentLoaded', function() {
        const links = document.querySelectorAll('a[href^="#"]');
        
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href !== '#' && document.querySelector(href)) {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    });

    // Premium button ripple effect
    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.btn-glow, .btn-glass');
        
        buttons.forEach(button => {
            button.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                ripple.style.position = 'absolute';
                ripple.style.borderRadius = '50%';
                ripple.style.background = 'radial-gradient(circle, rgba(255,255,255,0.8), transparent)';
                ripple.style.pointerEvents = 'none';
                ripple.style.opacity = '0.7';
                
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.style.width = ripple.style.height = size + 'px';
                
                this.appendChild(ripple);
                
                ripple.animate([
                    { transform: 'scale(0)', opacity: 0.8 },
                    { transform: 'scale(4)', opacity: 0 }
                ], {
                    duration: 600,
                    easing: 'ease-out'
                });
                
                setTimeout(() => ripple.remove(), 600);
            });
        });
    });
</script>

<style>
/* ===== Footer Section Styles ===== */
.footer-section {
    background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(30, 41, 59, 0.95) 100%);
    border-top: 1px solid rgba(0, 255, 255, 0.1);
    padding: 60px 20px 20px;
    position: relative;
    overflow: hidden;
}

.footer-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, #00ffff, transparent);
    opacity: 0.5;
}

/* Footer Content Grid */
.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 40px;
    padding: 40px 0;
}

/* Footer Section Items */
.footer-section-item {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.footer-section-item h4 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #ffffff;
    margin: 0;
    position: relative;
    padding-bottom: 10px;
}

.footer-section-item h4::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 30px;
    height: 2px;
    background: linear-gradient(90deg, #00ffff, #2563eb);
    border-radius: 2px;
}

.footer-section-item p {
    color: #cbd5e1;
    font-size: 0.95rem;
    line-height: 1.6;
    margin: 0;
}

/* Contact Info Section */
.footer-contact-info {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-top: 10px;
}

.contact-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.contact-item h5 {
    font-size: 0.95rem;
    color: #00ffff;
    margin: 0;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.contact-item a {
    color: #cbd5e1;
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    display: inline-block;
    width: fit-content;
}

.contact-item a:hover {
    color: #00ffff;
    transform: translateX(5px);
}

.contact-item p {
    color: #cbd5e1;
    font-size: 0.9rem;
    margin: 0;
    line-height: 1.5;
}

/* ===== Footer Menu Styles ===== */
.footer-section-item ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 0;
}

.footer-section-item ul li {
    margin: 0;
    list-style: none;
}

.footer-section-item ul li a {
    color: #cbd5e1;
    text-decoration: none;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    display: block;
    padding: 8px 0 8px 0;
    border-left: 2px solid transparent;
    padding-left: 12px;
}

.footer-section-item ul li a:hover {
    color: #00ffff;
    border-left-color: #00ffff;
    padding-left: 16px;
}

/* WordPress Menu Navigation */
.footer-section-item .menu {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 0;
}

.footer-section-item .menu li {
    margin: 0;
    list-style: none;
}

.footer-section-item .menu li a {
    color: #cbd5e1;
    text-decoration: none;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    display: block;
    padding: 8px 0 8px 0;
    border-left: 2px solid transparent;
    padding-left: 12px;
}

.footer-section-item .menu li a:hover {
    color: #00ffff;
    border-left-color: #00ffff;
    padding-left: 16px;
}

/* Sub-menu styles */
.footer-section-item .menu li ul,
.footer-section-item ul ul {
    list-style: none;
    padding-left: 0;
    margin-top: 0;
    margin-left: 0;
    display: flex;
    flex-direction: column;
    gap: 0;
}

.footer-section-item .menu li ul li,
.footer-section-item ul ul li {
    margin: 0;
    list-style: none;
}

.footer-section-item .menu li ul li a,
.footer-section-item ul ul li a {
    font-size: 0.85rem;
    color: #94a3b8;
    padding: 6px 0 6px 24px;
    border-left: 2px solid transparent;
}

.footer-section-item .menu li ul li a:hover,
.footer-section-item ul ul li a:hover {
    color: #00ffff;
    border-left-color: #00ffff;
    padding-left: 28px;
}

/* ===== Footer Bottom ===== */
.footer-bottom {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px 0;
    border-top: 1px solid rgba(0, 255, 255, 0.1);
    text-align: center;
}

.footer-bottom p {
    color: #94a3b8;
    font-size: 0.9rem;
    margin: 0;
    line-height: 1.6;
}

/* ===== Responsive Design ===== */
@media (max-width: 768px) {
    .footer-section {
        padding: 40px 15px 15px;
    }

    .footer-content {
        grid-template-columns: 1fr;
        gap: 30px;
        padding: 30px 0;
    }

    .footer-section-item h4 {
        font-size: 1.1rem;
    }

    .footer-contact-info {
        gap: 15px;
    }

    .contact-item h5 {
        font-size: 0.85rem;
    }

    .footer-section-item ul li a,
    .footer-section-item .menu li a {
        font-size: 0.9rem;
        padding: 7px 0 7px 10px;
    }

    .footer-section-item ul li a:hover,
    .footer-section-item .menu li a:hover {
        padding-left: 14px;
    }
}

@media (max-width: 480px) {
    .footer-section {
        padding: 30px 12px 12px;
    }

    .footer-content {
        gap: 25px;
        padding: 25px 0;
    }

    .footer-section-item h4 {
        font-size: 1rem;
    }

    .footer-section-item h4::after {
        width: 20px;
    }

    .footer-bottom {
        padding: 15px 0;
    }

    .footer-bottom p {
        font-size: 0.8rem;
    }
}
</style>

<?php wp_footer(); ?>
</body>
</html>
