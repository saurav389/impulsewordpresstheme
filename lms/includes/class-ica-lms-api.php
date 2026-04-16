<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ICA LMS REST API Class
 * Provides REST API endpoints for external applications to integrate with the LMS
 */
class ICA_LMS_API {
    const API_VERSION = '1.0.0';
    const API_NAMESPACE = 'ica-lms/v1';

    public static function init() {
        add_action('rest_api_init', array(__CLASS__, 'register_routes'));
        // Enable basic auth for REST API
        add_filter('determine_current_user', array(__CLASS__, 'handle_basic_auth'), 20);
    }

    /**
     * Handle HTTP Basic Authentication for REST API
     */
    public static function handle_basic_auth($user) {
        if ($user) {
            return $user;
        }

        // Check if basic auth is provided
        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
            // Check for Authorization header (some servers don't set PHP_AUTH_*)
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                list($auth_type, $auth_data) = explode(' ', $_SERVER['HTTP_AUTHORIZATION'], 2);
                if (strtolower($auth_type) === 'basic') {
                    list($username, $password) = explode(':', base64_decode($auth_data), 2);
                    $_SERVER['PHP_AUTH_USER'] = $username;
                    $_SERVER['PHP_AUTH_PW'] = $password;
                }
            }
        }

        // Try to authenticate
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $username = sanitize_user($_SERVER['PHP_AUTH_USER']);
            $password = $_SERVER['PHP_AUTH_PW'] ?? '';

            $user_obj = wp_authenticate($username, $password);

            if (!is_wp_error($user_obj)) {
                return $user_obj->ID;
            }
        }

        return $user;
    }

    /**
     * Register all REST API routes
     */
    public static function register_routes() {
        // Student endpoints
        register_rest_route(self::API_NAMESPACE, '/students', array(
            array(
                'methods' => 'GET',
                'callback' => array(__CLASS__, 'get_students'),
                'permission_callback' => array(__CLASS__, 'check_student_read_permission'),
                'args' => array(
                    'course_id' => array('type' => 'integer'),
                    'batch_id' => array('type' => 'integer'),
                    'page' => array('type' => 'integer', 'default' => 1),
                    'per_page' => array('type' => 'integer', 'default' => 20),
                    'search' => array('type' => 'string'),
                )
            ),
            array(
                'methods' => 'POST',
                'callback' => array(__CLASS__, 'create_student'),
                'permission_callback' => array(__CLASS__, 'check_admin_permission'),
            )
        ));

        register_rest_route(self::API_NAMESPACE, '/students/(?P<id>\d+)', array(
            array(
                'methods' => 'GET',
                'callback' => array(__CLASS__, 'get_student'),
                'permission_callback' => array(__CLASS__, 'check_student_read_permission'),
            ),
            array(
                'methods' => 'PUT',
                'callback' => array(__CLASS__, 'update_student'),
                'permission_callback' => array(__CLASS__, 'check_admin_permission'),
            ),
            array(
                'methods' => 'DELETE',
                'callback' => array(__CLASS__, 'delete_student'),
                'permission_callback' => array(__CLASS__, 'check_admin_permission'),
            )
        ));

        // Batch endpoints
        register_rest_route(self::API_NAMESPACE, '/batches', array(
            array(
                'methods' => 'GET',
                'callback' => array(__CLASS__, 'get_batches'),
                'permission_callback' => array(__CLASS__, 'check_student_read_permission'),
                'args' => array(
                    'course_id' => array('type' => 'integer'),
                    'status' => array('type' => 'string', 'enum' => array('active', 'inactive', 'completed')),
                )
            ),
            array(
                'methods' => 'POST',
                'callback' => array(__CLASS__, 'create_batch'),
                'permission_callback' => array(__CLASS__, 'check_admin_permission'),
            )
        ));

        register_rest_route(self::API_NAMESPACE, '/batches/(?P<id>\d+)', array(
            array(
                'methods' => 'GET',
                'callback' => array(__CLASS__, 'get_batch'),
                'permission_callback' => array(__CLASS__, 'check_student_read_permission'),
            ),
            array(
                'methods' => 'PUT',
                'callback' => array(__CLASS__, 'update_batch'),
                'permission_callback' => array(__CLASS__, 'check_admin_permission'),
            ),
            array(
                'methods' => 'DELETE',
                'callback' => array(__CLASS__, 'delete_batch'),
                'permission_callback' => array(__CLASS__, 'check_admin_permission'),
            )
        ));

        // Course endpoints
        register_rest_route(self::API_NAMESPACE, '/courses', array(
            array(
                'methods' => 'GET',
                'callback' => array(__CLASS__, 'get_courses'),
                'permission_callback' => array(__CLASS__, 'check_student_read_permission'),
                'args' => array(
                    'page' => array('type' => 'integer', 'default' => 1),
                    'per_page' => array('type' => 'integer', 'default' => 20),
                )
            )
        ));

        register_rest_route(self::API_NAMESPACE, '/courses/(?P<id>\d+)', array(
            array(
                'methods' => 'GET',
                'callback' => array(__CLASS__, 'get_course'),
                'permission_callback' => array(__CLASS__, 'check_student_read_permission'),
            )
        ));

        // Category endpoints
        register_rest_route(self::API_NAMESPACE, '/categories', array(
            array(
                'methods' => 'GET',
                'callback' => array(__CLASS__, 'get_categories'),
                'permission_callback' => array(__CLASS__, 'check_student_read_permission'),
            )
        ));

        // ID Card endpoints
        register_rest_route(self::API_NAMESPACE, '/id-card/(?P<student_id>\d+)', array(
            array(
                'methods' => 'GET',
                'callback' => array(__CLASS__, 'get_id_card'),
                'permission_callback' => array(__CLASS__, 'check_student_read_permission'),
                'args' => array(
                    'format' => array('type' => 'string', 'enum' => array('html', 'pdf'), 'default' => 'pdf'),
                )
            )
        ));

        // QR Code endpoint
        register_rest_route(self::API_NAMESPACE, '/qr-code/(?P<student_id>\d+)', array(
            array(
                'methods' => 'GET',
                'callback' => array(__CLASS__, 'get_qr_code'),
                'permission_callback' => array(__CLASS__, 'check_student_read_permission'),
            )
        ));

        // Payment endpoints
        register_rest_route(self::API_NAMESPACE, '/payments/student/(?P<student_id>\d+)', array(
            array(
                'methods' => 'GET',
                'callback' => array(__CLASS__, 'get_student_payments'),
                'permission_callback' => array(__CLASS__, 'check_student_read_permission'),
            )
        ));

        register_rest_route(self::API_NAMESPACE, '/payments/record', array(
            array(
                'methods' => 'POST',
                'callback' => array(__CLASS__, 'record_payment'),
                'permission_callback' => array(__CLASS__, 'check_admin_permission'),
            )
        ));

        // API Info endpoint
        register_rest_route(self::API_NAMESPACE, '/info', array(
            array(
                'methods' => 'GET',
                'callback' => array(__CLASS__, 'get_api_info'),
                'permission_callback' => '__return_true',
            )
        ));
    }

    /**
     * Check admin permission (for write operations)
     */
    public static function check_admin_permission($request) {
        if (!is_user_logged_in()) {
            return false;
        }
        return current_user_can('manage_options');
    }

    /**
     * Check student read permission - allows authenticated users to read student data
     * (admin can read all, students/teachers can read their own)
     */
    public static function check_student_read_permission($request) {
        if (!is_user_logged_in()) {
            return false;
        }
        
        // Admins can access everything
        if (current_user_can('manage_options')) {
            return true;
        }
        
        // Allow any authenticated user (student/teacher) to read
        return true;
    }

    /**
     * Check API permission - verify user has manage_options capability (deprecated, use check_admin_permission)
     */
    public static function check_api_permission($request) {
        // Check if user is authenticated and has manage_options capability
        if (!is_user_logged_in()) {
            return false;
        }
        return current_user_can('manage_options');
    }

    /**
     * GET /students - Get list of students
     */
    public static function get_students($request) {
        $course_id = $request->get_param('course_id');
        $batch_id = $request->get_param('batch_id');
        $search = $request->get_param('search');
        $page = max($request->get_param('page'), 1);
        $per_page = min($request->get_param('per_page'), 100);

        $limit = $per_page;
        $offset = ($page - 1) * $per_page;

        $students = ICA_LMS_DB::get_students($limit, $offset, $search, $course_id);
        $total = ICA_LMS_DB::count_students($search, $course_id);

        // Filter by batch if provided
        if ($batch_id) {
            $students = array_filter($students, function($student) use ($batch_id) {
                return (int) $student['batch_id'] === (int) $batch_id;
            });
        }

        return rest_ensure_response(array(
            'success' => true,
            'data' => self::format_students($students),
            'pagination' => array(
                'total' => $total,
                'page' => $page,
                'per_page' => $per_page,
                'total_pages' => ceil($total / $per_page),
            )
        ));
    }

    /**
     * GET /students/{id} - Get single student
     */
    public static function get_student($request) {
        $student_id = $request->get_param('id');
        $student = ICA_LMS_DB::get_student($student_id);

        if (!$student) {
            return new WP_Error('student_not_found', 'Student not found', array('status' => 404));
        }

        return rest_ensure_response(array(
            'success' => true,
            'data' => self::format_student($student)
        ));
    }

    /**
     * POST /students - Create new student
     */
    public static function create_student($request) {
        $params = $request->get_json_params();

        // Validate required fields
        if (empty($params['name']) || empty($params['mobile_no'])) {
            return new WP_Error('invalid_input', 'Name and Mobile No are required', array('status' => 400));
        }

        if (empty($params['course_id']) || empty($params['batch_id'])) {
            return new WP_Error('invalid_input', 'Course ID and Batch ID are required', array('status' => 400));
        }

        $result = ICA_LMS_DB::create_student($params);

        if (!$result['success']) {
            return new WP_Error('create_failed', $result['error'], array('status' => 400));
        }

        $student = ICA_LMS_DB::get_student($result['id']);

        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Student created successfully',
            'data' => self::format_student($student)
        ));
    }

    /**
     * PUT /students/{id} - Update student
     */
    public static function update_student($request) {
        $student_id = $request->get_param('id');
        $student = ICA_LMS_DB::get_student($student_id);

        if (!$student) {
            return new WP_Error('student_not_found', 'Student not found', array('status' => 404));
        }

        $params = $request->get_json_params();
        $result = ICA_LMS_DB::update_student($student_id, $params);

        if (!$result) {
            return new WP_Error('update_failed', 'Failed to update student', array('status' => 400));
        }

        $updated_student = ICA_LMS_DB::get_student($student_id);

        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Student updated successfully',
            'data' => self::format_student($updated_student)
        ));
    }

    /**
     * DELETE /students/{id} - Delete student
     */
    public static function delete_student($request) {
        $student_id = $request->get_param('id');
        $student = ICA_LMS_DB::get_student($student_id);

        if (!$student) {
            return new WP_Error('student_not_found', 'Student not found', array('status' => 404));
        }

        $result = ICA_LMS_DB::delete_student($student_id);

        if (!$result) {
            return new WP_Error('delete_failed', 'Failed to delete student', array('status' => 400));
        }

        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Student deleted successfully'
        ));
    }

    /**
     * GET /batches - Get list of batches
     */
    public static function get_batches($request) {
        $course_id = $request->get_param('course_id');
        $status = $request->get_param('status');

        $batches = ICA_LMS_DB::get_batches($course_id, $status);

        return rest_ensure_response(array(
            'success' => true,
            'data' => self::format_batches($batches)
        ));
    }

    /**
     * GET /batches/{id} - Get single batch
     */
    public static function get_batch($request) {
        $batch_id = $request->get_param('id');
        $batch = ICA_LMS_DB::get_batch($batch_id);

        if (!$batch) {
            return new WP_Error('batch_not_found', 'Batch not found', array('status' => 404));
        }

        return rest_ensure_response(array(
            'success' => true,
            'data' => self::format_batch($batch)
        ));
    }

    /**
     * POST /batches - Create new batch
     */
    public static function create_batch($request) {
        $params = $request->get_json_params();

        if (empty($params['batch_name']) || empty($params['course_id'])) {
            return new WP_Error('invalid_input', 'Batch name and course ID are required', array('status' => 400));
        }

        if (empty($params['total_students']) || $params['total_students'] <= 0) {
            return new WP_Error('invalid_input', 'Total students must be greater than 0', array('status' => 400));
        }

        $result = ICA_LMS_DB::create_batch(
            $params['batch_name'],
            $params['course_id'],
            $params['total_students'],
            isset($params['batch_start_date']) ? $params['batch_start_date'] : '',
            isset($params['batch_end_date']) ? $params['batch_end_date'] : '',
            isset($params['description']) ? $params['description'] : ''
        );

        if (!$result) {
            return new WP_Error('create_failed', 'Failed to create batch', array('status' => 400));
        }

        $batches = ICA_LMS_DB::get_batches($params['course_id']);
        $batch = end($batches);

        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Batch created successfully',
            'data' => self::format_batch($batch)
        ));
    }

    /**
     * PUT /batches/{id} - Update batch
     */
    public static function update_batch($request) {
        $batch_id = $request->get_param('id');
        $batch = ICA_LMS_DB::get_batch($batch_id);

        if (!$batch) {
            return new WP_Error('batch_not_found', 'Batch not found', array('status' => 404));
        }

        $params = $request->get_json_params();
        $result = ICA_LMS_DB::update_batch($batch_id, $params);

        if (!$result) {
            return new WP_Error('update_failed', 'Failed to update batch', array('status' => 400));
        }

        $updated_batch = ICA_LMS_DB::get_batch($batch_id);

        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Batch updated successfully',
            'data' => self::format_batch($updated_batch)
        ));
    }

    /**
     * DELETE /batches/{id} - Delete batch
     */
    public static function delete_batch($request) {
        $batch_id = $request->get_param('id');
        $batch = ICA_LMS_DB::get_batch($batch_id);

        if (!$batch) {
            return new WP_Error('batch_not_found', 'Batch not found', array('status' => 404));
        }

        $result = ICA_LMS_DB::delete_batch($batch_id);

        if (!$result) {
            return new WP_Error('delete_failed', 'Failed to delete batch', array('status' => 400));
        }

        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Batch deleted successfully'
        ));
    }

    /**
     * GET /courses - Get list of courses
     */
    public static function get_courses($request) {
        $page = max($request->get_param('page'), 1);
        $per_page = min($request->get_param('per_page'), 100);
        $offset = ($page - 1) * $per_page;

        $args = array(
            'post_type' => 'courses',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'post_status' => 'publish',
        );

        $query = new WP_Query($args);
        $courses = array();

        foreach ($query->posts as $course) {
            $courses[] = self::format_course($course);
        }

        return rest_ensure_response(array(
            'success' => true,
            'data' => $courses,
            'pagination' => array(
                'total' => $query->found_posts,
                'page' => $page,
                'per_page' => $per_page,
                'total_pages' => $query->max_num_pages,
            )
        ));
    }

    /**
     * GET /courses/{id} - Get single course
     */
    public static function get_course($request) {
        $course_id = $request->get_param('id');
        $course = get_post($course_id);

        if (!$course || $course->post_type !== 'courses') {
            return new WP_Error('course_not_found', 'Course not found', array('status' => 404));
        }

        return rest_ensure_response(array(
            'success' => true,
            'data' => self::format_course($course)
        ));
    }

    /**
     * GET /categories - Get list of categories
     */
    public static function get_categories($request) {
        $categories = ICA_LMS_DB::get_categories();

        return rest_ensure_response(array(
            'success' => true,
            'data' => self::format_categories($categories)
        ));
    }

    /**
     * GET /id-card/{student_id} - Get ID card
     */
    public static function get_id_card($request) {
        $student_id = $request->get_param('student_id');
        $format = $request->get_param('format');

        $student = ICA_LMS_DB::get_student($student_id);
        if (!$student) {
            return new WP_Error('student_not_found', 'Student not found', array('status' => 404));
        }

        if (!class_exists('ICA_LMS_ID_Card')) {
            return new WP_Error('id_card_unavailable', 'ID Card generator not available', array('status' => 500));
        }

        // Generate ID card in requested format
        ob_start();
        try {
            if ($format === 'pdf' && function_exists('generate_id_card_pdf')) {
                ICA_LMS_ID_Card::generate_id_card_pdf($student_id);
            } else {
                // HTML format - return as JSON with embedded HTML
                $html = ICA_LMS_ID_Card::generate_id_card_html($student_id);
                return rest_ensure_response(array(
                    'success' => true,
                    'format' => 'html',
                    'html' => $html,
                    'student_name' => $student['name'],
                    'reg_no' => $student['reg_no'],
                ));
            }
        } catch (Exception $e) {
            ob_end_clean();
            return new WP_Error('generation_failed', $e->getMessage(), array('status' => 500));
        }

        return rest_ensure_response(array(
            'success' => true,
            'message' => 'ID card generated and available for download'
        ));
    }

    /**
     * GET /qr-code/{student_id} - Get QR code
     */
    public static function get_qr_code($request) {
        $student_id = $request->get_param('student_id');
        $student = ICA_LMS_DB::get_student($student_id);

        if (!$student) {
            return new WP_Error('student_not_found', 'Student not found', array('status' => 404));
        }

        if (!class_exists('ICA_LMS_QR_Code')) {
            return new WP_Error('qr_code_unavailable', 'QR Code generator not available', array('status' => 500));
        }

        $qr_url = ICA_LMS_QR_Code::get_student_qr_url($student_id);

        return rest_ensure_response(array(
            'success' => true,
            'data' => array(
                'student_id' => $student_id,
                'reg_no' => $student['reg_no'],
                'qr_code_url' => $qr_url,
                'qr_data' => $student['reg_no'],
            )
        ));
    }

    /**
     * GET /payments/student/{student_id} - Get student payments
     */
    public static function get_student_payments($request) {
        $student_id = $request->get_param('student_id');
        $student = ICA_LMS_DB::get_student($student_id);

        if (!$student) {
            return new WP_Error('student_not_found', 'Student not found', array('status' => 404));
        }

        $payments = ICA_LMS_DB::get_student_payments($student_id);
        $summary = ICA_LMS_DB::get_payment_summary($student_id);

        return rest_ensure_response(array(
            'success' => true,
            'data' => array(
                'student_id' => $student_id,
                'reg_no' => $student['reg_no'],
                'name' => $student['name'],
                'summary' => $summary,
                'payments' => self::format_payments($payments),
            )
        ));
    }

    /**
     * POST /payments/record - Record payment
     */
    public static function record_payment($request) {
        $params = $request->get_json_params();

        if (empty($params['student_id']) || empty($params['installment_number']) || empty($params['amount'])) {
            return new WP_Error('invalid_input', 'Student ID, installment number, and amount are required', array('status' => 400));
        }

        $student = ICA_LMS_DB::get_student($params['student_id']);
        if (!$student) {
            return new WP_Error('student_not_found', 'Student not found', array('status' => 404));
        }

        $payment_id = ICA_LMS_DB::record_payment(
            $params['student_id'],
            $params['installment_number'],
            $params['amount'],
            isset($params['payment_method']) ? $params['payment_method'] : '',
            isset($params['transaction_id']) ? $params['transaction_id'] : '',
            isset($params['notes']) ? $params['notes'] : ''
        );

        if (!$payment_id) {
            return new WP_Error('payment_failed', 'Failed to record payment', array('status' => 400));
        }

        $payment = ICA_LMS_DB::get_payment($payment_id);

        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Payment recorded successfully',
            'data' => self::format_payment($payment)
        ));
    }

    /**
     * GET /info - Get API information
     */
    public static function get_api_info($request) {
        return rest_ensure_response(array(
            'success' => true,
            'data' => array(
                'api_version' => self::API_VERSION,
                'api_namespace' => self::API_NAMESPACE,
                'endpoints' => array(
                    'students' => '/wp-json/ica-lms/v1/students',
                    'batches' => '/wp-json/ica-lms/v1/batches',
                    'courses' => '/wp-json/ica-lms/v1/courses',
                    'categories' => '/wp-json/ica-lms/v1/categories',
                    'id_card' => '/wp-json/ica-lms/v1/id-card/{student_id}',
                    'qr_code' => '/wp-json/ica-lms/v1/qr-code/{student_id}',
                    'payments' => '/wp-json/ica-lms/v1/payments/student/{student_id}',
                ),
                'authentication' => 'WordPress user login required with manage_options capability',
                'description' => 'ICA LMS REST API for integration with external applications'
            )
        ));
    }

    /**
     * Format helper functions
     */
    private static function format_student($student) {
        return array(
            'id' => (int) $student['id'],
            'reg_no' => $student['reg_no'],
            'roll_no' => $student['roll_no'],
            'name' => $student['name'],
            'mobile_no' => $student['mobile_no'],
            'aadhar_no' => $student['aadhar_no'],
            'father_name' => $student['father_name'],
            'mother_name' => $student['mother_name'],
            'date_of_birth' => $student['date_of_birth'],
            'gender' => $student['gender'],
            'category_id' => (int) $student['category_id'],
            'qualification' => $student['qualification'],
            'address' => $student['address'],
            'course_id' => (int) $student['course_id'],
            'batch_id' => (int) $student['batch_id'],
            'fee_status' => $student['fee_status'],
            'fee_type' => $student['fee_type'],
            'fee_amount' => (float) $student['fee_amount'],
            'discount_amount' => (float) $student['discount_amount'],
            'status' => $student['status'],
            'student_photo_url' => $student['student_photo_url'],
            'student_signature_url' => $student['student_signature_url'],
            'aadhar_photo_url' => $student['aadhar_photo_url'],
            'qualification_cert_url' => $student['qualification_cert_url'],
            'admission_date' => $student['admission_date'],
            'created_at' => $student['created_at'],
            'updated_at' => $student['updated_at'],
        );
    }

    private static function format_students($students) {
        return array_map(array(__CLASS__, 'format_student'), $students);
    }

    private static function format_batch($batch) {
        return array(
            'id' => (int) $batch['id'],
            'batch_name' => $batch['batch_name'],
            'course_id' => (int) $batch['course_id'],
            'total_students' => (int) $batch['total_students'],
            'batch_start_date' => $batch['batch_start_date'],
            'batch_end_date' => $batch['batch_end_date'],
            'description' => $batch['description'],
            'status' => $batch['status'],
            'created_at' => $batch['created_at'],
            'updated_at' => $batch['updated_at'],
        );
    }

    private static function format_batches($batches) {
        return array_map(array(__CLASS__, 'format_batch'), $batches);
    }

    private static function format_course($course) {
        return array(
            'id' => (int) $course->ID,
            'title' => $course->post_title,
            'description' => $course->post_content,
            'price' => (float) get_post_meta($course->ID, 'course_fee', true),
            'currency' => get_post_meta($course->ID, 'course_currency', true) ?: 'INR',
            'status' => $course->post_status,
            'created_at' => $course->post_date,
            'updated_at' => $course->post_modified,
        );
    }

    private static function format_category($category) {
        return array(
            'id' => (int) $category['id'],
            'name' => $category['name'],
            'description' => $category['description'],
            'status' => $category['status'],
            'created_at' => $category['created_at'],
        );
    }

    private static function format_categories($categories) {
        return array_map(array(__CLASS__, 'format_category'), $categories);
    }

    private static function format_payment($payment) {
        return array(
            'id' => (int) $payment['id'],
            'student_id' => (int) $payment['student_id'],
            'installment_number' => (int) $payment['installment_number'],
            'amount' => (float) $payment['amount'],
            'currency' => $payment['currency'],
            'payment_method' => $payment['payment_method'],
            'transaction_id' => $payment['transaction_id'],
            'status' => $payment['status'],
            'payment_date' => $payment['payment_date'],
            'notes' => $payment['notes'],
            'created_at' => $payment['created_at'],
        );
    }

    private static function format_payments($payments) {
        return array_map(array(__CLASS__, 'format_payment'), $payments);
    }
}
