<?php
/**
 * Uninstall Script
 * Wird ausgeführt wenn das Plugin über WordPress gelöscht wird
 */

// Verhindere direkten Zugriff
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Lösche Plugin-Daten
 */
class WLC_Uninstaller {

    /**
     * Führe Deinstallation durch
     */
    public static function uninstall() {
        // Prüfe Berechtigung
        if (!current_user_can('activate_plugins')) {
            return;
        }

        // Lösche Queue-Tabelle
        self::drop_database_tables();

        // Lösche Plugin-Optionen
        self::delete_options();

        // Lösche PDF-Verzeichnis
        self::delete_upload_directory();

        // Lösche Transients
        self::delete_transients();

        // Lösche Cron-Jobs
        self::clear_scheduled_events();
    }

    /**
     * Lösche Datenbank-Tabellen
     */
    private static function drop_database_tables() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wlc_queue';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }

    /**
     * Lösche alle Plugin-Optionen
     */
    private static function delete_options() {
        $options = array(
            'wlc_api_key',
            'wlc_order_statuses',
            'wlc_invoice_title',
            'wlc_invoice_introduction',
            'wlc_payment_terms',
            'wlc_payment_due_days',
            'wlc_closing_text',
            'wlc_finalize_immediately',
            'wlc_auto_sync_contacts',
            'wlc_show_in_customer_area',
            'wlc_enable_logging',
            'wlc_email_on_error',
            'wlc_retry_attempts',
            'wlc_shipping_as_line_item',
            'wlc_api_logs',
            'wlc_error_logs'
        );

        foreach ($options as $option) {
            delete_option($option);
        }
    }

    /**
     * Lösche Upload-Verzeichnis mit PDFs
     */
    private static function delete_upload_directory() {
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/lexware-invoices';

        if (file_exists($pdf_dir)) {
            // Lösche alle Dateien im Verzeichnis
            $files = glob($pdf_dir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }

            // Lösche Verzeichnis
            rmdir($pdf_dir);
        }
    }

    /**
     * Lösche alle Transients
     */
    private static function delete_transients() {
        global $wpdb;

        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_wlc_%' 
             OR option_name LIKE '_transient_timeout_wlc_%'"
        );
    }

    /**
     * Lösche geplante Cron-Jobs
     */
    private static function clear_scheduled_events() {
        wp_clear_scheduled_hook('wlc_process_queue');
    }

    /**
     * Lösche Order-Meta-Daten (optional)
     * Auskommentiert, da Händler möglicherweise Lexware-IDs behalten möchten
     */
    private static function delete_order_meta() {
        global $wpdb;

        // Auskommentiert - nur aktivieren wenn gewünscht
        /*
        $wpdb->query(
            "DELETE FROM {$wpdb->postmeta} 
             WHERE meta_key IN (
                '_wlc_lexware_invoice_id',
                '_wlc_lexware_invoice_number',
                '_wlc_lexware_contact_id',
                '_wlc_lexware_credit_note_id',
                '_wlc_lexware_invoice_voided'
             )"
        );
        */
    }
}

// Führe Deinstallation aus
WLC_Uninstaller::uninstall();
