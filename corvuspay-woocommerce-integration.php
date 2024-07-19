<?php
/**
 * Plugin Name: CorvusPay WooCommerce Payment Gateway
 * Plugin URI: https://www.corvuspay.com/
 * Description: Extends WooCommerce with CorvusPay Credit Card payments.
 * Version: 2.6.1
 * Author: Corvus Pay d.o.o.
 * Author URI: https://www.corvuspay.com/
 * Copyright: © 2024 Corvus Pay
 * License: GNU General Public License v2.0 (or later)
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 4.0
 * Tested up to: 6.6
 * WC requires at least: 3.0
 * WC tested up to: 9.1.2
 * Text Domain: corvuspay-woocommerce-integration
 * Domain Path: /languages/
 *
 * @package corvuspay-woocommerce-integration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'plugins_loaded', 'woocommerce_corvuspay_init', 0 );

// Make the CorvusPay gateway available to WC.
add_filter( 'woocommerce_payment_gateways', 'add_gateway' );

// Registers WooCommerce Blocks integration.
add_action( 'woocommerce_blocks_loaded', 'woocommerce_corvuspay_block_support');

add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

/**
 * Echo WooCommerce not installed or not active notice.
 */
function woocommerce_corvuspay_notice_missing_wc() {
	?>
	<div class="notice notice-error">
		<p><?php esc_html_e( 'CorvusPay requires WooCommerce to be installed and active.', 'corvuspay-woocommerce-integration' ); ?></p>
	</div>
	<?php
}

/**
 * Add Settings link to plugin page.
 *
 * @param array $links List of existing plugin action links.
 *
 * @return array List of modified plugin action links.
 */
function corvuspay_action_links( $links ) {
	$settings = array(
		'<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=corvuspay' ) ) . '">' . esc_html__( 'Settings', 'corvuspay-woocommerce-integration' ) . '</a>',
	);

	return array_merge( $settings, $links );
}

/**
 * Add CorvusPay Gateway.
 *
 * @param array $methods Methods.
 *
 * @return array
 */
function woocommerce_add_corvuspay_gateway( $methods ) {
	$methods[] = 'WC_Gateway_CorvusPay';

	return $methods;
}

/**
 * Init CorvusPay WooCommerce gateway plugin.
 */
function woocommerce_corvuspay_init() {
	define( 'WC_CORVUSPAY_SETTINGS_VERSION', 4 );
	define( 'WC_CORVUSPAY_FILE', __FILE__ );
	define( 'WC_CORVUSPAY_PATH', dirname( __FILE__ ) );

	$domain = 'corvuspay-woocommerce-integration';
	$locale = apply_filters( 'plugin_locale', determine_locale(), $domain );
	$mofile = WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ) . '/languages/' . $domain . '-' . $locale . '.mo';
	unload_textdomain( $domain );
	load_textdomain( $domain, $mofile );
	load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		add_action( 'admin_notices', 'woocommerce_corvuspay_notice_missing_wc' );

		return;
	}

	require_once WC_CORVUSPAY_PATH . '/includes/class-wc-gateway-corvuspay.php';

	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'corvuspay_action_links' );

	add_filter( 'woocommerce_payment_gateways', 'woocommerce_add_corvuspay_gateway' );
	WC_Gateway_CorvusPay::get_instance()->init_hooks();
}

/**
 * Add the WC_Gateway_CorvusPay to the list of available gateways.
 *
 * @param array
 */
function add_gateway( $gateways ) {
	$gateways[] = 'WC_Gateway_CorvusPay';
	return $gateways;
}

/**
 * Registers WooCommerce Blocks integration.
 *
 */
function woocommerce_corvuspay_block_support() {
	if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
		require_once WC_CORVUSPAY_PATH . '/includes/class-wc-gateway-corvuspay-blocks.php';
		add_action(
			'woocommerce_blocks_payment_method_type_registration',
			function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
				$payment_method_registry->register( new WC_Gateway_CorvusPay_Blocks_Support() );
			}
		);
	}
}
