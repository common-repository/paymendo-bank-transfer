<?php

class PaymendoTransferCompleted extends WC_Email {
    public function __construct() {
        $this->id             = 'paymendo_bank_transfer_completed';
        $this->title          = __( 'Bank Transfer Completed', 'paymendo-bank-transfer-lite' );
        $this->description    = __( 'This email is received when the bank transfer completed of the order.', 'paymendo-bank-transfer-lite' );
        $this->template_base  = __DIR__;
        $this->template_html  = '/templates/template-payment-completed.php';

        parent::__construct();

        $this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
    }

    public function get_default_subject() {
        return __( '[{site_title}]: Transfer payment has been completed #{order_number}', 'paymendo-bank-transfer-lite' );
    }

    public function get_default_heading() {
        return __( 'Transfer payment has been completed: #{order_number}', 'paymendo-bank-transfer-lite' );
    }

    public function get_default_additional_content() {
        return '';
    }

    public function get_content_html() {
        $obj                = $this->object;

        $notification = pbt_get_transfer_notification_with_order_id($obj->get_id());
        $bank_account_obj = pbt_get_bank_account_with_id($notification->bank_id);

        $bank_slug = $bank_account_obj->bank_slug;
        $bank_in_system = pbt_get_bank_list($bank_slug);

        return wc_get_template_html(
            $this->template_html,
            array(
                'order'              => $obj,
                'bank_object' => $bank_account_obj,
                'bank_in_system' => $bank_in_system,
                'email_heading'      => $this->get_heading(),
                'additional_content' => $this->get_additional_content(),
                'sent_to_admin'      => true,
                'plain_text'         => false,
                'email'              => $this,
            ),
            '', $this->template_base
        );
    }

    public function trigger( $order_id, $order = false ) {
        $this->setup_locale();
        if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
            $order = wc_get_order( $order_id );
        }

        if ( is_a( $order, 'WC_Order' ) ) {
            $this->object                         = $order;
            $this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
            $this->placeholders['{order_number}'] = $this->object->get_order_number();
        }

        if ( $this->is_enabled() && $this->get_recipient() ) {
            $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
        }

        $this->restore_locale();
    }

}

return new PaymendoTransferCompleted();
