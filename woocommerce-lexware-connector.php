<?php
/**
 * Plugin Name: Invoice Connector for Lexware
 * Plugin URI: https://github.com/patsch9/woocommerce-lexware-connector
 * Description: Automatische Rechnungserstellung in Lexware Office aus WooCommerce-Bestellungen mit vollständiger Synchronisation und Kundenbereichs-Integration
 * Version: 1.0.0
 * Author: Patrick Schmidt
 * Author URI: https://github.com/patsch9
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: woo-lexware-connector
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 * WC tested up to: 9.0
 * Requires Plugins: woocommerce
 */

// Verhindere direkten Zugriff
if (!defined('ABSPATH')) {
    exit;
}

// Plugin-Konstanten definieren
define('WLC_VERSION', '1.0.0');
define('WLC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WLC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WLC_PLUGIN_BASENAME', plugin_basename(__FILE__));

/*
 * Hinweis: Dies ist ein inoffizielles Plugin und steht in keiner Verbindung zur Haufe-Lexware GmbH & Co. KG. Lexware® ist eine eingetragene Marke der Haufe-Lexware GmbH & Co. KG.
 */

/**
 * Hauptklasse des Plugins
 */
class WooCommerce_Lexware_Connector {
    /* ... bestehender Plugin-Code ... */
}
// ... Restliche Plugin-Datei unverändert belassen ...
