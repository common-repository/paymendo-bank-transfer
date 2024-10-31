<?php

if ( ! class_exists( 'WC_Gateway_Paymendo_Bank_Transfer' ) && class_exists( 'WC_Payment_Gateway' ) ) {
	class WC_Gateway_Paymendo_Bank_Transfer extends WC_Payment_Gateway {
		public function __construct() {
			$this->id                 = 'paymendo_bank_transfer';
			$this->icon               = '';
			$this->has_fields         = ! true;
			$this->method_title       = __( 'paymendo - Bank Transfer', 'paymendo-bank-transfer-lite' );
			$this->method_description = __( 'Define your bank accounts and keep control of incoming payments by bank transfer.', 'paymendo-bank-transfer-lite' );

			$this->supports = array(
				'products'
			);

			$this->init_form_fields();
			$this->init_settings();

			$this->title       = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );
			$this->enabled     = $this->get_option( 'enabled' );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
				$this,
				'process_admin_options'
			) );
		}

		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'     => array(
					'title'       => __( 'Enable/Disable', 'paymendo-bank-transfer-lite' ),
					'label'       => __( 'Enable Paymendo EFT/Transfer Method', 'paymendo-bank-transfer-lite' ),
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'no'
				),
				'title'       => array(
					'title'       => __( 'Title', 'paymendo-bank-transfer-lite' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'paymendo-bank-transfer-lite' ),
					'default'     => __( 'Bank Transfer', 'paymendo-bank-transfer-lite' ),
					'desc_tip'    => true,
				),
				'description' => array(
					'title'       => __( 'Description', 'paymendo-bank-transfer-lite' ),
					'type'        => 'textarea',
					'description' => __( 'This controls the description which the user sees during checkout.', 'paymendo-bank-transfer-lite' ),
					'default'     => __( 'Payment option by bank transfer!', 'paymendo-bank-transfer-lite' ),
				)
			);
		}

		public function payment_fields() {
			$description = $this->get_description();
			if ( $description ) {
				echo wp_kses_post(wpautop( wptexturize( $description ) ));
			}

			$bank_accounts_in_database = pbt_get_bank_accounts();
            $bank_accounts_in_system = pbt_get_bank_list();
            echo '<fieldset class="checkout-banks-fieldset">';

			echo '<div class="checkout-banks-logo">';
			$temp_account = array();
			foreach ( $bank_accounts_in_database as $account ) {
                $account = $bank_accounts_in_system[$account->bank_slug];
				if ( ! in_array( $account->logo, $temp_account ) ) //To not show the same logo again
				{
					echo '<img src="' . esc_attr($account->logo) . '">';
				}
				$temp_account[] = $account->logo;
			}
			echo '</div>';
			echo '<div class="clear"></div></fieldset>';
		}

		public function validate_fields() {
			if ( empty( $_POST['billing_first_name'] ) ) {
				return false;
			}

			return true;
		}

		public function process_payment( $order_id ) {
			global $woocommerce;

			// we need it to get any order details
			$order = wc_get_order( $order_id );
			$order->payment_complete();
			$order->update_status( 'on-hold' );

			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order )
			);
		}

		public static function get_bank_label( $account_row = null ) {
			if ( is_null( $account_row ) ) {
				return;
			}

			$account_object = new \stdClass();
			if ( isset( $account_row->bank_slug ) ) {
				$account_object = pbt_get_bank_list( $account_row->bank_slug );
			}
			$bank_label = "";
			$bank_label .= $account_object->getBankName();
			$bank_label .= ' - ';
			$bank_label .= $account_row->currency;

			return apply_filters( 'paymendo_bank_transfer_bank_label', $bank_label );
		}

		public static function get_currency( $single = null ) {
			if ( ! is_null( $single ) ) {
				return get_woocommerce_currency( $single );
			}

			return get_woocommerce_currencies();
		}
	}
}
new WC_Gateway_Paymendo_Bank_Transfer();