<?php $order = isset( $order ) ? $order : null; if(is_null($order)) return; ?>


<?php
if ( ! empty( $order->get_payment_method() ) && $order->get_payment_method() === 'paymendo_bank_transfer' ) {
	$notification = pbt_get_transfer_notification_with_order_id( $order->get_id() );
	?>
    <div class="payment-information">
	<?php if ( empty( $notification ) ): ?>
        <p>
            <strong><?php echo __( 'You can view our bank accounts below to complete the payment.', 'paymendo-bank-transfer-lite' ); ?></strong>
        </p>
	<?php endif; ?>

    <p class="payment-completed-area">
	<?php if ( empty( $notification ) ): ?>
        <button class="payment-completed-button"><?php echo __( 'I\'ve completed the payment', 'paymendo-bank-transfer-lite' ); ?></button>
	<?php else: ?>
        <strong><?php echo __( 'This order\'s bank transfer has been completed with bank information below.', 'paymendo-bank-transfer-lite' ); ?></strong>
		<?php if ( $notification->payment_status == 0 ): ?>
            <p><?php echo __( 'When the payment has been confirmed, order status will change automatically.', 'paymendo-bank-transfer-lite' ); ?></p>
		<?php endif; ?>
	<?php endif; ?>
    </p>

	<?php
	$bank_accounts = ! empty( $notification ) ? pbt_get_bank_account_with_id( $notification->bank_id ) : pbt_get_bank_accounts();

	if ( isset( $bank_accounts->id ) ) { //if $bank_accounts comes as single, to enter the foreach
		$bank_accounts = array(
			'0' => $bank_accounts
		);
	}
	foreach ( $bank_accounts as $account ):
        $account_object = pbt_get_bank_list($account->bank_slug);
		?>
        <table class="bank-accounts-table">
            <thead>
            <tr>
                <th colspan="2">
                    <img src="<?php echo esc_attr($account_object->getLogo()) ?>" alt="<?php echo esc_attr($account_object->getSlug()) . ' Logo' ?>">
                </th>
            </tr>
            </thead>
            <tbody>

			<?php
			$account_detail_rows = array(
				array(
					"key"   => "account_owner",
					"label" => __( 'Account Owner:', 'paymendo-bank-transfer-lite' )
				),
				array(
					"key"   => "branch_code",
					"label" => __( 'Branch Code:', 'paymendo-bank-transfer-lite' )
				),
				array(
					"key"   => "account_number",
					"label" => __( 'Account Number:', 'paymendo-bank-transfer-lite' )
				),
				array(
					"key"   => "iban",
					"label" => __( 'IBAN: ', 'paymendo-bank-transfer-lite' )
				),
				array(
					"key"   => "currency",
					"label" => __( 'Currency:', 'paymendo-bank-transfer-lite' )
				),
				array(
					"key"   => "swift",
					"label" => __( 'BIC / Swift:', 'paymendo-bank-transfer-lite' )
				),
				array(
					"key"   => "note",
					"label" => __( 'Note:', 'paymendo-bank-transfer-lite' )
				)
			);

			foreach ( $account_detail_rows as $arr ) {
				if ( empty( $account->{$arr['key']} ) ) {
					continue;
				}
				echo sprintf( '<tr>' .
				              '<th>%s</th>' .
				              '<td>%s</td>' .
				              '</tr>', esc_html($arr['label']), esc_html($account->{$arr['key']}) );
			}
			?>
            </tbody>
        </table>
	<?php
	endforeach;
	?>
    </div>

    <form method="post" id="paymendo-bank-transfer-modal">
        <table border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td colspan="2">
                    <p><?php _e( 'You can send a notification by selecting the bank you pay with by wire transfer.', 'paymendo-bank-transfer-lite' ) ?></p>
                </td>
            </tr>
            <tr>
                <th><?php echo __( 'Bank Name', 'paymendo-bank-transfer-lite' ); ?></th>
                <td>
                    <select name="paymendo_bank_transfer_completed_payment">
						<?php foreach ( $bank_accounts as $account ):
                            $bank_object = pbt_get_bank_list($account->bank_slug);
                        ?>
                            <option value="<?php echo esc_attr($account->id) ?>"><?php echo esc_html(WC_Gateway_Paymendo_Bank_Transfer::get_bank_label( $account )) ?></option>
						<?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>&nbsp;</th>
                <td>
                    <input type="hidden" name="order_number" value="<?php echo $order->get_id() ?>">
                    <button type="submit"><?php echo __( 'Submit', 'paymendo-bank-transfer-lite' ); ?></button>
                </td>
            </tr>
        </table>
    </form>
	<?php
}