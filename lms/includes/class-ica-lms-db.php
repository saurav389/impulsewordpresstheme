<?php
if (!defined('ABSPATH')) {
    exit;
}

class ICA_LMS_DB {
    const DB_VERSION = '4.0.0';

    public static function maybe_install() {
        $installed = get_option('ica_lms_db_version');
        if ($installed === self::DB_VERSION) {
            return;
        }

        self::install();
        update_option('ica_lms_db_version', self::DB_VERSION);
    }

    public static function install() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();
        $enrollments = self::table_enrollments();
        $students = self::table_students();
        $teachers = self::table_teachers();
        $subjects = self::table_subjects();
        $teacher_subjects = self::table_teacher_subjects();
        $categories = self::table_categories();
        $batches = self::table_batches();
        $payments = self::table_payment_installments();

        $sql = "
        CREATE TABLE $enrollments (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            course_id BIGINT UNSIGNED NOT NULL,
            enrolled_at DATETIME NOT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'active',
            PRIMARY KEY (id),
            UNIQUE KEY user_course (user_id, course_id),
            KEY course_id (course_id),
            KEY user_id (user_id)
        ) $charset_collate;

        CREATE TABLE $categories (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY status (status)
        ) $charset_collate;

        CREATE TABLE $batches (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            batch_name VARCHAR(100) NOT NULL,
            course_id BIGINT UNSIGNED NOT NULL,
            description TEXT NULL,
            total_students INT NOT NULL DEFAULT 0,
            batch_start_date DATE NULL,
            batch_end_date DATE NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY batch_course (batch_name, course_id),
            KEY course_id (course_id),
            KEY status (status)
        ) $charset_collate;

        CREATE TABLE $students (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            wp_user_id BIGINT UNSIGNED NULL,
            reg_no VARCHAR(50) NOT NULL UNIQUE,
            course_id BIGINT UNSIGNED NOT NULL,
            batch_id BIGINT UNSIGNED NULL,
            roll_no VARCHAR(50) NOT NULL,
            name VARCHAR(255) NOT NULL,
            father_name VARCHAR(255) NULL,
            mother_name VARCHAR(255) NULL,
            date_of_birth DATE NULL,
            gender VARCHAR(20) NULL,
            category_id BIGINT UNSIGNED NULL,
            qualification VARCHAR(255) NULL,
            mobile_no VARCHAR(20) NOT NULL,
            aadhar_no VARCHAR(20) NULL,
            address TEXT NULL,
            student_photo_url TEXT NULL,
            student_signature_url TEXT NULL,
            aadhar_photo_url TEXT NULL,
            qualification_cert_url TEXT NULL,
            fee_status VARCHAR(30) NOT NULL DEFAULT 'pending',
            fee_type VARCHAR(30) NOT NULL DEFAULT 'one_time',
            installment_count INT DEFAULT 1,
            fee_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
            paid_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
            discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
            fee_currency VARCHAR(10) NOT NULL DEFAULT 'INR',
            admission_date DATETIME NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY reg_no_key (reg_no),
            KEY course_id (course_id),
            KEY batch_id (batch_id),
            KEY roll_no (roll_no),
            KEY mobile_no (mobile_no),
            KEY category_id (category_id),
            KEY status_key (status)
        ) $charset_collate;

        CREATE TABLE $teachers (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            wp_user_id BIGINT UNSIGNED NULL,
            teacher_id VARCHAR(50) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            father_name VARCHAR(255) NULL,
            gender VARCHAR(20) NULL,
            date_of_birth DATE NULL,
            mobile_no VARCHAR(10) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            aadhar_no VARCHAR(20) NULL,
            photo_url TEXT NULL,
            signature_url TEXT NULL,
            qualification VARCHAR(255) NULL,
            department VARCHAR(100) NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY teacher_id_key (teacher_id),
            UNIQUE KEY email_key (email),
            KEY mobile_no (mobile_no),
            KEY status_key (status)
        ) $charset_collate;

        CREATE TABLE $subjects (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            subject_code VARCHAR(50) NOT NULL UNIQUE,
            subject_name VARCHAR(255) NOT NULL UNIQUE,
            description TEXT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY subject_code_key (subject_code),
            KEY status_key (status)
        ) $charset_collate;

        CREATE TABLE $teacher_subjects (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            teacher_id BIGINT UNSIGNED NOT NULL,
            subject_id BIGINT UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY teacher_subject (teacher_id, subject_id),
            KEY teacher_id (teacher_id),
            KEY subject_id (subject_id),
            FOREIGN KEY (teacher_id) REFERENCES $teachers(id) ON DELETE CASCADE,
            FOREIGN KEY (subject_id) REFERENCES $subjects(id) ON DELETE CASCADE
        ) $charset_collate;

        CREATE TABLE $payments (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            student_id BIGINT UNSIGNED NOT NULL,
            installment_number INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            currency VARCHAR(10) NOT NULL DEFAULT 'INR',
            payment_date DATETIME NULL,
            payment_method VARCHAR(50) NULL,
            transaction_id VARCHAR(100) NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            notes TEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY student_id (student_id),
            KEY installment_number (installment_number),
            KEY payment_date (payment_date),
            KEY status (status),
            UNIQUE KEY student_installment (student_id, installment_number)
        ) $charset_collate;
        ";

        dbDelta($sql);
    }

    public static function table_enrollments() {
        global $wpdb;
        return $wpdb->prefix . 'ica_lms_enrollments';
    }

    public static function table_students() {
        global $wpdb;
        return $wpdb->prefix . 'ica_lms_students';
    }

    public static function table_teachers() {
        global $wpdb;
        return $wpdb->prefix . 'ica_lms_teachers';
    }

    public static function table_subjects() {
        global $wpdb;
        return $wpdb->prefix . 'ica_lms_subjects';
    }

    public static function table_teacher_subjects() {
        global $wpdb;
        return $wpdb->prefix . 'ica_lms_teacher_subjects';
    }

    public static function table_categories() {
        global $wpdb;
        return $wpdb->prefix . 'ica_lms_categories';
    }

    public static function table_batches() {
        global $wpdb;
        return $wpdb->prefix . 'ica_lms_batches';
    }

    public static function table_payment_installments() {
        global $wpdb;
        return $wpdb->prefix . 'ica_lms_payment_installments';
    }

    /**
     * Enroll a user in a course
     */
    public static function enroll($user_id, $course_id) {
        global $wpdb;
        $table = self::table_enrollments();

        if (self::is_enrolled($user_id, $course_id)) {
            return true;
        }

        $existing = self::get_enrollment($user_id, $course_id);
        if (!empty($existing)) {
            $result = $wpdb->update(
                $table,
                array(
                    'enrolled_at' => current_time('mysql'),
                    'status' => 'active',
                ),
                array('id' => (int) $existing['id']),
                array('%s', '%s'),
                array('%d')
            );

            return (bool) $result;
        }

        $result = $wpdb->insert(
            $table,
            array(
                'user_id' => (int) $user_id,
                'course_id' => (int) $course_id,
                'enrolled_at' => current_time('mysql'),
                'status' => 'active',
            ),
            array('%d', '%d', '%s', '%s')
        );

        return (bool) $result;
    }

    /**
     * Check if user is enrolled in a course
     */
    public static function is_enrolled($user_id, $course_id) {
        $enrollment = self::get_enrollment($user_id, $course_id);
        if (empty($enrollment)) {
            return false;
        }

        if (($enrollment['status'] ?? '') !== 'active') {
            return false;
        }

        $access_days = (int) get_post_meta((int) $course_id, '_ica_course_access_days', true);
        if ($access_days <= 0) {
            $access_days = 180;
        }

        $enrolled_timestamp = strtotime((string) $enrollment['enrolled_at']);
        if (!$enrolled_timestamp) {
            return false;
        }

        $expires_at = strtotime('+' . $access_days . ' days', $enrolled_timestamp);
        $now = current_time('timestamp');
        if ($expires_at !== false && $now > $expires_at) {
            self::mark_enrollment_status($user_id, $course_id, 'expired');
            return false;
        }

        return true;
    }

    /**
     * Get enrollment record
     */
    public static function get_enrollment($user_id, $course_id) {
        global $wpdb;
        $table = self::table_enrollments();

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE user_id = %d AND course_id = %d LIMIT 1",
                (int) $user_id,
                (int) $course_id
            ),
            ARRAY_A
        );
    }

    /**
     * Update enrollment status
     */
    public static function mark_enrollment_status($user_id, $course_id, $status) {
        global $wpdb;
        $table = self::table_enrollments();

        return (bool) $wpdb->update(
            $table,
            array('status' => sanitize_key($status)),
            array(
                'user_id' => (int) $user_id,
                'course_id' => (int) $course_id,
            ),
            array('%s'),
            array('%d', '%d')
        );
    }

    /**
     * Get user enrollments
     */
    public static function get_user_enrollments($user_id) {
        global $wpdb;
        $table = self::table_enrollments();

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT course_id, status, enrolled_at FROM $table WHERE user_id = %d ORDER BY enrolled_at DESC",
                (int) $user_id
            ),
            ARRAY_A
        );
    }

    /**
     * Get course enrollments count
     */
    public static function get_course_enrollment_count($course_id) {
        global $wpdb;
        $table = self::table_enrollments();

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE course_id = %d AND status = 'active'",
                (int) $course_id
            )
        );
    }

    /**
     * ==================== STUDENT MANAGEMENT ====================
     */

    /**
     * Generate auto registration number
     * Format: ICAL-YYYY-XXXXX
     */
    public static function generate_registration_number() {
        global $wpdb;
        $table = self::table_students();
        
        $year = date('Y');
        $prefix = 'ICAL-' . $year . '-';
        
        $last = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT reg_no FROM $table WHERE reg_no LIKE %s ORDER BY id DESC LIMIT 1",
                $prefix . '%'
            )
        );
        
        if (empty($last)) {
            $next_number = 1;
        } else {
            $parts = explode('-', $last);
            $current_number = isset($parts[2]) ? (int) $parts[2] : 0;
            $next_number = $current_number + 1;
        }
        
        return $prefix . str_pad($next_number, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Generate roll number based on batch (starts from 1 for each batch)
     */
    public static function generate_roll_number($batch_id) {
        global $wpdb;
        $table = self::table_students();
        
        // Get next roll number for this specific batch
        $next_num = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT MAX(CAST(roll_no AS UNSIGNED)) FROM $table WHERE batch_id = %d",
                (int) $batch_id
            )
        );
        
        // Start from 1 if no students in batch yet
        $next_num = (int) $next_num + 1;
        
        // Get batch name for formatting
        $batch = self::get_batch($batch_id);
        if (!$batch) {
            return str_pad($next_num, 4, '0', STR_PAD_LEFT);
        }
        
        return $batch['batch_name'] . '-' . str_pad($next_num, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new student
     */
    public static function create_student($data = array()) {
        global $wpdb;
        $students_table = self::table_students();
        
        if (empty($data['mobile_no']) || empty($data['name'])) {
            return array('success' => false, 'error' => 'Name and Mobile No are required');
        }
        
        // Check if table exists
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
            DB_NAME,
            str_replace($wpdb->prefix, '', $students_table)
        ));
        
        if (!$table_exists) {
            // Force database initialization
            self::maybe_install();
        }
        
        $reg_no = self::generate_registration_number();
        
        // Validate batch_id is provided
        if (empty($data['batch_id'])) {
            return array('success' => false, 'error' => 'Batch selection is required');
        }
        
        $batch_id = (int) $data['batch_id'];
        $batch = self::get_batch($batch_id);
        
        if (!$batch) {
            return array('success' => false, 'error' => 'Selected batch not found');
        }
        
        // Validate batch is active
        if ($batch['status'] !== 'active') {
            return array('success' => false, 'error' => 'Selected batch is not active');
        }
        
        // Check if we haven't exceeded total students limit
        $current_students = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$students_table} WHERE batch_id = %d",
                $batch_id
            )
        );
        $current_students = (int) $current_students;
        $total_students = (int) $batch['total_students'];
        
        if ($current_students >= $total_students) {
            return array('success' => false, 'error' => "Batch is full. Maximum {$total_students} students allowed.");
        }
        
        // Generate roll number for this batch
        $roll_no = self::generate_roll_number($batch_id);
        
        // Build insert data - use empty string instead of null for nullable fields
        $insert_data = array(
            'reg_no' => $reg_no,
            'roll_no' => $roll_no,
            'name' => sanitize_text_field($data['name']),
            'mobile_no' => sanitize_text_field($data['mobile_no']),
            'course_id' => isset($data['course_id']) ? (int) $data['course_id'] : 0,
            'batch_id' => isset($data['batch_id']) && !empty($data['batch_id']) ? (int) $data['batch_id'] : 0,
            'father_name' => isset($data['father_name']) ? sanitize_text_field($data['father_name']) : '',
            'mother_name' => isset($data['mother_name']) ? sanitize_text_field($data['mother_name']) : '',
            'date_of_birth' => isset($data['date_of_birth']) ? sanitize_text_field($data['date_of_birth']) : '',
            'gender' => isset($data['gender']) ? sanitize_text_field($data['gender']) : '',
            'category_id' => isset($data['category_id']) && !empty($data['category_id']) ? (int) $data['category_id'] : 0,
            'qualification' => isset($data['qualification']) ? sanitize_text_field($data['qualification']) : '',
            'aadhar_no' => isset($data['aadhar_no']) ? sanitize_text_field($data['aadhar_no']) : '',
            'address' => isset($data['address']) ? sanitize_textarea_field($data['address']) : '',
            'student_photo_url' => isset($data['student_photo_url']) ? esc_url_raw($data['student_photo_url']) : '',
            'student_signature_url' => isset($data['student_signature_url']) ? esc_url_raw($data['student_signature_url']) : '',
            'aadhar_photo_url' => isset($data['aadhar_photo_url']) ? esc_url_raw($data['aadhar_photo_url']) : '',
            'qualification_cert_url' => isset($data['qualification_cert_url']) ? esc_url_raw($data['qualification_cert_url']) : '',
            'fee_status' => isset($data['fee_status']) ? sanitize_key($data['fee_status']) : 'pending',
            'fee_type' => isset($data['fee_type']) ? sanitize_key($data['fee_type']) : 'one_time',
            'installment_count' => isset($data['installment_count']) ? (int) $data['installment_count'] : 1,
            'fee_amount' => isset($data['fee_amount']) ? (float) $data['fee_amount'] : 0,
            'discount_amount' => isset($data['discount_amount']) ? (float) $data['discount_amount'] : 0,
            'fee_currency' => isset($data['fee_currency']) ? sanitize_text_field($data['fee_currency']) : 'INR',
            'admission_date' => current_time('mysql'),
            'status' => 'active',
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        );
        
        // Simpler approach - let wpdb handle formatting automatically
        $result = $wpdb->insert($students_table, $insert_data);
        
        if ($result === false) {
            // Log error for debugging
            error_log('ICA LMS Student Insert Error: ' . $wpdb->last_error);
            error_log('Table: ' . $students_table);
            error_log('Data: ' . print_r($insert_data, true));
            return array('success' => false, 'error' => 'Database error: ' . $wpdb->last_error);
        }
        
        $student_id = $wpdb->insert_id;
        $reg_no = $insert_data['reg_no'];
        $mobile_no = $insert_data['mobile_no'];
        
        // Create WordPress user for student
        // Username = registration number, Password = mobile number
        $user_email = strtolower(str_replace(' ', '.', $insert_data['name'])) . '@ica-lms.local';
        
        // Check if user already exists with this username
        if (!username_exists($reg_no)) {
            $user_id = wp_create_user($reg_no, $mobile_no, $user_email);
            
            if (!is_wp_error($user_id)) {
                // Assign student role
                $user = new WP_User($user_id);
                $user->set_role('ica_lms_student');
                
                // Store WordPress user ID in student record
                $wpdb->update(
                    $students_table,
                    array('wp_user_id' => $user_id),
                    array('id' => $student_id),
                    array('%d'),
                    array('%d')
                );
                
                // Log for debugging
                error_log("ICA LMS: Created WordPress user ID {$user_id} for student {$reg_no}");
            } else {
                // Log error but don't fail the student creation
                error_log('ICA LMS: Failed to create WordPress user for student ' . $reg_no . ': ' . $user_id->get_error_message());
            }
        } else {
            error_log('ICA LMS: Username ' . $reg_no . ' already exists as WordPress user');
        }
        
        return array('success' => true, 'id' => $student_id, 'reg_no' => $reg_no);
    }

    /**
     * Get student by ID
     */
    public static function get_student($student_id) {
        global $wpdb;
        $table = self::table_students();
        
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", (int) $student_id),
            ARRAY_A
        );
    }

    /**
     * Get student by WordPress user ID
     */
    public static function get_student_by_user_id($user_id) {
        global $wpdb;
        $table = self::table_students();
        
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE wp_user_id = %d", (int) $user_id),
            ARRAY_A
        );
    }

    /**
     * Update student
     */
    public static function update_student($student_id, $data = array()) {
        global $wpdb;
        $table = self::table_students();
        
        $update_data = array();
        $format = array();
        
        $allowed_fields = array(
            'name', 'father_name', 'mother_name', 'date_of_birth', 'gender',
            'category_id', 'qualification', 'mobile_no', 'aadhar_no', 'address',
            'student_photo_url', 'student_signature_url', 'aadhar_photo_url',
            'qualification_cert_url', 'fee_status', 'fee_type', 'fee_amount', 'fee_currency', 'installment_count', 'discount_amount'
        );
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                if ($field === 'category_id' || $field === 'installment_count' || $field === 'fee_amount' || $field === 'discount_amount') {
                    $update_data[$field] = ($field === 'category_id' || $field === 'installment_count') ? (int) $data[$field] : (float) $data[$field];
                } elseif (strpos($field, '_url') !== false) {
                    $update_data[$field] = esc_url_raw($data[$field]);
                } elseif ($field === 'address') {
                    $update_data[$field] = sanitize_textarea_field($data[$field]);
                } else {
                    $update_data[$field] = sanitize_text_field($data[$field]);
                }
                $format[] = strpos($field, '_url') !== false || $field === 'address' ? '%s' : (($field === 'category_id' || $field === 'installment_count' || $field === 'fee_amount' || $field === 'discount_amount') ? '%d' : '%s');
            }
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        $update_data['updated_at'] = current_time('mysql');
        $format[] = '%s';
        
        return (bool) $wpdb->update(
            $table,
            $update_data,
            array('id' => (int) $student_id),
            $format,
            array('%d')
        );
    }

    /**
     * Get all students with pagination
     */
    public static function get_students($limit = 20, $offset = 0, $search = '', $course_id = 0) {
        global $wpdb;
        $table = self::table_students();
        
        $query = "SELECT * FROM $table WHERE 1=1";
        
        if (!empty($search)) {
            $query .= $wpdb->prepare(
                " AND (name LIKE %s OR reg_no LIKE %s OR mobile_no LIKE %s OR aadhar_no LIKE %s)",
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }
        
        if ($course_id > 0) {
            $query .= $wpdb->prepare(" AND course_id = %d", (int) $course_id);
        }
        
        $query .= " ORDER BY created_at DESC";
        $query .= $wpdb->prepare(" LIMIT %d OFFSET %d", (int) $limit, (int) $offset);
        
        return $wpdb->get_results($query, ARRAY_A);
    }

    /**
     * Count students with filters
     */
    public static function count_students($search = '', $course_id = 0) {
        global $wpdb;
        $table = self::table_students();
        
        $query = "SELECT COUNT(*) FROM $table WHERE 1=1";
        
        if (!empty($search)) {
            $query .= $wpdb->prepare(
                " AND (name LIKE %s OR reg_no LIKE %s OR mobile_no LIKE %s OR aadhar_no LIKE %s)",
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }
        
        if ($course_id > 0) {
            $query .= $wpdb->prepare(" AND course_id = %d", (int) $course_id);
        }
        
        return (int) $wpdb->get_var($query);
    }

    /**
     * Delete student
     */
    public static function delete_student($student_id) {
        global $wpdb;
        $table = self::table_students();
        
        // Get student record to find associated WordPress user
        $student = self::get_student($student_id);
        
        if ($student && !empty($student['wp_user_id'])) {
            // Delete the associated WordPress user
            $wp_user_id = (int) $student['wp_user_id'];
            $user = get_user_by('id', $wp_user_id);
            
            if ($user) {
                // Delete user and their posts/metadata
                require_once ABSPATH . 'wp-admin/includes/user.php';
                wp_delete_user($wp_user_id, NULL);
                error_log("ICA LMS: Deleted WordPress user {$wp_user_id} for student {$student_id}");
            }
        }
        
        return (bool) $wpdb->delete($table, array('id' => (int) $student_id), array('%d'));
    }

    /**
     * ==================== TEACHER MANAGEMENT ====================
     */

    /**
     * Generate auto teacher ID
     * Format: TEACH-YYYY-XXXXX
     */
    public static function generate_teacher_id() {
        global $wpdb;
        $table = self::table_teachers();
        
        $year = date('Y');
        $prefix = 'TEACH-' . $year . '-';
        
        $last = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT teacher_id FROM $table WHERE teacher_id LIKE %s ORDER BY id DESC LIMIT 1",
                $prefix . '%'
            )
        );
        
        if (empty($last)) {
            $next_number = 1;
        } else {
            $parts = explode('-', $last);
            $current_number = isset($parts[2]) ? (int) $parts[2] : 0;
            $next_number = $current_number + 1;
        }
        
        return $prefix . str_pad($next_number, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Create teacher
     */
    public static function create_teacher($data = array()) {
        global $wpdb;
        $teachers_table = self::table_teachers();
        
        if (empty($data['name']) || empty($data['mobile_no']) || empty($data['email'])) {
            return array('success' => false, 'error' => 'Name, Mobile No, and Email are required');
        }

        // Check if email already exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $teachers_table WHERE email = %s",
            $data['email']
        ));

        if ($existing) {
            return array('success' => false, 'error' => 'Email already exists');
        }

        // Check if mobile number already exists
        $existing_mobile = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $teachers_table WHERE mobile_no = %s",
            $data['mobile_no']
        ));

        if ($existing_mobile) {
            return array('success' => false, 'error' => 'Mobile number already exists');
        }

        $teacher_id = self::generate_teacher_id();
        
        $insert_data = array(
            'teacher_id' => $teacher_id,
            'name' => sanitize_text_field($data['name']),
            'mobile_no' => sanitize_text_field($data['mobile_no']),
            'email' => sanitize_email($data['email']),
            'father_name' => isset($data['father_name']) ? sanitize_text_field($data['father_name']) : '',
            'gender' => isset($data['gender']) ? sanitize_text_field($data['gender']) : '',
            'date_of_birth' => isset($data['date_of_birth']) ? sanitize_text_field($data['date_of_birth']) : '',
            'aadhar_no' => isset($data['aadhar_no']) ? sanitize_text_field($data['aadhar_no']) : '',
            'photo_url' => isset($data['photo_url']) ? esc_url_raw($data['photo_url']) : '',
            'signature_url' => isset($data['signature_url']) ? esc_url_raw($data['signature_url']) : '',
            'qualification' => isset($data['qualification']) ? sanitize_text_field($data['qualification']) : '',
            'department' => isset($data['department']) ? sanitize_text_field($data['department']) : '',
            'status' => 'active',
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        );

        $result = $wpdb->insert($teachers_table, $insert_data);

        if ($result === false) {
            error_log('ICA LMS Teacher Insert Error: ' . $wpdb->last_error);
            error_log('Table: ' . $teachers_table);
            error_log('Data: ' . print_r($insert_data, true));
            return array('success' => false, 'error' => 'Database error: ' . $wpdb->last_error);
        }

        $teacher_db_id = $wpdb->insert_id;

        // Assign subjects to teacher
        if (!empty($data['subject_ids'])) {
            self::bulk_assign_subjects($teacher_db_id, $data['subject_ids']);
        }

        // Create WordPress user for teacher
        $user_email = strtolower(str_replace(' ', '.', $insert_data['name'])) . '@ica-lms.local';
        
        if (!username_exists($teacher_id)) {
            $user_id = wp_create_user($teacher_id, $data['mobile_no'], $user_email);
            
            if (!is_wp_error($user_id)) {
                $user = new WP_User($user_id);
                $user->set_role('ica_lms_teacher');
                
                $wpdb->update(
                    $teachers_table,
                    array('wp_user_id' => $user_id),
                    array('id' => $teacher_db_id),
                    array('%d'),
                    array('%d')
                );
                
                error_log("ICA LMS: Created WordPress user ID {$user_id} for teacher {$teacher_id}");
            } else {
                error_log('ICA LMS: Failed to create WordPress user for teacher ' . $teacher_id . ': ' . $user_id->get_error_message());
            }
        } else {
            error_log('ICA LMS: Username ' . $teacher_id . ' already exists as WordPress user');
        }

        return array('success' => true, 'id' => $teacher_db_id, 'teacher_id' => $teacher_id);
    }

    /**
     * Get teacher by ID
     */
    public static function get_teacher($teacher_id) {
        global $wpdb;
        $table = self::table_teachers();
        
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", (int) $teacher_id),
            ARRAY_A
        );
    }

    /**
     * Get teacher by WordPress user ID
     */
    public static function get_teacher_by_user_id($user_id) {
        global $wpdb;
        $table = self::table_teachers();
        
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE wp_user_id = %d", (int) $user_id),
            ARRAY_A
        );
    }

    /**
     * Get teachers list
     */
    public static function get_teachers($limit = 20, $offset = 0, $search = '', $status = 'active') {
        global $wpdb;
        $table = self::table_teachers();
        
        $sql = "SELECT * FROM $table WHERE 1=1";
        
        if ($status) {
            $sql .= $wpdb->prepare(" AND status = %s", $status);
        }
        
        if (!empty($search)) {
            $sql .= $wpdb->prepare(
                " AND (name LIKE %s OR teacher_id LIKE %s OR mobile_no LIKE %s OR email LIKE %s)",
                '%' . $search . '%',
                '%' . $search . '%',
                '%' . $search . '%',
                '%' . $search . '%'
            );
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $results = $wpdb->get_results($wpdb->prepare($sql, $limit, $offset), ARRAY_A);
        
        return !empty($results) ? $results : array();
    }

    /**
     * Count teachers
     */
    public static function count_teachers($search = '', $status = 'active') {
        global $wpdb;
        $table = self::table_teachers();
        
        $sql = "SELECT COUNT(*) FROM $table WHERE 1=1";
        
        if ($status) {
            $sql .= $wpdb->prepare(" AND status = %s", $status);
        }
        
        if (!empty($search)) {
            $sql .= $wpdb->prepare(
                " AND (name LIKE %s OR teacher_id LIKE %s OR mobile_no LIKE %s OR email LIKE %s)",
                '%' . $search . '%',
                '%' . $search . '%',
                '%' . $search . '%',
                '%' . $search . '%'
            );
        }
        
        return (int) $wpdb->get_var($sql);
    }

    /**
     * Update teacher
     */
    public static function update_teacher($teacher_id, $data = array()) {
        global $wpdb;
        $table = self::table_teachers();
        
        $update_data = array();
        
        $allowed_fields = array(
            'name', 'father_name', 'gender', 'date_of_birth',
            'mobile_no', 'email', 'aadhar_no', 'photo_url', 'signature_url',
            'qualification', 'department', 'status'
        );
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                if (in_array($field, array('photo_url', 'signature_url'))) {
                    $update_data[$field] = esc_url_raw($data[$field]);
                } elseif ($field === 'email') {
                    $update_data[$field] = sanitize_email($data[$field]);
                } else {
                    $update_data[$field] = sanitize_text_field($data[$field]);
                }
            }
        }
        
        if (empty($update_data) && empty($data['subject_ids'])) {
            return true;
        }
        
        if (!empty($update_data)) {
            $update_data['updated_at'] = current_time('mysql');
            $wpdb->update(
                $table,
                $update_data,
                array('id' => (int) $teacher_id),
                array(),
                array('%d')
            );
        }
        
        // Update subject assignments if provided
        if (isset($data['subject_ids'])) {
            self::bulk_assign_subjects($teacher_id, $data['subject_ids']);
        }
        
        return true;
    }

    /**
     * Delete teacher
     */
    public static function delete_teacher($teacher_id) {
        global $wpdb;
        $table = self::table_teachers();
        
        // Get teacher record to find associated WordPress user
        $teacher = self::get_teacher($teacher_id);
        
        if ($teacher && !empty($teacher['wp_user_id'])) {
            $wp_user_id = (int) $teacher['wp_user_id'];
            $user = get_user_by('id', $wp_user_id);
            
            if ($user) {
                require_once ABSPATH . 'wp-admin/includes/user.php';
                wp_delete_user($wp_user_id, NULL);
                error_log("ICA LMS: Deleted WordPress user {$wp_user_id} for teacher {$teacher_id}");
            }
        }
        
        return (bool) $wpdb->delete($table, array('id' => (int) $teacher_id), array('%d'));
    }

    /**
     * ==================== SUBJECT MANAGEMENT ====================
     */

    /**
     * Create subject
     */
    public static function create_subject($subject_name, $subject_code = '', $description = '') {
        global $wpdb;
        $table = self::table_subjects();

        if (empty($subject_name)) {
            return array('success' => false, 'error' => 'Subject name is required');
        }

        if (empty($subject_code)) {
            $subject_code = strtoupper(str_replace(' ', '-', $subject_name));
        }

        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table WHERE subject_code = %s OR subject_name = %s",
            $subject_code,
            $subject_name
        ));

        if ($existing) {
            return array('success' => false, 'error' => 'Subject already exists');
        }

        $insert_data = array(
            'subject_code' => sanitize_text_field($subject_code),
            'subject_name' => sanitize_text_field($subject_name),
            'description' => sanitize_textarea_field($description),
            'status' => 'active',
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        );

        $result = $wpdb->insert($table, $insert_data);

        if ($result === false) {
            error_log('ICA LMS Subject Insert Error: ' . $wpdb->last_error);
            return array('success' => false, 'error' => 'Database error: ' . $wpdb->last_error);
        }

        return array('success' => true, 'id' => $wpdb->insert_id);
    }

    /**
     * Get all subjects
     */
    public static function get_subjects($status = 'active') {
        global $wpdb;
        $table = self::table_subjects();

        $sql = "SELECT * FROM $table";

        if ($status) {
            $sql .= $wpdb->prepare(" WHERE status = %s", $status);
        }

        $sql .= " ORDER BY subject_name ASC";

        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Get subject by ID
     */
    public static function get_subject($subject_id) {
        global $wpdb;
        $table = self::table_subjects();

        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", (int) $subject_id),
            ARRAY_A
        );
    }

    /**
     * Get subjects list with pagination
     */
    public static function get_subjects_list($limit = 20, $offset = 0, $search = '', $status = 'active') {
        global $wpdb;
        $table = self::table_subjects();

        $sql = "SELECT * FROM $table WHERE 1=1";

        if ($status) {
            $sql .= $wpdb->prepare(" AND status = %s", $status);
        }

        if (!empty($search)) {
            $sql .= $wpdb->prepare(
                " AND (subject_name LIKE %s OR subject_code LIKE %s OR description LIKE %s)",
                '%' . $search . '%',
                '%' . $search . '%',
                '%' . $search . '%'
            );
        }

        $sql .= " ORDER BY subject_name ASC LIMIT %d OFFSET %d";
        $results = $wpdb->get_results($wpdb->prepare($sql, $limit, $offset), ARRAY_A);

        return !empty($results) ? $results : array();
    }

    /**
     * Count subjects
     */
    public static function count_subjects($search = '', $status = 'active') {
        global $wpdb;
        $table = self::table_subjects();

        $sql = "SELECT COUNT(*) FROM $table WHERE 1=1";

        if ($status) {
            $sql .= $wpdb->prepare(" AND status = %s", $status);
        }

        if (!empty($search)) {
            $sql .= $wpdb->prepare(
                " AND (subject_name LIKE %s OR subject_code LIKE %s OR description LIKE %s)",
                '%' . $search . '%',
                '%' . $search . '%',
                '%' . $search . '%'
            );
        }

        return (int) $wpdb->get_var($sql);
    }

    /**
     * Update subject
     */
    public static function update_subject($subject_id, $subject_name, $subject_code = '', $description = '', $status = '') {
        global $wpdb;
        $table = self::table_subjects();

        $update_data = array();

        if (!empty($subject_name)) {
            $update_data['subject_name'] = sanitize_text_field($subject_name);
        }

        if (!empty($subject_code)) {
            $update_data['subject_code'] = sanitize_text_field($subject_code);
        }

        if (isset($description)) {
            $update_data['description'] = sanitize_textarea_field($description);
        }

        if (!empty($status)) {
            $update_data['status'] = sanitize_text_field($status);
        }

        if (empty($update_data)) {
            return true;
        }

        $update_data['updated_at'] = current_time('mysql');

        return $wpdb->update(
            $table,
            $update_data,
            array('id' => (int) $subject_id),
            array(),
            array('%d')
        ) !== false;
    }

    /**
     * Delete subject
     */
    public static function delete_subject($subject_id) {
        global $wpdb;
        $table = self::table_subjects();

        return (bool) $wpdb->delete($table, array('id' => (int) $subject_id), array('%d'));
    }

    /**
     * Assign subject to teacher
     */
    public static function assign_subject_to_teacher($teacher_id, $subject_id) {
        global $wpdb;
        $table = self::table_teacher_subjects();

        // Check if already assigned
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table WHERE teacher_id = %d AND subject_id = %d",
            (int) $teacher_id,
            (int) $subject_id
        ));

        if ($existing) {
            return true;
        }

        $result = $wpdb->insert(
            $table,
            array(
                'teacher_id' => (int) $teacher_id,
                'subject_id' => (int) $subject_id,
                'created_at' => current_time('mysql'),
            )
        );

        return (bool) $result;
    }

    /**
     * Remove subject from teacher
     */
    public static function remove_subject_from_teacher($teacher_id, $subject_id) {
        global $wpdb;
        $table = self::table_teacher_subjects();

        return (bool) $wpdb->delete(
            $table,
            array(
                'teacher_id' => (int) $teacher_id,
                'subject_id' => (int) $subject_id,
            )
        );
    }

    /**
     * Get teacher subjects
     */
    public static function get_teacher_subjects($teacher_id) {
        global $wpdb;
        $ts_table = self::table_teacher_subjects();
        $s_table = self::table_subjects();

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT s.* FROM $s_table s 
                 INNER JOIN $ts_table ts ON s.id = ts.subject_id 
                 WHERE ts.teacher_id = %d ORDER BY s.subject_name ASC",
                (int) $teacher_id
            ),
            ARRAY_A
        );

        return !empty($results) ? $results : array();
    }

    /**
     * Bulk assign subjects to teacher
     */
    public static function bulk_assign_subjects($teacher_id, $subject_ids = array()) {
        global $wpdb;
        $table = self::table_teacher_subjects();

        // Delete existing subject assignments
        $wpdb->delete($table, array('teacher_id' => (int) $teacher_id));

        // Insert new assignments
        if (empty($subject_ids)) {
            return true;
        }

        foreach ($subject_ids as $subject_id) {
            $wpdb->insert(
                $table,
                array(
                    'teacher_id' => (int) $teacher_id,
                    'subject_id' => (int) $subject_id,
                    'created_at' => current_time('mysql'),
                )
            );
        }

        return true;
    }

    /**
     * ==================== CATEGORY MANAGEMENT ====================
     */

    /**
     * Create category
     */
    public static function create_category($name, $description = '') {
        global $wpdb;
        $table = self::table_categories();
        
        return (bool) $wpdb->insert(
            $table,
            array(
                'name' => sanitize_text_field($name),
                'description' => sanitize_textarea_field($description),
                'status' => 'active',
                'created_at' => current_time('mysql'),
            )
        );
    }

    /**
     * Get all categories
     */
    public static function get_categories($status = 'active') {
        global $wpdb;
        $table = self::table_categories();
        
        $query = "SELECT * FROM $table";
        if (!empty($status)) {
            $query .= $wpdb->prepare(" WHERE status = %s", $status);
        }
        $query .= " ORDER BY name ASC";
        
        return $wpdb->get_results($query, ARRAY_A);
    }

    /**
     * Get category by ID
     */
    public static function get_category($category_id) {
        global $wpdb;
        $table = self::table_categories();
        
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", (int) $category_id),
            ARRAY_A
        );
    }

    /**
     * Delete category
     */
    public static function delete_category($category_id) {
        global $wpdb;
        $table = self::table_categories();
        
        return (bool) $wpdb->delete($table, array('id' => (int) $category_id), array('%d'));
    }

    /**
     * Create a new batch
     */
    public static function create_batch($batch_name, $course_id, $total_students = 0, $batch_start_date = '', $batch_end_date = '', $description = '') {
        global $wpdb;
        $table = self::table_batches();
        
        if (empty($batch_name) || empty($course_id)) {
            return false;
        }
        
        // Check if batch already exists for this course
        $existing = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $table WHERE batch_name = %s AND course_id = %d",
                sanitize_text_field($batch_name),
                (int) $course_id
            )
        );
        
        if ($existing) {
            return false; // Batch already exists
        }
        
        return $wpdb->insert(
            $table,
            array(
                'batch_name' => sanitize_text_field($batch_name),
                'course_id' => (int) $course_id,
                'total_students' => (int) $total_students,
                'batch_start_date' => !empty($batch_start_date) ? sanitize_text_field($batch_start_date) : NULL,
                'batch_end_date' => !empty($batch_end_date) ? sanitize_text_field($batch_end_date) : NULL,
                'description' => !empty($description) ? sanitize_textarea_field($description) : '',
                'status' => 'active',
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ),
            array('%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s')
        );
    }

    /**
     * Update batch
     */
    public static function update_batch($batch_id, $data = array()) {
        global $wpdb;
        $table = self::table_batches();
        
        $update_data = array();
        $format = array();
        
        $allowed_fields = array('batch_name', 'description', 'total_students', 'batch_start_date', 'batch_end_date', 'status');
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                if ($field === 'total_students') {
                    $update_data[$field] = (int) $data[$field];
                    $format[] = '%d';
                } elseif ($field === 'batch_name') {
                    $update_data[$field] = sanitize_text_field($data[$field]);
                    $format[] = '%s';
                } elseif ($field === 'description') {
                    $update_data[$field] = sanitize_textarea_field($data[$field]);
                    $format[] = '%s';
                } elseif ($field === 'status') {
                    $update_data[$field] = sanitize_key($data[$field]);
                    $format[] = '%s';
                } else {
                    $update_data[$field] = sanitize_text_field($data[$field]);
                    $format[] = '%s';
                }
            }
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        $update_data['updated_at'] = current_time('mysql');
        $format[] = '%s';
        
        return (bool) $wpdb->update(
            $table,
            $update_data,
            array('id' => (int) $batch_id),
            $format,
            array('%d')
        );
    }

    /**
     * Get all batches for a course
     */
    public static function get_batches($course_id = 0, $status = 'active') {
        global $wpdb;
        $table = self::table_batches();
        
        $query = "SELECT * FROM $table";
        if (!empty($course_id)) {
            $query .= $wpdb->prepare(" WHERE course_id = %d", (int) $course_id);
            if (!empty($status)) {
                $query .= $wpdb->prepare(" AND status = %s", $status);
            }
        } elseif (!empty($status)) {
            $query .= $wpdb->prepare(" WHERE status = %s", $status);
        }
        $query .= " ORDER BY batch_name ASC";
        
        return $wpdb->get_results($query, ARRAY_A);
    }

    /**
     * Get batch by ID
     */
    public static function get_batch($batch_id) {
        global $wpdb;
        $table = self::table_batches();
        
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", (int) $batch_id),
            ARRAY_A
        );
    }

    /**
     * Delete batch
     */
    public static function delete_batch($batch_id) {
        global $wpdb;
        $table = self::table_batches();
        
        return (bool) $wpdb->delete($table, array('id' => (int) $batch_id), array('%d'));
    }

    /**
     * ==================== PAYMENT MANAGEMENT ====================
     */

    /**
     * Record a payment
     */
    public static function record_payment($student_id, $installment_number, $amount, $payment_method = '', $transaction_id = '', $notes = '') {
        global $wpdb;
        $table = self::table_payment_installments();
        $students_table = self::table_students();
        
        $result = $wpdb->insert(
            $table,
            array(
                'student_id' => (int) $student_id,
                'installment_number' => (int) $installment_number,
                'amount' => (float) $amount,
                'currency' => 'INR',
                'payment_method' => sanitize_text_field($payment_method),
                'transaction_id' => sanitize_text_field($transaction_id),
                'status' => 'completed',
                'payment_date' => current_time('mysql'),
                'notes' => sanitize_textarea_field($notes),
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ),
            array('%d', '%d', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if (!$result) {
            return false;
        }
        
        // Update paid_amount in student enrollments
        self::update_student_paid_amounts($student_id);
        
        return $wpdb->insert_id;
    }
    
    /**
     * Update paid_amount for all enrollments of a student
     */
    public static function update_student_paid_amounts($student_id) {
        global $wpdb;
        $students_table = self::table_students();
        $payments_table = self::table_payment_installments();
        
        // Get total paid amount for this student
        $total_paid = (float) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COALESCE(SUM(amount), 0) FROM $payments_table WHERE student_id = %d AND status = 'completed'",
                (int) $student_id
            )
        );
        
        // Get all enrollments for this student
        $enrollments = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, fee_amount FROM $students_table WHERE id = %d",
                (int) $student_id
            ),
            ARRAY_A
        );
        
        if (empty($enrollments)) {
            return;
        }
        
        // Get total fees for all enrollments
        $total_fees = array_reduce($enrollments, function($sum, $enrollment) {
            return $sum + (float) $enrollment['fee_amount'];
        }, 0);
        
        if ($total_fees <= 0) {
            return;
        }
        
        // Allocate paid_amount proportionally to each enrollment
        foreach ($enrollments as $enrollment) {
            $proportion = $enrollment['fee_amount'] / $total_fees;
            $allocated_paid = round($total_paid * $proportion, 2);
            
            // Determine fee_status based on paid amount
            $fee_status = ($allocated_paid >= $enrollment['fee_amount']) ? 'approved' : 'pending';
            
            $wpdb->update(
                $students_table,
                array(
                    'paid_amount' => $allocated_paid,
                    'fee_status' => $fee_status
                ),
                array('id' => (int) $enrollment['id']),
                array('%f', '%s'),
                array('%d')
            );
        }
    }

    /**
     * Get payment details for a student
     */
    public static function get_student_payments($student_id) {
        global $wpdb;
        $table = self::table_payment_installments();
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE student_id = %d ORDER BY installment_number ASC",
                (int) $student_id
            ),
            ARRAY_A
        );
    }

    /**
     * Get payment summary for a student
     */
    public static function get_payment_summary($student_id) {
        global $wpdb;
        $students_table = self::table_students();
        $payments_table = self::table_payment_installments();
        
        $student = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT fee_amount, installment_count FROM $students_table WHERE id = %d",
                (int) $student_id
            ),
            ARRAY_A
        );
        
        if (!$student) {
            return null;
        }
        
        $total_fee = (float) $student['fee_amount'];
        $installment_count = (int) $student['installment_count'];
        
        // Get paid amount
        $paid_amount = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COALESCE(SUM(amount), 0) FROM $payments_table WHERE student_id = %d AND status = 'completed'",
                (int) $student_id
            )
        );
        
        $paid_amount = (float) $paid_amount;
        $balance_amount = $total_fee - $paid_amount;
        
        return array(
            'total_fee' => $total_fee,
            'paid_amount' => $paid_amount,
            'balance_amount' => $balance_amount,
            'installment_count' => $installment_count,
            'per_installment' => $installment_count > 0 ? $total_fee / $installment_count : 0,
        );
    }

    /**
     * Get specific payment
     */
    public static function get_payment($payment_id) {
        global $wpdb;
        $table = self::table_payment_installments();
        
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", (int) $payment_id),
            ARRAY_A
        );
    }

    /**
     * Get course fee by course ID
     */
    public static function get_course_fee($course_id) {
        $course_id = (int) $course_id;
        
        // Verify course exists
        if (!get_post($course_id)) {
            error_log("ICA_LMS: Course ID $course_id not found");
            return array(
                'amount' => 0,
                'currency' => 'INR'
            );
        }
        
        // Try to get course fee - check both meta keys
        // First check for 'course_fee' (actual meta key used)
        $price = get_post_meta($course_id, 'course_fee', true);
        
        // If not found, try the prefixed version
        if (!$price || $price === '') {
            $price = get_post_meta($course_id, '_ica_course_price', true);
        }
        
        // Handle if price is an array (get first element)
        if (is_array($price) && !empty($price)) {
            $price = $price[0];
        }
        
        $currency = get_post_meta($course_id, '_ica_course_currency', true);
        if (!$currency || $currency === '') {
            $currency = get_post_meta($course_id, 'course_currency', true);
        }
        if (!$currency || $currency === '') {
            $currency = 'INR';
        }
        
        // Debug logging
        error_log("ICA_LMS: get_course_fee() - Course ID: $course_id, Price: " . var_export($price, true) . ", Currency: " . var_export($currency, true));
        
        // Ensure price is a proper float
        $amount = 0;
        if ($price !== '' && $price !== false && $price !== null) {
            $amount = (float) $price;
        }
        
        error_log("ICA_LMS: Final amount returned: $amount");
        
        return array(
            'amount' => $amount,
            'currency' => $currency
        );
    }

    /**
     * Debug: Check all metadata for a course
     */
    public static function debug_get_course_metadata($course_id) {
        $course_id = (int) $course_id;
        $course = get_post($course_id);
        
        if (!$course) {
            return array('error' => 'Course not found');
        }
        
        $all_meta = get_post_meta($course_id);
        
        return array(
            'course_id' => $course_id,
            'post_title' => $course->post_title,
            'post_type' => $course->post_type,
            'post_status' => $course->post_status,
            'all_meta' => $all_meta,
            '_ica_course_price' => get_post_meta($course_id, '_ica_course_price', true),
            '_ica_course_currency' => get_post_meta($course_id, '_ica_course_currency', true),
            '_ica_course_access_days' => get_post_meta($course_id, '_ica_course_access_days', true)
        );
    }
}

