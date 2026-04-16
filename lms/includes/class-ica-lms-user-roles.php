<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ICA LMS User Roles & Capabilities
 * Manages WordPress roles and capabilities for Admin, Teacher, and Student
 */
class ICA_LMS_User_Roles {
    const ADMIN_ROLE = 'ica_lms_admin';
    const TEACHER_ROLE = 'ica_lms_teacher';
    const STUDENT_ROLE = 'ica_lms_student';

    public static function init() {
        // Register roles on init hook with high priority to ensure they're available before admin_menu
        add_action('init', array(__CLASS__, 'register_roles'), 1);
        add_action('init', array(__CLASS__, 'ensure_role_capabilities'), 99);
        add_action('after_setup_theme', array(__CLASS__, 'ensure_role_capabilities'), 20);
        add_action('admin_init', array(__CLASS__, 'ensure_role_capabilities'));
    }
    
    /**
     * Force reset of all LMS roles (useful for troubleshooting)
     * Call via: do_action('ica_lms_force_reset_roles');
     */
    public static function force_reset_roles() {
        // Clear old roles completely
        remove_role(self::ADMIN_ROLE);
        remove_role(self::TEACHER_ROLE);
        remove_role(self::STUDENT_ROLE);
        
        // Reinitialize
        self::register_roles();
        self::ensure_role_capabilities();
        
        error_log('ICA LMS: Forced role reset completed');
    }

    /**
     * Ensure teacher and student roles have post capabilities
     * This handles the case where roles were created before post capabilities were added
     */
    public static function ensure_role_capabilities() {
        // For existing installations, ensure roles have all necessary capabilities
        $teacher_role = get_role(self::TEACHER_ROLE);
        if ($teacher_role) {
            $required_caps = array(
                'read', 'edit_posts', 'create_posts', 'delete_posts',
                'publish_posts', 'edit_published_posts', 'delete_published_posts'
            );
            foreach ($required_caps as $cap) {
                if (!isset($teacher_role->capabilities[$cap])) {
                    $teacher_role->add_cap($cap);
                }
            }
        }
        
        $student_role = get_role(self::STUDENT_ROLE);
        if ($student_role) {
            $required_caps = array(
                'read', 'edit_posts', 'create_posts', 'delete_posts',
                'publish_posts', 'edit_published_posts', 'delete_published_posts'
            );
            foreach ($required_caps as $cap) {
                if (!isset($student_role->capabilities[$cap])) {
                    $student_role->add_cap($cap);
                }
            }
        }
    }

    /**
     * Register all LMS roles and capabilities
     */
    public static function register_roles() {
        // Remove old roles if they exist to ensure clean registration
        remove_role(self::TEACHER_ROLE);
        remove_role(self::STUDENT_ROLE);
        
        // Admin Role - Full access to everything
        add_role(self::ADMIN_ROLE, __('LMS Admin'), array(
            // ICA LMS Capabilities
            'manage_lms' => true,
            'manage_courses' => true,
            'manage_students' => true,
            'manage_teachers' => true,
            'manage_batches' => true,
            'manage_payments' => true,
            'manage_tests' => true,
            'manage_modules' => true,
            'view_reports' => true,
            'view_analytics' => true,
            'download_files' => true,
            'export_data' => true,
            'import_data' => true,
            
            // WordPress Core Capabilities (delegated from manage_options)
            'manage_options' => true,
        ));

        // Teacher Role - Can create courses, modules, tests, and view student progress
        add_role(self::TEACHER_ROLE, __('LMS Teacher'), array(
            // Post Management - Standard WordPress behavior
            // Users can create posts and manage only their own
            'read' => true,
            'edit_posts' => true,
            'create_posts' => true,
            'delete_posts' => true,
            'publish_posts' => true,
            'edit_published_posts' => true,
            'delete_published_posts' => true,
            
            // Teaching Module Management
            'create_modules' => true,
            'edit_modules' => true,
            'delete_modules' => true,
            'edit_others_modules' => true,
            'delete_others_modules' => true,
            
            // Test & Examination Management
            'create_tests' => true,
            'edit_tests' => true,
            'delete_tests' => true,
            'edit_others_tests' => true,
            'delete_others_tests' => true,
            'grade_tests' => true,
            'view_test_results' => true,
            
            // Course Management (assigned courses only)
            'edit_courses' => true,
            'view_courses' => true,
            'manage_course_content' => true,
            
            // Student Progress & Performance
            'view_student_progress' => true,
            'view_student_performance' => true,
            'view_student_grades' => true,
            'view_student_attendance' => true,
            'export_student_reports' => true,
            
            // Resource Management
            'upload_resources' => true,
            'manage_resources' => true,
            'manage_assignments' => true,
            
            // General Capabilities
            'view_students' => true,
            'send_messages' => true,
            'download_files' => true,
        ));

        // Student Role - Can view courses, resources, and progress
        add_role(self::STUDENT_ROLE, __('LMS Student'), array(
            // Post Management - Standard WordPress behavior
            // Users can create posts and manage only their own
            'read' => true,
            'edit_posts' => true,
            'create_posts' => true,
            'delete_posts' => true,
            'publish_posts' => true,
            'edit_published_posts' => true,
            'delete_published_posts' => true,
            
            // Course Access
            'view_courses' => true,
            'view_course_content' => true,
            'access_course_materials' => true,
            
            // Learning Resources
            'view_modules' => true,
            'download_resources' => true,
            'watch_videos' => true,
            'read_notes' => true,
            
            // Testing & Assignments
            'take_tests' => true,
            'submit_assignments' => true,
            'view_test_results' => true,
            'view_grades' => true,
            
            // Progress & Performance Tracking
            'view_own_progress' => true,
            'view_own_performance' => true,
            'view_own_attendance' => true,
            
            // Account & Payment
            'view_own_profile' => true,
            'edit_own_profile' => true,
            'view_fee_history' => true,
            'view_invoice' => true,
        ));

        // Add capabilities to existing WordPress roles if needed
        $admin = get_role('administrator');
        if ($admin) {
            // Grant all LMS capabilities to WordPress administrators
            foreach (self::get_all_capabilities() as $cap) {
                $admin->add_cap($cap);
            }
        }
    }

    /**
     * Get all available capabilities
     */
    public static function get_all_capabilities() {
        return array(
            // Admin Only
            'manage_lms',
            'manage_courses',
            'manage_students',
            'manage_teachers',
            'manage_batches',
            'manage_payments',
            'manage_tests',
            'manage_modules',
            'view_reports',
            'view_analytics',
            'export_data',
            'import_data',
            
            // Teacher Capabilities
            'create_modules',
            'edit_modules',
            'delete_modules',
            'edit_others_modules',
            'delete_others_modules',
            'create_tests',
            'edit_tests',
            'delete_tests',
            'edit_others_tests',
            'delete_others_tests',
            'grade_tests',
            'view_test_results',
            'edit_courses',
            'view_courses',
            'manage_course_content',
            'view_student_progress',
            'view_student_performance',
            'view_student_grades',
            'view_student_attendance',
            'export_student_reports',
            'upload_resources',
            'manage_resources',
            'manage_assignments',
            'view_students',
            'send_messages',
            'download_files',
            
            // Student Capabilities
            'view_course_content',
            'access_course_materials',
            'view_modules',
            'download_resources',
            'watch_videos',
            'read_notes',
            'take_tests',
            'submit_assignments',
            'view_grades',
            'view_own_progress',
            'view_own_performance',
            'view_own_attendance',
            'view_own_profile',
            'edit_own_profile',
            'view_fee_history',
            'view_invoice',
        );
    }

    /**
     * Check if user role is LMS admin
     */
    public static function is_lms_admin($user_id = null) {
        $user_id = $user_id ?: get_current_user_id();
        $user = get_userdata($user_id);
        return $user && in_array(self::ADMIN_ROLE, $user->roles);
    }

    /**
     * Check if user role is teacher
     */
    public static function is_teacher($user_id = null) {
        $user_id = $user_id ?: get_current_user_id();
        $user = get_userdata($user_id);
        return $user && in_array(self::TEACHER_ROLE, $user->roles);
    }

    /**
     * Check if user role is student
     */
    public static function is_student($user_id = null) {
        $user_id = $user_id ?: get_current_user_id();
        $user = get_userdata($user_id);
        return $user && in_array(self::STUDENT_ROLE, $user->roles);
    }

    /**
     * Get user role type
     */
    public static function get_user_role_type($user_id = null) {
        $user_id = $user_id ?: get_current_user_id();
        
        if (self::is_lms_admin($user_id)) {
            return 'admin';
        } elseif (self::is_teacher($user_id)) {
            return 'teacher';
        } elseif (self::is_student($user_id)) {
            return 'student';
        } else {
            return 'unknown';
        }
    }

    /**
     * Remove LMS roles (cleanup on plugin deactivation)
     */
    public static function remove_roles() {
        remove_role(self::ADMIN_ROLE);
        remove_role(self::TEACHER_ROLE);
        remove_role(self::STUDENT_ROLE);
    }
}
