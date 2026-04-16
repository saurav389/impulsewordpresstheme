<?php
if (!defined('ABSPATH')) {
    exit;
}

class ICA_LMS {
    public static function init() {
        ICA_LMS_User_Roles::init();
        ICA_LMS_User_Management::init();
        ICA_LMS_CPTs::init();
        ICA_LMS_Course_Topics::init();
        ICA_LMS_Exam_Management::init();
        ICA_LMS_Pages::init();
        ICA_LMS_Student_Portal::init();
        ICA_LMS_Admin_Student::init();
        ICA_LMS_Admin_Subject::init();
        ICA_LMS_Admin_Teacher::init();
        ICA_LMS_Admin_Fees::init();
        ICA_LMS_API::init();

        add_action('init', array(__CLASS__, 'initialize_db'));
        add_action('init', array(__CLASS__, 'maybe_flush_rewrite'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_assets'));
        
        // Login AJAX handlers
        add_action('wp_ajax_nopriv_ica_student_login', array(__CLASS__, 'ajax_student_login'));
        add_action('wp_ajax_ica_student_login', array(__CLASS__, 'ajax_student_login'));
    }

    public static function initialize_db() {
        ICA_LMS_DB::maybe_install();
    }

    public static function maybe_flush_rewrite() {
        $version = get_option('ica_lms_rewrite_version');
        if ($version === ICA_LMS_VERSION) {
            return;
        }

        flush_rewrite_rules(false);
        update_option('ica_lms_rewrite_version', ICA_LMS_VERSION);
    }

    public static function enqueue_assets() {
        wp_enqueue_style(
            'ica-lms-style',
            ICA_LMS_URL . '/assets/css/lms.css',
            array(),
            filemtime(ICA_LMS_PATH . '/assets/css/lms.css')
        );

        wp_enqueue_script(
            'ica-lms-script',
            ICA_LMS_URL . '/assets/js/lms.js',
            array('jquery'),
            filemtime(ICA_LMS_PATH . '/assets/js/lms.js'),
            true
        );

        wp_localize_script('ica-lms-script', 'ICALMS', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ica_lms_nonce'),
        ));
    }

    public static function enqueue_admin_assets($hook) {
        if ($hook !== 'toplevel_page_ica-lms') {
            return;
        }

        $css = '
            .ica-lms-admin-cards{display:grid;grid-template-columns:repeat(4,minmax(180px,1fr));gap:12px;margin:18px 0 24px}
            .ica-lms-admin-card{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:16px;display:flex;flex-direction:column;gap:6px}
            .ica-lms-admin-card strong{font-size:26px;line-height:1.1}
            .ica-lms-admin-card span{color:#50575e}
            @media(max-width:1000px){.ica-lms-admin-cards{grid-template-columns:repeat(2,minmax(180px,1fr))}}
        ';

        wp_register_style('ica-lms-admin-inline', false, array(), ICA_LMS_VERSION);
        wp_enqueue_style('ica-lms-admin-inline');
        wp_add_inline_style('ica-lms-admin-inline', $css);
    }

    /**
     * AJAX: Student login handler
     */
    public static function ajax_student_login() {
        // Check nonce without dying on failure
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
        if (!$nonce || !wp_verify_nonce($nonce, 'ica_student_login')) {
            wp_send_json_error('Security check failed. Please refresh the page and try again.');
        }

        $username = isset($_POST['log']) ? sanitize_text_field($_POST['log']) : '';
        $password = isset($_POST['pwd']) ? $_POST['pwd'] : '';
        $rememberme = isset($_POST['rememberme']) ? (int) $_POST['rememberme'] : 0;

        // Validate inputs
        if (empty($username) || empty($password)) {
            wp_send_json_error('Username and password are required');
        }

        // Attempt authentication
        $user = wp_authenticate($username, $password);

        if (is_wp_error($user)) {
            wp_send_json_error('Invalid username or password');
        }

        // Check if user is a student or admin
        $user_roles = (array) $user->roles;
        $is_student = in_array(ICA_LMS_User_Roles::STUDENT_ROLE, $user_roles);
        $is_admin = in_array('administrator', $user_roles);

        if (!$is_student && !$is_admin) {
            wp_send_json_error('You do not have access to the student portal');
        }

        // Log the user in
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, $rememberme);
        do_action('wp_login', $user->user_login, $user);

        // Redirect to student portal
        $redirect_url = home_url('student') . '?tab=dashboard';
        
        // Success
        wp_send_json_success(array(
            'message' => 'Login successful',
            'redirect' => $redirect_url,
        ));
    }
}
