<?php
/**
 * Fired during plugin activation
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WhatsAppCart
 * @subpackage WhatsAppCart/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WhatsAppCart
 * @subpackage WhatsAppCart/includes
 * @author     Muhammad Qurban
 */
class WhatsAppCart_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        // Create custom order status if needed, but usually this requires a full init hook.
        // We can schedule an event or set default options here.
        if ( ! get_option( 'whatsapp_cart_settings' ) ) {
            $defaults = array(
                'whatsapp_number' => '',
                'enable_on_cart' => 'yes',
                'enable_on_checkout' => 'yes',
                'enable_on_product' => 'no',
                'create_draft_order' => 'no',
                'message_template' => "Hello, I would like to order:\n\n{product_list}\n\nTotal: {total}\n\nMy Details:\nName: {name}\nPhone: {phone}\nAddress: {address}, {city}",
            );
            add_option( 'whatsapp_cart_settings', $defaults );
        }

        self::create_analytics_table();
	}

    public static function create_analytics_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'whatsapp_cart_analytics';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            event_type varchar(50) NOT NULL,
            product_id bigint(20) DEFAULT NULL,
            order_id bigint(20) DEFAULT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

}
