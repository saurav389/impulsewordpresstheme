<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ICA LMS Exam Management System
 * Handles exam creation, question management, and course mapping
 */
class ICA_LMS_Exam_Management {
    public static function init() {
        // Register custom capability for exams on init hook (priority 1 - very early, before admin_menu and admin_init)
        add_action('init', array(__CLASS__, 'register_capabilities'), 1);
        
        // Admin menu
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'), 11);
        add_action('admin_init', array(__CLASS__, 'handle_form_submission'));
        
        // AJAX endpoints
        add_action('wp_ajax_ica_add_exam_question', array(__CLASS__, 'ajax_add_question'));
        add_action('wp_ajax_ica_edit_exam_question', array(__CLASS__, 'ajax_edit_question'));
        add_action('wp_ajax_ica_get_exam_question', array(__CLASS__, 'ajax_get_exam_question'));
        add_action('wp_ajax_ica_delete_exam_question', array(__CLASS__, 'ajax_delete_exam_question'));
        add_action('wp_ajax_ica_bulk_import_questions', array(__CLASS__, 'ajax_bulk_import_questions'));
        add_action('wp_ajax_ica_update_exam_courses', array(__CLASS__, 'ajax_update_exam_courses'));
        
        // Create database tables
        add_action('init', array(__CLASS__, 'create_tables'), 5);
    }

    /**
     * Register exam capabilities for teacher and admin roles
     * Runs on init hook at priority 1 to ensure capabilities are available before any permission checks
     */
    public static function register_capabilities() {
        // Get teacher role - ensure it has manage_exams capability
        $teacher_role = get_role(ICA_LMS_User_Roles::TEACHER_ROLE);
        if ($teacher_role) {
            $teacher_role->add_cap('manage_exams');
        }
        
        // Get admin role - ensure it has manage_exams capability
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->add_cap('manage_exams');
        }
    }

    /**
     * Create database tables for exams and questions
     */
    public static function create_tables() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();

        // Exams table
        $exams_table = $wpdb->prefix . 'ica_lms_exams';
        $sql_exams = "
        CREATE TABLE IF NOT EXISTS $exams_table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            created_by BIGINT UNSIGNED NOT NULL,
            exam_title VARCHAR(255) NOT NULL,
            exam_description TEXT NULL,
            exam_type VARCHAR(50) NOT NULL DEFAULT 'test',
            start_date DATETIME NOT NULL,
            end_date DATETIME NOT NULL,
            duration_hours INT NOT NULL DEFAULT 1,
            total_marks INT NOT NULL DEFAULT 100,
            pass_marks INT NOT NULL DEFAULT 40,
            status VARCHAR(50) NOT NULL DEFAULT 'draft',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY created_by (created_by),
            KEY exam_type (exam_type),
            KEY status (status),
            KEY start_date (start_date)
        ) $charset_collate;
        ";
        dbDelta($sql_exams);

        // Exam Questions table
        $questions_table = $wpdb->prefix . 'ica_lms_exam_questions';
        $sql_questions = "
        CREATE TABLE IF NOT EXISTS $questions_table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            exam_id BIGINT UNSIGNED NOT NULL,
            question_text LONGTEXT NOT NULL,
            question_order INT NOT NULL DEFAULT 0,
            marks INT NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY exam_id (exam_id),
            KEY question_order (question_order),
            FOREIGN KEY (exam_id) REFERENCES $exams_table(id) ON DELETE CASCADE
        ) $charset_collate;
        ";
        dbDelta($sql_questions);

        // Question Options table
        $options_table = $wpdb->prefix . 'ica_lms_exam_question_options';
        $sql_options = "
        CREATE TABLE IF NOT EXISTS $options_table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            question_id BIGINT UNSIGNED NOT NULL,
            option_text TEXT NOT NULL,
            option_order INT NOT NULL DEFAULT 0,
            is_correct BOOLEAN NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY question_id (question_id),
            KEY is_correct (is_correct),
            FOREIGN KEY (question_id) REFERENCES $questions_table(id) ON DELETE CASCADE
        ) $charset_collate;
        ";
        dbDelta($sql_options);

        // Exam Course Mapping table
        $course_mapping_table = $wpdb->prefix . 'ica_lms_exam_course_mapping';
        $sql_course_mapping = "
        CREATE TABLE IF NOT EXISTS $course_mapping_table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            exam_id BIGINT UNSIGNED NOT NULL,
            course_id BIGINT UNSIGNED NOT NULL,
            is_mandatory BOOLEAN NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY exam_course (exam_id, course_id),
            KEY exam_id (exam_id),
            KEY course_id (course_id),
            FOREIGN KEY (exam_id) REFERENCES $exams_table(id) ON DELETE CASCADE
        ) $charset_collate;
        ";
        dbDelta($sql_course_mapping);
    }

    /**
     * Get table names
     */
    public static function table_exams() {
        global $wpdb;
        return $wpdb->prefix . 'ica_lms_exams';
    }

    public static function table_questions() {
        global $wpdb;
        return $wpdb->prefix . 'ica_lms_exam_questions';
    }

    public static function table_options() {
        global $wpdb;
        return $wpdb->prefix . 'ica_lms_exam_question_options';
    }

    public static function table_course_mapping() {
        global $wpdb;
        return $wpdb->prefix . 'ica_lms_exam_course_mapping';
    }

    /**
     * Add admin menu
     */
    public static function add_admin_menu() {
        // Use custom manage_exams capability available to both teachers and admins
        add_submenu_page(
            'ica-lms',
            'Exam Management',
            'Exams',
            'manage_exams',
            'ica-lms-exams',
            array(__CLASS__, 'render_exams_page')
        );
    }

    /**
     * Render exams management page
     */
    public static function render_exams_page() {
        if (!current_user_can('manage_exams') && !current_user_can('manage_options')) {
            wp_die('Sorry, you are not allowed to access this page.');
        }

        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $exam_id = isset($_GET['exam_id']) ? (int) $_GET['exam_id'] : 0;

        if ($action === 'edit' && $exam_id) {
            self::render_edit_exam_form($exam_id);
        } elseif ($action === 'view_questions' && $exam_id) {
            self::render_questions_page($exam_id);
        } elseif ($action === 'map_courses' && $exam_id) {
            self::render_course_mapping_page($exam_id);
        } elseif ($action === 'new') {
            self::render_new_exam_form();
        } else {
            self::render_exams_list();
        }
    }

    /**
     * Render exams list
     */
    public static function render_exams_list() {
        global $wpdb;
        $exams_table = self::table_exams();
        
        $current_user_id = get_current_user_id();
        $user = get_userdata($current_user_id);
        
        // Check if user is admin or teacher
        $is_admin = current_user_can('manage_options');
        $is_teacher = in_array(ICA_LMS_User_Roles::TEACHER_ROLE, (array) $user->roles);
        
        // Build query based on user role
        if ($is_admin) {
            // Admins see all exams
            $query = "SELECT * FROM $exams_table ORDER BY created_at DESC";
        } elseif ($is_teacher) {
            // Teachers see only their own exams
            $query = $wpdb->prepare(
                "SELECT * FROM $exams_table WHERE created_by = %d ORDER BY created_at DESC",
                $current_user_id
            );
        } else {
            // Other users see no exams
            $query = "SELECT * FROM $exams_table WHERE 1=0";
        }

        $exams = $wpdb->get_results($query);

        ?>
        <div class="wrap">
            <h1>Exam Management
                <a href="?page=ica-lms-exams&action=new" class="page-title-action">Create New Exam</a>
            </h1>

            <table class="wp-list-table widefat striped hover">
                <thead>
                    <tr>
                        <th>Exam Name</th>
                        <th>Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Duration</th>
                        <th>Total Marks</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($exams)) : ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 20px;">No exams found. <a href="?page=ica-lms-exams&action=new">Create one now</a></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($exams as $exam) : ?>
                            <tr>
                                <td><strong><?php echo esc_html($exam->exam_title); ?></strong></td>
                                <td><?php echo esc_html(ucfirst($exam->exam_type)); ?></td>
                                <td><?php echo esc_html(date('M d, Y', strtotime($exam->start_date))); ?></td>
                                <td><?php echo esc_html(date('M d, Y', strtotime($exam->end_date))); ?></td>
                                <td><?php echo esc_html($exam->duration_hours); ?> hrs</td>
                                <td><?php echo esc_html($exam->total_marks); ?> marks</td>
                                <td>
                                    <span style="padding: 3px 8px; border-radius: 3px; background: <?php echo $exam->status === 'published' ? '#28a745' : '#ffc107'; ?>; color: white;">
                                        <?php echo ucfirst($exam->status); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?page=ica-lms-exams&action=view_questions&exam_id=<?php echo esc_attr($exam->id); ?>" class="button button-small">Questions</a>
                                    <a href="?page=ica-lms-exams&action=map_courses&exam_id=<?php echo esc_attr($exam->id); ?>" class="button button-small">Map Courses</a>
                                    <a href="?page=ica-lms-exams&action=edit&exam_id=<?php echo esc_attr($exam->id); ?>" class="button button-small button-primary">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render new exam form
     */
    public static function render_new_exam_form() {
        // Permission check
        if (!current_user_can('manage_exams') && !current_user_can('manage_options')) {
            wp_die('Sorry, you are not allowed to access this page.');
        }
        
        ?>
        <div class="wrap">
            <h1>Create New Exam</h1>
            <form method="post" style="max-width: 600px;">
                <?php wp_nonce_field('ica_lms_create_exam'); ?>
                
                <table class="form-table">
                    <tr>
                        <th><label for="exam_title">Exam Title *</label></th>
                        <td><input type="text" id="exam_title" name="exam_title" required style="width: 100%; padding: 8px;"></td>
                    </tr>
                    <tr>
                        <th><label for="exam_description">Description</label></th>
                        <td><textarea id="exam_description" name="exam_description" style="width: 100%; padding: 8px; height: 100px;"></textarea></td>
                    </tr>
                    <tr>
                        <th><label for="exam_type">Exam Type *</label></th>
                        <td>
                            <select id="exam_type" name="exam_type" required style="width: 100%; padding: 8px;">
                                <option value="test">Test</option>
                                <option value="final_exam">Final Exam</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="start_date">Start Date *</label></th>
                        <td><input type="datetime-local" id="start_date" name="start_date" required style="width: 100%; padding: 8px;"></td>
                    </tr>
                    <tr>
                        <th><label for="end_date">Expiry Date *</label></th>
                        <td><input type="datetime-local" id="end_date" name="end_date" required style="width: 100%; padding: 8px;"></td>
                    </tr>
                    <tr>
                        <th><label for="duration_hours">Duration (Hours) *</label></th>
                        <td><input type="number" id="duration_hours" name="duration_hours" min="1" required style="width: 100%; padding: 8px;" value="1"></td>
                    </tr>
                    <tr>
                        <th><label for="total_marks">Total Marks *</label></th>
                        <td><input type="number" id="total_marks" name="total_marks" min="1" required style="width: 100%; padding: 8px;" value="100"></td>
                    </tr>
                    <tr>
                        <th><label for="pass_marks">Pass Marks *</label></th>
                        <td><input type="number" id="pass_marks" name="pass_marks" min="1" required style="width: 100%; padding: 8px;" value="40"></td>
                    </tr>
                </table>
                
                <p>
                    <button type="submit" name="ica_create_exam" class="button button-primary">Create Exam</button>
                    <a href="?page=ica-lms-exams" class="button">Cancel</a>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Render edit exam form
     */
    public static function render_edit_exam_form($exam_id) {
        // Permission check
        if (!current_user_can('manage_exams') && !current_user_can('manage_options')) {
            wp_die('Sorry, you are not allowed to access this page.');
        }
        
        global $wpdb;
        $exams_table = self::table_exams();

        $exam = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $exams_table WHERE id = %d",
            $exam_id
        ));

        if (!$exam) {
            wp_die('Exam not found');
        }

        ?>
        <div class="wrap">
            <h1>Edit Exam: <?php echo esc_html($exam->exam_title); ?></h1>
            <form method="post" style="max-width: 600px;">
                <?php wp_nonce_field('ica_lms_edit_exam'); ?>
                <input type="hidden" name="exam_id" value="<?php echo esc_attr($exam_id); ?>">
                
                <table class="form-table">
                    <tr>
                        <th><label for="exam_title">Exam Title *</label></th>
                        <td><input type="text" id="exam_title" name="exam_title" required style="width: 100%; padding: 8px;" value="<?php echo esc_attr($exam->exam_title); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="exam_description">Description</label></th>
                        <td><textarea id="exam_description" name="exam_description" style="width: 100%; padding: 8px; height: 100px;"><?php echo esc_textarea($exam->exam_description); ?></textarea></td>
                    </tr>
                    <tr>
                        <th><label for="exam_type">Exam Type *</label></th>
                        <td>
                            <select id="exam_type" name="exam_type" required style="width: 100%; padding: 8px;">
                                <option value="test" <?php selected($exam->exam_type, 'test'); ?>>Test</option>
                                <option value="final_exam" <?php selected($exam->exam_type, 'final_exam'); ?>>Final Exam</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="start_date">Start Date *</label></th>
                        <td><input type="datetime-local" id="start_date" name="start_date" required style="width: 100%; padding: 8px;" value="<?php echo esc_attr(date('Y-m-d\TH:i', strtotime($exam->start_date))); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="end_date">Expiry Date *</label></th>
                        <td><input type="datetime-local" id="end_date" name="end_date" required style="width: 100%; padding: 8px;" value="<?php echo esc_attr(date('Y-m-d\TH:i', strtotime($exam->end_date))); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="duration_hours">Duration (Hours) *</label></th>
                        <td><input type="number" id="duration_hours" name="duration_hours" min="1" required style="width: 100%; padding: 8px;" value="<?php echo esc_attr($exam->duration_hours); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="total_marks">Total Marks *</label></th>
                        <td><input type="number" id="total_marks" name="total_marks" min="1" required style="width: 100%; padding: 8px;" value="<?php echo esc_attr($exam->total_marks); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="pass_marks">Pass Marks *</label></th>
                        <td><input type="number" id="pass_marks" name="pass_marks" min="1" required style="width: 100%; padding: 8px;" value="<?php echo esc_attr($exam->pass_marks); ?>"></td>
                    </tr>
                </table>
                
                <p>
                    <button type="submit" name="ica_update_exam" class="button button-primary">Update Exam</button>
                    <a href="?page=ica-lms-exams" class="button">Cancel</a>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Render questions management page
     */
    public static function render_questions_page($exam_id) {
        // Permission check
        if (!current_user_can('manage_exams') && !current_user_can('manage_options')) {
            wp_die('Sorry, you are not allowed to access this page.');
        }
        
        global $wpdb;
        $exams_table = self::table_exams();
        $questions_table = self::table_questions();
        $options_table = self::table_options();

        $exam = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $exams_table WHERE id = %d",
            $exam_id
        ));

        if (!$exam) {
            wp_die('Exam not found');
        }

        $questions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $questions_table WHERE exam_id = %d ORDER BY question_order ASC",
            $exam_id
        ));

        ?>
        <div class="wrap">
            <h1>Manage Questions: <?php echo esc_html($exam->exam_title); ?></h1>

            <div style="margin-bottom: 20px;">
                <button type="button" class="button button-primary" onclick="toggleQuestionForm()">Add Single Question</button>
                <button type="button" class="button" onclick="toggleBulkImportForm()">Bulk Import</button>
                <a href="?page=ica-lms-exams" class="button">Back to Exams</a>
            </div>

            <div id="ica_add_question_form" style="background: #f9f9f9; padding: 15px; margin-bottom: 20px; border-radius: 5px; display: none;">
                <h3 id="form_title">Add Question</h3>
                <form id="ica_question_form">
                    <input type="hidden" name="action" id="form_action" value="ica_add_exam_question">
                    <input type="hidden" name="exam_id" value="<?php echo esc_attr($exam_id); ?>">
                    <input type="hidden" name="question_id" id="form_question_id" value="">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('ica_lms_exam_nonce'); ?>">

                    <p>
                        <label for="question_text"><strong>Question Text *</strong></label><br>
                        <textarea id="question_text" name="question_text" required style="width: 100%; padding: 8px; height: 80px; margin-top: 5px;"></textarea>
                    </p>

                    <p>
                        <label for="marks"><strong>Marks *</strong></label><br>
                        <input type="number" id="marks" name="marks" min="1" required style="width: 100%; padding: 8px; margin-top: 5px;" value="1">
                    </p>

                    <h4>Options (Select correct answer)</h4>
                    <?php for ($i = 1; $i <= 4; $i++) : ?>
                        <p>
                            <label>
                                <input type="radio" name="correct_option" value="<?php echo $i; ?>" required>
                                Option <?php echo $i; ?>
                            </label><br>
                            <textarea name="option_<?php echo $i; ?>" required style="width: 100%; padding: 8px; margin-top: 5px; height: 50px;"></textarea>
                        </p>
                    <?php endfor; ?>

                    <p>
                        <button type="button" class="button button-primary" id="form_submit_btn" onclick="submitQuestion(this)">Add Question</button>
                        <button type="button" class="button" onclick="closeQuestionForm()">Cancel</button>
                    </p>
                    <div id="question_message"></div>
                </form>
            </div>

            <div id="ica_bulk_import_form" style="background: #fff3cd; padding: 15px; margin-bottom: 20px; border-radius: 5px; display: none;">
                <h3>Bulk Import Questions (CSV)</h3>
                <p>CSV Format: Question Text | Option 1 | Option 2 | Option 3 | Option 4 | Correct Option (1-4) | Marks</p>
                <form id="ica_bulk_form" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="ica_bulk_import_questions">
                    <input type="hidden" name="exam_id" value="<?php echo esc_attr($exam_id); ?>">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('ica_lms_exam_nonce'); ?>">
                    
                    <p>
                        <label for="csv_file"><strong>Upload CSV File *</strong></label><br>
                        <input type="file" id="csv_file" name="csv_file" accept=".csv" required style="padding: 8px; margin-top: 5px;">
                    </p>

                    <p>
                        <button type="button" class="button button-primary" onclick="submitBulkImport()">Import Questions</button>
                        <button type="button" class="button" onclick="toggleBulkImportForm()">Cancel</button>
                    </p>
                    <div id="bulk_message"></div>
                </form>
            </div>

            <h3>Current Questions (<?php echo count($questions); ?>)</h3>
            <table class="wp-list-table widefat striped hover">
                <thead>
                    <tr>
                        <th style="width: 50px;">Order</th>
                        <th>Question</th>
                        <th style="width: 80px;">Marks</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($questions)) : ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 20px;">No questions added yet. <a href="#" onclick="toggleQuestionForm(); return false;">Add one now</a></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($questions as $index => $question) : ?>
                            <tr>
                                <td><?php echo esc_html($index + 1); ?></td>
                                <td>
                                    <strong><?php echo wp_trim_words($question->question_text, 15); ?></strong>
                                    <br>
                                    <small style="color: #999;">
                                        <?php
                                        $correct_option = $wpdb->get_var($wpdb->prepare(
                                            "SELECT option_text FROM $options_table WHERE question_id = %d AND is_correct = 1 LIMIT 1",
                                            $question->id
                                        ));
                                        echo 'Correct: ' . esc_html($correct_option);
                                        ?>
                                    </small>
                                </td>
                                <td><?php echo esc_html($question->marks); ?></td>
                                <td>
                                    <button type="button" class="button button-small" onclick="editQuestion(<?php echo esc_attr($question->id); ?>)">Edit</button>
                                    <button type="button" class="button button-small button-secondary" onclick="deleteQuestion(<?php echo esc_attr($question->id); ?>)">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <script type="text/javascript">
        function toggleQuestionForm() {
            var form = document.getElementById('ica_add_question_form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
            if (form.style.display === 'block') {
                resetQuestionForm();
            }
        }

        function closeQuestionForm() {
            document.getElementById('ica_add_question_form').style.display = 'none';
            resetQuestionForm();
        }

        function resetQuestionForm() {
            document.getElementById('ica_question_form').reset();
            document.getElementById('form_question_id').value = '';
            document.getElementById('form_action').value = 'ica_add_exam_question';
            document.getElementById('form_title').textContent = 'Add Question';
            document.getElementById('form_submit_btn').textContent = 'Add Question';
            document.getElementById('question_message').innerHTML = '';
            document.getElementById('marks').value = '1';
        }

        function toggleBulkImportForm() {
            document.getElementById('ica_bulk_import_form').style.display = 
                document.getElementById('ica_bulk_import_form').style.display === 'none' ? 'block' : 'none';
        }

        function editQuestion(questionId) {
            // Fetch question data
            var formData = new FormData();
            formData.append('action', 'ica_get_exam_question');
            formData.append('question_id', questionId);
            formData.append('nonce', '<?php echo wp_create_nonce('ica_lms_exam_nonce'); ?>');

            fetch(ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    var question = data.data;
                    
                    // Populate form
                    document.getElementById('question_text').value = question.question_text;
                    document.getElementById('marks').value = question.marks;
                    document.getElementById('form_question_id').value = questionId;
                    document.getElementById('form_action').value = 'ica_edit_exam_question';
                    document.getElementById('form_title').textContent = 'Edit Question';
                    document.getElementById('form_submit_btn').textContent = 'Update Question';
                    
                    // Set options
                    for (var i = 1; i <= 4; i++) {
                        var optionField = document.querySelector('textarea[name="option_' + i + '"]');
                        if (question.options[i]) {
                            optionField.value = question.options[i].option_text;
                            if (question.options[i].is_correct == 1) {
                                document.querySelector('input[name="correct_option"][value="' + i + '"]').checked = true;
                            }
                        }
                    }
                    
                    // Show form
                    document.getElementById('ica_add_question_form').style.display = 'block';
                } else {
                    alert(data.data || 'Error loading question');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error. Please try again.');
            });
        }

        function submitQuestion(btn) {
            btn.disabled = true;
            var isEdit = document.getElementById('form_action').value === 'ica_edit_exam_question';
            btn.textContent = isEdit ? 'Updating...' : 'Adding...';

            var formData = new FormData(document.getElementById('ica_question_form'));
            
            fetch(ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    var message = isEdit ? 'Question updated successfully!' : 'Question added successfully!';
                    document.getElementById('question_message').innerHTML = '<div style="color: green; padding: 10px; background: #d4edda; border-radius: 3px;">' + message + '</div>';
                    document.getElementById('ica_question_form').reset();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    document.getElementById('question_message').innerHTML = '<div style="color: red; padding: 10px; background: #f8d7da; border-radius: 3px;">' + (data.data || 'Error') + '</div>';
                    btn.disabled = false;
                    btn.textContent = isEdit ? 'Update Question' : 'Add Question';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('question_message').innerHTML = '<div style="color: red; padding: 10px; background: #f8d7da; border-radius: 3px;">Network error. Please try again.</div>';
                btn.disabled = false;
                btn.textContent = isEdit ? 'Update Question' : 'Add Question';
            });
        }

        function submitBulkImport() {
            var form = document.getElementById('ica_bulk_form');
            var btn = form.querySelector('button[type="button"]');
            btn.disabled = true;
            btn.textContent = 'Importing...';

            var formData = new FormData(form);
            
            fetch(ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('bulk_message').innerHTML = '<div style="color: green; padding: 10px; background: #d4edda; border-radius: 3px;">' + data.data + '</div>';
                    setTimeout(() => location.reload(), 2000);
                } else {
                    document.getElementById('bulk_message').innerHTML = '<div style="color: red; padding: 10px; background: #f8d7da; border-radius: 3px;">' + (data.data || 'Error') + '</div>';
                    btn.disabled = false;
                    btn.textContent = 'Import Questions';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('bulk_message').innerHTML = '<div style="color: red; padding: 10px; background: #f8d7da; border-radius: 3px;">Network error. Please try again.</div>';
                btn.disabled = false;
                btn.textContent = 'Import Questions';
            });
        }

        function deleteQuestion(questionId) {
            if (!confirm('Are you sure you want to delete this question?')) return;

            var formData = new FormData();
            formData.append('action', 'ica_delete_exam_question');
            formData.append('question_id', questionId);
            formData.append('nonce', '<?php echo wp_create_nonce('ica_lms_exam_nonce'); ?>');

            fetch(ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.data || 'Error deleting question');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error. Please try again.');
            });
        }
        </script>
        <?php
    }

    /**
     * Render course mapping page
     */
    public static function render_course_mapping_page($exam_id) {
        // Permission check
        if (!current_user_can('manage_exams') && !current_user_can('manage_options')) {
            wp_die('Sorry, you are not allowed to access this page.');
        }
        
        global $wpdb;
        $exams_table = self::table_exams();
        $course_mapping_table = self::table_course_mapping();

        $exam = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $exams_table WHERE id = %d",
            $exam_id
        ));

        if (!$exam) {
            wp_die('Exam not found');
        }

        // Get all courses
        $all_courses = get_posts(array(
            'post_type' => 'courses',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ));

        // Get mapped courses
        $mapped_course_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT course_id FROM $course_mapping_table WHERE exam_id = %d",
            $exam_id
        ));

        ?>
        <div class="wrap">
            <h1>Map Courses to: <?php echo esc_html($exam->exam_title); ?></h1>

            <form method="post" style="max-width: 500px;">
                <?php wp_nonce_field('ica_lms_map_exam_courses'); ?>
                <input type="hidden" name="exam_id" value="<?php echo esc_attr($exam_id); ?>">

                <h3>Select Courses:</h3>
                <div style="background: #f9f9f9; padding: 15px; border-radius: 5px;">
                    <?php if (empty($all_courses)) : ?>
                        <p>No courses found. Please create courses first.</p>
                    <?php else : ?>
                        <?php foreach ($all_courses as $course) : ?>
                            <p>
                                <label>
                                    <input type="checkbox" name="course_ids[]" value="<?php echo esc_attr($course->ID); ?>" 
                                        <?php checked(in_array($course->ID, $mapped_course_ids)); ?>>
                                    <?php echo esc_html($course->post_title); ?>
                                </label>
                            </p>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <p style="margin-top: 20px;">
                    <button type="submit" name="ica_map_courses" class="button button-primary">Save Mapping</button>
                    <a href="?page=ica-lms-exams" class="button">Cancel</a>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Handle form submissions
     */
    public static function handle_form_submission() {
        // Permission check for all submissions
        // Allow both manage_exams capability AND administrator role
        if (!current_user_can('manage_exams') && !current_user_can('manage_options')) {
            wp_die('Sorry, you are not allowed to perform this action.');
        }

        if (isset($_POST['ica_create_exam'])) {
            check_admin_referer('ica_lms_create_exam');
            self::create_exam($_POST);
        }

        if (isset($_POST['ica_update_exam'])) {
            check_admin_referer('ica_lms_edit_exam');
            self::update_exam($_POST);
        }

        if (isset($_POST['ica_map_courses'])) {
            check_admin_referer('ica_lms_map_exam_courses');
            self::map_exam_to_courses($_POST);
        }
    }

    /**
     * Create exam
     */
    public static function create_exam($data) {
        global $wpdb;
        $exams_table = self::table_exams();

        $result = $wpdb->insert(
            $exams_table,
            array(
                'created_by' => get_current_user_id(),
                'exam_title' => sanitize_text_field($data['exam_title']),
                'exam_description' => sanitize_textarea_field($data['exam_description'] ?? ''),
                'exam_type' => sanitize_text_field($data['exam_type']),
                'start_date' => sanitize_text_field($data['start_date']),
                'end_date' => sanitize_text_field($data['end_date']),
                'duration_hours' => (int) $data['duration_hours'],
                'total_marks' => (int) $data['total_marks'],
                'pass_marks' => (int) $data['pass_marks'],
                'status' => 'draft',
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s')
        );

        if ($result) {
            wp_redirect(add_query_arg('page', 'ica-lms-exams', admin_url('admin.php')));
            exit;
        }
    }

    /**
     * Update exam
     */
    public static function update_exam($data) {
        global $wpdb;
        $exams_table = self::table_exams();

        $wpdb->update(
            $exams_table,
            array(
                'exam_title' => sanitize_text_field($data['exam_title']),
                'exam_description' => sanitize_textarea_field($data['exam_description'] ?? ''),
                'exam_type' => sanitize_text_field($data['exam_type']),
                'start_date' => sanitize_text_field($data['start_date']),
                'end_date' => sanitize_text_field($data['end_date']),
                'duration_hours' => (int) $data['duration_hours'],
                'total_marks' => (int) $data['total_marks'],
                'pass_marks' => (int) $data['pass_marks'],
                'updated_at' => current_time('mysql'),
            ),
            array('id' => (int) $data['exam_id']),
            array('%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s'),
            array('%d')
        );

        wp_redirect(add_query_arg('page', 'ica-lms-exams', admin_url('admin.php')));
        exit;
    }

    /**
     * Map exam to courses
     */
    public static function map_exam_to_courses($data) {
        global $wpdb;
        $exam_id = (int) $data['exam_id'];
        $course_ids = isset($data['course_ids']) ? array_map('intval', $data['course_ids']) : array();
        $mapping_table = self::table_course_mapping();

        // Remove old mappings
        $wpdb->delete($mapping_table, array('exam_id' => $exam_id), array('%d'));

        // Add new mappings
        foreach ($course_ids as $course_id) {
            $wpdb->insert(
                $mapping_table,
                array(
                    'exam_id' => $exam_id,
                    'course_id' => $course_id,
                    'is_mandatory' => 1,
                    'created_at' => current_time('mysql'),
                ),
                array('%d', '%d', '%d', '%s')
            );
        }

        wp_redirect(add_query_arg(array('page' => 'ica-lms-exams', 'action' => 'map_courses', 'exam_id' => $exam_id), admin_url('admin.php')));
        exit;
    }

    /**
     * AJAX: Add question
     */
    public static function ajax_add_question() {
        check_ajax_referer('ica_lms_exam_nonce', 'nonce');

        if (!current_user_can('manage_exams') && !current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $exam_id = (int) $_POST['exam_id'];
        $question_text = sanitize_textarea_field($_POST['question_text']);
        $marks = (int) $_POST['marks'];
        $correct_option = (int) $_POST['correct_option'];

        if (!$question_text || !$marks || !$correct_option || $correct_option < 1 || $correct_option > 4) {
            wp_send_json_error('Invalid question data');
        }

        global $wpdb;
        $questions_table = self::table_questions();
        $options_table = self::table_options();

        // Get max order
        $max_order = $wpdb->get_var($wpdb->prepare(
            "SELECT MAX(question_order) FROM $questions_table WHERE exam_id = %d",
            $exam_id
        ));

        // Insert question
        $result = $wpdb->insert(
            $questions_table,
            array(
                'exam_id' => $exam_id,
                'question_text' => $question_text,
                'question_order' => ($max_order ?? 0) + 1,
                'marks' => $marks,
                'created_at' => current_time('mysql'),
            ),
            array('%d', '%s', '%d', '%d', '%s')
        );

        if (!$result) {
            wp_send_json_error('Failed to create question');
        }

        $question_id = $wpdb->insert_id;

        // Insert options
        for ($i = 1; $i <= 4; $i++) {
            $option_text = sanitize_textarea_field($_POST['option_' . $i]);
            $is_correct = ($i === $correct_option) ? 1 : 0;

            $wpdb->insert(
                $options_table,
                array(
                    'question_id' => $question_id,
                    'option_text' => $option_text,
                    'option_order' => $i,
                    'is_correct' => $is_correct,
                    'created_at' => current_time('mysql'),
                ),
                array('%d', '%s', '%d', '%d', '%s')
            );
        }

        wp_send_json_success('Question added successfully');
    }

    /**
     * AJAX: Edit question
     */
    public static function ajax_edit_question() {
        check_ajax_referer('ica_lms_exam_nonce', 'nonce');

        if (!current_user_can('manage_exams') && !current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $question_id = (int) $_POST['question_id'];
        $question_text = sanitize_textarea_field($_POST['question_text']);
        $marks = (int) $_POST['marks'];
        $correct_option = (int) $_POST['correct_option'];

        if (!$question_text || !$marks || !$correct_option || $correct_option < 1 || $correct_option > 4) {
            wp_send_json_error('Invalid question data');
        }

        global $wpdb;
        $questions_table = self::table_questions();
        $options_table = self::table_options();

        // Update question
        $result = $wpdb->update(
            $questions_table,
            array(
                'question_text' => $question_text,
                'marks' => $marks,
            ),
            array('id' => $question_id),
            array('%s', '%d'),
            array('%d')
        );

        if ($result === false) {
            wp_send_json_error('Failed to update question');
        }

        // Delete existing options
        $wpdb->delete(
            $options_table,
            array('question_id' => $question_id),
            array('%d')
        );

        // Insert new options
        for ($i = 1; $i <= 4; $i++) {
            $option_text = sanitize_textarea_field($_POST['option_' . $i]);
            $is_correct = ($i === $correct_option) ? 1 : 0;

            $wpdb->insert(
                $options_table,
                array(
                    'question_id' => $question_id,
                    'option_text' => $option_text,
                    'option_order' => $i,
                    'is_correct' => $is_correct,
                    'created_at' => current_time('mysql'),
                ),
                array('%d', '%s', '%d', '%d', '%s')
            );
        }

        wp_send_json_success('Question updated successfully');
    }

    /**
     * AJAX: Get question data for editing
     */
    public static function ajax_get_exam_question() {
        check_ajax_referer('ica_lms_exam_nonce', 'nonce');

        if (!current_user_can('manage_exams') && !current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $question_id = (int) $_POST['question_id'];

        global $wpdb;
        $questions_table = self::table_questions();
        $options_table = self::table_options();

        // Get question
        $question = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $questions_table WHERE id = %d",
            $question_id
        ));

        if (!$question) {
            wp_send_json_error('Question not found');
        }

        // Get options
        $options = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $options_table WHERE question_id = %d ORDER BY option_order ASC",
            $question_id
        ));

        // Format options as associative array by option_order
        $options_array = array();
        foreach ($options as $option) {
            $options_array[$option->option_order] = $option;
        }

        // Return question with options
        wp_send_json_success(array(
            'question_text' => $question->question_text,
            'marks' => $question->marks,
            'options' => $options_array
        ));
    }

    /**
     * AJAX: Bulk import questions
     */
    public static function ajax_bulk_import_questions() {
        check_ajax_referer('ica_lms_exam_nonce', 'nonce');

        if (!current_user_can('manage_exams') && !current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error('No file uploaded');
        }

        $exam_id = (int) $_POST['exam_id'];
        $file = $_FILES['csv_file']['tmp_name'];

        if (!is_file($file)) {
            wp_send_json_error('File error');
        }

        global $wpdb;
        $questions_table = self::table_questions();
        $options_table = self::table_options();

        $count = 0;
        $errors = 0;

        if (($handle = fopen($file, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, '|')) !== false) {
                if (count($row) < 7) {
                    $errors++;
                    continue;
                }

                $question_text = trim($row[0]);
                $options = array(trim($row[1]), trim($row[2]), trim($row[3]), trim($row[4]));
                $correct_option = (int) trim($row[5]);
                $marks = (int) trim($row[6]);

                if (!$question_text || $correct_option < 1 || $correct_option > 4 || $marks < 1) {
                    $errors++;
                    continue;
                }

                // Get max order
                $max_order = $wpdb->get_var($wpdb->prepare(
                    "SELECT MAX(question_order) FROM $questions_table WHERE exam_id = %d",
                    $exam_id
                ));

                // Insert question
                $result = $wpdb->insert(
                    $questions_table,
                    array(
                        'exam_id' => $exam_id,
                        'question_text' => $question_text,
                        'question_order' => ($max_order ?? 0) + 1,
                        'marks' => $marks,
                        'created_at' => current_time('mysql'),
                    ),
                    array('%d', '%s', '%d', '%d', '%s')
                );

                if ($result) {
                    $question_id = $wpdb->insert_id;

                    // Insert options
                    for ($i = 0; $i < 4; $i++) {
                        $is_correct = ($i + 1 === $correct_option) ? 1 : 0;
                        $wpdb->insert(
                            $options_table,
                            array(
                                'question_id' => $question_id,
                                'option_text' => $options[$i],
                                'option_order' => $i + 1,
                                'is_correct' => $is_correct,
                                'created_at' => current_time('mysql'),
                            ),
                            array('%d', '%s', '%d', '%d', '%s')
                        );
                    }

                    $count++;
                }
            }
            fclose($handle);
        }

        wp_send_json_success("Imported {$count} questions. Errors: {$errors}");
    }

    /**
     * AJAX: Delete question
     */
    public static function ajax_delete_exam_question() {
        check_ajax_referer('ica_lms_exam_nonce', 'nonce');

        if (!current_user_can('manage_exams') && !current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $question_id = (int) $_POST['question_id'];

        global $wpdb;
        $questions_table = self::table_questions();

        $result = $wpdb->delete(
            $questions_table,
            array('id' => $question_id),
            array('%d')
        );

        if ($result) {
            wp_send_json_success('Question deleted');
        } else {
            wp_send_json_error('Failed to delete question');
        }
    }
}
