<?php

if ( ! function_exists( 'paymendo_bank_transfer_set_payments_page' ) ) {
	function paymendo_bank_transfer_set_payments_page() {
		$page_title       = __( 'Payment Notifications', 'paymendo-bank-transfer-lite' );
		$page_description = __( 'You can review the payment records reported by customers on this page.','paymendo-bank-transfer-lite');
		$page_extra       = "<div class='w-100 text-right'><button type='button' class='btn btn-dark d-flex filter-toggle float-right'><span>".__('Filter Results','paymendo-bank-transfer-lite')."</span> <i class='ml-2 mt-1 fa fa-filter'></i></button></div>";
		require_once dirname( __FILE__ ) . '/blocks/header.php';
		$bank_accounts = pbt_get_bank_accounts();
		?>
        <form method="GET" id="notification-filter-form" style="display:none;"
              class="bg-white border-bottom shadow p-2">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group bank-filter-group">
                        <label><?php echo __( 'By Bank: ', 'paymendo-bank-transfer-lite' ); ?></label>
                        <select
                                data-placeholder="<?php _e( 'Select Bank Account', 'paymendo-bank-transfer-lite' ) ?>"
                                name="banks_filter" multiple class="form-control">
							<?php foreach ( $bank_accounts as $bank_account ): ?>
                                <option value="<?php echo esc_attr($bank_account->id) ?>">
									<?php $bank_object = pbt_get_bank_list( $bank_account->bank_slug ) ?>
									<?php echo esc_html($bank_object->getBankName()); ?>
                                </option>
							<?php endforeach; ?>
                        </select>
                    </div>


                </div>

                <div class="col-md-2">

                    <div class="form-group status-filter-group">
                        <label><?php echo __( 'By Status: ', 'paymendo-bank-transfer-lite' ); ?></label>
                        <select name="status_filter" class="form-control">
                            <option selected
                                    value=""><?php echo __( 'All Status', 'paymendo-bank-transfer-lite' ) ?></option>
                            <option value="1"><?php echo __( 'Confirmed', 'paymendo-bank-transfer-lite' ) ?></option>
                            <option value="0"><?php echo __( 'Unconfirmed', 'paymendo-bank-transfer-lite' ) ?></option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group currency-filter-group">
                        <label><?php echo __( 'By Currency: ', 'paymendo-bank-transfer-lite' ); ?></label>
                        <select
                                data-placeholder="<?php _e( 'Select Currency', 'paymendo-bank-transfer-lite' ) ?>"
                                name="currency_filter" multiple class="form-control">
                            <option value=""></option>
							<?php
							$selected_currencies = array();
//							if ( isset( $_GET['currency_filter'] ) && ! empty( $_GET['currency_filter'] ) ) {
//								$selected_currencies = explode( ",", sanitize_text_field($_GET['currency_filter']) );
//							}
							foreach ( WC_Gateway_Paymendo_Bank_Transfer::get_currency() as $key => $item ) {
								//$selected = isset( $_GET['currency_filter'] ) && in_array( $key, $selected_currencies ) ? ' selected' : null;
								echo sprintf( '<option value="%s">%s</option>', esc_attr($key)/*, $selected*/, esc_html($item) );
							}
							?>
                        </select>
                    </div>
                </div>

                <div class="col-md-4">

                    <div class="row">
                        <div class="col">
                            <label><?php echo __( 'Date Range:', 'paymendo-bank-transfer-lite' ); ?></label>
                            <p class="w-100">
                                <input name="date_range"
                                       id="date-filter"
                                       class="form-control"
                                />
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-2 mb-2">
                <div class="col">
                    <div class="form-group ml-2">
                        <label><?php echo __( 'By Order Amount: ', 'paymendo-bank-transfer-lite' ); ?></label>
                        <div>
                            <div class="slider-wrapper">
                                <input
                                        id="price-filter"
                                        data-min="0"
                                        data-max="<?php echo esc_attr(\Grilabs\Paymendo\BankTransfer\PaymendoBankTransfer::get_max_notification_amount()) ?>"
                                        data-slider-range="true" data-slider-tooltip_split="true"/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <p>&nbsp;</p>
                    <div class="text-right">

                        <button type="button" class="btn btn-sm btn-light filter-close">Kapat <i
                                    class="fa fa-times"></i></button>

                        <button type="button"
                                class="btn btn-sm btn-primary"
                                onclick="pbt_redraw_table()"><?php echo __( 'Filter', 'paymendo-bank-transfer-lite' ); ?> <i
                                    class="fa fa-search"></i></button>
                    </div>
                </div>
            </div>


        </form>

        <div class="p-3">
            <div class="card w-100 shadow border mt-0" style="max-width: none">
                <div class="card-body w-100">
                    <table class="banks-table mt-1 float-left shadow" id="data-table">
                        <thead>
                        <tr>
                            <th width="10%"><?php echo __( 'Order No', 'paymendo-bank-transfer-lite' ) ?></th>
                            <th width="10%"><?php echo __( 'Customer Name', 'paymendo-bank-transfer-lite' ) ?></th>
                            <th width="15%"><?php echo __( 'Bank Account', 'paymendo-bank-transfer-lite' ) ?></th>
                            <th width="10%"><?php echo __( 'Order Amount', 'paymendo-bank-transfer-lite' ) ?></th>
                            <th width="17.5%"><?php echo __( 'Created', 'paymendo-bank-transfer-lite' ) ?></th>
                            <th width="17.5%"><?php echo __( 'Updated', 'paymendo-bank-transfer-lite' ) ?></th>
                            <th width="20%">
								<?php echo __( 'Actions', 'paymendo-bank-transfer-lite' ) ?>
                            </th>
                        </tr>
                        </thead>
                        <tbody style="min-height: 400px;"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <form method="post" class="pbt-delete-payment-modal">
            <p>
				<?php echo __( 'Would you like to share the information that the payment was canceled with the customer? You can write and send a message.', 'paymendo-bank-transfer-lite' ); ?>
            </p>
            <p>
                <label><?php echo __( 'SMS Message', 'paymendo-bank-transfer-lite' ); ?></label>
                <textarea name="paymendo_bank_transfer_sms_for_deleted_payment" style="width: 100%;height:100px" required></textarea>
            </p>
            <p>
                <input type="hidden" id="order_id_for_payments_deleted" name="order_id" value="">
                <button type="submit"><?php echo __( 'Submit', 'paymendo-bank-transfer-lite' ); ?></button>
            </p>
        </form>
		<?php
		require_once dirname( __FILE__ ) . '/blocks/footer.php';

	}
}