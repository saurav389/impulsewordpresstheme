<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ICA LMS User Management Class
 * Handles creation and management of users with different roles
 */
class ICA_LMS_User_Management {
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_user_management_menu'));
        add_action('admin_init', array(__CLASS__, 'handle_user_creation'));
        add_action('wp_ajax_ica_create_user', array(__CLASS__, 'ajax_create_user'));
        add_action('wp_ajax_ica_get_user', array(__CLASS__, 'ajax_get_user'));
        add_action('wp_ajax_ica_update_user', array(__CLASS__, 'ajax_update_user'));
        add_action('wp_ajax_ica_delete_user', array(__CLASS__, 'ajax_delete_user'));
        add_action('wp_ajax_ica_get_users', array(__CLASS__, 'ajax_get_users'));
        
        // Post restrictions for teachers and students - only see their own posts
        add_filter('pre_get_posts', array(__CLASS__, 'restrict_user_posts'));
        add_filter('map_meta_cap', array(__CLASS__, 'restrict_user_edit_caps'), 10, 4);
        
        // Grant post capabilities to teachers and students
        add_filter('user_has_cap', array(__CLASS__, 'grant_post_capabilities'), 10, 4);
        
        // REST API permission for posts - allow teachers/students to access post endpoints
        add_filter('rest_can_access_post_type', array(__CLASS__, 'rest_can_access_post_type'), 10, 2);
        
        // Verify roles on admin load
        add_action('admin_init', array(__CLASS__, 'verify_user_roles'));
    }

    /**
     * Add user management menu to LMS admin
     */
    public static function add_user_management_menu() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Add Users submenu to LMS (Admin only)
        add_submenu_page(
            'ica-lms',
            'Users',
            'Users',
            'manage_options',
            'ica-lms-users',
            array(__CLASS__, 'render_users_page')
        );
    }

    /**
     * Render users management page
     */
    public static function render_users_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $user_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;

        if ($action === 'edit' && $user_id) {
            self::render_edit_user_form($user_id);
        } elseif ($action === 'add') {
            self::render_add_user_form();
        } else {
            self::render_users_list();
        }
    }

    /**
     * Render add user form
     */
    public static function render_add_user_form() {
        ?>
        <div class="wrap">
            <h1>Add New User</h1>
            <form id="add_user_form">
                <table class="form-table">
                    <tr>
                        <th><label for="first_name">First Name *</label></th>
                        <td><input type="text" id="first_name" name="first_name" required style="width: 100%; padding: 8px;"></td>
                    </tr>
                    <tr>
                        <th><label for="last_name">Last Name *</label></th>
                        <td><input type="text" id="last_name" name="last_name" required style="width: 100%; padding: 8px;"></td>
                    </tr>
                    <tr>
                        <th><label for="email">Email *</label></th>
                        <td><input type="email" id="email" name="email" required style="width: 100%; padding: 8px;"></td>
                    </tr>
                    <tr>
                        <th><label for="username">Username *</label></th>
                        <td><input type="text" id="username" name="username" required style="width: 100%; padding: 8px;"></td>
                    </tr>
                    <tr>
                        <th><label for="password">Password *</label></th>
                        <td><input type="password" id="password" name="password" required style="width: 100%; padding: 8px;"></td>
                    </tr>
                    <tr>
                        <th><label for="user_type">User Type *</label></th>
                        <td>
                            <select id="user_type" name="user_type" required style="width: 100%; padding: 8px;">
                                <option value="">-- Select User Type --</option>
                                <option value="admin">Admin</option>
                                <option value="teacher">Teacher</option>
                                <option value="student">Student</option>
                            </select>
                            <p style="color: #666; margin-top: 5px; font-size: 12px;">
                                • <strong>Admin</strong>: Full access to LMS management<br/>
                                • <strong>Teacher</strong>: Can create courses, modules, tests, and manage student progress<br/>
                                • <strong>Student</strong>: Can view courses, resources, and track progress
                            </p>
                        </td>
                    </tr>
                    <tr id="teacher_qualification_row" style="display: none;">
                        <th><label for="teacher_qualification">Teacher Qualification</label></th>
                        <td><input type="text" id="teacher_qualification" name="teacher_qualification" style="width: 100%; padding: 8px;" placeholder="e.g., M.Tech, B.Sc"></td>
                    </tr>
                    <tr id="teacher_department_row" style="display: none;">
                        <th><label for="teacher_department">Teacher Department</label></th>
                        <td><input type="text" id="teacher_department" name="teacher_department" style="width: 100%; padding: 8px;" placeholder="e.g., Engineering, Science"></td>
                    </tr>
                    <tr id="student_phone_row" style="display: none;">
                        <th><label for="student_phone">Phone Number</label></th>
                        <td><input type="tel" id="student_phone" name="student_phone" style="width: 100%; padding: 8px;"></td>
                    </tr>
                </table>
                <p>
                    <button type="button" class="button button-primary" onclick="ica_create_user();">Create User</button>
                    <a href="<?php echo esc_url(add_query_arg('page', 'ica-lms-users', admin_url('admin.php'))); ?>" class="button">Cancel</a>
                </p>
            </form>
        </div>

        <script>
            jQuery(document).ready(function() {
                // Show/hide fields based on user type
                jQuery('#user_type').on('change', function() {
                    const type = jQuery(this).val();
                    jQuery('#teacher_qualification_row, #teacher_department_row, #student_phone_row').hide();
                    
                    if (type === 'teacher') {
                        jQuery('#teacher_qualification_row, #teacher_department_row').show();
                    } else if (type === 'student') {
                        jQuery('#student_phone_row').show();
                    }
                });
            });

            function ica_create_user() {
                const first_name = document.getElementById('first_name').value;
                const last_name = document.getElementById('last_name').value;
                const email = document.getElementById('email').value;
                const username = document.getElementById('username').value;
                const password = document.getElementById('password').value;
                const user_type = document.getElementById('user_type').value;
                const teacher_qualification = document.getElementById('teacher_qualification').value;
                const teacher_department = document.getElementById('teacher_department').value;
                const student_phone = document.getElementById('student_phone').value;

                if (!first_name || !last_name || !email || !username || !password || !user_type) {
                    alert('Please fill in all required fields');
                    return;
                }

                jQuery.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        action: 'ica_create_user',
                        first_name: first_name,
                        last_name: last_name,
                        email: email,
                        username: username,
                        password: password,
                        user_type: user_type,
                        teacher_qualification: teacher_qualification,
                        teacher_department: teacher_department,
                        student_phone: student_phone,
                        nonce: '<?php echo esc_js(wp_create_nonce('ica_create_user')); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('User created successfully!');
                            window.location.href = '<?php echo esc_url(add_query_arg('page', 'ica-lms-users', admin_url('admin.php'))); ?>';
                        } else {
                            alert('Error: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Failed to create user');
                    }
                });
            }
        </script>
        <?php
    }

    /**
     * Render users list
     */
    public static function render_users_list() {
        $users_query = new WP_User_Query(array(
            'role__in' => array(
                ICA_LMS_User_Roles::ADMIN_ROLE,
                ICA_LMS_User_Roles::TEACHER_ROLE,
                ICA_LMS_User_Roles::STUDENT_ROLE
            )
        ));

        $users = $users_query->get_results();
        ?>
        <div class="wrap">
            <h1>LMS Users</h1>
            <a href="<?php echo esc_url(add_query_arg('action', 'add', admin_url('admin.php?page=ica-lms-users'))); ?>" class="button button-primary" style="margin-bottom: 20px;">+ Add New User</a>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>User Type</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)) : ?>
                        <?php foreach ($users as $user) : 
                            $user_type = ICA_LMS_User_Roles::get_user_role_type($user->ID);
                            $user_badge_color = $user_type === 'admin' ? '#dc3545' : ($user_type === 'teacher' ? '#0066cc' : '#28a745');
                        ?>
                            <tr>
                                <td><?php echo esc_html($user->display_name); ?></td>
                                <td><?php echo esc_html($user->user_email); ?></td>
                                <td><?php echo esc_html($user->user_login); ?></td>
                                <td>
                                    <span style="background-color: <?php echo $user_badge_color; ?>; color: white; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold;">
                                        <?php echo ucfirst($user_type); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html(date_i18n('F d, Y', strtotime($user->user_registered))); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(array('action' => 'edit', 'user_id' => $user->ID), admin_url('admin.php?page=ica-lms-users'))); ?>" class="button button-small">Edit</a>
                                    <a href="#" onclick="if(confirm('Are you sure? This action cannot be undone.')) { ica_delete_user(<?php echo $user->ID; ?>); }" class="button button-small button-danger" style="color: #dc3545;">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px;">No LMS users found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <script>
            function ica_delete_user(user_id) {
                jQuery.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        action: 'ica_delete_user',
                        user_id: user_id,
                        nonce: '<?php echo esc_js(wp_create_nonce('ica_delete_user')); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('User deleted successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + response.data);
                        }
                    }
                });
            }
        </script>
        <?php
    }

    /**
     * Render edit user form
     */
    public static function render_edit_user_form($user_id) {
        $user = get_userdata($user_id);
        if (!$user) {
            wp_die('User not found');
        }

        $user_type = ICA_LMS_User_Roles::get_user_role_type($user_id);
        ?>
        <div class="wrap">
            <h1>Edit User: <?php echo esc_html($user->display_name); ?></h1>
            <form id="edit_user_form">
                <input type="hidden" id="user_id" name="user_id" value="<?php echo $user_id; ?>">
                <table class="form-table">
                    <tr>
                        <th><label for="first_name">First Name</label></th>
                        <td><input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($user->first_name); ?>" style="width: 100%; padding: 8px;"></td>
                    </tr>
                    <tr>
                        <th><label for="last_name">Last Name</label></th>
                        <td><input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($user->last_name); ?>" style="width: 100%; padding: 8px;"></td>
                    </tr>
                    <tr>
                        <th><label for="email">Email</label></th>
                        <td><input type="email" id="email" name="email" value="<?php echo esc_attr($user->user_email); ?>" style="width: 100%; padding: 8px;"></td>
                    </tr>
                    <tr>
                        <th><label for="user_type">User Type</label></th>
                        <td>
                            <select id="user_type" name="user_type" style="width: 100%; padding: 8px;">
                                <option value="admin" <?php selected($user_type, 'admin'); ?>>Admin</option>
                                <option value="teacher" <?php selected($user_type, 'teacher'); ?>>Teacher</option>
                                <option value="student" <?php selected($user_type, 'student'); ?>>Student</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <p>
                    <button type="button" class="button button-primary" onclick="ica_update_user(<?php echo $user_id; ?>);">Update User</button>
                    <a href="<?php echo esc_url(add_query_arg('page', 'ica-lms-users', admin_url('admin.php'))); ?>" class="button">Cancel</a>
                </p>
            </form>
        </div>

        <script>
            function ica_update_user(user_id) {
                const first_name = document.getElementById('first_name').value;
                const last_name = document.getElementById('last_name').value;
                const email = document.getElementById('email').value;
                const user_type = document.getElementById('user_type').value;

                jQuery.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        action: 'ica_update_user',
                        user_id: user_id,
                        first_name: first_name,
                        last_name: last_name,
                        email: email,
                        user_type: user_type,
                        nonce: '<?php echo esc_js(wp_create_nonce('ica_update_user')); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('User updated successfully!');
                            window.location.href = '<?php echo esc_url(add_query_arg('page', 'ica-lms-users', admin_url('admin.php'))); ?>';
                        } else {
                            alert('Error: ' + response.data);
                        }
                    }
                });
            }
        </script>
        <?php
    }

    /**
     * Handle user creation
     */
    public static function handle_user_creation() {
        // Handled via AJAX
    }

    /**
     * AJAX: Create new user
     */
    public static function ajax_create_user() {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['nonce'], 'ica_create_user')) {
            wp_send_json_error('Unauthorized');
        }

        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $email = sanitize_email($_POST['email']);
        $username = sanitize_user($_POST['username']);
        $password = $_POST['password']; // Password should not be sanitized
        $user_type = sanitize_key($_POST['user_type']);

        if (empty($first_name) || empty($last_name) || empty($email) || empty($username) || empty($password) || empty($user_type)) {
            wp_send_json_error('Required fields are missing');
        }

        // Validate user type
        $valid_types = array('admin', 'teacher', 'student');
        if (!in_array($user_type, $valid_types)) {
            wp_send_json_error('Invalid user type');
        }

        // Check if username exists
        if (username_exists($username)) {
            wp_send_json_error('Username already exists');
        }

        // Check if email exists
        if (email_exists($email)) {
            wp_send_json_error('Email already exists');
        }

        // Create user
        $user_data = array(
            'user_login' => $username,
            'user_email' => $email,
            'user_pass' => $password,
            'first_name' => $first_name,
            'last_name' => $last_name,
        );

        $user_id = wp_insert_user($user_data);

        if (is_wp_error($user_id)) {
            wp_send_json_error($user_id->get_error_message());
        }

        // Assign role
        $user = new WP_User($user_id);
        
        // Remove any existing LMS roles first
        foreach (array(ICA_LMS_User_Roles::ADMIN_ROLE, ICA_LMS_User_Roles::TEACHER_ROLE, ICA_LMS_User_Roles::STUDENT_ROLE) as $role) {
            $user->remove_role($role);
        }

        // Assign new role
        if ($user_type === 'admin') {
            $user->add_role(ICA_LMS_User_Roles::ADMIN_ROLE);
        } elseif ($user_type === 'teacher') {
            $user->add_role(ICA_LMS_User_Roles::TEACHER_ROLE);
        } elseif ($user_type === 'student') {
            $user->add_role(ICA_LMS_User_Roles::STUDENT_ROLE);
        }

        // Save metadata for roles
        if ($user_type === 'teacher') {
            $teacher_qualification = sanitize_text_field($_POST['teacher_qualification']);
            $teacher_department = sanitize_text_field($_POST['teacher_department']);
            update_user_meta($user_id, 'teacher_qualification', $teacher_qualification);
            update_user_meta($user_id, 'teacher_department', $teacher_department);
        } elseif ($user_type === 'student') {
            $student_phone = sanitize_text_field($_POST['student_phone']);
            update_user_meta($user_id, 'student_phone', $student_phone);
        }

        wp_send_json_success(array(
            'user_id' => $user_id,
            'message' => 'User created successfully'
        ));
    }

    /**
     * AJAX: Get user
     */
    public static function ajax_get_user() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $user_id = (int) $_POST['user_id'];
        $user = get_userdata($user_id);

        if (!$user) {
            wp_send_json_error('User not found');
        }

        wp_send_json_success(array(
            'id' => $user->ID,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->user_email,
            'username' => $user->user_login,
            'user_type' => ICA_LMS_User_Roles::get_user_role_type($user_id),
        ));
    }

    /**
     * AJAX: Update user
     */
    public static function ajax_update_user() {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['nonce'], 'ica_update_user')) {
            wp_send_json_error('Unauthorized');
        }

        $user_id = (int) $_POST['user_id'];
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $email = sanitize_email($_POST['email']);
        $user_type = sanitize_key($_POST['user_type']);

        $user_data = array(
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'user_email' => $email,
        );

        $result = wp_update_user($user_data);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        // Update role
        $user = new WP_User($user_id);
        foreach (array(ICA_LMS_User_Roles::ADMIN_ROLE, ICA_LMS_User_Roles::TEACHER_ROLE, ICA_LMS_User_Roles::STUDENT_ROLE) as $role) {
            $user->remove_role($role);
        }

        if ($user_type === 'admin') {
            $user->add_role(ICA_LMS_User_Roles::ADMIN_ROLE);
        } elseif ($user_type === 'teacher') {
            $user->add_role(ICA_LMS_User_Roles::TEACHER_ROLE);
        } elseif ($user_type === 'student') {
            $user->add_role(ICA_LMS_User_Roles::STUDENT_ROLE);
        }

        wp_send_json_success('User updated successfully');
    }

    /**
     * AJAX: Delete user
     */
    public static function ajax_delete_user() {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['nonce'], 'ica_delete_user')) {
            wp_send_json_error('Unauthorized');
        }

        $user_id = (int) $_POST['user_id'];
        $result = wp_delete_user($user_id);

        if ($result) {
            wp_send_json_success('User deleted successfully');
        } else {
            wp_send_json_error('Failed to delete user');
        }
    }

    /**
     * AJAX: Get users list
     */
    public static function ajax_get_users() {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['nonce'], 'ica_get_users')) {
            wp_send_json_error('Unauthorized');
        }

        $user_type = sanitize_key($_POST['user_type']);
        $valid_types = array('admin', 'teacher', 'student');

        if (!in_array($user_type, $valid_types)) {
            wp_send_json_error('Invalid user type');
        }

        $role_map = array(
            'admin' => ICA_LMS_User_Roles::ADMIN_ROLE,
            'teacher' => ICA_LMS_User_Roles::TEACHER_ROLE,
            'student' => ICA_LMS_User_Roles::STUDENT_ROLE,
        );

        $users_query = new WP_User_Query(array(
            'role' => $role_map[$user_type],
        ));

        $users_data = array();
        foreach ($users_query->get_results() as $user) {
            $users_data[] = array(
                'id' => $user->ID,
                'name' => $user->display_name,
                'email' => $user->user_email,
                'username' => $user->user_login,
            );
        }

        wp_send_json_success($users_data);
    }

    /**
     * Restrict users (teachers/students) to viewing only their own posts in the admin
     * 
     * @param WP_Query $query
     * @return WP_Query
     */
    public static function restrict_user_posts($query) {
        // Only apply on admin and for post type queries
        if (!is_admin() || !$query->is_main_query()) {
            return $query;
        }

        $current_user = wp_get_current_user();
        
        // Check if user is a teacher or student
        $is_teacher = in_array(ICA_LMS_User_Roles::TEACHER_ROLE, $current_user->roles);
        $is_student = in_array(ICA_LMS_User_Roles::STUDENT_ROLE, $current_user->roles);
        
        if (!$is_teacher && !$is_student) {
            return $query;
        }

        // Get the post type being queried
        $post_type = $query->get('post_type');
        
        // Only restrict posts (not pages or custom post types)
        if (empty($post_type) || $post_type === 'post') {
            $query->set('author', $current_user->ID);
        }

        return $query;
    }

    /**
     * Restrict users (teachers/students) from editing others' posts
     * 
     * @param array $caps Capabilities for the user
     * @param string $cap The capability being filtered
     * @param int $user_id The user ID
     * @param array $args Additional arguments
     * @return array Modified capabilities
     */
    public static function restrict_user_edit_caps($caps, $cap, $user_id, $args) {
        // Only apply to edit/delete post capabilities
        if (!in_array($cap, array('edit_post', 'delete_post', 'edit_published_posts', 'delete_published_posts'))) {
            return $caps;
        }

        $current_user = get_userdata($user_id);
        
        // Check if user is a teacher or student
        $is_teacher = in_array(ICA_LMS_User_Roles::TEACHER_ROLE, $current_user->roles);
        $is_student = in_array(ICA_LMS_User_Roles::STUDENT_ROLE, $current_user->roles);
        
        if (!$is_teacher && !$is_student) {
            return $caps;
        }

        // Get the post being checked
        $post_id = isset($args[0]) ? (int)$args[0] : 0;
        if ($post_id <= 0) {
            return $caps;
        }

        $post = get_post($post_id);
        if (!$post) {
            return $caps;
        }

        // Cast post_author to int for comparison
        $post_author = (int)$post->post_author;
        
        // Allow if:
        // 1. Post author is the current user - they can edit their own posts
        // 2. Post author is not yet assigned (new draft) - allow them to edit it
        if ($post_author > 0 && $post_author !== $user_id) {
            // This post belongs to someone else - block it
            $caps[] = 'do_not_allow';
        }

        return $caps;
    }

    /**
     * Grant standard post creation capabilities to Teachers and Students
     * This ensures they can create posts like default WordPress users
     * 
     * @param array $allcaps All capabilities for the user
     * @param array $caps Capabilities being checked
     * @param array $args Additional arguments
     * @param int $user_id The user ID
     * @return array Modified capabilities
     */
    public static function grant_post_capabilities($allcaps, $caps, $args, $user_id) {
        if (!$user_id) {
            return $allcaps;
        }
        
        $user = get_userdata($user_id);
        if (!$user) {
            return $allcaps;
        }
        
        $is_teacher = in_array(ICA_LMS_User_Roles::TEACHER_ROLE, (array)$user->roles);
        $is_student = in_array(ICA_LMS_User_Roles::STUDENT_ROLE, (array)$user->roles);
        
        if (!$is_teacher && !$is_student) {
            return $allcaps;
        }
        
        // Grant all standard post-related capabilities to teachers and students
        // This allows them to create, edit, publish, and delete their own posts
        $allcaps['create_posts'] = true;
        $allcaps['edit_posts'] = true;
        $allcaps['read_posts'] = true;
        $allcaps['delete_posts'] = true;
        $allcaps['publish_posts'] = true;
        $allcaps['edit_published_posts'] = true;
        $allcaps['delete_published_posts'] = true;
        
        return $allcaps;
    }

    /**
     * Grant REST API access to teachers and students for post-related operations
     * This ensures the block editor (Gutenberg) can load posts for these users
     * 
     * @param bool $allowed Whether access is allowed
     * @param object|null $post_type_obj The post type object
     * @return bool Modified access permission
     */
    public static function rest_can_access_post_type($allowed, $post_type_obj) {
        // Only handle 'post' post type
        if (!$post_type_obj || $post_type_obj->name !== 'post') {
            return $allowed;
        }
        
        $current_user = wp_get_current_user();
        if (!$current_user || !$current_user->ID) {
            return $allowed;
        }
        
        $is_teacher = in_array(ICA_LMS_User_Roles::TEACHER_ROLE, (array)$current_user->roles);
        $is_student = in_array(ICA_LMS_User_Roles::STUDENT_ROLE, (array)$current_user->roles);
        
        // Grant access to teachers and students
        if ($is_teacher || $is_student) {
            return true;
        }
        
        return $allowed;
    }

    /**
     * Verify and fix user roles if missing
     * This ensures teachers and students have the proper WordPress roles
     */
    public static function verify_user_roles() {
        $current_user = wp_get_current_user();
        
        // Skip if user is not logged in
        if ($current_user->ID === 0) {
            return;
        }
        
        // Check if user is a teacher in LMS database
        $teacher = ICA_LMS_DB::get_teacher_by_user_id($current_user->ID);
        if ($teacher && !in_array(ICA_LMS_User_Roles::TEACHER_ROLE, $current_user->roles)) {
            $current_user->add_role(ICA_LMS_User_Roles::TEACHER_ROLE);
            error_log("ICA LMS: Added teacher role to user {$current_user->ID}");
        }
        
        // Check if user is a student in LMS database
        $student = ICA_LMS_DB::get_student_by_user_id($current_user->ID);
        if ($student && !in_array(ICA_LMS_User_Roles::STUDENT_ROLE, $current_user->roles)) {
            $current_user->add_role(ICA_LMS_User_Roles::STUDENT_ROLE);
            error_log("ICA LMS: Added student role to user {$current_user->ID}");
        }
    }

}
