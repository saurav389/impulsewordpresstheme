<?php
if (!defined('ABSPATH')) {
    exit;
}

class ICA_LMS_QR_Code {
    /**
     * Generate QR code URL on-the-fly (no file storage)
     * Returns direct URL to external QR service
     */
    public static function generate_qr_code_url($data) {
        try {
            // Use external QR server - reliable and free service
            // Size options: adjust as needed (e.g., 200x200, 300x300)
            $qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($data);
            
            return $qr_url;
        } catch (Exception $e) {
            error_log('ICA_LMS QR Code URL Generation Error: ' . $e->getMessage());
            return '';
        }
    }
    
    /**
     * Get QR code URL for a student (on-the-fly generation)
     */
    public static function get_student_qr_url($student_id) {
        try {
            $student = ICA_LMS_DB::get_student($student_id);
            if (!$student) {
                error_log('ICA_LMS QR Code: Student not found: ' . $student_id);
                return '';
            }
            
            // Use registration number as QR data
            return self::generate_qr_code_url($student['reg_no']);
        } catch (Exception $e) {
            error_log('ICA_LMS QR Code URL Error: ' . $e->getMessage());
            return '';
        }
    }
}
