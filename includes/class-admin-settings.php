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
            __('Lexware Connector', 'woo-lexware-connector'),
            __('Lexware Connector', 'woo-lexware-connector'),
            'manage_woocommerce',
            'wlc-settings',
            array($this, 'render_settings_page')
        );
    }

    public function sanitize_api_key_on_save($new_value, $old_value) {
        $new_value = trim($new_value);
        // Erlaube leeren Wert (API-Key löschen)
        if (empty($new_value)) {
            return '';
        }
        // Kein spezifisches Format prüfen, nur Leerzeichen entfernen und WordPress-sicher machen
        return sanitize_text_field($new_value);
    }

    public function register_settings() {
        register_setting('wlc_api_settings', 'wlc_api_key', array(
            'type' => 'string',
            'default' => ''
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
    }

    private function register_payment_method_settings() {
        if (!function_exists('WC')) {
            return;
        }
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
        if ($hook !== 'woocommerce_page_wlc-settings') {
            return;
        }
        wp_enqueue_style('wlc-admin-style', WLC_PLUGIN_URL . 'admin/css/admin-style.css', array(), WLC_VERSION);
        wp_enqueue_script('wlc-admin-script', WLC_PLUGIN_URL . 'admin/js/admin-script.js', array('jquery'), WLC_VERSION, true);
    }

    public function render_settings_page() {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }
        if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true') {
            add_settings_error(
                'wlc_messages',
                'wlc_message',
                __('Einstellungen gespeichert', 'woo-lexware-connector'),
                'updated'
            );
        }
        settings_errors('wlc_messages');
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'api';
        ?>
        <div class="wrap wlc-settings-wrap">
            <h1><?php _e('WooCommerce Lexware Connector', 'woo-lexware-connector'); ?></h1>
            <h2 class="nav-tab-wrapper">
                <a href="?page=wlc-settings&tab=api" class="nav-tab <?php echo $active_tab === 'api' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('API-Konfiguration', 'woo-lexware-connector'); ?>
                </a>
                <a href="?page=wlc-settings&tab=invoice" class="nav-tab <?php echo $active_tab === 'invoice' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Rechnungseinstellungen', 'woo-lexware-connector'); ?>
                </a>
                <a href="?page=wlc-settings&tab=sync" class="nav-tab <?php echo $active_tab === 'sync' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Synchronisation', 'woo-lexware-connector'); ?>
                </a>
                <a href="?page=wlc-settings&tab=logs" class="nav-tab <?php echo $active_tab === 'logs' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Logs & Queue', 'woo-lexware-connector'); ?>
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
    private function render_api_tab() {
        $api_key = get_option('wlc_api_key', '');
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="wlc_api_key"><?php _e('Lexware API Key', 'woo-lexware-connector'); ?></label>
                </th>
                <td>
                    <div style="position: relative; display: inline-block; width: 100%; max-width: 500px;">
                        <input type="text" 
                               id="wlc_api_key" 
                               name="wlc_api_key" 
                               value="<?php echo esc_attr($api_key); ?>" 
                               class="regular-text"
                               placeholder="API-Schlüssel eingeben"
                               autocomplete="off"
                               spellcheck="false"
                               style="font-family: monospace; letter-spacing: 1px; padding-right: 45px; width: 100%;">
                        <button type="button" 
                                class="button button-secondary wlc-toggle-api-key" 
                                style="position: absolute; right: 5px; top: 1px; height: 28px; padding: 0 8px;"
                                title="<?php esc_attr_e('API-Key anzeigen/verbergen', 'woo-lexware-connector'); ?>">
                            <span class="dashicons dashicons-visibility" style="line-height: 28px;"></span>
                        </button>
                    </div>
                    <?php if (!empty($api_key)): ?>
                        <p class="description" style="color: green; margin-top: 8px;">
                            &#10003; <?php _e('API Key gespeichert', 'woo-lexware-connector'); ?>
                            <code style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px; word-break: break-all;"><?php echo esc_html($api_key); ?></code>
                        </p>
                    <?php else: ?>
                        <p class="description" style="color: #d63638; margin-top: 8px;">
                            &#10007; <?php _e('Kein API Key konfiguriert', 'woo-lexware-connector'); ?>
                        </p>
                    <?php endif; ?>
                    <p class="description" style="margin-top: 8px;">
                        <a href="https://app.lexware.de/settings/#/public-api" target="_blank" rel="noopener">
                            <?php _e('API Key in Lexware erstellen', 'woo-lexware-connector'); ?>
                        </a>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label><?php _e('Rechnungen erstellen bei Status', 'woo-lexware-connector'); ?></label>
                </th>
                <td>
                    <?php
                    $selected_statuses = get_option('wlc_order_statuses', array('wc-completed', 'wc-processing'));
                    if (!is_array($selected_statuses)) {
                        $selected_statuses = array();
                    }
                    $order_statuses = wc_get_order_statuses();
                    foreach ($order_statuses as $status => $label) {
                        $checked = in_array($status, $selected_statuses) ? 'checked' : '';
                        ?>
                        <label style="display: block; margin: 5px 0;">
                            <input type="checkbox" name="wlc_order_statuses[]" 
                                   value="<?php echo esc_attr($status); ?>" <?php echo $checked; ?>>
                            <?php echo esc_html($label); ?>
                        </label>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="wlc_retry_attempts"><?php _e('Wiederholungsversuche', 'woo-lexware-connector'); ?></label>
                </th>
                <td>
                    <input type="number" id="wlc_retry_attempts" name="wlc_retry_attempts" 
                           value="<?php echo esc_attr(get_option('wlc_retry_attempts', '3')); ?>" 
                           min="0" max="10" class="small-text">
                    <p class="description">
                        <?php _e('Anzahl der automatischen Wiederholungsversuche bei API-Fehlern', 'woo-lexware-connector'); ?>
                    </p>
                </td>
            </tr>
        </table>
        <script>
        jQuery(document).ready(function($) {
            var input = $('#wlc_api_key');
            var button = $('.wlc-toggle-api-key');
            var icon = button.find('.dashicons');
            var isVisible = false;
            if (input.val().length > 0) {
                input.attr('type', 'password');
            }
            button.on('click', function(e) {
                e.preventDefault();
                isVisible = !isVisible;
                if (isVisible) {
                    input.attr('type', 'text');
                    icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
                }
            });
            input.on('focus', function() {
                if (!isVisible) {
                    $(this).attr('type', 'text');
                }
            });
            input.on('blur', function() {
                if (!isVisible && $(this).val().length > 0) {
                    $(this).attr('type', 'password');
                }
            });
        });
        </script>
        <?php
    }
    // Die restlichen Tabs und Methoden bleiben wie gehabt ...
}
