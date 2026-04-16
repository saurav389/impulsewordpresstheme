<?php
/**
 * Template Name: Student Portal
 * Description: Student login and dashboard portal
 */

// If not logged in, show login page
if (!is_user_logged_in()) {
    // Enqueue login styles and scripts BEFORE wp_head()
    $login_css_path = dirname(__FILE__) . '/lms/assets/css/student-login.css';
    $login_css_ver = file_exists($login_css_path) ? filemtime($login_css_path) : '1.0.0';
    
    wp_enqueue_style(
        'ica-login-style',
        get_stylesheet_directory_uri() . '/lms/assets/css/student-login.css',
        array(),
        $login_css_ver
    );

    // Enqueue jQuery
    wp_enqueue_script('jquery');
    
    $login_js_path = dirname(__FILE__) . '/lms/assets/js/student-login.js';
    $login_js_ver = file_exists($login_js_path) ? filemtime($login_js_path) : '1.0.0';
    
    wp_enqueue_script(
        'ica-login-script',
        get_stylesheet_directory_uri() . '/lms/assets/js/student-login.js',
        array('jquery'),
        $login_js_ver,
        true
    );

    // Localize script with AJAX data
    $redirect_url = home_url('student') . '?tab=dashboard';
    wp_localize_script('ica-login-script', 'ICALogin', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ica_student_login'),
        'redirect_url' => $redirect_url,
    ));
    
    // Output full HTML with login page (no theme header/footer)
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php bloginfo('name'); ?> - Student Login</title>
        <link rel="dns-prefetch" href="//fonts.googleapis.com">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            html, body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                background: #f5f7fa;
                color: #333;
                height: 100%;
            }
        </style>
        <?php wp_head(); ?>
    </head>
    <body <?php body_class('ica-student-login-page'); ?>>
        <?php ICA_LMS_Student_Portal::render_login_page(); ?>
        <?php wp_footer(); ?>
    </body>
    </html>
    <?php
    exit;
}

// User is logged in - check if student or admin
$user = wp_get_current_user();
$user_roles = (array) $user->roles;

// Define constants if not already defined
if (!defined('ICA_LMS_STUDENT_ROLE')) {
    define('ICA_LMS_STUDENT_ROLE', 'ica_lms_student');
}

$is_student = in_array(ICA_LMS_STUDENT_ROLE, $user_roles);
$is_admin = in_array('administrator', $user_roles);

if (!$is_student && !$is_admin) {
    // User is logged in but not a student or admin
    get_header();
    ?>
    <div style="padding: 40px; text-align: center; max-width: 600px; margin: 0 auto;">
        <h2 style="color: #e74c3c;">Access Denied</h2>
        <p style="font-size: 16px; color: #555; margin-bottom: 20px;">
            This portal is only accessible to students and administrators.<br>
            You are currently logged in as: <strong><?php echo esc_html($user->display_name); ?></strong>
        </p>
        <p>
            <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="button" style="background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
                Logout
            </a>
        </p>
    </div>
    <?php
    get_footer();
    exit;
}

// For dashboard, output the full HTML with the shortcode
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php bloginfo('name'); ?> - Student Dashboard</title>
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa3IW2lEnf3lGY6i7IYLEcRoS2j5OyKLNR5+kx0wNMf1i7ZCLL8Ww2+YEJXlAH6pxzHI5QJlY8gVg3d50Q==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f7fa;
            color: #333;
        }
    </style>
    <?php wp_head(); ?>
</head>
<body <?php body_class('ica-student-dashboard-page'); ?>>
    <div class="ica-dashboard-wrapper">
        <?php
        // Output the student dashboard via shortcode
        echo do_shortcode('[ica_lms_student_dashboard]');
        ?>
    </div>
    <?php wp_footer(); ?>
</body>
</html>
<?php
exit;