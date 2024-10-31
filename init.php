<?php

const TABLE_NAME_PAYMENDO_BANK_ACCOUNTS = 'paymendo_bank_accounts';
const TABLE_NAME_PAYMENDO_TRANSFER_NOTIFICATIONS = 'paymendo_transfer_notifications';

function pbt_set_allowed_html($elements = array())
{
    $allowed_html = array();
    foreach ($elements as $element) {
        $allowed_html[$element] = [
            'id' => true,
            'class' => true,
            'for' => true,
            'name' => true,
            'data-value' => true,
            'disabled' => true,
            'checked' => true,
            'value' => true,
            'href' => true,
            'type' => true,
            'required' => true,
            'style' => true,
            'onclick' => true,
            'placeholder' => true,
            'cols' => true,
            'rows' => true,
            'data-lesstext' => true,
            'data-moretext' => true,
            'data-opened' => true,
            'selected' => true
        ];
    }
    return $allowed_html;
}

if(!defined('PBT_ALLOWED_HTML')) {
	define( "PBT_ALLOWED_HTML",
		pbt_set_allowed_html( array(
			'div',
			'p',
			'label',
			'for',
			'select',
			'option',
			'a',
			'i',
			'input',
			'button',
			'textarea'
		) ) );
}

function pbt_requireFiles($folder, $extension = null, $exclude = [])
{
    foreach (glob(__DIR__ . '/' . $folder . '/*' . $extension) as $file) {
        $file_clear = str_replace(__DIR__ . '/' . $folder . '/', '', $file);
        if (!in_array($file_clear, $exclude)) {
            require $file;
        }
    }
}

pbt_requireFiles('inc', '.php', array(
    'show-promotions.php'
));
pbt_requireFiles('inc/database', '.php');
pbt_requireFiles('lib', '.php');
pbt_requireFiles('views', '.php');
pbt_requireFiles('views/site', '.php');

require __DIR__ . '/PaymendoBankTransfer.php';

// Plugin Text Domain
$plugin_rel_path = basename(dirname(__FILE__)) . '/lang';
load_plugin_textdomain('paymendo-bank-transfer-lite', false, $plugin_rel_path);
