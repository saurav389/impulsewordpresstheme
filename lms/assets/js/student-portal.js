/* ICA LMS Student Portal JavaScript */

jQuery(document).ready(function($) {
    
    // Enroll course button
    $(document).on('click', '.ica-enroll-btn', function() {
        var $btn = $(this);
        var courseId = $btn.data('course-id');
        
        $btn.prop('disabled', true).text('Enrolling...');
        
        $.ajax({
            url: ICAStudentPortal.ajax_url,
            type: 'POST',
            data: {
                action: 'ica_enroll_course',
                nonce: ICAStudentPortal.nonce,
                course_id: courseId
            },
            success: function(response) {
                if (response.success) {
                    alert('Successfully enrolled! Redirecting...');
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                    $btn.prop('disabled', false).text('Enroll Now');
                }
            },
            error: function() {
                alert('Network error. Please try again.');
                $btn.prop('disabled', false).text('Enroll Now');
            }
        });
    });

    // View course materials
    $(document).on('click', '.ica-view-materials-btn', function() {
        var $btn = $(this);
        var courseId = $btn.data('course-id');
        var courseName = $btn.closest('.ica-course-card').find('h3').text();
        
        $.ajax({
            url: ICAStudentPortal.ajax_url,
            type: 'POST',
            data: {
                action: 'ica_get_course_materials',
                nonce: ICAStudentPortal.nonce,
                course_id: courseId
            },
            success: function(response) {
                if (response.success) {
                    $('#ica-materials-title').text(courseName);
                    $('#ica-materials-list').html(response.data.html);
                    $('#ica-materials-modal').css('display', 'flex');
                } else {
                    alert('Error loading materials');
                }
            },
            error: function() {
                alert('Error loading materials');
            }
        });
    });

    // Close materials modal
    $(document).on('click', '.ica-close', function() {
        $('#ica-materials-modal').css('display', 'none');
    });

    // Close modal when clicking outside
    $(window).on('click', function(event) {
        var $modal = $('#ica-materials-modal');
        if (event.target == $modal[0]) {
            $modal.css('display', 'none');
        }
    });

    // Start exam
    $(document).on('click', '.ica-start-exam-btn', function() {
        var $btn = $(this);
        var examId = $btn.data('exam-id');
        
        $btn.prop('disabled', true).text('Loading...');
        
        $.ajax({
            url: ICAStudentPortal.ajax_url,
            type: 'POST',
            data: {
                action: 'ica_start_exam',
                nonce: ICAStudentPortal.nonce,
                exam_id: examId
            },
            success: function(response) {
                if (response.success) {
                    // Redirect to exam interface
                    window.location.href = '?ica-exam=' + examId;
                } else {
                    alert('Error: ' + response.data);
                    $btn.prop('disabled', false).text('Start Exam');
                }
            },
            error: function() {
                alert('Network error. Please try again.');
                $btn.prop('disabled', false).text('Start Exam');
            }
        });
    });

    // View exam result
    $(document).on('click', '.ica-view-result-btn', function() {
        var $btn = $(this);
        var examId = $btn.data('exam-id');
        
        // Redirect to exam result page
        window.location.href = '?ica-exam-result=' + examId;
    });

    // Tab switching
    $(document).on('click', '.ica-tab-btn', function(e) {
        // Let the link navigate naturally
    });

    console.log('Student Portal initialized');
});
