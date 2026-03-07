<?php get_header(); ?>

<style>
    /* Archive Courses Styling */
    .archive-courses-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 4rem 2rem;
    }

    .archive-header {
        text-align: center;
        margin-bottom: 4rem;
        animation: fadeInDown 0.8s ease-out;
    }

    .archive-header h1 {
        color: white;
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, #00ffff, #2563eb);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .archive-header p {
        color: #cbd5e1;
        font-size: 1.2rem;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Filter Section */
    .courses-filters {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-bottom: 3rem;
        flex-wrap: wrap;
        animation: fadeInUp 0.8s ease-out 0.2s both;
    }

    .filter-btn {
        padding: 0.75rem 1.5rem;
        border: 2px solid rgba(255, 255, 255, 0.1);
        background: rgba(15, 23, 42, 0.6);
        color: #cbd5e1;
        border-radius: 2rem;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .filter-btn:hover,
    .filter-btn.active {
        border-color: #00ffff;
        color: #00ffff;
        background: rgba(0, 255, 255, 0.1);
        transform: translateY(-2px);
    }

    /* Courses Grid */
    .archive-courses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 2rem;
        animation: fadeIn 0.8s ease-out 0.4s both;
    }

    .archive-course-card {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(0, 255, 255, 0.1);
        border-radius: 1rem;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        height: 100%;
        position: relative;
        animation: slideInUp 0.6s ease-out both;
    }

    .archive-course-card:nth-child(1) { animation-delay: 0.1s; }
    .archive-course-card:nth-child(2) { animation-delay: 0.2s; }
    .archive-course-card:nth-child(3) { animation-delay: 0.3s; }
    .archive-course-card:nth-child(4) { animation-delay: 0.4s; }
    .archive-course-card:nth-child(5) { animation-delay: 0.5s; }
    .archive-course-card:nth-child(6) { animation-delay: 0.6s; }

    .archive-course-card:hover {
        border-color: rgba(0, 255, 255, 0.4);
        box-shadow: 0 20px 40px rgba(0, 255, 255, 0.1), 0 0 40px rgba(37, 99, 235, 0.1);
        transform: translateY(-10px);
    }

    .course-image-wrapper {
        position: relative;
        width: 100%;
        height: 220px;
        overflow: hidden;
    }

    .archive-course-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .archive-course-card:hover img {
        transform: scale(1.1) rotate(1deg);
    }

    .course-badge-archive {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: linear-gradient(135deg, #2563eb, #00ffff);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-size: 0.8rem;
        font-weight: 600;
        z-index: 10;
        animation: bounceIn 0.6s ease-out both;
    }

    .archive-course-content {
        padding: 2rem;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    .archive-course-title {
        color: white;
        font-size: 1.4rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }

    .archive-course-subtitle {
        color: #94a3b8;
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
        line-height: 1.5;
    }

    .archive-course-meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .archive-meta-item {
        display: flex;
        flex-direction: column;
    }

    .archive-meta-label {
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.3rem;
    }

    .archive-meta-value {
        color: #00ffff;
        font-size: 1.1rem;
        font-weight: 600;
    }

    .archive-course-stats {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
    }

    .archive-stat {
        color: #cbd5e1;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    .archive-stat-highlight {
        color: #00ffff;
        font-weight: 600;
    }

    .archive-course-btn {
        display: inline-block;
        padding: 0.9rem 1.5rem;
        background: linear-gradient(135deg, #2563eb, #00ffff);
        color: white;
        text-decoration: none;
        border-radius: 0.5rem;
        text-align: center;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        font-size: 0.95rem;
        margin-top: auto;
    }

    .archive-course-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
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

    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(40px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes bounceIn {
        from {
            opacity: 0;
            transform: scale(0.8);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    /* Empty State */
    .courses-empty {
        text-align: center;
        padding: 4rem 2rem;
        color: #cbd5e1;
    }

    .courses-empty h2 {
        color: white;
        margin-bottom: 1rem;
    }

    @media (max-width: 768px) {
        .archive-header h1 {
            font-size: 2rem;
        }

        .archive-courses-grid {
            grid-template-columns: 1fr;
        }

        .courses-filters {
            flex-direction: column;
            align-items: center;
        }

        .filter-btn {
            width: 100%;
        }
    }
</style>

<div class="archive-courses-container">
    <!-- Header -->
    <div class="archive-header">
        <h1>Master In-Demand Skills</h1>
        <p>Explore our comprehensive collection of professional courses designed to transform your career. Learn from industry experts and get 100% placement support.</p>
    </div>

    <!-- Filters -->
    <!-- <div class="courses-filters">
        <button class="filter-btn active" onclick="filterCourses('all')">All Courses</button>
        <button class="filter-btn" onclick="filterCourses('beginner')">Beginner</button>
        <button class="filter-btn" onclick="filterCourses('intermediate')">Intermediate</button>
        <button class="filter-btn" onclick="filterCourses('advanced')">Advanced</button>
    </div> -->

    <!-- Courses Grid -->
    <div class="archive-courses-grid">
        <?php
        $args = array(
            'post_type' => 'courses',
            'posts_per_page' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                
                $duration = get_field('course_duration');
                $fee = get_field('course_fee');
                $students = get_field('students');
                $image = get_field('course_image');
                $subtitle = get_field('course_subtitle');
                $level = get_field('course_level');
        ?>
            <div class="archive-course-card">
                <div class="course-image-wrapper">
                    <?php if ($image): ?>
                        <img src="<?php echo esc_url(is_array($image) ? $image['url'] : $image); ?>" alt="<?php the_title(); ?>">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/400x300?text=<?php echo urlencode(get_the_title()); ?>" alt="<?php the_title(); ?>">
                    <?php endif; ?>
                    <?php if ($level): ?>
                        <div class="course-badge-archive"><?php echo esc_html($level); ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="archive-course-content">
                    <h3 class="archive-course-title"><?php the_title(); ?></h3>
                    
                    <?php if ($subtitle): ?>
                        <p class="archive-course-subtitle"><?php echo esc_html($subtitle); ?></p>
                    <?php endif; ?>

                    <div class="archive-course-meta">
                        <div class="archive-meta-item">
                            <span class="archive-meta-label">⏱️ Duration</span>
                            <span class="archive-meta-value"><?php echo esc_html($duration ?: 'N/A'); ?></span>
                        </div>
                        <div class="archive-meta-item">
                            <span class="archive-meta-label">💰 Fee</span>
                            <span class="archive-meta-value"><?php echo esc_html($fee ?: 'N/A'); ?></span>
                        </div>
                    </div>

                    <div class="archive-course-stats">
                        <span class="archive-stat">
                            👥 <span class="archive-stat-highlight"><?php echo esc_html($students ?: '0'); ?></span> Students
                        </span>
                    </div>

                    <a href="<?php the_permalink(); ?>" class="archive-course-btn">View Details →</a>
                </div>
            </div>
        <?php
            }
            wp_reset_postdata();
        } else {
        ?>
            <div class="courses-empty" style="grid-column: 1 / -1;">
                <h2>No courses found</h2>
                <p>Check back soon for new courses!</p>
            </div>
        <?php
        }
        ?>
    </div>
</div>

<script>
    function filterCourses(level) {
        // Update active button
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');

        // In a real implementation, you would filter the courses
        // For now, this is a placeholder for filtering logic
    }
</script>

<?php get_footer(); ?>