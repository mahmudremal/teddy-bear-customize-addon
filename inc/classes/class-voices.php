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
		global $teddy_Voices;
		$teddy_Voices = $this;
		// load class.
		$this->setup_hooks();
	}
	protected function setup_hooks() {
	}
	public function get_all_voices($order) {
		global $teddyProduct;$voices = [];$order_id = $order->get_id();
		foreach($order->get_items() as $order_item_id => $order_item) {
			$singles = $this->get_single_voices($order, $order_item);
			foreach($singles as $voice) {
				$voices[] = $voice;
			}
		}
		return $voices;
	}
	public function get_single_voices($order, $order_item) {
		global $teddyProduct;$voices = [];
		$order_id = $order->get_id();
		$item_id = $order_item->get_id();
		$product_id = $order_item->get_product_id();
		$popup_meta = $teddyProduct->get_order_pops_meta($order, $order_item, $product_id);
		if(!$popup_meta || !is_array($popup_meta) || count($popup_meta) <= 0) {return $voices;}
		
		foreach($popup_meta as $posI => $posRow) {
			foreach($posRow as $i => $field) {
				if($field['type'] == 'voice') {
					$item_meta_data = $order_item->get_meta('custom_teddey_bear_data', true);
					if(!$item_meta_data) {continue;}
					foreach($item_meta_data['field'] as $i => $iRow) {
						foreach($iRow as $j => $jRow) {
							if(isset($jRow['voice']) && !empty($jRow['voice'])) {
								$voices[] = TEDDY_BEAR_CUSTOMIZE_ADDON_UPLOAD_DIR . '' . $jRow['voice'];
							}
						}
					}
				}
			}
		}
		return $voices;
	}
	public function has_single_voices($order, $order_item, $args = []) {
		global $teddyProduct;global $teddyBear__Order;
		$order_id  = $order->get_id();
		$item_id  = $order_item->get_id();
		$product_id = $order_item->get_product_id();
		$custom_popup = $teddyProduct->get_order_pops_meta($order, $order_item, $product_id);
		if(!$custom_popup || empty($custom_popup)) {return false;}
		$voiceFileExists = false;
		
		$item_metas = $order_item->get_meta_data();
		foreach($custom_popup as $posI => $posRow) {
			foreach($posRow as $row) {
				if($row['type'] == 'voice') {
					if($this->should_exists_voices($order, $order_item)) {
						$meta_data = $order_item->get_meta('custom_teddey_bear_data', true);
						if(isset($meta_data['field'])) {
							if(!is_array($meta_data['field'])) {
								$meta_data = (array) $meta_data;
							}
							foreach($meta_data['field'] as $field) {
								foreach($field as $i => $row) {
									if(strtolower($row['title']) == 'voice') {
										$voiceFileExists = true;
									}
								}
							}
						}
					}
				}
			}
		}
		
		return $voiceFileExists;
	}
	public function should_exists_voices($order, $order_item, $args = []) {
		// global $teddyProduct;global $teddyBear__Order;
		// $order_id  = $order->get_id();
		// $item_id  = $order_item->get_id();
		// $product_id = $order_item->get_product_id();
		// $custom_popup = $teddyProduct->get_order_pops_meta($order, $order_item, $product_id);
		// if(!$custom_popup || empty($custom_popup)) {return;}
		// $voiceShouldExists = false;
		
		$item_metas = $order_item->get_meta_data();
		foreach($item_metas as $meta) {
			if(in_array(strtolower($meta->key), ['voice'])) {
			// if(in_array(trim(strtolower($meta->key)), ['voice', strtolower('Record your voice')])) {
				$voiceShouldExists = true; // 'voice'; // $row['steptitle'];
			}
		}
		
		// foreach($custom_popup as $posI => $posRow) {
		// 	foreach($posRow as $row) {
		// 		if($row['type'] == 'voice') {
					// above foreach loop moved from here
		// 		}
		// 	}
		// }
		return $voiceShouldExists;
	}
}
