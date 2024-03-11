<?php
/**
 * LoadmorePosts
 *
 * @package TeddyBearCustomizeAddon
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
use \WP_Query;
class Meta {
	use Singleton;
	protected function __construct() {
		// load class.
		$this->setup_hooks();
	}
	protected function setup_hooks() {
	}
	public function get_order_item_charges($order_item, $order) {
		return (array) $order_item->get_meta('custom_makeup', true);
	}
	public function get_order_item_dataset($order_item, $order) {
		return (array) $order_item->get_meta('custom_dataset', true);
	}
	public function get_order_item_popset($order_item, $order_id, $post_id = false) {
		$item_meta = $order_item->get_meta('custom_popset', true);
		
	}
}
