<?php get_header(); ?>
<?php
// Template Name: Contact
?>

<!-- Contact Page Hero Section -->
<section class="contact-hero">
    <div class="container">
        <div class="contact-hero-content">
            <div class="hero-text">
                <span class="badge">Get In Touch</span>
                <h1 class="hero-title">Let's Connect and <span class="gradient-text-neon">Start Your Journey</span></h1>
                <p class="hero-subtitle">Have questions about our courses or services? Our team is here to help! Reach out to us anytime and we'll get back to you within 24 hours.</p>
                <div class="hero-quick-info">
                    <div class="quick-info-item">
                        <span class="info-icon">📞</span>
                        <div>
                            <p class="info-label">Call Us</p>
                            <p class="info-value">+91 7979815545</p>
                        </div>
                    </div>
                    <div class="quick-info-item">
                        <span class="info-icon">📧</span>
                        <div>
                            <p class="info-label">Email Us</p>
                            <p class="info-value">info@impulsecomputeracademy.com</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hero-visual">
                <form class="contact-form glass-premium" id="contactForm">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" required placeholder="John Doe">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required placeholder="john@example.com">
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject *</label>
                        <select id="subject" name="subject" required>
                            <option value="">Select a Subject</option>
                            <option value="course-info">Course Information</option>
                            <option value="admission">Admission Inquiry</option>
                            <option value="corporate">Corporate Training</option>
                            <option value="internship">Internship Program</option>
                            <option value="technical">Technical Support</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message">Message *</label>
                        <textarea id="message" name="message" rows="4" required placeholder="Tell us more about your inquiry..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-large">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Contact Info Cards -->
<section class="contact-info-section section-padding">
    <div class="container">
        <div class="contact-info-grid">
            <div class="info-card glass-premium">
                <div class="info-icon-large">📍</div>
                <h3>Our Location</h3>
                <p class="info-text">Impulse Computer Academy <br>Jamshedpur, Jharkhand<br>India - 400001</p>
                <a href="#" class="info-link">View on Map →</a>
            </div>

            <div class="info-card glass-premium">
                <div class="info-icon-large">📞</div>
                <h3>Phone</h3>
                <p class="info-text">Main: +91 7979815545<br>Support: +91 9709034301<br>Office Hours: 9 AM - 8 PM</p>
                <a href="tel:+917979815545" class="info-link">Call Now →</a>
            </div>

            <div class="info-card glass-premium">
                <div class="info-icon-large">📧</div>
                <h3>Email</h3>
                <p class="info-text">General: info@impulsecomputeracademy.com<br>Support: support@impulsecomputeracademy.com<br>Careers: careers@impulsecomputeracademy.com</p>
                <a href="mailto:info@impulsecomputeracademy.com" class="info-link">Email Us →</a>
            </div>
        </div>
    </div>
</section>


<!-- FAQ Section -->
<section class="faq-section section-padding">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Frequently Asked <span class="gradient-text">Questions</span></h2>
            <p class="section-subtitle">Find answers to common questions</p>
        </div>

        <div class="faq-grid">
            <div class="faq-item glass">
                <div class="faq-question">
                    <h3>How do I apply for a course?</h3>
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-answer">
                    <p>You can apply directly through our website or contact our admissions team. We'll guide you through the enrollment process and help you choose the best course based on your goals and background.</p>
                </div>
            </div>

            <div class="faq-item glass">
                <div class="faq-question">
                    <h3>What is the course duration?</h3>
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-answer">
                    <p>Course durations vary from 3 weeks for workshops to 6 months for comprehensive programs. We also offer flexible scheduling with weekend and online options to fit your lifestyle.</p>
                </div>
            </div>

            <div class="faq-item glass">
                <div class="faq-question">
                    <h3>Do you offer certifications?</h3>
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-answer">
                    <p>Yes! All our courses come with industry-recognized certifications upon completion. These credentials are valued by employers worldwide and boost your job prospects significantly.</p>
                </div>
            </div>

            <div class="faq-item glass">
                <div class="faq-question">
                    <h3>What is your placement rate?</h3>
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-answer">
                    <p>We have a 98% successful placement and career outcome rate. Our dedicated placement team works with hundreds of companies to secure positions for our graduates.</p>
                </div>
            </div>

            <div class="faq-item glass">
                <div class="faq-question">
                    <h3>Is financial assistance available?</h3>
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-answer">
                    <p>Yes, we offer flexible payment plans, scholarships for deserving candidates, and EMI options. Contact our admissions team to discuss what's available for your situation.</p>
                </div>
            </div>

            <div class="faq-item glass">
                <div class="faq-question">
                    <h3>Can I join if I'm a beginner?</h3>
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-answer">
                    <p>Absolutely! We have courses for all skill levels - beginners, intermediate, and advanced. Our curriculum is designed to take you from zero to job-ready in any technology.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Benefits of Contacting Us -->
<section class="benefits-section section-padding gradient-bg">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Why Contact <span class="gradient-text">Us Today</span></h2>
            <p class="section-subtitle">What you'll gain from reaching out</p>
        </div>

        <div class="benefits-grid">
            <div class="benefit-card glass-premium">
                <div class="benefit-number">1</div>
                <h3>Free Consultation</h3>
                <p>Get personalized advice from our experienced counselors about the best course for your goals</p>
            </div>

            <div class="benefit-card glass-premium">
                <div class="benefit-number">2</div>
                <h3>Course Roadmap</h3>
                <p>Receive a customized learning path tailored to your skill level and career objectives</p>
            </div>

            <div class="benefit-card glass-premium">
                <div class="benefit-number">3</div>
                <h3>Special Offers</h3>
                <p>Get exclusive discounts and offers available only to direct inquiries from our website</p>
            </div>

            <div class="benefit-card glass-premium">
                <div class="benefit-number">4</div>
                <h3>Demo Session</h3>
                <p>Experience a free demo class to see our teaching style and course content firsthand</p>
            </div>

            <div class="benefit-card glass-premium">
                <div class="benefit-number">5</div>
                <h3>One-on-One Chat</h3>
                <p>Connect with our advisors for detailed discussions about your learning goals and concerns</p>
            </div>

            <div class="benefit-card glass-premium">
                <div class="benefit-number">6</div>
                <h3>Fast Response</h3>
                <p>Get replies to your inquiries within 24 hours with dedicated support from our team</p>
            </div>
        </div>
    </div>
</section>

<!-- Location Map Section -->
<section class="map-section section-padding">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Visit Our <span class="gradient-text">Training Center</span></h2>
            <p class="section-subtitle">Located in the heart of Mumbai, easily accessible from all major areas</p>
        </div>

        <div class="map-wrapper glass">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3770.8648765267356!2d72.82479!3d19.07283!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be7d08e3e5e5e5d%3A0x5e5e5e5e5e5e5e5e!2sMumbai%2C%20Maharashtra!5e0!3m2!1sen!2sin!4v1234567890"
                    width="100%" height="400" style="border:none; border-radius: 12px;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>

        <div class="map-info-grid">
            <div class="map-info-card glass">
                <h3>🚇 By Metro</h3>
                <p>Just 5 minutes walk from Central Station. Exit towards Market Road area.</p>
            </div>
            <div class="map-info-card glass">
                <h3>🚍 By Bus</h3>
                <p>Multiple bus routes available. Stop near Town Hall, walking distance to center.</p>
            </div>
            <div class="map-info-card glass">
                <h3>🅿️ Parking</h3>
                <p>Free parking available in the basement. Valid ID required for entry.</p>
            </div>
        </div>
    </div>
</section>

<!-- Social Media Section -->
<section class="social-section section-padding gradient-bg">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Connect With <span class="gradient-text">Us Online</span></h2>
            <p class="section-subtitle">Follow us for updates, tips, and success stories</p>
        </div>

        <div class="social-buttons">
            <a href="#" class="social-btn glass-premium" target="_blank">
                <span class="social-icon">f</span>
                <span class="social-name">Facebook</span>
            </a>
            <a href="#" class="social-btn glass-premium" target="_blank">
                <span class="social-icon">in</span>
                <span class="social-name">LinkedIn</span>
            </a>
            <a href="#" class="social-btn glass-premium" target="_blank">
                <span class="social-icon">𝕏</span>
                <span class="social-name">Twitter</span>
            </a>
            <a href="#" class="social-btn glass-premium" target="_blank">
                <span class="social-icon">▶</span>
                <span class="social-name">YouTube</span>
            </a>
            <a href="#" class="social-btn glass-premium" target="_blank">
                <span class="social-icon">📷</span>
                <span class="social-name">Instagram</span>
            </a>
            <a href="#" class="social-btn glass-premium" target="_blank">
                <span class="social-icon">💬</span>
                <span class="social-name">WhatsApp</span>
            </a>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section section-padding">
    <div class="container">
        <div class="cta-content glass-premium">
            <div class="cta-text">
                <h2>Ready to Take the Next <span class="gradient-text-neon">Step</span>?</h2>
                <p>Don't wait! Reach out to our team today for a free consultation and discover how we can help you achieve your career goals.</p>
            </div>
            <div class="cta-buttons">
                <a href="#contactForm" class="btn btn-primary btn-large">Send a Message</a>
                <a href="tel:+919876543210" class="btn btn-secondary btn-large">Call Us Now</a>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>

<style>
/* Contact Page Specific Styles */

/* Hero Section */
.contact-hero {
    padding: 10px 0;
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(0, 255, 255, 0.05));
    position: relative;
    overflow: hidden;
}

.contact-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 20% 50%, rgba(37, 99, 235, 0.1) 0%, transparent 50%);
    pointer-events: none;
}

.contact-hero-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    position: relative;
    z-index: 1;
}

.hero-text h1 {
    font-size: clamp(2.5rem, 6vw, 3.5rem);
    font-weight: 800;
    margin-bottom: 20px;
    line-height: 1.2;
}

.badge {
    display: inline-block;
    background: rgba(37, 99, 235, 0.2);
    border: 1px solid rgba(37, 99, 235, 0.5);
    color: #00ffff;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 20px;
}

.hero-subtitle {
    font-size: 1.1rem;
    color: #cbd5e1;
    margin-bottom: 30px;
    line-height: 1.6;
}

.hero-quick-info {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
    margin-top: 40px;
}

.quick-info-item {
    display: flex;
    align-items: center;
    gap: 15px;
}

.info-icon {
    font-size: 1.8rem;
}

.info-label {
    font-size: 0.9rem;
    color: #94a3b8;
    margin: 0;
}

.info-value {
    font-size: 1.05rem;
    font-weight: 700;
    margin: 5px 0 0;
}

.floating-card {
    padding: 30px;
    border-radius: 12px;
    text-align: center;
    animation: float 3s ease-in-out infinite;
}

.card-icon {
    font-size: 3rem;
    margin-bottom: 15px;
}

.floating-card h3 {
    margin: 15px 0 10px;
    font-size: 1.3rem;
}

.floating-card p {
    color: #94a3b8;
    font-size: 0.95rem;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-20px);
    }
}

/* Contact Info Section */
.contact-info-section {
    background: transparent;
}

.contact-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
}

.info-card {
    padding: 40px;
    border-radius: 12px;
    text-align: center;
    transition: all 0.3s ease;
}

.info-card:hover {
    transform: translateY(-10px);
    border-color: rgba(0, 255, 255, 0.4);
}

.info-icon-large {
    font-size: 2.5rem;
    margin-bottom: 20px;
}

.info-card h3 {
    font-size: 1.3rem;
    margin-bottom: 15px;
}

.info-text {
    color: #cbd5e1;
    line-height: 1.8;
    margin-bottom: 20px;
}

.info-link {
    color: #00ffff;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.info-link:hover {
    color: #2563eb;
}

/* Section Header */
.section-padding {
    padding: 80px 0;
}

.section-header {
    text-align: center;
    margin-bottom: 60px;
}

.section-title {
    font-size: clamp(2rem, 5vw, 2.8rem);
    font-weight: 800;
    margin-bottom: 15px;
}

.section-subtitle {
    color: #94a3b8;
    font-size: 1.1rem;
}

/* Contact Form Section */
.contact-form-section {
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(245, 158, 11, 0.1));
}

.form-wrapper {
    max-width: 700px;
    margin: 0 auto;
}

.form-header {
    text-align: center;
    margin-bottom: 50px;
}

.contact-form {
    padding: 20px;
    border-radius: 12px;
}

.form-group {
    margin-bottom: 10px;
}

.form-group label {
    display: block;
    margin-bottom: 10px;
    font-weight: 600;
    font-size: 0.95rem;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    background: rgba(15, 23, 42, 0.5);
    color: #e2e8f0;
    font-family: inherit;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color: #64748b;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: rgba(0, 255, 255, 0.5);
    background: rgba(15, 23, 42, 0.8);
    box-shadow: 0 0 15px rgba(0, 255, 255, 0.2);
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group textarea {
    resize: vertical;
}

.form-group.checkbox {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 30px;
}

.form-group.checkbox input {
    width: auto;
    cursor: pointer;
    accent-color: #2563eb;
}

.form-group.checkbox label {
    margin: 0;
    cursor: pointer;
    font-size: 0.95rem;
}

.btn {
    padding: 12px 32px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-block;
    border: none;
    cursor: pointer;
    font-size: 1rem;
}

.btn-primary {
    background: linear-gradient(to right, #2563eb, #f59e0b);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(37, 99, 235, 0.4);
}

.btn-secondary {
    background: transparent;
    border: 2px solid #2563eb;
    color: #2563eb;
}

.btn-secondary:hover {
    background: rgba(37, 99, 235, 0.1);
    border-color: #00ffff;
    color: #00ffff;
}

.btn-large {
    padding: 14px 40px;
    font-size: 1.05rem;
}

.form-note {
    text-align: center;
    color: #94a3b8;
    font-size: 0.9rem;
    margin-top: 15px;
}

/* FAQ Section */
.faq-section {
    background: transparent;
}

.faq-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 25px;
}

.faq-item {
    padding: 25px;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.faq-item:hover {
    border-color: rgba(0, 255, 255, 0.3);
    background: rgba(0, 255, 255, 0.04);
}

.faq-question {
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    padding: 10px 0;
    user-select: none;
}

.faq-question h3 {
    font-size: 1.05rem;
    margin: 0;
    font-weight: 600;
}

.faq-icon {
    font-size: 1.5rem;
    color: #2563eb;
    font-weight: bold;
    transition: all 0.3s ease;
}

.faq-item.active .faq-icon {
    transform: rotate(45deg);
    color: #00ffff;
}

.faq-answer {
    display: none;
    padding-top: 15px;
    margin-top: 15px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    color: #cbd5e1;
    line-height: 1.7;
}

.faq-item.active .faq-answer {
    display: block;
}

/* Benefits Section */
.benefits-section {
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(245, 158, 11, 0.1));
}

.benefits-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
}

.benefit-card {
    padding: 40px;
    border-radius: 12px;
    text-align: center;
    transition: all 0.3s ease;
    position: relative;
    padding-top: 80px;
}

.benefit-card:hover {
    transform: translateY(-10px);
    border-color: rgba(0, 255, 255, 0.4);
}

.benefit-number {
    position: absolute;
    top: -20px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #2563eb, #f59e0b);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    font-weight: 700;
}

.benefit-card h3 {
    font-size: 1.2rem;
    margin-bottom: 15px;
}

.benefit-card p {
    color: #cbd5e1;
    line-height: 1.6;
}

/* Map Section */
.map-section {
    background: transparent;
}

.map-wrapper {
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 40px;
    overflow: hidden;
}

.map-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
}

.map-info-card {
    padding: 30px;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.map-info-card:hover {
    border-color: rgba(0, 255, 255, 0.3);
    background: rgba(0, 255, 255, 0.05);
}

.map-info-card h3 {
    font-size: 1.1rem;
    margin-bottom: 12px;
}

.map-info-card p {
    color: #cbd5e1;
    font-size: 0.95rem;
}

/* Social Section */
.social-section {
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(245, 158, 11, 0.1));
}

.social-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 25px;
    max-width: 1000px;
    margin: 0 auto;
}

.social-btn {
    padding: 25px;
    border-radius: 12px;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    transition: all 0.3s ease;
}

.social-btn:hover {
    transform: translateY(-8px);
    border-color: rgba(0, 255, 255, 0.4);
}

.social-icon {
    font-size: 2rem;
    font-weight: bold;
    color: #00ffff;
}

.social-name {
    font-weight: 600;
    font-size: 1rem;
}

/* CTA Section */
.cta-section {
    background: transparent;
}

.cta-content {
    padding: 60px;
    border-radius: 16px;
    text-align: center;
}

.cta-text h2 {
    font-size: clamp(2rem, 5vw, 2.5rem);
    margin-bottom: 20px;
}

.cta-text p {
    color: #cbd5e1;
    font-size: 1.1rem;
    margin-bottom: 40px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.cta-buttons {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
}

/* Container */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Glass Effects */
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

.glass {
    -webkit-backdrop-filter: blur(10px);
    backdrop-filter: blur(10px);
    background: rgba(15, 23, 42, 0.8);
    border: 1px solid rgba(255, 255, 255, 0.1);
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
}

/* Responsive Design */
@media (max-width: 768px) {
    .contact-hero-content {
        grid-template-columns: 1fr;
        gap: 40px;
    }
    
    .hero-quick-info {
        flex-direction: column;
        gap: 20px;
    }
    
    .contact-form {
        padding: 30px 20px;
    }
    
    .cta-content {
        padding: 40px 20px;
    }
    
    .cta-buttons {
        flex-direction: column;
    }
    
    .social-buttons {
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    }
}

/* FAQ Interaction Script */
document.addEventListener('DOMContentLoaded', function() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        question.addEventListener('click', function() {
            // Close other items
            faqItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                }
            });
            // Toggle current item
            item.classList.toggle('active');
        });
    });
});
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        question.addEventListener('click', function() {
            faqItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                }
            });
            item.classList.toggle('active');
        });
    });

    // Contact Form Handling - Integrated with Impulse Enquiry Manager Plugin
    // The form submission is handled by the plugin via AJAX
    // This ensures enquiries are automatically saved to the database
});
</script>