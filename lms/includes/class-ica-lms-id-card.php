<?php
if (!defined('ABSPATH')) {
    exit;
}

class ICA_LMS_ID_Card {
    /**
     * Generate ID Card PDF for student
     */
    public static function generate_id_card_pdf($student_id) {
        try {
            $student = ICA_LMS_DB::get_student($student_id);
            if (!$student) {
                wp_die('Student not found');
            }
            
            // Get course and batch names
            $course = get_post($student['course_id']);
            $batch = ICA_LMS_DB::get_batch($student['batch_id']);
            
            $course_name = $course ? $course->post_title : 'N/A';
            $batch_name = $batch ? $batch['batch_name'] : 'N/A';
            
            // Get QR code URL (handle if empty)
            $qr_url = '';
            if (class_exists('ICA_LMS_QR_Code')) {
                $qr_url = ICA_LMS_QR_Code::get_student_qr_url($student_id);
            }
            
            // Generate HTML content for ID card
            $html = self::generate_id_card_html($student, $course_name, $batch_name, $qr_url);
            
            // Generate PDF
            return self::render_pdf($html, $student['reg_no']);
        } catch (Exception $e) {
            error_log('ICA_LMS ID Card Generation Error: ' . $e->getMessage());
            wp_die('Error generating ID card: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate HTML for ID Card
     */
    private static function generate_id_card_html($student, $course_name, $batch_name, $qr_url) {
        $photo_url = !empty($student['student_photo_url']) ? $student['student_photo_url'] : '';
        $admission_date = !empty($student['admission_date']) ? date('d-m-Y', strtotime($student['admission_date'])) : 'N/A';
        
        $photo_html = !empty($photo_url) ? '<img src="' . esc_attr($photo_url) . '" alt="Student Photo">' : '<div class="photo-placeholder">No Photo</div>';
        $qr_html = '<img src="' . esc_attr($qr_url) . '" alt="QR Code">';
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ID Card - ' . esc_html($student['name']) . '</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: "Arial", sans-serif;
            background: #fff;
            padding: 20px;
            page-break-after: always;
        }
        .id-card-container {
            width: 8.56in;
            height: 5.39in;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            position: relative;
            overflow: hidden;
            color: #333;
            display: flex;
            flex-direction: row;
            padding: 20px;
            gap: 20px;
        }
        .id-card-container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 50%, rgba(255,255,255,.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(255,255,255,.05) 0%, transparent 50%);
            pointer-events: none;
        }
        .left-section {
            position: relative;
            z-index: 1;
            flex: 0 0 30%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        .photo-container {
            width: 140px;
            height: 160px;
            background: white;
            border: 3px solid #667eea;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .photo-placeholder {
            width: 100%;
            height: 100%;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #999;
        }
        .qr-code-container {
            width: 120px;
            height: 120px;
            background: white;
            padding: 5px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .qr-code-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .right-section {
            position: relative;
            z-index: 1;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid rgba(255,255,255,0.3);
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            color: white;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .header p {
            font-size: 11px;
            color: rgba(255,255,255,0.9);
            margin: 3px 0 0 0;
        }
        .student-info {
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex: 1;
        }
        .info-row {
            display: flex;
            font-size: 13px;
            line-height: 1.2;
        }
        .info-label {
            font-weight: bold;
            color: white;
            min-width: 120px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }
        .info-value {
            color: rgba(255,255,255,0.95);
            flex: 1;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
            word-break: break-word;
        }
        .footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid rgba(255,255,255,0.3);
            padding-top: 8px;
            margin-top: 8px;
            font-size: 10px;
            color: rgba(255,255,255,0.8);
        }
        .admission-date {
            text-align: left;
        }
        .signature-space {
            text-align: right;
            border-top: 1px solid rgba(255,255,255,0.8);
            padding-top: 4px;
            width: 80px;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .id-card-container {
                margin: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="id-card-container">
        <div class="left-section">
            <div class="photo-container">' . $photo_html . '</div>
            <div class="qr-code-container">' . $qr_html . '</div>
        </div>
        <div class="right-section">
            <div class="header">
                <h1>IMPULSE ACADEMY</h1>
                <p>Student ID Card</p>
            </div>
            <div class="student-info">
                <div class="info-row">
                    <span class="info-label">Reg. No:</span>
                    <span class="info-value">' . esc_html($student['reg_no']) . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value">' . esc_html($student['name']) . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Roll No:</span>
                    <span class="info-value">' . esc_html($student['roll_no']) . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Course:</span>
                    <span class="info-value">' . esc_html($course_name) . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Batch:</span>
                    <span class="info-value">' . esc_html($batch_name) . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Admission Date:</span>
                    <span class="info-value">' . esc_html($admission_date) . '</span>
                </div>
            </div>
            <div class="footer">
                <div class="admission-date">Valid from: ' . esc_html($admission_date) . '</div>
                <div class="signature-space">Authorized</div>
            </div>
        </div>
    </div>
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Render HTML to PDF
     */
    private static function render_pdf($html, $student_reg_no) {
        try {
            // Check if Dompdf is available
            if (self::has_dompdf()) {
                return self::generate_with_dompdf($html, $student_reg_no);
            }
            
            // Fallback: Generate HTML file that can be printed to PDF
            return self::render_html($html, $student_reg_no);
        } catch (Exception $e) {
            error_log('ICA_LMS ID Card Render Error: ' . $e->getMessage());
            // Output as HTML anyway
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="ID_Card_' . sanitize_file_name($student_reg_no) . '.html"');
            echo $html;
            exit;
        }
    }
    
    /**
     * Check if Dompdf is available
     */
    private static function has_dompdf() {
        return class_exists('Dompdf\\Dompdf');
    }
    
    /**
     * Generate PDF using Dompdf
     */
    private static function generate_with_dompdf($html, $student_reg_no) {
        try {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            
            $output = $dompdf->output();
            $filename = 'ID_Card_' . sanitize_file_name($student_reg_no) . '.pdf';
            
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            echo $output;
            exit;
        } catch (Exception $e) {
            error_log('Dompdf Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Fallback: Render as HTML for browser print
     */
    private static function render_html($html, $student_reg_no) {
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="ID_Card_' . sanitize_file_name($student_reg_no) . '.html"');
        echo $html;
        exit;
    }
    
    /**
     * Generate bulk ID cards as ZIP file
     */
    public static function generate_bulk_id_cards($student_ids = array()) {
        if (empty($student_ids)) {
            // Get all students if none specified
            $students = ICA_LMS_DB::get_students(self::get_all_students_count(), 0);
            $student_ids = wp_list_pluck($students, 'id');
        }
        
        // Create temporary directory for HTML/PDF files
        $temp_dir = wp_upload_dir()['basedir'] . '/ica-lms-idcards-temp/';
        wp_mkdir_p($temp_dir);
        
        // Generate ID card for each student
        foreach ($student_ids as $student_id) {
            $student = ICA_LMS_DB::get_student($student_id);
            if (!$student) {
                continue;
            }
            
            $course = get_post($student['course_id']);
            $batch = ICA_LMS_DB::get_batch($student['batch_id']);
            
            $course_name = $course ? $course->post_title : 'N/A';
            $batch_name = $batch ? $batch['batch_name'] : 'N/A';
            
            $qr_url = ICA_LMS_QR_Code::get_student_qr_url($student_id);
            
            $html = self::generate_id_card_html($student, $course_name, $batch_name, $qr_url);
            
            // Save HTML file
            $filename = sanitize_file_name($student['reg_no']) . '.html';
            file_put_contents($temp_dir . $filename, $html);
        }
        
        // Create ZIP file
        if (class_exists('ZipArchive')) {
            $zip_file = wp_upload_dir()['basedir'] . '/ica-lms-id-cards-' . date('Y-m-d-His') . '.zip';
            $zip = new ZipArchive();
            $zip->open($zip_file, ZipArchive::CREATE);
            
            $files = glob($temp_dir . '*.html');
            foreach ($files as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();
            
            // Clean up temporary files
            array_map('unlink', $files);
            rmdir($temp_dir);
            
            return $zip_file;
        }
        
        return false;
    }
    
    /**
     * Get count of all students
     */
    private static function get_all_students_count() {
        return ICA_LMS_DB::count_students();
    }
}
