<?php
/*
 * Plugin Name: Paymendo Bank Transfer
 * Description: Show transfer options and list your bank accounts on your WooCommerce website.
 * Plugin URI:
 * Version: 1.1
 * Author: grilabs
 * Author URI: https://www.gri.net
 * Text Domain: paymendo-bank-transfer-lite
 * Domain Path: /lang/
 */

defined( 'ABSPATH' ) || exit;

function pbt_get_plugin_assets( $suffix ) {
	$base = plugin_dir_url( __FILE__ );
	if ( substr( $suffix, 0, 1 ) !== '/' ) {
		$suffix = '/' . $suffix;
	}

	return $base . 'assets' . $suffix;
}

register_activation_hook( __FILE__, function () {
	global $wpdb;
	$sql = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'paymendo_bank_accounts(
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `bank_slug` varchar(50) COLLATE utf8_turkish_ci NOT NULL,
          `iban` varchar(250) COLLATE utf8_turkish_ci NOT NULL,
          `account_owner` varchar(500) COLLATE utf8_turkish_ci NOT NULL,
          `branch_code` varchar(25) COLLATE utf8_turkish_ci DEFAULT NULL,
          `account_number` varchar(250) COLLATE utf8_turkish_ci DEFAULT NULL,
          `currency` varchar(10) COLLATE utf8_turkish_ci DEFAULT NULL,
          `swift` varchar(150) COLLATE utf8_turkish_ci DEFAULT NULL,
          `note` text COLLATE utf8_turkish_ci DEFAULT NULL,
          `created` datetime NOT NULL DEFAULT current_timestamp(),
          `updated` datetime DEFAULT NULL,
           PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;';
	$wpdb->query( $sql );

	$sql = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'paymendo_transfer_notifications(
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `bank_id` int(11) NOT NULL,
          `order_id` int(11) NOT NULL,
          `payment_status` enum(\'0\',\'1\') COLLATE utf8_turkish_ci NOT NULL DEFAULT \'0\',
          `created` datetime NOT NULL DEFAULT current_timestamp(),
          `updated` datetime DEFAULT NULL,
          PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;';
	$wpdb->query( $sql );
} );

function load_paymendo_bank_transfer_plugin() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		function pbt_woocommerce_need_error() {
			$class   = 'notice notice-error';
			$message = sprintf( __( '<strong>Paymendo Bank Transfer</strong> must use with <a href="%s">WooCommerce</a>!', 'paymendo-bank-transfer-lite' ), admin_url( "plugin-install.php?s=woocommerce&tab=search&type=term" ) );
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );
		}

		add_action( 'admin_notices', 'pbt_woocommerce_need_error' );

		return false;
	}
	require __DIR__ . '/init.php';

	return true;
}

add_action( 'plugins_loaded', 'load_paymendo_bank_transfer_plugin' );
