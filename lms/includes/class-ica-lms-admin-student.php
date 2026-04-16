<?php
if (!defined('ABSPATH')) {
    exit;
}

class ICA_LMS_Admin_Student {
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        add_action('admin_init', array(__CLASS__, 'handle_form_submission'));
        add_action('wp_ajax_ica_search_courses', array(__CLASS__, 'ajax_search_courses'));
        add_action('wp_ajax_ica_get_course_fee', array(__CLASS__, 'ajax_get_course_fee'));
        add_action('wp_ajax_ica_add_category', array(__CLASS__, 'ajax_add_category'));
        add_action('wp_ajax_ica_get_batches', array(__CLASS__, 'ajax_get_batches'));
        add_action('wp_ajax_ica_add_batch', array(__CLASS__, 'ajax_add_batch'));
        add_action('wp_ajax_ica_edit_batch', array(__CLASS__, 'ajax_edit_batch'));
        add_action('admin_post_ica_lms_delete_student', array(__CLASS__, 'handle_delete_student'));
        add_action('admin_post_ica_lms_download_id_card', array(__CLASS__, 'handle_download_id_card'));
        add_action('wp_ajax_ica_lms_bulk_download_id_cards', array(__CLASS__, 'ajax_bulk_download_id_cards'));
    }

    public static function add_admin_menu() {
        // Show LMS menu to both admins and teachers
        if (!current_user_can('edit_posts')) {
            return;
        }

        $current_user = wp_get_current_user();
        $is_admin = current_user_can('manage_options');
        $is_teacher = in_array(ICA_LMS_User_Roles::TEACHER_ROLE, (array) $current_user->roles);

        // Add main LMS menu
        add_menu_page(
            'LMS',
            'LMS',
            'edit_posts',
            'ica-lms',
            array(__CLASS__, 'render_lms_dashboard'),
            'dashicons-book',
            22
        );

        // Add Dashboard submenu
        add_submenu_page(
            'ica-lms',
            'Dashboard',
            'Dashboard',
            'edit_posts',
            'ica-lms',
            array(__CLASS__, 'render_lms_dashboard')
        );

        // View Students - Only visible to teachers and admins
        add_submenu_page(
            'ica-lms',
            'View Students',
            'View Students',
            'edit_posts',
            'ica-lms-view-students',
            array(__CLASS__, 'render_teacher_students_page')
        );

        // Student Progress - Only visible to teachers and admins
        add_submenu_page(
            'ica-lms',
            'Student Progress',
            'Student Progress',
            'edit_posts',
            'ica-lms-student-progress',
            array(__CLASS__, 'render_student_progress_page')
        );

        // View Course Materials - Teachers can access their course materials
        add_submenu_page(
            'ica-lms',
            'Course Materials',
            'Course Materials',
            'edit_posts',
            'ica-lms-course-materials',
            array(__CLASS__, 'render_course_materials_page')
        );

        // Admin-only menus
        if ($is_admin) {
            add_submenu_page(
                'ica-lms',
                'All Students',
                'All Students',
                'manage_options',
                'ica-lms-students',
                array(__CLASS__, 'render_students_page')
            );

            add_submenu_page(
                'ica-lms',
                'All Courses',
                'All Courses',
                'manage_options',
                'ica-lms-all-courses',
                array(__CLASS__, 'render_all_courses_page')
            );

            add_submenu_page(
                'ica-lms',
                'Fees',
                'Fees',
                'manage_options',
                'ica-lms-fees'
            );

            add_submenu_page(
                'ica-lms',
                'Subjects',
                'Subjects',
                'manage_options',
                'ica-lms-subjects'
            );

            add_submenu_page(
                'ica-lms',
                'Teachers',
                'Teachers',
                'manage_options',
                'ica-lms-teachers'
            );

            add_submenu_page(
                'ica-lms',
                'User Management',
                'User Management',
                'manage_options',
                'ica-lms-users'
            );
        }
    }

    /**
     * Render LMS Dashboard
     */
    public static function render_lms_dashboard() {
        if (!current_user_can('edit_posts')) {
            wp_die('Unauthorized');
        }

        $current_user = wp_get_current_user();
        $is_admin = current_user_can('manage_options');
        $is_teacher = in_array(ICA_LMS_User_Roles::TEACHER_ROLE, (array) $current_user->roles);
        $is_student = in_array(ICA_LMS_User_Roles::STUDENT_ROLE, (array) $current_user->roles);
        ?>
        <div class="wrap">
            <h1>LMS Dashboard</h1>
            
            <?php if ($is_student && !$is_teacher && !$is_admin) : ?>
                <div style="margin-top: 30px; padding: 20px; background: #e8f5e9; border-left: 4px solid #4caf50; border-radius: 5px;">
                    <h2 style="margin-top: 0;">Welcome, <?php echo esc_html($current_user->display_name); ?>!</h2>
                    <p style="font-size: 16px; color: #333;">You are a student. You can access your enrolled courses through the student portal.</p>
                </div>

                <div style="margin-top: 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div style="background: #f5f5f5; padding: 20px; border-radius: 5px; text-align: center;">
                        <h3 style="margin-top: 0;">📚 My Courses</h3>
                        <p>View and access your enrolled courses and course materials through the frontend portal.</p>
                    </div>
                    <div style="background: #f5f5f5; padding: 20px; border-radius: 5px; text-align: center;">
                        <h3 style="margin-top: 0;">📊 My Progress</h3>
                        <p>Track your learning progress and course completion status.</p>
                    </div>
                    <div style="background: #f5f5f5; padding: 20px; border-radius: 5px; text-align: center;">
                        <h3 style="margin-top: 0;">👤 Profile</h3>
                        <p>Update your profile information and view your account details.</p>
                    </div>
                    <div style="background: #f5f5f5; padding: 20px; border-radius: 5px; text-align: center;">
                        <h3 style="margin-top: 0;">💳 Fees</h3>
                        <p>View payment history and fee status.</p>
                    </div>
                </div>

            <?php elseif ($is_teacher && !$is_admin) : ?>
                <div style="margin-top: 30px; padding: 20px; background: #e8f5e9; border-left: 4px solid #4caf50; border-radius: 5px;">
                    <h2 style="margin-top: 0;">Welcome, <?php echo esc_html($current_user->display_name); ?>!</h2>
                    <p style="font-size: 16px; color: #333;">You have access to the following features:</p>
                    <ul style="list-style: none; padding: 0;">
                        <li>📝 <strong>Create & Manage Exams</strong> - Create tests and final exams with questions</li>
                        <li>👥 <strong>View Students</strong> - See students in your courses</li>
                        <li>📊 <strong>Student Progress</strong> - Track student progress in courses and exams</li>
                        <li>📚 <strong>Course Materials</strong> - View and manage course topics</li>
                    </ul>
                </div>

                <div style="margin-top: 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div style="background: #f5f5f5; padding: 20px; border-radius: 5px; text-align: center;">
                        <h3 style="margin-top: 0;">📝 My Exams</h3>
                        <p><a href="?page=ica-lms-exams" class="button button-primary">Manage Exams</a></p>
                    </div>
                    <div style="background: #f5f5f5; padding: 20px; border-radius: 5px; text-align: center;">
                        <h3 style="margin-top: 0;">👥 My Students</h3>
                        <p><a href="?page=ica-lms-view-students" class="button button-primary">View Students</a></p>
                    </div>
                    <div style="background: #f5f5f5; padding: 20px; border-radius: 5px; text-align: center;">
                        <h3 style="margin-top: 0;">📊 Progress</h3>
                        <p><a href="?page=ica-lms-student-progress" class="button button-primary">View Progress</a></p>
                    </div>
                    <div style="background: #f5f5f5; padding: 20px; border-radius: 5px; text-align: center;">
                        <h3 style="margin-top: 0;">📚 Topics</h3>
                        <p><a href="?page=ica-lms-course-materials" class="button button-primary">Manage Topics</a></p>
                    </div>
                </div>

            <?php elseif ($is_admin) : ?>
                <div style="margin-top: 30px; padding: 20px; background: #e3f2fd; border-left: 4px solid #2196f3; border-radius: 5px;">
                    <h2 style="margin-top: 0;">Welcome, Administrator!</h2>
                    <p style="font-size: 16px; color: #333;">You have full access to all LMS features across the system.</p>
                </div>

                <div style="margin-top: 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div style="background: #f5f5f5; padding: 20px; border-radius: 5px; text-align: center;">
                        <h3 style="margin-top: 0;">👥 All Students</h3>
                        <p><a href="?page=ica-lms-students" class="button button-primary">Manage</a></p>
                    </div>
                    <div style="background: #f5f5f5; padding: 20px; border-radius: 5px; text-align: center;">
                        <h3 style="margin-top: 0;">📚 All Courses</h3>
                        <p><a href="?page=ica-lms-all-courses" class="button button-primary">Manage</a></p>
                    </div>
                    <div style="background: #f5f5f5; padding: 20px; border-radius: 5px; text-align: center;">
                        <h3 style="margin-top: 0;">📝 All Exams</h3>
                        <p><a href="?page=ica-lms-exams" class="button button-primary">Manage</a></p>
                    </div>
                    <div style="background: #f5f5f5; padding: 20px; border-radius: 5px; text-align: center;">
                        <h3 style="margin-top: 0;">💰 Fees</h3>
                        <p><a href="?page=ica-lms-fees" class="button button-primary">Manage</a></p>
                    </div>
                    <div style="background: #f5f5f5; padding: 20px; border-radius: 5px; text-align: center;">
                        <h3 style="margin-top: 0;">📖 Subjects</h3>
                        <p><a href="?page=ica-lms-subjects" class="button button-primary">Manage</a></p>
                    </div>
                    <div style="background: #f5f5f5; padding: 20px; border-radius: 5px; text-align: center;">
                        <h3 style="margin-top: 0;">👨‍🏫 Teachers</h3>
                        <p><a href="?page=ica-lms-teachers" class="button button-primary">Manage</a></p>
                    </div>
                    <div style="background: #f5f5f5; padding: 20px; border-radius: 5px; text-align: center;">
                        <h3 style="margin-top: 0;">👤 Users</h3>
                        <p><a href="?page=ica-lms-users" class="button button-primary">Manage</a></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render students management page
     */
    public static function render_students_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $student_id = isset($_GET['student_id']) ? (int) $_GET['student_id'] : 0;

        if ($action === 'edit' && $student_id) {
            self::render_edit_form($student_id);
        } elseif ($action === 'add') {
            self::render_add_form();
        } else {
            self::render_students_list();
        }
    }

    /**
     * Render students list
     */
    public static function render_students_list() {
        $paged = isset($_GET['paged']) ? (int) $_GET['paged'] : 1;
        $limit = 20;
        $offset = ($paged - 1) * $limit;
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $course_filter = isset($_GET['course_id']) ? (int) $_GET['course_id'] : 0;

        $students = ICA_LMS_DB::get_students($limit, $offset, $search, $course_filter);
        $total = ICA_LMS_DB::count_students($search, $course_filter);
        $total_pages = ceil($total / $limit);

        ?>
        <div class="wrap">
            <h1>LMS Students Management
                <a href="<?php echo esc_url(add_query_arg('action', 'add')); ?>" class="page-title-action">Add New Student</a>
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
                    <input type="hidden" name="page" value="ica-lms-students">
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 10px; align-items: end;">
                        <div>
                            <label>Search (Name, RegNo, Mobile, Aadhar):</label>
                            <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Search...">
                        </div>
                        <div>
                            <label>Filter by Course:</label>
                            <select name="course_id">
                                <option value="">All Courses</option>
                                <?php
                                $courses = get_posts(array(
                                    'post_type' => 'courses',
                                    'post_status' => 'publish',
                                    'posts_per_page' => -1,
                                ));
                                foreach ($courses as $course) :
                                    ?>
                                    <option value="<?php echo esc_attr($course->ID); ?>" <?php selected($course_filter, $course->ID); ?>>
                                        <?php echo esc_html($course->post_title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="grid-column: auto;">
                            <button type="submit" class="button button-primary">Search</button>
                        </div>
                        <div style="grid-column: auto;">
                            <a href="?page=ica-lms-students" class="button">Clear</a>
                        </div>
                        <div style="grid-column: auto;">
                            <button type="button" class="button button-secondary" onclick="ica_bulk_download_id_cards(<?php echo esc_attr($course_filter); ?>)" title="Download ID cards for all students">Bulk ID Cards</button>
                        </div>
                    </div>
                </form>
            </div>

            <style>
                .student-photo-circle {
                    width: 50px;
                    height: 50px;
                    border-radius: 50%;
                    background: #e0e0e0;
                    overflow: hidden;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border: 2px solid #667eea;
                }
                .student-photo-circle img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }
                .student-photo-circle-empty {
                    width: 50px;
                    height: 50px;
                    border-radius: 50%;
                    background: #f0f0f0;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border: 2px solid #ddd;
                    color: #999;
                    font-size: 12px;
                    font-weight: bold;
                }
            </style>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 80px;">Photo</th>
                        <th>RegNo</th>
                        <th>Name</th>
                        <th>Course</th>
                        <th>Roll No</th>
                        <th>Mobile</th>
                        <th>Aadhar</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)) : ?>
                        <tr>
                            <td colspan="9" style="text-align: center;">No students found.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($students as $student) : ?>
                            <?php
                            $course = get_post($student['course_id']);
                            $course_name = $course ? $course->post_title : 'N/A';
                            ?>
                            <tr>
                                <td>
                                    <?php if (!empty($student['student_photo_url'])) : ?>
                                        <div class="student-photo-circle">
                                            <img src="<?php echo esc_url($student['student_photo_url']); ?>" alt="Student Photo" title="<?php echo esc_attr($student['name']); ?>">
                                        </div>
                                    <?php else : ?>
                                        <div class="student-photo-circle-empty">No Photo</div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo esc_html($student['reg_no']); ?></strong></td>
                                <td><?php echo esc_html($student['name']); ?></td>
                                <td><?php echo esc_html($course_name); ?></td>
                                <td><?php echo esc_html($student['roll_no']); ?></td>
                                <td><?php echo esc_html($student['mobile_no']); ?></td>
                                <td><?php echo esc_html(substr($student['aadhar_no'], -4) ?: 'N/A'); ?></td>
                                <td>
                                    <span class="badge" style="padding: 3px 8px; border-radius: 3px; background: <?php echo $student['status'] === 'active' ? '#28a745' : '#dc3545'; ?>; color: white;">
                                        <?php echo esc_html(ucfirst($student['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(array('action' => 'edit', 'student_id' => $student['id']))); ?>" class="button button-small">Edit</a>
                                    <a href="<?php echo esc_url(admin_url('admin-post.php?action=ica_lms_download_id_card&student_id=' . $student['id'] . '&nonce=' . wp_create_nonce('ica_lms_id_card_' . $student['id']))); ?>" class="button button-small" title="Download ID Card">ID Card</a>
                                    <?php
                                    $delete_url = wp_nonce_url(
                                        admin_url('admin-post.php?action=ica_lms_delete_student&student_id=' . $student['id']),
                                        'ica_lms_delete_student_' . $student['id']
                                    );
                                    ?>
                                    <a href="<?php echo esc_url($delete_url); ?>" class="button button-small" onclick="return confirm('Are you sure?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="tablenav bottom">
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
                    <small>Total: <?php echo esc_html($total); ?> students</small>
                </div>
            </div>
        </div>

        <script>
            function ica_bulk_download_id_cards(courseFilter) {
                // Show loading message
                alert('Preparing ID cards... This may take a moment.');
                
                jQuery.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'ica_lms_bulk_download_id_cards',
                        course_id: courseFilter,
                        nonce: '<?php echo esc_js(wp_create_nonce('ica_lms_bulk_download_id_cards')); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Download ZIP file
                            var link = document.createElement('a');
                            link.href = response.data.zip_url;
                            link.download = response.data.filename;
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                            alert('ID cards downloaded successfully!');
                        } else {
                            alert('Error: ' + (response.data || 'Unknown error'));
                        }
                    },
                    error: function() {
                        alert('Error downloading ID cards. Please try again.');
                    }
                });
            }
        </script>
        <?php
    }

    /**
     * Render add student form
     */
    public static function render_add_form() {
        $categories = ICA_LMS_DB::get_categories();
        ?>
        <div class="wrap">
            <h1>Add New Student</h1>
            <form method="post" enctype="multipart/form-data" style="max-width: 900px;">
                <?php wp_nonce_field('ica_lms_add_student'); ?>
                <input type="hidden" name="action" value="ica_lms_add_student">

                <table class="form-table">
                    <tr>
                        <th><label>Course *</label></th>
                        <td>
                            <select name="course_id" id="course_search" required style="width: 100%; padding: 8px;">
                                <option value="">-- Select Course --</option>
                                <?php
                                $courses = get_posts(array(
                                    'post_type' => 'courses',
                                    'post_status' => 'publish',
                                    'posts_per_page' => -1,
                                ));
                                foreach ($courses as $course) :
                                    ?>
                                    <option value="<?php echo esc_attr($course->ID); ?>">
                                        <?php echo esc_html($course->post_title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th><label>Batch *</label></th>
                        <td style="display: flex; gap: 10px; align-items: center;">
                            <select name="batch_id" id="batch_select" required style="flex: 1; padding: 8px;">
                                <option value="">-- Select Active Batch --</option>
                            </select>
                            <button type="button" class="button" onclick="jQuery('#add_batch_modal').show();" style="white-space: nowrap;">+ New</button>
                            <button type="button" class="button" id="edit_batch_btn" onclick="ica_open_edit_batch(parseInt(document.getElementById('batch_select').value));" style="white-space: nowrap;" disabled>✎ Edit</button>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2" style="padding: 0; border-top: none;">
                            <small style="color: #666; display: block; margin-top: -8px; padding-left: 150px;">Only active batches are shown. Roll numbers start from 1 for each batch.</small>
                            <small id="batch_capacity_info" style="color: #999; display: none; block; padding-left: 150px;"></small>
                        </td>
                    </tr>

                    <tr>
                        <th><label>Name *</label></th>
                        <td><input type="text" name="name" required style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Father Name</label></th>
                        <td><input type="text" name="father_name" style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Mother Name</label></th>
                        <td><input type="text" name="mother_name" style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Date of Birth</label></th>
                        <td><input type="date" name="date_of_birth" style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Gender</label></th>
                        <td>
                            <select name="gender" style="width: 100%; padding: 8px;">
                                <option value="">Select</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th><label>Category</label></th>
                        <td style="display: flex; gap: 10px; align-items: center;">
                            <select name="category_id" id="category_select" style="flex: 1; padding: 8px;">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat) : ?>
                                    <option value="<?php echo esc_attr($cat['id']); ?>">
                                        <?php echo esc_html($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="button" onclick="jQuery('#add_category_modal').show();">+</button>
                        </td>
                    </tr>

                    <tr>
                        <th><label>Qualification</label></th>
                        <td><input type="text" name="qualification" style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Mobile No *</label></th>
                        <td><input type="tel" name="mobile_no" required style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Aadhar No</label></th>
                        <td><input type="text" name="aadhar_no" style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Address</label></th>
                        <td><textarea name="address" style="width: 100%; padding: 8px; height: 100px;"></textarea></td>
                    </tr>

                    <tr>
                        <th><label>Upload Student Photo</label></th>
                        <td><input type="file" name="student_photo" accept="image/*"></td>
                    </tr>

                    <tr>
                        <th><label>Upload Student Signature</label></th>
                        <td><input type="file" name="student_signature" accept="image/*"></td>
                    </tr>

                    <tr>
                        <th><label>Upload Aadhar Photo</label></th>
                        <td><input type="file" name="aadhar_photo" accept="image/*"></td>
                    </tr>

                    <tr>
                        <th><label>Upload Qualification Certificate</label></th>
                        <td><input type="file" name="qualification_cert" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"></td>
                    </tr>

                    <tr>
                        <th><label>Course Fee *</label></th>
                        <td>
                            <input type="hidden" name="original_course_fee" id="original_course_fee" value="0">
                            <input type="number" name="fee_amount" id="course_fee_display" step="0.01" min="0" value="0" required style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Discount Amount</label></th>
                        <td><input type="number" name="discount_amount" id="discount_amount" step="0.01" min="0" value="0" style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Fee after Discount</label></th>
                        <td><input type="number" id="final_fee_display" step="0.01" readonly style="width: 100%; padding: 8px; background: #f5f5f5; font-weight: bold;"></td>
                    </tr>

                    <tr>
                        <th><label>Fee Payment Type *</label></th>
                        <td>
                            <select name="fee_type" id="fee_type_select" required style="width: 100%; padding: 8px;">
                                <option value="one_time">One Time Payment</option>
                                <option value="installment">Installment</option>
                            </select>
                        </td>
                    </tr>

                    <tr id="installment_count_row" style="display: none;">
                        <th><label>Number of Installments *</label></th>
                        <td>
                            <select name="installment_count" id="installment_count_select" style="width: 100%; padding: 8px;">
                                <option value="">-- Select Number --</option>
                                <?php for ($i = 1; $i <= 12; $i++) : ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i . ' Installment' . ($i > 1 ? 's' : ''); ?></option>
                                <?php endfor; ?>
                            </select>
                        </td>
                    </tr>
                </table>

                <p>
                    <button type="submit" class="button button-primary" style="padding: 10px 20px;">Add Student</button>
                    <a href="?page=ica-lms-students" class="button">Cancel</a>
                </p>
            </form>
        </div>

        <!-- Add Category Modal -->
        <div id="add_category_modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999;">
            <div style="background: white; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); padding: 20px; border-radius: 5px; width: 400px;">
                <h2>Add New Category</h2>
                <form id="add_category_form">
                    <p>
                        <label>Category Name *</label><br>
                        <input type="text" id="new_category_name" placeholder="e.g., SC-OBC" style="width: 100%; padding: 8px;">
                    </p>
                    <p>
                        <label>Description</label><br>
                        <textarea id="new_category_desc" style="width: 100%; padding: 8px; height: 80px;"></textarea>
                    </p>
                    <div style="text-align: right; gap: 10px;">
                        <button type="button" class="button" onclick="jQuery('#add_category_modal').hide();">Cancel</button>
                        <button type="button" class="button button-primary" onclick="ica_add_category();">Add Category</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function ica_add_category() {
                const name = document.getElementById('new_category_name').value;
                const desc = document.getElementById('new_category_desc').value;

                if (!name) {
                    alert('Please enter category name');
                    return;
                }

                jQuery.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'ica_add_category',
                        name: name,
                        description: desc,
                        nonce: '<?php echo esc_js(wp_create_nonce('ica_add_category')); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            jQuery('#category_select').append(
                                jQuery('<option></option>').attr('value', response.data.id).text(response.data.name)
                            );
                            jQuery('#category_select').val(response.data.id);
                            jQuery('#add_category_modal').hide();
                            jQuery('#new_category_name').val('');
                            jQuery('#new_category_desc').val('');
                        } else {
                            alert('Error: ' + response.data);
                        }
                    }
                });
            }

            function ica_load_batches() {
                const course_id = document.getElementById('course_search').value;
                if (!course_id) {
                    jQuery('#batch_select').html('<option value="">-- Select Batch --</option>');
                    return;
                }

                jQuery.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'ica_get_batches',
                        course_id: course_id,
                        nonce: '<?php echo esc_js(wp_create_nonce('ica_get_batches')); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            let html = '<option value="">-- Select Batch --</option>';
                            response.data.forEach(batch => {
                                html += '<option value="' + batch.id + '">' + batch.batch_name + ' [' + batch.total_students + ' students]</option>';
                            });
                            jQuery('#batch_select').html(html);
                            jQuery('#edit_batch_btn').prop('disabled', true);
                        }
                    }
                });
            }

            // Enable edit button when batch is selected
            jQuery(document).on('change', '#batch_select', function() {
                jQuery('#edit_batch_btn').prop('disabled', !this.value);
            });

            function ica_add_batch() {
                const batch_name = document.getElementById('new_batch_name').value;
                const course_id = document.getElementById('course_search').value;
                const total_students = document.getElementById('new_batch_total_students').value;
                const batch_start_date = document.getElementById('new_batch_start_date').value;
                const batch_end_date = document.getElementById('new_batch_end_date').value;
                const description = document.getElementById('new_batch_desc').value;

                if (!batch_name) {
                    alert('Please enter batch name');
                    return;
                }

                if (!course_id) {
                    alert('Please select a course first');
                    return;
                }

                if (!total_students || total_students <= 0) {
                    alert('Please enter total students (must be greater than 0)');
                    return;
                }

                jQuery.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'ica_add_batch',
                        batch_name: batch_name,
                        course_id: course_id,
                        total_students: total_students,
                        batch_start_date: batch_start_date,
                        batch_end_date: batch_end_date,
                        description: description,
                        nonce: '<?php echo esc_js(wp_create_nonce('ica_add_batch')); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            jQuery('#batch_select').append(
                                jQuery('<option></option>').attr('value', response.data.id).text(response.data.batch_name)
                            );
                            jQuery('#batch_select').val(response.data.id);
                            jQuery('#add_batch_modal').hide();
                            jQuery('#new_batch_name').val('');
                            jQuery('#new_batch_total_students').val('');
                            jQuery('#new_batch_start_date').val('');
                            jQuery('#new_batch_end_date').val('');
                            jQuery('#new_batch_desc').val('');
                            alert('Batch created successfully!');
                        } else {
                            alert('Error: ' + response.data);
                        }
                    }
                });
            }

            function ica_open_edit_batch(batch_id) {
                if (!batch_id) {
                    alert('Please select a batch first');
                    return;
                }

                const course_id = document.getElementById('course_search').value;
                if (!course_id) {
                    alert('Please select a course first');
                    return;
                }

                jQuery.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'ica_get_batches',
                        course_id: course_id,
                        nonce: '<?php echo esc_js(wp_create_nonce('ica_get_batches')); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            const batch = response.data.find(b => parseInt(b.id) === batch_id);
                            if (batch) {
                                document.getElementById('edit_batch_id').value = batch.id;
                                document.getElementById('edit_batch_name').value = batch.batch_name;
                                document.getElementById('edit_batch_total_students').value = batch.total_students;
                                document.getElementById('edit_batch_start_date').value = batch.batch_start_date || '';
                                document.getElementById('edit_batch_end_date').value = batch.batch_end_date || '';
                                document.getElementById('edit_batch_desc').value = batch.description || '';
                                document.getElementById('edit_batch_status').value = batch.status || 'active';
                                jQuery('#edit_batch_modal').show();
                            } else {
                                alert('Batch not found');
                            }
                        } else {
                            alert('Error loading batch data');
                        }
                    }
                });
            }

            function ica_edit_batch() {
                const batch_id = document.getElementById('edit_batch_id').value;
                const batch_name = document.getElementById('edit_batch_name').value;
                const total_students = document.getElementById('edit_batch_total_students').value;
                const batch_start_date = document.getElementById('edit_batch_start_date').value;
                const batch_end_date = document.getElementById('edit_batch_end_date').value;
                const description = document.getElementById('edit_batch_desc').value;
                const status = document.getElementById('edit_batch_status').value;

                if (!batch_id) {
                    alert('Batch ID is missing');
                    return;
                }
                if (!batch_name) {
                    alert('Please enter batch name');
                    return;
                }
                if (!total_students || total_students <= 0) {
                    alert('Please enter total students (must be greater than 0)');
                    return;
                }

                jQuery.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'ica_edit_batch',
                        batch_id: batch_id,
                        batch_name: batch_name,
                        total_students: total_students,
                        batch_start_date: batch_start_date,
                        batch_end_date: batch_end_date,
                        description: description,
                        status: status,
                        nonce: '<?php echo esc_js(wp_create_nonce('ica_edit_batch')); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            jQuery('#edit_batch_modal').hide();
                            ica_load_batches();
                            alert('Batch updated successfully!');
                        } else {
                            alert('Error: ' + response.data);
                        }
                    }
                });
            }

            // Load batches when course is selected
            document.getElementById('course_search').addEventListener('change', ica_load_batches);

            // Load course fee when course is selected
            document.getElementById('course_search').addEventListener('change', function() {
                const course_id = this.value;
                if (!course_id) {
                    document.getElementById('course_fee_display').value = '';
                    document.getElementById('original_course_fee').value = '0';
                    document.getElementById('final_fee_display').value = '';
                    document.getElementById('discount_amount').value = '0';
                    return;
                }

                jQuery.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'ica_get_course_fee',
                        course_id: course_id,
                        nonce: '<?php echo esc_js(wp_create_nonce('ica_get_course_fee')); ?>'
                    },
                    success: function(response) {
                        console.log('Course fee response:', response);
                        if (response.success && response.data) {
                            const fee = parseFloat(response.data.amount) || 0;
                            document.getElementById('course_fee_display').value = fee.toFixed(2);
                            document.getElementById('original_course_fee').value = fee.toFixed(2);
                            // Don't reset discount - preserve user's entry
                            updateFinalFee();
                        } else {
                            console.error('Invalid response structure:', response);
                            alert('Could not load course fee. Please refresh and try again.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error loading course fee:', status, error, xhr.responseText);
                        alert('Error loading course fee: ' + error);
                    }
                });
            });

            // Update final fee when discount is changed
            document.getElementById('discount_amount').addEventListener('change', updateFinalFee);
            document.getElementById('discount_amount').addEventListener('keyup', updateFinalFee);
            
            // Also update final fee when course fee is manually changed
            document.getElementById('course_fee_display').addEventListener('change', updateFinalFee);
            document.getElementById('course_fee_display').addEventListener('keyup', updateFinalFee);

            function updateFinalFee() {
                const courseFee = parseFloat(document.getElementById('course_fee_display').value) || 0;
                const discount = parseFloat(document.getElementById('discount_amount').value) || 0;
                const finalFee = Math.max(0, courseFee - discount);
                document.getElementById('final_fee_display').value = finalFee.toFixed(2);
            }

            // Toggle installment count field based on fee type
            function toggleInstallmentField() {
                const feeType = document.getElementById('fee_type_select').value;
                const installmentRow = document.getElementById('installment_count_row');
                
                if (feeType === 'installment') {
                    installmentRow.style.display = 'table-row';
                    document.getElementById('installment_count_select').required = true;
                } else {
                    installmentRow.style.display = 'none';
                    document.getElementById('installment_count_select').required = false;
                }
            }

            // Add event listener to fee type field
            if (document.getElementById('fee_type_select')) {
                document.getElementById('fee_type_select').addEventListener('change', toggleInstallmentField);
            }
        </script>

        <!-- Add Batch Modal -->
        <div id="add_batch_modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999;">
            <div style="background: white; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); padding: 20px; border-radius: 5px; width: 500px; max-height: 80vh; overflow-y: auto;">
                <h2>Add New Batch</h2>
                <form id="add_batch_form">
                    <p>
                        <label>Batch Name *</label><br>
                        <input type="text" id="new_batch_name" placeholder="e.g., Batch 2026 Spring" style="width: 100%; padding: 8px;" required>
                    </p>
                    <p>
                        <label>Total Students *</label><br>
                        <input type="number" id="new_batch_total_students" placeholder="e.g., 30" style="width: 100%; padding: 8px;" min="1" required>
                    </p>
                    <p>
                        <label>Batch Start Date</label><br>
                        <input type="date" id="new_batch_start_date" style="width: 100%; padding: 8px;">
                    </p>
                    <p>
                        <label>Batch End Date</label><br>
                        <input type="date" id="new_batch_end_date" style="width: 100%; padding: 8px;">
                    </p>
                    <p>
                        <label>Description</label><br>
                        <textarea id="new_batch_desc" style="width: 100%; padding: 8px; height: 80px; box-sizing: border-box;"></textarea>
                    </p>
                    <div style="text-align: right; gap: 10px;">
                        <button type="button" class="button" onclick="jQuery('#add_batch_modal').hide();">Cancel</button>
                        <button type="button" class="button button-primary" onclick="ica_add_batch();">Add Batch</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Batch Modal -->
        <div id="edit_batch_modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999;">
            <div style="background: white; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); padding: 20px; border-radius: 5px; width: 500px; max-height: 80vh; overflow-y: auto;">
                <h2>Edit Batch</h2>
                <form id="edit_batch_form">
                    <input type="hidden" id="edit_batch_id">
                    <p>
                        <label>Batch Name *</label><br>
                        <input type="text" id="edit_batch_name" placeholder="e.g., Batch 2026 Spring" style="width: 100%; padding: 8px;" required>
                    </p>
                    <p>
                        <label>Total Students *</label><br>
                        <input type="number" id="edit_batch_total_students" placeholder="e.g., 30" style="width: 100%; padding: 8px;" min="1" required>
                    </p>
                    <p>
                        <label>Batch Start Date</label><br>
                        <input type="date" id="edit_batch_start_date" style="width: 100%; padding: 8px;">
                    </p>
                    <p>
                        <label>Batch End Date</label><br>
                        <input type="date" id="edit_batch_end_date" style="width: 100%; padding: 8px;">
                    </p>
                    <p>
                        <label>Description</label><br>
                        <textarea id="edit_batch_desc" style="width: 100%; padding: 8px; height: 80px; box-sizing: border-box;"></textarea>
                    </p>
                    <p>
                        <label>Status *</label><br>
                        <select id="edit_batch_status" style="width: 100%; padding: 8px;" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="completed">Completed</option>
                        </select>
                    </p>
                    <div style="text-align: right; gap: 10px;">
                        <button type="button" class="button" onclick="jQuery('#edit_batch_modal').hide();">Cancel</button>
                        <button type="button" class="button button-primary" onclick="ica_edit_batch();">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Render edit student form
     */
    public static function render_edit_form($student_id) {
        $student = ICA_LMS_DB::get_student($student_id);
        if (!$student) {
            wp_die('Student not found');
        }

        $categories = ICA_LMS_DB::get_categories();
        ?>
        <style>
            .edit-form-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 20px;
            }
            .edit-form-header h1 {
                margin: 0;
            }
            .student-photo-large-rectangular {
                width: 150px;
                height: 180px;
                border-radius: 8px;
                background: #f0f0f0;
                overflow: hidden;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 3px solid #667eea;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
            .student-photo-large-rectangular img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            .student-photo-placeholder-large {
                width: 100%;
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                font-weight: bold;
                font-size: 14px;
                text-align: center;
                padding: 10px;
                box-sizing: border-box;
            }
        </style>
        <div class="wrap">
            <div class="edit-form-header">
                <h1>Edit Student</h1>
                <div class="student-photo-large-rectangular">
                    <?php if (!empty($student['student_photo_url'])) : ?>
                        <img src="<?php echo esc_url($student['student_photo_url']); ?>" alt="Student Photo">
                    <?php else : ?>
                        <div class="student-photo-placeholder-large"><?php echo esc_html(substr($student['name'], 0, 2)); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <form method="post" enctype="multipart/form-data" style="max-width: 900px;">
                <?php wp_nonce_field('ica_lms_edit_student'); ?>
                <input type="hidden" name="action" value="ica_lms_edit_student">
                <input type="hidden" name="student_id" value="<?php echo esc_attr($student_id); ?>">

                <table class="form-table">
                    <tr>
                        <th><label>RegNo</label></th>
                        <td>
                            <strong><?php echo esc_html($student['reg_no']); ?></strong>
                            <input type="hidden" name="reg_no" value="<?php echo esc_attr($student['reg_no']); ?>">
                        </td>
                    </tr>

                    <tr>
                        <th><label>Roll No</label></th>
                        <td>
                            <strong><?php echo esc_html($student['roll_no']); ?></strong>
                        </td>
                    </tr>

                    <tr>
                        <th><label>Course</label></th>
                        <td>
                            <?php
                            $course = get_post($student['course_id']);
                            echo esc_html($course ? $course->post_title : 'N/A');
                            ?>
                        </td>
                    </tr>

                    <tr>
                        <th><label>Name *</label></th>
                        <td><input type="text" name="name" value="<?php echo esc_attr($student['name']); ?>" required style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Father Name</label></th>
                        <td><input type="text" name="father_name" value="<?php echo esc_attr($student['father_name']); ?>" style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Mother Name</label></th>
                        <td><input type="text" name="mother_name" value="<?php echo esc_attr($student['mother_name']); ?>" style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Date of Birth</label></th>
                        <td><input type="date" name="date_of_birth" value="<?php echo esc_attr($student['date_of_birth']); ?>" style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Gender</label></th>
                        <td>
                            <select name="gender" style="width: 100%; padding: 8px;">
                                <option value="">Select</option>
                                <option value="Male" <?php selected($student['gender'], 'Male'); ?>>Male</option>
                                <option value="Female" <?php selected($student['gender'], 'Female'); ?>>Female</option>
                                <option value="Other" <?php selected($student['gender'], 'Other'); ?>>Other</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th><label>Category</label></th>
                        <td>
                            <select name="category_id" style="width: 100%; padding: 8px;">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat) : ?>
                                    <option value="<?php echo esc_attr($cat['id']); ?>" <?php selected($student['category_id'], $cat['id']); ?>>
                                        <?php echo esc_html($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th><label>Qualification</label></th>
                        <td><input type="text" name="qualification" value="<?php echo esc_attr($student['qualification']); ?>" style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Mobile No *</label></th>
                        <td><input type="tel" name="mobile_no" value="<?php echo esc_attr($student['mobile_no']); ?>" required style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Aadhar No</label></th>
                        <td><input type="text" name="aadhar_no" value="<?php echo esc_attr($student['aadhar_no']); ?>" style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Address</label></th>
                        <td><textarea name="address" style="width: 100%; padding: 8px; height: 100px;"><?php echo esc_textarea($student['address']); ?></textarea></td>
                    </tr>

                    <tr>
                        <th><label>Upload Student Photo</label></th>
                        <td>
                            <input type="file" name="student_photo" accept="image/*">
                            <?php if (!empty($student['student_photo_url'])) : ?>
                                <div style="margin-top: 8px;">
                                    <small>Current: <a href="<?php echo esc_url($student['student_photo_url']); ?>" target="_blank">View</a></small>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th><label>Upload Student Signature</label></th>
                        <td>
                            <input type="file" name="student_signature" accept="image/*">
                            <?php if (!empty($student['student_signature_url'])) : ?>
                                <div style="margin-top: 8px;">
                                    <small>Current: <a href="<?php echo esc_url($student['student_signature_url']); ?>" target="_blank">View</a></small>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th><label>Upload Aadhar Photo</label></th>
                        <td>
                            <input type="file" name="aadhar_photo" accept="image/*">
                            <?php if (!empty($student['aadhar_photo_url'])) : ?>
                                <div style="margin-top: 8px;">
                                    <small>Current: <a href="<?php echo esc_url($student['aadhar_photo_url']); ?>" target="_blank">View</a></small>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th><label>Upload Qualification Certificate</label></th>
                        <td>
                            <input type="file" name="qualification_cert" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <?php if (!empty($student['qualification_cert_url'])) : ?>
                                <div style="margin-top: 8px;">
                                    <small>Current: <a href="<?php echo esc_url($student['qualification_cert_url']); ?>" target="_blank">View</a></small>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th><label>Course Fee</label></th>
                        <td><input type="number" name="fee_amount" id="course_fee_edit" value="<?php echo esc_attr($student['fee_amount'] + $student['discount_amount']); ?>" step="0.01" min="0" style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Discount Amount</label></th>
                        <td><input type="number" name="discount_amount" id="discount_amount_edit" value="<?php echo esc_attr($student['discount_amount']); ?>" step="0.01" min="0" style="width: 100%; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <th><label>Fee after Discount</label></th>
                        <td><input type="number" id="final_fee_edit" value="<?php echo esc_attr($student['fee_amount']); ?>" step="0.01" readonly style="width: 100%; padding: 8px; background: #f5f5f5; font-weight: bold;"></td>
                    </tr>

                    <tr>
                        <th><label>Fee Payment Type</label></th>
                        <td>
                            <select name="fee_type" id="edit_fee_type_select" style="width: 100%; padding: 8px;">
                                <option value="one_time" <?php selected($student['fee_type'], 'one_time'); ?>>One Time Payment</option>
                                <option value="installment" <?php selected($student['fee_type'], 'installment'); ?>>Installment</option>
                            </select>
                        </td>
                    </tr>

                    <tr id="edit_installment_count_row" style="display: <?php echo ($student['fee_type'] === 'installment') ? 'table-row' : 'none'; ?>;">
                        <th><label>Number of Installments *</label></th>
                        <td>
                            <select name="installment_count" id="edit_installment_count_select" style="width: 100%; padding: 8px;">
                                <option value="">-- Select Number --</option>
                                <?php for ($i = 1; $i <= 12; $i++) : ?>
                                    <option value="<?php echo $i; ?>" <?php selected($student['installment_count'] ?? 1, $i); ?>><?php echo $i . ' Installment' . ($i > 1 ? 's' : ''); ?></option>
                                <?php endfor; ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th><label>Fee Status</label></th>
                        <td>
                            <select name="fee_status" style="width: 100%; padding: 8px;">
                                <option value="pending" <?php selected($student['fee_status'], 'pending'); ?>>Pending</option>
                                <option value="submitted" <?php selected($student['fee_status'], 'submitted'); ?>>Submitted</option>
                                <option value="approved" <?php selected($student['fee_status'], 'approved'); ?>>Approved</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th><label>Status</label></th>
                        <td>
                            <select name="status" style="width: 100%; padding: 8px;">
                                <option value="active" <?php selected($student['status'], 'active'); ?>>Active</option>
                                <option value="inactive" <?php selected($student['status'], 'inactive'); ?>>Inactive</option>
                                <option value="completed" <?php selected($student['status'], 'completed'); ?>>Completed</option>
                            </select>
                        </td>
                    </tr>
                </table>

                <p>
                    <button type="submit" class="button button-primary" style="padding: 10px 20px;">Update Student</button>
                    <a href="?page=ica-lms-students" class="button">Cancel</a>
                </p>
            </form>

            <script>
                // Toggle installment count field for edit form
                function toggleEditInstallmentField() {
                    const feeType = document.getElementById('edit_fee_type_select').value;
                    const installmentRow = document.getElementById('edit_installment_count_row');
                    
                    if (feeType === 'installment') {
                        installmentRow.style.display = 'table-row';
                        document.getElementById('edit_installment_count_select').required = true;
                    } else {
                        installmentRow.style.display = 'none';
                        document.getElementById('edit_installment_count_select').required = false;
                    }
                }

                // Update final fee when discount is changed (edit form)
                function updateEditFinalFee() {
                    const courseFee = parseFloat(document.getElementById('course_fee_edit').value) || 0;
                    const discount = parseFloat(document.getElementById('discount_amount_edit').value) || 0;
                    const finalFee = Math.max(0, courseFee - discount);
                    document.getElementById('final_fee_edit').value = finalFee.toFixed(2);
                }

                // Add event listeners to edit fee type field
                if (document.getElementById('edit_fee_type_select')) {
                    document.getElementById('edit_fee_type_select').addEventListener('change', toggleEditInstallmentField);
                }

                // Add event listeners for discount amount and course fee
                if (document.getElementById('discount_amount_edit')) {
                    document.getElementById('discount_amount_edit').addEventListener('change', updateEditFinalFee);
                    document.getElementById('discount_amount_edit').addEventListener('keyup', updateEditFinalFee);
                }
                
                // Also update when course fee is manually changed
                if (document.getElementById('course_fee_edit')) {
                    document.getElementById('course_fee_edit').addEventListener('change', updateEditFinalFee);
                    document.getElementById('course_fee_edit').addEventListener('keyup', updateEditFinalFee);
                }
            </script>
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

        if ($action === 'ica_lms_add_student') {
            if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['_wpnonce'], 'ica_lms_add_student')) {
                wp_die('Unauthorized');
            }

            // Get the ORIGINAL course fee (before discount)
            $course_fee = (float) ($_POST['fee_amount'] ?? 0);
            $discount_amount = (float) ($_POST['discount_amount'] ?? 0);
            $final_fee = max(0, $course_fee - $discount_amount);
            
            $data = array(
                'name' => $_POST['name'] ?? '',
                'father_name' => $_POST['father_name'] ?? '',
                'mother_name' => $_POST['mother_name'] ?? '',
                'date_of_birth' => $_POST['date_of_birth'] ?? '',
                'gender' => $_POST['gender'] ?? '',
                'category_id' => $_POST['category_id'] ?? 0,
                'qualification' => $_POST['qualification'] ?? '',
                'mobile_no' => $_POST['mobile_no'] ?? '',
                'aadhar_no' => $_POST['aadhar_no'] ?? '',
                'address' => $_POST['address'] ?? '',
                'course_id' => (int) ($_POST['course_id'] ?? 0),
                'batch_id' => (int) ($_POST['batch_id'] ?? 0),
                'fee_amount' => $final_fee,
                'discount_amount' => $discount_amount,
                'fee_type' => $_POST['fee_type'] ?? 'one_time',
                'installment_count' => (int) ($_POST['installment_count'] ?? 1),
            );

            // Validate required fields
            if (empty($data['name']) || empty($data['mobile_no']) || empty($data['course_id']) || empty($data['batch_id'])) {
                echo '<div class="notice notice-error"><p>Please fill all required fields: Name, Mobile No, Course, and Batch.</p></div>';
                return;
            }

            // Handle file uploads
            if (!function_exists('wp_handle_upload')) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }

            if (!empty($_FILES['student_photo']['name'])) {
                $upload = wp_handle_upload($_FILES['student_photo'], array('test_form' => false));
                if ($upload && !isset($upload['error'])) {
                    $data['student_photo_url'] = $upload['url'];
                }
            }

            if (!empty($_FILES['student_signature']['name'])) {
                $upload = wp_handle_upload($_FILES['student_signature'], array('test_form' => false));
                if ($upload && !isset($upload['error'])) {
                    $data['student_signature_url'] = $upload['url'];
                }
            }

            if (!empty($_FILES['aadhar_photo']['name'])) {
                $upload = wp_handle_upload($_FILES['aadhar_photo'], array('test_form' => false));
                if ($upload && !isset($upload['error'])) {
                    $data['aadhar_photo_url'] = $upload['url'];
                }
            }

            if (!empty($_FILES['qualification_cert']['name'])) {
                $upload = wp_handle_upload($_FILES['qualification_cert'], array('test_form' => false));
                if ($upload && !isset($upload['error'])) {
                    $data['qualification_cert_url'] = $upload['url'];
                }
            }

            $result = ICA_LMS_DB::create_student($data);

            if ($result && isset($result['success']) && $result['success']) {
                // Store success message with credentials in transient
                $success_msg = sprintf(
                    'Student created successfully! WordPress user created with - Username: %s | Password: %s',
                    isset($result['reg_no']) ? $result['reg_no'] : 'Student',
                    $data['mobile_no']
                );
                set_transient('ica_lms_success_message', $success_msg, 30);
                
                wp_redirect(add_query_arg('page', 'ica-lms-students', admin_url('admin.php')));
                exit;
            } else {
                $error_msg = isset($result['error']) ? $result['error'] : 'Error adding student. Please try again.';
                echo '<div class="notice notice-error"><p>' . esc_html($error_msg) . '</p></div>';
            }
        } elseif ($action === 'ica_lms_edit_student') {
            if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['_wpnonce'], 'ica_lms_edit_student')) {
                wp_die('Unauthorized');
            }

            $student_id = (int) $_POST['student_id'];

            $course_fee = (float) ($_POST['fee_amount'] ?? 0);
            $discount_amount = (float) ($_POST['discount_amount'] ?? 0);
            $final_fee = max(0, $course_fee - $discount_amount);

            $data = array(
                'name' => $_POST['name'] ?? '',
                'father_name' => $_POST['father_name'] ?? '',
                'mother_name' => $_POST['mother_name'] ?? '',
                'date_of_birth' => $_POST['date_of_birth'] ?? '',
                'gender' => $_POST['gender'] ?? '',
                'category_id' => $_POST['category_id'] ?? 0,
                'qualification' => $_POST['qualification'] ?? '',
                'mobile_no' => $_POST['mobile_no'] ?? '',
                'aadhar_no' => $_POST['aadhar_no'] ?? '',
                'address' => $_POST['address'] ?? '',
                'fee_amount' => $final_fee,
                'discount_amount' => $discount_amount,
                'fee_type' => $_POST['fee_type'] ?? 'one_time',
                'installment_count' => (int) ($_POST['installment_count'] ?? 1),
                'fee_status' => $_POST['fee_status'] ?? 'pending',
                'status' => $_POST['status'] ?? 'active',
            );

            // Handle file uploads
            if (!function_exists('wp_handle_upload')) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }

            if (!empty($_FILES['student_photo']['name'])) {
                $upload = wp_handle_upload($_FILES['student_photo'], array('test_form' => false));
                if ($upload && !isset($upload['error'])) {
                    $data['student_photo_url'] = $upload['url'];
                }
            }

            if (!empty($_FILES['student_signature']['name'])) {
                $upload = wp_handle_upload($_FILES['student_signature'], array('test_form' => false));
                if ($upload && !isset($upload['error'])) {
                    $data['student_signature_url'] = $upload['url'];
                }
            }

            if (!empty($_FILES['aadhar_photo']['name'])) {
                $upload = wp_handle_upload($_FILES['aadhar_photo'], array('test_form' => false));
                if ($upload && !isset($upload['error'])) {
                    $data['aadhar_photo_url'] = $upload['url'];
                }
            }

            if (!empty($_FILES['qualification_cert']['name'])) {
                $upload = wp_handle_upload($_FILES['qualification_cert'], array('test_form' => false));
                if ($upload && !isset($upload['error'])) {
                    $data['qualification_cert_url'] = $upload['url'];
                }
            }

            ICA_LMS_DB::update_student($student_id, $data);

            wp_redirect(add_query_arg('page', 'ica-lms-students', admin_url('admin.php')));
            exit;
        }
    }

    /**
     * AJAX: Search courses
     */
    public static function ajax_search_courses() {
        $search = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : '';

        $courses = get_posts(array(
            'post_type' => 'courses',
            'post_status' => 'publish',
            'posts_per_page' => 10,
            's' => $search,
        ));

        $results = array();
        foreach ($courses as $course) {
            $results[] = array(
                'id' => $course->ID,
                'label' => $course->post_title,
                'value' => $course->post_title,
            );
        }

        wp_send_json($results);
    }

    /**
     * AJAX: Get course fee
     */
    public static function ajax_get_course_fee() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'ica_get_course_fee')) {
            wp_send_json_error('Nonce verification failed');
        }

        $course_id = isset($_POST['course_id']) ? (int) $_POST['course_id'] : 0;

        error_log("ICA_LMS AJAX: ajax_get_course_fee() called with course_id: $course_id");

        if (!$course_id) {
            error_log("ICA_LMS AJAX: Course ID is empty");
            wp_send_json_error('Course ID required');
        }

        $course = get_post($course_id);
        if (!$course) {
            error_log("ICA_LMS AJAX: Course post not found for ID: $course_id");
            wp_send_json_error('Course not found');
        }

        error_log("ICA_LMS AJAX: Course found - Title: " . $course->post_title . ", ID: " . $course->ID . ", Post Type: " . $course->post_type);

        // Get all post meta for debugging
        $all_meta = get_post_meta($course_id);
        error_log("ICA_LMS AJAX: All post meta for course $course_id: " . json_encode($all_meta));

        $fee_data = ICA_LMS_DB::get_course_fee($course_id);

        error_log("ICA_LMS AJAX: Fee data returned: " . json_encode($fee_data));

        if (empty($fee_data) || !isset($fee_data['amount'])) {
            error_log("ICA_LMS AJAX: Fee data is invalid");
            wp_send_json_error(array('amount' => 0, 'currency' => 'INR'));
        }

        wp_send_json_success($fee_data);
    }

    /**
     * AJAX: Add category
     */
    public static function ajax_add_category() {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['nonce'], 'ica_add_category')) {
            wp_send_json_error('Unauthorized');
        }

        $name = sanitize_text_field($_POST['name']);
        $description = sanitize_textarea_field($_POST['description']);

        if (empty($name)) {
            wp_send_json_error('Category name is required');
        }

        $result = ICA_LMS_DB::create_category($name, $description);

        if ($result) {
            $categories = ICA_LMS_DB::get_categories();
            $new_cat = end($categories);
            wp_send_json_success(array(
                'id' => $new_cat['id'],
                'name' => $new_cat['name'],
            ));
        } else {
            wp_send_json_error('Failed to add category');
        }
    }

    /**
     * AJAX: Get batches for a course
     */
    public static function ajax_get_batches() {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['nonce'], 'ica_get_batches')) {
            wp_send_json_error('Unauthorized');
        }

        $course_id = (int) $_POST['course_id'];

        if (empty($course_id)) {
            wp_send_json_error('Course ID is required');
        }

        $batches = ICA_LMS_DB::get_batches($course_id);

        wp_send_json_success($batches);
    }

    /**
     * AJAX: Add new batch
     */
    public static function ajax_add_batch() {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['nonce'], 'ica_add_batch')) {
            wp_send_json_error('Unauthorized');
        }

        $batch_name = sanitize_text_field($_POST['batch_name']);
        $course_id = (int) $_POST['course_id'];
        $total_students = (int) $_POST['total_students'];
        $batch_start_date = sanitize_text_field($_POST['batch_start_date']);
        $batch_end_date = sanitize_text_field($_POST['batch_end_date']);
        $description = sanitize_textarea_field($_POST['description']);

        if (empty($batch_name) || empty($course_id) || $total_students <= 0) {
            wp_send_json_error('Batch name, course, and total students are required');
        }

        $result = ICA_LMS_DB::create_batch($batch_name, $course_id, $total_students, $batch_start_date, $batch_end_date, $description);

        if ($result) {
            $batch = ICA_LMS_DB::get_batches($course_id);
            $new_batch = end($batch);
            wp_send_json_success($new_batch);
        } else {
            wp_send_json_error('Batch already exists or failed to create');
        }
    }

    /**
     * AJAX: Edit batch
     */
    public static function ajax_edit_batch() {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['nonce'], 'ica_edit_batch')) {
            wp_send_json_error('Unauthorized');
        }

        $batch_id = (int) $_POST['batch_id'];
        $batch_name = sanitize_text_field($_POST['batch_name']);
        $total_students = (int) $_POST['total_students'];
        $batch_start_date = sanitize_text_field($_POST['batch_start_date']);
        $batch_end_date = sanitize_text_field($_POST['batch_end_date']);
        $description = sanitize_textarea_field($_POST['description']);
        $status = sanitize_key($_POST['status']);

        if (empty($batch_id) || empty($batch_name) || $total_students <= 0) {
            wp_send_json_error('Batch ID, name, and total students are required');
        }

        $update_data = array(
            'batch_name' => $batch_name,
            'total_students' => $total_students,
            'batch_start_date' => !empty($batch_start_date) ? $batch_start_date : null,
            'batch_end_date' => !empty($batch_end_date) ? $batch_end_date : null,
            'description' => $description,
            'status' => in_array($status, array('active', 'inactive', 'completed')) ? $status : 'active'
        );

        $result = ICA_LMS_DB::update_batch($batch_id, $update_data);

        if ($result) {
            $batch = ICA_LMS_DB::get_batch($batch_id);
            wp_send_json_success($batch);
        } else {
            wp_send_json_error('Failed to update batch');
        }
    }

    /**
     * Handle delete student
     */
    public static function handle_delete_student() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $student_id = (int) $_GET['student_id'];
        $nonce = $_GET['_wpnonce'];

        if (!wp_verify_nonce($nonce, 'ica_lms_delete_student_' . $student_id)) {
            wp_die('Unauthorized');
        }

        ICA_LMS_DB::delete_student($student_id);

        wp_redirect(add_query_arg('page', 'ica-lms-students', admin_url('admin.php')));
        exit;
    }

    /**
     * Handle individual ID card download
     */
    public static function handle_download_id_card() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        try {
            $student_id = (int) $_GET['student_id'];
            $nonce = $_GET['nonce'];

            if (!wp_verify_nonce($nonce, 'ica_lms_id_card_' . $student_id)) {
                wp_die('Unauthorized');
            }

            if (!class_exists('ICA_LMS_ID_Card')) {
                wp_die('ID Card Generator not available');
            }

            ICA_LMS_ID_Card::generate_id_card_pdf($student_id);
        } catch (Exception $e) {
            error_log('ICA_LMS ID Card Download Error: ' . $e->getMessage());
            wp_die('Error downloading ID card: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: Bulk download ID cards
     */
    public static function ajax_bulk_download_id_cards() {
        try {
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Unauthorized');
            }

            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ica_lms_bulk_download_id_cards')) {
                wp_send_json_error('Nonce verification failed');
            }

            if (!class_exists('ICA_LMS_ID_Card')) {
                wp_send_json_error('ID Card Generator not available');
            }

            // Get all students or specific ones if provided
            $course_filter = isset($_POST['course_id']) ? (int) $_POST['course_id'] : 0;
            
            if ($course_filter > 0) {
                $total = ICA_LMS_DB::count_students('', $course_filter);
                $students = ICA_LMS_DB::get_students($total, 0, '', $course_filter);
                $student_ids = wp_list_pluck($students, 'id');
            } else {
                // Get all students
                $total = ICA_LMS_DB::count_students();
                $students = ICA_LMS_DB::get_students($total, 0);
                $student_ids = wp_list_pluck($students, 'id');
            }

            $zip_file = ICA_LMS_ID_Card::generate_bulk_id_cards($student_ids);

            if ($zip_file && file_exists($zip_file)) {
                wp_send_json_success(array(
                    'zip_url' => wp_upload_dir()['baseurl'] . '/' . basename($zip_file),
                    'filename' => basename($zip_file)
                ));
            } else {
                wp_send_json_error('Failed to generate ID cards');
            }
        } catch (Exception $e) {
            error_log('ICA_LMS Bulk Download Error: ' . $e->getMessage());
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }

    /**
     * Render teacher students page (filtered for teacher's courses)
     */
    public static function render_teacher_students_page() {
        if (!current_user_can('edit_posts')) {
            wp_die('Unauthorized');
        }

        $current_user_id = get_current_user_id();
        $user = get_userdata($current_user_id);
        $is_admin = current_user_can('manage_options');
        $is_teacher = in_array(ICA_LMS_User_Roles::TEACHER_ROLE, (array) $user->roles);
        $is_student = in_array(ICA_LMS_User_Roles::STUDENT_ROLE, (array) $user->roles);

        // Block students from accessing teacher pages
        if ($is_student && !$is_teacher && !$is_admin) {
            wp_die('Sorry, you are not allowed to perform this action.');
        }

        // Get teacher's courses (posts they created)
        if ($is_teacher || $is_admin) {
            $teacher_courses = get_posts(array(
                'post_type' => 'courses',
                'author' => $is_admin ? 0 : $current_user_id,
                'posts_per_page' => -1,
            ));
            $teacher_course_ids = wp_list_pluck($teacher_courses, 'ID');
        } else {
            $teacher_courses = array();
            $teacher_course_ids = array();
        }

        if (empty($teacher_course_ids) && ($is_teacher || $is_admin)) {
            ?>
            <div class="wrap">
                <h1>View Students</h1>
                <div style="padding: 20px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 5px;">
                    <p>You don't have any courses yet. Please create courses first to view enrolled students.</p>
                </div>
            </div>
            <?php
            return;
        }

        // Get students from teacher's courses
        global $wpdb;
        $db_table = ICA_LMS_DB::table_students();

        $placeholders = implode(',', array_fill(0, count($teacher_course_ids), '%d'));
        $query = $wpdb->prepare(
            "SELECT * FROM $db_table WHERE course_id IN ($placeholders) ORDER BY name ASC",
            $teacher_course_ids
        );
        $students = $wpdb->get_results($query, ARRAY_A);

        ?>
        <div class="wrap">
            <h1>Students in My Courses</h1>

            <?php if (empty($students)) : ?>
                <div style="padding: 20px; background: #e8f5e9; border-radius: 5px;">
                    <p>No students enrolled in your courses yet.</p>
                </div>
            <?php else : ?>
                <table class="wp-list-table widefat striped">
                    <thead>
                        <tr>
                            <th>RegNo</th>
                            <th>Name</th>
                            <th>Course</th>
                            <th>Roll No</th>
                            <th>Mobile</th>
                            <th>Fee Amount</th>
                            <th>Payment Status</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student) : ?>
                            <?php $course = get_post($student['course_id']); ?>
                            <tr>
                                <td><strong><?php echo esc_html($student['reg_no']); ?></strong></td>
                                <td><?php echo esc_html($student['name']); ?></td>
                                <td><?php echo esc_html($course ? $course->post_title : 'N/A'); ?></td>
                                <td><?php echo esc_html($student['roll_no']); ?></td>
                                <td><?php echo esc_html($student['mobile_no']); ?></td>
                                <td>
                                    <strong><?php echo esc_html(number_format($student['fee_amount'], 2)); ?></strong>
                                    <?php if (!empty($student['discount_amount']) && $student['discount_amount'] > 0) : ?>
                                        <br><small style="color: #666;">Discount: <?php echo esc_html(number_format($student['discount_amount'], 2)); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="padding: 3px 8px; border-radius: 3px; background: <?php 
                                        $bg_color = '#28a745';
                                        if ($student['fee_status'] === 'pending') {
                                            $bg_color = '#ffc107';
                                        } elseif ($student['fee_status'] === 'submitted') {
                                            $bg_color = '#17a2b8';
                                        } elseif ($student['fee_status'] === 'approved') {
                                            $bg_color = '#28a745';
                                        }
                                        echo $bg_color;
                                    ?>; color: white;">
                                        <?php echo esc_html(ucfirst($student['fee_status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="padding: 3px 8px; border-radius: 3px; background: <?php echo $student['status'] === 'active' ? '#28a745' : '#dc3545'; ?>; color: white;">
                                        <?php echo esc_html(ucfirst($student['status'])); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render student progress page
     */
    public static function render_student_progress_page() {
        if (!current_user_can('edit_posts')) {
            wp_die('Unauthorized');
        }

        $current_user_id = get_current_user_id();
        $user = get_userdata($current_user_id);
        $is_admin = current_user_can('manage_options');
        $is_teacher = in_array(ICA_LMS_User_Roles::TEACHER_ROLE, (array) $user->roles);
        $is_student = in_array(ICA_LMS_User_Roles::STUDENT_ROLE, (array) $user->roles);

        // Block students from accessing teacher pages
        if ($is_student && !$is_teacher && !$is_admin) {
            wp_die('Sorry, you are not allowed to perform this action.');
        }

        // Get teacher's courses
        if ($is_teacher || $is_admin) {
            $teacher_courses = get_posts(array(
                'post_type' => 'courses',
                'author' => $is_admin ? 0 : $current_user_id,
                'posts_per_page' => -1,
            ));
            $teacher_course_ids = wp_list_pluck($teacher_courses, 'ID');
        } else {
            $teacher_courses = array();
            $teacher_course_ids = array();
        }

        if (empty($teacher_course_ids) && ($is_teacher || $is_admin)) {
            ?>
            <div class="wrap">
                <h1>Student Progress</h1>
                <div style="padding: 20px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 5px;">
                    <p>You don't have any courses yet.</p>
                </div>
            </div>
            <?php
            return;
        }

        ?>
        <div class="wrap">
            <h1>Student Progress</h1>

            <div style="padding: 20px; background: #e3f2fd; border-left: 4px solid #2196f3; border-radius: 5px; margin-bottom: 20px;">
                <p>Track student progress in topics and courses. This shows which topics students have read and their completion percentage.</p>
            </div>

            <table class="wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Course</th>
                        <th>Topics Read</th>
                        <th>Progress (%)</th>
                        <th>Last Accessed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($teacher_course_ids)) {
                        global $wpdb;
                        $progress_table = ICA_LMS_Course_Topics::table()['progress'] ?? '';
                        $students_table = ICA_LMS_DB::table_students();

                        if (!empty($progress_table)) {
                            $placeholders = implode(',', array_fill(0, count($teacher_course_ids), '%d'));
                            $query = $wpdb->prepare(
                                "SELECT DISTINCT s.id, s.name, s.course_id, COUNT(p.id) as topics_read 
                                 FROM $students_table s 
                                 LEFT JOIN $progress_table p ON s.id = p.student_id AND s.course_id = p.course_id
                                 WHERE s.course_id IN ($placeholders)
                                 GROUP BY s.id, s.course_id
                                 ORDER BY s.name ASC",
                                $teacher_course_ids
                            );
                            $progress_data = $wpdb->get_results($query, ARRAY_A);

                            if (!empty($progress_data)) {
                                foreach ($progress_data as $row) {
                                    $course = get_post($row['course_id']);
                                    $course_name = $course ? $course->post_title : 'N/A';
                                    $progress = isset($row['progress_percent']) ? $row['progress_percent'] : 0;
                                    ?>
                                    <tr>
                                        <td><?php echo esc_html($row['name']); ?></td>
                                        <td><?php echo esc_html($course_name); ?></td>
                                        <td><?php echo esc_html($row['topics_read'] ?? 0); ?></td>
                                        <td>
                                            <div style="width: 200px; background: #f0f0f0; border-radius: 3px; overflow: hidden;">
                                                <div style="background: #4caf50; width: <?php echo esc_attr($progress); ?>%; height: 20px; border-radius: 3px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 12px;">
                                                    <?php echo esc_html(number_format($progress, 0)); ?>%
                                                </div>
                                            </div>
                                        </td>
                                        <td>-</td>
                                    </tr>
                                    <?php
                                }
                            }
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render course materials page
     */
    public static function render_course_materials_page() {
        if (!current_user_can('edit_posts')) {
            wp_die('Unauthorized');
        }

        $current_user_id = get_current_user_id();
        $user = get_userdata($current_user_id);
        $is_admin = current_user_can('manage_options');
        $is_teacher = in_array(ICA_LMS_User_Roles::TEACHER_ROLE, (array) $user->roles);
        $is_student = in_array(ICA_LMS_User_Roles::STUDENT_ROLE, (array) $user->roles);

        // Block students from accessing teacher pages
        if ($is_student && !$is_teacher && !$is_admin) {
            wp_die('Sorry, you are not allowed to perform this action.');
        }

        // Get teacher's courses
        $teacher_courses = get_posts(array(
            'post_type' => 'courses',
            'author' => $is_admin ? 0 : $current_user_id,
            'posts_per_page' => -1,
        ));

        if (empty($teacher_courses)) {
            ?>
            <div class="wrap">
                <h1>Course Materials</h1>
                <div style="padding: 20px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 5px;">
                    <p>You don't have any courses yet.</p>
                </div>
            </div>
            <?php
            return;
        }

        ?>
        <div class="wrap">
            <h1>Course Materials (Topics)</h1>

            <div style="padding: 20px; background: #e8f5e9; border-left: 4px solid #4caf50; border-radius: 5px; margin-bottom: 20px;">
                <p>View and manage topics assigned to your courses. Topics are posts that students can access and read.</p>
            </div>

            <?php foreach ($teacher_courses as $course) : ?>
                <?php
                // Get topics for this course
                if (!class_exists('ICA_LMS_Course_Topics')) {
                    continue;
                }

                $topics = ICA_LMS_Course_Topics::get_course_topics($course->ID);
                $available_posts = ICA_LMS_Course_Topics::get_available_posts();
                ?>
                <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #2196f3;">
                    <h3 style="margin-top: 0;">
                        <?php echo esc_html($course->post_title); ?>
                        <small style="color: #666; font-weight: normal;">(<?php echo count($topics); ?> topics)</small>
                    </h3>

                    <!-- Add Topic Form -->
                    <div style="background: #e8f5e9; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                        <label><strong>Add Topic:</strong></label>
                        <div style="display: grid; grid-template-columns: 1fr auto; gap: 10px; margin-top: 10px;">
                            <select class="ica-add-topic-select" data-course-id="<?php echo esc_attr($course->ID); ?>" style="padding: 8px; border: 1px solid #ddd; border-radius: 3px;">
                                <option value="">-- Select a Post to Add --</option>
                                <?php foreach ($available_posts as $post_item) : ?>
                                    <option value="<?php echo esc_attr($post_item->ID); ?>">
                                        <?php echo esc_html($post_item->post_title); ?> (by <?php echo esc_html($post_item->post_author_name ?? 'Unknown'); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="button button-primary ica-add-topic-btn" data-course-id="<?php echo esc_attr($course->ID); ?>" style="padding: 8px 15px;">Add Topic</button>
                        </div>
                        <div class="ica-topic-message" data-course-id="<?php echo esc_attr($course->ID); ?>" style="margin-top: 10px; display: none; padding: 10px; border-radius: 3px;"></div>
                    </div>

                    <?php if (empty($topics)) : ?>
                        <p style="color: #999;">No topics added to this course yet. Add topics from the dropdown above.</p>
                    <?php else : ?>
                        <table class="wp-list-table widefat striped" style="margin-top: 10px;">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">Order</th>
                                    <th>Topic Title</th>
                                    <th style="width: 150px;">Author</th>
                                    <th style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topics as $index => $topic) : ?>
                                    <tr>
                                        <td><?php echo esc_html($index + 1); ?></td>
                                        <td>
                                            <strong><?php echo esc_html($topic['post_title']); ?></strong>
                                            <br>
                                            <small style="color: #999;"><?php echo wp_trim_words($topic['post_content'], 20); ?></small>
                                        </td>
                                        <td><?php echo esc_html(get_the_author_meta('display_name', $topic['post_author'])); ?></td>
                                        <td>
                                            <a href="<?php echo esc_url(get_permalink($topic['ID'])); ?>" class="button button-small" target="_blank">View</a>
                                            <a href="<?php echo esc_url(admin_url('post.php?post=' . $topic['ID'] . '&action=edit')); ?>" class="button button-small">Edit</a>
                                            <button type="button" class="button button-small ica-remove-topic" data-course-id="<?php echo esc_attr($course->ID); ?>" data-post-id="<?php echo esc_attr($topic['ID']); ?>" style="background: #dc3545; color: white; border: none; cursor: pointer;">Remove</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <script>
            jQuery(document).ready(function() {
                // Add topic functionality
                jQuery(document).on('click', '.ica-add-topic-btn', function() {
                    const courseId = jQuery(this).data('course-id');
                    const postId = jQuery(this).closest('div').find('.ica-add-topic-select').val();
                    const messageDiv = jQuery(this).closest('div').find('.ica-topic-message');
                    
                    if (!postId) {
                        messageDiv.css('background', '#fff3cd').css('border', '1px solid #ffc107').css('color', '#856404').text('Please select a post to add.').show();
                        return;
                    }
                    
                    jQuery.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        data: {
                            action: 'ica_add_topic',
                            course_id: courseId,
                            post_id: postId,
                            nonce: '<?php echo esc_js(wp_create_nonce('ica_add_topic')); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                messageDiv.css('background', '#d4edda').css('border', '1px solid #c3e6cb').css('color', '#155724').html('✓ Topic added successfully! <strong><a href="#" onclick="location.reload(); return false;">Refresh to see changes</a></strong>').show();
                            } else {
                                messageDiv.css('background', '#f8d7da').css('border', '1px solid #f5c6cb').css('color', '#721c24').text('Error: ' + (response.data || 'Unknown error')).show();
                            }
                        },
                        error: function() {
                            messageDiv.css('background', '#f8d7da').css('border', '1px solid #f5c6cb').css('color', '#721c24').text('Error adding topic. Please try again.').show();
                        }
                    });
                });
                
                // Remove topic functionality
                jQuery(document).on('click', '.ica-remove-topic', function() {
                    if (!confirm('Are you sure you want to remove this topic?')) return;
                    
                    const courseId = jQuery(this).data('course-id');
                    const postId = jQuery(this).data('post-id');
                    const button = jQuery(this);
                    
                    jQuery.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        data: {
                            action: 'ica_remove_topic',
                            course_id: courseId,
                            post_id: postId,
                            nonce: '<?php echo esc_js(wp_create_nonce('ica_remove_topic')); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                button.closest('tr').fadeOut(300, function() {
                                    jQuery(this).remove();
                                });
                            } else {
                                alert('Error: ' + (response.data || 'Unknown error'));
                            }
                        },
                        error: function() {
                            alert('Error removing topic. Please try again.');
                        }
                    });
                });
            });
        </script>
        <?php
    }

    /**
     * Render all courses page (admin only)
     */
    public static function render_all_courses_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $courses = get_posts(array(
            'post_type' => 'courses',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ));

        ?>
        <div class="wrap">
            <h1>All Courses</h1>

            <?php if (empty($courses)) : ?>
                <div style="padding: 20px; background: #fff3cd; border-radius: 5px;">
                    <p>No courses found.</p>
                </div>
            <?php else : ?>
                <table class="wp-list-table widefat striped">
                    <thead>
                        <tr>
                            <th>Course Name</th>
                            <th>Teacher</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course) : ?>
                            <tr>
                                <td><strong><?php echo esc_html($course->post_title); ?></strong></td>
                                <td><?php echo esc_html(get_the_author_meta('display_name', $course->post_author)); ?></td>
                                <td><?php echo esc_html(ucfirst($course->post_status)); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(get_permalink($course->ID)); ?>" class="button button-small" target="_blank">View</a>
                                    <a href="<?php echo esc_url(admin_url('post.php?post=' . $course->ID . '&action=edit')); ?>" class="button button-small">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }
}

