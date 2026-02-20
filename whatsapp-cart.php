<?php
/**
 * Plugin Name:       WhatsApp Cart
 * Description:       Adds a "Confirm Order on WhatsApp" button to WooCommerce cart and checkout pages and optionally creates a draft WooCommerce order.
 * Version:           1.0.0
 * Author:            Muhammad Qurban
 * Author URI:        https://mqurban.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       whatsapp-cart
 * Domain Path:       /languages
 *
 * @package           WhatsAppCart
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'WHATSAPP_CART_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-whatsapp-cart-activator.php
 */
function activate_whatsapp_cart() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-whatsapp-cart-activator.php';
	WhatsAppCart_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-whatsapp-cart-deactivator.php
 */
function deactivate_whatsapp_cart() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-whatsapp-cart-deactivator.php';
	WhatsAppCart_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_whatsapp_cart' );
register_deactivation_hook( __FILE__, 'deactivate_whatsapp_cart' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-whatsapp-cart.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_whatsapp_cart() {

    // Verify WooCommerce is active before running
    if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
        add_action( 'admin_notices', 'whatsapp_cart_woocommerce_missing_notice' );
        return;
    }

	$plugin = new WhatsAppCart();
	$plugin->run();

}

/**
 * Display notice if WooCommerce is not installed/active.
 */
function whatsapp_cart_woocommerce_missing_notice() {
    ?>
    <div class="error notice">
        <p><?php _e( 'WhatsApp Cart requires WooCommerce to be installed and active.', 'whatsapp-cart' ); ?></p>
    </div>
    <?php
}

run_whatsapp_cart();
