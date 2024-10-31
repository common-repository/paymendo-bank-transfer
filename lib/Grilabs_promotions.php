<?php
/**
 * Created by PhpStorm.
 * File: Grilabs_promotiosns.php
 * Project: paymendo-bank-transfer
 * Year: 2021
 */

namespace Grilabs\Paymendo\BankTransfer;

if(!class_exists("Grilabs\Paymendo\BankTransfer\Grilabs_promotions")) {
	class Grilabs_promotions {
		const PROMO_URL = "https://vpos-services.grilabs.net/promotions/paymendo-bank-transfer.php";
		private $data = "";

		public function __construct() {
			$this->read();
		}

		public function read() {
			$request    = wp_remote_get( esc_url_raw( self::PROMO_URL ), array(
				'headers' => array( 'referer' => site_url() )
			) );
			$this->data = wp_remote_retrieve_body( $request );
		}

		public function get_data() {
			return json_decode( $this->data );
		}
	}
}