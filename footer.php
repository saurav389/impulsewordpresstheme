</div><!-- #page -->

<!-- Footer Section -->
<footer class="footer-section">
    <style>
        .footer-section {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(245, 158, 11, 0.1));
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 3rem 1rem 1rem;
            margin-top: 4rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        .footer-section-item h4 {
            color: white;
            font-size: 1.25rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .footer-section-item p {
            color: #cbd5e1;
            line-height: 1.625;
        }

        .footer-section-item ul {
            list-style: none;
        }

        .footer-section-item ul li {
            margin-bottom: 0.5rem;
        }

        .footer-section-item ul li a {
            color: #cbd5e1;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-section-item ul li a:hover {
            color: #2563eb;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #64748b;
            max-width: 1200px;
            margin: 0 auto 0 1rem;
        }
    </style>

    <div class="footer-content">
        <!-- About Section -->
        <div class="footer-section-item">
            <h4><?php bloginfo('name'); ?></h4>
            <p><?php bloginfo('description'); ?></p>
        </div>

        <!-- Quick Links -->
        <div class="footer-section-item">
            <h4><?php esc_html_e('Quick Links', 'impulse-academy-clone'); ?></h4>
            <ul>
                <li><a href="#home"><?php esc_html_e('Home', 'impulse-academy-clone'); ?></a></li>
                <li><a href="#courses"><?php esc_html_e('Courses', 'impulse-academy-clone'); ?></a></li>
                <li><a href="#about"><?php esc_html_e('About', 'impulse-academy-clone'); ?></a></li>
                <li><a href="#contact"><?php esc_html_e('Contact', 'impulse-academy-clone'); ?></a></li>
            </ul>
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

<?php wp_footer(); ?>
</body>
</html>
