<?php

defined('ABSPATH') || exit;

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action('woocommerce_email_header', $email_heading, $email); ?>

    <p><?php printf(esc_html__('Bank transfer of your order(#%s) has been completed!', 'paymendo-bank-transfer-lite'), $order->get_order_number()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
    <p>
        <?php echo esc_html__('You can view the transfer information following.', 'paymendo-bank-transfer-lite') ?>
    </p>
    <table>
        <tbody>
        <tr>
            <th><?php echo esc_html__('Bank', 'paymendo-bank-transfer-lite') ?></th>
            <td>
                <img src="<?php echo esc_attr($bank_in_system->logo) ?>" alt="<?php echo esc_attr($bank_in_system->bank_name) ?> Logo">
            </td>
        </tr>
        <tr>
            <th><?php echo esc_html__('Account Owner', 'paymendo-bank-transfer-lite') ?></th>
            <td>
                <?php echo esc_html($bank_object->account_owner) ?>
            </td>
        </tr>
        <tr>
            <th><?php echo esc_html__('IBAN', 'paymendo-bank-transfer-lite') ?></th>
            <td>
                <?php echo esc_html($bank_object->iban) ?>
            </td>
        </tr>
        <tr>
            <th><?php echo esc_html__('Currency', 'paymendo-bank-transfer-lite') ?></th>
            <td>
                <?php echo esc_html($bank_object->currency) ?>
            </td>
        </tr>
        <?php if (!empty($bank_object->note)): ?>
            <tr>
                <th><?php echo esc_html__('Your note', 'paymendo-bank-transfer-lite') ?></th>
                <td>
                    <?php echo esc_html($bank_object->note) ?>
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
    <p>&nbsp;</p>
<?php


/*
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 * @since 2.5.0
 */
do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email);

/*
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email);

/*
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email);

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ($additional_content) {
    echo wp_kses_post(wpautop(wptexturize($additional_content)));
}

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action('woocommerce_email_footer', $email);
