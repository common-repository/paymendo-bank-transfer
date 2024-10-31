<?php


function paymendo_bank_transfer_edit_order_details_page($order)
{
    if (is_checkout() && empty(is_wc_endpoint_url('view-order'))) {
        return;
    }
    require(__DIR__ . '/static/bank-accounts.php');
}

add_action('woocommerce_order_details_before_order_table', 'paymendo_bank_transfer_edit_order_details_page');