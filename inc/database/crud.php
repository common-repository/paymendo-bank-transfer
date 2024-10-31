<?php

function pbt_get_bank_accounts()
{
    global $wpdb;
    $table = $wpdb->prefix . TABLE_NAME_PAYMENDO_BANK_ACCOUNTS;

    return $wpdb->get_results('SELECT * FROM ' . $table);
}

function pbt_get_bank_account_with_id($id)
{
    global $wpdb;

    return $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . TABLE_NAME_PAYMENDO_BANK_ACCOUNTS . ' WHERE id=' . $id);
}

function pbt_update_bank_account($id, $data)
{
    global $wpdb;
    $data['updated'] = date('Y-m-d H:i:sa');

    return $wpdb->update($wpdb->prefix . TABLE_NAME_PAYMENDO_BANK_ACCOUNTS, $data, ['id' => $id]);
}

function pbt_add_bank_account($data)
{
    global $wpdb;

    return $wpdb->insert($wpdb->prefix . TABLE_NAME_PAYMENDO_BANK_ACCOUNTS, $data);
}

function pbt_delete_bank_account($id)
{
    global $wpdb;
    if ($id !== '')
        do_action('pbt_delete_notifications_after_deleted_bank', $id);
    return $wpdb->delete($wpdb->prefix . TABLE_NAME_PAYMENDO_BANK_ACCOUNTS, ['id' => $id]);
}

function pbt_add_transfer_notification($data)
{
    global $wpdb;

    return $wpdb->insert($wpdb->prefix . TABLE_NAME_PAYMENDO_TRANSFER_NOTIFICATIONS, $data);
}

function pbt_get_transfer_notifications($offset = null, $limit = null)
{
    global $wpdb;
    $query = 'SELECT * FROM ' . $wpdb->prefix . TABLE_NAME_PAYMENDO_TRANSFER_NOTIFICATIONS . ' ORDER BY created DESC ';
    $query .= $limit !== null && $offset !== null ? 'LIMIT ' . $offset . ',' . $limit : '';

    return $wpdb->get_results($query);
}

function pbt_get_transfer_notification_with_join($where, $offset = null, $limit = null, $select = null, $multiple = true, $orderBy = 'notifications.created', $orderDir = 'DESC')
{
    global $wpdb;
    $table_notification = $wpdb->prefix . TABLE_NAME_PAYMENDO_TRANSFER_NOTIFICATIONS;

    if (is_null($select)) {
        $select = 'notifications.*, accounts.bank_slug, accounts.currency, total_meta.meta_value AS \'amount\', CONCAT(firstname_meta.meta_value, \' \',lastname_meta.meta_value) AS \'customer_full_name\'';
    }

    $query = 'SELECT ' . $select . ' FROM ' . $table_notification . ' AS notifications 
    LEFT JOIN ' . $wpdb->prefix . 'paymendo_bank_accounts as accounts ON notifications.bank_id = accounts.id
    LEFT JOIN ' . $wpdb->prefix . 'postmeta AS total_meta ON (notifications.order_id = total_meta.post_id AND total_meta.meta_key = \'_order_total\')
    LEFT JOIN ' . $wpdb->prefix . 'postmeta AS firstname_meta ON (notifications.order_id = firstname_meta.post_id AND firstname_meta.meta_key = \'_shipping_first_name\')
    LEFT JOIN ' . $wpdb->prefix . 'postmeta AS lastname_meta ON (notifications.order_id = lastname_meta.post_id AND lastname_meta.meta_key = \'_shipping_last_name\') 
    WHERE TRUE AND ';

    $query .= !empty($where) ? $where : 'TRUE ';
    $query .= 'ORDER BY ' . $orderBy . ' ' . $orderDir;
    $query .= $limit !== null && $offset !== null ? ' LIMIT ' . $offset . ',' . $limit : '';


    if ($multiple) {
        return $wpdb->get_results($query);
    } else {
        return $wpdb->get_row($query);
    }
}

function pbt_get_transfer_notifications_count()
{
    global $wpdb;

    return $wpdb->get_row('SELECT COUNT(\'id\') AS total_result FROM ' . $wpdb->prefix . TABLE_NAME_PAYMENDO_TRANSFER_NOTIFICATIONS);
}

function pbt_get_transfer_notification_with_id($notification_id)
{
    global $wpdb;

    return $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . TABLE_NAME_PAYMENDO_TRANSFER_NOTIFICATIONS . ' WHERE id=' . $notification_id);
}

function pbt_get_transfer_notification_with_order_id($order_id)
{
    global $wpdb;

    return $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . TABLE_NAME_PAYMENDO_TRANSFER_NOTIFICATIONS . ' WHERE order_id=' . $order_id);
}

function pbt_update_transfer_notification_to_payment_status($notification_id, $status = 1)
{
    global $wpdb;
    $updatedDate = date('Y-m-d H:i:sa');
    $update = $wpdb->update($wpdb->prefix . TABLE_NAME_PAYMENDO_TRANSFER_NOTIFICATIONS, [
        'payment_status' => $status,
        'updated' => $updatedDate
    ], ['id' => $notification_id]);
    if (!empty($update)) {
        if ($status == 1) {
            do_action('pbt_payment_completed', $notification_id);
        } else {
            do_action('pbt_payment_canceled', $notification_id);
        }
    }

    return $update;
}

function pbt_delete_transfer_notification($notification_id)
{
    global $wpdb;
    $delete = $wpdb->delete($wpdb->prefix . TABLE_NAME_PAYMENDO_TRANSFER_NOTIFICATIONS, ['id' => $notification_id]);
    if (!empty($delete)) {
        do_action('pbt_payment_deleted');
    }
    return $delete;
}

function pbt_delete_notifications_according_to_bank_id($bank_id)
{
    global $wpdb;
    return $wpdb->delete($wpdb->prefix . TABLE_NAME_PAYMENDO_TRANSFER_NOTIFICATIONS, ['bank_id' => $bank_id]);
}

