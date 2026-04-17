<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme Update Checker - Connects to GitHub for automatic updates
 * 
 * This class handles checking for theme updates on GitHub and integrating
 * them with WordPress's native update system. Updates are displayed in the
 * WordPress admin dashboard just like premium themes.
 */
class ICA_Theme_Updater {
    private static $instance = null;
    
    // GitHub configuration
    private $github_user = 'impulsecomputeracademy';  // Replace with your GitHub username
    private $github_repo = 'impulse-academy-clone';   // Replace with your repo name
    private $github_branch = 'main';                   // Branch to check
    private $text_domain = 'impulse-academy-clone';
    
    // Theme info
    private $theme_slug = 'impulse-academy-clone';
    private $theme_file = '';
    private $current_version = '';
    
    // Transient
    private $transient_key = 'ica_theme_update_check';
    private $check_interval = 12 * HOUR_IN_SECONDS; // Check every 12 hours

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->theme_file = get_stylesheet_directory() . '/style.css';
        $this->current_version = wp_get_theme()->get('Version');
        
        // Hook into WordPress update system
        add_filter('transient_update_themes', array($this, 'check_for_updates'));
        add_filter('site_transient_update_themes', array($this, 'check_for_updates'));
        
        // Admin notices
        add_action('admin_notices', array($this, 'display_update_notice'));
        add_action('load-themes.php', array($this, 'maybe_trigger_update'));
        
        // Allow checking updates manually
        add_action('wp_ajax_ica_check_theme_updates', array($this, 'ajax_check_updates'));
    }

    /**
     * Manually trigger update check
     */
    public function manual_check() {
        delete_transient($this->transient_key);
        do_action('set_site_transient_update_themes', null);
    }

    /**
     * Check for updates from GitHub
     */
    public function check_for_updates($transients) {
        // Check transient - only check if expired
        $cached_update = get_transient($this->transient_key);
        if ($cached_update !== false) {
            // Use cached result if not expired
            if ($cached_update && isset($cached_update['version'])) {
                $transients->response[$this->theme_slug] = $cached_update;
            }
            return $transients;
        }

        // Fetch update from GitHub
        $update_data = $this->get_github_release();

        if ($update_data && version_compare($this->current_version, $update_data['version'], '<')) {
            // Cache the update info
            set_transient($this->transient_key, $update_data, $this->check_interval);
            
            // Add to transients response
            $transients->response[$this->theme_slug] = $update_data;
            
            error_log('ICA Theme: Update available - v' . $update_data['version']);
        } else {
            // Cache "no update" for 12 hours
            set_transient($this->transient_key, array('no_update' => true), $this->check_interval);
        }

        return $transients;
    }

    /**
     * Get the latest release from GitHub API
     */
    private function get_github_release() {
        $url = "https://api.github.com/repos/{$this->github_user}/{$this->github_repo}/releases/latest";

        $args = array(
            'timeout' => 10,
            'headers' => array(
                'User-Agent' => 'ImpulseAcademy-Theme-Update-Checker/1.0'
            ),
        );

        // Add GitHub token if available (increases rate limit)
        if (!empty(get_option('ica_theme_github_token'))) {
            $args['headers']['Authorization'] = 'token ' . get_option('ica_theme_github_token');
        }

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            error_log('ICA Theme Update: GitHub API Error - ' . $response->get_error_message());
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data) || !isset($data['tag_name'])) {
            error_log('ICA Theme Update: Invalid GitHub response or no release found');
            return false;
        }

        // Parse version from tag (e.g., "v1.0.1" -> "1.0.1")
        $version = ltrim($data['tag_name'], 'v');

        return array(
            'theme' => $this->theme_slug,
            'new_version' => $version,
            'version' => $version,
            'url' => $data['html_url'],
            'package' => $data['zipball_url'],
            'requires' => '6.0',
            'requires_php' => '8.0',
            'tested' => '6.9',
            'requires_wp' => '6.0',
            'requires_php' => '8.0',
            'download_link' => $data['zipball_url'],
            'sections' => array(
                'description' => isset($data['body']) ? wp_kses_post($data['body']) : 'See release details on GitHub.',
            ),
        );
    }

    /**
     * Display update notice in admin
     */
    public function display_update_notice() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $update_info = get_transient($this->transient_key);

        if (!$update_info || isset($update_info['no_update'])) {
            return;
        }

        if (!isset($update_info['version'])) {
            return;
        }

        ?>
        <div class="notice notice-info" style="border-left: 4px solid #0073aa;">
            <p>
                <strong><?php esc_html_e('Impulse Academy Theme Update Available', $this->text_domain); ?></strong><br>
                <?php printf(
                    esc_html__('A new version (%s) of the Impulse Academy theme is available.', $this->text_domain),
                    '<span style="color: #0073aa; font-weight: bold;">' . esc_html($update_info['version']) . '</span>'
                ); ?>
                <a href="<?php echo esc_url(admin_url('themes.php')); ?>" class="button button-primary" style="margin-left: 10px;">
                    <?php esc_html_e('View Update', $this->text_domain); ?>
                </a>
                <a href="<?php echo esc_url(add_query_arg('ica_dismiss_update', '1')); ?>" class="button button-secondary" style="margin-left: 5px;">
                    <?php esc_html_e('Dismiss', $this->text_domain); ?>
                </a>
            </p>
        </div>
        <?php
    }

    /**
     * Maybe trigger manual update check
     */
    public function maybe_trigger_update() {
        if (isset($_GET['ica_check_updates']) && current_user_can('manage_options')) {
            check_admin_referer('ica_check_theme_updates');
            $this->manual_check();
            wp_safe_redirect(admin_url('themes.php?ica_update_checked=1'));
            exit;
        }
    }

    /**
     * AJAX: Check for updates manually
     */
    public function ajax_check_updates() {
        check_ajax_referer('ica_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $this->manual_check();

        $update_info = $this->get_github_release();

        if ($update_info) {
            wp_send_json_success(array(
                'version' => $update_info['version'],
                'message' => sprintf(
                    __('Update available: v%s', $this->text_domain),
                    $update_info['version']
                ),
            ));
        } else {
            wp_send_json_success(array(
                'message' => __('Your theme is up to date!', $this->text_domain),
            ));
        }
    }

    /**
     * Get update check URL for admin
     */
    public static function get_check_url() {
        return add_query_arg(
            array(
                'ica_check_updates' => '1',
                '_wpnonce' => wp_create_nonce('ica_check_theme_updates'),
            ),
            admin_url('themes.php')
        );
    }

    /**
     * Get current version
     */
    public function get_current_version() {
        return $this->current_version;
    }

    /**
     * Set GitHub token for authenticated requests (higher rate limit)
     * Run this once: update_option('ica_theme_github_token', 'your_gh_token_here');
     */
    public static function set_github_token($token) {
        update_option('ica_theme_github_token', sanitize_text_field($token));
    }
}

// Initialize updater
ICA_Theme_Updater::get_instance();
