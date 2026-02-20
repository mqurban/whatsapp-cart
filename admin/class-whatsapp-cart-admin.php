<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WhatsAppCart
 * @subpackage WhatsAppCart/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    WhatsAppCart
 * @subpackage WhatsAppCart/admin
 * @author     Muhammad Qurban
 */
class WhatsAppCart_Admin {

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

        // Check if DB update needed
        if ( is_admin() ) {
            $this->check_db_update();
        }
	}

    public function check_db_update() {
        if ( get_option( 'whatsapp_cart_db_version' ) != '1.0.0' ) {
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-whatsapp-cart-activator.php';
            WhatsAppCart_Activator::create_analytics_table();
            update_option( 'whatsapp_cart_db_version', '1.0.0' );
        }
    }

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
        $hook = add_menu_page(
            'WhatsApp Cart Settings',
            'WhatsApp Cart',
            'manage_options',
            'whatsapp-cart',
            array( $this, 'options_page_html' ),
            'dashicons-cart',
            58 // Position after WooCommerce Products
        );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

    public function enqueue_styles( $hook ) {
        if ( 'toplevel_page_whatsapp-cart' !== $hook ) {
            return;
        }
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/whatsapp-cart-admin.css', array(), $this->version, 'all' );
    }

    /**
     * Register settings.
     */
    public function register_settings() {
        register_setting( 'whatsapp_cart_options', 'whatsapp_cart_settings' );

        add_settings_section(
            'whatsapp_cart_general_section',
            'General Settings',
            null,
            'whatsapp-cart'
        );

        add_settings_field(
            'whatsapp_number',
            'WhatsApp Number',
            array( $this, 'render_field_whatsapp_number' ),
            'whatsapp-cart',
            'whatsapp_cart_general_section'
        );

        add_settings_field(
            'enable_on_product',
            'Enable on Product Page',
            array( $this, 'render_field_enable_on_product' ),
            'whatsapp-cart',
            'whatsapp_cart_general_section'
        );

        add_settings_field(
            'enable_on_cart',
            'Enable on Cart Page',
            array( $this, 'render_field_enable_on_cart' ),
            'whatsapp-cart',
            'whatsapp_cart_general_section'
        );

        add_settings_field(
            'enable_on_checkout',
            'Enable on Checkout Page',
            array( $this, 'render_field_enable_on_checkout' ),
            'whatsapp-cart',
            'whatsapp_cart_general_section'
        );

        add_settings_field(
            'create_draft_order',
            'Create Draft Order',
            array( $this, 'render_field_create_draft_order' ),
            'whatsapp-cart',
            'whatsapp_cart_general_section'
        );

        add_settings_field(
            'message_template',
            'Message Template',
            array( $this, 'render_field_message_template' ),
            'whatsapp-cart',
            'whatsapp_cart_general_section'
        );
    }

    public function render_field_whatsapp_number() {
        $options = get_option( 'whatsapp_cart_settings' );
        $val = isset( $options['whatsapp_number'] ) ? esc_attr( $options['whatsapp_number'] ) : '';
        echo "<input type='text' name='whatsapp_cart_settings[whatsapp_number]' value='$val' placeholder='e.g. 15551234567' />";
        echo "<p class='description'>Enter number in international format without symbols.</p>";
    }

    public function render_field_enable_on_product() {
        $options = get_option( 'whatsapp_cart_settings' );
        $val = isset( $options['enable_on_product'] ) ? $options['enable_on_product'] : 'no';
        $checked = checked( $val, 'yes', false );
        echo '<label class="whatsapp-cart-toggle">';
        echo "<input type='checkbox' name='whatsapp_cart_settings[enable_on_product]' value='yes' $checked />";
        echo '<span class="slider"></span>';
        echo '</label>';
    }

    public function render_field_enable_on_cart() {
        $options = get_option( 'whatsapp_cart_settings' );
        $val = isset( $options['enable_on_cart'] ) ? $options['enable_on_cart'] : 'yes';
        $checked = checked( $val, 'yes', false );
        echo '<label class="whatsapp-cart-toggle">';
        echo "<input type='checkbox' name='whatsapp_cart_settings[enable_on_cart]' value='yes' $checked />";
        echo '<span class="slider"></span>';
        echo '</label>';
    }

    public function render_field_enable_on_checkout() {
        $options = get_option( 'whatsapp_cart_settings' );
        $val = isset( $options['enable_on_checkout'] ) ? $options['enable_on_checkout'] : 'yes';
        $checked = checked( $val, 'yes', false );
        echo '<label class="whatsapp-cart-toggle">';
        echo "<input type='checkbox' name='whatsapp_cart_settings[enable_on_checkout]' value='yes' $checked />";
        echo '<span class="slider"></span>';
        echo '</label>';
    }

    public function render_field_create_draft_order() {
        $options = get_option( 'whatsapp_cart_settings' );
        $val = isset( $options['create_draft_order'] ) ? $options['create_draft_order'] : 'no';
        $checked = checked( $val, 'yes', false );
        echo '<label class="whatsapp-cart-toggle">';
        echo "<input type='checkbox' name='whatsapp_cart_settings[create_draft_order]' value='yes' $checked />";
        echo '<span class="slider"></span>';
        echo '</label>';
        echo "<p class='description' style='margin-top: 5px;'>If enabled, a pending order will be created before redirecting to WhatsApp.</p>";
    }

    public function render_field_message_template() {
        $options = get_option( 'whatsapp_cart_settings' );
        $default_msg = "Hello, I would like to order:\n\n{product_list}\n\nTotal: {total}\n\nMy Details:\nName: {name}\nPhone: {phone}\nAddress: {address}, {city}";
        $val = isset( $options['message_template'] ) ? esc_textarea( $options['message_template'] ) : $default_msg;
        echo "<textarea name='whatsapp_cart_settings[message_template]' rows='10' cols='50'>$val</textarea>";
        echo "<p class='description'>Available placeholders: {order_id}, {product_list}, {total}, {name}, {phone}, {city}, {address}</p>";
    }

    public function options_page_html() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
        ?>
        <div class="wrap whatsapp-cart-admin-wrap">
            <div class="whatsapp-cart-header">
                <h1><span class="dashicons dashicons-whatsapp"></span> WhatsApp Cart Settings</h1>
            </div>

            <h2 class="nav-tab-wrapper">
                <a href="?page=whatsapp-cart&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">General Settings</a>
                <a href="?page=whatsapp-cart&tab=analytics" class="nav-tab <?php echo $active_tab == 'analytics' ? 'nav-tab-active' : ''; ?>">Analytics</a>
            </h2>

            <?php if ( $active_tab == 'general' ) : ?>
                <form action="options.php" method="post">
                    <?php
                    settings_fields( 'whatsapp_cart_options' );
                    do_settings_sections( 'whatsapp-cart' );
                    ?>
                    <div class="whatsapp-cart-submit">
                        <?php submit_button( 'Save Changes' ); ?>
                    </div>
                </form>
            <?php else : ?>
                <?php $this->render_analytics_tab(); ?>
            <?php endif; ?>
        </div>
        <?php
    }

    public function render_analytics_tab() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'whatsapp_cart_analytics';
        
        // Get stats
        $total_clicks = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE event_type LIKE 'click_%'" );
        $total_orders = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE event_type = 'order_created'" );
        
        // Detailed breakdown
        $clicks_product = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE event_type = 'click_product'" );
        $clicks_cart = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE event_type = 'click_cart'" );
        $clicks_checkout = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE event_type = 'click_checkout'" );

        // Recent events
        $recent_events = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY time DESC LIMIT 20" );
        
        ?>
        <div class="whatsapp-cart-analytics">
            <div class="card-grid">
                <div class="card">
                    <h3>Total Clicks</h3>
                    <p class="number"><?php echo esc_html($total_clicks ? $total_clicks : 0); ?></p>
                </div>
                <div class="card">
                    <h3>Total Orders (WhatsApp)</h3>
                    <p class="number"><?php echo esc_html($total_orders ? $total_orders : 0); ?></p>
                </div>
                 <div class="card">
                    <h3>Product Page Clicks</h3>
                    <p class="number"><?php echo esc_html($clicks_product ? $clicks_product : 0); ?></p>
                </div>
                <div class="card">
                    <h3>Cart/Checkout Clicks</h3>
                    <p class="number"><?php echo esc_html(($clicks_cart ? $clicks_cart : 0) + ($clicks_checkout ? $clicks_checkout : 0)); ?></p>
                </div>
            </div>
            
            <h3 style="margin-top: 30px;">Recent Activity</h3>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Event</th>
                        <th>Product ID</th>
                        <th>Order ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( $recent_events ) : ?>
                        <?php foreach ( $recent_events as $event ) : ?>
                            <tr>
                                <td><?php echo esc_html($event->time); ?></td>
                                <td><?php echo esc_html(str_replace('_', ' ', ucfirst($event->event_type))); ?></td>
                                <td><?php echo $event->product_id ? '<a href="post.php?post='.$event->product_id.'&action=edit">#'.esc_html($event->product_id).'</a>' : '-'; ?></td>
                                <td><?php echo $event->order_id ? '<a href="post.php?post='.$event->order_id.'&action=edit">#'.esc_html($event->order_id).'</a>' : '-'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr><td colspan="4">No activity yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Register Custom Order Status
     */
    public function register_custom_order_status() {
        register_post_status( 'wc-whatsapp-pending', array(
            'label'                     => 'WhatsApp Pending',
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'WhatsApp Pending <span class="count">(%s)</span>', 'WhatsApp Pending <span class="count">(%s)</span>' )
        ) );
    }

    public function add_custom_order_status_to_list( $order_statuses ) {
        $new_order_statuses = array();
        foreach ( $order_statuses as $key => $status ) {
            $new_order_statuses[ $key ] = $status;
            if ( 'wc-pending' === $key ) {
                $new_order_statuses['wc-whatsapp-pending'] = 'WhatsApp Pending';
            }
        }
        return $new_order_statuses;
    }

}
