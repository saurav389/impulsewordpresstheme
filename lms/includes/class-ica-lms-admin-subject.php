<?php
if (!defined('ABSPATH')) {
    exit;
}

class ICA_LMS_Admin_Subject {
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        add_action('admin_init', array(__CLASS__, 'handle_form_submission'));
        add_action('admin_post_ica_lms_delete_subject', array(__CLASS__, 'handle_delete_subject'));
    }

    public static function add_admin_menu() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Add Subjects submenu under Teachers
        add_submenu_page(
            'ica-lms',
            'Subjects',
            'Subjects',
            'manage_options',
            'ica-lms-subjects',
            array(__CLASS__, 'render_subjects_page')
        );
    }

    /**
     * Render subjects management page
     */
    public static function render_subjects_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $subject_id = isset($_GET['subject_id']) ? (int) $_GET['subject_id'] : 0;

        if ($action === 'edit' && $subject_id) {
            self::render_edit_form($subject_id);
        } elseif ($action === 'add') {
            self::render_add_form();
        } else {
            self::render_subjects_list();
        }
    }

    /**
     * Render subjects list
     */
    public static function render_subjects_list() {
        $paged = isset($_GET['paged']) ? (int) $_GET['paged'] : 1;
        $limit = 20;
        $offset = ($paged - 1) * $limit;
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

        $subjects = ICA_LMS_DB::get_subjects_list($limit, $offset, $search);
        $total = ICA_LMS_DB::count_subjects($search);
        $total_pages = ceil($total / $limit);

        ?>
        <div class="wrap">
            <h1>LMS Subjects Management
                <a href="<?php echo esc_url(add_query_arg('action', 'add')); ?>" class="page-title-action">Add New Subject</a>
            </h1>

            <?php
            // Display success message if exists
            $success_msg = get_transient('ica_lms_success_message');
            if ($success_msg) {
                echo '<div class="notice notice-success is-dismissible"><p><strong>✓ Success!</strong> ' . esc_html($success_msg) . '</p></div>';
                delete_transient('ica_lms_success_message');
            }
            ?>

            <div class="ica-lms-filters" style="margin-bottom: 20px; padding: 15px; background: #f5f5f5; border-radius: 5px;">
                <form method="get">
                    <input type="hidden" name="page" value="ica-lms-subjects">
                    <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; align-items: end;">
                        <div>
                            <label>Search (Name, Code, Description):</label>
                            <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Search...">
                        </div>
                        <div style="grid-column: auto;">
                            <button type="submit" class="button button-primary">Search</button>
                        </div>
                        <div style="grid-column: auto;">
                            <a href="?page=ica-lms-subjects" class="button">Clear</a>
                        </div>
                    </div>
                </form>
            </div>

            <table class="wp-list-table widefat striped hover">
                <thead>
                    <tr>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($subjects)) : ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px;">No subjects found.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($subjects as $subject) : ?>
                            <tr>
                                <td><strong><?php echo esc_html($subject['subject_code']); ?></strong></td>
                                <td><?php echo esc_html($subject['subject_name']); ?></td>
                                <td><?php echo esc_html(substr($subject['description'], 0, 50)) . (strlen($subject['description']) > 50 ? '...' : ''); ?></td>
                                <td>
                                    <span style="padding: 3px 8px; border-radius: 3px; background: <?php echo $subject['status'] === 'active' ? '#28a745' : '#dc3545'; ?>; color: white;">
                                        <?php echo ucfirst($subject['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html(date('M d, Y', strtotime($subject['created_at']))); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(array('action' => 'edit', 'subject_id' => $subject['id']))); ?>" class="button button-small button-primary">Edit</a>
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=ica_lms_delete_subject&subject_id=' . $subject['id']), 'ica_lms_delete_subject')); ?>" class="button button-small button-secondary" onclick="return confirm('Are you sure?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="pagination">
                <?php for ($p = 1; $p <= $total_pages; $p++) : ?>
                    <?php
                    $page_link = add_query_arg('paged', $p);
                    $class = ($p === $paged) ? 'current' : '';
                    ?>
                    <a href="<?php echo esc_url($page_link); ?>" class="<?php echo esc_attr($class); ?>">
                        <?php echo esc_html($p); ?>
                    </a>
                <?php endfor; ?>
            </div>
            <div style="margin-top: 10px;">
                <small>Total: <?php echo esc_html($total); ?> subjects</small>
            </div>
        </div>
        <?php
    }

    /**
     * Render add subject form
     */
    public static function render_add_form() {
        ?>
        <div class="wrap">
            <h1>Add New Subject</h1>
            <form method="post" style="max-width: 600px;">
                <?php wp_nonce_field('ica_lms_add_subject'); ?>
                <input type="hidden" name="action" value="ica_lms_add_subject">

                <table class="form-table">
                    <tr>
                        <th><label>Subject Code *</label></th>
                        <td><input type="text" name="subject_code" required placeholder="e.g., CS-001, MATH-101" style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Subject Name *</label></th>
                        <td><input type="text" name="subject_name" required style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Description</label></th>
                        <td><textarea name="description" rows="4" style="width: 100%; padding: 8px;"></textarea></td>
                    </tr>

                    <tr>
                        <td colspan="2" style="padding: 20px 0;">
                            <button type="submit" class="button button-primary button-large">Add Subject</button>
                            <a href="?page=ica-lms-subjects" class="button button-secondary button-large">Cancel</a>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
    }

    /**
     * Render edit subject form
     */
    public static function render_edit_form($subject_id) {
        $subject = ICA_LMS_DB::get_subject($subject_id);

        if (!$subject) {
            echo '<div class="wrap"><p>Subject not found.</p></div>';
            return;
        }

        ?>
        <div class="wrap">
            <h1>Edit Subject</h1>
            <form method="post" style="max-width: 600px;">
                <?php wp_nonce_field('ica_lms_edit_subject'); ?>
                <input type="hidden" name="action" value="ica_lms_edit_subject">
                <input type="hidden" name="subject_id" value="<?php echo esc_attr($subject['id']); ?>">

                <table class="form-table">
                    <tr>
                        <th><label>Subject Code *</label></th>
                        <td><input type="text" name="subject_code" value="<?php echo esc_attr($subject['subject_code']); ?>" required style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Subject Name *</label></th>
                        <td><input type="text" name="subject_name" value="<?php echo esc_attr($subject['subject_name']); ?>" required style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Description</label></th>
                        <td><textarea name="description" rows="4" style="width: 100%; padding: 8px;"><?php echo esc_textarea($subject['description']); ?></textarea></td>
                    </tr>

                    <tr>
                        <th><label>Status</label></th>
                        <td>
                            <select name="status" style="width: 100%; padding: 8px;">
                                <option value="active" <?php selected($subject['status'], 'active'); ?>>Active</option>
                                <option value="inactive" <?php selected($subject['status'], 'inactive'); ?>>Inactive</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2" style="padding: 20px 0;">
                            <button type="submit" class="button button-primary button-large">Update Subject</button>
                            <a href="?page=ica-lms-subjects" class="button button-secondary button-large">Cancel</a>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
    }

    /**
     * Handle form submission
     */
    public static function handle_form_submission() {
        if (!isset($_POST['action'])) {
            return;
        }

        $action = sanitize_text_field($_POST['action']);

        if ($action === 'ica_lms_add_subject') {
            if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['_wpnonce'], 'ica_lms_add_subject')) {
                wp_die('Unauthorized');
            }

            $subject_code = $_POST['subject_code'] ?? '';
            $subject_name = $_POST['subject_name'] ?? '';
            $description = $_POST['description'] ?? '';

            if (empty($subject_code) || empty($subject_name)) {
                echo '<div class="notice notice-error"><p>Subject Code and Subject Name are required.</p></div>';
                return;
            }

            $result = ICA_LMS_DB::create_subject($subject_name, $subject_code, $description);

            if ($result && isset($result['success']) && $result['success']) {
                set_transient('ica_lms_success_message', 'Subject created successfully!', 30);
                wp_redirect(add_query_arg('page', 'ica-lms-subjects', admin_url('admin.php')));
                exit;
            } else {
                $error_msg = isset($result['error']) ? $result['error'] : 'Error adding subject. Please try again.';
                echo '<div class="notice notice-error"><p>' . esc_html($error_msg) . '</p></div>';
            }
        } elseif ($action === 'ica_lms_edit_subject') {
            if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['_wpnonce'], 'ica_lms_edit_subject')) {
                wp_die('Unauthorized');
            }

            $subject_id = (int) $_POST['subject_id'];
            $subject_code = $_POST['subject_code'] ?? '';
            $subject_name = $_POST['subject_name'] ?? '';
            $description = $_POST['description'] ?? '';
            $status = $_POST['status'] ?? 'active';

            if (empty($subject_code) || empty($subject_name)) {
                echo '<div class="notice notice-error"><p>Subject Code and Subject Name are required.</p></div>';
                return;
            }

            ICA_LMS_DB::update_subject($subject_id, $subject_name, $subject_code, $description, $status);

            set_transient('ica_lms_success_message', 'Subject updated successfully!', 30);

            wp_redirect(add_query_arg('page', 'ica-lms-subjects', admin_url('admin.php')));
            exit;
        }
    }

    /**
     * Handle delete subject
     */
    public static function handle_delete_subject() {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_REQUEST['_wpnonce'], 'ica_lms_delete_subject')) {
            wp_die('Unauthorized');
        }

        $subject_id = (int) $_REQUEST['subject_id'];
        ICA_LMS_DB::delete_subject($subject_id);

        set_transient('ica_lms_success_message', 'Subject deleted successfully!', 30);

        wp_redirect(admin_url('admin.php?page=ica-lms-subjects'));
        exit;
    }
}
