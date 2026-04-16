<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!defined('ICA_LMS_VERSION')) {
    define('ICA_LMS_VERSION', '1.4.0');
}

if (!defined('ICA_LMS_PATH')) {
    define('ICA_LMS_PATH', __DIR__);
}

if (!defined('ICA_LMS_URL')) {
    define('ICA_LMS_URL', get_stylesheet_directory_uri() . '/lms');
}

require_once ICA_LMS_PATH . '/includes/class-ica-lms-db.php';
require_once ICA_LMS_PATH . '/includes/class-ica-lms-cpts.php';
require_once ICA_LMS_PATH . '/includes/class-ica-lms-pages.php';
require_once ICA_LMS_PATH . '/includes/class-ica-lms-qr-code.php';
require_once ICA_LMS_PATH . '/includes/class-ica-lms-id-card.php';
require_once ICA_LMS_PATH . '/includes/class-ica-lms-user-roles.php';
require_once ICA_LMS_PATH . '/includes/class-ica-lms-user-management.php';
require_once ICA_LMS_PATH . '/includes/class-ica-lms-course-topics.php';
require_once ICA_LMS_PATH . '/includes/class-ica-lms-exam-management.php';
require_once ICA_LMS_PATH . '/includes/class-ica-lms-student-portal.php';
require_once ICA_LMS_PATH . '/includes/class-ica-lms-admin-subject.php';
require_once ICA_LMS_PATH . '/includes/class-ica-lms-admin-student.php';
require_once ICA_LMS_PATH . '/includes/class-ica-lms-admin-teacher.php';
require_once ICA_LMS_PATH . '/includes/class-ica-lms-admin-fees.php';
require_once ICA_LMS_PATH . '/includes/class-ica-lms-api.php';
require_once ICA_LMS_PATH . '/includes/class-ica-lms.php';

ICA_LMS::init();

// CRITICAL: Run role setup very early to ensure roles exist with all capabilities before any user checks
add_action('plugins_loaded', 'ica_lms_early_role_setup', 1);
function ica_lms_early_role_setup() {
    // This runs very early, before even wp_loaded
    // Ensure roles are set up and have all necessary capabilities
    if (class_exists('ICA_LMS_User_Roles')) {
        ICA_LMS_User_Roles::register_roles();
        ICA_LMS_User_Roles::ensure_role_capabilities();
    }
}

