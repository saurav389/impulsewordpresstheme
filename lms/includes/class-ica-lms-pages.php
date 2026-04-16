<?php
if (!defined('ABSPATH')) {
    exit;
}

class ICA_LMS_Pages {
    public static function init() {
        add_shortcode('ica_lms_catalog', array(__CLASS__, 'render_catalog_shortcode'));
        add_shortcode('ica_lms_my_courses', array(__CLASS__, 'render_my_courses_shortcode'));
        add_action('admin_init', array(__CLASS__, 'maybe_create_pages'));
        add_action('wp_loaded', array(__CLASS__, 'ensure_student_page_exists'));
    }

    /**
     * Ensure student page exists (runs on every page load)
     */
    public static function ensure_student_page_exists() {
        $student_page = get_page_by_path('student');
        if (!$student_page) {
            self::create_page_if_missing('Student Portal', 'student', '', 'student.php');
        }
    }

    public static function maybe_create_pages() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $created = get_option('ica_lms_pages_created');
        if ($created) {
            return;
        }

        self::create_page_if_missing('Student Portal', 'student', '', 'student.php');
        self::create_page_if_missing('Course Catalog', 'lms-catalog', '[ica_lms_catalog]');
        self::create_page_if_missing('Student Dashboard', 'student-dashboard', '[ica_lms_student_dashboard]');
        update_option('ica_lms_pages_created', 1);
    }

    private static function create_page_if_missing($title, $slug, $content, $template = null) {
        $existing = get_page_by_path($slug);
        if ($existing) {
            return;
        }

        $page_id = wp_insert_post(array(
            'post_title' => $title,
            'post_name' => $slug,
            'post_content' => $content,
            'post_status' => 'publish',
            'post_type' => 'page',
        ));
        
        // If a template is specified, assign it
        if ($template && !empty($page_id)) {
            update_post_meta($page_id, '_wp_page_template', $template);
        }
    }

    /**
     * Render course catalog shortcode
     */
    public static function render_catalog_shortcode() {
        $courses = get_posts(array(
            'post_type' => 'courses',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        ));

        ob_start();
        ?>
        <div class="ica-lms-shell">
            <div class="ica-lms-header">
                <h2>Course Catalog</h2>
                <p>Browse all available courses.</p>
            </div>
            <div class="ica-lms-grid">
                <?php if (empty($courses)) : ?>
                    <p>No courses available at this time.</p>
                <?php else : ?>
                    <?php foreach ($courses as $course) : ?>
                        <div class="ica-lms-card">
                            <h3><?php echo esc_html($course->post_title); ?></h3>
                            <?php
                            $excerpt = wp_trim_words($course->post_content, 20, '...');
                            if (!empty($excerpt)) :
                            ?>
                                <p><?php echo esc_html($excerpt); ?></p>
                            <?php endif; ?>
                            <a class="ica-lms-btn" href="<?php echo esc_url(get_permalink($course->ID)); ?>">View Course</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render student's enrolled courses with topics shortcode
     */
    public static function render_my_courses_shortcode() {
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            return '<div class="ica-lms-alert">Please log in to view your courses.</div>';
        }

        // Get user enrollments
        $enrollments = ICA_LMS_DB::get_user_enrollments($user_id);
        
        if (empty($enrollments)) {
            return '<div class="ica-lms-alert">You are not enrolled in any courses yet.</div>';
        }

        ob_start();
        ?>
        <div class="ica-lms-my-courses">
            <div class="ica-lms-header">
                <h2>My Courses</h2>
                <p>Continue learning where you left off.</p>
            </div>

            <?php foreach ($enrollments as $enrollment) : 
                $course_id = $enrollment['course_id'];
                $course = get_post($course_id);
                $progress = ICA_LMS_Course_Topics::get_student_course_progress($user_id, $course_id);
                $topics = ICA_LMS_Course_Topics::get_course_topics($course_id);
            ?>
                <div class="ica-lms-course-card" style="margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                    <h3><?php echo esc_html($course->post_title); ?></h3>
                    
                    <div class="ica-course-progress" style="margin: 15px 0;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span>Progress:</span>
                            <strong><?php echo esc_html($progress['progress_percent']); ?>% (<?php echo esc_html($progress['topics_read']); ?>/<?php echo esc_html($progress['total_topics']); ?> topics)</strong>
                        </div>
                        <div style="height: 20px; background: #f0f0f0; border-radius: 10px; overflow: hidden;">
                            <div style="height: 100%; background: #4CAF50; width: <?php echo esc_attr($progress['progress_percent']); ?>%; transition: width 0.3s;">
                            </div>
                        </div>
                    </div>

                    <h4>Topics:</h4>
                    <ul style="list-style: none; padding: 0; margin: 10px 0;">
                        <?php if (empty($topics)) : ?>
                            <li style="padding: 10px; color: #999;">No topics available in this course.</li>
                        <?php else : ?>
                            <?php foreach ($topics as $index => $topic) : 
                                // Check if student has read this topic
                                global $wpdb;
                                $progress_table = $wpdb->prefix . 'ica_lms_student_progress';
                                $is_read = $wpdb->get_var($wpdb->prepare(
                                    "SELECT status FROM $progress_table 
                                     WHERE student_id = %d AND post_id = %d AND course_id = %d",
                                    $user_id,
                                    $topic['post_id'],
                                    $course_id
                                )) === 'completed';
                            ?>
                                <li style="padding: 10px; border-left: 4px solid <?php echo $is_read ? '#4CAF50' : '#ccc'; ?>; margin-bottom: 5px; background: #f9f9f9;">
                                    <span><?php echo esc_html($index + 1); ?>.</span>
                                    <a href="<?php echo esc_url(get_permalink($topic['post_id'])); ?>" style="text-decoration: none;">
                                        <?php echo esc_html($topic['post_title']); ?>
                                    </a>
                                    <?php if ($is_read) : ?>
                                        <span style="color: #4CAF50; font-weight: bold;"> ✓ Read</span>
                                    <?php else : ?>
                                        <span style="color: #999;"> (Not read)</span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
