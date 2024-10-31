<?php

use Grilabs\Paymendo\BankTransfer\PBT_Bank;

function pbt_get_bank_list( $bank_slug = null ) {
	$bank_list                   = array();
	$bank_list['akbank']         = new PBT_Bank( 'Akbank', 'akbank', pbt_get_plugin_assets( '/images/logo/akbank.png' ) );
	$bank_list['albaraka-turk']  = new PBT_Bank( 'Albaraka Türk', 'albaraka', pbt_get_plugin_assets( '/images/logo/albaraka-turk.png' ) );
	$bank_list['denizbank']      = new PBT_Bank( 'Denizbank', 'denizbank', pbt_get_plugin_assets( '/images/logo/denizbank.png' ) );
	$bank_list['garanti']        = new PBT_Bank( 'Garanti Bankası', 'garanti', pbt_get_plugin_assets( '/images/logo/garanti.png' ) );
	$bank_list['halkbank']       = new PBT_Bank( 'Halkbank', 'halkbank', pbt_get_plugin_assets( '/images/logo/halkbank.png' ) );
	$bank_list['ing']            = new PBT_Bank( 'ING', 'ing', pbt_get_plugin_assets( '/images/logo/ing.png' ) );
	$bank_list['is-bankasi']     = new PBT_Bank( 'Türkiye İş Bankası', 'is-bankasi', pbt_get_plugin_assets( '/images/logo/is-bankasi.png' ) );
	$bank_list['kuveytturk']     = new PBT_Bank( 'Kuveyttürk', 'kuveytturk', pbt_get_plugin_assets( '/images/logo/kuveytturk.png' ) );
	$bank_list['qnb-finansbank'] = new PBT_Bank( 'QNB Finansbank', 'qnb-finansbank', pbt_get_plugin_assets( '/images/logo/qnb-finansbank.png' ) );
	$bank_list['sekerbank']      = new PBT_Bank( 'Şekerbank', 'sekerbank', pbt_get_plugin_assets( '/images/logo/sekerbank.png' ) );
	$bank_list['teb']            = new PBT_Bank( 'TEB', 'teb', pbt_get_plugin_assets( '/images/logo/teb.png' ) );
	$bank_list['turkiye-finans'] = new PBT_Bank( 'Türkiye Finans Katılım Bankası', 'turkiye-finans', pbt_get_plugin_assets( '/images/logo/turkiye-finans.png' ) );
	$bank_list['vakifbank']      = new PBT_Bank( 'Vakıfbank', 'vakifbank', pbt_get_plugin_assets( '/images/logo/vakifbank.png' ) );
	$bank_list['vakif-katilim']  = new PBT_Bank( 'Vakıf Katılım', 'vakif-katilim', pbt_get_plugin_assets( '/images/logo/vakif-katilim.png' ) );
	$bank_list['yapi-kredi']     = new PBT_Bank( 'Yapı Kredi', 'yapi-kredi', pbt_get_plugin_assets( '/images/logo/yapi-kredi.png' ) );
	$bank_list['ziraat-bankasi'] = new PBT_Bank( 'Ziraat Bankası', 'ziraat-bankasi', pbt_get_plugin_assets( '/images/logo/ziraat-bankasi.png' ) );
	$bank_list['ziraat-katilim'] = new PBT_Bank( 'Ziraat Katılım', 'ziraat-katilim', pbt_get_plugin_assets( '/images/logo/ziraat-katilim.png' ) );
	$bank_list                   = apply_filters( 'paymendo_bank_transfer_bank_list', $bank_list );

	return $bank_slug !== null && isset( $bank_list[ $bank_slug ] ) ? $bank_list[ $bank_slug ] : $bank_list;
}

function pbt_update_order_status_according_to_notification( $notification_id, $order_status_key = 'processing' ) {
	$notification = pbt_get_transfer_notification_with_id( $notification_id );

	$order = wc_get_order( $notification->order_id );

	$currentUser         = get_userdata( get_current_user_id() );
	$currentUserFullName = $currentUser->first_name . ' ' . $currentUser->last_name;

	$bank_account = pbt_get_bank_account_with_id( $notification->bank_id );

	if ( $order_status_key === 'processing' ) {
		$note = sprintf( __( 'This payment approved by %s.(%s)', 'paymendo-bank-transfer-lite' ), $currentUserFullName, WC_Gateway_Paymendo_Bank_Transfer::get_bank_label( $bank_account ) );
		$order->add_order_note( $note );
		update_post_meta( $order->get_id(), 'paymendo_bank_transfer_bank_id', $notification->bank_id );
		pbt_send_sms_to_customer( $order );
	} elseif ( $order_status_key === 'on-hold' ) {
		$note = sprintf( __( 'This payment canceled by %s.(%s)', 'paymendo-bank-transfer-lite' ), $currentUserFullName, WC_Gateway_Paymendo_Bank_Transfer::get_bank_label( $bank_account ) );
		$order->add_order_note( $note );
		delete_post_meta( $order->get_id(), 'paymendo_bank_transfer_bank_id' );
	}
	$order->update_status( $order_status_key );
}

function pbt_send_email_to_admin($order_id ) {
	if ( ! pbt_is_admin_email_enabled() ) {
		return;
	}
	$mailer = WC()->mailer()->get_emails();
	$mailer['Paymendo_Bank_Transfer_Completed']->trigger( $order_id );
}

function pbt_send_sms_to_admin($order, $bank_account ) {
	if ( ! pbt_is_admin_sms_enabled() || empty( pbt_get_admin_sms_number() ) || !function_exists('wp_sms_send_sms') ) {
		return;
	}

	$savedMsgSendToAdmin = pbt_get_admin_sms_message();
	$oldMsgShortCodes    = array(
		'{customer_name}',
		'{bank_name_with_currency}'
	);
	$newMsgReplaced      = array(
		$order->get_formatted_billing_full_name(),
		WC_Gateway_Paymendo_Bank_Transfer::get_bank_label( $bank_account )
	);

	$msg = str_replace( $oldMsgShortCodes, $newMsgReplaced, $savedMsgSendToAdmin );
	// control sms notification enable / disable AND get sms message and admin phone number from options FOR ADMIN
	wp_sms_send_sms( pbt_get_admin_sms_number(), $msg );
}

function pbt_send_sms_to_customer($order, $private_message = null ) {
	if ( ! pbt_is_customer_sms_enabled() || !function_exists('wp_sms_send_sms') ) {
		return;
	}

	if ( $private_message === null ) {
		$savedMsgSendToCustomer = pbt_get_customer_sms_message();
		$oldMsgShortCodes       = array(
			'{customer_name}',
			'{order_id}',
			'{order_number}'
		);
		$newMsgReplaced         = array(
			$order->get_formatted_billing_full_name(),
			$order->get_id(),
			$order->get_order_number()
		);
		$msg                    = str_replace( $oldMsgShortCodes, $newMsgReplaced, $savedMsgSendToCustomer );
	} else {
		$msg = $private_message;
	}

	wp_sms_send_sms( $order->get_billing_phone(), $msg );
}

function pbt_send_sms_and_email_to_admin( $order_id, $bank_account ) {
	$order = wc_get_order( $order_id );
	pbt_send_sms_to_admin( $order, $bank_account );
	pbt_send_email_to_admin( $order_id );
}

function pbt_is_admin_sms_enabled() {
	return get_option( 'paymendo_bank_transfer_admin_sms_enabled', 'off' ) === 'on';
}

function pbt_get_admin_sms_message() {
	return get_option( 'paymendo_bank_transfer_admin_sms_message' );
}

function pbt_get_admin_sms_number() {
	return get_option( 'paymendo_bank_transfer_admin_sms_number' );
}

function pbt_is_admin_email_enabled() {
	return get_option( 'paymendo_bank_transfer_admin_email_enabled', 'off' ) === 'on';
}

function pbt_is_customer_sms_enabled() {
	return get_option( 'paymendo_bank_transfer_customer_sms_enabled', 'off' ) === 'on';
}

function pbt_get_customer_sms_message() {
	return get_option( 'paymendo_bank_transfer_customer_sms_message' );
}

function pbt_generate_account_card_body($account = null, $key = '' ) {
	$disabled = '';
	if ( is_null( $account ) ) {
		$account                 = new \stdClass();
		$account->bank_slug      = '';
		$account->id             = '';
		$account->account_owner   = '';
		$account->iban           = '';
		$account->branch_code    = '';
		$account->account_number = '';
		$account->currency       = get_woocommerce_currency();
		$account->swift          = '';
		$account->note           = '';
		$disabled                = ' disabled';
	}
	$out = "";

	$out       .= '<div class="card-header border-bottom bg-light"><div class="d-flex position-relative">';
	$dataValue = $account->id ? ' data-value=' . esc_attr($account->id) . '' : '';
	$out       .= '<p class="w-100">';
	$out       .= '<label for="paymendo_bank_transfer_' . esc_attr($key) . '_bank_slug">';
	$out       .= esc_html(__( 'Select Bank', 'paymendo-bank-transfer-lite' ));
	$out       .= '</label>';
	$out       .= '<select id="paymendo_bank_transfer_' . esc_attr($key) . '_bank_slug" class="form-control bank-name-select" name="paymendo_bank_transfer[' . esc_attr($key) . '][bank_slug]">';
	$bank_list = pbt_get_bank_list();
	foreach ( $bank_list as $bank ):
		if ( ! ( is_a( $bank, 'Grilabs\Paymendo\BankTransfer\PBT_Bank') ) ) {
			continue;
		}
		$selected = $account->bank_slug === $bank->getSlug() ? ' selected' : '';
		$out      .= '<option value="' . esc_attr($bank->getSlug()) . '"' . $selected . '>' . esc_html($bank->getBankName()) . '</option>';
	endforeach;
	$out .= '</select>';
	$out .= '</p>';
	$out .= '<div class="text-center"><a href="#" class="btn btn-sm btn-danger delete-account"' . esc_attr($dataValue) . '><i class="fa fa-times"></i></a></div>';
	$out .= '</div>' .
	        '</div>';
	$out .= '<div class="card-body p-3">';
	$out .= '<p>' .
	        '<label for="paymendo_bank_transfer_' . esc_attr($key) . '_account_owner">' . esc_html(__( 'Account Owner', 'paymendo-bank-transfer-lite' )) . '</label>';
	$out .= '<input id="paymendo_bank_transfer_' . esc_attr($key) . '_account_owner" type="text" class="account-name-text form-control" name="paymendo_bank_transfer[' . esc_attr($key) . '][account_owner]" value="' . esc_attr($account->account_owner) . '" required ' . esc_attr($disabled) . ' />' .
	        '</p>';


	$out .= '<p>
    <label for="paymendo_bank_transfer_' . esc_attr($key) . '_iban">' . esc_html(__( 'IBAN', 'paymendo-bank-transfer-lite' )) . '</label>
    <input type="text" class="iban-text form-control"
           id="paymendo_bank_transfer_' . esc_attr($key) . '_iban"
           name="paymendo_bank_transfer[' . esc_attr($key) . '][iban]"
           value="' . esc_attr($account->iban) . '" required' . esc_attr($disabled) . '>
</p>';

	$out .= '<div class="more-settings" style="display: none">';
	$out .= '<div class="row">
        <div class="col-5">
            <label for="paymendo_bank_transfer_' . esc_attr($key) . '_branch_code">' . esc_html(__( 'Branch Code', 'paymendo - bank - transfer' )) . '</label>
            <input type="text" class="branch-code-text form-control"
                   id="paymendo_bank_transfer_' . esc_attr($key) . '_branch_code"
                   name="paymendo_bank_transfer[' . esc_attr($key) . '][branch_code]"
                   value="' . esc_attr($account->branch_code) . '"' . esc_attr($disabled) . '>
        </div>
        <div class="col-7">
            <label for="paymendo_bank_transfer_' . esc_attr($key) . '_account_number">' . esc_html(__( 'Account Number', 'paymendo-bank-transfer-lite' )) . '</label>
            <input type="text" class="account-number-text form-control"
                   id="paymendo_bank_transfer_' . esc_attr($key) . '_account_number"
                   name="paymendo_bank_transfer[' . esc_attr($key) . '][account_number]"
                   value="' . esc_attr($account->account_number) . '"' . esc_attr($disabled) . '>
        </div>
    </div>';

	$out      .= '<p class="mt-2">
        <label for="paymendo_bank_transfer_' . esc_attr($key) . '_currency">' . esc_html(__( 'Currency', 'paymendo-bank-transfer-lite' )) . '</label>
        <select
        	id="paymendo_bank_transfer_' . esc_attr($key) . '_currency"
            class="currency-select form-control"
            name="paymendo_bank_transfer[' . esc_attr($key) . '][currency]"
            ' . esc_attr($disabled) . '>';
	$selected = false;

	$currency_selected = $account->currency;
	foreach ( WC_Gateway_Paymendo_Bank_Transfer::get_currency() as $key => $item ) {
		$selected = ! $selected && $key === $currency_selected ? " selected" : null;
		$out      .= sprintf( '<option value="%s"%s>%s</option>', esc_attr($key), esc_attr($selected), esc_html($item) );
	}

	$out .= '</select></p>';

	$out .= '<p>
    <label for="paymendo_bank_transfer_' . esc_attr($key) . '_swift">' . esc_html(__( 'BIC / Swift', 'paymendo-bank-transfer-lite' )) . '</label>
    <input type="text" class="swift-text form-control"
           id="paymendo_bank_transfer_' . esc_attr($key) . '_swift"
           name="paymendo_bank_transfer[' . esc_attr($key) . '][swift]"
           value="' . esc_attr($account->swift) . '"' . esc_attr($disabled) . '>
</p>';

	$out .= '<p>
    <label for="paymendo_bank_transfer_' . esc_attr($key) . '_note">' . esc_html(__( 'Note', 'paymendo-bank-transfer-lite' )) . '</label>
    <textarea class="note-textarea form-control"
              name="paymendo_bank_transfer[' . esc_attr($key) . '][note]"
              cols="30"
              rows="3"
              placeholder="' . esc_attr(__( 'If there is a special detail for this option, you can add it.', 'paymendo-bank-transfer-lite' )) . '"' . esc_attr($disabled) . '>' . esc_textarea($account->note) . '</textarea>
</p>';


	$out .= '</div>';
	$out .= '<p class="text-center">
    <button type="button" style="font-size: 11px" class="btn btn-sm btn-outline-primary more-button"
            data-lesstext="' . esc_html(__( 'Basic', 'paymendo-bank-transfer-lite' )) . '"
            data-moretext="' . esc_html(__( 'Advanced', 'paymendo-bank-transfer-lite' )) . '">' . esc_html(__( 'Advanced', 'paymendo-bank-transfer-lite' )) . '</button></p>';
	$out .= '</div>';

	$out .= ' <input type="hidden" class="id-text"
                                   name="paymendo_bank_transfer[' . esc_attr($key) . '][id]"
                                   value="' . esc_attr($account->id) . '"' . esc_attr($disabled) . '>';

	return $out;
}