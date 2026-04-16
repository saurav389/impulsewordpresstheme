<?php
if (!defined('ABSPATH')) {
    exit;
}

class ICA_LMS_Admin_Fees {
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        add_action('admin_init', array(__CLASS__, 'handle_form_submission'));
        add_action('wp_ajax_ica_receive_payment', array(__CLASS__, 'ajax_receive_payment'));
        add_action('wp_ajax_ica_get_student_fees', array(__CLASS__, 'ajax_get_student_fees'));
        add_action('admin_post_ica_download_receipt', array(__CLASS__, 'download_receipt'));
    }

    public static function add_admin_menu() {
        if (!current_user_can('manage_options')) {
            return;
        }

        add_submenu_page(
            'ica-lms',
            'Fees',
            'Fees',
            'manage_options',
            'ica-lms-fees',
            array(__CLASS__, 'render_fees_page')
        );
    }

    /**
     * Render fees management page
     */
    public static function render_fees_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        ?>
        <div class="wrap">
            <h1>Fee Management</h1>

            <div class="ica-lms-filters" style="margin-bottom: 20px; padding: 15px; background: #f5f5f5; border-radius: 5px;">
                <form method="get">
                    <input type="hidden" name="page" value="ica-lms-fees">
                    <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; align-items: end;">
                        <div>
                            <label>Search Student (Name/RegNo/Mobile):</label>
                            <input type="text" id="student_search" placeholder="Search..." style="width: 100%; padding: 8px;">
                        </div>
                        <div id="student_results" style="background: white; border: 1px solid #ddd; border-radius: 3px; max-height: 200px; overflow-y: auto; display: none; position: absolute; z-index: 100; width: 300px;"></div>
                    </div>
                </form>
            </div>

            <div id="fee_details" style="margin-top: 30px; display: none;">
                <h2 id="student_name_display"></h2>
                
                <table class="widefat" style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th style="width: 15%;">Installment</th>
                            <th style="width: 15%;">Amount</th>
                            <th style="width: 15%;">Status</th>
                            <th style="width: 15%;">Paid Date</th>
                            <th style="width: 40%;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="fee_table"></tbody>
                </table>

                <div style="margin-top: 20px; padding: 15px; background: #f0f0f0; border-radius: 5px;">
                    <strong>Summary:</strong>
                    <div>Total Fee: <span id="total_fee">0</span> INR</div>
                    <div>Paid Amount: <span id="paid_amount">0</span> INR</div>
                    <div>Balance: <span id="balance_amount">0</span> INR</div>
                </div>
            </div>

            <div id="receive_payment_modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999;">
                <div style="background: white; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); padding: 20px; border-radius: 5px; width: 500px;">
                    <h2>Receive Payment</h2>
                    <form id="receive_payment_form">
                        <input type="hidden" id="payment_student_id">
                        <input type="hidden" id="payment_installment_number">
                        
                        <p>
                            <label>Amount (INR) *</label><br>
                            <input type="number" id="payment_amount" step="0.01" placeholder="Amount" required style="width: 100%; padding: 8px; box-sizing: border-box;">
                        </p>
                        
                        <p>
                            <label>Payment Method</label><br>
                            <select id="payment_method" style="width: 100%; padding: 8px; box-sizing: border-box;">
                                <option value="cash">Cash</option>
                                <option value="cheque">Cheque</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="online">Online</option>
                                <option value="other">Other</option>
                            </select>
                        </p>
                        
                        <p>
                            <label>Transaction ID / Reference</label><br>
                            <input type="text" id="payment_transaction_id" placeholder="Reference ID" style="width: 100%; padding: 8px; box-sizing: border-box;">
                        </p>
                        
                        <p>
                            <label>Notes</label><br>
                            <textarea id="payment_notes" style="width: 100%; padding: 8px; box-sizing: border-box; height: 80px;"></textarea>
                        </p>
                        
                        <div style="text-align: right; gap: 10px;">
                            <button type="button" class="button" onclick="jQuery('#receive_payment_modal').hide();">Cancel</button>
                            <button type="button" class="button button-primary" onclick="ica_submit_payment();">Submit Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <style>
            .search-result-item {
                padding: 10px;
                border-bottom: 1px solid #eee;
                cursor: pointer;
            }
            .search-result-item:hover {
                background: #f0f0f0;
            }
            .payment-badge {
                padding: 4px 8px;
                border-radius: 3px;
                font-weight: bold;
                text-align: center;
            }
            .payment-paid {
                background: #d4edda;
                color: #155724;
            }
            .payment-pending {
                background: #fff3cd;
                color: #856404;
            }
        </style>

        <script>
            let currentStudentId = 0;

            // Search students
            document.getElementById('student_search').addEventListener('keyup', function() {
                const search = this.value;
                if (search.length < 2) {
                    jQuery('#student_results').hide();
                    return;
                }

                jQuery.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'ica_get_student_fees',
                        search: search,
                        nonce: '<?php echo esc_js(wp_create_nonce('ica_fees_nonce')); ?>'
                    },
                    success: function(response) {
                        if (response.success && response.data.students.length > 0) {
                            let html = '';
                            response.data.students.forEach(student => {
                                html += '<div class="search-result-item" onclick="ica_select_student(' + student.id + ', \'' + student.name + '\', ' + student.fee_amount + ', ' + student.installment_count + ')">';
                                html += '<strong>' + student.name + '</strong> (RegNo: ' + student.reg_no + ') Mobile: ' + student.mobile_no;
                                html += '</div>';
                            });
                            jQuery('#student_results').html(html).show();
                        } else {
                            jQuery('#student_results').html('<div style="padding: 10px;">No students found</div>').show();
                        }
                    }
                });
            });

            function ica_select_student(studentId, name, feeAmount, installmentCount) {
                currentStudentId = studentId;
                jQuery('#student_search').val(name);
                jQuery('#student_results').hide();
                jQuery('#student_name_display').text(name);

                // Load student fees
                jQuery.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'ica_get_student_fees',
                        student_id: studentId,
                        nonce: '<?php echo esc_js(wp_create_nonce('ica_fees_nonce')); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            const data = response.data;
                            
                            // Update summary
                            jQuery('#total_fee').text(data.summary.total_fee.toFixed(2));
                            jQuery('#paid_amount').text(data.summary.paid_amount.toFixed(2));
                            jQuery('#balance_amount').text(data.summary.balance_amount.toFixed(2));

                            // Build fee table
                            let html = '';
                            const isOneTime = data.summary.fee_type === 'one_time';
                            const installmentCount = isOneTime ? 1 : data.summary.installment_count;
                            
                            for (let i = 1; i <= installmentCount; i++) {
                                const perInstallment = data.summary.per_installment.toFixed(2);
                                const payment = data.payments.find(p => p.installment_number == i);
                                const status = payment ? 'paid' : 'pending';
                                const paidDate = payment ? payment.payment_date : '-';
                                const amount = payment ? payment.amount : perInstallment;
                                const label = isOneTime ? 'Full Payment' : '#' + i;
                                const modalAmount = isOneTime ? data.summary.total_fee.toFixed(2) : perInstallment;

                                html += '<tr>';
                                html += '<td>' + label + '</td>';
                                html += '<td>₹' + amount + '</td>';
                                html += '<td><span class="payment-badge payment-' + status + '">' + status.toUpperCase() + '</span></td>';
                                html += '<td>' + paidDate + '</td>';
                                html += '<td>';
                                
                                if (!payment) {
                                    html += '<button class="button" onclick="ica_open_payment_modal(' + studentId + ', ' + i + ', ' + modalAmount + ')">Receive Payment</button>';
                                } else {
                                    html += '<button class="button" onclick="ica_download_receipt(' + payment.id + ')">Download Receipt</button>';
                                }
                                
                                html += '</td>';
                                html += '</tr>';
                            }
                            jQuery('#fee_table').html(html);
                            jQuery('#fee_details').show();
                        }
                    }
                });
            }

            function ica_open_payment_modal(studentId, installmentNumber, amount) {
                jQuery('#payment_student_id').val(studentId);
                jQuery('#payment_installment_number').val(installmentNumber);
                jQuery('#payment_amount').val(amount);
                jQuery('#receive_payment_modal').show();
            }

            function ica_submit_payment() {
                const studentId = jQuery('#payment_student_id').val();
                const installmentNumber = jQuery('#payment_installment_number').val();
                const amount = jQuery('#payment_amount').val();
                const method = jQuery('#payment_method').val();
                const transactionId = jQuery('#payment_transaction_id').val();
                const notes = jQuery('#payment_notes').val();

                if (!amount) {
                    alert('Please enter amount');
                    return;
                }

                jQuery.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'ica_receive_payment',
                        student_id: studentId,
                        installment_number: installmentNumber,
                        amount: amount,
                        payment_method: method,
                        transaction_id: transactionId,
                        notes: notes,
                        nonce: '<?php echo esc_js(wp_create_nonce('ica_receive_payment')); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Payment recorded successfully!');
                            jQuery('#receive_payment_modal').hide();
                            // Reload student details
                            ica_select_student(studentId, jQuery('#student_name_display').text(), 0, 0);
                        } else {
                            alert('Error: ' + response.data);
                        }
                    }
                });
            }

            function ica_download_receipt(paymentId) {
                window.open('<?php echo admin_url('admin-post.php'); ?>?action=ica_download_receipt&payment_id=' + paymentId + '&nonce=<?php echo wp_create_nonce('ica_download_receipt'); ?>', '_blank');
            }
        </script>
        <?php
    }

    /**
     * Handle form submission
     */
    public static function handle_form_submission() {
        // Add any form handling here if needed
    }

    /**
     * AJAX: Get student fees
     */
    public static function ajax_get_student_fees() {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['nonce'], 'ica_fees_nonce')) {
            wp_send_json_error('Unauthorized');
        }

        if (isset($_POST['search'])) {
            // Search for students
            $search = sanitize_text_field($_POST['search']);
            global $wpdb;
            $table = $wpdb->prefix . 'ica_lms_students';

            $students = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id, name, reg_no, mobile_no, fee_amount, installment_count, fee_type FROM $table 
                     WHERE (name LIKE %s OR reg_no LIKE %s OR mobile_no LIKE %s) 
                     LIMIT 10",
                    '%' . $wpdb->esc_like($search) . '%',
                    '%' . $wpdb->esc_like($search) . '%',
                    '%' . $wpdb->esc_like($search) . '%'
                ),
                ARRAY_A
            );

            wp_send_json_success(array('students' => $students));
        } elseif (isset($_POST['student_id'])) {
            // Get fee details for specific student
            $student_id = (int) $_POST['student_id'];
            
            global $wpdb;
            $table = $wpdb->prefix . 'ica_lms_students';
            $student_info = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT fee_type, installment_count FROM $table WHERE id = %d",
                    $student_id
                ),
                ARRAY_A
            );

            $payments = ICA_LMS_DB::get_student_payments($student_id);
            $summary = ICA_LMS_DB::get_payment_summary($student_id);
            $summary['fee_type'] = $student_info['fee_type'];

            wp_send_json_success(array(
                'payments' => $payments,
                'summary' => $summary
            ));
        }

        wp_send_json_error('Invalid request');
    }

    /**
     * AJAX: Receive payment
     */
    public static function ajax_receive_payment() {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['nonce'], 'ica_receive_payment')) {
            wp_send_json_error('Unauthorized');
        }

        $student_id = (int) $_POST['student_id'];
        $installment_number = (int) $_POST['installment_number'];
        $amount = (float) $_POST['amount'];
        $payment_method = sanitize_text_field($_POST['payment_method']);
        $transaction_id = sanitize_text_field($_POST['transaction_id']);
        $notes = sanitize_textarea_field($_POST['notes']);

        $payment_id = ICA_LMS_DB::record_payment($student_id, $installment_number, $amount, $payment_method, $transaction_id, $notes);

        if ($payment_id) {
            wp_send_json_success(array('payment_id' => $payment_id));
        } else {
            wp_send_json_error('Failed to record payment');
        }
    }

    /**
     * Download receipt as PDF
     */
    public static function download_receipt() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $payment_id = (int) $_GET['payment_id'];
        $nonce = $_GET['nonce'];

        if (!wp_verify_nonce($nonce, 'ica_download_receipt')) {
            wp_die('Unauthorized');
        }

        $payment = ICA_LMS_DB::get_payment($payment_id);
        if (!$payment) {
            wp_die('Payment not found');
        }

        $student = ICA_LMS_DB::get_student($payment['student_id']);
        if (!$student) {
            wp_die('Student not found');
        }

        // Determine if this is a one-time payment
        $is_one_time = isset($student['fee_type']) && $student['fee_type'] === 'one_time';
        $payment_label = $is_one_time ? 'Full Payment' : 'Installment #' . $payment['installment_number'];

        // Get payment summary for balance details
        $summary = ICA_LMS_DB::get_payment_summary($payment['student_id']);

        // Generate receipt content
        ob_start();
        ?>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Payment Receipt</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .header h1 { margin: 0; }
                .receipt-section { margin-bottom: 20px; }
                .receipt-section label { font-weight: bold; }
                table { width: 100%; margin-top: 20px; border-collapse: collapse; }
                table th, table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .total { font-weight: bold; background: #f0f0f0; }
                .balance { font-weight: bold; background: #fff3cd; color: #856404; }
                .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Payment Receipt</h1>
                <p>Impulse Academy</p>
            </div>

            <div class="receipt-section">
                <label>Student Name:</label> <?php echo esc_html($student['name']); ?>
            </div>

            <div class="receipt-section">
                <label>Registration Number:</label> <?php echo esc_html($student['reg_no']); ?>
            </div>

            <div class="receipt-section">
                <label><?php echo $is_one_time ? 'Payment Type:' : 'Installment Number:'; ?></label> <?php echo $is_one_time ? 'Full Payment' : '#' . $payment['installment_number']; ?>
            </div>

            <table>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
                <tr>
                    <td><?php echo $payment_label; ?></td>
                    <td><?php echo esc_html($payment['amount']); ?> <?php echo esc_html($payment['currency']); ?></td>
                </tr>
                <tr class="total">
                    <td>Total Amount Paid (This Payment)</td>
                    <td><?php echo esc_html($payment['amount']); ?> <?php echo esc_html($payment['currency']); ?></td>
                </tr>
            </table>

            <table>
                <tr>
                    <th>Fee Summary</th>
                    <th>Amount</th>
                </tr>
                <tr>
                    <td>Total Course Fee</td>
                    <td><?php echo esc_html(number_format($summary['total_fee'], 2)); ?> INR</td>
                </tr>
                <tr>
                    <td>Total Paid So Far</td>
                    <td><?php echo esc_html(number_format($summary['paid_amount'], 2)); ?> INR</td>
                </tr>
                <tr class="balance">
                    <td>Balance Fee Outstanding</td>
                    <td><?php echo esc_html(number_format($summary['balance_amount'], 2)); ?> INR</td>
                </tr>
            </table>

            <div class="receipt-section" style="margin-top: 20px;">
                <label>Payment Method:</label> <?php echo esc_html($payment['payment_method']); ?>
            </div>

            <div class="receipt-section">
                <label>Payment Date:</label> <?php echo esc_html($payment['payment_date']); ?>
            </div>

            <?php if (!empty($payment['transaction_id'])) : ?>
                <div class="receipt-section">
                    <label>Transaction ID:</label> <?php echo esc_html($payment['transaction_id']); ?>
                </div>
            <?php endif; ?>

            <div class="footer">
                <p>This is an automated receipt. For more information, please contact the academy.</p>
                <p>Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
            </div>
        </body>
        </html>
        <?php
        $html = ob_get_clean();

        // Output as HTML (can be printed to PDF via browser print)
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="Receipt_' . $payment_id . '.html"');
        echo $html;
        exit;
    }
}
