<?php get_header(); ?>

<style>
    /* Single Course Page Styling */
    .single-course-hero {
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.2), rgba(0, 255, 255, 0.1));
        padding: 4rem 2rem;
        margin-bottom: 3rem;
        border-bottom: 1px solid rgba(0, 255, 255, 0.2);
        animation: fadeInDown 0.8s ease-out;
    }

    .course-hero-container {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
        align-items: center;
    }

    .course-hero-content h1 {
        color: white;
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 1rem;
        line-height: 1.2;
    }

    .course-hero-subtitle {
        color: #cbd5e1;
        font-size: 1.1rem;
        margin-bottom: 2rem;
        line-height: 1.6;
    }

    .course-hero-meta {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .hero-info-item {
        background: rgba(15, 23, 42, 0.4);
        padding: 1rem;
        border-radius: 0.5rem;
        border-left: 3px solid #00ffff;
    }

    .hero-info-label {
        color: #94a3b8;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.3rem;
    }

    .hero-info-value {
        color: white;
        font-size: 1.3rem;
        font-weight: 700;
    }

    .course-hero-image {
        position: relative;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0, 255, 255, 0.15);
        animation: slideInRight 0.8s ease-out;
    }

    .course-hero-image img {
        width: 100%;
        height: auto;
        display: block;
        transition: transform 0.5s ease;
    }

    .course-hero-image:hover img {
        transform: scale(1.05);
    }

    .course-level-badge {
        position: absolute;
        top: 1.5rem;
        right: 1.5rem;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: white;
        padding: 0.6rem 1.2rem;
        border-radius: 2rem;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .cta-buttons-group {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .cta-btn {
        padding: 1rem 2rem;
        border-radius: 0.5rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        font-size: 1rem;
    }

    .cta-btn-primary {
        background: linear-gradient(135deg, #2563eb, #00ffff);
        color: white;
    }

    .cta-btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(37, 99, 235, 0.3);
    }

    .cta-btn-secondary {
        background: transparent;
        color: #00ffff;
        border: 2px solid #00ffff;
    }

    .cta-btn-secondary:hover {
        background: rgba(0, 255, 255, 0.1);
        transform: translateY(-3px);
    }

    /* Course Details Section */
    .course-details-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .course-tabs {
        display: flex;
        gap: 1rem;
        margin-bottom: 3rem;
        border-bottom: 2px solid rgba(255, 255, 255, 0.05);
        padding-bottom: 1rem;
        flex-wrap: wrap;
        animation: fadeInUp 0.8s ease-out 0.2s both;
    }

    .tab-btn {
        padding: 1rem 1.5rem;
        background: transparent;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 600;
        transition: all 0.3s ease;
        position: relative;
    }

    .tab-btn.active {
        color: #00ffff;
    }

    .tab-btn.active::after {
        content: '';
        position: absolute;
        bottom: -1rem;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, #2563eb, #00ffff);
    }

    .tab-btn:hover {
        color: #cbd5e1;
    }

    .tab-content {
        display: none;
        animation: fadeIn 0.5s ease-out;
    }

    .tab-content.active {
        display: block;
    }

    /* Learning Outcomes */
    .learning-outcomes {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .outcome-item {
        background: rgba(15, 23, 42, 0.6);
        padding: 1.5rem;
        border-radius: 0.75rem;
        border: 1px solid rgba(0, 255, 255, 0.1);
        transition: all 0.3s ease;
    }

    .outcome-item:hover {
        border-color: rgba(0, 255, 255, 0.3);
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 255, 255, 0.1);
    }

    .outcome-icon {
        font-size: 2rem;
        margin-bottom: 1rem;
        display: inline-block;
    }

    .outcome-text {
        color: #cbd5e1;
        line-height: 1.6;
    }

    /* Syllabus */
    .syllabus-container {
        max-width: 800px;
    }

    .syllabus-module {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(0, 255, 255, 0.1);
        border-radius: 0.75rem;
        margin-bottom: 1.5rem;
        overflow: hidden;
        transition: all 0.3s ease;
        animation: slideInUp 0.6s ease-out both;
    }

    .syllabus-module:nth-child(1) { animation-delay: 0.1s; }
    .syllabus-module:nth-child(2) { animation-delay: 0.2s; }
    .syllabus-module:nth-child(3) { animation-delay: 0.3s; }
    .syllabus-module:nth-child(4) { animation-delay: 0.4s; }
    .syllabus-module:nth-child(5) { animation-delay: 0.5s; }
    .syllabus-module:nth-child(6) { animation-delay: 0.6s; }

    .syllabus-module:hover {
        border-color: rgba(0, 255, 255, 0.3);
        box-shadow: 0 10px 25px rgba(0, 255, 255, 0.1);
    }

    .syllabus-header {
        padding: 1.5rem;
        background: linear-gradient(90deg, rgba(37, 99, 235, 0.1), rgba(0, 255, 255, 0.05));
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s ease;
    }

    .syllabus-header:hover {
        background: linear-gradient(90deg, rgba(37, 99, 235, 0.2), rgba(0, 255, 255, 0.1));
    }

    .syllabus-title {
        color: white;
        font-size: 1.1rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .syllabus-toggle {
        color: #00ffff;
        font-size: 1.5rem;
        transition: transform 0.3s ease;
    }

    .syllabus-module.active .syllabus-toggle {
        transform: rotate(180deg);
    }

    .syllabus-content {
        display: none;
        padding: 1.5rem;
        color: #cbd5e1;
    }

    .syllabus-module.active .syllabus-content {
        display: block;
    }

    .syllabus-topic {
        padding: 0.75rem 0;
        padding-left: 2rem;
        position: relative;
    }

    .syllabus-topic:before {
        content: '✓';
        position: absolute;
        left: 0;
        color: #00ffff;
        font-weight: bold;
        font-size: 1.2rem;
    }

    /* Instructor Section */
    .instructors-section {
        animation: fadeInUp 0.8s ease-out 0.3s both;
    }

    .instructors-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
    }

    .instructor-card {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(0, 255, 255, 0.1);
        border-radius: 1rem;
        overflow: hidden;
        transition: all 0.4s ease;
    }

    .instructor-card:hover {
        border-color: rgba(0, 255, 255, 0.3);
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0, 255, 255, 0.15);
    }

    .instructor-header {
        height: 200px;
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.3), rgba(0, 255, 255, 0.2));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 4rem;
    }

    .instructor-content {
        padding: 2rem;
    }

    .instructor-name {
        color: white;
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.3rem;
    }

    .instructor-title {
        color: #00ffff;
        font-size: 0.95rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .instructor-bio {
        color: #cbd5e1;
        line-height: 1.6;
        font-size: 0.95rem;
    }

    /* Enhanced Instructor Card */
    .instructor-card-enhanced {
        background: linear-gradient(135deg, rgba(15, 23, 42, 0.8), rgba(37, 99, 235, 0.1));
        border: 1px solid rgba(0, 255, 255, 0.2);
        border-radius: 1.5rem;
        overflow: hidden;
        transition: all 0.5s ease;
        display: grid;
        grid-template-columns: 350px 1fr;
        grid-template-rows: auto auto;
    }

    .instructor-card-enhanced:hover {
        border-color: rgba(0, 255, 255, 0.5);
        transform: translateY(-15px);
        box-shadow: 0 30px 60px rgba(0, 255, 255, 0.2), 0 0 30px rgba(37, 99, 235, 0.15);
    }

    .instructor-image-section {
        grid-row: 1 / -1;
        position: relative;
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.15), rgba(0, 255, 255, 0.05));
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        min-height: 400px;
    }

    .instructor-avatar-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .instructor-card-enhanced:hover .instructor-avatar-image {
        transform: scale(1.05);
    }

    .instructor-avatar-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 5rem;
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.2), rgba(0, 255, 255, 0.15));
    }

    .instructor-badge {
        position: absolute;
        top: 1.5rem;
        right: 1.5rem;
        background: linear-gradient(135deg, #00ffff, #00d4ff);
        color: #0f172a;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-weight: 700;
        font-size: 0.85rem;
        box-shadow: 0 10px 25px rgba(0, 255, 255, 0.3);
    }

    .instructor-info-section {
        padding: 2.5rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .instructor-name-enhanced {
        color: white;
        font-size: 1.8rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        line-height: 1.2;
    }

    .instructor-experience {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid rgba(0, 255, 255, 0.2);
    }

    .experience-icon {
        font-size: 1.5rem;
    }

    .experience-text {
        color: #00ffff;
        font-size: 1.1rem;
        font-weight: 700;
    }

    .instructor-bio-enhanced {
        color: #cbd5e1;
        line-height: 1.7;
        font-size: 1rem;
        margin-bottom: 2rem;
    }

    .instructor-bio-enhanced p {
        margin: 0 !important;
        color: #cbd5e1;
        line-height: 1.7;
    }

    .instructor-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(0, 255, 255, 0.1);
    }

    .stat-box {
        text-align: center;
        padding: 1rem;
        background: rgba(37, 99, 235, 0.1);
        border-radius: 0.75rem;
        border: 1px solid rgba(0, 255, 255, 0.1);
        transition: all 0.3s ease;
    }

    .stat-box:hover {
        background: rgba(37, 99, 235, 0.2);
        border-color: rgba(0, 255, 255, 0.3);
    }

    .stat-number {
        color: #00ffff;
        font-size: 1.5rem;
        font-weight: 800;
        margin-bottom: 0.3rem;
    }

    .stat-label {
        color: #94a3b8;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Contact Section */
    .course-contact-section {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(0, 255, 255, 0.2);
        padding: 2rem;
        border-radius: 1rem;
        margin-top: 3rem;
        animation: fadeInUp 0.8s ease-out 0.4s both;
    }

    .contact-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 2rem;
    }

    .contact-info-item {
        text-align: center;
    }

    .contact-info-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }

    .contact-info-label {
        color: white;
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .contact-info-value {
        color: #00ffff;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .contact-info-value:hover {
        text-decoration: underline;
    }

    /* Animations */
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 768px) {
        .course-hero-container {
            grid-template-columns: 1fr;
        }

        .course-hero-content h1 {
            font-size: 1.8rem;
        }

        .course-hero-meta {
            grid-template-columns: 1fr;
        }

        .course-tabs {
            gap: 0.5rem;
            overflow-x: auto;
        }

        .tab-btn {
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
        }

        .learning-outcomes {
            grid-template-columns: 1fr;
        }

        .instructors-grid {
            grid-template-columns: 1fr;
        }

        .instructor-card-enhanced {
            grid-template-columns: 1fr;
            grid-template-rows: auto auto;
        }

        .instructor-image-section {
            grid-row: auto;
            min-height: 280px;
        }

        .instructor-info-section {
            padding: 2rem;
        }

        .instructor-name-enhanced {
            font-size: 1.5rem;
        }

        .cta-buttons-group {
            flex-direction: column;
        }

        .cta-btn {
            width: 100%;
        }
    }
</style>

<?php if (have_posts()) { while (have_posts()) { the_post();
    $duration = get_field('course_duration');
    $fee = get_field('course_fee');
    $students = get_field('students');
    $image = get_field('course_image');
    $subtitle = get_field('course_subtitle');
    $level = get_field('course_level');
    $learning_outcomes = get_field('learning_outcomes');
    $syllabus = get_field('syllabus');
    $instructor_name = get_field('instructor_name');
    $instructor_bio = get_field('instructor_bio');
?>

<!-- Hero Section -->
<div class="single-course-hero">
    <div class="course-hero-container">
        <div class="course-hero-content">
            <h1><?php the_title(); ?></h1>
            <?php if ($subtitle): ?>
                <p class="course-hero-subtitle"><?php echo esc_html($subtitle); ?></p>
            <?php endif; ?>

            <div class="course-hero-meta">
                <?php if ($duration): ?>
                    <div class="hero-info-item">
                        <div class="hero-info-label">⏱️ Duration</div>
                        <div class="hero-info-value"><?php echo esc_html($duration); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($fee): ?>
                    <div class="hero-info-item">
                        <div class="hero-info-label">💰 Fee</div>
                        <div class="hero-info-value"><?php echo esc_html($fee); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($students): ?>
                    <div class="hero-info-item">
                        <div class="hero-info-label">👥 Students</div>
                        <div class="hero-info-value"><?php echo esc_html($students); ?>+</div>
                    </div>
                <?php endif; ?>

                <?php if ($level): ?>
                    <div class="hero-info-item">
                        <div class="hero-info-label">🎯 Level</div>
                        <div class="hero-info-value"><?php echo esc_html($level); ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="cta-buttons-group">
                <a href="https://wa.me/919709034301" target="_blank" class="cta-btn cta-btn-primary">
                    Enroll Now →
                </a>
                <a href="tel:7979815545" class="cta-btn cta-btn-secondary">
                    Call Us
                </a>
            </div>
        </div>

        <div class="course-hero-image">
            <?php if ($image): ?>
                <img src="<?php echo esc_url(is_array($image) ? $image['url'] : $image); ?>" alt="<?php the_title(); ?>">
            <?php else: ?>
                <img src="https://via.placeholder.com/500x400?text=<?php echo urlencode(get_the_title()); ?>" alt="<?php the_title(); ?>">
            <?php endif; ?>
            <?php if ($level): ?>
                <div class="course-level-badge"><?php echo esc_html($level); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Details Section -->
<div class="course-details-container">
    <!-- Tabs -->
    <div class="course-tabs">
        <button class="tab-btn active" onclick="switchTab(event, 'overview')">📚 Overview</button>
        <button class="tab-btn" onclick="switchTab(event, 'learning')">🎯 What You'll Learn</button>
        <button class="tab-btn" onclick="switchTab(event, 'syllabus')">📋 Syllabus</button>
        <button class="tab-btn" onclick="switchTab(event, 'instructor')">👨‍🏫 Instructors</button>
    </div>

    <!-- Overview Tab -->
    <div id="overview" class="tab-content active">
        <div class="course-overview">
            <h2 style="color: white; font-size: 2rem; margin-bottom: 2rem; animation: fadeInUp 0.6s ease-out;">Course Overview</h2>
            <div style="color: #cbd5e1; line-height: 1.8; font-size: 1rem; animation: fadeInUp 0.6s ease-out 0.1s both;">
                <?php the_content(); ?>
            </div>
        </div>
    </div>

    <!-- Learning Outcomes Tab -->
    <div id="learning" class="tab-content">
        <h2 style="color: white; font-size: 2rem; margin-bottom: 2rem; animation: fadeInUp 0.6s ease-out;">What You'll Learn</h2>
        <div class="learning-outcomes">
            <?php if ($learning_outcomes): ?>
                <?php foreach ($learning_outcomes as $index => $outcome): ?>
                    <div class="outcome-item" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                        <div class="outcome-icon">🎯</div>
                        <div class="outcome-text"><?php echo isset($outcome['outcome']) ? esc_html($outcome['outcome']) : (is_string($outcome) ? esc_html($outcome) : 'Learning outcome'); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Syllabus Tab -->
    <div id="syllabus" class="tab-content">
        <h2 style="color: white; font-size: 2rem; margin-bottom: 2rem; animation: fadeInUp 0.6s ease-out;">Course Syllabus</h2>
        <div class="syllabus-container">
            <?php if ($syllabus): ?>
                <?php foreach ($syllabus as $index => $module): ?>
                    <div class="syllabus-module" onclick="toggleModule(this)">
                        <div class="syllabus-header">
                            <div class="syllabus-title">
                                <span style="background: linear-gradient(135deg, #2563eb, #00ffff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; width: 30px;">
                                    <?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?>
                                </span>
                                <?php echo isset($module['topic_name']) ? esc_html($module['topic_name']) : 'Module ' . ($index + 1); ?>
                            </div>
                            <div class="syllabus-toggle">▼</div>
                        </div>
                        <div class="syllabus-content">
                            <?php if (isset($module['topic_description'])): ?>
                                
                                    <div class="syllabus-topic"><?php echo isset($module['topic_description']) ? esc_html($module['topic_description']) : (is_string($module) ? esc_html($module) : ''); ?></div>
                     
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Instructor Tab -->
    <div id="instructor" class="tab-content">
        <div class="instructors-section">
            <h2 style="color: white; font-size: 2rem; margin-bottom: 3rem; animation: fadeInUp 0.6s ease-out;">Meet Your Instructor</h2>
            <div class="instructors-grid">
                <?php 
                $instructor_name = get_field('instructor_name');
                $instructor_bio = get_field('instructor_bio');
                $instructor_image = get_field('instructor_image');
                $instructor_experience = get_field('instructor_experience');
                
                if ($instructor_name): 
                ?>
                    <div class="instructor-card-enhanced" style="animation-delay: 0s;">
                        <!-- Instructor Image Section -->
                        <div class="instructor-image-section">
                            <?php if ($instructor_image): ?>
                                <img src="<?php echo esc_url(is_array($instructor_image) ? $instructor_image['url'] : $instructor_image); ?>" alt="<?php echo esc_html($instructor_name); ?>" class="instructor-avatar-image">
                            <?php else: ?>
                                <div class="instructor-avatar-placeholder">👨‍💼</div>
                            <?php endif; ?>
                            <div class="instructor-badge">Expert</div>
                        </div>

                        <!-- Instructor Info Section -->
                        <div class="instructor-info-section">
                            <div class="instructor-name-enhanced"><?php echo esc_html($instructor_name); ?></div>
                            
                            <?php if ($instructor_experience): ?>
                                <div class="instructor-experience">
                                    <span class="experience-icon">⭐</span>
                                    <span class="experience-text"><?php echo esc_html($instructor_experience); ?> Experience</span>
                                </div>
                            <?php endif; ?>

                            <div class="instructor-bio-enhanced"><?php echo $instructor_bio ? wpautop(esc_html($instructor_bio)) : 'Experienced instructor with industry expertise and passion for teaching.'; ?></div>

                            <!-- Quick Stats -->
                            <div class="instructor-stats">
                                <div class="stat-box">
                                    <div class="stat-number">500+</div>
                                    <div class="stat-label">Students</div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-number">4.9★</div>
                                    <div class="stat-label">Rating</div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-number">100%</div>
                                    <div class="stat-label">Satisfaction</div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php 
                else:
                ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 3rem 2rem; color: #cbd5e1;">
                        <p style="font-size: 1.1rem;">Instructor information coming soon!</p>
                    </div>
                <?php 
                endif; 
                ?>
            </div>
        </div>
    </div>

    <!-- Contact Section -->
    <div class="course-contact-section">
        <h2 style="color: white; font-size: 1.8rem; margin-bottom: 2rem; text-align: center;">Ready to Start Your Journey?</h2>
        <div class="contact-info-grid">
            <div class="contact-info-item">
                <div class="contact-info-icon">📞</div>
                <div class="contact-info-label">Call Us</div>
                <a href="tel:7979815545" class="contact-info-value">7979815545</a>
            </div>
            <div class="contact-info-item">
                <div class="contact-info-icon">💬</div>
                <div class="contact-info-label">WhatsApp</div>
                <a href="https://wa.me/919709034301" target="_blank" class="contact-info-value">9709034301</a>
            </div>
            <div class="contact-info-item">
                <div class="contact-info-icon">📧</div>
                <div class="contact-info-label">Email Us</div>
                <a href="mailto:impulsebirsanagar@gmail.com" class="contact-info-value">Send Message</a>
            </div>
        </div>
    </div>
</div>

<script>
    function switchTab(event, tabName) {
        // Hide all tabs
        const tabs = document.querySelectorAll('.tab-content');
        tabs.forEach(tab => {
            tab.classList.remove('active');
        });

        // Remove active class from all buttons
        const buttons = document.querySelectorAll('.tab-btn');
        buttons.forEach(btn => {
            btn.classList.remove('active');
        });

        // Show selected tab
        document.getElementById(tabName).classList.add('active');
        event.target.classList.add('active');
    }

    function toggleModule(element) {
        element.classList.toggle('active');
    }

    // Expand first module by default
    document.addEventListener('DOMContentLoaded', function() {
        const firstModule = document.querySelector('.syllabus-module');
        if (firstModule) {
            firstModule.classList.add('active');
        }
    });
</script>

<?php } } else {
    echo '<p style="text-align: center; color: white; padding: 2rem;">Course not found.</p>';
} ?>

<?php get_footer(); ?>