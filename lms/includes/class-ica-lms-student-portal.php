<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ICA LMS Student Portal
 * Frontend student dashboard and course management
 */
class ICA_LMS_Student_Portal {
    public static function init() {
        // Register frontend shortcode
        add_shortcode('ica_lms_student_dashboard', array(__CLASS__, 'render_dashboard_shortcode'));
        
        // AJAX handlers
        add_action('wp_ajax_ica_enroll_course', array(__CLASS__, 'ajax_enroll_course'));
        add_action('wp_ajax_ica_get_course_materials', array(__CLASS__, 'ajax_get_course_materials'));
        add_action('wp_ajax_ica_start_exam', array(__CLASS__, 'ajax_start_exam'));
        add_action('wp_ajax_ica_submit_exam', array(__CLASS__, 'ajax_submit_exam'));
        add_action('wp_ajax_ica_get_payment_history', array(__CLASS__, 'ajax_get_payment_history'));
        add_action('wp_ajax_ica_submit_payment_notification', array(__CLASS__, 'ajax_submit_payment_notification'));
        
        // Student receipt printing
        add_action('wp_ajax_nopriv_ica_print_student_receipt', array(__CLASS__, 'print_student_receipt'));
        add_action('wp_ajax_ica_print_student_receipt', array(__CLASS__, 'print_student_receipt'));
        
        // Hide admin bar from student portal
        add_action('wp_head', array(__CLASS__, 'hide_admin_bar_for_students'), 5);
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_student_portal_assets'));
    }
    
    /**
     * Hide admin bar for students on portal pages
     */
    public static function hide_admin_bar_for_students() {
        if (!is_user_logged_in()) {
            return;
        }
        
        $user = wp_get_current_user();
        $is_student = in_array(ICA_LMS_User_Roles::STUDENT_ROLE, (array) $user->roles);
        
        // If it's a student and we're not in admin, hide the admin bar
        if ($is_student && !is_admin()) {
            echo '<style>';
            echo 'html { margin-top: 0 !important; }';
            echo '#wpadminbar { display: none !important; }';
            echo 'body { margin-top: 0 !important; }';
            echo '</style>';
        }
    }

    /**
     * Enqueue student portal assets
     */
    public static function enqueue_student_portal_assets() {
        if (!is_user_logged_in()) {
            return;
        }

        $user = wp_get_current_user();
        $is_student = in_array(ICA_LMS_User_Roles::STUDENT_ROLE, (array) $user->roles);
        
        if (!$is_student) {
            return;
        }

        // Enqueue portal CSS
        $portal_css_path = ICA_LMS_PATH . '/assets/css/student-portal.css';
        $portal_css_ver = file_exists($portal_css_path) ? filemtime($portal_css_path) : ICA_LMS_VERSION;
        
        wp_enqueue_style(
            'ica-student-portal-style',
            ICA_LMS_URL . '/assets/css/student-portal.css',
            array(),
            $portal_css_ver
        );

        // Enqueue portal JS
        $portal_js_path = ICA_LMS_PATH . '/assets/js/student-portal.js';
        $portal_js_ver = file_exists($portal_js_path) ? filemtime($portal_js_path) : ICA_LMS_VERSION;
        
        wp_enqueue_script(
            'ica-student-portal-script',
            ICA_LMS_URL . '/assets/js/student-portal.js',
            array('jquery'),
            $portal_js_ver,
            true
        );

        wp_localize_script('ica-student-portal-script', 'ICAStudentPortal', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ica_student_portal_nonce'),
            'receipt_nonce' => wp_create_nonce('ica_student_receipt'),
            'student_id' => get_current_user_id(),
        ));
        
        // Inline JavaScript for receipt printing
        wp_add_inline_script('ica-student-portal-script', "
            function ica_print_student_receipt(paymentId) {
                if (!paymentId) {
                    alert('Invalid payment ID');
                    return;
                }
                const nonce = typeof ICAStudentPortal !== 'undefined' ? ICAStudentPortal.receipt_nonce : '';
                const url = window.location.origin + '/wp-admin/admin-ajax.php?action=ica_print_student_receipt&payment_id=' + paymentId + '&nonce=' + nonce;
                window.open(url, '_blank');
            }
        ");
    }

    /**
     * Render student dashboard shortcode
     */
    public static function render_dashboard_shortcode() {
        if (!is_user_logged_in()) {
            return '<div class="ica-alert"><p>Please log in to access your student dashboard.</p></div>';
        }

        $user = wp_get_current_user();
        $is_student = in_array(ICA_LMS_User_Roles::STUDENT_ROLE, (array) $user->roles);
        
        if (!$is_student) {
            return '<div class="ica-alert"><p>Sorry, you are not a student.</p></div>';
        }

        $student_id = get_current_user_id();
        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';

        ob_start();
        ?>
        <div class="ica-student-portal">
            <div class="ica-portal-header">
                <?php
                // Get student record from database
                global $wpdb;
                $students_table = ICA_LMS_DB::table_students();
                $student_record = $wpdb->get_row($wpdb->prepare(
                    "SELECT name, reg_no FROM $students_table WHERE wp_user_id = %d LIMIT 1",
                    $student_id
                ));
                $student_name = $student_record ? $student_record->name : $user->display_name;
                $student_reg = $student_record ? $student_record->reg_no : get_user_meta($student_id, 'student_reg_no', true);
                ?>
                <div class="ica-portal-welcome">
                    <h1>Welcome, <?php echo esc_html($student_name); ?></h1>
                    <p style="margin-top: 5px; color: #666; font-size: 14px;">ID: <?php echo esc_html($student_reg); ?></p>
                    <p>Your Learning Dashboard</p>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <div class="ica-portal-tabs">
                <a href="<?php echo esc_url(add_query_arg('tab', 'dashboard')); ?>" class="ica-tab-btn <?php echo $tab === 'dashboard' ? 'active' : ''; ?>">
                    📊 Dashboard
                </a>
                <a href="<?php echo esc_url(add_query_arg('tab', 'courses')); ?>" class="ica-tab-btn <?php echo $tab === 'courses' ? 'active' : ''; ?>">
                    📚 My Courses
                </a>
                <a href="<?php echo esc_url(add_query_arg('tab', 'available')); ?>" class="ica-tab-btn <?php echo $tab === 'available' ? 'active' : ''; ?>">
                    🔍 Available Courses
                </a>
                <a href="<?php echo esc_url(add_query_arg('tab', 'exams')); ?>" class="ica-tab-btn <?php echo $tab === 'exams' ? 'active' : ''; ?>">
                    📝 Exams
                </a>
                <a href="<?php echo esc_url(add_query_arg('tab', 'performance')); ?>" class="ica-tab-btn <?php echo $tab === 'performance' ? 'active' : ''; ?>">
                    📈 Performance
                </a>
                <a href="<?php echo esc_url(add_query_arg('tab', 'payments')); ?>" class="ica-tab-btn <?php echo $tab === 'payments' ? 'active' : ''; ?>">
                    💳 Payments
                </a>
            </div>

            <!-- Tab Contents -->
            <div class="ica-portal-content">
                <?php
                switch ($tab) {
                    case 'courses':
                        self::render_enrolled_courses($student_id);
                        break;
                    case 'available':
                        self::render_available_courses($student_id);
                        break;
                    case 'exams':
                        self::render_student_exams($student_id);
                        break;
                    case 'performance':
                        self::render_performance_analytics($student_id);
                        break;
                    case 'payments':
                        self::render_payment_history($student_id);
                        break;
                    default:
                        self::render_dashboard_overview($student_id);
                }
                ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render dashboard overview
     */
    private static function render_dashboard_overview($student_id) {
        global $wpdb;
        $students_table = ICA_LMS_DB::table_students();

        // Get student info
        $student = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $students_table WHERE wp_user_id = %d LIMIT 1",
            $student_id
        ));

        // Get enrolled courses count
        $enrolled_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT course_id) FROM $students_table WHERE wp_user_id = %d",
            $student_id
        ));

        // Get completed courses count
        $completed_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT course_id) FROM $students_table WHERE wp_user_id = %d AND status = 'completed'",
            $student_id
        ));

        // Get pending payments
        $pending_fees = $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(fee_amount - COALESCE(paid_amount, 0)), 0) FROM $students_table WHERE wp_user_id = %d",
            $student_id
        ));

        ?>
        <div class="ica-dashboard-cards">
            <div class="ica-card">
                <div class="ica-card-icon">📚</div>
                <div class="ica-card-content">
                    <h3><?php echo esc_html($enrolled_count); ?></h3>
                    <p>Enrolled Courses</p>
                </div>
            </div>
            <div class="ica-card">
                <div class="ica-card-icon">✅</div>
                <div class="ica-card-content">
                    <h3><?php echo esc_html($completed_count); ?></h3>
                    <p>Completed Courses</p>
                </div>
            </div>
            <div class="ica-card">
                <div class="ica-card-icon">💳</div>
                <div class="ica-card-content">
                    <h3>₹<?php echo esc_html(number_format($pending_fees, 2)); ?></h3>
                    <p>Pending Payment</p>
                </div>
            </div>
            <div class="ica-card">
                <div class="ica-card-icon">⭐</div>
                <div class="ica-card-content">
                    <h3><?php echo esc_html($student ? $student->reg_no : 'N/A'); ?></h3>
                    <p>Registration No.</p>
                </div>
            </div>
        </div>

        <div style="margin-top: 30px;">
            <h2>Quick Actions</h2>
            <div class="ica-quick-actions">
                <a href="<?php echo esc_url(add_query_arg('tab', 'courses')); ?>" class="ica-action-btn">
                    <strong>View My Courses</strong>
                    <span>Access your enrolled courses and materials</span>
                </a>
                <a href="<?php echo esc_url(add_query_arg('tab', 'exams')); ?>" class="ica-action-btn">
                    <strong>Take an Exam</strong>
                    <span>Test your knowledge</span>
                </a>
                <a href="<?php echo esc_url(add_query_arg('tab', 'available')); ?>" class="ica-action-btn">
                    <strong>Browse Courses</strong>
                    <span>Enroll in new courses</span>
                </a>
                <a href="<?php echo esc_url(add_query_arg('tab', 'performance')); ?>" class="ica-action-btn">
                    <strong>Check Performance</strong>
                    <span>Track your progress</span>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Render enrolled courses
     */
    private static function render_enrolled_courses($student_id) {
        global $wpdb;
        $students_table = ICA_LMS_DB::table_students();

        $enrolled = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $students_table WHERE wp_user_id = %d ORDER BY admission_date DESC",
            $student_id
        ));

        if (empty($enrolled)) {
            echo '<div class="ica-alert">You are not enrolled in any courses yet. <a href="' . esc_url(add_query_arg('tab', 'available')) . '">Browse available courses</a></div>';
            return;
        }

        ?>
        <h2>My Enrolled Courses</h2>
        <div class="ica-courses-grid">
            <?php foreach ($enrolled as $enrollment) : ?>
                <?php 
                $course = get_post($enrollment->course_id);
                if (!$course) continue;
                
                $progress = ICA_LMS_Course_Topics::get_student_course_progress($student_id, $course->ID);
                ?>
                <div class="ica-course-card">
                    <div class="ica-course-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 8px 8px 0 0;">
                        <h3 style="margin: 0; color: white;">
                            <?php echo esc_html($course->post_title); ?>
                        </h3>
                    </div>
                    <div class="ica-course-body" style="padding: 15px;">
                        <p><?php echo wp_trim_words($course->post_excerpt ?: $course->post_content, 15); ?></p>
                        
                        <div class="ica-progress-bar" style="margin: 15px 0;">
                            <div class="ica-progress-fill" style="width: <?php echo $progress; ?>%"></div>
                        </div>
                        <p style="margin: 0; text-align: center; font-size: 14px; color: #666;">
                            Progress: <strong><?php echo $progress; ?>%</strong>
                        </p>

                        <div style="margin-top: 15px; display: grid; gap: 10px;">
                            <button type="button" class="ica-btn ica-btn-primary ica-view-materials-btn" data-course-id="<?php echo esc_attr($course->ID); ?>">
                                📚 View Materials
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Materials Modal -->
        <div id="ica-materials-modal" class="ica-modal" style="display: none;">
            <div class="ica-modal-content">
                <span class="ica-close">&times;</span>
                <h2 id="ica-materials-title"></h2>
                <div id="ica-materials-list"></div>
            </div>
        </div>
        <?php
    }

    /**
     * Render available courses
     */
    private static function render_available_courses($student_id) {
        $all_courses = get_posts(array(
            'post_type' => 'courses',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ));

        // Get enrolled course IDs
        global $wpdb;
        $students_table = ICA_LMS_DB::table_students();
        $enrolled_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT course_id FROM $students_table WHERE wp_user_id = %d",
            $student_id
        ));

        if (empty($all_courses)) {
            echo '<div class="ica-alert">No courses available at this time.</div>';
            return;
        }

        ?>
        <h2>Available Courses</h2>
        <div class="ica-courses-grid">
            <?php foreach ($all_courses as $course) : ?>
                <?php 
                $is_enrolled = in_array($course->ID, (array) $enrolled_ids);
                $course_meta = get_post_meta($course->ID, 'course_fee', true);
                $fee = $course_meta ?: 0;
                ?>
                <div class="ica-course-card">
                    <div class="ica-course-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 20px; border-radius: 8px 8px 0 0;">
                        <h3 style="margin: 0; color: white;">
                            <?php echo esc_html($course->post_title); ?>
                        </h3>
                    </div>
                    <div class="ica-course-body" style="padding: 15px;">
                        <p><?php echo wp_trim_words($course->post_excerpt ?: $course->post_content, 15); ?></p>
                        
                        <?php if ($fee > 0) : ?>
                            <p style="font-size: 18px; font-weight: bold; color: #e74c3c; margin: 10px 0;">
                                Fee: ₹<?php echo esc_html(number_format($fee, 2)); ?>
                            </p>
                        <?php else : ?>
                            <p style="font-size: 18px; font-weight: bold; color: #27ae60; margin: 10px 0;">
                                FREE
                            </p>
                        <?php endif; ?>

                        <div style="margin-top: 15px;">
                            <?php if ($is_enrolled) : ?>
                                <button type="button" class="ica-btn ica-btn-success" disabled>
                                    ✓ Already Enrolled
                                </button>
                            <?php else : ?>
                                <button type="button" class="ica-btn ica-btn-primary ica-enroll-btn" data-course-id="<?php echo esc_attr($course->ID); ?>">
                                    Enroll Now
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Render student exams
     */
    private static function render_student_exams($student_id) {
        global $wpdb;
        $exams_table = ICA_LMS_Exam_Management::table_exams();
        $course_mapping_table = ICA_LMS_Exam_Management::table_course_mapping();
        $students_table = ICA_LMS_DB::table_students();

        // Get student's enrolled course IDs
        $enrolled_courses = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT course_id FROM $students_table WHERE wp_user_id = %d",
            $student_id
        ));

        if (empty($enrolled_courses)) {
            echo '<div class="ica-alert">You are not enrolled in any courses. Please enroll to take exams.</div>';
            return;
        }

        $placeholders = implode(',', array_fill(0, count($enrolled_courses), '%d'));
        $exams = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT e.* FROM $exams_table e
            JOIN $course_mapping_table cm ON e.id = cm.exam_id
            WHERE cm.course_id IN ($placeholders)
            ORDER BY e.start_date DESC",
            $enrolled_courses
        ));

        if (empty($exams)) {
            echo '<div class="ica-alert">No exams available for your enrolled courses.</div>';
            return;
        }

        ?>
        <h2>Available Exams</h2>
        <div class="ica-exams-list">
            <?php foreach ($exams as $exam) : ?>
                <?php 
                $now = current_time('Y-m-d H:i:s');
                $is_active = ($now >= $exam->start_date && $now <= $exam->end_date);
                $has_taken = self::has_student_taken_exam($student_id, $exam->id);
                ?>
                <div class="ica-exam-card" style="border: 1px solid #ddd; border-radius: 5px; padding: 20px; margin-bottom: 15px; background: #f9f9f9;">
                    <div style="display: grid; grid-template-columns: 1fr auto; gap: 20px; align-items: start;">
                        <div>
                            <h3 style="margin-top: 0;"><?php echo esc_html($exam->exam_title); ?></h3>
                            <p><?php echo esc_html($exam->exam_description); ?></p>
                            
                            <div style="margin: 15px 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px;">
                                <p><strong>Type:</strong> <?php echo esc_html(ucfirst($exam->exam_type)); ?></p>
                                <p><strong>Duration:</strong> <?php echo esc_html($exam->duration_hours); ?> hour(s)</p>
                                <p><strong>Total Marks:</strong> <?php echo esc_html($exam->total_marks); ?></p>
                                <p><strong>Pass Marks:</strong> <?php echo esc_html($exam->pass_marks); ?></p>
                            </div>

                            <p style="color: #666;">
                                <strong>Active:</strong> <?php echo date('M d, Y', strtotime($exam->start_date)); ?> - <?php echo date('M d, Y', strtotime($exam->end_date)); ?>
                            </p>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <?php if ($has_taken) : ?>
                                <button type="button" class="ica-btn ica-btn-info ica-view-result-btn" data-exam-id="<?php echo esc_attr($exam->id); ?>">
                                    📊 View Result
                                </button>
                            <?php elseif ($is_active) : ?>
                                <button type="button" class="ica-btn ica-btn-success ica-start-exam-btn" data-exam-id="<?php echo esc_attr($exam->id); ?>">
                                    ▶ Start Exam
                                </button>
                            <?php else : ?>
                                <button type="button" class="ica-btn" disabled>
                                    ⏳ Not Active
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Render performance analytics
     */
    private static function render_performance_analytics($student_id) {
        ?>
        <h2>Your Performance</h2>
        <div style="background: #f5f5f5; padding: 20px; border-radius: 5px; text-align: center;">
            <p style="font-size: 16px; color: #666;">Performance analytics Dashboard</p>
            <p>📊 Charts showing:<br>
            • Overall exam performance<br>
            • Course progress tracking<br>
            • Subject-wise performance<br>
            • Comparison with class average (coming soon)
            </p>
        </div>
        <?php
    }

    /**
     * Render payment history with individual payment records
     */
    private static function render_payment_history($student_id) {
        global $wpdb;
        $students_table = ICA_LMS_DB::table_students();

        $student_enrollments = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $students_table WHERE wp_user_id = %d",
            $student_id
        ));

        if (empty($student_enrollments)) {
            echo '<div class="ica-alert">No enrollment records found.</div>';
            return;
        }

        // Get payment summary - use the first student record's ID from database
        $first_enrollment = $student_enrollments[0];
        $first_student_db_id = isset($first_enrollment->id) ? (int)$first_enrollment->id : 0;
        $fee_type = isset($first_enrollment->fee_type) ? $first_enrollment->fee_type : 'installment';
        
        if ($first_student_db_id === 0) {
            echo '<div class="ica-alert">No enrollment records found.</div>';
            return;
        }
        
        // Ensure paid_amount is calculated for all enrollments
        foreach ($student_enrollments as $enrollment) {
            if (isset($enrollment->id)) {
                ICA_LMS_DB::update_student_paid_amounts((int)$enrollment->id);
            }
        }
        
        // Refresh enrollment data after calculation
        $student_enrollments = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $students_table WHERE wp_user_id = %d",
            $student_id
        ));
        
        $payment_summary = ICA_LMS_DB::get_payment_summary($first_student_db_id);
        
        // Get individual payment records
        $payments = ICA_LMS_DB::get_student_payments($first_student_db_id);

        ?>
        <h2>Payment History & Fee Details</h2>
        
        <!-- Fee Summary Section -->
        <div style="background: #f5f5f5; padding: 20px; border-radius: 5px; margin-bottom: 30px;">
            <h3 style="margin-top: 0;">Fee Summary</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                <div style="background: white; padding: 15px; border-radius: 5px; border-left: 4px solid #3498db;">
                    <div style="color: #7f8c8d; font-size: 12px; text-transform: uppercase; margin-bottom: 5px;">Total Fee</div>
                    <div style="font-size: 24px; font-weight: bold; color: #2c3e50;">₹<?php echo esc_html(number_format($payment_summary['total_fee'], 2)); ?></div>
                </div>
                <div style="background: white; padding: 15px; border-radius: 5px; border-left: 4px solid #27ae60;">
                    <div style="color: #7f8c8d; font-size: 12px; text-transform: uppercase; margin-bottom: 5px;">Amount Paid</div>
                    <div style="font-size: 24px; font-weight: bold; color: #27ae60;">₹<?php echo esc_html(number_format($payment_summary['paid_amount'], 2)); ?></div>
                </div>
                <div style="background: white; padding: 15px; border-radius: 5px; border-left: 4px solid <?php echo $payment_summary['balance_amount'] > 0 ? '#e74c3c' : '#27ae60'; ?>;">
                    <div style="color: #7f8c8d; font-size: 12px; text-transform: uppercase; margin-bottom: 5px;">Outstanding Balance</div>
                    <div style="font-size: 24px; font-weight: bold; color: <?php echo $payment_summary['balance_amount'] > 0 ? '#e74c3c' : '#27ae60'; ?>;">₹<?php echo esc_html(number_format($payment_summary['balance_amount'], 2)); ?></div>
                </div>
            </div>
        </div>

        <!-- Course-wise Fees Summary -->
        <h3 style="margin-top: 30px; margin-bottom: 15px;">Course-wise Fees</h3>
        <table class="ica-payment-table" style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
            <thead style="background: #2c3e50; color: #ffffff;">
                <tr>
                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #34495e; color: #ffffff; font-weight: 600;">Course</th>
                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #34495e; color: #ffffff; font-weight: 600;">Fee Amount</th>
                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #34495e; color: #ffffff; font-weight: 600;">Paid Amount</th>
                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #34495e; color: #ffffff; font-weight: 600;">Due Amount</th>
                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #34495e; color: #ffffff; font-weight: 600;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_fee = 0;
                $total_paid = 0;
                
                // Get payments for all student enrollments
                $payments_table = ICA_LMS_DB::table_payment_installments();
                $all_student_ids = wp_list_pluck($student_enrollments, 'id');
                
                foreach ($student_enrollments as $enrollment) : 
                    $course = get_post($enrollment->course_id);
                    
                    // Get paid amount from payments table for this student enrollment
                    $paid_amount = 0;
                    if (!empty($enrollment->id)) {
                        $paid_amount = (float) $wpdb->get_var($wpdb->prepare(
                            "SELECT COALESCE(SUM(amount), 0) FROM $payments_table WHERE student_id = %d AND status = 'completed'",
                            (int)$enrollment->id
                        ));
                    }
                    
                    $due = $enrollment->fee_amount - $paid_amount;
                    $total_fee += $enrollment->fee_amount;
                    $total_paid += $paid_amount;
                    
                    // Determine status based on payment
                    $fee_status = ($paid_amount >= $enrollment->fee_amount) ? 'approved' : 'pending';
                    ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px;"><?php echo esc_html($course ? $course->post_title : 'N/A'); ?></td>
                        <td style="padding: 12px;">₹<?php echo esc_html(number_format($enrollment->fee_amount, 2)); ?></td>
                        <td style="padding: 12px;">₹<?php echo esc_html(number_format($paid_amount, 2)); ?></td>
                        <td style="padding: 12px; font-weight: bold; color: <?php echo $due > 0 ? '#e74c3c' : '#27ae60'; ?>;">
                            ₹<?php echo esc_html(number_format($due, 2)); ?>
                        </td>
                        <td style="padding: 12px;">
                            <span style="padding: 4px 8px; border-radius: 3px; background: <?php echo $fee_status === 'approved' ? '#d4edda' : '#fff3cd'; ?>; color: <?php echo $fee_status === 'approved' ? '#155724' : '#856404'; ?>;">
                                <?php echo esc_html(ucfirst($fee_status)); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Individual Payment Records -->
        <h3 style="margin-top: 30px; margin-bottom: 15px;">Payment Transactions</h3>
        <?php if (empty($payments)): ?>
            <div class="ica-alert" style="padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 5px; color: #856404;">
                ℹ️ No payment records found. Once you make a payment through the admin dashboard, it will appear here.
            </div>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #2c3e50; color: #ffffff;">
                    <tr>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Installment</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Amount</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Payment Date</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Payment Method</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Status</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): 
                        $payment_label = $fee_type === 'one_time' ? 'Full Payment' : 'Installment #' . $payment['installment_number'];
                        ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 12px;"><?php echo esc_html($payment_label); ?></td>
                            <td style="padding: 12px; font-weight: bold;">₹<?php echo esc_html(number_format($payment['amount'], 2)); ?></td>
                            <td style="padding: 12px;"><?php echo esc_html($payment['payment_date']); ?></td>
                            <td style="padding: 12px;"><?php echo esc_html(ucfirst(str_replace('_', ' ', $payment['payment_method']))); ?></td>
                            <td style="padding: 12px;">
                                <span style="padding: 4px 8px; border-radius: 3px; background: #d4edda; color: #155724; font-weight: bold;">
                                    <?php echo esc_html(ucfirst($payment['status'])); ?>
                                </span>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <a href="javascript:void(0);" onclick="ica_print_student_receipt(<?php echo $payment['id']; ?>)" class="button button-small" style="padding: 6px 12px; text-decoration: none; background: #2c3e50; color: white; border-radius: 3px; cursor: pointer;">
                                    📄 Receipt
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Pending Installments Section -->
        <h3 style="margin-top: 30px; margin-bottom: 15px;">📋 Pending Payments</h3>
        <?php
        $pending_count = 0;
        for ($i = 1; $i <= $payment_summary['installment_count']; $i++) {
            $payment = array_filter($payments, function($p) use ($i) {
                return $p['installment_number'] == $i && $p['status'] === 'completed';
            });
            if (empty($payment)) {
                $pending_count++;
            }
        }
        
        if ($pending_count === 0): ?>
            <div class="ica-alert" style="padding: 15px; background: #d4edda; border: 1px solid #28a745; border-radius: 5px; color: #155724;">
                ✓ Congratulations! All payments are up to date.
            </div>
        <?php else: ?>
            <div style="background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; border-radius: 5px; margin-bottom: 20px;">
                <strong>You have <?php echo $pending_count; ?> pending installment(s) totaling ₹<?php echo esc_html(number_format($payment_summary['balance_amount'], 2)); ?></strong>
            </div>
            
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <thead style="background: #f5f5f5;">
                    <tr>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;"><?php echo $fee_type === 'one_time' ? 'Payment' : 'Installment'; ?></th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Amount</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    for ($i = 1; $i <= $payment_summary['installment_count']; $i++) {
                        $payment = array_filter($payments, function($p) use ($i) {
                            return $p['installment_number'] == $i && $p['status'] === 'completed';
                        });
                        if (empty($payment)) {
                            $per_installment = $payment_summary['per_installment'];
                            $label = $fee_type === 'one_time' ? 'Full Payment' : 'Installment #' . $i;
                            ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px;"><?php echo esc_html($label); ?></td>
                                <td style="padding: 12px; font-weight: bold;">₹<?php echo esc_html(number_format($per_installment, 2)); ?></td>
                                <td style="padding: 12px;"><span style="padding: 4px 8px; border-radius: 3px; background: #fff3cd; color: #856404; font-weight: bold;">PENDING</span></td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Payment Methods Available -->
        <h3 style="margin-top: 30px; margin-bottom: 15px;">💰 Available Payment Methods</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">
            
            <div style="background: white; border: 2px solid #e3e3e3; border-radius: 8px; padding: 20px; transition: all 0.3s;">
                <div style="font-size: 24px; margin-bottom: 12px;">💵</div>
                <h4 style="margin-top: 0; color: #2c3e50;">Cash Payment</h4>
                <p style="color: #7f8c8d; font-size: 14px; margin-bottom: 15px;">Pay directly in cash at the academy office during office hours.</p>
                <div style="background: #f0f0f0; padding: 10px; border-radius: 5px; font-size: 12px; color: #555; margin-bottom: 10px;">
                    <strong>Office Hours:</strong><br>
                    Mon-Fri: 9:00 AM - 5:00 PM<br>
                    Sat: 10:00 AM - 2:00 PM
                </div>
                <button onclick="alert('Please visit the academy office with your registration number and payment amount.');" class="button button-primary" style="width: 100%; padding: 10px; cursor: pointer; background: #27ae60; border: none; color: white; border-radius: 5px;">
                    Pay Cash
                </button>
            </div>

            <div style="background: white; border: 2px solid #e3e3e3; border-radius: 8px; padding: 20px; transition: all 0.3s;">
                <div style="font-size: 24px; margin-bottom: 12px;">💳</div>
                <h4 style="margin-top: 0; color: #2c3e50;">Online Payment</h4>
                <p style="color: #7f8c8d; font-size: 14px; margin-bottom: 15px;">Secure online payment via bank transfer or credit/debit card.</p>
                <div style="background: #f0f0f0; padding: 10px; border-radius: 5px; font-size: 12px; color: #555; margin-bottom: 10px;">
                    <strong>Methods:</strong><br>
                    • Bank Transfer<br>
                    • Credit/Debit Card<br>
                    • UPI (Coming Soon)
                </div>
                <button onclick="alert('Online payment integration coming soon. Please contact the admin for payment details.');" class="button button-primary" style="width: 100%; padding: 10px; cursor: pointer; background: #3498db; border: none; color: white; border-radius: 5px;">
                    Pay Online
                </button>
            </div>

            <div style="background: white; border: 2px solid #e3e3e3; border-radius: 8px; padding: 20px; transition: all 0.3s;">
                <div style="font-size: 24px; margin-bottom: 12px;">✉️</div>
                <h4 style="margin-top: 0; color: #2c3e50;">Cheque Payment</h4>
                <p style="color: #7f8c8d; font-size: 14px; margin-bottom: 15px;">Submit cheque payment with your registration number.</p>
                <div style="background: #f0f0f0; padding: 10px; border-radius: 5px; font-size: 12px; color: #555; margin-bottom: 10px;">
                    <strong>Payee:</strong><br>
                    Impulse Academy<br>
                    <strong>Email:</strong><br>
                    admin@impulseacademy.com
                </div>
                <button onclick="alert('Please contact the academy for cheque payment instructions.');" class="button button-primary" style="width: 100%; padding: 10px; cursor: pointer; background: #9b59b6; border: none; color: white; border-radius: 5px;">
                    Cheque Payment
                </button>
            </div>

        </div>

        <!-- Important Information -->
        <div style="background: #ecf0f1; padding: 15px; border-left: 4px solid #34495e; border-radius: 5px; margin-top: 20px;">
            <strong>📌 Important Information:</strong>
            <ul style="margin: 10px 0; padding-left: 20px; color: #555;">
                <li>Maintain payment schedule to avoid late fees</li>
                <li>Always request a receipt after making payment</li>
                <li>Keep your registration number handy for all payments</li>
                <li>Contact admin for payment plan adjustments if needed</li>
                <li>Downloaded receipts can be used as proof of payment</li>
            </ul>
        </div>

        <!-- Payment Submission Form -->
        <?php if ($pending_count > 0): ?>
        <h3 style="margin-top: 40px; margin-bottom: 15px; padding-top: 20px; border-top: 2px solid #ecf0f1;">📝 Notify Admin of Payment</h3>
        <p style="color: #7f8c8d; margin-bottom: 20px;">Have you made a payment? Submit your payment details below to notify the admin. The admin will verify and update your payment record.</p>
        
        <form id="ica_payment_notification_form" style="background: white; padding: 20px; border: 2px solid #e3e3e3; border-radius: 8px;">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: bold;">Payment Method *</label>
                <select name="payment_method" id="payment_method" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                    <option value="">-- Select Payment Method --</option>
                    <option value="cash">Cash</option>
                    <option value="cheque">Cheque</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="online">Online</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: bold;">Amount Paid (₹) *</label>
                <input type="number" name="amount" id="payment_amount" step="0.01" placeholder="Enter amount" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: bold;">Transaction ID / Reference Number</label>
                <input type="text" name="transaction_id" id="transaction_id" placeholder="Bank reference, cheque number, UPI ID, etc." style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: bold;">Additional Notes</label>
                <textarea name="notes" id="payment_notes" placeholder="Any additional information about your payment" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; height: 100px; resize: vertical;"></textarea>
            </div>

            <div id="payment_notification_message" style="margin-bottom: 20px; display: none; padding: 12px; border-radius: 5px;"></div>

            <button type="submit" class="button button-primary" style="width: 100%; padding: 12px; background: #3498db; color: white; border: none; border-radius: 5px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.3s;">
                Submit Payment Notification
            </button>
        </form>

        <script>
        jQuery(document).ready(function() {
            jQuery('#ica_payment_notification_form').on('submit', function(e) {
                e.preventDefault();
                
                const form = this;
                const messageDiv = jQuery('#payment_notification_message');
                
                jQuery.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    method: 'POST',
                    data: {
                        action: 'ica_submit_payment_notification',
                        nonce: '<?php echo wp_create_nonce('ica_student_portal_nonce'); ?>',
                        payment_method: jQuery('#payment_method').val(),
                        amount: jQuery('#payment_amount').val(),
                        transaction_id: jQuery('#transaction_id').val(),
                        notes: jQuery('#payment_notes').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            messageDiv.addClass('ica-alert').css('background', '#d4edda').css('color', '#155724').css('border', '1px solid #c3e6cb');
                            messageDiv.html('✓ ' + response.data.message).show();
                            
                            // Clear form
                            form.reset();
                            
                            // Hide message after 5 seconds
                            setTimeout(function() {
                                messageDiv.fadeOut();
                            }, 5000);
                        } else {
                            messageDiv.addClass('ica-alert').css('background', '#f8d7da').css('color', '#721c24').css('border', '1px solid #f5c6cb');
                            messageDiv.html('✕ Error: ' + response.data).show();
                        }
                    },
                    error: function() {
                        messageDiv.addClass('ica-alert').css('background', '#f8d7da').css('color', '#721c24').css('border', '1px solid #f5c6cb');
                        messageDiv.html('✕ Error submitting notification. Please try again.').show();
                    }
                });
            });
        });
        </script>
        <?php endif; ?>
        <?php
    }

    /**
     * AJAX: Enroll course
     */
    public static function ajax_enroll_course() {
        check_ajax_referer('ica_student_portal_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('Not logged in');
        }

        $student_id = get_current_user_id();
        $course_id = (int) $_POST['course_id'];

        // Check if student already enrolled
        global $wpdb;
        $students_table = ICA_LMS_DB::table_students();
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $students_table WHERE wp_user_id = %d AND course_id = %d",
            $student_id,
            $course_id
        ));

        if ($exists) {
            wp_send_json_error('Already enrolled');
        }

        // Add enrollment
        $result = $wpdb->insert(
            $students_table,
            array(
                'user_id' => $student_id,
                'course_id' => $course_id,
                'reg_no' => sanitize_text_field(get_user_meta($student_id, 'student_reg_no', true) ?: 'N/A'),
                'name' => sanitize_text_field($user->display_name),
                'fee_amount' => (float) get_post_meta($course_id, 'course_fee', true) ?: 0,
                'fee_status' => 'pending',
                'status' => 'active',
                'enrolled_date' => current_time('mysql'),
            ),
            array('%d', '%d', '%s', '%s', '%f', '%s', '%s', '%s')
        );

        if ($result) {
            wp_send_json_success('Enrolled successfully');
        } else {
            wp_send_json_error('Enrollment failed');
        }
    }

    /**
     * AJAX: Get course materials
     */
    public static function ajax_get_course_materials() {
        check_ajax_referer('ica_student_portal_nonce', 'nonce');

        $course_id = (int) $_POST['course_id'];

        if (!class_exists('ICA_LMS_Course_Topics')) {
            wp_send_json_error('Course topics not available');
        }

        $topics = ICA_LMS_Course_Topics::get_course_topics($course_id);

        if (empty($topics)) {
            wp_send_json_success(array('html' => '<p>No topics available yet.</p>'));
        }

        $html = '<ul style="list-style: none; padding: 0;">';
        foreach ($topics as $topic) {
            $post_id = isset($topic['post_id']) ? (int)$topic['post_id'] : (int)$topic->post_id;
            $post = get_post($post_id);
            
            if (!$post) {
                continue; // Skip deleted posts
            }
            
            $html .= '<li style="padding: 10px; border-bottom: 1px solid #eee;">';
            $html .= '<a href="' . esc_url(get_permalink($post)) . '" target="_blank" style="text-decoration: none; color: #2196f3;">';
            $html .= '<strong>' . esc_html($post->post_title) . '</strong>';
            if (!empty($topic['author_name']) || !empty($topic->author_name)) {
                $author = isset($topic['author_name']) ? $topic['author_name'] : $topic->author_name;
                $html .= '<br><small style="color: #666;">By ' . esc_html($author) . '</small>';
            }
            $html .= '</a>';
            $html .= '</li>';
        }
        $html .= '</ul>';

        wp_send_json_success(array('html' => $html));
    }

    /**
     * AJAX: Start exam
     */
    public static function ajax_start_exam() {
        check_ajax_referer('ica_student_portal_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('Not logged in');
        }

        $exam_id = (int) $_POST['exam_id'];
        // Implementation will be added in next phase
        wp_send_json_success(array('message' => 'Exam interface loading...'));
    }

    /**
     * AJAX: Submit exam
     */
    public static function ajax_submit_exam() {
        check_ajax_referer('ica_student_portal_nonce', 'nonce');
        // Implementation will be added in next phase
        wp_send_json_success(array('message' => 'Exam submitted'));
    }

    /**
     * AJAX: Get payment history
     */
    public static function ajax_get_payment_history() {
        check_ajax_referer('ica_student_portal_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('Not logged in');
        }

        $student_id = get_current_user_id();
        // Implementation will be added
        wp_send_json_success(array('message' => 'Payment history loaded'));
    }

    /**
     * AJAX: Submit payment notification
     * Students notify admin about payment made
     */
    public static function ajax_submit_payment_notification() {
        check_ajax_referer('ica_student_portal_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('Not logged in');
        }

        $user_id = get_current_user_id();
        $user = get_user_by('ID', $user_id);
        $payment_method = sanitize_text_field($_POST['payment_method']);
        $amount = (float) $_POST['amount'];
        $transaction_id = sanitize_text_field($_POST['transaction_id']);
        $notes = sanitize_textarea_field($_POST['notes']);

        if ($amount <= 0) {
            wp_send_json_error('Invalid amount');
        }

        // Get student ID from user
        global $wpdb;
        $students_table = ICA_LMS_DB::table_students();
        $student = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $students_table WHERE user_id = %d LIMIT 1",
            $user_id
        ));

        if (!$student) {
            wp_send_json_error('Student record not found');
        }

        // Store payment notification
        $notification_data = array(
            'user_id' => $user_id,
            'student_id' => $student->id,
            'payment_method' => $payment_method,
            'amount' => $amount,
            'transaction_id' => $transaction_id,
            'user_notes' => $notes,
            'student_name' => $user->display_name,
            'created_at' => current_time('mysql'),
            'status' => 'pending'
        );

        // Send email to admin about payment notification
        $admin_email = get_option('admin_email');
        $subject = "Payment Notification from Student: {$user->display_name}";
        $message = "
        <h2>Payment Notification Received</h2>
        <p><strong>Student Name:</strong> {$user->display_name}</p>
        <p><strong>Amount:</strong> ₹" . number_format($amount, 2) . "</p>
        <p><strong>Payment Method:</strong> " . ucfirst(str_replace('_', ' ', $payment_method)) . "</p>
        <p><strong>Transaction ID/Reference:</strong> {$transaction_id}</p>
        <p><strong>Student Notes:</strong><br>{$notes}</p>
        <p>Please verify this payment in the Fee Management section of the admin dashboard.</p>
        ";

        wp_mail(
            $admin_email,
            $subject,
            $message,
            array('Content-Type: text/html; charset=UTF-8')
        );

        wp_send_json_success(array(
            'message' => 'Payment notification submitted successfully! Admin will verify your payment soon.',
            'data' => $notification_data
        ));
    }

    /**
     * Render login page HTML only (scripts must be enqueued in template)
     */
    public static function render_login_page() {
        ?>
        <div class="ica-login-container">
            <div class="ica-login-background"></div>
            <div class="ica-login-box">
                <div class="ica-login-header">
                    <h1>Student Portal</h1>
                    <p>Learning Management System</p>
                </div>

                <form id="ica-login-form" class="ica-login-form">
                    <div class="ica-form-group">
                        <label for="log">Username or Email</label>
                        <input type="text" id="log" name="log" class="ica-form-input" placeholder="Enter your username or email" required>
                        <span class="ica-error-message" id="log-error"></span>
                    </div>

                    <div class="ica-form-group">
                        <label for="pwd">Password</label>
                        <input type="password" id="pwd" name="pwd" class="ica-form-input" placeholder="Enter your password" required>
                        <span class="ica-error-message" id="pwd-error"></span>
                    </div>

                    <div class="ica-form-group ica-checkbox-group">
                        <label for="rememberme">
                            <input type="checkbox" id="rememberme" name="rememberme" value="1">
                            <span>Remember me</span>
                        </label>
                    </div>

                    <?php 
                    if (isset($_GET['login']) && $_GET['login'] === 'failed') {
                        echo '<div class="ica-alert ica-alert-error">Invalid username or password</div>';
                    }
                    ?>

                    <div class="ica-form-group">
                        <button type="submit" class="ica-login-btn">Sign In</button>
                    </div>

                    <div class="ica-login-footer">
                        <p><a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="ica-forgot-link">Forgot Password?</a></p>
                        <p>Not a student yet? <a href="<?php echo esc_url(home_url('/contact')); ?>" class="ica-contact-link">Contact Us</a></p>
                    </div>
                </form>

                <div id="ica-login-message" class="ica-alert" style="display: none;"></div>
            </div>
        </div>
        <?php
    }

    /**
     * Render student dashboard for template
     */
    public static function render_student_dashboard() {
        // Enqueue styles and scripts
        wp_enqueue_style('ica-student-portal-style', ICA_LMS_URL . '/assets/css/student-portal.css', array(), filemtime(ICA_LMS_PATH . '/assets/css/student-portal.css'));
        wp_enqueue_script('ica-student-portal-script', ICA_LMS_URL . '/assets/js/student-portal.js', array('jquery'), filemtime(ICA_LMS_PATH . '/assets/js/student-portal.js'), true);

        wp_localize_script('ica-student-portal-script', 'ICAStudentPortal', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ica_student_portal_nonce'),
            'student_id' => get_current_user_id(),
        ));

        // Output minimal HTML structure and let shortcode handle content
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php bloginfo('name'); ?> - Student Dashboard</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            <?php wp_head(); ?>
        </head>
        <body <?php body_class('ica-student-dashboard-page'); ?>>
            <div class="ica-dashboard-wrapper">
                <?php echo do_shortcode('[ica_lms_student_dashboard]'); ?>
            </div>
            <?php wp_footer(); ?>
        </body>
        </html>
        <?php
        exit;
    }

    /**
     * Check if student has taken exam
     */
    private static function has_student_taken_exam($student_id, $exam_id) {
        global $wpdb;
        $submissions_table = ICA_LMS_Exam_Management::table_submissions();
        
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $submissions_table WHERE exam_id = %d AND user_id = %d LIMIT 1",
            $exam_id,
            $student_id
        ));

        return $result > 0;
    }

    /**
     * Print payment receipt in new tab
     */
    public static function print_student_receipt() {
        $payment_id = isset($_GET['payment_id']) ? (int)$_GET['payment_id'] : 0;
        $nonce = isset($_GET['nonce']) ? $_GET['nonce'] : '';

        if (!wp_verify_nonce($nonce, 'ica_student_receipt')) {
            wp_die('Unauthorized access');
        }

        if (!$payment_id) {
            wp_die('Invalid payment ID');
        }

        $payment = ICA_LMS_DB::get_payment($payment_id);
        if (!$payment) {
            wp_die('Payment not found');
        }

        $student = ICA_LMS_DB::get_student($payment['student_id']);
        if (!$student) {
            wp_die('Student not found');
        }

        // Verify that current user owns this payment
        $current_user_id = get_current_user_id();
        if ((int)$student['wp_user_id'] !== (int)$current_user_id && !current_user_can('manage_options')) {
            wp_die('You are not authorized to view this receipt');
        }

        // Determine if this is a one-time payment
        $is_one_time = isset($student['fee_type']) && $student['fee_type'] === 'one_time';
        $payment_label = $is_one_time ? 'Full Payment' : 'Installment #' . $payment['installment_number'];

        // Get payment summary for balance details
        $summary = ICA_LMS_DB::get_payment_summary($payment['student_id']);

        // Generate receipt content
        ob_start();
        ?>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Payment Receipt</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
                .receipt-container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #2c3e50; padding-bottom: 20px; }
                .header h1 { margin: 0; color: #2c3e50; }
                .header p { margin: 5px 0; color: #666; }
                .receipt-section { margin-bottom: 20px; }
                .receipt-section label { font-weight: bold; color: #2c3e50; display: inline-block; width: 150px; }
                table { width: 100%; margin-top: 20px; border-collapse: collapse; }
                table th, table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
                table th { background: #34495e; color: white; font-weight: bold; }
                .total { font-weight: bold; background: #ecf0f1; }
                .balance { font-weight: bold; background: #fff3cd; color: #856404; }
                .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #ddd; padding-top: 20px; }
                @media print {
                    body { background: white; margin: 0; }
                    .receipt-container { box-shadow: none; }
                }
            </style>
            <script>
                window.onload = function() {
                    window.print();
                };
            </script>
        </head>
        <body>
            <div class="receipt-container">
                <div class="header">
                    <h1>Payment Receipt</h1>
                    <p>Impulse Academy - Learning Management System</p>
                </div>

                <div class="receipt-section">
                    <label>Student Name:</label> <?php echo esc_html($student['name']); ?>
                </div>

                <div class="receipt-section">
                    <label>Registration Number:</label> <?php echo esc_html($student['reg_no']); ?>
                </div>

                <div class="receipt-section">
                    <label>Mobile Number:</label> <?php echo esc_html($student['mobile_no']); ?>
                </div>

                <div class="receipt-section">
                    <label><?php echo $is_one_time ? 'Payment Type:' : 'Installment:'; ?></label> <?php echo $is_one_time ? 'Full Payment' : '#' . $payment['installment_number']; ?>
                </div>

                <table>
                    <tr>
                        <th>Description</th>
                        <th>Amount</th>
                    </tr>
                    <tr>
                        <td><?php echo $payment_label; ?></td>
                        <td>₹<?php echo esc_html(number_format($payment['amount'], 2)); ?></td>
                    </tr>
                    <tr class="total">
                        <td>Total Amount Paid (This Payment)</td>
                        <td>₹<?php echo esc_html(number_format($payment['amount'], 2)); ?></td>
                    </tr>
                </table>

                <table style="margin-top: 30px;">
                    <tr>
                        <th>Fee Summary</th>
                        <th>Amount</th>
                    </tr>
                    <tr>
                        <td>Total Course Fee</td>
                        <td>₹<?php echo esc_html(number_format($summary['total_fee'], 2)); ?></td>
                    </tr>
                    <tr>
                        <td>Total Paid So Far</td>
                        <td>₹<?php echo esc_html(number_format($summary['paid_amount'], 2)); ?></td>
                    </tr>
                    <tr class="balance">
                        <td>Balance Outstanding</td>
                        <td>₹<?php echo esc_html(number_format($summary['balance_amount'], 2)); ?></td>
                    </tr>
                </table>

                <div class="receipt-section" style="margin-top: 20px;">
                    <label>Payment Method:</label> <?php echo esc_html($payment['payment_method']); ?>
                </div>

                <div class="receipt-section">
                    <label>Payment Date:</label> <?php echo esc_html($payment['payment_date']); ?>
                </div>

                <?php if (!empty($payment['transaction_id'])) : ?>
                    <div class="receipt-section">
                        <label>Transaction ID:</label> <?php echo esc_html($payment['transaction_id']); ?>
                    </div>
                <?php endif; ?>

                <?php if ($payment['status'] === 'completed') : ?>
                    <div class="receipt-section" style="padding: 12px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 3px; color: #155724;">
                        <strong>✓ Payment Completed</strong>
                    </div>
                <?php endif; ?>

                <div class="footer">
                    <p>This is an automated receipt. For any queries, please contact the academy.</p>
                    <p>Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
                    <p style="margin-top: 10px; color: #bbb;">Receipt ID: RCP-<?php echo str_pad($payment_id, 6, '0', STR_PAD_LEFT); ?></p>
                </div>
            </div>
        </body>
        </html>
        <?php
        $html = ob_get_clean();

        // Output as HTML for viewing/printing
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit;
    }
}
