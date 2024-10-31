<?php

if ( ! function_exists( 'paymendo_bank_transfer_set_banks_page' ) ) {
	function paymendo_bank_transfer_set_banks_page() {
		$page_title       = __( "Bank Accounts", 'paymendo-bank-transfer-lite' );
		$page_description = __( "You can define the bank accounts of your store here. Defined banks can be viewed by your customers.", 'paymendo-bank-transfer-lite' );
		require_once dirname( __FILE__ ) . '/blocks/header.php';
		?>


        <div id="default-bank-card" style="display:none;" class="col-md-4 card-collapser">
            <div class="card single-bank-account-card p-0">
				<?php echo wp_kses(pbt_generate_account_card_body( null, "0" ),PBT_ALLOWED_HTML) ?>
            </div>
        </div>

        <form method="POST">
            <div class="p-3">
                <div id="banks-list" class="row justify-content-center">
					<?php
					    $bank_accounts = pbt_get_bank_accounts();
					    foreach ( $bank_accounts as $account ):
                    ?>
                        <div class="col-md-4 card-collapser">
                            <div class="card border single-bank-account-card p-0">
								<?php echo wp_kses(pbt_generate_account_card_body( $account, key( $bank_accounts ) ),PBT_ALLOWED_HTML) ?>
                            </div><!--//card-->
                        </div><!--//col -->
						<?php next( $bank_accounts ); endforeach; ?>
                    <div class="col-md-4 align-self-center">
                        <div class="card border text-center p-5 cursor-pointer" id="new-row">
                            <button type="button" class="btn btn-outline-primary align-self-center p-0"
                                    style="border-radius: 100%; line-height: 60px; width: 60px">
                                <i class="fa fa-plus"></i>
                            </button>
                            <span class="text-primary mt-2"><?php _e('ADD ACCOUNT','paymendo-bank-transfer-lite') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-right mt-3 border-top bg-light p-3">
                <button type="submit"
                        class="btn btn-primary"><?php echo esc_html(__( 'Save Changes', 'paymendo-bank-transfer-lite' )) ?></button>
            </div>
        </form>
		<?php
		require_once dirname( __FILE__ ) . '/blocks/footer.php';
	}
}