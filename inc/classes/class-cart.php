<?php
/**
 * The OpenAI ChatGPT-3.
 * https://www.npmjs.com/package/openai
 * https://www.npmjs.com/package/chatgpt
 * 
 * @package TeddyBearCustomizeAddon
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;

class Cart {
	use Singleton;
	private $base;
	private $showedAlready;
	protected function __construct() {
		$this->base = [];
		$this->showedAlready = [];
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		// add_action('wp_ajax_nopriv_add_to_cart', [$this, 'ajax_add_to_cart'], 10, 0);
		// add_action('wp_ajax_add_to_cart', [$this, 'ajax_add_to_cart'], 10, 0);
		add_action('wp_ajax_nopriv_futurewordpress/project/ajax/cart/add', [$this, 'ajax_add_to_cart'], 10, 0);
		add_action('wp_ajax_futurewordpress/project/ajax/cart/add', [$this, 'ajax_add_to_cart'], 10, 0);

		// add_action('woocommerce_cart_calculate_fees', [$this, 'woocommerce_cart_calculate_fees'], 10, 0);
		add_filter('woocommerce_cart_item_name', [$this, 'display_additional_charges'], 10, 3);
		// add_filter('woocommerce_order_item_name', [$this, 'woocommerce_order_item_name'], 10, 3);
		
		add_action('woocommerce_before_calculate_totals', [$this, 'woocommerce_calculate_totals'], 10, 1);
		add_action('woocommerce_checkout_create_order_line_item', [$this, 'woocommerce_checkout_create_order_line_item'], 10, 4);
	}
	public function get_cart_item_meta($cart_item) {
		$meta_key = 'custom_teddey_bear_makeup';
		return isset($cart_item[$meta_key])?floatval($cart_item[$meta_key]):0;
	}

	public function ajax_add_to_cart() {
		if(!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
			wp_send_json_error('Missing required data.');
		}
		$json = [
			'hooks' => ['popup_submitting_failed'],
			'message' => __('Something went wrong. Please try again.', 'teddybearsprompts')
		];
		$product_id = intval($_POST['product_id']);
		$quantity = intval($_POST['quantity']);
		$product = wc_get_product($product_id);
		if (!$product || !$product->is_purchasable()) {
			wp_send_json_error('Invalid product or product is not purchasable.');
		}
		
		
		try {
			$dataset = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', stripslashes(html_entity_decode($_POST['dataset']))), true);
			$charges = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', stripslashes(html_entity_decode($_POST['charges']))), true);
			if(isset($_FILES['voice'])) {
				$is_uploaded = $this->custom_upload_audio_video($_FILES['voice']);
			}
			WC()->cart->add_to_cart($product_id, $quantity, 0, [], [
				'custom_teddey_bear_makeup' => $charges,
				'custom_teddey_bear_data'	=> $dataset
			]);
			$cart_item_key = WC()->cart->generate_cart_id($product_id);
        	$cart_item = WC()->cart->get_cart_item($cart_item_key);

			// print_r(WC()->cart->get_cart());
			// wc_add_order_item_meta($cart_item['data']->get_id(), 'custom_teddey_bear_data', $dataset);

			$json['hooks'] = ['popup_submitting_done'];
			$json['redirectedTo'] = wc_get_checkout_url();
			$json['message'] = __('Product added to cart successfully. Please hold on until you\'re redirected to checkout page.', 'teddybearsprompts');
			wp_send_json_success($json);
		} catch (\Exception $e) {
			// Handle the exception here
			$json['message'] = 'Error: ' . $e->getMessage();
			wp_send_json_error($json);
		}
		
	}
	public function woocommerce_cart_calculate_fees() {
		if (is_admin() && !defined('DOING_AJAX')) {return;}
		$cart = WC()->cart;
		
		foreach($cart->get_cart() as $cart_item_key => $cart_item) {
			if(array_key_exists('custom_teddey_bear_makeup', $cart_item)) {
				// $additional_cost = 0;
				// print_r($cart->get_cart());
				foreach($cart_item['custom_teddey_bear_makeup'] as $fee) {
					$cart->add_fee($fee['item'], ($fee['price'] * $cart_item['quantity']), true, 'standard');
					// $additional_cost += ($fee['price'] * $cart_item['quantity']);
				}
				// $cart_item['data']->set_price($cart_item['data']->get_price() + $additional_cost);
			}
		}
	}
	public function woocommerce_order_item_name($item_name, $order_item, $order) {
		$meta_data = $order_item->get_meta_data();
		if($meta_data && !empty($meta_data)) {
            $item_name .= '<br><small class="additional-charges">';
            foreach ($meta_data as $meta) {
                $key = $meta->key;$value = $meta->value;
                $item_name .= esc_html($key).': '.wc_price($value).', ';
            }
            // Remove the trailing comma and space
            $item_name = rtrim($item_name, ', ');
            $item_name .= '</small>';
        }

    	return $item_name;
	}
	public function display_additional_charges($item_name, $cart_item, $cart_item_key) {
		// if(isset($cart_item['_additional_charges_applied'])) {return $item_name;}
		if(isset($cart_item['custom_teddey_bear_makeup']) && !in_array($cart_item_key, $this->showedAlready)) {
			foreach ($cart_item['custom_teddey_bear_makeup'] as $fee) {
				$item_name .= '<br>
				<small class="additional-charges">'.esc_html($fee['item']).': '.wc_price($fee['price']).' x '.esc_html(number_format_i18n($cart_item['quantity'], 0)).'</small>';
			}
			// $cart_item['_additional_charges_applied'] = true;
			$this->showedAlready[] = $cart_item_key;
		}
		return $item_name;
	}
	public function woocommerce_calculate_totals($cart) {
		if(is_admin() && !defined('DOING_AJAX')) {return;}
	
		foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
			if (array_key_exists('custom_teddey_bear_makeup', $cart_item)) {
				$additional_cost = 0;
				foreach($cart_item['custom_teddey_bear_makeup'] as $fee) {
					$additional_cost += ($fee['price'] * $cart_item['quantity']);
				}
				$cart_item['data']->set_price($cart_item['data']->get_price() + $additional_cost);
			}
		}
	}

	public function woocommerce_checkout_create_order_line_item($item, $cart_item_key, $values, $order) {
		if(isset($values['custom_teddey_bear_makeup'])) {
			foreach($values['custom_teddey_bear_makeup'] as $meta) {
				$item->add_meta_data(esc_html($meta['item']), $meta['price'], true);
			}
		}
		if(isset($values['custom_teddey_bear_data'])) {
			$extra_data = (array) $values['custom_teddey_bear_data'];
			if(isset($extra_data['field'])) {
				$extra_data['field'] = empty($extra_data['field'])?[]:(array) $extra_data['field'];
				foreach($extra_data['field'] as $key => $fields) {
					$fields = empty($fields)?[]:(array) $fields;
					foreach($fields as $fkey => $field) {
						$field['title'] = empty($field['title'])?__('N/A', 'teddybearsprompts'):$field['title'];
						$item->add_meta_data($field['title'], $field['value'], true);
					}
				}
			}
		}
	}

	public function custom_upload_audio_video($file) {
		$upload_dir = wp_upload_dir();$custom_dir = 'custom_popup';
		$target_dir = $upload_dir['basedir'].'/'.$custom_dir.'/';
		if(!file_exists($target_dir)) {mkdir($target_dir, 0755, true);}
		$file_name = $file['name'];$file_tmp = $file['tmp_name'];$file_type = $file['type'];
		$allowed_regex = '/^(audio|video)\/(.*?)/i';
		if(!preg_match($allowed_regex, $file_type)) {
			throw new \Exception(__('Error: Only audio and video files are allowed.', 'teddybearsprompts'));
		}
		$max_file_size = 400 * 1024 * 1024;
		if($file['size'] > $max_file_size) {
			throw new \Exception(__('Error: File size exceeds the maximum limit of 400 MB.', 'teddybearsprompts'));
		}
		$target_file = $target_dir . $file_name;
		if(!move_uploaded_file($file_tmp, $target_file)) {
			throw new \Exception(__('Error uploading file.', 'teddybearsprompts'));
		}
		return true;
	}
  
}
