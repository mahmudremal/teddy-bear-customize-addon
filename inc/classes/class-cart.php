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
	private $charges;
	private $dataset;
	private $prod2AdAccessory;
	private $showedAlready;
	private $calculatedAlready;
	protected function __construct() {
		$this->base = [];
		$this->charges = [];
		$this->dataset = [];
		$this->showedAlready = [];
		$this->calculatedAlready = [];
		$this->prod2AdAccessory = false;
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		add_action('wp_ajax_nopriv_teddy/ajax/cart/add', [$this, 'ajax_add_to_cart'], 10, 0);
		add_action('wp_ajax_teddy/ajax/cart/add', [$this, 'ajax_add_to_cart'], 10, 0);

		add_action('wp_ajax_nopriv_teddybear/project/ajax/update/cart', [$this, 'ajax_update_cart'], 10, 0);
		add_action('wp_ajax_teddybear/project/ajax/update/cart', [$this, 'ajax_update_cart'], 10, 0);

		add_action('wp_ajax_nopriv_teddybear/project/ajax/empty/cart', [$this, 'ajax_empty_cart'], 10, 0);
		add_action('wp_ajax_teddybear/project/ajax/empty/cart', [$this, 'ajax_empty_cart'], 10, 0);

		// add_action('woocommerce_cart_calculate_fees', [$this, 'woocommerce_cart_calculate_fees'], 10, 0);
		add_filter('woocommerce_cart_item_name', [$this, 'display_additional_charges'], 10, 3);
		// add_filter('woocommerce_order_item_name', [$this, 'woocommerce_order_item_name'], 10, 3);
		// woocommerce_before_calculate_totals
		add_action('woocommerce_calculate_totals', [$this, 'woocommerce_calculate_totals'], 10, 1);
		// add_filter('woocommerce_cart_item_price', [$this, 'woocommerce_cart_item_price'], 10, 3);

		add_filter('woocommerce_cart_item_subtotal', [$this, 'woocommerce_cart_item_subtotal'], 10, 3);
		add_filter('woocommerce_cart_subtotal', [$this, 'woocommerce_cart_subtotal'], 10, 3);
		add_filter('woocommerce_calculated_total', [$this, 'woocommerce_calculated_total'], 10, 2);

		add_filter('woocommerce_add_cart_item_data', [$this, 'woocommerce_add_cart_item_data'], 10, 4);
		
	}

	public function ajax_add_to_cart() {
		do_action('teddybear/project/nonce/check', $_POST['_nonce']);
		if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
			wp_send_json_error('Missing required data.');
		}
		$json = [
			'hooks' => ['popup_submitting_failed'], 'voices_path' => [],
			'message' => __('Something went wrong. Please try again.', 'teddybearsprompts')
		];
		$product_id = intval($_POST['product_id']);
		$quantity = intval($_POST['quantity']);
		$product = wc_get_product($product_id);
		if (!$product || !$product->is_purchasable()) {
			wp_send_json_error('Invalid product or product is not purchasable.');
		}
		// 
		try {
			// $dataset = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', stripslashes(html_entity_decode($_POST['dataset']??''))), true);
			// $charges = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', stripslashes(html_entity_decode($_POST['charges']??''))), true);
			// 
			$dataset = json_decode(stripslashes(html_entity_decode($_POST['dataset']??'{}')), true);
			$charges = json_decode(stripslashes(html_entity_decode($_POST['charges']??'{}')), true);
			if (isset($_FILES['_blobs'])) {
				if (isset($_FILES['_blobs']['name'])) {
					$_FILES['_blobs'] = [$_FILES['_blobs']];
				}
				foreach ($_FILES['_blobs'] as $file) {
					$is_uploaded = $this->custom_upload_audio_video($file);
					$is_uploaded = str_replace([ABSPATH], [''], $is_uploaded);
					$json['voices_path'][] = $is_uploaded;
				}
			}
			if (isset($_FILES['_canvas'])) {
				if (isset($_FILES['_canvas']['name'])) {
					$_FILES['_canvas'] = [$_FILES['_canvas']];
				}
				foreach ($_FILES['_canvas'] as $file) {
					$is_uploaded = $this->custom_upload_file($file);
					$is_uploaded = str_replace([ABSPATH], [''], $is_uploaded);
					$json['canvas_path'][] = $is_uploaded;
				}
			}
			$this->charges = [];$this->dataset = [];
			foreach ($charges as $i => $row) {
				if (isset($row['product']) && !empty(trim($row['product'])) && is_numeric($row['product'])) {
					$row['product'] = $row['product'];
					$row['cart_item_key'] = WC()->cart->add_to_cart((int) $row['product'], $quantity);
					// 
					$charges[$i] = $row;
				}
			}
			$_canvas = (isset($json['canvas_path']) && !empty($json['canvas_path']))?$json['canvas_path']:false;$quantity = 1;
			foreach ($dataset as $i => $_row) {
				if ($_canvas) {$dataset[$i]['_canvas'] = $_canvas;$_canvas = false;}
				switch ($_row['type']) {
					case 'outfit':
						if ($_row['groups']) {
							foreach ((array) $_row['groups'] as $_groupIndex => $_group) {
								foreach ((array) $_group['options'] as $_gOptIndex => $_option) {
									if (!isset($_option['product']) || empty($_option['product'])) {continue;}
                                    $_product = wc_get_product((int) $_option['product']);
                                    if ($_product && $_product->is_purchasable()) {
                                        // $_option['product'] = $_product->get_id();
                                        $_option['cart_item_key'] = WC()->cart->add_to_cart($_product->get_id(), $quantity);
                                    }
									$_group['options'][$_gOptIndex] = $_option;
                                }
								$_row['groups'][$_groupIndex] = $group;
							}
						}
						break;
					case 'radio':
					case 'select':
					case 'checkbox':
						foreach ((array) $_row['options'] as $_option_index => $_option) {
							if (!isset($_option['product']) || empty($_option['product'])) {continue;}
							$_product = wc_get_product((int) $_option['product']);
							if ($_product && $_product->is_purchasable()) {
								// $_option['product'] = $_product->get_id();
								$_option['cart_item_key'] = WC()->cart->add_to_cart($_product->get_id(), $quantity);
							}
							$_row['options'][$_option_index] = $_option;
						}
						break;
					default:
						$_option = $_row;
						if (isset($_option['product']) && !empty($_option['product'])) {
							$_product = wc_get_product((int) $_option['product']);
							if ($_product && $_product->is_purchasable()) {
								// $_option['product'] = $_product->get_id();
								$_option['cart_item_key'] = WC()->cart->add_to_cart($_product->get_id(), $quantity);
							}
							$_row = $_option;
						}
						break;
				}
				$dataset[$i] = $_row;
			}
			$this->charges = $charges;$this->dataset = $dataset;
			$this->prod2AdAccessory = $product_id;
			$cart_item_key = WC()->cart->add_to_cart($product_id, $quantity);
			// 
			// $cart_item = WC()->cart->get_cart_item($cart_item_key);
			// print_r($cart_item);wp_die();
			// 
			$this->prod2AdAccessory = false;
			$json['hooks'] = ['popup_submitting_done'];
			// $json['redirectedTo'] = wc_get_checkout_url();
			// $json['message'] = __('Product added to cart successfully. Please hold on until you\'re redirected to checkout page.', 'teddybearsprompts');
			if (apply_filters('teddybear/project/system/isactive', 'addons-enable')) {
				$json['wrapping'] = [
					'title' => apply_filters('teddybear/project/system/getoption', 'addons-feetitle', 'Wrapping box'),
					'price' => floatval(apply_filters('teddybear/project/system/getoption', 'addons-feeamount', 0)),
					'thumbnail' => apply_filters('teddybear/project/system/getoption', 'addons-thumbnail', '')
				];
			}
			
			$json['message'] = false;
			$json['confirmation'] = [
				'title'				=> sprintf(__('Added successfully %s', 'teddybearsprompts'), get_the_title($product_id)),
				'accessoriesUrl'	=> get_the_permalink(apply_filters('teddybear/project/system/getoption', 'standard-accessory', 2115)), // (isset($custom_data['accessoriesUrl']) && !empty($custom_data['accessoriesUrl']))?esc_url($custom_data['accessoriesUrl']):get_the_permalink(2115),
				'suggestion'		=> $this->get_products_by_category_id(),
				'checkoutUrl'		=> wc_get_checkout_url(),
				'cartItemKey'		=> $cart_item_key
			];
			wp_send_json_success($json);
		} catch (\Exception $e) {
			// Handle the exception here
			$json['message'] = 'Error: ' . $e->getMessage();
			wp_send_json_error($json);
		}
	}
	public function ajax_update_cart() {
		do_action('teddybear/project/nonce/check', $_POST['_nonce']);
		$json = ['message' => __('Something went wrong. Please try again.', 'teddybearsprompts'), 'hooks' => ['popup_submitting_failed']];
		if (isset($_POST['_product']) && isset($_POST['_price']) && isset($_POST['_mode'])) {
			switch ($_POST['_mode']) {
				case 'add':
					$cart_item_key = WC()->cart->add_to_cart((int) $_POST['_product'], 1);
					$json['cartItemKey'] = $cart_item_key;
					$json['message'] = __('Accessories added to cart successfully.', 'teddybearsprompts');
					wp_send_json_success($json);
					break;
				case 'inc':
					$quantity = WC()->cart->get_quantity($cart_item_key, $new_quantity);
					$new_quantity = ($quantity + (int) $_POST['_quantity']);
					$cart_item_key = WC()->cart->set_quantity($cart_item_key, $new_quantity);
					$json['cartItemKey'] = $cart_item_key;
					$json['message'] = __('Accessories added to cart successfully.', 'teddybearsprompts');
					wp_send_json_success($json);
					break;
				case 'del':
					WC()->cart->remove_cart_item($_POST['_key']);
					$json['message'] = __('Accessories removed from cart successfully.', 'teddybearsprompts');
					wp_send_json_success($json);
					break;
				default:
					break;
			}
		}
		wp_send_json_error($json);
	}
	public function woocommerce_add_cart_item_data($cart_item_data, $product_id, $variation_id, $quantity) {
		global $teddy_Product;global $teddy_Plushies;
		if ($this->prod2AdAccessory && $this->prod2AdAccessory != $product_id) {return $cart_item_data;}
		if (count($this->dataset) <= 0) {return $cart_item_data;}
		// if (count($this->charges) <= 0) {return $cart_item_data;}
		if ($teddy_Plushies->is_accessory($product_id)) {return $cart_item_data;}
		if ($this->dataset && count($this->dataset) > 0) {
			$cart_item_data['custom_dataset'] = $this->dataset;
		}
		if ($this->charges && count($this->charges) > 0) {
			$cart_item_data['custom_makeup'] = $this->charges;
		}
		// $cart_item_data['custom_popset'] = $teddy_Product->get_post_meta($product_id, '_product_custom_popup', true);
		return $cart_item_data;
	}
	public function woocommerce_cart_calculate_fees() {
		if (is_admin() && !defined('DOING_AJAX')) {return;}
		$cart = WC()->cart;
		
		foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
			if (array_key_exists('custom_makeup', $cart_item)) {
				// $additional_cost = 0;
				// print_r($cart->get_cart());
				foreach ($cart_item['custom_makeup'] as $fee) {
					$cart->add_fee($fee['item'], ($fee['price'] * $cart_item['quantity']), true, 'standard');
					// $additional_cost += ($fee['price'] * $cart_item['quantity']);
				}
				// $cart_item['data']->set_price($cart_item['data']->get_price() + $additional_cost);
			}
		}
	}
	public function woocommerce_order_item_name($item_name, $order_item, $order) {
		$meta_data = $order_item->get_meta_data();
		
		if ($meta_data && !empty($meta_data)) {
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
		// if (isset($cart_item['_additional_charges_applied'])) {return $item_name;}
		if (isset($cart_item['custom_dataset']) && !in_array($cart_item_key, $this->showedAlready)) {
			foreach ($cart_item['custom_dataset'] as $dataRow) {
				if ($dataRow && is_array($dataRow)) {
					try {
						switch ($dataRow['type']) {
							case 'radio':
							case 'checkbox':
							case 'select':
								if (isset($dataRow['options'])) {
									foreach ($dataRow['options'] as $index => $option) {
										if (isset($option['cost'])) {
											$dataRow['type'] = $dataRow['type']??'';$dataRow['label'] = $dataRow['label']??'';
											$option['label'] = (!isste($option['label']) || empty($option['label']))?$dataRow['type']:$dataRow['label'];
											// $option['label'] = empty($option['label'])?$dataRow['type']:$dataRow['label'];
											$option['cost'] = is_string($option['cost'])?floatval($option['cost']):$option['cost'];
											// $option['product'] // means it's a product
											$item_name .= '<br><small class="additional-charges">'.esc_html(
												apply_filters('teddybear/project/system/translate/string', $option['label'], 'teddybearsprompts', $option['label'] . ' - input field')
											).': '.wc_price($option['cost']).' x '.esc_html(number_format_i18n($cart_item['quantity'], 0)).'</small>';
										}
									}
								}
								break;
							case 'outfit':
								if (isset($dataRow['groups'])) {
									foreach ($dataRow['groups'] as $gIndex => $group) {
										if (isset($group['options'])) {
											foreach ($group['options'] as $oIndex => $option) {
												if (isset($option['cost'])) {
													$dataRow['type'] = $dataRow['type']??'';$dataRow['label'] = $dataRow['label']??'';
													$option['label'] = (!isste($option['label']) || empty($option['label']))?$dataRow['type']:$dataRow['label'];
													$option['cost'] = is_string($option['cost'])?floatval($option['cost']):$option['cost'];
													$item_name .= '<br><small class="additional-charges">'.esc_html(
														apply_filters('teddybear/project/system/translate/string', $option['label'], 'teddybearsprompts', $option['label'] . ' - input field')
													).': '.wc_price($option['cost']).' x '.esc_html(number_format_i18n($cart_item['quantity'], 0)).'</small>';
												}
											}
										}
									}
								}
								break;
							default:
								$option = $dataRow;
								if (isset($option['cost'])) {
									$option['label'] = empty($option['label'])?$dataRow['type']:$dataRow['label'];
									$option['cost'] = is_string($option['cost'])?floatval($option['cost']):$option['cost'];
									$item_name .= '<br><small class="additional-charges">'.esc_html(
										apply_filters('teddybear/project/system/translate/string', $option['label'], 'teddybearsprompts', $option['label'] . ' - input field')
									).': '.wc_price($option['cost']).' x '.esc_html(number_format_i18n($cart_item['quantity'], 0)).'</small>';
								}
								break;
						}
					} catch (\Error $th) {}
				}
			}
			// $cart_item['_additional_charges_applied'] = true;
			$this->showedAlready[] = $cart_item_key;
		}
		return $item_name;
	}
	public function woocommerce_cart_item_subtotal($subtotal, $cart_item, $cart_item_key) {
		$subtotal = $this->calculate_cart_item_subtotal($cart_item, $cart_item_key, true);
		return wc_price($subtotal);
	}
	public function woocommerce_cart_item_price($price_html, $cart_item, $cart_item_key) {
		// $this->calculate_cart_item_subtotal($cart_item, $cart_item_key);
		return $price_html;
	}
	public function woocommerce_calculate_totals($cart) {
		// if (is_admin()) {return;}
		// if (!defined('DOING_AJAX')) {return;}
		// if (is_admin() && !defined('DOING_AJAX')) {return;}
		// if (did_action('woocommerce_before_calculate_totals') >= 2) {return;}

		foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
			$this->calculate_cart_item_subtotal($cart_item, $cart_item_key);
		}
	}
	public function calculate_cart_item_subtotal($cart_item, $cart_item_key, $return = false, $accessoriesOnly = false) {
		if (array_key_exists('custom_dataset', $cart_item) && !empty($cart_item['custom_dataset']) && !in_array($cart_item_key, $this->calculatedAlready)) {
			$additional_cost = 0;
			foreach ($cart_item['custom_dataset'] as $dataRow) {
				if ($dataRow && is_array($dataRow)) {
					try {
						switch ($dataRow['type']??'') {
							case 'radio':
							case 'checkbox':
							case 'select':
								if (isset($dataRow['options'])) {
									foreach ($dataRow['options'] as $index => $option) {
										if (isset($option['product']) && !empty($option['product'])) {continue;} # Pass if product
										if (isset($option['cost'])) {
											$option['cost'] = is_string($option['cost'])?floatval($option['cost']):$option['cost'];
											if ($option['cost'] && $option['cost'] > 0) {
												$additional_cost += $option['cost'];
											}
										}
									}
								}
								break;
							case 'outfit':
								if (isset($dataRow['groups'])) {
									foreach ($dataRow['groups'] as $gIndex => $group) {
										if (isset($group['options'])) {
											foreach ($group['options'] as $oIndex => $option) {
												if (isset($option['product']) && !empty($option['product'])) {continue;} # Pass if product
												if (isset($option['cost'])) {
													$option['cost'] = is_string($option['cost'])?floatval($option['cost']):$option['cost'];
													if ($option['cost'] && $option['cost'] > 0) {
														$additional_cost += $option['cost'];
													}
												}
											}
										}
									}
								}
								break;
							default:
								// if (isset($option['product']) && !empty($option['product'])) {continue;} # Pass if product
								if (isset($dataRow['cost'])) {
									$dataRow['cost'] = is_string($dataRow['cost'])?floatval($dataRow['cost']):$dataRow['cost'];
									if ($dataRow['cost'] && $dataRow['cost'] > 0) {
										$additional_cost += $dataRow['cost'];
									}
								}
								break;
						}
					} catch (\Error $th) {}
				}
			}

			if ($accessoriesOnly) {
				$calculatedPrice = ($additional_cost * $cart_item['quantity']);
			} else {
				$orginalPrice = $cart_item['data']->get_price();
				$calculatedPrice = (($orginalPrice + $additional_cost) * $cart_item['quantity']);
			}
			
			if ($additional_cost > 0 && ! $accessoriesOnly) {
				// if ($accessoriesOnly) {
				// 	$calculatedPrice -= ($orginalPrice * $cart_item['quantity']);
				// }
				$cart_item['data']->price = $calculatedPrice;
				$cart_item['data']->set_price($calculatedPrice);
			}
			// $this->calculatedAlready[] = $cart_item_key;
			if ($return) {return $calculatedPrice;}
		} else {
			// 
		}
		if ($return) {
			return ($accessoriesOnly)?0:($cart_item['data']->get_price() * $cart_item['quantity']);
		}
	}
	public function woocommerce_cart_subtotal($cart_subtotal, $compound, $cart) {		
		$newSubtotal = 0;
		foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
			$newSubtotal += $this->calculate_cart_item_subtotal($cart_item, $cart_item_key, true);
		}
		return wc_price($newSubtotal);
	}
	public function woocommerce_calculated_total($total, $cart) {		
		$accessoriesCost = 0;
		foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
			$accessoriesCost += $this->calculate_cart_item_subtotal($cart_item, $cart_item_key, true, true);
		}
		return ($total + $accessoriesCost);
	}
	public function custom_upload_audio_video($file) {
		// print_r($file);wp_die();
		$upload_dir = wp_upload_dir();$custom_dir = 'custom_popup';
		$target_dir = $upload_dir['basedir'].'/'.$custom_dir.'/';
		if (!file_exists($target_dir)) {mkdir($target_dir, 0755, true);}
		$file_name = $file['name'];$file_tmp = $file['tmp_name'];$file_type = $file['type'];
		$allowed_regex = '/^(audio|video|text)\/(.*?)/i';
		if (!preg_match($allowed_regex, $file_type)) {
			throw new \Exception(__('Error: Only audio and video files are allowed.', 'teddybearsprompts'));
		}
		$max_file_size = 400 * 1024 * 1024;
		if ($file['size'] > $max_file_size) {
			throw new \Exception(__('Error: File size exceeds the maximum limit of 400 MB.', 'teddybearsprompts'));
		}
		$target_file = $target_dir . $file_name;
		if (!move_uploaded_file($file_tmp, $target_file)) {
			throw new \Exception(__('Error uploading file.', 'teddybearsprompts'));
		}
		return $target_file;
	}
	public function custom_upload_file($file) {
		$upload_dir = wp_upload_dir();$custom_dir = 'custom_popup';
		$target_dir = $upload_dir['basedir'].'/'.$custom_dir.'/';
		if (!file_exists($target_dir)) {mkdir($target_dir, 0755, true);}
		$file_name = $file['name'];$file_tmp = $file['tmp_name'];$file_type = $file['type'];
		$target_file = $target_dir . $file_name;
		if (!move_uploaded_file($file_tmp, $target_file)) {
			throw new \Exception(__('Error uploading file.', 'teddybearsprompts'));
		}
		return $target_file;
	}
	public function get_products_by_category_id() {
		$product_term_ids = [...explode(',', apply_filters('teddybear/project/system/getoption', 'standard-category', '16,10,4,7'))];
		$product_term_args = [
			'taxonomy'	=> 'product_cat',
			'include'	=> $product_term_ids,
			'orderby'	=> 'include'
		];
		$product_terms = get_terms($product_term_args);
		$product_term_slugs = [];$results = [];
		foreach ($product_terms as $product_term) {
			$product_term_slugs[] = $product_term->slug;
		}
		$product_args = [
			'post_status' => 'publish',
			'limit' => -1,
			'category' => $product_term_slugs
		];
		$products = wc_get_products($product_args);
		foreach ($products as $product) {
			$product_data = [
				'ID'        => $product->get_id(),
				'title'     => $product->get_title(),
				'price'     => $product->get_price(),
				'priceHtml' => $product->get_price_html(),
				'thumbnail' => get_the_post_thumbnail($product->get_id(), 'thumbnail')
			];
			$results[] = $product_data;
		}
		return $results;
	}
	public function ajax_empty_cart() {
		do_action('teddybear/project/nonce/check', $_GET['_nonce']);
		if (WC()->cart->empty_cart()) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}

}
