<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WhatsAppCart
 * @subpackage WhatsAppCart/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * @package    WhatsAppCart
 * @subpackage WhatsAppCart/public
 * @author     Muhammad Qurban
 */
class WhatsAppCart_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $plugin_name       The name of this plugin.
	 * @param    string    $version           The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
        if ( is_cart() || is_checkout() || is_product() ) {
            wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/whatsapp-cart-public.js', array( 'jquery' ), $this->version, true );
            wp_localize_script( $this->plugin_name, 'whatsapp_cart_obj', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'whatsapp_cart_nonce' ),
                'is_cart'  => is_cart(),
                'is_checkout' => is_checkout(),
                'is_product' => is_product()
            ) );
            
            // Basic CSS for the button
            wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/whatsapp-cart-public.css', array(), $this->version, 'all' );
        }
    }

    public function add_cart_button() {
        $options = get_option( 'whatsapp_cart_settings' );
        if ( isset( $options['enable_on_cart'] ) && $options['enable_on_cart'] === 'yes' ) {
            echo '<a href="#" id="whatsapp-cart-button" class="button alt whatsapp-cart-btn" style="background-color: #25D366; color: white; margin-top: 10px; margin-bottom: 10px; width: 100%; text-align: center;">' . __( 'Confirm on WhatsApp', 'whatsapp-cart' ) . '</a>';
        }
    }

    public function add_checkout_button() {
        $options = get_option( 'whatsapp_cart_settings' );
        if ( isset( $options['enable_on_checkout'] ) && $options['enable_on_checkout'] === 'yes' ) {
            echo '<a href="#" id="whatsapp-checkout-button" class="button alt whatsapp-cart-btn" style="background-color: #25D366; color: white; margin-top: 10px; width: 100%; text-align: center; display: block;">' . __( 'Confirm Order on WhatsApp', 'whatsapp-cart' ) . '</a>';
        }
    }

    public function add_product_button() {
        $options = get_option( 'whatsapp_cart_settings' );
        if ( isset( $options['enable_on_product'] ) && $options['enable_on_product'] === 'yes' ) {
            global $product;
            echo '<a href="#" id="whatsapp-product-button" class="button alt whatsapp-cart-btn" data-product_id="' . $product->get_id() . '" style="background-color: #25D366; color: white; margin-top: 10px; width: 100%; text-align: center; display: block;">' . __( 'Order via WhatsApp', 'whatsapp-cart' ) . '</a>';
        }
    }

    public function handle_whatsapp_order_ajax() {
        check_ajax_referer( 'whatsapp_cart_nonce', 'security' );

        $options = get_option( 'whatsapp_cart_settings' );
        $whatsapp_number = isset( $options['whatsapp_number'] ) ? $options['whatsapp_number'] : '';
        $create_order = isset( $options['create_draft_order'] ) && $options['create_draft_order'] === 'yes';
        $template = isset( $options['message_template'] ) ? $options['message_template'] : '';

        // Track Events
        $track_product_id = null;
        if ( isset( $_POST['is_product'] ) && $_POST['is_product'] == 'true' ) {
            $track_product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : null;
            $this->record_event( 'click_product', $track_product_id );
        } elseif ( isset( $_POST['is_cart'] ) && $_POST['is_cart'] == 'true' ) {
            $this->record_event( 'click_cart' );
        } elseif ( isset( $_POST['is_checkout'] ) && $_POST['is_checkout'] == 'true' ) {
            $this->record_event( 'click_checkout' );
        }

        // Product Page Handler
        if ( isset( $_POST['is_product'] ) && $_POST['is_product'] == 'true' ) {
            $product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
            $quantity = isset( $_POST['quantity'] ) ? intval( $_POST['quantity'] ) : 1;
            $variation_id = isset( $_POST['variation_id'] ) ? intval( $_POST['variation_id'] ) : 0;
            
            if ( $product_id ) {
                WC()->cart->empty_cart();
                WC()->cart->add_to_cart( $product_id, $quantity, $variation_id );
            } else {
                 wp_send_json_error( array( 'message' => 'Invalid Product' ) );
            }
        } elseif ( isset( $_POST['is_product'] ) && $_POST['is_product'] == 'true' && !isset( $_POST['product_id'] ) ) {
            // Handle case where product ID is missing
             wp_send_json_error( array( 'message' => 'Invalid Product' ) );
        }

        // Collect Data
        $cart = WC()->cart;
        if ( $cart->is_empty() ) {
            wp_send_json_error( array( 'message' => 'Cart is empty' ) );
        }

        $order_id = 'N/A';
        $customer_data = array(
            'first_name' => '',
            'last_name'  => '',
            'phone'      => '',
            'address_1'  => '',
            'city'       => '',
            'email'      => ''
        );

        // Get data from POST (if checkout)
        if ( isset( $_POST['form_data'] ) ) {
            // Parse serialized form data
            parse_str( stripslashes( $_POST['form_data'] ), $checkout_data );
            
            $customer_data['first_name'] = isset($checkout_data['billing_first_name']) ? sanitize_text_field($checkout_data['billing_first_name']) : '';
            $customer_data['last_name']  = isset($checkout_data['billing_last_name']) ? sanitize_text_field($checkout_data['billing_last_name']) : '';
            $customer_data['phone']      = isset($checkout_data['billing_phone']) ? sanitize_text_field($checkout_data['billing_phone']) : '';
            $customer_data['address_1']  = isset($checkout_data['billing_address_1']) ? sanitize_text_field($checkout_data['billing_address_1']) : '';
            $customer_data['city']       = isset($checkout_data['billing_city']) ? sanitize_text_field($checkout_data['billing_city']) : '';
            $customer_data['email']      = isset($checkout_data['billing_email']) ? sanitize_email($checkout_data['billing_email']) : '';
        } else {
            // Try to get from current user if logged in
            $user_id = get_current_user_id();
            if ( $user_id ) {
                $customer_data['first_name'] = get_user_meta( $user_id, 'billing_first_name', true );
                $customer_data['last_name']  = get_user_meta( $user_id, 'billing_last_name', true );
                $customer_data['phone']      = get_user_meta( $user_id, 'billing_phone', true );
                $customer_data['address_1']  = get_user_meta( $user_id, 'billing_address_1', true );
                $customer_data['city']       = get_user_meta( $user_id, 'billing_city', true );
                $customer_data['email']      = get_userdata( $user_id )->user_email;
            }
        }

        if ( $create_order ) {
            $order = wc_create_order();
            foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
                $order->add_product( $cart_item['data'], $cart_item['quantity'] );
            }
            
            $address = array(
                'first_name' => $customer_data['first_name'],
                'last_name'  => $customer_data['last_name'],
                'email'      => $customer_data['email'],
                'phone'      => $customer_data['phone'],
                'address_1'  => $customer_data['address_1'],
                'city'       => $customer_data['city'],
            );

            $order->set_address( $address, 'billing' );
            $order->set_address( $address, 'shipping' );
            $order->calculate_totals();
            $order->update_status( 'wc-whatsapp-pending', 'Order created via WhatsApp Cart', true );
            $order_id = $order->get_id();
            
            // Track Order Creation
            $this->record_event( 'order_created', null, $order_id );
            
            // Empty cart if order created? Usually yes, but let's keep it optional or just clear it.
            // WC()->cart->empty_cart(); // Maybe don't empty it immediately so they can go back? 
            // Standard flow is empty cart.
            WC()->cart->empty_cart();
        }

        // Prepare Message
        $product_list = "";
        foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
            $product = $cart_item['data'];
            $price = wc_price( $product->get_price() * $cart_item['quantity'] );
            $price = html_entity_decode( strip_tags( $price ) );
            $product_list .= $product->get_name() . " x " . $cart_item['quantity'] . " - " . $price . "\n";
        }

        $total = WC()->cart->get_total(); // If order created, use $order->get_total()
        if ( $create_order && isset($order) ) {
            $total = $order->get_formatted_order_total();
        }
        $total = html_entity_decode( strip_tags( $total ) );

        $replacements = array(
            '{order_id}'     => $order_id,
            '{product_list}' => $product_list,
            '{total}'        => $total,
            '{name}'         => $customer_data['first_name'] . ' ' . $customer_data['last_name'],
            '{phone}'        => $customer_data['phone'],
            '{city}'         => $customer_data['city'],
            '{address}'      => $customer_data['address_1']
        );

        $message = str_replace( array_keys( $replacements ), array_values( $replacements ), $template );
        $encoded_message = urlencode( $message );
        $whatsapp_url = "https://wa.me/$whatsapp_number?text=$encoded_message";

        wp_send_json_success( array( 'redirect_url' => $whatsapp_url ) );
    }

    private function record_event( $event_type, $product_id = null, $order_id = null ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'whatsapp_cart_analytics';
        
        // Ensure table exists before inserting
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
            return;
        }

        $wpdb->insert(
            $table_name,
            array(
                'time' => current_time( 'mysql' ),
                'event_type' => sanitize_text_field( $event_type ),
                'product_id' => $product_id ? intval( $product_id ) : null,
                'order_id' => $order_id ? intval( $order_id ) : null
            ),
            array( '%s', '%s', '%d', '%d' )
        );
    }
}
