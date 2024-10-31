<?php

function paymendo_bank_transfer_edit_order_received_page($defaultText, $order)
{
    ?>
    <p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received">
        <?php echo esc_html($defaultText); ?>
    </p>
    <?php
    require_once(__DIR__ . '/static/bank-accounts.php');
}

add_filter('woocommerce_thankyou_order_received_text', 'paymendo_bank_transfer_edit_order_received_page', 10, 2);


