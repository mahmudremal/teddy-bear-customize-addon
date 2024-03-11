<?php
/**
 * LoadmorePosts
 *
 * @package TeddyBearCustomizeAddon
 */

namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;

use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
use \WP_Query;

class Product {
	use Singleton;
	protected function __construct() {
		$this->setup_hooks();
	}
	public function setup_hooks() {
		add_action('uael_woo_products_add_to_cart_before', [$this, 'uael_woo_products_add_to_cart_before'], 10, 2);
		add_action('uael_woo_products_add_to_cart_after', [$this, 'uael_woo_products_add_to_cart_after'], 10, 2);
		add_action('uael_woo_products_before_summary_wrap', [$this, 'products_before_summary_wrap'], 10, 2);
		// add_filter('get_post_metadata', [$this, 'get_post_metadata'], 99, 4);
	}
	public function products_before_summary_wrap($product_id, $settings) {
		// do_action('woocommerce_before_shop_loop_item');
		echo do_shortcode('[yith_wcwl_add_to_wishlist]', false);
		
		// if (!apply_filters('teddybear/project/system/isactive', 'badges-enable')) {return;}
		
		$_meta = (array) get_post_meta($product_id, '_teddy_custom_data', true);
		$filteredData = [];// print_r([$_meta]);
		foreach ($_meta as $key => $value) {
			if (strpos($key, 'badge-') !== false) {
				if ($value == 'on' && count($filteredData) <= 0) {$filteredData[substr($key, 6)] = $value;}
			}
		}
		if (count($filteredData) >= 1) {
			foreach ($filteredData as $i => $label) {
				?>
				<span class="uael-woo-badges" style="<?php echo esc_attr(
					'color: ' . apply_filters('teddybear/project/system/getoption', 'teddy-badge-textcolor-' . $i, '#') . ';'.
					'background: ' . apply_filters('teddybear/project/system/getoption', 'teddy-badge-backgound-' . $i, '#') . ';'
					); ?>">
					<span class="uael-woo-badges__text"><?php echo esc_html(apply_filters('teddybear/project/system/getoption', 'teddy-badge-label-' . $i, '')); ?></span>
				</span>
				<?php
			}
		}
	}

	public function uael_woo_products_add_to_cart_before($product_id, $settings) {
		global $teddy_Plushies;$gallery = [];
		$_product = wc_get_product($product_id);
		$_product_ids = $_product->get_gallery_image_ids();
		foreach ($_product_ids as $i => $attachment_id) {
			$Original_image_url = wp_get_attachment_url($attachment_id);
			$gallery[] = [
				'id'			=> $attachment_id,
				'image_url'		=> wp_get_attachment_image_src($attachment_id, 'woocommerce_thumbnail'),
				// 'thumbnail'	=> wp_get_attachment_image($attachment_id, 'full'),medium
				'thumb_url'		=> wp_get_attachment_image_src($attachment_id, 'thumbnail'),
				'thumb_title'	=> get_the_title($attachment_id),
			];
		}
		$class = ['fwp_custom_actions'];
		if ($teddy_Plushies->is_accessory($product_id)) {
			$class[] = 'is-accessory';
		}
		?>
		<div class="<?php echo esc_attr(implode(' ', $class)); ?>" data-gallery="<?php echo esc_attr(json_encode($gallery)); ?>">
		<?php
	}
	public function uael_woo_products_add_to_cart_after($product_id, $settings) {
		global $teddy_Hooks;$teddy_Hooks->woocommerce_after_add_to_cart_button();
		?></div><?php
	}
	
	public function get_order_pops_meta($order_id, $order_item, $post_id) {
		// $item_meta = get_option('pops_order' . $order_id . '_item_' . $item_id, false);
		$item_meta = $order_item->get_meta('custom_popset', true);
		if ($item_meta && ! is_wp_error($item_meta) && !empty($item_meta)) {return $item_meta;}
		return $this->get_post_meta($post_id, '_product_custom_popup', true);
	}
	public function get_post_meta($post_id, $meta_key, $single) {
		$value = get_post_meta($post_id, $meta_key, $single);
		return $this->get_post_metadata($value, $post_id, $meta_key, $single);
	}
	public function get_post_metadata($value, $post_id, $meta_key, $single) {
		$post_meta = get_post_meta($post_id, '_teddy_custom_data', true);
		$global_key = (isset($post_meta['product_type']) && $post_meta['product_type'] == 'sitting')?'sitting-global':'standing-global';
		$global_post_id = apply_filters('teddybear/project/system/getoption', $global_key, 0);
		if ($single && $meta_key == '_product_custom_popup' && $post_id != $global_post_id) {
			if (!$value || !is_array($value) || apply_filters('teddybear/project/system/isactive', 'standard-forceglobal')) {
				$value = $this->hook_canvas_image_on_global_customization(
					get_post_meta($global_post_id, '_product_custom_popup', true),
					$value
				);
			}
		}
		return $value;
	}
	public function get_frontend_product_json($product_id) {
		global $teddy_Plushies;$product_meta = get_post_meta($product_id, '_teddy_custom_data', true);
		$product_type = (isset($product_meta['product_type']) && $product_meta['product_type'] == 'sitting')?'sitting':'standing';
		$request = ['product_id' => $product_id];

		$json = $this->get_post_meta($product_id, '_product_custom_popup', true);
		$json = ($json && !empty($json))?(array)$json:[];
		foreach ($json as $_posI => $_pos) {
			foreach ($json[$_posI] as $i => $_prod) {

				/**
				 * Translation operations.
				 */
				$field2Translate = ['label', 'heading', 'placeholder', 'steptitle', 'subtitle', 'product_title', 'description'];
				foreach($field2Translate as $toTrans) {
					if (isset($_prod[$toTrans])) {
						if ($toTrans == 'label' && is_numeric($_prod[$toTrans])) {continue;}
						$json[$_posI][$i][$toTrans] = apply_filters('teddybear/project/system/translate/string', $_prod[$toTrans], 'teddybearsprompts', $_prod[$toTrans] . ' - input field');
					}
				}
				
				
				if (!empty(trim($_prod['headerbg']))) {
					$json[$_posI][$i]['headerbgurl'] = wp_get_attachment_url($_prod['headerbg']);
				}
				if (isset($_prod['product']) && !empty(trim($_prod['product']))) {
					$_product = wc_get_product((int) $_prod['product']);
					if ($_product && !is_wp_error($_product)) {
						/**
						 * Check product stock status. If stock out happen then remove Item.
						 */
						$option['stock_status'] = $_product->get_stock_status();
						$json[$_posI][$i]['product_title'] = $_product->get_name();
						// isset($_prod['cost']) && !$_prod['cost'] || empty($_prod['cost'])
						if (true) {
							$json[$_posI][$i]['cost'] = $_product->get_price();
						}
					}
				}
				
				if (isset($_prod['options'])) {
					$_prod['options'] = (!empty($_prod['options']))?(array)$_prod['options']:[];
					foreach ($_prod['options'] as $j => $option) {
						if (isset($option['label']) && !empty($option['label']) && is_numeric($option['label'])) {
							$title = get_the_title($option['label']);
							if ($title && !is_wp_error($title) && !empty($title)) {
								$option['product'] = $option['label'];
								$option['label'] = $title;
							}
						}

						/**
						 * Translation operations.
						 */
						$field2Translate = ['label', 'product_title'];
						foreach($field2Translate as $toTrans) {
							if (isset($option[$toTrans])) {
								if ($toTrans == 'label' && is_numeric($option[$toTrans])) {
									continue;
								}
								$option[$toTrans] = apply_filters('teddybear/project/system/translate/string', $option[$toTrans], 'teddybearsprompts', $option[$toTrans] . ' - input field');
							}
						}
						
						/**
						 * Impliment product ID on this row.
						 */
						if (isset($option['product']) && !empty(trim($option['product']))) {
							$_product = wc_get_product((int) $option['product']);
							if ($_product && !is_wp_error($_product)) {
								/**
								 * Check product stock status. If stock out happen then remove Item.
								 */
								$option['stock_status'] = $_product->get_stock_status();

								$option['product_title'] = $_product->get_name();
								/**
								 * Check whether if custom prices exists or product prices will be applied here.
								 */
								if (!$option['cost'] || empty($option['cost']) || $option['cost'] == 0) {
									$option['cost'] = $_product->get_price();
								}
								/**
								 * Check whether if custom thumbnail added or it will replace with product owned thumbnail.
								 */
								if (!$option['thumb'] || empty($option['thumb']) || $option['thumb'] == 0) {
									// $option['thumb'] = $teddy_Plushies->get_accessory_canvas_image((int) $option['label'], $option, $json, $product_type, $request['product_id']);
									// $option['thumb'] = get_post_thumbnail_id((int) $option['label']);
								}
								/**
								 * Check whether if custom images added or it will replace with product owned image.
								 */
								if (!$option['image'] || empty($option['image']) || $option['image'] == 0) {
									$image_id = $teddy_Plushies->get_accessory_canvas_image((int) $option['product'], $option, $json, $product_type, $request['product_id']);
									if ($image_id) {$option['image'] = $image_id;}
								}
							}
						}

						if (isset($option['image']) && !empty($option['image']) && is_numeric($option['image'])) {
							$option['imageUrl'] = wp_get_attachment_url((int) $option['image']);
						}
						if (isset($option['thumb']) && !empty($option['thumb']) && is_numeric($option['thumb'])) {
							/**
							 * Check whether is it GIF file or not.
							 * If it is a Gif file then return thumbnail full image
							 * Otherwise it will return 150x150 thumbnail.
							 */
							$isGif = wp_attachment_is('gif', (int) $option['thumb']);
							$option['thumbUrl'] = wp_get_attachment_image_url((int) $option['thumb'], 
								$isGif?'full':'thumbnail'
							);
						}
						$json[$_posI][$i]['options'][$j] = $option;
					}
				}
				if (isset($_prod['groups'])) {
					foreach ($_prod['groups'] as $k => $group) {
						if (isset($group['options'])) {
							foreach ($group['options'] as $l => $option) {

								/**
								 * Translation operations.
								 */
								$field2Translate = ['label'];
								foreach($field2Translate as $toTrans) {
									if (isset($option[$toTrans])) {
										if ($toTrans == 'label' && is_numeric($option[$toTrans])) {
											continue;
										}
										$option[$toTrans] = apply_filters('teddybear/project/system/translate/string', $option[$toTrans], 'teddybearsprompts', $option[$toTrans] . ' - input field');
									}
								}
								
								if (!empty($option['label']) && is_numeric($option['label'])) {
									$_prod_accessory = wc_get_product((int) $option['label']);
									if ($_prod_accessory && !is_wp_error($_prod_accessory)) {
										/**
										 * Check product stock status.
										 */
										$option['stock_status'] = $_prod_accessory->get_stock_status();
										$option = [
											...$option,
											'product'		=> (int) $option['label'],
											'label'			=> $_prod_accessory->get_name(),
											'cost'			=> $_prod_accessory->get_price(),
											'thumb'			=> (int) get_post_thumbnail_id((int) $option['label']),
											/**
											 * There should be two possibility.
											 * 1. Frame Image should be define individually from custom pops so that those could be fit on canvas properly.
											 * 2. Image would be define globally.
											 */
											'image'			=> $teddy_Plushies->get_accessory_canvas_image((int) $option['label'], $option, $json, $product_type, $request['product_id'])
										];
									}
								}
								if (true) {
									if (isset($option['image']) && is_numeric($option['image']) && $option['image']) {
										$option['imageUrl'] = wp_get_attachment_url((int) $option['image']);
									}
									if (isset($option['thumb']) && is_numeric($option['thumb']) && $option['thumb']) {
										$option['thumbUrl'] = wp_get_attachment_image_url((int) $option['thumb'], 'thumbnail');
									}
									$json[$_posI][$i]['groups'][$k]['options'][$l] = $option;
								}
							}
							/**
							 * Resort them with serialize
							 */
							// $group['options'] = array_values($group['options']);
						}
					}
				}
			}
		}
		return $json;
	}
	/**
	 * Replacing canvas image on global product instead of global product canvas.
	 */
	public function hook_canvas_image_on_global_customization($global, $private) {
		// foreach ($private as $pos)
		if (is_array($private) || is_object($private)) {
			foreach ($private as $posI => $posRow) {
				foreach ($posRow as $i => $field) {
					if (
						isset($field['headerbg']) && !empty($field['headerbg'])
						&&
						isset($global[$posI][$i]['headerbg'])
					) {
						$global[$posI][$i]['headerbg'] = $field['headerbg'];
					}
				}
			}
		}
		
		return $global;
	}
	
}
