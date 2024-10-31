<?php

if ( ! function_exists( 'paymendo_bank_transfer_set_settings_page' ) ) {
	function paymendo_bank_transfer_set_settings_page() {
		$page_title       = __( 'General Settings', 'paymendo-bank-transfer-lite' );
		$page_description = __( 'You can configure general module settings on this page.', 'paymendo-bank-transfer-lite' );
		require_once dirname( __FILE__ ) . '/blocks/header.php';

		$sms_functions_enabled = function_exists( 'wp_sms_send_sms' );
		?>
        <form method="POST">
            <div class="m-b-30 p-0 w-100" style="max-width: none">
                <div class="card-body p-3">

					<?php if ( ! $sms_functions_enabled ) : ?>
                        <div class="notice notice-error mb-3">
                            <p><?php echo sprintf( __( 'Get the <a href="%s">"WP SMS Functions"</a> plugin to use the SMS sending features.', 'paymendo-bank-transfer-lite' ), "https://wordpress.org/plugins/wp-sms-functions/" ); ?></p>
                        </div>
					<?php endif; ?>

                    <div class="form-group">
                        <table class="table">
                            <tbody>
							<?php
							if ( $sms_functions_enabled ) {
								$transferSMSMessage = pbt_get_admin_sms_message();
								$shortcodes         = array(
									array(
										'label' => __( 'Customer Name', 'paymendo-bank-transfer-lite' ),
										'code'  => 'customer_name'
									),
									array(
										'label' => __( 'Your Bank Account', 'paymendo-bank-transfer-lite' ),
										'code'  => 'bank_name_with_currency'
									)
								)
								?>
                                <tr>
                                    <th width="60%" class="border-top-0">
                                        <label for="enable_sms_for_admin"><?php echo __( 'Enable SMS notification to billing manager?', 'paymendo-bank-transfer-lite' ) ?></label>
                                        <p class="text-info" style="font-weight: normal"><i
                                                    class="fa fa-info-circle"></i> <?php echo __( 'When the payment notification is made, activate it to send an SMS notification to the billing manager.', 'paymendo-bank-transfer-lite' ) ?>
                                        </p>
                                    </th>
                                    <td class="border-top-0">
                                        <input type="checkbox" id="enable_sms_for_admin" name="enable_sms_for_admin"
                                               autocomplete="off"
                                               value="on"
											<?php echo pbt_is_admin_sms_enabled() ? "checked" : "" ?>
                                        />
                                    </td>
                                </tr>
                                <tr id="sms-message-template-setting-for-admin" style="<?php
								echo pbt_is_admin_sms_enabled() ? "" : "display:none"
								?>">
                                    <td colspan="2" class="p-0">
                                        <table width="100%" class="bg-light table">
                                            <tbody>
                                            <tr>
                                                <th class="border-top-0" width="60%">
													<?php echo __( 'Admin Phone Number(s)', 'paymendo-bank-transfer-lite' ) ?>
                                                </th>
                                                <td class="border-top-0">
                                                    <input type="text"
                                                           name="sms_number_for_admin" pattern="\d+" maxlength="11"
                                                           minlength="11"
                                                           value="<?php echo pbt_get_admin_sms_number(); ?>"
                                                           class="form-control" autocomplete="off"
                                                    >
                                                </td>
                                            </tr>
                                            <tr>
                                                <th width="60%" class="align-top">
                                                    <label for="sms_message_for_admin"><?php echo __( 'Template for billing manager notification:', 'paymendo-bank-transfer-lite' ) ?></label>
                                                    <p class="text-info" style="font-weight: normal"><i
                                                                class="fa fa-info-circle"></i> <?php echo __( 'Set the template for the SMS to be sent to the payment manager.', 'paymendo-bank-transfer-lite' ) ?>
                                                    </p>
                                                </th>
                                                <td>
                                                    <p>
                                                    <textarea id="sms_message_for_admin" class="form-control"
                                                              name="sms_message_for_admin"><?php echo esc_html($transferSMSMessage) ?></textarea>
                                                    </p>

                                                    <ul class="p-0">
                                                        <li class="shortlist-title"><?php echo __( 'Available Shortcodes:', 'paymendo-bank-transfer-lite' ) ?></li>
														<?php
														foreach ( $shortcodes as $shortcode ) {
															echo sprintf( "<li><strong onclick='paymendo_bank_transfer_insert2input(\"{%s}\")'>{%s}</strong>: %s</li>", esc_attr($shortcode['code']), esc_html($shortcode['code']), esc_html($shortcode['label']) );
														} ?> <br/>
														<?php
														echo __( '<strong>Example:</strong> The customer named {customer_name} has payed to your {bank_name_with_currency} account.', 'paymendo-bank-transfer-lite' );
														?>
                                                    </ul>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
								<?php
							}
							?>
                            <tr>
                                <th>
                                    <label for="enable_email_for_admin"><?php echo __( 'Enable email notification to billing manager?', 'paymendo-bank-transfer-lite' ) ?></label>
                                </th>
                                <td>
                                    <input
                                            autocomplete="off"
                                            type="checkbox" id="enable_email_for_admin" name="enable_email_for_admin"
                                            value="on"
                                            autocomplete="off"
										<?php echo pbt_is_admin_email_enabled() ? "checked" : "" ?>
                                    />
                                </td>
                            </tr>
							<?php
							if ( $sms_functions_enabled ) {
								$transferSMSMessageToSendCustomer = pbt_get_customer_sms_message();
								$shortcodes                       = array(
									array(
										'label' => __( 'Customer Name', 'paymendo-bank-transfer-lite' ),
										'code'  => 'customer_name'
									),
									array(
										'label' => __( 'Order Id', 'paymendo-bank-transfer-lite' ),
										'code'  => 'order_id'
									),
									array(
										'label' => __( 'Order Number', 'paymendo-bank-transfer-lite' ),
										'code'  => 'order_number'
									)
								)
								?>
                                <tr>
                                    <th>
                                        <label for="enable_sms_for_customer"><?php echo __( 'Send SMS notification on payment confirmation?', 'paymendo-bank-transfer-lite' ) ?></label>
                                    </th>
                                    <td>
                                        <input type="checkbox" id="enable_sms_for_customer"
                                               name="enable_sms_for_customer"
                                               value="on"
                                               autocomplete="off"
											<?php echo pbt_is_customer_sms_enabled() ? "checked" : "" ?>
                                        />
                                    </td>
                                </tr>
                                <tr id="sms-message-template-setting-for-customer" style="<?php
								echo pbt_is_customer_sms_enabled() ? "" : "display:none"
								?>">
                                    <td colspan="2" class="p-0">
                                        <table class="bg-light table" width="100%">
                                            <tbody>
                                            <tr>
                                                <th width="60%">
                                                    <label for="sms_message_for_customer"><?php echo __( 'Payment confirmation SMS template:', 'paymendo-bank-transfer-lite' ) ?></label>
                                                </th>
                                                <td>
                                                    <textarea
                                                            autocomplete="off"
                                                            id="sms_message_for_customer" class="form form-control mb-2"
                                                            name="sms_message_for_customer"><?php echo esc_textarea($transferSMSMessageToSendCustomer) ?></textarea>
                                                    <h6 class="shortlist-title text-underline"><?php echo __( 'Available Shortcodes', 'paymendo-bank-transfer-lite' ) ?></h6>
                                                    <ul class="p-0">
														<?php
														foreach ( $shortcodes as $shortcode ) {
															echo sprintf( "<li><strong class='cursor-pointer' onclick='paymendo_bank_transfer_insert2customer_input(\"{%s}\")'>{%s}</strong>: %s</li>", esc_attr($shortcode['code']), esc_html($shortcode['code']), esc_html($shortcode['label']) );
														}
														?><br/>
                                                        <li class="text-info p-2 font-italic border bg-white rounded">
															<?php echo __( '<strong>Example:</strong> Dear {customer_name}, we have approved your payment for the order #{order_number}. We will process your order as soon as possible. Thank you for choosing us.', 'paymendo-bank-transfer-lite' ); ?>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
								<?php
							}
							?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer border-top bg-light p-3 text-right">
                    <button class="btn btn-sm btn-primary"
                            type="submit"><?php _e( 'Save Changes', 'paymendo-bank-transfer-lite' ) ?></button>
                </div>
            </div>
        </form>
		<?php
		require_once dirname( __FILE__ ) . '/blocks/footer.php';
	}
}

