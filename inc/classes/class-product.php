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
		
		// if(!apply_filters('teddybear/project/system/isactive', 'badges-enable')) {return;}
		
		$_meta = (array) get_post_meta($product_id, '_teddy_custom_data', true);
		$filteredData = [];// print_r([$_meta]);
		foreach($_meta as $key => $value) {
			if(strpos($key, 'badge-') !== false) {
				if($value == 'on' && count($filteredData) <= 0) {$filteredData[substr($key, 6)] = $value;}
			}
		}

		if(count($filteredData) >= 1) :
		?>
			<?php foreach($filteredData as $i => $label) : ?>
		<span class="uael-woo-badges" style="<?php echo esc_attr(
				'color: ' . apply_filters('teddybear/project/system/getoption', 'teddy-badge-textcolor-' . $i, '#') . ';'.
				'background: ' . apply_filters('teddybear/project/system/getoption', 'teddy-badge-backgound-' . $i, '#') . ';'
		  		); ?>">
				<span class="uael-woo-badges__text"><?php echo esc_html(apply_filters('teddybear/project/system/getoption', 'teddy-badge-label-' . $i, '')); ?></span>
		</span>
			<?php endforeach; ?>
		<?php
		endif;
		if(true) {return;}
		if(isset($_meta['isFeatured']) || isset($_meta['isBestSeller']) || isset($_meta['onSale'])) {
		  if(isset($_meta['isFeatured']) && apply_filters('teddybear/project/system/isactive', 'badges-featured')) { ?><span class="uael-woo-featured" style="<?php echo esc_attr(
			'color: ' . apply_filters('teddybear/project/system/getoption', 'badges-featured-color', '#') . ';'.
			'background: ' . apply_filters('teddybear/project/system/getoption', 'badges-featured-bgcolor', '#') . ';'
		  ); ?>;"><?php esc_html_e('Featured', 'teddybearsprompts'); ?></span><?php }
		  if(isset($_meta['isBestSeller']) && apply_filters('teddybear/project/system/isactive', 'badges-bestseller')) { ?><span class="uael-woo-bestseller" style="<?php echo esc_attr(
			'color: ' . apply_filters('teddybear/project/system/getoption', 'badges-bestseller-color', '#') . ';'.
			'background: ' . apply_filters('teddybear/project/system/getoption', 'badges-bestseller-bgcolor', '#') . ';'
		  ); ?>;"><?php esc_html_e('Best Seller', 'teddybearsprompts'); ?></span><?php }
		  if(isset($_meta['onSale']) && !empty(trim($_meta['onSale'])) && apply_filters('teddybear/project/system/isactive', 'badges-onsale')) { ?><span class="uael-woo-onsale" style="<?php echo esc_attr(
			'color: ' . apply_filters('teddybear/project/system/getoption', 'badges-onsale-color', '#') . ';'.
			'background: ' . apply_filters('teddybear/project/system/getoption', 'badges-onsale-bgcolor', '#') . ';'
		  ); ?>;"><?php echo esc_html($_meta['onSale']); ?></span><?php }
		}
	}
	public function get_order_pops_meta($order_id, $order_item, $post_id) {
		// $item_meta = get_option('pops_order' . $order_id . '_item_' . $item_id, false);
		$item_meta = $order_item->get_meta('custom_pops_data', true);
		if($item_meta && ! is_wp_error($item_meta) && !empty($item_meta)) {return $item_meta;}
		return $this->get_post_meta($post_id, '_product_custom_popup', true);
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
