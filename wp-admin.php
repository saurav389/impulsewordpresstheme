<?php
/**
 * Template Name: Custom WP Admin Login
 * Description: Custom login page for WordPress admin access.
 */

if (!defined('ABSPATH')) {
    $wp_load = dirname(__DIR__, 3) . '/wp-load.php';
    if (!file_exists($wp_load)) {
        exit;
    }

    require_once $wp_load;
}

nocache_headers();

if (force_ssl_admin() && !is_ssl()) {
    $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '/';
    wp_safe_redirect(set_url_scheme(home_url($request_uri), 'https'));
    exit;
}

if (!function_exists('impulse_clone_admin_login_base_url')) {
    function impulse_clone_admin_login_base_url() {
        $page_id = get_queried_object_id();

        if (!empty($page_id)) {
            return get_permalink($page_id);
        }

        $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '/';
        $request_uri = strtok($request_uri, '?');

        return home_url($request_uri ?: '/');
    }
}

if (!function_exists('impulse_clone_admin_login_url')) {
    function impulse_clone_admin_login_url($args = array()) {
        $args = array_filter($args, static function ($value) {
            return null !== $value && '' !== $value;
        });

        return empty($args) ? impulse_clone_admin_login_base_url() : add_query_arg($args, impulse_clone_admin_login_base_url());
    }
}

if (!function_exists('impulse_clone_admin_login_redirect')) {
    function impulse_clone_admin_login_redirect($redirect_to = '') {
        $redirect_to = $redirect_to ? wp_validate_redirect($redirect_to, admin_url()) : admin_url();
        wp_safe_redirect($redirect_to);
        exit;
    }
}

if (!function_exists('impulse_clone_admin_login_password_message')) {
    function impulse_clone_admin_login_password_message($message, $key, $user_login, $user_data = null) {
        $reset_url = impulse_clone_admin_login_url(
            array(
                'action' => 'rp',
                'key'    => $key,
                'login'  => rawurlencode($user_login),
            )
        );

        return sprintf(
            "Someone has requested a password reset for the following account:\r\n\r\nUsername: %s\r\n\r\nIf this was a mistake, just ignore this email and nothing will happen.\r\n\r\nTo reset your password, visit the following address:\r\n\r\n%s",
            $user_login,
            $reset_url
        );
    }
}

if (!function_exists('impulse_clone_admin_login_error_html')) {
    function impulse_clone_admin_login_error_html(WP_Error $errors) {
        if (!$errors->has_errors()) {
            return '';
        }

        $items = array();
        foreach ($errors->get_error_codes() as $code) {
            foreach ($errors->get_error_messages($code) as $message) {
                $items[] = '<li>' . wp_kses_post($message) . '</li>';
            }
        }

        if (empty($items)) {
            return '';
        }

        return '<div id="login_error"><ul><li>' . implode('</li><li>', $items) . '</li></ul></div>';
    }
}

if (!function_exists('impulse_clone_admin_login_message_html')) {
    function impulse_clone_admin_login_message_html($message, $class = 'message') {
        if (empty($message)) {
            return '';
        }

        return '<div class="' . esc_attr($class) . '"><p>' . wp_kses_post($message) . '</p></div>';
    }
}

do_action('login_init');

$allowed_actions = array('login', 'logout', 'lostpassword', 'retrievepassword', 'rp', 'resetpass', 'register');
$action = isset($_REQUEST['action']) ? sanitize_key(wp_unslash($_REQUEST['action'])) : 'login';
$action = in_array($action, $allowed_actions, true) ? $action : 'login';

$redirect_to = isset($_REQUEST['redirect_to']) ? wp_unslash($_REQUEST['redirect_to']) : admin_url();
$redirect_to = wp_validate_redirect($redirect_to, admin_url());
$errors = new WP_Error();
$messages = array();
$reset_user = null;
$request_method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper(wp_unslash($_SERVER['REQUEST_METHOD'])) : 'GET';

if (defined('TEST_COOKIE')) {
    $secure_cookie = is_ssl();
    setcookie(TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN, $secure_cookie, true);

    if (SITECOOKIEPATH !== COOKIEPATH) {
        setcookie(TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN, $secure_cookie, true);
    }
}

if (is_user_logged_in() && 'logout' !== $action) {
    impulse_clone_admin_login_redirect($redirect_to);
}

if ('logout' === $action) {
    $logout_nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

    if ($logout_nonce && wp_verify_nonce($logout_nonce, 'log-out')) {
        wp_logout();
        wp_safe_redirect(impulse_clone_admin_login_url(array('loggedout' => '1')));
        exit;
    }

    $errors->add('logout_failed', __('The logout link is invalid. Please try again.', 'impulse-academy-clone'));
    $action = 'login';
}

if ('changed' === (isset($_GET['password']) ? sanitize_key(wp_unslash($_GET['password'])) : '')) {
    $messages[] = __('Your password has been reset. You can log in now.', 'impulse-academy-clone');
}

if ('1' === (isset($_GET['loggedout']) ? sanitize_text_field(wp_unslash($_GET['loggedout'])) : '')) {
    $messages[] = __('You are now logged out.', 'impulse-academy-clone');
}

if ('register' === $action && !get_option('users_can_register')) {
    $errors->add('register_disabled', __('User registration is currently disabled.', 'impulse-academy-clone'));
    $action = 'login';
}

if (in_array($action, array('rp', 'resetpass'), true) && 'POST' !== $request_method) {
    $rp_login = isset($_GET['login']) ? sanitize_text_field(wp_unslash($_GET['login'])) : '';
    $rp_key = isset($_GET['key']) ? sanitize_text_field(wp_unslash($_GET['key'])) : '';
    $reset_user = check_password_reset_key($rp_key, $rp_login);

    if (is_wp_error($reset_user)) {
        $errors = $reset_user;
        $action = 'lostpassword';
    } else {
        $action = 'resetpass';
    }
}

if ('POST' === $request_method) {
    switch ($action) {
        case 'login':
            check_admin_referer('impulse_clone_admin_login');

            $user_login = isset($_POST['log']) ? sanitize_text_field(wp_unslash($_POST['log'])) : '';
            $user_password = isset($_POST['pwd']) ? wp_unslash($_POST['pwd']) : '';
            $remember = !empty($_POST['rememberme']);
            $redirect_to = isset($_POST['redirect_to']) ? wp_validate_redirect(wp_unslash($_POST['redirect_to']), admin_url()) : admin_url();

            if (empty($user_login)) {
                $errors->add('empty_username', __('Please enter your username or email address.', 'impulse-academy-clone'));
                break;
            }

            if (empty($user_password)) {
                $errors->add('empty_password', __('Please enter your password.', 'impulse-academy-clone'));
                break;
            }

            $user = wp_signon(
                array(
                    'user_login'    => $user_login,
                    'user_password' => $user_password,
                    'remember'      => $remember,
                ),
                is_ssl()
            );

            if (is_wp_error($user)) {
                $errors = $user;
                break;
            }

            $redirect_to = apply_filters('login_redirect', $redirect_to, $redirect_to, $user);
            impulse_clone_admin_login_redirect($redirect_to);
            break;

        case 'lostpassword':
        case 'retrievepassword':
            check_admin_referer('impulse_clone_admin_lostpassword');

            add_filter('retrieve_password_message', 'impulse_clone_admin_login_password_message', 10, 4);
            $result = retrieve_password();
            remove_filter('retrieve_password_message', 'impulse_clone_admin_login_password_message', 10);

            if (is_wp_error($result)) {
                $errors = $result;
            } else {
                $messages[] = __('Check your email for the confirmation link.', 'impulse-academy-clone');
            }

            $action = 'lostpassword';
            break;

        case 'resetpass':
        case 'rp':
            check_admin_referer('impulse_clone_admin_reset_password');

            $rp_login = isset($_POST['rp_login']) ? sanitize_text_field(wp_unslash($_POST['rp_login'])) : '';
            $rp_key = isset($_POST['rp_key']) ? sanitize_text_field(wp_unslash($_POST['rp_key'])) : '';
            $pass1 = isset($_POST['pass1']) ? (string) wp_unslash($_POST['pass1']) : '';
            $pass2 = isset($_POST['pass2']) ? (string) wp_unslash($_POST['pass2']) : '';

            $reset_user = check_password_reset_key($rp_key, $rp_login);

            if (is_wp_error($reset_user)) {
                $errors = $reset_user;
                $action = 'lostpassword';
                break;
            }

            if (empty($pass1) || empty($pass2)) {
                $errors->add('password_empty', __('Please enter your new password twice.', 'impulse-academy-clone'));
                $action = 'resetpass';
                break;
            }

            if ($pass1 !== $pass2) {
                $errors->add('password_mismatch', __('The passwords do not match.', 'impulse-academy-clone'));
                $action = 'resetpass';
                break;
            }

            reset_password($reset_user, $pass1);
            wp_safe_redirect(impulse_clone_admin_login_url(array('password' => 'changed')));
            exit;

        case 'register':
            check_admin_referer('impulse_clone_admin_register');

            if (!get_option('users_can_register')) {
                $errors->add('register_disabled', __('User registration is currently disabled.', 'impulse-academy-clone'));
                break;
            }

            $user_login = isset($_POST['user_login']) ? sanitize_user(wp_unslash($_POST['user_login'])) : '';
            $user_email = isset($_POST['user_email']) ? sanitize_email(wp_unslash($_POST['user_email'])) : '';
            $result = register_new_user($user_login, $user_email);

            if (is_wp_error($result)) {
                $errors = $result;
            } else {
                $messages[] = __('Registration complete. Please check your email.', 'impulse-academy-clone');
            }
            break;
    }
}

wp_enqueue_style('login');
do_action('login_enqueue_scripts');
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html(get_bloginfo('name') . ' - Admin Login'); ?></title>
    <style>
        body.login.impulse-admin-login-page {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(13, 110, 253, 0.12), transparent 35%),
                radial-gradient(circle at bottom right, rgba(25, 135, 84, 0.10), transparent 30%),
                #f0f4f8;
        }
        .impulse-admin-login-page #login {
            width: min(100%, 400px);
            padding: 6vh 20px 20px;
        }
        .impulse-admin-login-page .wp-login-logo a {
            width: auto;
            height: auto;
            background: none;
            text-indent: 0;
            overflow: visible;
            font-size: 32px;
            font-weight: 700;
            line-height: 1.2;
            text-decoration: none;
        }
        .impulse-admin-login-page .login-message-switch {
            text-align: center;
            margin-top: 18px;
            color: #50575e;
        }
        .impulse-admin-login-page .login-message-switch a {
            color: #2271b1;
            text-decoration: none;
        }
        .impulse-admin-login-page .login-message-switch a:hover {
            text-decoration: underline;
        }
        .impulse-admin-login-page .reset-pass-submit,
        .impulse-admin-login-page .submit {
            margin-bottom: 0;
        }
    </style>
    <?php
    wp_head();
    do_action('login_head');
    ?>
</head>
<body <?php body_class('login wp-core-ui impulse-admin-login-page login-action-' . $action); ?>>
<?php
wp_body_open();
do_action('login_header');
?>
<div id="login">
    <h1 role="presentation" class="wp-login-logo">
        <a href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html(get_bloginfo('name')); ?></a>
    </h1>

    <?php
    echo impulse_clone_admin_login_error_html($errors);

    foreach ($messages as $message) {
        echo impulse_clone_admin_login_message_html($message);
    }

    do_action("login_form_{$action}");

    if ('lostpassword' === $action || 'retrievepassword' === $action) :
        ?>
        <p class="message"><?php esc_html_e('Enter your username or email address and we will send you a password reset link.', 'impulse-academy-clone'); ?></p>
        <form name="lostpasswordform" id="lostpasswordform" action="<?php echo esc_url(impulse_clone_admin_login_url(array('action' => 'lostpassword'))); ?>" method="post">
            <p>
                <label for="user_login"><?php esc_html_e('Username or Email Address', 'impulse-academy-clone'); ?></label>
                <input type="text" name="user_login" id="user_login" class="input" value="<?php echo isset($_POST['user_login']) ? esc_attr(wp_unslash($_POST['user_login'])) : ''; ?>" size="20" autocapitalize="off" autocomplete="username">
            </p>
            <?php wp_nonce_field('impulse_clone_admin_lostpassword'); ?>
            <p class="submit">
                <input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e('Get New Password', 'impulse-academy-clone'); ?>">
            </p>
        </form>
        <p id="nav">
            <a href="<?php echo esc_url(impulse_clone_admin_login_url(array('redirect_to' => $redirect_to))); ?>"><?php esc_html_e('Back to login', 'impulse-academy-clone'); ?></a>
        </p>
        <?php
    elseif ('resetpass' === $action) :
        $rp_login = isset($_POST['rp_login'])
            ? sanitize_text_field(wp_unslash($_POST['rp_login']))
            : (isset($_REQUEST['login']) ? sanitize_text_field(wp_unslash($_REQUEST['login'])) : '');
        $rp_key = isset($_POST['rp_key'])
            ? sanitize_text_field(wp_unslash($_POST['rp_key']))
            : (isset($_REQUEST['key']) ? sanitize_text_field(wp_unslash($_REQUEST['key'])) : '');
        ?>
        <form name="resetpassform" id="resetpassform" action="<?php echo esc_url(impulse_clone_admin_login_url(array('action' => 'resetpass'))); ?>" method="post" autocomplete="off">
            <p>
                <label for="pass1"><?php esc_html_e('New Password', 'impulse-academy-clone'); ?></label>
                <input type="password" name="pass1" id="pass1" class="input" size="24" autocomplete="new-password">
            </p>
            <p>
                <label for="pass2"><?php esc_html_e('Confirm New Password', 'impulse-academy-clone'); ?></label>
                <input type="password" name="pass2" id="pass2" class="input" size="24" autocomplete="new-password">
            </p>
            <input type="hidden" name="rp_login" value="<?php echo esc_attr($rp_login); ?>">
            <input type="hidden" name="rp_key" value="<?php echo esc_attr($rp_key); ?>">
            <?php wp_nonce_field('impulse_clone_admin_reset_password'); ?>
            <p class="submit reset-pass-submit">
                <input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e('Reset Password', 'impulse-academy-clone'); ?>">
            </p>
        </form>
        <p id="nav">
            <a href="<?php echo esc_url(impulse_clone_admin_login_url()); ?>"><?php esc_html_e('Back to login', 'impulse-academy-clone'); ?></a>
        </p>
        <?php
    elseif ('register' === $action && get_option('users_can_register')) :
        ?>
        <p class="message register"><?php esc_html_e('Register a new account. WordPress will email your password setup instructions.', 'impulse-academy-clone'); ?></p>
        <form name="registerform" id="registerform" action="<?php echo esc_url(impulse_clone_admin_login_url(array('action' => 'register'))); ?>" method="post" novalidate="novalidate">
            <p>
                <label for="user_login"><?php esc_html_e('Username', 'impulse-academy-clone'); ?></label>
                <input type="text" name="user_login" id="user_login" class="input" value="<?php echo isset($_POST['user_login']) ? esc_attr(wp_unslash($_POST['user_login'])) : ''; ?>" size="20" autocapitalize="off" autocomplete="username">
            </p>
            <p>
                <label for="user_email"><?php esc_html_e('Email', 'impulse-academy-clone'); ?></label>
                <input type="email" name="user_email" id="user_email" class="input" value="<?php echo isset($_POST['user_email']) ? esc_attr(wp_unslash($_POST['user_email'])) : ''; ?>" size="25" autocomplete="email">
            </p>
            <?php wp_nonce_field('impulse_clone_admin_register'); ?>
            <p class="submit">
                <input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e('Register', 'impulse-academy-clone'); ?>">
            </p>
        </form>
        <p id="nav">
            <a href="<?php echo esc_url(impulse_clone_admin_login_url()); ?>"><?php esc_html_e('Back to login', 'impulse-academy-clone'); ?></a>
        </p>
        <?php
    else :
        $posted_login = isset($_POST['log']) ? wp_unslash($_POST['log']) : '';
        ?>
        <form name="loginform" id="loginform" action="<?php echo esc_url(impulse_clone_admin_login_url()); ?>" method="post">
            <p>
                <label for="user_login"><?php esc_html_e('Username or Email Address', 'impulse-academy-clone'); ?></label>
                <input type="text" name="log" id="user_login" class="input" value="<?php echo esc_attr($posted_login); ?>" size="20" autocapitalize="off" autocomplete="username">
            </p>
            <div class="user-pass-wrap">
                <label for="user_pass"><?php esc_html_e('Password', 'impulse-academy-clone'); ?></label>
                <input type="password" name="pwd" id="user_pass" class="input" value="" size="20" autocomplete="current-password">
            </div>
            <p class="forgetmenot">
                <input name="rememberme" type="checkbox" id="rememberme" value="forever" <?php checked(!empty($_POST['rememberme'])); ?>>
                <label for="rememberme"><?php esc_html_e('Remember Me', 'impulse-academy-clone'); ?></label>
            </p>
            <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect_to); ?>">
            <input type="hidden" name="testcookie" value="1">
            <?php wp_nonce_field('impulse_clone_admin_login'); ?>
            <p class="submit">
                <input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e('Log In', 'impulse-academy-clone'); ?>">
            </p>
            <?php do_action('login_form'); ?>
        </form>

        <p id="nav">
            <a href="<?php echo esc_url(impulse_clone_admin_login_url(array('action' => 'lostpassword', 'redirect_to' => $redirect_to))); ?>"><?php esc_html_e('Lost your password?', 'impulse-academy-clone'); ?></a>
            <?php if (get_option('users_can_register')) : ?>
                | <a href="<?php echo esc_url(impulse_clone_admin_login_url(array('action' => 'register'))); ?>"><?php esc_html_e('Register', 'impulse-academy-clone'); ?></a>
            <?php endif; ?>
        </p>

        <?php if (is_user_logged_in()) : ?>
            <p class="login-message-switch">
                <a href="<?php echo esc_url(wp_logout_url(impulse_clone_admin_login_url(array('loggedout' => '1')))); ?>"><?php esc_html_e('Log out', 'impulse-academy-clone'); ?></a>
            </p>
        <?php endif; ?>
        <?php
    endif;
    ?>

    <p id="backtoblog">
        <a href="<?php echo esc_url(home_url('/')); ?>">&larr; <?php echo esc_html(get_bloginfo('name')); ?></a>
    </p>
</div>
<?php
do_action('login_footer');
wp_footer();
?>
</body>
</html>
<?php
exit;
