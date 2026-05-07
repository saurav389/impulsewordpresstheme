(function($) {
    'use strict';

    $(function() {
        var frame;
        var $imageId = $('#impulse-banner-mobile-image-id');
        var $preview = $('.impulse-banner-mobile-preview');
        var $removeButton = $('.impulse-banner-remove-mobile-image');
        var $startDate = $('#impulse-banner-start-date');
        var $endDate = $('#impulse-banner-end-date');

        function renderPreview(attachment) {
            if (!attachment || !attachment.url) {
                $preview.html('<span class="description">No mobile image selected yet.</span>');
                $removeButton.prop('disabled', true);
                return;
            }

            $preview.html(
                '<img src="' + attachment.url + '" alt="" style="max-width:100%;height:auto;border:1px solid #dcdcde;border-radius:6px;">'
            );
            $removeButton.prop('disabled', false);
        }

        /**
         * Validate start date - warn if it's in the future
         */
        function validateStartDate() {
            var startDateValue = $startDate.val();
            if (!startDateValue) {
                return; // Empty is fine
            }

            // Convert datetime-local format to timestamp
            var startDateTime = new Date(startDateValue.replace('T', ' '));
            var now = new Date();

            if (startDateTime > now) {
                // Future date detected
                var message = 'Warning: This start date is in the future. The banner will not show until this date is reached.\n\nCurrent time: ' + now.toLocaleString() + '\nStart time: ' + startDateTime.toLocaleString();
                console.warn(message);
                
                // Add visual warning
                $startDate.css('border-color', '#dc3545').css('box-shadow', '0 0 0 0.2rem rgba(220, 53, 69, 0.25)');
            } else {
                // Past or current date is OK
                $startDate.css('border-color', '').css('box-shadow', '');
            }
        }

        /**
         * Validate end date - warn if it's before start date
         */
        function validateEndDate() {
            var startDateValue = $startDate.val();
            var endDateValue = $endDate.val();

            if (!startDateValue || !endDateValue) {
                return;
            }

            var startDateTime = new Date(startDateValue.replace('T', ' '));
            var endDateTime = new Date(endDateValue.replace('T', ' '));

            if (endDateTime < startDateTime) {
                $endDate.css('border-color', '#dc3545').css('box-shadow', '0 0 0 0.2rem rgba(220, 53, 69, 0.25)');
            } else {
                $endDate.css('border-color', '').css('box-shadow', '');
            }
        }

        // Validate dates on change
        $startDate.on('change', validateStartDate);
        $endDate.on('change', validateEndDate);

        // Validate on page load
        validateStartDate();
        validateEndDate();

        $('.impulse-banner-select-mobile-image').on('click', function(event) {
            event.preventDefault();

            if (frame) {
                frame.open();
                return;
            }

            frame = wp.media({
                title: 'Select mobile banner image',
                button: {
                    text: 'Use this image'
                },
                library: {
                    type: 'image'
                },
                multiple: false
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $imageId.val(attachment.id);
                renderPreview(attachment);
            });

            frame.open();
        });

        $removeButton.on('click', function(event) {
            event.preventDefault();
            $imageId.val('');
            renderPreview(null);
        });
    });
})(jQuery);
