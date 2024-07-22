<?php
/**
 * LoadmorePosts
 *
 * @package TeddyBearCustomizeAddon
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
use \WP_Query;
class Voices {
	use Singleton;
	protected function __construct() {
		// load class.
		$this->setup_hooks();
	}
	protected function setup_hooks() {
	}
	public function get_all_voices($order) {
		global $teddy_Product;$voices = [];$order_id = $order->get_id();
		foreach ($order->get_items() as $order_item_id => $order_item) {
			$singles = $this->get_single_voices($order, $order_item);
			foreach ($singles as $voice) {
				$voices[] = $voice;
			}
		}
		return $voices;
	}
	public function get_single_voices($order, $order_item, $return_object = false) {
		global $teddy_Product;global $teddy_Meta;$voices = [];
		$order_id = $order->get_id();
		$item_id = $order_item->get_id();
		$product_id = $order_item->get_product_id();
		$popup_meta = $teddy_Product->get_order_pops_meta($order, $order_item, $product_id);
		if (!$popup_meta || !is_array($popup_meta) || count($popup_meta) <= 0) {return $voices;}
		
		$_dataset = $teddy_Meta->get_order_item_dataset($order_item, $order);
		if (!$_dataset) {return $voices;}
		foreach ($_dataset as $row) {
			if (!is_array($row) || !isset($row['type']) || $row['type'] != 'voice') {continue;}
			if (!isset($row['attaced']) || empty($row['attaced'])) {$row['attaced'] = ['skip' => true];}
			if (isset($row['attaced']['blob']) && !empty($row['attaced']['blob'])) {
				$row['attaced']['blob'] = apply_filters('teddybear/project/slashes/fix', TEDDY_BEAR_CUSTOMIZE_ADDON_UPLOAD_DIR . $row['attaced']['blob']);
			}
			if ($return_object) {
				$voices[] = $row['attaced'];
			} else {
				$voices[] = $row['attaced']['blob'];
			}
		}
		return $voices;
	}
	public function has_single_voices($order, $order_item, $args = []) {
		global $teddy_Product;global $teddy_Order;global $teddy_Meta;
		$order_id  = $order->get_id();
		$item_id  = $order_item->get_id();
		$product_id = $order_item->get_product_id();
		$custom_popup = $teddy_Product->get_order_pops_meta($order, $order_item, $product_id);
		if (!$custom_popup || empty($custom_popup)) {return false;}
		$voiceFileExists = false;
		
		// $item_metas = $order_item->get_meta_data();
		// foreach ($custom_popup as $posI => $posRow) {
		// 	foreach ($posRow as $row) {
		// 		if ($row['type'] == 'voice') {
		// 			if ($this->should_exists_voices($order, $order_item)) {
		// 				$meta_data = $teddy_Meta->get_order_item_dataset($order_item, $order);
		// 				if (!$meta_data) {continue;}
		// 				if (isset($meta_data['field'])) {
		// 					if (!is_array($meta_data['field'])) {
		// 						$meta_data = (array) $meta_data;
		// 					}
		// 					foreach ($meta_data['field'] as $field) {
		// 						foreach ($field as $i => $row) {
		// 							if (strtolower($row['title']) == 'voice') {
		// 								$voiceFileExists = true;
		// 							}
		// 						}
		// 					}
		// 				}
		// 			}
		// 		}
		// 	}
		// }

		$custom_dataset = $teddy_Meta->get_order_item_dataset($order_item, $order);
		if ($custom_dataset && isset($custom_dataset['field'])) {
			foreach ($custom_dataset['field'] as $group) {
				foreach ($group as $index => $row) {
					if (isset($row['voice']) && !empty($row['voice'])) {
						$voiceFileExists = true;
					}
				}
			}
		}
		
		return $voiceFileExists;
	}
	public function should_exists_voices($order, $order_item, $args = []) {
		global $teddy_Product;global $teddy_Order;global $teddy_Meta;
		$order_id  = $order->get_id();
		$item_id  = $order_item->get_id();
		// $product_id = $order_item->get_product_id();
		// $custom_popup = $teddy_Product->get_order_pops_meta($order, $order_item, $product_id);
		// if (!$custom_popup || empty($custom_popup)) {return;}
		$voiceShouldExists = false;
		
		$custom_dataset = $teddy_Meta->get_order_item_dataset($order_item, $order);
		if ($custom_dataset && isset($custom_dataset['field'])) {
			foreach ($custom_dataset['field'] as $group) {
				foreach ($group as $index => $row) {
					if (
						(isset($row['voice']) && !empty($row['voice']))
											||
						(isset($row['voiceLater']) && $row['voiceLater'])
					) {
						$voiceShouldExists = true;
					}
				}
			}
		}
		
		if ($voiceShouldExists === false) {
			$item_metas = $order_item->get_meta_data();
			foreach ($item_metas as $meta) {
				if (in_array(strtolower($meta->key), ['voice'])) {
					// if (in_array(trim(strtolower($meta->key)), ['voice', strtolower('Record your voice')])) {
					$voiceShouldExists = true; // 'voice'; // $row['steptitle'];
				}
			}
		}

		/**
		 * Either we could search voice file.
		 */
		if ($voiceShouldExists === false) {
			$file_path = TEDDY_BEAR_CUSTOMIZE_ADDON_UPLOAD_DIR . 'voice-' . $order_id . '-' . $item_id . '.webm';
			if (file_exists($file_path) && !is_dir($file_path)) {$voiceShouldExists = true;}
		}

		/**
		 * Return result.
		 */
		return $voiceShouldExists;
	}
}
