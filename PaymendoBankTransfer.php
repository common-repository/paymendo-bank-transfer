<?php

namespace Grilabs\Paymendo\BankTransfer;
if (!class_exists('\Grilabs\Paymendo\BankTransfer\PaymendoBankTransfer')) {
    class PaymendoBankTransfer
    {
        public $plugin_file;

        public function __construct()
        {
//            $this->plugin_file = __DIR__ . '/paymendo-bank-transfer.php';
//            register_deactivation_hook($this->plugin_file, array($this, 'plugin_deactivate'));
            add_action('admin_enqueue_scripts', array($this, 'paymendo_bank_transfer_load_assets'));
            add_action('wp_enqueue_scripts', array($this, 'paymendo_bank_transfer_load_site_assets'));
            add_action('admin_menu', array($this, 'paymendo_bank_transfer'));
            add_filter('woocommerce_payment_gateways', array($this, 'paymendo_bank_transfer_add_gateway_class'));
            add_action('admin_init', array($this, 'paymendo_bank_transfer_save_bank')); //banks page
            add_action('wp_ajax_paymendo_bank_transfer_bank_delete', array(
                $this,
                'wp_ajax_delete_bank'
            )); //banks page - delete bank
            add_action('wp_ajax_paymendo_bank_transfer_payments', array(
                $this,
                'wp_ajax_payments_complete'
            )); //payments page
            add_action('wp_ajax_paymendo_bank_transfer_payments_data', array(
                $this,
                'wp_ajax_payments_data'
            )); //payments page
            add_action('wp_ajax_paymendo_bank_transfer_cancel_payment', array(
                $this,
                'wp_ajax_payments_cancel'
            )); //payments page - cancel payment
            add_action('wp_ajax_paymendo_bank_transfer_delete_payment', array(
                $this,
                'wp_ajax_payments_delete'
            )); //payments page - delete payment
            add_action('wp_ajax_paymendo_bank_transfer_sms_for_deleted_payment', array(
                $this,
                'wp_ajax_payments_deleted_sms'
            )); //payments page - delete payment sms
            add_action('admin_init', array($this, 'paymendo_bank_transfer_save_settings')); //settings page

            /*site*/
            add_action('wp_ajax_paymendo_bank_transfer', array(
                $this,
                'wp_ajax_notify_the_payment'
            )); //bank transfer notification for admin
            add_action('wp_ajax_nopriv_paymendo_bank_transfer', array(
                $this,
                'wp_ajax_notify_the_payment'
            )); //bank transfer notification for user
            /**/

            add_filter('woocommerce_email_classes', array($this, 'email_class'));

            /*custom actions*/
            add_action('pbt_payment_completed', array($this, 'payment_completed_action'));
            add_action('pbt_payment_canceled', array($this, 'payment_canceled_action'));
            add_action('pbt_delete_notifications_after_deleted_bank', array($this, 'delete_notifications'));
        }

        public function payment_completed_action($notification_id)
        {
            pbt_update_order_status_according_to_notification($notification_id);
        }

        public function payment_canceled_action($notification_id)
        {
            pbt_update_order_status_according_to_notification($notification_id, 'on-hold');
        }

        public function delete_notifications($bank_id)
        {
            pbt_delete_notifications_according_to_bank_id($bank_id);
        }

//        public function plugin_deactivate()
//        {
//
//        }

        public function paymendo_bank_transfer_load_assets()
        {

            wp_enqueue_script('jquery-ui-slider');
            wp_enqueue_script('selectWoo');
            wp_enqueue_style('select2');

            wp_enqueue_style('paymendo_bank_transfer_admin_css', pbt_get_plugin_assets('/css/main.css'), false, '1.0.0');
            wp_enqueue_style('paymendo_bank_transfer_font_awesome', pbt_get_plugin_assets('/css/font_awesome.css'));


            wp_enqueue_script('paymendo_bank_transfer_moment_js_with_tr', pbt_get_plugin_assets('/js/moment-with-locales.min.js'), array());
            wp_enqueue_script('paymendo_bank_transfer_daterangepicker', pbt_get_plugin_assets('/js/daterangepicker.js'), array());
            wp_enqueue_script('paymendo_bank_transfer_admin_js', pbt_get_plugin_assets('/js/main.js'), array());
            wp_enqueue_script('paymendo_bank_transfer_range_slider', pbt_get_plugin_assets('/js/ion.rangeSlider.min.js'), array());

            wp_enqueue_script('paymendo_bank_transfer__modal_js', pbt_get_plugin_assets('/site/js/modal.js'), array());
            if (isset($_GET['page']) && $_GET['page'] === 'paymendo-bank-transfer-payments') {
                wp_enqueue_style('paymendo_bank_transfer_datatable_css', pbt_get_plugin_assets('/css/jquery.dataTables.min.css'));
                wp_enqueue_script('paymendo_bank_transfer_datatable_js', pbt_get_plugin_assets('/js/jquery.dataTables.min.js'), array());
            }
            wp_localize_script('paymendo_bank_transfer_admin_js', 'paymendo_bank_transfer_extra',
                array('sms_enabled' => function_exists('wp_sms_send_sms'))
            );

            wp_localize_script('paymendo_bank_transfer_admin_js', 'paymendo_bank_transfer_lang',
                array(
                    'total_amount' => __('Total Amount: %s', 'paymendo-bank-transfer-lite'),
                    'sure_text' => __('Are you sure?', 'paymendo-bank-transfer-lite'),
                    'error_msg' => __('An error has occurred.', 'paymendo-bank-transfer-lite'),
                    'loading_gif' => pbt_get_plugin_assets('/images/loading.gif'),
                    'confirm_payment' => __('Confirm', 'paymendo-bank-transfer-lite'),
                    'cancel_payment' => __('Cancel the Payment', 'paymendo-bank-transfer-lite'),
                    'delete_payment' => __('Delete', 'paymendo-bank-transfer-lite'),
                    'confirmed' => __('Confirmed', 'paymendo-bank-transfer-lite'),
                    'set_unconfirmed' => __('Set Unconfirmed', 'paymendo-bank-transfer-lite'),
                    'date_range' => array(
                        'today' => __('Today', 'paymendo-bank-transfer-lite'),
                        'yesterday' => __('Yesterday', 'paymendo-bank-transfer-lite'),
                        'last_a_week' => __('Last a Week', 'paymendo-bank-transfer-lite'),
                        'last_a_month' => __('Last a Month', 'paymendo-bank-transfer-lite'),
                        'this_month' => __('This Month', 'paymendo-bank-transfer-lite'),
                        'last_month' => __('Last Month', 'paymendo-bank-transfer-lite'),
                        'this_year' => __('This Year', 'paymendo-bank-transfer-lite'),
                        'last_year' => __('Last Year', 'paymendo-bank-transfer-lite'),
                        'custom_range' => __('Custom Range', 'paymendo-bank-transfer-lite')
                    ),
                    'datatable' => array(

                        "decimal" => "",
                        "emptyTable" => __("No data available in table", 'paymendo-bank-transfer-lite'),
                        "info" => __("Showing _START_ to _END_ of _TOTAL_ entries", 'paymendo-bank-transfer-lite'),
                        "infoEmpty" => __("Showing 0 to 0 of 0 entries", 'paymendo-bank-transfer-lite'),
                        "infoFiltered" => __("(filtered from _MAX_ total entries)", 'paymendo-bank-transfer-lite'),
                        "infoPostFix" => "",
                        "thousands" => ",",
                        "lengthMenu" => __("Show _MENU_ entries", 'paymendo-bank-transfer-lite'),
                        "loadingRecords" => __("Loading...", 'paymendo-bank-transfer-lite'),
                        "processing" => __("Processing...", 'paymendo-bank-transfer-lite'),
                        "search" => "",
                        "zeroRecords" => __("No matching records found", 'paymendo-bank-transfer-lite'),
                        "paginate" => array(
                            "first" => __("First", 'paymendo-bank-transfer-lite'),
                            "last" => __("Last", 'paymendo-bank-transfer-lite'),
                            "next" => __("Next", 'paymendo-bank-transfer-lite'),
                            "previous" => __("Previous", 'paymendo-bank-transfer-lite')
                        ),
                        "aria" => array(
                            "sortAscending" => __(": activate to sort column ascending", 'paymendo-bank-transfer-lite'),
                            "sortDescending" => __(": activate to sort column descending", 'paymendo-bank-transfer-lite')
                        )
                    )
                )
            );

            wp_localize_script('paymendo_bank_transfer_admin_js', 'paymendo_bank_transfer_bank_list', pbt_get_bank_list());
        }

        public function paymendo_bank_transfer_load_site_assets()
        {
            wp_enqueue_style('paymendo_bank_transfer_site_css', pbt_get_plugin_assets('/site/css/site.css'), false, '1.0.0');
            wp_enqueue_script('paymendo_bank_transfer_site_modal_js', pbt_get_plugin_assets('/site/js/modal.js'), array());
            wp_enqueue_script('paymendo_bank_transfer_site_js', pbt_get_plugin_assets('/site/js/site.js'), array());
            wp_localize_script('paymendo_bank_transfer_site_js', 'paymendo_bank_transfer_site',
                array(
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'error_msg' => __('An error has occurred.', 'paymendo-bank-transfer-lite'),
                    'loading_gif' => pbt_get_plugin_assets('/images/loading.gif')
                ));
        }

        public function paymendo_bank_transfer()
        {
            add_menu_page(
                __('paymendo - Bank Transfer', 'paymendo-bank-transfer-lite'),
                __('paymendo - Bank Transfer', 'paymendo-bank-transfer-lite'),
                'manage_options',
                'paymendo-bank-transfer-lite',
                'paymendo_bank_transfer_set_banks_page',
                pbt_get_plugin_assets('images/paymendo-bank-transfer-icon.png'),
                '3'
            );
            $sub_menus = array(
                array(
                    'page_title' => __('Payments', 'paymendo-bank-transfer-lite'),
                    'menu_title' => __('Payments', 'paymendo-bank-transfer-lite'),
                    'capability' => 'manage_options',
                    'menu_slug' => 'paymendo-bank-transfer-payments',
                    'function' => 'paymendo_bank_transfer_set_payments_page'
                ),
                array(
                    'page_title' => __('Settings', 'paymendo-bank-transfer-lite'),
                    'menu_title' => __('Settings', 'paymendo-bank-transfer-lite'),
                    'capability' => 'manage_options',
                    'menu_slug' => 'paymendo-bank-transfer-settings',
                    'function' => 'paymendo_bank_transfer_set_settings_page'
                )
            );
            foreach ($sub_menus as $sub_menu) {
                add_submenu_page(
                    'paymendo-bank-transfer-lite',
                    $sub_menu['page_title'],
                    $sub_menu['menu_title'],
                    $sub_menu['capability'],
                    $sub_menu['menu_slug'],
                    $sub_menu['function']);
            }
        }

        public function paymendo_bank_transfer_save_bank()
        {
            if (!$_POST) {
                return;
            }
            $bank_list = pbt_get_bank_list();

            global $wpdb;

            if (!isset($_POST['paymendo_bank_transfer'])) {
                return;
            }

            foreach (filter_var_array($_POST['paymendo_bank_transfer']) as $item) {
                if ($item === null) {
                    return;
                }
                $bank = $bank_list[sanitize_text_field($item['bank_slug'])];
                if (!isset($bank)) {
                    continue;
                }
                $data = array(
                    'bank_slug' => sanitize_text_field($item['bank_slug']),
                    'iban' => sanitize_text_field($item['iban']),
                    'account_owner' => sanitize_text_field($item['account_owner']),
                    'branch_code' => sanitize_text_field($item['branch_code']),
                    'account_number' => sanitize_text_field($item['account_number']),
                    'currency' => sanitize_text_field($item['currency']),
                    'swift' => sanitize_text_field($item['swift']),
                    'note' => sanitize_text_field($item['note'])
                );
                if (isset($item['id']) && !empty($item['id'])) {
                    pbt_update_bank_account(sanitize_text_field($item['id']), $data);
                    continue;
                }
                pbt_add_bank_account($data);
            }
        }

        public function paymendo_bank_transfer_add_gateway_class($gateways)
        {
            $gateways[] = 'WC_Gateway_Paymendo_Bank_Transfer';

            return $gateways;
        }

        public function wp_ajax_notify_the_payment()
        {
            if ($_SERVER['REQUEST_METHOD'] !== "POST") {
                wp_die(__("Not allowed HTTP method!", 'paymendo-bank-transfer-lite'));
            }

            $acceptable_post_data = array(
                'paymendo_bank_transfer_completed_payment',
                'order_number'
            );

            foreach ($acceptable_post_data as $data) {
                if (!isset($_POST[$data])) {
                    echo 'error';
                    exit;
                }
            }

            $order = wc_get_order(sanitize_text_field($_POST['order_number']));
            $bank_account = pbt_get_bank_account_with_id(sanitize_text_field($_POST['paymendo_bank_transfer_completed_payment']));

            //order, bank account and notification control
            if (empty($order)
                || $order->get_payment_method() !== 'paymendo_bank_transfer'
                || empty($bank_account)
                || pbt_get_transfer_notification_with_order_id($order->get_id())) {
                echo 'error';
                exit;
            }

            // Add $order_id and $bank_account_id to Notification database
            $data = array(
                'bank_id' => $bank_account->id,
                'order_id' => $order->get_id()
            );

            if (!pbt_add_transfer_notification($data)) {
                echo 'error';
                exit;
            }

            pbt_send_sms_and_email_to_admin($order->get_id(), $bank_account);

            echo 'success';
            exit;
        }

        public function wp_ajax_delete_bank()
        {
            if ($_SERVER['REQUEST_METHOD'] !== "POST") {
                wp_die(__("Not allowed HTTP method!", 'paymendo-bank-transfer-lite'));
            }

            if (!isset($_POST['id'])) {
                return;
            }
            pbt_delete_bank_account(sanitize_text_field($_POST['id']));
            echo 'success';
            exit;
        }

        public static function get_max_notification_amount()
        {
            global $wpdb;
            $row = $wpdb->get_row('SELECT MAX(' . $wpdb->prefix . 'postmeta.meta_value) AS maxval FROM `' . $wpdb->prefix . 'paymendo_transfer_notifications` INNER JOIN ' . $wpdb->prefix . 'postmeta ON ' . $wpdb->prefix . 'postmeta.post_id = ' . $wpdb->prefix . 'paymendo_transfer_notifications.order_id AND ' . $wpdb->prefix . 'postmeta.meta_key = \'_order_total\' ');

            return floatval($row->maxval);
        }

        public function wp_ajax_payments_data()
        {
            $limit = filter_input(1, 'length', FILTER_SANITIZE_NUMBER_INT);
            $offset = filter_input(1, 'start', FILTER_SANITIZE_NUMBER_INT);

            $filters = array(
                'banks_filter',
                'status_filter',
                'initial_amount',
                'last_amount',
                'currency_filter',
                'initial_date',
                'last_date'
            );

            $where = '';

            //banks filter
            if (isset($_GET['banks_filter']) && !empty($_GET['banks_filter'])) {
                $banks_filter = sanitize_text_field($_GET['banks_filter']);
                if (strpos($banks_filter, ",") !== false) {
                    $banks_filter = str_replace(',', "','", $banks_filter);
                }
                $where .= "notifications.bank_id IN ('$banks_filter') AND ";
            }

            //status filter
            if (isset($_GET['status_filter']) && $_GET['status_filter'] !== '') {
                $status = sanitize_text_field($_GET['status_filter']);

                $where .= 'notifications.payment_status = \'' . $status . '\' AND ';
            }

            //amount range
            if ((isset($_GET['initial_amount']) && !empty($_GET['initial_amount']))
                || (isset($_GET['last_amount']) && !empty($_GET['last_amount']))) {
                $initial = empty($_GET['initial_amount']) ? 0 : sanitize_text_field($_GET['initial_amount']);
                $last = sanitize_text_field($_GET['last_amount']);
                $where .= 'total_meta.meta_value >= ' . $initial . ' AND ';
                if (!empty($last)) {
                    $where .= 'total_meta.meta_value <= ' . $last . ' AND ';
                }
            }

            if (isset($_GET['currency_filter']) && !empty($_GET['currency_filter'])) {
                $currency_filters = sanitize_text_field($_GET['currency_filter']);
                if (strpos($currency_filters, ",") !== false) {
                    $currency_filters = str_replace(",", "','", $currency_filters);
                }
                $where .= "accounts.currency IN ('$currency_filters') AND ";
            }

            if ((isset($_GET['initial_date']) && !empty($_GET['initial_date']))
                || (isset($_GET['last_date']) && !empty($_GET['last_date']))) {
                $initial_date = !empty($_GET['initial_date']) ? sanitize_text_field($_GET['initial_date']) : date('Y-m-d H:i:s');
                $last_date = !empty($_GET['last_date']) ? sanitize_text_field($_GET['last_date']) : '';

                $where .= 'notifications.created >= \'' . $initial_date . '\' AND ';
                if (!empty($last_date)) {
                    $where .= 'notifications.created <= \'' . $last_date . '\' AND ';
                }

            }
            foreach ($filters as $filter) {
                if (isset($_GET[$filter]) && $_GET[$filter] !== '') {
                    $where .= 'TRUE ';
                    break;
                }
            }

            if (isset($_GET['q']) && !empty($_GET['q'])) {
                $q = sanitize_text_field($_GET['q']);
                if ($where !== '') {
                    $where .= 'AND ';
                }
                $where .= "CONCAT( order_id, ' ', account_owner, ' ', total_meta.meta_value, ' ', notifications.created, ' ', COALESCE(notifications.updated,''), firstname_meta.meta_value, ' ', lastname_meta.meta_value)";
                $where .= "LIKE '%$q%' ";
            }

//            // For 'All' showing
//            if($limit == -1) {
//                $limit = null;
//                $offset = null;
//            }

            $orderBy = 'notifications.created';
            $orderDir = 'DESC';
            if (isset($_GET['orderBy']) && !empty($_GET['orderBy'])) {
                $orderBy = sanitize_text_field($_GET['orderBy']);
            }

            if (isset($_GET['orderDir']) && ($_GET['orderDir'] === 'asc' || $_GET['orderDir'] === 'desc')) {
                $orderDir = strtoupper(sanitize_text_field($_GET['orderDir']));
            }

            $notifications = pbt_get_transfer_notification_with_join($where, $offset, $limit, null, true, $orderBy, $orderDir);

            $notifications_sum = pbt_get_transfer_notification_with_join($where, null, null, 'SUM(total_meta.meta_value) AS total', false)->total;
            $total = pbt_get_transfer_notification_with_join($where, null, null, 'COUNT(\'id\') as total_result', false)->total_result;

            foreach ($notifications as $notification) {
                $order = wc_get_order($notification->order_id);
                $notification->order_formatted_total = $order->get_formatted_order_total();
                $notification->order_number = $order->get_order_number();
                $notification->customer_name = $order->get_formatted_billing_full_name();
                $notification->customer_id = $order->get_customer_id();
            }

            header('Content-Type: application/json');
            die(json_encode(array(
                    'draw' => filter_input(1, 'draw'),
                    'recordsTotal' => $total,
                    'recordsFiltered' => $total,
                    'data' => $notifications,
                    "sum" => $notifications_sum
                )
                , JSON_UNESCAPED_UNICODE));
        }

        public function wp_ajax_payments_complete()
        {
            if ($_SERVER['REQUEST_METHOD'] !== "POST") {
                wp_die(__("Not allowed HTTP method!", 'paymendo-bank-transfer-lite'));
            }
            if (!isset($_POST['id'])) {
                echo 'error';

                return;
            }

            if (empty(pbt_update_transfer_notification_to_payment_status(sanitize_text_field($_POST['id'])))) {
                echo 'error';

                return;
            }

            echo 'success';
            exit;
        }

        public function wp_ajax_payments_cancel()
        {
            if ($_SERVER['REQUEST_METHOD'] !== "POST") {
                wp_die(__("Not allowed HTTP method!", 'paymendo-bank-transfer-lite'));
            }
            if (!isset($_POST['id'])) {
                echo 'error';

                return;
            }
            if (empty(pbt_update_transfer_notification_to_payment_status(sanitize_text_field($_POST['id']), 0))) {
                echo 'error';

                return;
            }

            echo 'success';
            exit;
        }

        public function wp_ajax_payments_delete()
        {
            if ($_SERVER['REQUEST_METHOD'] !== "POST") {
                wp_die(__("Not allowed HTTP method!", 'paymendo-bank-transfer-lite'));
            }
            if (!isset($_POST['order_id'])) {
                echo 'error';

                return;
            }
            if (empty($notification = pbt_get_transfer_notification_with_order_id(sanitize_text_field($_POST['order_id'])))) {
                echo 'error';

                return;
            }

            wc_get_order(sanitize_text_field($_POST['order_id']))->update_status('on-hold');

            pbt_delete_transfer_notification($notification->id);

            echo 'success';
            exit;
        }

        public function wp_ajax_payments_deleted_sms()
        {
            if ($_SERVER['REQUEST_METHOD'] !== "POST") {
                wp_die(__("Not allowed HTTP method!", 'paymendo-bank-transfer-lite'));
            }
            if (!isset($_POST['order_id']) || !isset($_POST['paymendo_bank_transfer_sms_for_deleted_payment'])) {
                echo 'error';

                return;
            }
            pbt_send_sms_to_customer(wc_get_order(sanitize_text_field($_POST['order_id'])), sanitize_text_field($_POST['paymendo_bank_transfer_sms_for_deleted_payment']));

            echo 'success';
            exit;
        }

        public function email_class($actions)
        {
            $actions['Paymendo_Bank_Transfer_Completed'] = include_once('inc/emails/payment-completed.php');

            return $actions;
        }

        public function paymendo_bank_transfer_save_settings()
        {
            if (isset($_POST['sms_message_for_admin'])) {
                //admin
                $smsMessageForAdmin = sanitize_text_field($_POST['sms_message_for_admin']);
                $smsForAdminEnabled = isset($_POST['enable_sms_for_admin']) ? 'on' : 'off';
                $smsNumberForAdmin = sanitize_text_field($_POST['sms_number_for_admin']);
                $emailForAdminEnabled = isset($_POST['enable_email_for_admin']) ? 'on' : 'off';
                update_option('paymendo_bank_transfer_admin_sms_message', $smsMessageForAdmin);
                update_option('paymendo_bank_transfer_admin_sms_enabled', $smsForAdminEnabled);
                update_option('paymendo_bank_transfer_admin_email_enabled', $emailForAdminEnabled);
                update_option('paymendo_bank_transfer_admin_sms_number', $smsNumberForAdmin);
                //customer
                $smsMessageForCustomer = sanitize_text_field($_POST['sms_message_for_customer']);
                $smsForCustomerEnabled = isset($_POST['enable_sms_for_customer']) ? 'on' : 'off';
                update_option('paymendo_bank_transfer_customer_sms_message', $smsMessageForCustomer);
                update_option('paymendo_bank_transfer_customer_sms_enabled', $smsForCustomerEnabled);
            }
        }

    }
}
new PaymendoBankTransfer();