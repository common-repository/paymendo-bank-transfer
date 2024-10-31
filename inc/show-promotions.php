<?php
/**
 * Created by PhpStorm.
 * File: show-promotions.php
 * Project: paymendo-bank-transfer
 * Year: 2021
 */
try {
	include dirname( __DIR__ ) . '/lib/Grilabs_promotions.php';
	$promo = new \Grilabs\Paymendo\BankTransfer\Grilabs_promotions();
	$ads   = $promo->get_data();

	if ( isset( $ads ) && is_array( $ads ) && $ads ) {
		?>
		<div class="grilabs-ads">
			<div class="promo-text">
				<img width="35" src="<?php echo esc_attr(pbt_get_plugin_assets( '/images/up-arrow.png' )) ?>"/>
				<p>
					<?php
					echo sprintf( __( 'You can <b>fully automated</b> the bank transfer processes on your WooCommerce website. <a class="btn btn-sm btn-outline-success m-2" href="%s" target="_blank">%s</a>', 'paymendo-bank-transfer-lite' ), 'https://www.gri.net/wordpress-eklentileri/', __( 'Upgrade Now', 'paymendo-bank-transfer-lite' ) );
					?>
				</p>
			</div>
			<div class="ad-list">
				<?php
				foreach ( $ads as $ad ) {
					echo '<div class="single-ad">' .
					     '<a href="' . esc_attr($ad->url) . '" target="_blank"><img src="' . esc_attr($ad->image) . '"/></a>' .
					     '</div>';
				}
				?>
			</div>
		</div>
		<?php
	}

} catch ( \Exception $exception ) {
	echo esc_html($exception->getMessage());
}