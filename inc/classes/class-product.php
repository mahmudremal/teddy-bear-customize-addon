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
		global $teddyProduct;
		$teddyProduct = $this;
	}
	public function setup_hooks() {
		add_action('uael_woo_products_before_summary_wrap', [$this, 'products_before_summary_wrap'], 10, 2);
		// add_filter('get_post_metadata', [$this, 'get_post_metadata'], 99, 4);
	}
	public function products_before_summary_wrap($product_id, $settings) {
		// do_action('woocommerce_before_shop_loop_item');
		echo do_shortcode('[yith_wcwl_add_to_wishlist]', false);
		
		if(!apply_filters('teddybear/project/system/isactive', 'badges-enable')) {return;}
		
		$_meta = (array) get_post_meta($product_id, '_teddy_custom_data', true);
		if(isset($_meta['isFeatured']) || isset($_meta['isBestSeller'])) {
		  if(isset($_meta['isFeatured']) && apply_filters('teddybear/project/system/isactive', 'badges-featured')) { ?><span class="uael-woo-featured" style="<?php echo esc_attr(
			'color: ' . apply_filters('teddybear/project/system/getoption', 'badges-featured-color', '#') . ';'.
			'background: ' . apply_filters('teddybear/project/system/getoption', 'badges-featured-bgcolor', '#') . ';'
		  ); ?>;"><?php esc_html_e('Featured', 'teddybearsprompts'); ?></span><?php }
		  if(isset($_meta['isBestSeller']) && apply_filters('teddybear/project/system/isactive', 'badges-bestseller')) { ?><span class="uael-woo-bestseller" style="<?php echo esc_attr(
			'color: ' . apply_filters('teddybear/project/system/getoption', 'badges-bestseller-color', '#') . ';'.
			'background: ' . apply_filters('teddybear/project/system/getoption', 'badges-bestseller-bgcolor', '#') . ';'
		  ); ?>;"><?php esc_html_e('Best Seller', 'teddybearsprompts'); ?></span><?php }
		}
	}
	public function get_post_meta($post_id, $meta_key, $single) {
		$value = get_post_meta($post_id, $meta_key, $single);
		return $this->get_post_metadata($value, $post_id, $meta_key, $single);
	}
	public function get_post_metadata($value, $post_id, $meta_key, $single) {
		$global_post_id = apply_filters('teddybear/project/system/getoption', 'standard-global', 0);
		if ($single && $meta_key == '_product_custom_popup' && $post_id != $global_post_id) {
			if (!$value || !is_array($value) || apply_filters('teddybear/project/system/isactive', 'standard-forceglobal')) {
				$value = get_post_meta($global_post_id, '_product_custom_popup', true);
			}
		}
		return $value;
	}
	
}
