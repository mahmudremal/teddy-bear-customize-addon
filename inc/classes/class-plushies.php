<?php
/**
 * Block Patterns
 *
 * @package TeddyBearCustomizeAddon
 */

namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;

use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;

class Plushies {
	use Singleton;
	protected function __construct() {
		global $Plushies;
		$Plushies = $this;
		$this->setup_hooks();
	}
	protected function setup_hooks() {
	}
	public function get_accessories_terms() {
		return [
			'standing-outfit' => [
				'title'		=> __('Standing outfit', 'teddybearsprompts')
			],
			'sitting-outfit'  => [
				'title'		=> __('Sitting outfit', 'teddybearsprompts')
			],
			'standing-footwear' => [
				'title'		=> __('Standing footwear', 'teddybearsprompts')
			],
			'sitting-footwear' => [
				'title'		=> __('Sitting footwear', 'teddybearsprompts')
			],
			'not-accessories' => [
				'title'		=> __('Not accessories nor Product (Others)', 'teddybearsprompts')
			],
		];
	}
	public function is_accessory($post_id) {
		foreach($this->get_accessories_terms() as $_key => $_text) {
			$meta = get_post_meta($post_id, $_key, true);
			if($meta && !is_wp_error($meta) && !empty($meta)) {
				return true;
			}
		}
		return false;
	}
	public function get_accessory_canvas_image($accessory_id, $option, $custom_fields, $product_type = 'standing', $product_id = false) {
		foreach(['outfit', 'footwear'] as $_i => $_key) {
			$_thumb = get_post_meta($accessory_id, $product_type . '-' . $_key . '_thumb', true);
			$meta = get_post_meta($accessory_id, $product_type . '-' . $_key, true);
			if(
				$meta && !is_wp_error($meta) && !empty($meta)
				&&
				$_thumb && !is_wp_error($_thumb) && !empty($_thumb)
			) {
				return (int) $_thumb;
			}
		}
		foreach(['not-accessories'] as $_i => $_key) {
			$_thumb = get_post_meta($accessory_id, $_key . '_thumb', true);
			$meta = get_post_meta($accessory_id, $_key, true);
			if(
				$meta && !is_wp_error($meta) && !empty($meta)
				&&
				$_thumb && !is_wp_error($_thumb) && !empty($_thumb)
			) {
				return (int) $_thumb;
			}
		}
		return false;
	}
}
