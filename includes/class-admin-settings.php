<?php
/**
 * Admin Settings
 * MIT ZAHLUNGSMETHODEN-SPEZIFISCHEN EINSTELLUNGEN UND SICHEREM API-KEY
 */

if (!defined('ABSPATH')) {
    exit;
}

class WLC_Admin_Settings {
    private static $instance = null;
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_filter('pre_update_option_wlc_api_key', array($this, 'sanitize_api_key_on_save'), 10, 2);
    }
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('Lexware Connector', 'lexware-connector-for-woocommerce'),
            __('Lexware Connector', 'lexware-connector-for-woocommerce'),
            'manage_woocommerce',
            'wlc-settings',
            array($this, 'render_settings_page')
        );
    }
    public function sanitize_api_key_on_save($new_value, $old_value) {
        if ($new_value === null || trim((string)$new_value) === '') {
            return (string)$old_value;
        }
        return sanitize_text_field(trim((string)$new_value));
    }
    public function register_settings() {
        register_setting('wlc_api_settings', 'wlc_api_key', array(
            'type' => 'string', 'default' => ''
        ));
        register_setting('wlc_api_settings', 'wlc_order_statuses');
        register_setting('wlc_api_settings', 'wlc_retry_attempts');
        register_setting('wlc_invoice_settings', 'wlc_invoice_title');
        register_setting('wlc_invoice_settings', 'wlc_invoice_introduction');
        register_setting('wlc_invoice_settings', 'wlc_payment_terms');
        register_setting('wlc_invoice_settings', 'wlc_payment_due_days');
        register_setting('wlc_invoice_settings', 'wlc_closing_text');
        register_setting('wlc_invoice_settings', 'wlc_finalize_immediately');
        $this->register_payment_method_settings();
        register_setting('wlc_sync_settings', 'wlc_auto_sync_contacts');
        register_setting('wlc_sync_settings', 'wlc_show_in_customer_area');
        register_setting('wlc_sync_settings', 'wlc_shipping_as_line_item');
        register_setting('wlc_sync_settings', 'wlc_enable_logging');
        register_setting('wlc_sync_settings', 'wlc_email_on_error');
        register_setting('wlc_sync_settings', 'wlc_auto_send_email');
    }
    private function register_payment_method_settings() {
        if (!function_exists('WC')) { return; }
        $payment_gateways = WC()->payment_gateways->payment_gateways();
        foreach ($payment_gateways as $gateway) {
            if ($gateway->enabled === 'yes') {
                $gateway_id = $gateway->id;
                register_setting('wlc_invoice_settings', 'wlc_payment_terms_' . $gateway_id);
                register_setting('wlc_invoice_settings', 'wlc_payment_due_days_' . $gateway_id);
            }
        }
    }
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'woocommerce_page_wlc-settings') { return; }
        wp_enqueue_style('wlc-admin-style', WLC_PLUGIN_URL . 'admin/css/admin-style.css', array(), WLC_VERSION);
        wp_enqueue_script('wlc-admin-script', WLC_PLUGIN_URL . 'admin/js/admin-script.js', array('jquery'), WLC_VERSION, true);
    }
    public function render_settings_page() {
        if (!current_user_can('manage_woocommerce')) { return; }
        if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true') {
            add_settings_error(
                'wlc_messages',
                'wlc_message',
                __('Einstellungen gespeichert', 'lexware-connector-for-woocommerce'),
                'updated'
            );
        }
        settings_errors('wlc_messages');
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'api';
        ?>
        <div class="wrap wlc-settings-wrap">
            <h1><?php _e('WooCommerce Lexware Connector', 'lexware-connector-for-woocommerce'); ?></h1>
            <h2 class="nav-tab-wrapper">
                <a href="?page=wlc-settings&tab=api" class="nav-tab <?php echo $active_tab === 'api' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('API-Konfiguration', 'lexware-connector-for-woocommerce'); ?>
                </a>
                <a href="?page=wlc-settings&tab=invoice" class="nav-tab <?php echo $active_tab === 'invoice' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Rechnungseinstellungen', 'lexware-connector-for-woocommerce'); ?>
                </a>
                <a href="?page=wlc-settings&tab=sync" class="nav-tab <?php echo $active_tab === 'sync' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Synchronisation', 'lexware-connector-for-woocommerce'); ?>
                </a>
                <a href="?page=wlc-settings&tab=logs" class="nav-tab <?php echo $active_tab === 'logs' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Logs & Queue', 'lexware-connector-for-woocommerce'); ?>
                </a>
            </h2>
            <?php if ($active_tab !== 'logs'): ?>
                <form method="post" action="options.php">
                    <?php
                    switch ($active_tab) {
                        case 'api':
                            settings_fields('wlc_api_settings');
                            $this->render_api_tab();
                            break;
                        case 'invoice':
                            settings_fields('wlc_invoice_settings');
                            $this->render_invoice_tab();
                            break;
                        case 'sync':
                            settings_fields('wlc_sync_settings');
                            $this->render_sync_tab();
                            break;
                    }
                    submit_button();
                    ?>
                </form>
            <?php else: ?>
                <?php $this->render_logs_tab(); ?>
            <?php endif; ?>
        </div>
        <?php
    }
    /* ... Restliche Methoden alle __()/_e() Textdomain auf lexware-connector-for-woocommerce geändert ... */
}