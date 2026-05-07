<?php
if (!defined('ABSPATH')) {
    exit;
}

$ica_lms_bootstrap = get_stylesheet_directory() . '/lms/bootstrap.php';
if (file_exists($ica_lms_bootstrap)) {
    require_once $ica_lms_bootstrap;
}

$impulse_banner_manager = get_stylesheet_directory() . '/inc/class-impulse-banner-manager.php';
if (file_exists($impulse_banner_manager)) {
    require_once $impulse_banner_manager;
}

/**
 * TEMPORARY: Force role reset and cache clear for teacher/student post issues
 * Remove this after you verify posts can be created
 */
add_action('admin_init', function() {
    if (current_user_can('manage_options') && isset($_GET['ica_reset_roles'])) {
        // Clear WordPress caches
        wp_cache_flush();
        delete_transient('ica_lms_rewrite_version');
        
        // Force role re-registration
        remove_role('ica_lms_teacher');
        remove_role('ica_lms_student');
        
        // Trigger init hooks to re-register roles
        do_action('init');
        
        error_log('=== ICA LMS: Forced role reset and cache clear ===');
        wp_safe_remote_post(admin_url('admin-ajax.php'), array(
            'blocking' => false,
        ));
        
        wp_redirect(remove_query_arg('ica_reset_roles'));
        exit;
    }
}, 1);

function impulse_clone_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'gallery', 'caption', 'style', 'script'));
    
    // Register navigation menu
    register_nav_menus(array(
        'primary-menu' => 'Primary Navigation Menu',
        'footer-menu' => 'Footer Menu'
    ));
}
add_action('after_setup_theme', 'impulse_clone_setup');

function impulse_clone_get_admin_login_page_id() {
    static $page_id = null;

    if (null !== $page_id) {
        return $page_id;
    }

    $pages = get_posts(array(
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_key'       => '_wp_page_template',
        'meta_value'     => 'wp-admin.php',
    ));

    $page_id = !empty($pages) ? (int) $pages[0] : 0;

    return $page_id;
}

function impulse_clone_get_admin_login_page_url($args = array()) {
    $page_id = impulse_clone_get_admin_login_page_id();

    if (!$page_id) {
        return '';
    }

    $url = get_permalink($page_id);

    if (!$url) {
        return '';
    }

    return empty($args) ? $url : add_query_arg($args, $url);
}

add_filter('login_url', function($login_url, $redirect, $force_reauth) {
    $custom_login_url = impulse_clone_get_admin_login_page_url();

    if (!$custom_login_url) {
        return $login_url;
    }

    $args = array();

    if (!empty($redirect)) {
        $args['redirect_to'] = $redirect;
    }

    if ($force_reauth) {
        $args['reauth'] = '1';
    }

    return impulse_clone_get_admin_login_page_url($args);
}, 10, 3);

add_filter('logout_url', function($logout_url, $redirect) {
    $custom_logout_url = impulse_clone_get_admin_login_page_url(array('action' => 'logout'));

    if (!$custom_logout_url) {
        return $logout_url;
    }

    if (!empty($redirect)) {
        $custom_logout_url = add_query_arg('redirect_to', $redirect, $custom_logout_url);
    }

    return wp_nonce_url($custom_logout_url, 'log-out');
}, 10, 2);

add_filter('lostpassword_url', function($lostpassword_url, $redirect) {
    $custom_lostpassword_url = impulse_clone_get_admin_login_page_url(array('action' => 'lostpassword'));

    if (!$custom_lostpassword_url) {
        return $lostpassword_url;
    }

    if (!empty($redirect)) {
        $custom_lostpassword_url = add_query_arg('redirect_to', $redirect, $custom_lostpassword_url);
    }

    return $custom_lostpassword_url;
}, 10, 2);

add_filter('register_url', function($register_url) {
    $custom_register_url = impulse_clone_get_admin_login_page_url(array('action' => 'register'));

    return $custom_register_url ?: $register_url;
});

// function impulse_clone_get_asset_uri($pattern) {
//     $matches = glob(get_template_directory() . '/dist/assets/' . $pattern);
//     if (!empty($matches)) {
//         return get_template_directory_uri() . '/dist/assets/' . basename($matches[0]);
//     }
//     return '';
// }

// function impulse_clone_get_asset_ver($pattern) {
//     $matches = glob(get_template_directory() . '/dist/assets/' . $pattern);
//     if (!empty($matches) && file_exists($matches[0])) {
//         return (string) filemtime($matches[0]);
//     }
//     return wp_get_theme()->get('Version');
// }

function impulse_clone_enqueue_assets() {
    wp_enqueue_style(
        'impulse-clone-style',
        get_stylesheet_uri(),
        array(),
        filemtime(get_stylesheet_directory() . '/style.css')
    );

    // $react_css = impulse_clone_get_asset_uri('index-*.css');
    // if ($react_css) {
    //     wp_enqueue_style(
    //         'impulse-clone-react-style',
    //         $react_css,
    //         array('impulse-clone-style'),
    //         impulse_clone_get_asset_ver('index-*.css')
    //     );
    // }

    // $react_js = impulse_clone_get_asset_uri('index-*.js');
    // if ($react_js) {
    //     wp_enqueue_script(
    //         'impulse-clone-react-app',
    //         $react_js,
    //         array(),
    //         impulse_clone_get_asset_ver('index-*.js'),
    //         true
    //     );
    // }
}
add_action('wp_enqueue_scripts', 'impulse_clone_enqueue_assets');

function impulse_clone_module_script($tag, $handle, $src) {
    if ('impulse-clone-react-app' === $handle) {
        return '<script type="module" src="' . esc_url($src) . '"></script>';
    }
    return $tag;
}
add_filter('script_loader_tag', 'impulse_clone_module_script', 10, 3);

// function impulse_clone_body_class($classes) {
//     if (is_front_page() || is_home()) {
//         $classes[] = 'react-shell-page';
//         $classes[] = 'page-home';
//     }
//     return $classes;
// }
// add_filter('body_class', 'impulse_clone_body_class');

// function impulse_clone_title($title) {
//     if (is_front_page() || is_home()) {
//         return 'impulsecomputeracademy.com';
//     }
//     return $title;
// }
// add_filter('pre_get_document_title', 'impulse_clone_title');

/**
 * Contact form fallback for when the enquiry plugin is inactive.
 */
function impulse_clone_has_enquiry_plugin() {
    return class_exists('Impulse_Enquiry_Manager');
}

add_action('wp_enqueue_scripts', function() {
    if (!is_page_template('contact.php') || impulse_clone_has_enquiry_plugin()) {
        return;
    }

    wp_enqueue_script('jquery');
    wp_localize_script('jquery', 'contactFormData', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('contact_form_nonce')
    ));
});

if (!impulse_clone_has_enquiry_plugin()) {
    add_action('wp_ajax_submit_contact_form', 'handle_contact_form_submission');
    add_action('wp_ajax_nopriv_submit_contact_form', 'handle_contact_form_submission');
}

function handle_contact_form_submission() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'contact_form_nonce')) {
        error_log('Contact Form: Nonce verification failed');
        wp_send_json_error(array('message' => 'Security verification failed. Please refresh and try again.'));
    }

    // Sanitize and validate inputs
    $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
    $subject = isset($_POST['subject']) ? sanitize_text_field(wp_unslash($_POST['subject'])) : '';
    $course = isset($_POST['course']) ? sanitize_text_field(wp_unslash($_POST['course'])) : '';
    $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';

    error_log('Contact Form Received: Name=' . $name . ', Email=' . $email . ', Subject=' . $subject);

    // Validate required fields
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        error_log('Contact Form: Missing required fields. Name: ' . (!empty($name) ? 'OK' : 'MISSING') . ', Email: ' . (!empty($email) ? 'OK' : 'MISSING') . ', Subject: ' . (!empty($subject) ? 'OK' : 'MISSING') . ', Message: ' . (!empty($message) ? 'OK' : 'MISSING'));
        wp_send_json_error(array('message' => 'Please fill in all required fields (marked with *)'));
    }

    // Validate email
    if (!is_email($email)) {
        error_log('Contact Form: Invalid email - ' . $email);
        wp_send_json_error(array('message' => 'Please enter a valid email address'));
    }

    // Validate message length
    if (strlen($message) < 10) {
        error_log('Contact Form: Message too short - ' . strlen($message) . ' characters');
        wp_send_json_error(array('message' => 'Message must be at least 10 characters long'));
    }

    // Prepare email content
    $admin_email = get_option('admin_email');
    $site_name = get_bloginfo('name');
    
    $email_subject = "New Contact Form Inquiry - {$subject}";
    
    $email_body = "
    <h2>New Inquiry from Contact Form</h2>
    <p><strong>Name:</strong> {$name}</p>
    <p><strong>Email:</strong> {$email}</p>
    <p><strong>Phone:</strong> " . (!empty($phone) ? $phone : 'Not provided') . "</p>
    <p><strong>Subject:</strong> {$subject}</p>
    <p><strong>Course:</strong> " . (!empty($course) ? $course : 'Not provided') . "</p>
    <p><strong>Message:</strong></p>
    <p>{$message}</p>
    <hr>
    <p><small>Submitted on: " . current_time('mysql') . "</small></p>
    ";

    // Set email headers
    $headers = array('Content-Type: text/html; charset=UTF-8');
    
    // Send email to admin
    error_log('Contact Form: Attempting to send email to ' . $admin_email);
    $email_sent = wp_mail($admin_email, $email_subject, $email_body, $headers);

    if (!$email_sent) {
        error_log('Contact Form: Email failed to send to admin: ' . $admin_email);
        wp_send_json_error(array('message' => 'Email sending failed. Please try again or contact us directly at ' . $admin_email));
    }

    error_log('Contact Form: Email sent successfully to ' . $admin_email);

    // Optional: Send confirmation email to user
    $user_email_subject = "We Received Your Inquiry - {$site_name}";
    $user_email_body = "
    <h2>Thank you for contacting us!</h2>
    <p>Hello {$name},</p>
    <p>We have received your inquiry and will get back to you as soon as possible, typically within 24 hours.</p>
    <p><strong>Your Inquiry Details:</strong></p>
    <p><strong>Subject:</strong> {$subject}</p>
    <p><strong>Course:</strong> " . (!empty($course) ? $course : 'Not provided') . "</p>
    <p><strong>Message:</strong> {$message}</p>
    <hr>
    <p>If you have any urgent questions, feel free to call us at +91 7979815545</p>
    <p>Best regards,<br>{$site_name} Team</p>
    ";

    wp_mail($email, $user_email_subject, $user_email_body, $headers);

    // Success response
    error_log('Contact Form: Successfully processed submission from ' . $email);
    wp_send_json_success(array(
        'message' => 'Thank you! Your inquiry has been submitted successfully. We will get back to you within 24 hours.'
    ));
}
