(function ($) {
    'use strict';

    function ajaxPost(action, payload) {
        return $.post(ICALMS.ajax_url, $.extend({
            action: action,
            nonce: ICALMS.nonce
        }, payload || {}));
    }

    $(document).on('click', '.js-ica-enroll-btn', function () {
        var btn = $(this);
        btn.prop('disabled', true);

        ajaxPost('ica_lms_enroll', {
            course_id: btn.data('course-id')
        }).done(function (res) {
            if (res && res.success) {
                btn.replaceWith('<span class="ica-lms-pill success">Enrolled</span>');
                return;
            }
            alert((res && res.data && res.data.message) ? res.data.message : 'Enrollment failed.');
            btn.prop('disabled', false);
        }).fail(function () {
            alert('Enrollment request failed.');
            btn.prop('disabled', false);
        });
    });

    $(document).on('click', '.js-ica-complete-lesson-btn', function () {
        var btn = $(this);
        btn.prop('disabled', true);

        ajaxPost('ica_lms_complete_lesson', {
            course_id: btn.data('course-id'),
            lesson_id: btn.data('lesson-id')
        }).done(function (res) {
            if (res && res.success) {
                btn.replaceWith('<span class="ica-lms-pill success">Completed</span>');
                if (res.data && res.data.certificate_issued) {
                    alert('Course completed. Your certificate is now available on the dashboard.');
                    window.location.reload();
                }
                return;
            }
            alert((res && res.data && res.data.message) ? res.data.message : 'Unable to update progress.');
            btn.prop('disabled', false);
        }).fail(function () {
            alert('Progress request failed.');
            btn.prop('disabled', false);
        });
    });

    $(document).on('submit', '.js-ica-quiz-form', function (e) {
        e.preventDefault();
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        submitBtn.prop('disabled', true);

        var payload = form.serialize() + '&action=ica_lms_submit_quiz&nonce=' + encodeURIComponent(ICALMS.nonce);
        $.post(ICALMS.ajax_url, payload).done(function (res) {
            if (res && res.success) {
                var msg = 'Score: ' + res.data.score + '%.';
                msg += res.data.passed ? ' Passed.' : ' Passing score is ' + res.data.passing_score + '%.';
                if (res.data.certificate_issued) {
                    msg += ' Certificate issued.';
                }
                alert(msg);
                if (res.data.certificate_issued) {
                    window.location.reload();
                }
                return;
            }
            alert((res && res.data && res.data.message) ? res.data.message : 'Quiz submission failed.');
            submitBtn.prop('disabled', false);
        }).fail(function () {
            alert('Quiz request failed.');
            submitBtn.prop('disabled', false);
        });
    });
})(jQuery);
