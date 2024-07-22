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
		$_makeup = $order_item->get_meta('custom_makeup', true);
		if (empty($_makeup)) {$_makeup = [];}
		return (array) $_makeup;
	}
	public function get_order_item_dataset($order_item, $order) {
		$_dataset = $order_item->get_meta('custom_dataset', true);
		// if (empty($_dataset)) {
		// 	$_dataset = get_post_meta($order_item->get_id());
		// }
		if (empty($_dataset)) {$_dataset = [];}
		return (array) $_dataset;
	}
	public function get_order_item_popset($order_item, $order_id, $post_id = false) {
		$_popset = $order_item->get_meta('custom_popset', true);
		if (empty($_popset)) {$_popset = [];}
		return (array) $_popset;
	}
}
