<?php
/**
 * LoadmorePosts
 *
 * @package TeddyBearCustomizeAddon
 */

namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;

use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
use \WP_Query;

class Columns {
	use Singleton;
	protected function __construct() {
		$this->setup_hooks();
	}
	public function setup_hooks() {
		// add_filter('manage_edit-product_columns', [$this, 'manage_edit_product_columns'], 10, 1);
		// add_action('manage_posts_custom_column', [$this, 'manage_posts_custom_column'], 10, 2);
		add_filter('post_row_actions', [$this, 'post_row_actions'], 10, 2);
		
		add_filter('manage_edit-shop_order_columns', [$this, 'manage_edit_shop_order_columns'], 10, 1);
		add_action('manage_posts_custom_column', [$this, 'manage_shop_order_custom_column'], 10, 2);
		
		add_action('wp_ajax_nopriv_futurewordpress/project/ajax/order/downloads', [$this, 'ajax_order_downloads'], 10, 0);
		add_action('wp_ajax_futurewordpress/project/ajax/order/downloads', [$this, 'ajax_order_downloads'], 10, 0);

	}
	public function manage_edit_product_columns($columns) {
		$columns['pops_actions'] = __('Pops', 'teddybearsprompts');
		return $columns;
		$offset = 2;
		$pops_one = ['pops_actions' => __('Pops', 'teddybearsprompts')];
		$newColumns = array_slice($columns, 0, $offset, true) + $pors_one + array_slice($columns, $offset, NULL, true);
		return $newColumns;
	}
	public function manage_posts_custom_column($column, $post_id) {
		switch ($column) {
			case 'pops_actions':
				$post_meta = (array) get_post_meta($post_id, '_teddy_custom_data', true);
				$global_key = (isset($post_meta['product_type']) && $post_meta['product_type'] == 'sitting')?'sitting-global':'standing-global';
				$global_post_id = apply_filters('teddybear/project/system/getoption', $global_key, 0);
				?>
				<button class="fwp-button fwppopspopup-open" type="button" <?php echo esc_attr(
					(
						apply_filters('teddybear/project/system/isactive', 'standard-forceglobal') && 
						$global_post_id != get_the_ID()
					)?'disabled':''
				); ?>><?php esc_html_e('Open', 'teddybearsprompts'); ?></button>
				<?php
				break;
			default:
				break;
		}
	}
	public function post_row_actions($actions, $post) {
		global $teddy_Plushies;
		if($post->post_type =="product"){

			if($teddy_Plushies->is_accessory($post->ID)) {return $actions;}
			
			$post_meta = (array) get_post_meta($post->ID, '_teddy_custom_data', true);
			$global_key = (isset($post_meta['product_type']) && $post_meta['product_type'] == 'sitting')?'sitting-global':'standing-global';
			$global_post_id = apply_filters('teddybear/project/system/getoption', $global_key, 0);
			if(!(apply_filters('teddybear/project/system/isactive', 'standard-forceglobal') && $global_post_id != get_the_ID())) {
				$config = ['id' => $post->ID];
				$actions['pops_actions'] = '<a href="#" class="fwppopspopup-open" type="button" data-config="' . esc_attr(json_encode($config)) . '">' . esc_html__('Setup', 'teddybearsprompts') . '</a>';
			}
		}
		return $actions;
	}

	
	public function manage_edit_shop_order_columns($columns) {
		$columns['teddy_attaches'] = __('Attached', 'teddybearsprompts');
		return $columns;
	}
	public function manage_shop_order_custom_column($column, $post_id) {
		global $teddy_Certificate;global $teddy_Voices;
		switch($column) {
			case 'teddy_attaches':
				$json = ['order_id' => $post_id];
				$voiceDefination = ['tooltip' => __('Voice Skipped', 'teddybearsprompts'), 'icon' => 'no-alt', 'defined' => 0];
				$order = wc_get_order($post_id);
				if($order && !is_wp_error($order)) {
					foreach($order->get_items() as $order_item_id => $order_item) {
						if($teddy_Voices->should_exists_voices($order, $order_item)) {
							if($voiceDefination['defined'] == 0) {
								$voiceDefination = ['tooltip' => __('Voice should be sent through email.', 'teddybearsprompts'), 'icon' => 'email-alt', 'defined' => 1];
								// print_r("Should </br>");
							}
							if($teddy_Voices->has_single_voices($order, $order_item)) {
								if($voiceDefination['defined'] != 2) {
									$voiceDefination = ['tooltip' => __('Voice defined and is downloadable.', 'teddybearsprompts'), 'icon' => 'download', 'defined' => 2];
									// print_r("Exists </br>");
								}
							}
						// } else {
							// print_r($order_item->get_meta_data());
						}
						// print_r("Ended </br>");
					}
				}
				?>
				<button type="button" class="btn launch_orderd_arrachments tippy-tooltip" data-config="<?php echo esc_attr(json_encode($json)); ?>" data-tippy-content="<?php echo esc_attr($voiceDefination['tooltip']); ?>">
					<span class="dashicons-before dashicons-<?php echo esc_attr($voiceDefination['icon']); ?>"></span>
				</button>
				<?php
				break;
			default:
				break;
		}
	}
	public function ajax_order_downloads() {
		global $teddy_Certificate;global $teddy_Voices;
		$json = ['hooks' => ['order_downloads_failed'], 'attached' => []];
		if(isset($_POST['order_id'])) {
			$order = wc_get_order((int) $_POST['order_id']);
			if($order && !is_wp_error($order)) {
				foreach($order->get_items() as $order_item_id => $order_item) {
					$voices = (array) $teddy_Voices->get_single_voices($order, $order_item);
					foreach($voices as $i => $voice_path) {
						$voices[$i] = str_replace(ABSPATH, home_url('/'), $voice_path);
					}
					$certificates = (array) $teddy_Certificate->get_single_certificates($order, $order_item);
					foreach($certificates as $i => $pdf_path) {
						$certificates[$i] = str_replace(ABSPATH, home_url('/'), $pdf_path);
					}
					$json['attached'][] = [
						'product'		=> $order_item->get_name(),
						'quantity'		=> $order_item->get_quantity(),
						'product_id'	=> $order_item->get_product_id(),
						'product_edit'	=> admin_url(sprintf('/post.php?post=%d&action=edit', $order_item->get_product_id())),
						'order_item_id'	=> $order_item->get_id(),
						'order_id'		=> $order->get_id(),
						'attached'		=> [
							'voices'		=> $voices,
							'certificates'	=> $certificates
						],
						'meta_data'		=> []
					];
				}
				if(count($json['attached']) <= 0) {
					$json['errorSVG'] = esc_url(TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_URI . '/icons/Information carousel_Monochromatic.svg');
				}
				$json['hooks'] = ['order_downloads_success'];
				wp_send_json_success($json);
			}
		}
		wp_send_json_error($json);
	}
	
}
