<?php
/**
 * Block Patterns
 *
 * @package TeddyBearCustomizeAddon
 */

namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;

use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;

class Email {
	use Singleton;
	protected function __construct() {
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		// add_action('woocommerce_order_status_completed', [$this, 'woocommerce_order_status_completed'], 10, 2);
	}
	public function woocommerce_order_status_completed($order_id, $order) {
		if(!in_array($order->get_status(), ['completed'])) {return;}
		do_action('woocommerce_order_action_send_birth_certificates', $order);
	}
}
