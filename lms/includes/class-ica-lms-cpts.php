<?php
if (!defined('ABSPATH')) {
    exit;
}

class ICA_LMS_CPTs {
    public static function init() {
        add_action('init', array(__CLASS__, 'register_post_types'));
        add_action('add_meta_boxes', array(__CLASS__, 'register_meta_boxes'));
        add_action('save_post_courses', array(__CLASS__, 'save_course_meta'));
    }

    public static function register_post_types() {
        if (!post_type_exists('courses')) {
            register_post_type('courses', array(
                'labels' => array(
                    'name' => 'Courses',
                    'singular_name' => 'Course',
                ),
                'public' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => 'courses'),
                'menu_icon' => 'dashicons-welcome-learn-more',
                'supports' => array('title', 'editor', 'thumbnail', 'author'),
            ));
        }
    }

    public static function register_meta_boxes() {
        add_meta_box(
            'ica_lms_course_commerce_meta',
            'LMS Commerce Settings',
            array(__CLASS__, 'render_course_meta_box'),
            'courses',
            'side',
            'default'
        );
    }

    public static function render_course_meta_box($post) {
        wp_nonce_field('ica_lms_save_course', 'ica_lms_course_nonce');
        $price = get_post_meta($post->ID, '_ica_course_price', true);
        $currency = get_post_meta($post->ID, '_ica_course_currency', true);
        $duration_days = (int) get_post_meta($post->ID, '_ica_course_access_days', true);
        if ($currency === '') {
            $currency = 'INR';
        }
        if ($duration_days <= 0) {
            $duration_days = 180;
        }
        ?>
        <p>
            <label for="ica_course_price"><strong>Price</strong></label><br>
            <input type="number" min="0" step="0.01" name="ica_course_price" id="ica_course_price" value="<?php echo esc_attr($price); ?>" style="width:100%;">
            <small>Use 0 for free courses.</small>
        </p>
        <p>
            <label for="ica_course_currency"><strong>Currency</strong></label><br>
            <input type="text" maxlength="10" name="ica_course_currency" id="ica_course_currency" value="<?php echo esc_attr($currency); ?>" style="width:100%;">
        </p>
        <p>
            <label for="ica_course_access_days"><strong>Access Duration (days)</strong></label><br>
            <input type="number" min="1" step="1" name="ica_course_access_days" id="ica_course_access_days" value="<?php echo esc_attr($duration_days); ?>" style="width:100%;">
        </p>
        <?php
    }

    public static function save_course_meta($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (wp_is_post_revision($post_id)) {
            return;
        }
        if (!isset($_POST['ica_lms_course_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ica_lms_course_nonce'])), 'ica_lms_save_course')) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $price = isset($_POST['ica_course_price']) ? (float) $_POST['ica_course_price'] : 0;
        $currency = isset($_POST['ica_course_currency']) ? sanitize_text_field(wp_unslash($_POST['ica_course_currency'])) : 'INR';
        $access_days = isset($_POST['ica_course_access_days']) ? (int) $_POST['ica_course_access_days'] : 180;

        update_post_meta($post_id, '_ica_course_price', max(0, $price));
        update_post_meta($post_id, '_ica_course_currency', strtoupper(substr($currency, 0, 10)));
        update_post_meta($post_id, '_ica_course_access_days', max(1, $access_days));
    }
}
