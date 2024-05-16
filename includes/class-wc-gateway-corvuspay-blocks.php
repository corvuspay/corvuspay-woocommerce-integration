<?php
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Block-based checkout support
 *
 * @since 2.6.0
 */
	final class WC_Gateway_CorvusPay_Blocks_Support extends AbstractPaymentMethodType {

	/**
	 * The gateway instance.
	 *
	 * @var WC_Gateway_CorvusPay
	 */
	private $gateway;

	/**
	 * Payment method id.
	 *
	 * @var string
	 */
	protected $name = 'corvuspay';

	/**
	 * Initializes the payment method type.
	 */
	public function initialize() {
		$this->settings = get_option( 'corvuspay_settings_version', [] );
		$gateways       = WC()->payment_gateways->payment_gateways();
		$this->gateway  = $gateways[ $this->name ];
	}

	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @return boolean
	 */
	public function is_active() {
		return $this->gateway->is_available();
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$script_path       = '/assets/js/frontend/blocks.js';
		$script_asset_path = '/wp-content/plugins/corvuspay-woocommerce-integration' . '/assets/js/frontend/blocks.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require( $script_asset_path )
			: array(
				'dependencies' => array(),
				'version'      => '1'
			);
		$script_url        = '/wp-content/plugins/corvuspay-woocommerce-integration' . $script_path;

		wp_register_script(
			'wc-gateway-corvuspay-blocks',
			$script_url,
			$script_asset[ 'dependencies' ],
			$script_asset[ 'version' ],
			true
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'wc-gateway-corvuspay-blocks', 'corvuspay-woocommerce-integration', '/wp-content/plugins/corvuspay-woocommerce-integration' . '/languages/' );
		}

		return [ 'wc-gateway-corvuspay-blocks' ];
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		$contains_subscription = false;
		if ( $this->gateway->options->subscriptions ) {
			$contains_subscription = WC_Subscriptions_Cart::cart_contains_subscription() || wcs_cart_contains_renewal();
			$this->gateway->log->debug( '$contains_subscription: ' . wp_json_encode( $contains_subscription ) );
		}
		return [
			'title'       => $this->gateway->title,
			'description' => $this->gateway->replace_card_type_icons(),
			'supports'    => array_filter( $this->gateway->supports, [ $this->gateway, 'supports' ] ),
			'showSaveOption' => $this->gateway->get_option('tokenization'),
			'showSavedCards' => $this->gateway->get_option('tokenization'),
			'disableAndCheckSaveOption' => $contains_subscription
		];
	}
}