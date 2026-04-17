<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme Update Manager - Admin UI for update settings and manual checks
 */
class ICA_Theme_Update_Manager {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('admin_menu', array($this, 'register_menu'), 21);
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function register_menu() {
        add_submenu_page(
            'ica-lms',
            __('Theme Updates', 'impulse-academy-clone'),
            __('Theme Updates', 'impulse-academy-clone'),
            'manage_options',
            'ica-theme-updates',
            array($this, 'render_page')
        );
    }

    public function register_settings() {
        register_setting('ica_theme_updates', 'ica_theme_github_token');
        register_setting('ica_theme_updates', 'ica_theme_auto_update');
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'impulse-academy-clone'));
        }

        $updater = ICA_Theme_Updater::get_instance();
        $current_version = $updater->get_current_version();
        $github_token = get_option('ica_theme_github_token', '');
        $auto_update = get_option('ica_theme_auto_update', 0);

        ?>
        <div class="wrap">
            <h1 style="display: flex; align-items: center; gap: 10px;">
                <span style="color: #667eea; font-size: 28px;">⚡</span>
                <?php esc_html_e('Theme Updates', 'impulse-academy-clone'); ?>
            </h1>

            <div style="background: #fff; padding: 20px; border-radius: 8px; margin: 20px 0; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2><?php esc_html_e('Current Version', 'impulse-academy-clone'); ?></h2>
                <p style="font-size: 18px; margin: 10px 0;">
                    <strong>Impulse Academy Theme</strong>
                    <span style="color: #667eea; font-size: 20px; font-weight: bold;">v<?php echo esc_html($current_version); ?></span>
                </p>
            </div>

            <div style="background: #f5f7fa; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #667eea;">
                <h3><?php esc_html_e('Check for Updates', 'impulse-academy-clone'); ?></h3>
                <p style="color: #555; margin-bottom: 15px;">
                    <?php esc_html_e('Click the button below to manually check GitHub for the latest version. The system automatically checks every 12 hours.', 'impulse-academy-clone'); ?>
                </p>
                <a href="<?php echo esc_url(ICA_Theme_Updater::get_check_url()); ?>" class="button button-primary" style="background: #667eea; border-color: #667eea;">
                    <span style="margin-right: 5px;">🔄</span><?php esc_html_e('Check Now', 'impulse-academy-clone'); ?>
                </a>
                <?php
                if (isset($_GET['ica_update_checked']) && $_GET['ica_update_checked'] == '1') {
                    echo '<span style="color: #28a745; margin-left: 15px;">✓ Check completed!</span>';
                }
                ?>
            </div>

            <form method="post" action="options.php" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <?php settings_fields('ica_theme_updates'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="ica_theme_github_token">
                                <?php esc_html_e('GitHub Personal Access Token (Optional)', 'impulse-academy-clone'); ?>
                            </label>
                        </th>
                        <td>
                            <input 
                                type="password" 
                                id="ica_theme_github_token" 
                                name="ica_theme_github_token" 
                                value="<?php echo esc_attr($github_token); ?>"
                                style="width: 100%; max-width: 400px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                                placeholder="ghp_xxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                            />
                            <p class="description">
                                <?php esc_html_e('Enter a GitHub Personal Access Token to increase update check rate limits (optional).', 'impulse-academy-clone'); ?>
                                <a href="https://github.com/settings/tokens/new" target="_blank" style="color: #667eea;">
                                    <?php esc_html_e('Create Token →', 'impulse-academy-clone'); ?>
                                </a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="ica_theme_auto_update">
                                <?php esc_html_e('Enable Auto-Update (Coming Soon)', 'impulse-academy-clone'); ?>
                            </label>
                        </th>
                        <td>
                            <input 
                                type="checkbox" 
                                id="ica_theme_auto_update" 
                                name="ica_theme_auto_update" 
                                value="1"
                                <?php checked($auto_update, 1); ?>
                                disabled
                            />
                            <p class="description">
                                <?php esc_html_e('When enabled, the theme will automatically update when a new version is released (coming in next version).', 'impulse-academy-clone'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>

            <div style="background: #f0f7ff; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #667eea;">
                <h3>ℹ️ <?php esc_html_e('How It Works', 'impulse-academy-clone'); ?></h3>
                <ul style="line-height: 1.8; color: #555;">
                    <li>✓ <?php esc_html_e('Checks your GitHub repository for new releases every 12 hours', 'impulse-academy-clone'); ?></li>
                    <li>✓ <?php esc_html_e('Displays update notifications in the WordPress admin dashboard', 'impulse-academy-clone'); ?></li>
                    <li>✓ <?php esc_html_e('Users with admin access can update directly from Appearance → Themes', 'impulse-academy-clone'); ?></li>
                    <li>✓ <?php esc_html_e('Works exactly like premium WordPress themes', 'impulse-academy-clone'); ?></li>
                </ul>
            </div>

            <div style="background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;">
                <h3>⚙️ <?php esc_html_e('Setup Instructions', 'impulse-academy-clone'); ?></h3>
                <ol style="line-height: 1.8; color: #555;">
                    <li><?php esc_html_e('Push your theme changes to GitHub in a new release/tag (e.g., v1.0.1)', 'impulse-academy-clone'); ?></li>
                    <li><?php esc_html_e('The system will automatically detect the new release within 12 hours', 'impulse-academy-clone'); ?></li>
                    <li><?php esc_html_e('Or click "Check Now" above to check immediately', 'impulse-academy-clone'); ?></li>
                    <li><?php esc_html_e('Updates appear in Appearance → Themes for all admin users', 'impulse-academy-clone'); ?></li>
                </ol>
                <p style="color: #856404; margin-top: 10px; font-size: 13px;">
                    <strong><?php esc_html_e('Note:', 'impulse-academy-clone'); ?></strong>
                    <?php esc_html_e('Make sure your GitHub repository is set up correctly and releases are published via GitHub API.', 'impulse-academy-clone'); ?>
                </p>
            </div>
        </div>
        <?php
    }
}

// Initialize only if LMS is active
if (class_exists('ICA_LMS')) {
    ICA_Theme_Update_Manager::get_instance();
}
