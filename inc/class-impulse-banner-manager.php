<?php
if (!defined('ABSPATH')) {
    exit;
}

class Impulse_Clone_Banner_Manager {
    const POST_TYPE = 'ica_banner';
    const OPTION_KEY = 'impulse_banner_settings';
    const META_NONCE = 'impulse_banner_meta_nonce';

    public static function init() {
        add_action('init', array(__CLASS__, 'register_post_type'));
        add_action('add_meta_boxes', array(__CLASS__, 'register_meta_boxes'));
        add_action('save_post_' . self::POST_TYPE, array(__CLASS__, 'save_banner_meta'), 10, 2);
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_assets'));
        add_action('admin_menu', array(__CLASS__, 'register_settings_page'));
        add_action('admin_init', array(__CLASS__, 'register_settings'));
        add_action('admin_init', array(__CLASS__, 'handle_row_actions'));
        add_action('admin_notices', array(__CLASS__, 'render_admin_notices'));
        add_action('pre_get_posts', array(__CLASS__, 'set_default_admin_order'));

        add_filter('manage_' . self::POST_TYPE . '_posts_columns', array(__CLASS__, 'filter_admin_columns'));
        add_action('manage_' . self::POST_TYPE . '_posts_custom_column', array(__CLASS__, 'render_admin_column'), 10, 2);
        add_filter('post_row_actions', array(__CLASS__, 'add_row_actions'), 10, 2);
        add_filter('enter_title_here', array(__CLASS__, 'filter_title_placeholder'), 10, 2);
    }

    public static function register_post_type() {
        $labels = array(
            'name'               => __('Homepage Banners', 'impulse-academy-clone'),
            'singular_name'      => __('Homepage Banner', 'impulse-academy-clone'),
            'menu_name'          => __('Homepage Banners', 'impulse-academy-clone'),
            'name_admin_bar'     => __('Homepage Banner', 'impulse-academy-clone'),
            'add_new'            => __('Add New Banner', 'impulse-academy-clone'),
            'add_new_item'       => __('Add New Homepage Banner', 'impulse-academy-clone'),
            'edit_item'          => __('Edit Homepage Banner', 'impulse-academy-clone'),
            'new_item'           => __('New Homepage Banner', 'impulse-academy-clone'),
            'view_item'          => __('View Homepage Banner', 'impulse-academy-clone'),
            'search_items'       => __('Search Homepage Banners', 'impulse-academy-clone'),
            'not_found'          => __('No homepage banners found.', 'impulse-academy-clone'),
            'not_found_in_trash' => __('No homepage banners found in Trash.', 'impulse-academy-clone'),
        );

        register_post_type(
            self::POST_TYPE,
            array(
                'labels'             => $labels,
                'public'             => false,
                'show_ui'            => true,
                'show_in_menu'       => true,
                'show_in_rest'       => false,
                'supports'           => array('title', 'thumbnail'),
                'menu_icon'          => 'dashicons-images-alt2',
                'menu_position'      => 26,
                'has_archive'        => false,
                'exclude_from_search'=> true,
                'publicly_queryable' => false,
                'capability_type'    => 'post',
            )
        );
    }

    public static function register_meta_boxes() {
        add_meta_box(
            'impulse-banner-details',
            __('Banner Details', 'impulse-academy-clone'),
            array(__CLASS__, 'render_details_meta_box'),
            self::POST_TYPE,
            'normal',
            'high'
        );

        add_meta_box(
            'impulse-banner-preview',
            __('Banner Preview', 'impulse-academy-clone'),
            array(__CLASS__, 'render_preview_meta_box'),
            self::POST_TYPE,
            'side',
            'high'
        );
    }

    public static function enqueue_admin_assets($hook) {
        if (!in_array($hook, array('post.php', 'post-new.php'), true)) {
            return;
        }

        $screen = get_current_screen();
        if (!$screen || self::POST_TYPE !== $screen->post_type) {
            return;
        }

        wp_enqueue_media();

        $script_path = get_stylesheet_directory() . '/assets/js/banner-admin.js';
        $script_url = get_stylesheet_directory_uri() . '/assets/js/banner-admin.js';
        $script_ver = file_exists($script_path) ? (string) filemtime($script_path) : '1.0.0';

        wp_enqueue_script(
            'impulse-banner-admin',
            $script_url,
            array('jquery'),
            $script_ver,
            true
        );
    }

    public static function register_settings_page() {
        add_submenu_page(
            'edit.php?post_type=' . self::POST_TYPE,
            __('Banner Settings', 'impulse-academy-clone'),
            __('Banner Settings', 'impulse-academy-clone'),
            'manage_options',
            'impulse-banner-settings',
            array(__CLASS__, 'render_settings_page')
        );
    }

    public static function register_settings() {
        register_setting(
            'impulse_banner_settings_group',
            self::OPTION_KEY,
            array(__CLASS__, 'sanitize_settings')
        );
    }

    public static function sanitize_settings($input) {
        $defaults = self::get_default_settings();
        $input = is_array($input) ? $input : array();

        $settings = array(
            'enabled'           => empty($input['enabled']) ? 0 : 1,
            'autoplay'          => empty($input['autoplay']) ? 0 : 1,
            'autoplay_delay'    => isset($input['autoplay_delay']) ? max(1000, absint($input['autoplay_delay'])) : $defaults['autoplay_delay'],
            'loop'              => empty($input['loop']) ? 0 : 1,
            'show_navigation'   => empty($input['show_navigation']) ? 0 : 1,
            'show_pagination'   => empty($input['show_pagination']) ? 0 : 1,
            'pause_on_hover'    => empty($input['pause_on_hover']) ? 0 : 1,
            'fallback_mode'     => self::sanitize_choice(
                isset($input['fallback_mode']) ? $input['fallback_mode'] : $defaults['fallback_mode'],
                array('static_slides', 'hide_carousel', 'latest_banner')
            ),
            'default_cta_text'  => isset($input['default_cta_text']) ? sanitize_text_field($input['default_cta_text']) : $defaults['default_cta_text'],
        );

        return $settings;
    }

    public static function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $settings = self::get_settings();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Banner Carousel Settings', 'impulse-academy-clone'); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('impulse_banner_settings_group'); ?>
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><?php esc_html_e('Enable Dynamic Banners', 'impulse-academy-clone'); ?></th>
                            <td><label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[enabled]" value="1" <?php checked($settings['enabled'], 1); ?>> <?php esc_html_e('Show admin-managed banners in the homepage hero carousel.', 'impulse-academy-clone'); ?></label></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Autoplay', 'impulse-academy-clone'); ?></th>
                            <td><label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[autoplay]" value="1" <?php checked($settings['autoplay'], 1); ?>> <?php esc_html_e('Automatically rotate slides.', 'impulse-academy-clone'); ?></label></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="impulse-banner-delay"><?php esc_html_e('Autoplay Delay (ms)', 'impulse-academy-clone'); ?></label></th>
                            <td><input type="number" min="1000" step="500" id="impulse-banner-delay" name="<?php echo esc_attr(self::OPTION_KEY); ?>[autoplay_delay]" value="<?php echo esc_attr($settings['autoplay_delay']); ?>" class="small-text"> <p class="description"><?php esc_html_e('Recommended: 5000 to 7000 milliseconds.', 'impulse-academy-clone'); ?></p></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Loop Slides', 'impulse-academy-clone'); ?></th>
                            <td><label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[loop]" value="1" <?php checked($settings['loop'], 1); ?>> <?php esc_html_e('Repeat slides continuously.', 'impulse-academy-clone'); ?></label></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Show Navigation Arrows', 'impulse-academy-clone'); ?></th>
                            <td><label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[show_navigation]" value="1" <?php checked($settings['show_navigation'], 1); ?>> <?php esc_html_e('Display previous/next controls.', 'impulse-academy-clone'); ?></label></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Show Pagination Dots', 'impulse-academy-clone'); ?></th>
                            <td><label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[show_pagination]" value="1" <?php checked($settings['show_pagination'], 1); ?>> <?php esc_html_e('Display clickable pagination dots.', 'impulse-academy-clone'); ?></label></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Pause On Hover', 'impulse-academy-clone'); ?></th>
                            <td><label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[pause_on_hover]" value="1" <?php checked($settings['pause_on_hover'], 1); ?>> <?php esc_html_e('Pause autoplay while the user hovers over the banner area.', 'impulse-academy-clone'); ?></label></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="impulse-banner-fallback"><?php esc_html_e('Fallback Mode', 'impulse-academy-clone'); ?></label></th>
                            <td>
                                <select id="impulse-banner-fallback" name="<?php echo esc_attr(self::OPTION_KEY); ?>[fallback_mode]">
                                    <option value="static_slides" <?php selected($settings['fallback_mode'], 'static_slides'); ?>><?php esc_html_e('Use current static slides', 'impulse-academy-clone'); ?></option>
                                    <option value="hide_carousel" <?php selected($settings['fallback_mode'], 'hide_carousel'); ?>><?php esc_html_e('Hide carousel area', 'impulse-academy-clone'); ?></option>
                                    <option value="latest_banner" <?php selected($settings['fallback_mode'], 'latest_banner'); ?>><?php esc_html_e('Show the latest complete banner only', 'impulse-academy-clone'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="impulse-default-cta"><?php esc_html_e('Default CTA Text', 'impulse-academy-clone'); ?></label></th>
                            <td><input type="text" id="impulse-default-cta" name="<?php echo esc_attr(self::OPTION_KEY); ?>[default_cta_text]" value="<?php echo esc_attr($settings['default_cta_text']); ?>" class="regular-text"> <p class="description"><?php esc_html_e('Used when a banner-specific CTA text is empty.', 'impulse-academy-clone'); ?></p></td>
                        </tr>
                    </tbody>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public static function render_details_meta_box($post) {
        wp_nonce_field('impulse_save_banner_meta', self::META_NONCE);

        $meta = self::get_banner_meta($post->ID);
        $pages = get_pages(array('sort_column' => 'post_title', 'sort_order' => 'asc'));
        ?>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><label for="impulse-banner-caption"><?php esc_html_e('Short Caption', 'impulse-academy-clone'); ?></label></th>
                    <td><input type="text" id="impulse-banner-caption" name="impulse_banner_caption" value="<?php echo esc_attr($meta['caption']); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="impulse-banner-cta"><?php esc_html_e('CTA Text', 'impulse-academy-clone'); ?></label></th>
                    <td><input type="text" id="impulse-banner-cta" name="impulse_banner_cta_text" value="<?php echo esc_attr($meta['cta_text']); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="impulse-banner-page"><?php esc_html_e('Internal Page Link', 'impulse-academy-clone'); ?></label></th>
                    <td>
                        <select id="impulse-banner-page" name="impulse_banner_target_page_id" class="regular-text">
                            <option value="0"><?php esc_html_e('Select a page', 'impulse-academy-clone'); ?></option>
                            <?php foreach ($pages as $page) : ?>
                                <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($meta['target_page_id'], $page->ID); ?>><?php echo esc_html($page->post_title); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e('If selected, this page link will be used before the external URL.', 'impulse-academy-clone'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="impulse-banner-url"><?php esc_html_e('External URL', 'impulse-academy-clone'); ?></label></th>
                    <td><input type="url" id="impulse-banner-url" name="impulse_banner_external_url" value="<?php echo esc_attr($meta['external_url']); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Open In New Tab', 'impulse-academy-clone'); ?></th>
                    <td><label><input type="checkbox" name="impulse_banner_open_new_tab" value="1" <?php checked($meta['open_new_tab'], 1); ?>> <?php esc_html_e('Open banner link in a new browser tab.', 'impulse-academy-clone'); ?></label></td>
                </tr>
                <tr>
                    <th scope="row"><label for="impulse-banner-mobile-image"><?php esc_html_e('Mobile Banner Image', 'impulse-academy-clone'); ?></label></th>
                    <td>
                        <input type="hidden" id="impulse-banner-mobile-image-id" name="impulse_banner_mobile_image_id" value="<?php echo esc_attr($meta['mobile_image_id']); ?>">
                        <div class="impulse-banner-mobile-preview" style="margin-bottom:12px;">
                            <?php if ($meta['mobile_image_id']) : ?>
                                <?php echo wp_get_attachment_image($meta['mobile_image_id'], 'medium', false, array('style' => 'max-width:100%;height:auto;border:1px solid #dcdcde;border-radius:6px;')); ?>
                            <?php else : ?>
                                <span class="description"><?php esc_html_e('No mobile image selected yet.', 'impulse-academy-clone'); ?></span>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="button impulse-banner-select-mobile-image"><?php esc_html_e('Choose Mobile Image', 'impulse-academy-clone'); ?></button>
                        <button type="button" class="button impulse-banner-remove-mobile-image" <?php disabled($meta['mobile_image_id'], 0); ?>><?php esc_html_e('Remove', 'impulse-academy-clone'); ?></button>
                        <p class="description"><?php esc_html_e('Recommended for better mobile cropping. The featured image is used as the desktop poster.', 'impulse-academy-clone'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="impulse-banner-order"><?php esc_html_e('Display Order', 'impulse-academy-clone'); ?></label></th>
                    <td><input type="number" min="0" id="impulse-banner-order" name="impulse_banner_display_order" value="<?php echo esc_attr($meta['display_order']); ?>" class="small-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="impulse-banner-start-date"><?php esc_html_e('Start Date', 'impulse-academy-clone'); ?></label></th>
                    <td>
                        <input type="datetime-local" id="impulse-banner-start-date" name="impulse_banner_start_date" value="<?php echo esc_attr(self::format_datetime_for_input($meta['start_date'])); ?>">
                        <p class="description"><?php esc_html_e('Leave empty to show immediately. If set, the banner will only appear after this date/time.', 'impulse-academy-clone'); ?></p>
                        <p class="description" style="color: #d63638;"><strong><?php esc_html_e('⚠️ Note: Future dates will prevent the banner from showing until that time arrives.', 'impulse-academy-clone'); ?></strong></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="impulse-banner-end-date"><?php esc_html_e('End Date', 'impulse-academy-clone'); ?></label></th>
                    <td>
                        <input type="datetime-local" id="impulse-banner-end-date" name="impulse_banner_end_date" value="<?php echo esc_attr(self::format_datetime_for_input($meta['end_date'])); ?>">
                        <p class="description"><?php esc_html_e('Leave empty to show indefinitely. Banner will hide after this date/time.', 'impulse-academy-clone'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Banner Active', 'impulse-academy-clone'); ?></th>
                    <td><label><input type="checkbox" name="impulse_banner_is_active" value="1" <?php checked($meta['is_active'], 1); ?>> <?php esc_html_e('Enable this banner for homepage display.', 'impulse-academy-clone'); ?></label></td>
                </tr>
                <tr>
                    <th scope="row"><label for="impulse-banner-badge"><?php esc_html_e('Badge Label', 'impulse-academy-clone'); ?></label></th>
                    <td><input type="text" id="impulse-banner-badge" name="impulse_banner_badge_label" value="<?php echo esc_attr($meta['badge_label']); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="impulse-banner-overlay"><?php esc_html_e('Overlay Color', 'impulse-academy-clone'); ?></label></th>
                    <td><input type="text" id="impulse-banner-overlay" name="impulse_banner_overlay_color" value="<?php echo esc_attr($meta['overlay_color']); ?>" class="regular-text"> <p class="description"><?php esc_html_e('Use a HEX or rgba() color value. Example: #2563eb or rgba(37, 99, 235, 0.45)', 'impulse-academy-clone'); ?></p></td>
                </tr>
                <tr>
                    <th scope="row"><label for="impulse-banner-button-style"><?php esc_html_e('Button Style', 'impulse-academy-clone'); ?></label></th>
                    <td>
                        <select id="impulse-banner-button-style" name="impulse_banner_button_style">
                            <option value="primary" <?php selected($meta['button_style'], 'primary'); ?>><?php esc_html_e('Primary', 'impulse-academy-clone'); ?></option>
                            <option value="secondary" <?php selected($meta['button_style'], 'secondary'); ?>><?php esc_html_e('Secondary', 'impulse-academy-clone'); ?></option>
                            <option value="outline" <?php selected($meta['button_style'], 'outline'); ?>><?php esc_html_e('Outline', 'impulse-academy-clone'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="impulse-banner-text-align"><?php esc_html_e('Text Alignment', 'impulse-academy-clone'); ?></label></th>
                    <td>
                        <select id="impulse-banner-text-align" name="impulse_banner_text_alignment">
                            <option value="left" <?php selected($meta['text_alignment'], 'left'); ?>><?php esc_html_e('Left', 'impulse-academy-clone'); ?></option>
                            <option value="center" <?php selected($meta['text_alignment'], 'center'); ?>><?php esc_html_e('Center', 'impulse-academy-clone'); ?></option>
                            <option value="right" <?php selected($meta['text_alignment'], 'right'); ?>><?php esc_html_e('Right', 'impulse-academy-clone'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="impulse-banner-audience"><?php esc_html_e('Audience Tag', 'impulse-academy-clone'); ?></label></th>
                    <td><input type="text" id="impulse-banner-audience" name="impulse_banner_audience_tag" value="<?php echo esc_attr($meta['audience_tag']); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="impulse-banner-campaign"><?php esc_html_e('Campaign Code', 'impulse-academy-clone'); ?></label></th>
                    <td><input type="text" id="impulse-banner-campaign" name="impulse_banner_campaign_code" value="<?php echo esc_attr($meta['campaign_code']); ?>" class="regular-text"></td>
                </tr>
            </tbody>
        </table>
        <p class="description"><?php esc_html_e('Use the featured image box in the sidebar to upload the main banner poster.', 'impulse-academy-clone'); ?></p>
        <?php
    }

    public static function render_preview_meta_box($post) {
        $meta = self::get_banner_meta($post->ID);
        $image_id = get_post_thumbnail_id($post->ID);
        $link = self::get_banner_link($post->ID, $meta);
        $runtime_status = self::get_runtime_status_label($post->ID, $meta);
        $status_class = '';
        $status_message = '';

        if ('Scheduled' === $runtime_status) {
            $status_class = 'impulse-banner-scheduled';
            $status_message = __('This banner is scheduled and will not show until the start date is reached.', 'impulse-academy-clone');
        } elseif ('Expired' === $runtime_status) {
            $status_class = 'impulse-banner-expired';
            $status_message = __('This banner has expired and will not show on the homepage.', 'impulse-academy-clone');
        } elseif ('Inactive' === $runtime_status) {
            $status_class = 'impulse-banner-inactive';
            $status_message = __('This banner is inactive. Check the "Banner Active" checkbox to enable it.', 'impulse-academy-clone');
        } elseif ('Incomplete Poster' === $runtime_status) {
            $status_class = 'impulse-banner-incomplete';
            $status_message = __('This banner is incomplete. Upload a featured image to enable it.', 'impulse-academy-clone');
        } else {
            $status_class = 'impulse-banner-live';
        }
        ?>
        <div class="impulse-banner-preview-box <?php echo esc_attr($status_class); ?>">
            <?php if ($image_id) : ?>
                <div style="margin-bottom:12px;"><?php echo wp_get_attachment_image($image_id, 'medium', false, array('style' => 'width:100%;height:auto;border-radius:8px;border:1px solid #dcdcde;')); ?></div>
            <?php else : ?>
                <p class="description"><?php esc_html_e('Set a featured image to use as the main banner poster.', 'impulse-academy-clone'); ?></p>
            <?php endif; ?>

            <p><strong><?php echo esc_html(get_the_title($post)); ?></strong></p>
            <?php if (!empty($meta['caption'])) : ?>
                <p><?php echo esc_html($meta['caption']); ?></p>
            <?php endif; ?>
            <?php if (!empty($meta['cta_text'])) : ?>
                <p><strong><?php esc_html_e('CTA:', 'impulse-academy-clone'); ?></strong> <?php echo esc_html($meta['cta_text']); ?></p>
            <?php endif; ?>
            <p><strong><?php esc_html_e('Runtime Status:', 'impulse-academy-clone'); ?></strong> <span class="status-badge" style="display:inline-block; padding:4px 8px; border-radius:3px; font-weight:bold; margin-top:4px;">
                <?php
                if ('Live' === $runtime_status) {
                    echo '<span style="background-color:#d4edda; color:#155724;">✅ ' . esc_html($runtime_status) . '</span>';
                } elseif ('Scheduled' === $runtime_status) {
                    echo '<span style="background-color:#fff3cd; color:#856404;">⏰ ' . esc_html($runtime_status) . '</span>';
                } elseif ('Expired' === $runtime_status) {
                    echo '<span style="background-color:#f8d7da; color:#721c24;">❌ ' . esc_html($runtime_status) . '</span>';
                } else {
                    echo '<span style="background-color:#e2e3e5; color:#383d41;">⚠️ ' . esc_html($runtime_status) . '</span>';
                }
                ?>
            </span></p>

            <?php if ($status_message) : ?>
                <div style="margin-top:12px; padding:10px; background-color:#fff3cd; border-left:4px solid #ffc107; color:#856404; border-radius:3px;">
                    <p style="margin:0;"><strong><?php esc_html_e('⚠️ Attention:', 'impulse-academy-clone'); ?></strong> <?php echo esc_html($status_message); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($link) : ?>
                <p style="margin-top:12px;"><strong><?php esc_html_e('Resolved Link:', 'impulse-academy-clone'); ?></strong><br><a href="<?php echo esc_url($link['url']); ?>" target="_blank" rel="noopener"><?php echo esc_html(wp_trim_words($link['url'], 10, '...')); ?></a></p>
            <?php else : ?>
                <p class="description" style="margin-top:12px;"><?php esc_html_e('No link is currently configured.', 'impulse-academy-clone'); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }

    public static function save_banner_meta($post_id, $post) {
        if (!isset($_POST[self::META_NONCE]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::META_NONCE])), 'impulse_save_banner_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (wp_is_post_revision($post_id) || self::POST_TYPE !== $post->post_type) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $start_date = self::normalize_datetime_input(isset($_POST['impulse_banner_start_date']) ? wp_unslash($_POST['impulse_banner_start_date']) : '');
        $end_date = self::normalize_datetime_input(isset($_POST['impulse_banner_end_date']) ? wp_unslash($_POST['impulse_banner_end_date']) : '');

        if ($start_date && $end_date && strtotime($end_date) < strtotime($start_date)) {
            $end_date = '';
        }

        $meta = array(
            'caption'         => isset($_POST['impulse_banner_caption']) ? sanitize_text_field(wp_unslash($_POST['impulse_banner_caption'])) : '',
            'cta_text'        => isset($_POST['impulse_banner_cta_text']) ? sanitize_text_field(wp_unslash($_POST['impulse_banner_cta_text'])) : '',
            'target_page_id'  => isset($_POST['impulse_banner_target_page_id']) ? absint($_POST['impulse_banner_target_page_id']) : 0,
            'external_url'    => isset($_POST['impulse_banner_external_url']) ? esc_url_raw(wp_unslash($_POST['impulse_banner_external_url'])) : '',
            'open_new_tab'    => empty($_POST['impulse_banner_open_new_tab']) ? 0 : 1,
            'mobile_image_id' => isset($_POST['impulse_banner_mobile_image_id']) ? absint($_POST['impulse_banner_mobile_image_id']) : 0,
            'display_order'   => isset($_POST['impulse_banner_display_order']) ? absint($_POST['impulse_banner_display_order']) : 0,
            'start_date'      => $start_date,
            'end_date'        => $end_date,
            'is_active'       => empty($_POST['impulse_banner_is_active']) ? 0 : 1,
            'badge_label'     => isset($_POST['impulse_banner_badge_label']) ? sanitize_text_field(wp_unslash($_POST['impulse_banner_badge_label'])) : '',
            'overlay_color'   => self::sanitize_overlay_color(isset($_POST['impulse_banner_overlay_color']) ? wp_unslash($_POST['impulse_banner_overlay_color']) : ''),
            'button_style'    => self::sanitize_choice(isset($_POST['impulse_banner_button_style']) ? wp_unslash($_POST['impulse_banner_button_style']) : 'primary', array('primary', 'secondary', 'outline')),
            'text_alignment'  => self::sanitize_choice(isset($_POST['impulse_banner_text_alignment']) ? wp_unslash($_POST['impulse_banner_text_alignment']) : 'left', array('left', 'center', 'right')),
            'audience_tag'    => isset($_POST['impulse_banner_audience_tag']) ? sanitize_text_field(wp_unslash($_POST['impulse_banner_audience_tag'])) : '',
            'campaign_code'   => isset($_POST['impulse_banner_campaign_code']) ? sanitize_text_field(wp_unslash($_POST['impulse_banner_campaign_code'])) : '',
        );

        foreach ($meta as $key => $value) {
            update_post_meta($post_id, '_impulse_banner_' . $key, $value);
        }
    }

    public static function filter_admin_columns($columns) {
        return array(
            'cb'           => $columns['cb'],
            'thumbnail'    => __('Poster', 'impulse-academy-clone'),
            'title'        => __('Banner Title', 'impulse-academy-clone'),
            'target_link'  => __('Target Link', 'impulse-academy-clone'),
            'runtime'      => __('Runtime Status', 'impulse-academy-clone'),
            'display_order'=> __('Order', 'impulse-academy-clone'),
            'schedule'     => __('Schedule', 'impulse-academy-clone'),
            'date'         => $columns['date'],
        );
    }

    public static function render_admin_column($column, $post_id) {
        $meta = self::get_banner_meta($post_id);

        if ('thumbnail' === $column) {
            $image_id = get_post_thumbnail_id($post_id);
            if ($image_id) {
                echo wp_kses_post(wp_get_attachment_image($image_id, array(90, 60), false, array('style' => 'width:90px;height:60px;object-fit:cover;border-radius:6px;')));
            } else {
                echo esc_html__('No poster', 'impulse-academy-clone');
            }

            return;
        }

        if ('target_link' === $column) {
            $link = self::get_banner_link($post_id, $meta);
            if (!$link) {
                echo esc_html__('No link', 'impulse-academy-clone');
            } else {
                echo '<a href="' . esc_url($link['url']) . '" target="_blank" rel="noopener">' . esc_html(wp_trim_words($link['label'], 6, '...')) . '</a>';
            }

            return;
        }

        if ('runtime' === $column) {
            $status = self::get_runtime_status_label($post_id, $meta);
            echo esc_html($status);
            if (!empty($meta['is_active'])) {
                echo '<br><small>' . esc_html__('Enabled', 'impulse-academy-clone') . '</small>';
            }

            return;
        }

        if ('display_order' === $column) {
            echo esc_html((string) $meta['display_order']);
            return;
        }

        if ('schedule' === $column) {
            if (empty($meta['start_date']) && empty($meta['end_date'])) {
                echo esc_html__('Always On', 'impulse-academy-clone');
                return;
            }

            if (!empty($meta['start_date'])) {
                echo '<strong>' . esc_html__('Start:', 'impulse-academy-clone') . '</strong> ' . esc_html(self::format_datetime_for_display($meta['start_date'])) . '<br>';
            }

            if (!empty($meta['end_date'])) {
                echo '<strong>' . esc_html__('End:', 'impulse-academy-clone') . '</strong> ' . esc_html(self::format_datetime_for_display($meta['end_date']));
            }
        }
    }

    public static function add_row_actions($actions, $post) {
        if (self::POST_TYPE !== $post->post_type || !current_user_can('edit_post', $post->ID)) {
            return $actions;
        }

        $toggle_label = self::get_banner_meta($post->ID, 'is_active') ? __('Deactivate', 'impulse-academy-clone') : __('Activate', 'impulse-academy-clone');
        $toggle_url = wp_nonce_url(
            add_query_arg(
                array(
                    'post_type'              => self::POST_TYPE,
                    'impulse_banner_action'  => 'toggle_active',
                    'banner_id'              => $post->ID,
                ),
                admin_url('edit.php')
            ),
            'impulse_banner_action_toggle_active_' . $post->ID
        );

        $duplicate_url = wp_nonce_url(
            add_query_arg(
                array(
                    'post_type'              => self::POST_TYPE,
                    'impulse_banner_action'  => 'duplicate',
                    'banner_id'              => $post->ID,
                ),
                admin_url('edit.php')
            ),
            'impulse_banner_action_duplicate_' . $post->ID
        );

        $actions['impulse_toggle_active'] = '<a href="' . esc_url($toggle_url) . '">' . esc_html($toggle_label) . '</a>';
        $actions['impulse_duplicate'] = '<a href="' . esc_url($duplicate_url) . '">' . esc_html__('Duplicate', 'impulse-academy-clone') . '</a>';

        return $actions;
    }

    public static function handle_row_actions() {
        if (!is_admin() || !isset($_GET['impulse_banner_action'], $_GET['banner_id'])) {
            return;
        }

        $action = sanitize_key(wp_unslash($_GET['impulse_banner_action']));
        $banner_id = absint($_GET['banner_id']);

        if (!$banner_id || self::POST_TYPE !== get_post_type($banner_id) || !current_user_can('edit_post', $banner_id)) {
            return;
        }

        check_admin_referer('impulse_banner_action_' . $action . '_' . $banner_id);

        if ('toggle_active' === $action) {
            $current = self::get_banner_meta($banner_id, 'is_active');
            update_post_meta($banner_id, '_impulse_banner_is_active', $current ? 0 : 1);

            wp_safe_redirect(
                add_query_arg(
                    array(
                        'post_type'              => self::POST_TYPE,
                        'impulse_banner_notice'  => $current ? 'deactivated' : 'activated',
                    ),
                    admin_url('edit.php')
                )
            );
            exit;
        }

        if ('duplicate' === $action) {
            $new_id = self::duplicate_banner($banner_id);
            $notice = $new_id ? 'duplicated' : 'duplicate_failed';

            wp_safe_redirect(
                add_query_arg(
                    array(
                        'post_type'              => self::POST_TYPE,
                        'impulse_banner_notice'  => $notice,
                        'new_banner_id'          => $new_id,
                    ),
                    admin_url('edit.php')
                )
            );
            exit;
        }
    }

    public static function render_admin_notices() {
        if (!is_admin() || empty($_GET['impulse_banner_notice'])) {
            return;
        }

        $screen = get_current_screen();
        if (!$screen || self::POST_TYPE !== $screen->post_type) {
            return;
        }

        $notice = sanitize_key(wp_unslash($_GET['impulse_banner_notice']));
        $messages = array(
            'activated'       => __('Banner activated successfully.', 'impulse-academy-clone'),
            'deactivated'     => __('Banner deactivated successfully.', 'impulse-academy-clone'),
            'duplicated'      => __('Banner duplicated successfully.', 'impulse-academy-clone'),
            'duplicate_failed'=> __('Banner duplication failed.', 'impulse-academy-clone'),
        );

        if (!isset($messages[$notice])) {
            return;
        }

        $class = 'notice notice-success';
        if ('duplicate_failed' === $notice) {
            $class = 'notice notice-error';
        }

        echo '<div class="' . esc_attr($class) . '"><p>' . esc_html($messages[$notice]) . '</p></div>';
    }

    public static function set_default_admin_order($query) {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }

        if (self::POST_TYPE !== $query->get('post_type') || $query->get('orderby')) {
            return;
        }

        $query->set('orderby', 'date');
        $query->set('order', 'DESC');
    }

    public static function filter_title_placeholder($text, $post) {
        if ($post && self::POST_TYPE === $post->post_type) {
            return __('Enter banner title', 'impulse-academy-clone');
        }

        return $text;
    }

    public static function get_default_settings() {
        return array(
            'enabled'          => 1,
            'autoplay'         => 1,
            'autoplay_delay'   => 5000,
            'loop'             => 1,
            'show_navigation'  => 1,
            'show_pagination'  => 1,
            'pause_on_hover'   => 1,
            'fallback_mode'    => 'static_slides',
            'default_cta_text' => __('Learn More', 'impulse-academy-clone'),
        );
    }

    public static function get_settings() {
        return wp_parse_args((array) get_option(self::OPTION_KEY, array()), self::get_default_settings());
    }

    public static function get_frontend_settings() {
        $settings = self::get_settings();

        return array(
            'autoplay'        => !empty($settings['autoplay']) ? 1 : 0,
            'autoplay_delay'  => max(1000, absint($settings['autoplay_delay'])),
            'loop'            => !empty($settings['loop']) ? 1 : 0,
            'show_navigation' => !empty($settings['show_navigation']) ? 1 : 0,
            'show_pagination' => !empty($settings['show_pagination']) ? 1 : 0,
            'pause_on_hover'  => !empty($settings['pause_on_hover']) ? 1 : 0,
        );
    }

    public static function get_homepage_payload() {
        $settings = self::get_settings();

        if (!empty($settings['enabled'])) {
            $banners = self::get_active_banners();
            if (!empty($banners)) {
                return array(
                    'mode'     => 'dynamic',
                    'banners'   => $banners,
                    'settings' => self::get_frontend_settings(),
                );
            }
        }

        if ('hide_carousel' === $settings['fallback_mode']) {
            return array(
                'mode'     => 'hidden',
                'banners'   => array(),
                'settings' => self::get_frontend_settings(),
            );
        }

        if ('latest_banner' === $settings['fallback_mode']) {
            $latest_banner = self::get_latest_fallback_banner();
            if (!empty($latest_banner)) {
                return array(
                    'mode'     => 'dynamic',
                    'banners'   => array($latest_banner),
                    'settings' => self::get_frontend_settings(),
                );
            }
        }

        return array(
            'mode'     => 'static',
            'banners'   => array(),
            'settings' => self::get_frontend_settings(),
        );
    }

    public static function get_active_banners() {
        $posts = get_posts(
            array(
                'post_type'      => self::POST_TYPE,
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => 'date',
                'order'          => 'DESC',
            )
        );

        $banners = array();
        foreach ($posts as $post) {
            $meta = self::get_banner_meta($post->ID);

            if (!self::is_banner_complete($post->ID, $meta)) {
                continue;
            }

            if (!self::is_banner_visible_now($post->ID, $meta)) {
                continue;
            }

            $post->impulse_banner_meta = $meta;
            $banners[] = $post;
        }

        usort($banners, array(__CLASS__, 'sort_banners'));

        return $banners;
    }

    public static function get_latest_complete_banner() {
        $posts = get_posts(
            array(
                'post_type'      => self::POST_TYPE,
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => 'date',
                'order'          => 'DESC',
            )
        );

        foreach ($posts as $post) {
            $meta = self::get_banner_meta($post->ID);
            if (!self::is_banner_complete($post->ID, $meta)) {
                continue;
            }

            $post->impulse_banner_meta = $meta;
            return $post;
        }

        return null;
    }

    public static function get_latest_fallback_banner() {
        $visible_banners = self::get_active_banners();
        if (!empty($visible_banners)) {
            usort(
                $visible_banners,
                static function ($a, $b) {
                    return strtotime($b->post_date_gmt) <=> strtotime($a->post_date_gmt);
                }
            );

            return $visible_banners[0];
        }

        $posts = get_posts(
            array(
                'post_type'      => self::POST_TYPE,
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => 'date',
                'order'          => 'DESC',
            )
        );

        foreach ($posts as $post) {
            $meta = self::get_banner_meta($post->ID);
            if (!self::is_banner_complete($post->ID, $meta)) {
                continue;
            }

            if (empty($meta['is_active'])) {
                continue;
            }

            $post->impulse_banner_meta = $meta;
            return $post;
        }

        return self::get_latest_complete_banner();
    }

    public static function render_banner_slide($banner) {
        $post = is_object($banner) ? $banner : get_post($banner);
        if (!$post) {
            return '';
        }

        $meta = isset($post->impulse_banner_meta) ? $post->impulse_banner_meta : self::get_banner_meta($post->ID);
        if (!self::is_banner_complete($post->ID, $meta)) {
            return '';
        }

        $desktop_image_id = get_post_thumbnail_id($post->ID);
        $mobile_image_id = !empty($meta['mobile_image_id']) ? $meta['mobile_image_id'] : $desktop_image_id;
        $desktop_url = wp_get_attachment_image_url($desktop_image_id, 'large');
        $mobile_url = wp_get_attachment_image_url($mobile_image_id, 'medium_large');

        if (!$desktop_url) {
            return '';
        }

        $link = self::get_banner_link($post->ID, $meta);
        $cta_text = !empty($meta['cta_text']) ? $meta['cta_text'] : self::get_settings()['default_cta_text'];
        $target_attr = $link && !empty($meta['open_new_tab']) ? ' target="_blank" rel="noopener"' : '';
        $link_open = $link ? '<a class="hero-slide-link" href="' . esc_url($link['url']) . '"' . $target_attr . '>' : '';
        $link_close = $link ? '</a>' : '';
        $slide_classes = array('swiper-slide', 'hero-slide', 'hero-slide-dynamic', 'banner-align-' . $meta['text_alignment']);
        if ($link) {
            $slide_classes[] = 'is-clickable';
        }

        $overlay_style = 'background: linear-gradient(180deg, rgba(15, 23, 42, 0.15), rgba(15, 23, 42, 0.82)), linear-gradient(135deg, ' . esc_attr($meta['overlay_color']) . ', rgba(0, 255, 255, 0.16));';
        $image_alt = get_post_meta($desktop_image_id, '_wp_attachment_image_alt', true);
        if (!$image_alt) {
            $image_alt = get_the_title($post);
        }

        ob_start();
        ?>
        <div class="<?php echo esc_attr(implode(' ', $slide_classes)); ?>">
            <?php echo $link_open; ?>
            <div class="slide-background">
                <picture>
                    <?php if ($mobile_url && $mobile_url !== $desktop_url) : ?>
                        <source media="(max-width: 767px)" srcset="<?php echo esc_url($mobile_url); ?>">
                    <?php endif; ?>
                    <img src="<?php echo esc_url($desktop_url); ?>" alt="<?php echo esc_attr($image_alt); ?>" class="slide-image">
                </picture>
                <div class="slide-overlay" style="<?php echo esc_attr($overlay_style); ?>"></div>
            </div>
            <div class="slide-content">
                <?php if (!empty($meta['badge_label'])) : ?>
                    <span class="slide-badge"><?php echo esc_html($meta['badge_label']); ?></span>
                <?php endif; ?>
                <h3 class="slide-title"><?php echo esc_html(get_the_title($post)); ?></h3>
                <?php if (!empty($meta['caption'])) : ?>
                    <p class="slide-desc"><?php echo esc_html($meta['caption']); ?></p>
                <?php endif; ?>
                <?php if (!empty($cta_text) && $link) : ?>
                    <span class="hero-slide-cta hero-slide-cta-<?php echo esc_attr($meta['button_style']); ?>"><?php echo esc_html($cta_text); ?></span>
                <?php endif; ?>
            </div>
            <?php echo $link_close; ?>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    public static function get_banner_swiper_attributes() {
        $settings = self::get_frontend_settings();

        return sprintf(
            'data-autoplay="%d" data-delay="%d" data-loop="%d" data-navigation="%d" data-pagination="%d" data-pause-hover="%d"',
            (int) $settings['autoplay'],
            (int) $settings['autoplay_delay'],
            (int) $settings['loop'],
            (int) $settings['show_navigation'],
            (int) $settings['show_pagination'],
            (int) $settings['pause_on_hover']
        );
    }

    public static function get_banner_meta($post_id, $key = null) {
        $defaults = array(
            'caption'         => '',
            'cta_text'        => '',
            'target_page_id'  => 0,
            'external_url'    => '',
            'open_new_tab'    => 0,
            'mobile_image_id' => 0,
            'display_order'   => 0,
            'start_date'      => '',
            'end_date'        => '',
            'is_active'       => 1,
            'badge_label'     => '',
            'overlay_color'   => 'rgba(37, 99, 235, 0.42)',
            'button_style'    => 'primary',
            'text_alignment'  => 'left',
            'audience_tag'    => '',
            'campaign_code'   => '',
        );

        if (null !== $key) {
            $value = get_post_meta($post_id, '_impulse_banner_' . $key, true);
            return '' === $value ? $defaults[$key] : $value;
        }

        $meta = array();
        foreach ($defaults as $meta_key => $default) {
            $value = get_post_meta($post_id, '_impulse_banner_' . $meta_key, true);
            $meta[$meta_key] = '' === $value ? $default : $value;
        }

        $meta['target_page_id'] = absint($meta['target_page_id']);
        $meta['mobile_image_id'] = absint($meta['mobile_image_id']);
        $meta['display_order'] = absint($meta['display_order']);
        $meta['open_new_tab'] = !empty($meta['open_new_tab']) ? 1 : 0;
        $meta['is_active'] = !empty($meta['is_active']) ? 1 : 0;

        return $meta;
    }

    public static function get_banner_link($post_id, $meta = null) {
        $meta = is_array($meta) ? $meta : self::get_banner_meta($post_id);

        if (!empty($meta['target_page_id'])) {
            $url = get_permalink($meta['target_page_id']);
            if ($url) {
                return array(
                    'url'   => $url,
                    'label' => get_the_title($meta['target_page_id']),
                );
            }
        }

        if (!empty($meta['external_url'])) {
            return array(
                'url'   => esc_url_raw($meta['external_url']),
                'label' => $meta['external_url'],
            );
        }

        return null;
    }

    public static function is_banner_complete($post_id, $meta = null) {
        $meta = is_array($meta) ? $meta : self::get_banner_meta($post_id);
        return (bool) get_post_thumbnail_id($post_id);
    }

    public static function is_banner_visible_now($post_id, $meta = null) {
        $meta = is_array($meta) ? $meta : self::get_banner_meta($post_id);
        if (empty($meta['is_active'])) {
            return false;
        }

        $now = current_datetime()->getTimestamp();

        if (!empty($meta['start_date'])) {
            $start = self::parse_local_timestamp($meta['start_date']);
            if ($start && $start > $now) {
                return false;
            }
        }

        if (!empty($meta['end_date'])) {
            $end = self::parse_local_timestamp($meta['end_date']);
            if ($end && $end < $now) {
                return false;
            }
        }

        return true;
    }

    public static function get_runtime_status_label($post_id, $meta = null) {
        $meta = is_array($meta) ? $meta : self::get_banner_meta($post_id);

        if (!self::is_banner_complete($post_id, $meta)) {
            return __('Incomplete Poster', 'impulse-academy-clone');
        }

        if (empty($meta['is_active'])) {
            return __('Inactive', 'impulse-academy-clone');
        }

        $now = current_datetime()->getTimestamp();

        if (!empty($meta['start_date'])) {
            $start = self::parse_local_timestamp($meta['start_date']);
            if ($start && $start > $now) {
                return __('Scheduled', 'impulse-academy-clone');
            }
        }

        if (!empty($meta['end_date'])) {
            $end = self::parse_local_timestamp($meta['end_date']);
            if ($end && $end < $now) {
                return __('Expired', 'impulse-academy-clone');
            }
        }

        return __('Live', 'impulse-academy-clone');
    }

    private static function duplicate_banner($banner_id) {
        $post = get_post($banner_id);
        if (!$post || self::POST_TYPE !== $post->post_type) {
            return 0;
        }

        $new_post_id = wp_insert_post(
            array(
                'post_type'   => self::POST_TYPE,
                'post_status' => 'draft',
                'post_title'  => sprintf(__('Copy of %s', 'impulse-academy-clone'), $post->post_title),
            ),
            true
        );

        if (is_wp_error($new_post_id) || !$new_post_id) {
            return 0;
        }

        $custom = get_post_custom($banner_id);
        foreach ($custom as $meta_key => $values) {
            if (in_array($meta_key, array('_edit_lock', '_edit_last'), true)) {
                continue;
            }

            foreach ($values as $value) {
                add_post_meta($new_post_id, $meta_key, maybe_unserialize($value));
            }
        }

        return (int) $new_post_id;
    }

    private static function sort_banners($a, $b) {
        $meta_a = isset($a->impulse_banner_meta) ? $a->impulse_banner_meta : self::get_banner_meta($a->ID);
        $meta_b = isset($b->impulse_banner_meta) ? $b->impulse_banner_meta : self::get_banner_meta($b->ID);

        $order_a = isset($meta_a['display_order']) ? (int) $meta_a['display_order'] : 0;
        $order_b = isset($meta_b['display_order']) ? (int) $meta_b['display_order'] : 0;

        if ($order_a === $order_b) {
            return strtotime($b->post_date_gmt) <=> strtotime($a->post_date_gmt);
        }

        return $order_a <=> $order_b;
    }

    private static function normalize_datetime_input($value) {
        $value = sanitize_text_field($value);
        if (empty($value)) {
            return '';
        }

        $value = str_replace('T', ' ', $value);
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) {
            $value .= ':00';
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
            return '';
        }

        return $value;
    }

    private static function format_datetime_for_input($value) {
        if (empty($value)) {
            return '';
        }

        $timestamp = self::parse_local_timestamp($value);
        if (!$timestamp) {
            return '';
        }

        return wp_date('Y-m-d\TH:i', $timestamp, wp_timezone());
    }

    private static function format_datetime_for_display($value) {
        $timestamp = self::parse_local_timestamp($value);
        if (!$timestamp) {
            return '';
        }

        return wp_date('M j, Y g:i a', $timestamp, wp_timezone());
    }

    private static function parse_local_timestamp($value) {
        if (empty($value)) {
            return 0;
        }

        try {
            $datetime = new DateTimeImmutable($value, wp_timezone());
            return $datetime->getTimestamp();
        } catch (Exception $exception) {
            return 0;
        }
    }

    private static function sanitize_choice($value, $allowed) {
        $value = sanitize_key($value);
        return in_array($value, $allowed, true) ? $value : $allowed[0];
    }

    private static function sanitize_overlay_color($value) {
        $value = trim(sanitize_text_field($value));
        if (empty($value)) {
            return 'rgba(37, 99, 235, 0.42)';
        }

        if (preg_match('/^#[0-9a-fA-F]{3,8}$/', $value)) {
            return $value;
        }

        if (preg_match('/^rgba?\([0-9,\.\s]+\)$/', $value)) {
            return $value;
        }

        return 'rgba(37, 99, 235, 0.42)';
    }
}

Impulse_Clone_Banner_Manager::init();
