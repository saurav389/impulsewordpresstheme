<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ICA LMS Course Topics & Progress Tracking
 * Manages assignment of posts to courses and student reading progress
 */
class ICA_LMS_Course_Topics {
    public static function init() {
        // Add meta box to courses
        add_action('add_meta_boxes', array(__CLASS__, 'add_course_topics_meta_box'));
        add_action('save_post_courses', array(__CLASS__, 'save_course_topics'));
        
        // AJAX endpoints for topic management
        add_action('wp_ajax_ica_add_topic', array(__CLASS__, 'ajax_add_topic'));
        add_action('wp_ajax_ica_remove_topic', array(__CLASS__, 'ajax_remove_topic'));
        add_action('wp_ajax_ica_get_course_topics', array(__CLASS__, 'ajax_get_course_topics'));
        add_action('wp_ajax_ica_mark_post_read', array(__CLASS__, 'ajax_mark_post_read'));
        add_action('wp_ajax_ica_get_course_progress', array(__CLASS__, 'ajax_get_course_progress'));
        
        // Frontend hooks
        add_filter('the_content', array(__CLASS__, 'mark_topic_as_read_on_view'), 999);
        
        // Create database tables on init
        add_action('init', array(__CLASS__, 'create_tables'), 5);
    }

    /**
     * Create custom tables for course topics and student progress
     */
    public static function create_tables() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();
        
        // Course Topics table - links posts to courses
        $course_topics_table = $wpdb->prefix . 'ica_lms_course_topics';
        $sql_course_topics = "
        CREATE TABLE IF NOT EXISTS $course_topics_table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            course_id BIGINT UNSIGNED NOT NULL,
            post_id BIGINT UNSIGNED NOT NULL,
            topic_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY course_post (course_id, post_id),
            KEY course_id (course_id),
            KEY post_id (post_id),
            KEY topic_order (topic_order)
        ) $charset_collate;
        ";
        dbDelta($sql_course_topics);

        // Student Progress table - tracks which students have read which posts
        $student_progress_table = $wpdb->prefix . 'ica_lms_student_progress';
        $sql_student_progress = "
        CREATE TABLE IF NOT EXISTS $student_progress_table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            student_id BIGINT UNSIGNED NOT NULL,
            post_id BIGINT UNSIGNED NOT NULL,
            course_id BIGINT UNSIGNED NOT NULL,
            read_at DATETIME NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'reading',
            PRIMARY KEY (id),
            UNIQUE KEY student_post_course (student_id, post_id, course_id),
            KEY student_id (student_id),
            KEY post_id (post_id),
            KEY course_id (course_id),
            KEY read_at (read_at)
        ) $charset_collate;
        ";
        dbDelta($sql_student_progress);
    }

    /**
     * Get table names
     */
    public static function table_course_topics() {
        global $wpdb;
        return $wpdb->prefix . 'ica_lms_course_topics';
    }

    public static function table_student_progress() {
        global $wpdb;
        return $wpdb->prefix . 'ica_lms_student_progress';
    }

    /**
     * Add meta box to course edit screen
     */
    public static function add_course_topics_meta_box() {
        add_meta_box(
            'ica_lms_course_topics',
            'Course Topics (Posts)',
            array(__CLASS__, 'render_course_topics_meta_box'),
            'courses',
            'normal',
            'high'
        );
    }

    /**
     * Render course topics meta box
     */
    public static function render_course_topics_meta_box($post) {
        $course_id = $post->ID;
        $current_topics = self::get_course_topics($course_id);
        $available_posts = self::get_available_posts();
        
        ?>
        <div style="padding: 15px;">
            <div style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border-radius: 5px;">
                <label for="ica_new_topic"><strong>Add Topic (Post):</strong></label><br>
                <div style="display: grid; grid-template-columns: 1fr auto; gap: 10px; margin-top: 10px;">
                    <select id="ica_new_topic" style="padding: 10px; border: 1px solid #ddd; border-radius: 3px;">
                        <option value="">-- Select a Post to Add --</option>
                        <?php foreach ($available_posts as $post_item) : ?>
                            <option value="<?php echo esc_attr($post_item->ID); ?>">
                                <?php echo esc_html($post_item->post_title); ?> (by <?php echo esc_html($post_item->post_author_name); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" id="ica_add_topic_btn" class="button button-primary" style="padding: 8px 15px;">Add Topic</button>
                </div>
                <div id="ica_topic_message" style="margin-top: 10px; display: none; padding: 10px; border-radius: 3px;"></div>
            </div>

            <h4 style="margin-top: 20px; margin-bottom: 15px;">Current Topics (<span id="ica_topic_count"><?php echo esc_html(count($current_topics)); ?></span>):</h4>
            <table class="wp-list-table widefat striped hover" style="margin-top: 10px;">
                <thead>
                    <tr>
                        <th style="width: 50px;">Order</th>
                        <th>Topic Title</th>
                        <th>Author</th>
                        <th style="width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody id="ica_topics_list">
                    <?php if (empty($current_topics)) : ?>
                        <tr id="ica_no_topics_row">
                            <td colspan="4" style="text-align: center; padding: 30px; color: #999;">
                                No topics added yet. Add posts from the dropdown above.
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($current_topics as $index => $topic) : ?>
                            <tr class="ica-topic-row" data-post-id="<?php echo esc_attr($topic['post_id']); ?>" data-course-id="<?php echo esc_attr($course_id); ?>">
                                <td style="text-align: center;">
                                    <span class="ica-topic-order"><?php echo esc_html($index + 1); ?></span>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url(get_edit_post_link($topic['post_id'])); ?>" target="_blank">
                                        <?php echo esc_html($topic['post_title']); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html($topic['author_name']); ?></td>
                                <td>
                                    <button type="button" class="button button-small button-secondary ica-remove-topic" data-post-id="<?php echo esc_attr($topic['post_id']); ?>" data-course-id="<?php echo esc_attr($course_id); ?>">Remove</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <script type="text/javascript">
        (function() {
            var courseId = <?php echo json_encode($course_id); ?>;
            var ajaxNonce = '<?php echo wp_create_nonce('ica_lms_topic_nonce'); ?>';

            // Add Topic
            document.getElementById('ica_add_topic_btn').addEventListener('click', function(e) {
                e.preventDefault();
                var postId = document.getElementById('ica_new_topic').value;
                if (!postId) {
                    showMessage('Please select a post', 'error');
                    return;
                }

                var btn = this;
                btn.disabled = true;
                btn.textContent = 'Adding...';

                fetch(ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=ica_add_topic&nonce=' + ajaxNonce + '&course_id=' + courseId + '&post_id=' + postId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage('Topic added successfully!', 'success');
                        document.getElementById('ica_new_topic').value = '';
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showMessage(data.data || 'Error adding topic', 'error');
                    }
                    btn.disabled = false;
                    btn.textContent = 'Add Topic';
                })
                .catch(err => {
                    console.error('Error:', err);
                    showMessage('Network error', 'error');
                    btn.disabled = false;
                    btn.textContent = 'Add Topic';
                });
            });

            // Remove Topic
            document.querySelectorAll('.ica-remove-topic').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (!confirm('Are you sure you want to remove this topic from the course?')) {
                        return;
                    }

                    var postId = this.getAttribute('data-post-id');
                    var btn = this;
                    btn.disabled = true;
                    btn.textContent = 'Removing...';

                    fetch(ajaxurl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=ica_remove_topic&nonce=' + ajaxNonce + '&course_id=' + courseId + '&post_id=' + postId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showMessage('Topic removed successfully!', 'success');
                            setTimeout(() => location.reload(), 800);
                        } else {
                            showMessage(data.data || 'Error removing topic', 'error');
                        }
                        btn.disabled = false;
                        btn.textContent = 'Remove';
                    })
                    .catch(err => {
                        console.error('Error:', err);
                        showMessage('Network error', 'error');
                        btn.disabled = false;
                        btn.textContent = 'Remove';
                    });
                });
            });

            // Show message
            function showMessage(message, type) {
                var msgDiv = document.getElementById('ica_topic_message');
                msgDiv.textContent = message;
                msgDiv.style.display = 'block';
                msgDiv.style.background = type === 'success' ? '#d4edda' : '#f8d7da';
                msgDiv.style.color = type === 'success' ? '#155724' : '#721c24';
                msgDiv.style.border = '1px solid ' + (type === 'success' ? '#c3e6cb' : '#f5c6cb');
            }
        })();
        </script>
        <?php
    }

    /**
     * Save course topics (kept for backwards compatibility, now using AJAX)
     */
    public static function save_course_topics($post_id) {
        // Nonce verification removed - using AJAX instead
        // This function is now minimal since AJAX handles the operations
    }

    /**
     * AJAX: Add topic to course
     */
    public static function ajax_add_topic() {
        check_ajax_referer('ica_add_topic', 'nonce');

        // Allow teachers and admins
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Unauthorized');
        }

        $course_id = isset($_POST['course_id']) ? (int) $_POST['course_id'] : 0;
        $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;

        if (!$course_id || !$post_id) {
            wp_send_json_error('Invalid course or post ID');
        }

        // Verify course exists
        if (get_post_type($course_id) !== 'courses') {
            wp_send_json_error('Invalid course');
        }

        // For teachers, verify they own the course
        $current_user_id = get_current_user_id();
        $course = get_post($course_id);
        if (!current_user_can('manage_options') && $course->post_author != $current_user_id) {
            wp_send_json_error('You can only add topics to your own courses');
        }

        // Verify post exists
        if (get_post($post_id) === null) {
            wp_send_json_error('Invalid post');
        }

        $result = self::add_topic_to_course($course_id, $post_id);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Topic added successfully'));
        } else {
            wp_send_json_error('Topic already exists or database error');
        }
    }

    /**
     * AJAX: Remove topic from course
     */
    public static function ajax_remove_topic() {
        check_ajax_referer('ica_remove_topic', 'nonce');

        // Allow teachers and admins
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Unauthorized');
        }

        $course_id = isset($_POST['course_id']) ? (int) $_POST['course_id'] : 0;
        $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;

        if (!$course_id || !$post_id) {
            wp_send_json_error('Invalid course or post ID');
        }

        // For teachers, verify they own the course
        $current_user_id = get_current_user_id();
        $course = get_post($course_id);
        if (!current_user_can('manage_options') && $course->post_author != $current_user_id) {
            wp_send_json_error('You can only remove topics from your own courses');
        }

        $result = self::remove_topic_from_course($course_id, $post_id);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Topic removed successfully'));
        } else {
            wp_send_json_error('Error removing topic');
        }
    }

    /**
     * Get available posts for adding to course (teacher posts)
     */
    public static function get_available_posts() {
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        );

        $query = new WP_Query($args);
        
        $posts = array();
        foreach ($query->posts as $post) {
            $post->post_author_name = get_the_author_meta('display_name', $post->post_author);
            $posts[] = $post;
        }

        return $posts;
    }

    /**
     * Get topics for a course
     */
    public static function get_course_topics($course_id) {
        global $wpdb;
        $table = self::table_course_topics();

        $topics = $wpdb->get_results($wpdb->prepare(
            "SELECT ct.post_id, p.ID, p.post_title, p.post_content, p.post_author, COALESCE(u.display_name, 'Unknown') as author_name 
             FROM $table ct
             JOIN {$wpdb->posts} p ON ct.post_id = p.ID
             LEFT JOIN {$wpdb->users} u ON p.post_author = u.ID
             WHERE ct.course_id = %d AND p.post_status IN ('publish', 'draft')
             ORDER BY ct.topic_order ASC",
            $course_id
        ), ARRAY_A);

        return $topics ?: array();
    }

    /**
     * Add topic to course
     */
    public static function add_topic_to_course($course_id, $post_id) {
        global $wpdb;
        $table = self::table_course_topics();

        // Check if already exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE course_id = %d AND post_id = %d",
            $course_id,
            $post_id
        ));

        if ($existing) {
            return false;
        }

        // Get max order
        $max_order = $wpdb->get_var($wpdb->prepare(
            "SELECT MAX(topic_order) FROM $table WHERE course_id = %d",
            $course_id
        ));

        $result = $wpdb->insert(
            $table,
            array(
                'course_id' => $course_id,
                'post_id' => $post_id,
                'topic_order' => ($max_order ?? 0) + 1,
                'created_at' => current_time('mysql'),
            ),
            array('%d', '%d', '%d', '%s')
        );

        return $result !== false;
    }

    /**
     * Remove topic from course
     */
    public static function remove_topic_from_course($course_id, $post_id) {
        global $wpdb;
        $table = self::table_course_topics();

        $result = $wpdb->delete(
            $table,
            array('course_id' => $course_id, 'post_id' => $post_id),
            array('%d', '%d')
        );

        // Clean up student progress for this post
        if ($result) {
            $progress_table = self::table_student_progress();
            $wpdb->delete(
                $progress_table,
                array('course_id' => $course_id, 'post_id' => $post_id),
                array('%d', '%d')
            );
        }

        return $result !== false;
    }

    /**
     * Mark a post as read by a student
     */
    public static function mark_post_read($student_id, $post_id, $course_id) {
        global $wpdb;
        $table = self::table_student_progress();

        $result = $wpdb->replace(
            $table,
            array(
                'student_id' => $student_id,
                'post_id' => $post_id,
                'course_id' => $course_id,
                'read_at' => current_time('mysql'),
                'status' => 'completed',
            ),
            array('%d', '%d', '%d', '%s', '%s')
        );

        return $result !== false;
    }

    /**
     * Get student progress for a course
     */
    public static function get_student_course_progress($student_id, $course_id) {
        global $wpdb;
        $progress_table = self::table_student_progress();
        $course_topics_table = self::table_course_topics();

        // Get total topics in course
        $total_topics = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $course_topics_table WHERE course_id = %d",
            $course_id
        ));

        // Get topics read by student
        $topics_read = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $progress_table 
             WHERE student_id = %d AND course_id = %d AND status = 'completed'",
            $student_id,
            $course_id
        ));

        $progress_percent = $total_topics > 0 ? round(($topics_read / $total_topics) * 100) : 0;

        return array(
            'total_topics' => $total_topics,
            'topics_read' => $topics_read,
            'progress_percent' => $progress_percent,
        );
    }

    /**
     * AJAX: Get course topics
     */
    public static function ajax_get_course_topics() {
        check_ajax_referer('ica_lms_nonce', 'nonce');

        $course_id = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;
        $student_id = get_current_user_id();

        if (!$course_id || !$student_id) {
            wp_send_json_error('Invalid course or user');
        }

        // Check if student is enrolled in course (check both enrollments and students table)
        $enrollment = ICA_LMS_DB::get_enrollment($student_id, $course_id);
        if (!$enrollment) {
            // Also check the students table for LMS-registered students
            global $wpdb;
            $students_table = ICA_LMS_DB::table_students();
            $student_enrollment = $wpdb->get_row($wpdb->prepare(
                "SELECT id FROM $students_table WHERE wp_user_id = %d AND course_id = %d LIMIT 1",
                (int)$student_id,
                (int)$course_id
            ));
            
            if (!$student_enrollment) {
                wp_send_json_error('Not enrolled in this course');
            }
        }

        $topics = self::get_course_topics($course_id);
        if (empty($topics)) {
            wp_send_json_success(array('topics' => array(), 'message' => 'No topics available'));
        }

        // Enhance with read status
        global $wpdb;
        $progress_table = self::table_student_progress();

        foreach ($topics as &$topic) {
            $read_status = $wpdb->get_var($wpdb->prepare(
                "SELECT status FROM $progress_table 
                 WHERE student_id = %d AND post_id = %d AND course_id = %d",
                $student_id,
                $topic['post_id'],
                $course_id
            ));

            $topic['is_read'] = $read_status === 'completed';
            $topic['post_url'] = get_permalink($topic['post_id']);
        }

        $progress = self::get_student_course_progress($student_id, $course_id);

        wp_send_json_success(array(
            'topics' => $topics,
            'progress' => $progress,
        ));
    }

    /**
     * AJAX: Mark post as read
     */
    public static function ajax_mark_post_read() {
        check_ajax_referer('ica_lms_nonce', 'nonce');

        $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
        $course_id = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;
        $student_id = get_current_user_id();

        if (!$post_id || !$course_id || !$student_id) {
            wp_send_json_error('Invalid parameters');
        }

        $result = self::mark_post_read($student_id, $post_id, $course_id);
        if ($result) {
            $progress = self::get_student_course_progress($student_id, $course_id);
            wp_send_json_success(array('progress' => $progress));
        } else {
            wp_send_json_error('Failed to save progress');
        }
    }

    /**
     * AJAX: Get course progress
     */
    public static function ajax_get_course_progress() {
        check_ajax_referer('ica_lms_nonce', 'nonce');

        $course_id = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;
        $student_id = get_current_user_id();

        if (!$course_id || !$student_id) {
            wp_send_json_error('Invalid course or user');
        }

        $progress = self::get_student_course_progress($student_id, $course_id);
        wp_send_json_success(array('progress' => $progress));
    }

    /**
     * Mark topic as read when student views the post
     */
    public static function mark_topic_as_read_on_view($content) {
        if (!is_single() || get_post_type() !== 'post') {
            return $content;
        }

        $student_id = get_current_user_id();
        if (!$student_id) {
            return $content;
        }

        // Check if user is a student
        $user = get_userdata($student_id);
        if (!in_array(ICA_LMS_User_Roles::STUDENT_ROLE, (array)$user->roles)) {
            return $content;
        }

        // Find which course this post belongs to
        global $wpdb;
        $course_topics_table = self::table_course_topics();
        $course_id = $wpdb->get_var($wpdb->prepare(
            "SELECT course_id FROM $course_topics_table WHERE post_id = %d LIMIT 1",
            get_the_ID()
        ));

        // Mark as read if this post is assigned to a course
        if ($course_id) {
            self::mark_post_read($student_id, get_the_ID(), $course_id);
        }

        return $content;
    }
}
