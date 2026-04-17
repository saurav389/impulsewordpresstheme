/**
 * Student Portal - Login Page JavaScript
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
        // Debug: Check if ICALogin object is loaded
        if (typeof ICALogin === 'undefined') {
            console.error('ICALogin object not found! AJAX login will not work.');
            return;
        }

        console.log('ICALogin loaded:', ICALogin);

        var $form = $('#ica-login-form');
        var $button = $form.find('button[type="submit"]');
        var $message = $('#ica-login-message');

        // Form submission
        $form.on('submit', function (e) {
            e.preventDefault();

            // Clear previous errors
            $('.ica-error-message').removeClass('show');
            $message.removeClass('ica-alert-error ica-alert-success').html('').hide();

            // Get form values
            var username = $.trim($('input[name="log"]').val());
            var password = $.trim($('input[name="pwd"]').val());
            var rememberme = $('input[name="rememberme"]').is(':checked') ? 1 : 0;

            // Validate inputs
            if (!username) {
                $('#log-error').text('Username or email is required').addClass('show');
                return false;
            }

            if (!password) {
                $('#pwd-error').text('Password is required').addClass('show');
                return false;
            }

            if (password.length < 6) {
                $('#pwd-error').text('Password must be at least 6 characters').addClass('show');
                return false;
            }

            // Send login request
            loginStudent(username, password, rememberme);

            return false;
        });

        /**
         * Login student via AJAX
         */
        function loginStudent(username, password, rememberme) {
            // Disable button and show loading state
            $button.prop('disabled', true).addClass('loading');

            console.log('Sending login request to:', ICALogin.ajax_url);

            $.ajax({
                url: ICALogin.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'ica_student_login',
                    nonce: ICALogin.nonce,
                    log: username,
                    pwd: password,
                    rememberme: rememberme,
                },
                success: function (response) {
                    console.log('AJAX response received:', response);
                    
                    if (response.success) {
                        // Show success message
                        $message
                            .addClass('ica-alert ica-alert-success')
                            .html('<strong>✓ Login successful!</strong> Redirecting...')
                            .show();

                        console.log('Redirecting to:', ICALogin.redirect_url);

                        // Redirect after a short delay
                        setTimeout(function () {
                            window.location.href = ICALogin.redirect_url;
                        }, 1000);
                    } else {
                        // Show error message
                        var errorMsg = response.data || 'Login failed. Please try again.';
                        console.error('Login error:', errorMsg);
                        
                        $message
                            .addClass('ica-alert ica-alert-error')
                            .html('<strong>✗ Login failed:</strong> ' + errorMsg)
                            .show();

                        // Reset button
                        $button.prop('disabled', false).removeClass('loading');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    // Log detailed error information
                    console.error('AJAX Error:', {
                        status: jqXHR.status,
                        statusText: jqXHR.statusText,
                        textStatus: textStatus,
                        errorThrown: errorThrown,
                        responseText: jqXHR.responseText
                    });
                    
                    console.log('AJAX login failed, falling back to standard login');
                    wp_login_fallback(username, password);
                },
            });
        }

        /**
         * Fallback to standard WordPress login if AJAX fails
         */
        function wp_login_fallback(username, password) {
            // Extract wp-login.php URL from admin_url
            var loginUrl = ICALogin.ajax_url.replace('admin-ajax.php', 'wp-login.php');
            
            // Create a hidden form for standard login
            var $loginForm = $('<form>')
                .attr('method', 'post')
                .attr('action', loginUrl)
                .css('display', 'none');

            $loginForm.append($('<input>').attr('type', 'hidden').attr('name', 'log').val(username));
            $loginForm.append($('<input>').attr('type', 'hidden').attr('name', 'pwd').val(password));
            $loginForm.append($('<input>').attr('type', 'hidden').attr('name', 'wp-submit').val('Log In'));
            $loginForm.append($('<input>').attr('type', 'hidden').attr('name', 'redirect_to').val(ICALogin.redirect_url));

            $('body').append($loginForm);
            $loginForm.submit();
        }

        // Real-time validation
        $('input[name="log"]').on('blur', function () {
            if ($.trim($(this).val()) === '') {
                $('#log-error').text('Username or email is required').addClass('show');
            } else {
                $('#log-error').removeClass('show');
            }
        });

        $('input[name="pwd"]').on('blur', function () {
            if ($.trim($(this).val()) === '') {
                $('#pwd-error').text('Password is required').addClass('show');
            } else if ($(this).val().length < 6) {
                $('#pwd-error').text('Password must be at least 6 characters').addClass('show');
            } else {
                $('#pwd-error').removeClass('show');
            }
        });

        // Clear errors on focus
        $('input').on('focus', function () {
            $(this).siblings('.ica-error-message').removeClass('show');
        });
    });

})(jQuery);
