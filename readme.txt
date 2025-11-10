=== WooCommerce Lexware Connector ===
Contributors: deinname
Tags: woocommerce, lexware, rechnung, invoice, buchhaltung
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatische Rechnungserstellung in Lexware Office aus WooCommerce-Bestellungen.

== Description ==

WooCommerce Lexware Connector verbindet deinen WooCommerce-Shop mit Lexware Office und erstellt automatisch Rechnungen, wenn Bestellungen eingehen.

**Features:**

* Automatische Rechnungserstellung bei konfigurierbaren WooCommerce-Status
* Kundendaten-Synchronisation zu Lexware
* Automatische Rechnungsstornierung bei stornierten Bestellungen
* Rechnungskorrektur bei Bestelländerungen
* PDF-Anzeige im WooCommerce-Kundenbereich
* Queue-System mit automatischen Wiederholungsversuchen
* Umfangreiches Logging und Fehlerbehandlung
* Manuelle Rechnungserstellung aus dem Admin-Bereich

*Shortcodes*
| Shortcode          | Beschreibung     | Beispiel         |
| ------------------ | ---------------- | ---------------- |
| [order_number]     | Bestellnummer    | 855              |
| [order_id]         | Bestellungs-ID   | 855              |
| [order_date]       | Datum formatiert | 06.11.2025       |
| [order_date_raw]   | Datum YYYY-MM-DD | 2025-11-06       |
| [customer_name]    | Kundenname       | Max Mustermann   |
| [customer_email]   | E-Mail           | kunde@email.de   |
| [customer_company] | Firma            | Musterfirma GmbH |
| [total]            | Gesamtbetrag     | 123,45 �         |
| [payment_method]   | Zahlungsart      | PayPal           |

== Installation ==

1. Lade das Plugin in den `/wp-content/plugins/` Ordner hoch
2. Aktiviere das Plugin im 'Plugins' Menü
3. Gehe zu WooCommerce → Lexware Connector
4. Trage deinen Lexware API Key ein
5. Konfiguriere die Einstellungen nach deinen Wünschen

== Changelog ==

= 1.0.0 =
* Erste Veröffentlichung
