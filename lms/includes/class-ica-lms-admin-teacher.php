<?php
if (!defined('ABSPATH')) {
    exit;
}

class ICA_LMS_Admin_Teacher {
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        add_action('admin_init', array(__CLASS__, 'handle_form_submission'));
        add_action('admin_post_ica_lms_delete_teacher', array(__CLASS__, 'handle_delete_teacher'));
    }

    public static function add_admin_menu() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Add Teachers submenu
        add_submenu_page(
            'ica-lms',
            'Teachers',
            'Teachers',
            'manage_options',
            'ica-lms-teachers',
            array(__CLASS__, 'render_teachers_page')
        );
    }

    /**
     * Render teachers management page
     */
    public static function render_teachers_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $teacher_id = isset($_GET['teacher_id']) ? (int) $_GET['teacher_id'] : 0;

        if ($action === 'edit' && $teacher_id) {
            self::render_edit_form($teacher_id);
        } elseif ($action === 'add') {
            self::render_add_form();
        } else {
            self::render_teachers_list();
        }
    }

    /**
     * Render teachers list
     */
    public static function render_teachers_list() {
        $paged = isset($_GET['paged']) ? (int) $_GET['paged'] : 1;
        $limit = 20;
        $offset = ($paged - 1) * $limit;
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

        $teachers = ICA_LMS_DB::get_teachers($limit, $offset, $search);
        $total = ICA_LMS_DB::count_teachers($search);
        $total_pages = ceil($total / $limit);

        ?>
        <div class="wrap">
            <h1>LMS Teachers Management
                <a href="<?php echo esc_url(add_query_arg('action', 'add')); ?>" class="page-title-action">Add New Teacher</a>
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
                    <input type="hidden" name="page" value="ica-lms-teachers">
                    <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; align-items: end;">
                        <div>
                            <label>Search (Name, Teacher ID, Mobile, Email):</label>
                            <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Search...">
                        </div>
                        <div style="grid-column: auto;">
                            <button type="submit" class="button button-primary">Search</button>
                        </div>
                        <div style="grid-column: auto;">
                            <a href="?page=ica-lms-teachers" class="button">Clear</a>
                        </div>
                    </div>
                </form>
            </div>

            <table class="wp-list-table widefat striped hover">
                <thead>
                    <tr>
                        <th>Teacher ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Gender</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($teachers)) : ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 20px;">No teachers found.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($teachers as $teacher) : ?>
                            <tr>
                                <td><strong><?php echo esc_html($teacher['teacher_id']); ?></strong></td>
                                <td><?php echo esc_html($teacher['name']); ?></td>
                                <td><?php echo esc_html($teacher['email']); ?></td>
                                <td><?php echo esc_html($teacher['mobile_no']); ?></td>
                                <td><?php echo esc_html($teacher['gender']); ?></td>
                                <td><?php echo esc_html($teacher['department']); ?></td>
                                <td>
                                    <span style="padding: 3px 8px; border-radius: 3px; background: <?php echo $teacher['status'] === 'active' ? '#28a745' : '#dc3545'; ?>; color: white;">
                                        <?php echo ucfirst($teacher['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(array('action' => 'edit', 'teacher_id' => $teacher['id']))); ?>" class="button button-small button-primary">Edit</a>
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=ica_lms_delete_teacher&teacher_id=' . $teacher['id']), 'ica_lms_delete_teacher')); ?>" class="button button-small button-secondary" onclick="return confirm('Are you sure?');">Delete</a>
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
                <small>Total: <?php echo esc_html($total); ?> teachers</small>
            </div>
        </div>
        <?php
    }

    /**
     * Render add teacher form
     */
    public static function render_add_form() {
        ?>
        <div class="wrap">
            <h1>Add New Teacher</h1>
            <form method="post" enctype="multipart/form-data" style="max-width: 900px;">
                <?php wp_nonce_field('ica_lms_add_teacher'); ?>
                <input type="hidden" name="action" value="ica_lms_add_teacher">

                <table class="form-table">
                    <tr>
                        <th><label>Name *</label></th>
                        <td><input type="text" name="name" required style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Father Name</label></th>
                        <td><input type="text" name="father_name" style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Gender *</label></th>
                        <td>
                            <select name="gender" required style="width: 100%; padding: 8px;">
                                <option value="">-- Select Gender --</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th><label>Date of Birth</label></th>
                        <td><input type="date" name="date_of_birth" style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Mobile Number (Max 10 digits) *</label></th>
                        <td><input type="tel" name="mobile_no" required pattern="\d{1,10}" maxlength="10" placeholder="e.g., 9876543210" style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Email ID *</label></th>
                        <td><input type="email" name="email" required style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Aadhar Number</label></th>
                        <td><input type="text" name="aadhar_no" placeholder="e.g., 1234-5678-9012" style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Qualification</label></th>
                        <td><input type="text" name="qualification" placeholder="e.g., M.Tech, Ph.D" style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Department</label></th>
                        <td><input type="text" name="department" placeholder="e.g., Computer Science" style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Subjects (Can teach multiple)</label></th>
                        <td>
                            <div style="border: 1px solid #ddd; padding: 10px; border-radius: 4px; max-height: 300px; overflow-y: auto;">
                                <?php
                                $all_subjects = ICA_LMS_DB::get_subjects('active');
                                if (!empty($all_subjects)) {
                                    foreach ($all_subjects as $subject) {
                                        echo '<div style="margin-bottom: 8px;">';
                                        echo '<label style="margin: 0;">';
                                        echo '<input type="checkbox" name="subject_ids[]" value="' . esc_attr($subject['id']) . '"> ';
                                        echo esc_html($subject['subject_name']) . ' (' . esc_html($subject['subject_code']) . ')';
                                        echo '</label>';
                                        echo '</div>';
                                    }
                                } else {
                                    echo '<p style="margin: 0; color: #666;">No subjects available. <a href="' . esc_url(add_query_arg('page', 'ica-lms-subjects', admin_url('admin.php'))) . '">Create subjects first</a></p>';
                                }
                                ?>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th><label>Photo</label></th>
                        <td><input type="file" name="teacher_photo" accept="image/*"></td>
                    </tr>

                    <tr>
                        <th><label>Signature</label></th>
                        <td><input type="file" name="teacher_signature" accept="image/*"></td>
                    </tr>

                    <tr>
                        <td colspan="2" style="padding: 20px 0;">
                            <button type="submit" class="button button-primary button-large">Add Teacher</button>
                            <a href="?page=ica-lms-teachers" class="button button-secondary button-large">Cancel</a>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
    }

    /**
     * Render edit teacher form
     */
    public static function render_edit_form($teacher_id) {
        $teacher = ICA_LMS_DB::get_teacher($teacher_id);

        if (!$teacher) {
            echo '<div class="wrap"><p>Teacher not found.</p></div>';
            return;
        }

        ?>
        <div class="wrap">
            <h1>Edit Teacher</h1>
            <form method="post" enctype="multipart/form-data" style="max-width: 900px;">
                <?php wp_nonce_field('ica_lms_edit_teacher'); ?>
                <input type="hidden" name="action" value="ica_lms_edit_teacher">
                <input type="hidden" name="teacher_id" value="<?php echo esc_attr($teacher['id']); ?>">

                <table class="form-table">
                    <tr>
                        <th><label>Teacher ID</label></th>
                        <td><input type="text" value="<?php echo esc_attr($teacher['teacher_id']); ?>" disabled style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Name *</label></th>
                        <td><input type="text" name="name" value="<?php echo esc_attr($teacher['name']); ?>" required style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Father Name</label></th>
                        <td><input type="text" name="father_name" value="<?php echo esc_attr($teacher['father_name']); ?>" style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Gender</label></th>
                        <td>
                            <select name="gender" style="width: 100%; padding: 8px;">
                                <option value="">-- Select Gender --</option>
                                <option value="Male" <?php selected($teacher['gender'], 'Male'); ?>>Male</option>
                                <option value="Female" <?php selected($teacher['gender'], 'Female'); ?>>Female</option>
                                <option value="Other" <?php selected($teacher['gender'], 'Other'); ?>>Other</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th><label>Date of Birth</label></th>
                        <td><input type="date" name="date_of_birth" value="<?php echo esc_attr($teacher['date_of_birth']); ?>" style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Mobile Number (Max 10 digits)</label></th>
                        <td><input type="tel" name="mobile_no" value="<?php echo esc_attr($teacher['mobile_no']); ?>" pattern="\d{1,10}" maxlength="10" style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Email ID</label></th>
                        <td><input type="email" name="email" value="<?php echo esc_attr($teacher['email']); ?>" style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Aadhar Number</label></th>
                        <td><input type="text" name="aadhar_no" value="<?php echo esc_attr($teacher['aadhar_no']); ?>" style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Qualification</label></th>
                        <td><input type="text" name="qualification" value="<?php echo esc_attr($teacher['qualification']); ?>" style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Department</label></th>
                        <td><input type="text" name="department" value="<?php echo esc_attr($teacher['department']); ?>" style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Subjects (Can teach multiple)</label></th>
                        <td>
                            <div style="border: 1px solid #ddd; padding: 10px; border-radius: 4px; max-height: 300px; overflow-y: auto;">
                                <?php
                                $all_subjects = ICA_LMS_DB::get_subjects('active');
                                $teacher_subjects = ICA_LMS_DB::get_teacher_subjects($teacher['id']);
                                $teacher_subject_ids = array_map(function($s) { return $s['id']; }, $teacher_subjects);
                                
                                if (!empty($all_subjects)) {
                                    foreach ($all_subjects as $subject) {
                                        $is_checked = in_array($subject['id'], $teacher_subject_ids);
                                        echo '<div style="margin-bottom: 8px;">';
                                        echo '<label style="margin: 0;">';
                                        echo '<input type="checkbox" name="subject_ids[]" value="' . esc_attr($subject['id']) . '"' . ($is_checked ? ' checked' : '') . '> ';
                                        echo esc_html($subject['subject_name']) . ' (' . esc_html($subject['subject_code']) . ')';
                                        echo '</label>';
                                        echo '</div>';
                                    }
                                } else {
                                    echo '<p style="margin: 0; color: #666;">No subjects available. <a href="' . esc_url(add_query_arg('page', 'ica-lms-subjects', admin_url('admin.php'))) . '">Create subjects first</a></p>';
                                }
                                ?>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th><label>Status</label></th>
                        <td>
                            <select name="status" style="width: 100%; padding: 8px;">
                                <option value="active" <?php selected($teacher['status'], 'active'); ?>>Active</option>
                                <option value="inactive" <?php selected($teacher['status'], 'inactive'); ?>>Inactive</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th><label>Photo</label></th>
                        <td>
                            <?php if (!empty($teacher['photo_url'])) : ?>
                                <div style="margin-bottom: 10px;">
                                    <img src="<?php echo esc_url($teacher['photo_url']); ?>" alt="Teacher Photo" style="max-width: 100px; height: 100px;">
                                </div>
                            <?php endif; ?>
                            <p><input type="file" name="teacher_photo" accept="image/*"></p>
                        </td>
                    </tr>

                    <tr>
                        <th><label>Signature</label></th>
                        <td>
                            <?php if (!empty($teacher['signature_url'])) : ?>
                                <div style="margin-bottom: 10px;">
                                    <img src="<?php echo esc_url($teacher['signature_url']); ?>" alt="Teacher Signature" style="max-width: 100px; height: 50px;">
                                </div>
                            <?php endif; ?>
                            <p><input type="file" name="teacher_signature" accept="image/*"></p>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2" style="padding: 20px 0;">
                            <button type="submit" class="button button-primary button-large">Update Teacher</button>
                            <a href="?page=ica-lms-teachers" class="button button-secondary button-large">Cancel</a>
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

        if ($action === 'ica_lms_add_teacher') {
            if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['_wpnonce'], 'ica_lms_add_teacher')) {
                wp_die('Unauthorized');
            }

            $data = array(
                'name' => $_POST['name'] ?? '',
                'father_name' => $_POST['father_name'] ?? '',
                'gender' => $_POST['gender'] ?? '',
                'date_of_birth' => $_POST['date_of_birth'] ?? '',
                'mobile_no' => $_POST['mobile_no'] ?? '',
                'email' => $_POST['email'] ?? '',
                'aadhar_no' => $_POST['aadhar_no'] ?? '',
                'qualification' => $_POST['qualification'] ?? '',
                'department' => $_POST['department'] ?? '',
                'subject_ids' => isset($_POST['subject_ids']) ? array_map('intval', (array) $_POST['subject_ids']) : [],
            );

            // Validate required fields
            if (empty($data['name']) || empty($data['gender']) || empty($data['mobile_no']) || empty($data['email'])) {
                echo '<div class="notice notice-error"><p>Please fill all required fields: Name, Gender, Mobile No, and Email.</p></div>';
                return;
            }

            // Handle file uploads
            if (!function_exists('wp_handle_upload')) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }

            if (!empty($_FILES['teacher_photo']['name'])) {
                $upload = wp_handle_upload($_FILES['teacher_photo'], array('test_form' => false));
                if ($upload && !isset($upload['error'])) {
                    $data['photo_url'] = $upload['url'];
                }
            }

            if (!empty($_FILES['teacher_signature']['name'])) {
                $upload = wp_handle_upload($_FILES['teacher_signature'], array('test_form' => false));
                if ($upload && !isset($upload['error'])) {
                    $data['signature_url'] = $upload['url'];
                }
            }

            $result = ICA_LMS_DB::create_teacher($data);

            if ($result && isset($result['success']) && $result['success']) {
                // Store success message with credentials in transient
                $success_msg = sprintf(
                    'Teacher created successfully! WordPress user created with - Username: %s | Password: %s',
                    isset($result['teacher_id']) ? $result['teacher_id'] : 'Teacher',
                    $data['mobile_no']
                );
                set_transient('ica_lms_success_message', $success_msg, 30);
                
                wp_redirect(add_query_arg('page', 'ica-lms-teachers', admin_url('admin.php')));
                exit;
            } else {
                $error_msg = isset($result['error']) ? $result['error'] : 'Error adding teacher. Please try again.';
                echo '<div class="notice notice-error"><p>' . esc_html($error_msg) . '</p></div>';
            }
        } elseif ($action === 'ica_lms_edit_teacher') {
            if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['_wpnonce'], 'ica_lms_edit_teacher')) {
                wp_die('Unauthorized');
            }

            $teacher_id = (int) $_POST['teacher_id'];

            $data = array(
                'name' => $_POST['name'] ?? '',
                'father_name' => $_POST['father_name'] ?? '',
                'gender' => $_POST['gender'] ?? '',
                'date_of_birth' => $_POST['date_of_birth'] ?? '',
                'mobile_no' => $_POST['mobile_no'] ?? '',
                'email' => $_POST['email'] ?? '',
                'aadhar_no' => $_POST['aadhar_no'] ?? '',
                'qualification' => $_POST['qualification'] ?? '',
                'department' => $_POST['department'] ?? '',
                'status' => $_POST['status'] ?? 'active',
                'subject_ids' => isset($_POST['subject_ids']) ? array_map('intval', (array) $_POST['subject_ids']) : [],
            );

            // Handle file uploads
            if (!function_exists('wp_handle_upload')) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }

            if (!empty($_FILES['teacher_photo']['name'])) {
                $upload = wp_handle_upload($_FILES['teacher_photo'], array('test_form' => false));
                if ($upload && !isset($upload['error'])) {
                    $data['photo_url'] = $upload['url'];
                }
            }

            if (!empty($_FILES['teacher_signature']['name'])) {
                $upload = wp_handle_upload($_FILES['teacher_signature'], array('test_form' => false));
                if ($upload && !isset($upload['error'])) {
                    $data['signature_url'] = $upload['url'];
                }
            }

            ICA_LMS_DB::update_teacher($teacher_id, $data);

            set_transient('ica_lms_success_message', 'Teacher updated successfully!', 30);

            wp_redirect(add_query_arg('page', 'ica-lms-teachers', admin_url('admin.php')));
            exit;
        }
    }

    /**
     * Handle delete teacher
     */
    public static function handle_delete_teacher() {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_REQUEST['_wpnonce'], 'ica_lms_delete_teacher')) {
            wp_die('Unauthorized');
        }

        $teacher_id = (int) $_REQUEST['teacher_id'];
        ICA_LMS_DB::delete_teacher($teacher_id);

        set_transient('ica_lms_success_message', 'Teacher deleted successfully!', 30);

        wp_redirect(admin_url('admin.php?page=ica-lms-teachers'));
        exit;
    }
}
