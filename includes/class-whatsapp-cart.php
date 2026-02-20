<?php
/**
 * The file that defines the core plugin class
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WhatsAppCart
 * @subpackage WhatsAppCart/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since      1.0.0
 * @package    WhatsAppCart
 * @subpackage WhatsAppCart/includes
 * @author     Muhammad Qurban
 */
class WhatsAppCart {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WhatsAppCart_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WHATSAPP_CART_VERSION' ) ) {
			$this->version = WHATSAPP_CART_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'whatsapp-cart';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - WhatsAppCart_Loader. Orchestrates the hooks of the plugin.
	 * - WhatsAppCart_Admin. Defines all hooks for the admin area.
	 * - WhatsAppCart_Public. Defines all hooks for the public side of the site.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-whatsapp-cart-loader.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-whatsapp-cart-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-whatsapp-cart-public.php';

		$this->loader = new WhatsAppCart_Loader();

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new WhatsAppCart_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
        // Bonus: Custom order status
        $this->loader->add_action( 'init', $plugin_admin, 'register_custom_order_status' );
        $this->loader->add_filter( 'wc_order_statuses', $plugin_admin, 'add_custom_order_status_to_list' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new WhatsAppCart_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        
        // Cart Page Button
        $this->loader->add_action( 'woocommerce_proceed_to_checkout', $plugin_public, 'add_cart_button', 20 );
        
        // Checkout Page Button
        $this->loader->add_action( 'woocommerce_review_order_before_submit', $plugin_public, 'add_checkout_button', 20 );

        // Product Page Button
        $this->loader->add_action( 'woocommerce_after_add_to_cart_button', $plugin_public, 'add_product_button', 20 );

        // AJAX Handler
        $this->loader->add_action( 'wp_ajax_whatsapp_cart_submit', $plugin_public, 'handle_whatsapp_order_ajax' );
        $this->loader->add_action( 'wp_ajax_nopriv_whatsapp_cart_submit', $plugin_public, 'handle_whatsapp_order_ajax' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
        // Load text domain
        load_plugin_textdomain( 'whatsapp-cart', false, dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/' );
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    WhatsAppCart_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
