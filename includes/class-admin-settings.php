private function render_payment_method_settings() {
    if (!function_exists('WC')) {
        return;
    }
    $payment_gateways = WC()->payment_gateways->payment_gateways();
    $active_gateways = array_filter($payment_gateways, function($gateway) {
        return $gateway->enabled === 'yes';
    });
    if (empty($active_gateways)) {
        return;
    }
    ?>
    <hr style="margin: 30px 0;">
    <h3>💳 <?php _e('Zahlungsmethoden-spezifische Einstellungen', 'woo-lexware-connector'); ?></h3>
    <p class="description">
        <?php _e('Konfiguriere individuelle Zahlungsbedingungen für jede Zahlungsmethode. Leer = Standard-Einstellungen verwenden.', 'woo-lexware-connector'); ?>
    </p>
    <table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
        <thead>
            <tr>
                <th style="width: 200px;"><?php _e('Zahlungsmethode', 'woo-lexware-connector'); ?></th>
                <th><?php _e('Zahlungsbedingungen', 'woo-lexware-connector'); ?></th>
                <th style="width: 120px;"><?php _e('Zahlungsziel (Tage)', 'woo-lexware-connector'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($active_gateways as $gateway): ?>
                <?php
                $gateway_id = $gateway->id;
                $payment_terms = get_option('wlc_payment_terms_' . $gateway_id, '');
                $payment_days = get_option('wlc_payment_due_days_' . $gateway_id, '');
                $default_terms = get_option('wlc_payment_terms', '');
                $default_days = get_option('wlc_payment_due_days', '14');
                ?>
                <tr>
                    <td>
                        <strong><?php echo esc_html($gateway->get_title()); ?></strong><br>
                        <code style="font-size: 11px; color: #666;"><?php echo esc_html($gateway_id); ?></code>
                    </td>
                    <td>
                        <input type="text"
                               name="wlc_payment_terms_<?php echo esc_attr($gateway_id); ?>"
                               value="<?php echo esc_attr($payment_terms); ?>"
                               class="widefat"
                               placeholder="<?php echo esc_attr($default_terms ?: 'Standard verwenden'); ?>">
                    </td>
                    <td>
                        <input type="number"
                               name="wlc_payment_due_days_<?php echo esc_attr($gateway_id); ?>"
                               value="<?php echo esc_attr($payment_days); ?>"
                               class="small-text"
                               min="0" max="365"
                               placeholder="<?php echo esc_attr($default_days); ?>">
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p class="description" style="margin-top: 10px;">
        💡 <strong>Beispiele:</strong> PayPal: "Bereits bezahlt per PayPal" + 0 Tage | Rechnung: "Zahlbar innerhalb von 14 Tagen" + 14 Tage
    </p>
    <?php
}