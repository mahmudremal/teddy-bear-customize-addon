<?php
/**
 * Enqueue theme assets
 *
 * @package TeddyBearCustomizeAddon
 */

namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
class Checkout {
	use Singleton;
	private $calculated;
	private $calculatedOnce;
	protected function __construct() {
		// load class.
		$this->calculatedOnce = false;
		$this->calculated = (object) ['allAddiTotal' => 0];
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		add_filter('pre_kses', [$this, 'import_fontend_js_on_checkout'], 10, 3);
		add_filter('peachpay_script_data', [$this, 'peachpay_script_data'], 10, 1);
		add_filter('peachpay_calculate_carts', [$this, 'peachpay_calculate_carts'], 10, 1);
		add_filter('peachpay_cart_page_line_item', [$this, 'peachpay_cart_page_line_item'], 10, 2);

		add_action('wp_ajax_nopriv_teddybear/project/ajax/remove/singlecartmeta', [$this, 'remove_singlecartmeta'], 10, 0);
		add_action('wp_ajax_teddybear/project/ajax/remove/singlecartmeta', [$this, 'remove_singlecartmeta'], 10, 0);
	}
	public function import_fontend_js_on_checkout($content, $allowed_html, $allowed_protocols) {
		if(strpos($content, 'var checkout_data') !== false) {
			$cart = WC()->cart;$json = [];
			// foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
			// 	$cart_item['custom_makeup'] = isset($cart_item['custom_makeup'])?$cart_item['custom_makeup']:[];
			// 	foreach($cart_item['custom_makeup'] as $key => $meta) {
			// 		$cart_item['custom_makeup'][$key] = [
			// 			...$meta,
			// 			'item'	=> $meta['item'],
			// 			'price'	=> wc_price($meta['price'])
			// 		];
			// 	}
			// 	$json[$cart_item_key] = $cart_item['custom_makeup'];
			// }
			$content .= '<script>var checkout_metadata=' . json_encode($json) . ';</script>';
			ob_start();
			?>
			<script>
			var theInterval = setInterval(function() {
				var cartLabels = document.querySelectorAll('.pp-order-summary .pp-order-summary-item .pp-cart-item-info .pp-item-label:not([data-handled])');
				if(cartLabels) {
					cartLabels.forEach(function(el, i) {
						el.dataset.handled = true;
						var cart_item_trash = ((el?.nextElementSibling)?.nextElementSibling)?.querySelector('.pp-item-remover-btn');
						var cart_item_id = (cart_item_trash?.dataset)?.qid;

						cart_item_trash.addEventListener('click', function(e) {
							var product_el = document.querySelector('.to_track_product[data-cart_item="' + cart_item_id + '"]');
							var product_name = ((product_el?.parentElement)?.previousElementSibling)?.innerText || 'a product.';
							if(product_el && !confirm('Are you sure?\nThis item is an accessory Item & if you remove this item, it\'ll remove an accessory of "' + product_name + '"')) {
								e.preventDefault();e.stopPropagation();
							}
						});
						
						var cartMetas, cartSingle = (cart_item_id && cart_item_id != '')?(checkout_data.cart_calculation_response.data.cart_calculation_record[0].cart.find(function(row) {return row.item_key == cart_item_id}) || []):[];
						// var cartMetas = checkout_metadata[cart_item_id] || [];
						// var cartMetas = Object.values(checkout_metadata)[i] || [];

						el.dataset.cartItemId = cart_item_id;
						
						if(cartSingle) {
							var span = document.createElement('span');span.classList.add('pp-item-label__meta');
							cartMetas = (cartSingle?.meta_data) || cartSingle;
							
							var currency_sign_left = (checkout_data.currency_info.position == 'left')?checkout_data.currency_info.symbol:'';
							var currency_sign_right = (checkout_data.currency_info.position == 'right')?checkout_data.currency_info.symbol:'';
							
							var trash = document.createElement('img');trash.src = document.querySelector('.pp-item-remover-img')?.src;
							trash.dataset.meta = '{}';trash.height = '15';trash.width = '15';
							setTimeout(function() {
								trash.classList.add('pp-item-remove-meta');

								// console.log(cartMetas);
								
								let subTotal = (
									(cartSingle?.price??0) * (cartSingle?.quantity??1)
								); // Calculated Subtotal.
								Object.values(cartMetas).map(function(meta) {
									var label = document.createElement('span');
									trash.dataset.meta = JSON.stringify(meta);
									label.innerHTML = meta.key + ': ' + currency_sign_left + meta.value + currency_sign_right + trash.outerHTML;
									label.classList.add('product_single_meta');
									if(meta?.cart_item_key) {
										label.classList.add('to_track_product');
										label.dataset.product = meta?.product;
										label.dataset.cart_item = meta?.cart_item_key;
										
										var target = document.querySelector('[data-cart-item-id="'+label.dataset.cart_item+'"]');
										if(target) {
											target = ((target?.parentElement)?.parentElement)?.parentElement || target;
											if(target) {target.style.display = 'none';}
											label.addEventListener('click', function (event) {
												target.classList.add('blink-animation');
												setTimeout(function() {
													target.classList.remove('blink-animation');
												}, 1000);
											});
										}
									}

									// if(!(meta?.product)) {
										subTotal += ((meta?.price??0) * (meta?.quantity??1));
									// }

									setTimeout(function() {
										document.querySelectorAll('.pp-item-remove-meta:not([data-handled])').forEach(function(trash) {
											trash.dataset.handled = true;
											trash.addEventListener('click', function(event) {
												event.preventDefault();var meta = JSON.parse(trash.dataset.meta);
												
												var conditions = confirm(
													'Are you sure you want to remove "'+(meta?.item??'this')+'" from cart?'
												);
												if(conditions) {
													meta.meta_item_cart_key = trash.parentElement.parentElement.parentElement.dataset?.cartItemId??'';
													var search = Object.keys(meta).map(function(key) {return key+'='+meta[key];}).join('&');
													fetch('<?php echo esc_url(admin_url('admin-ajax.php?action=teddybear/project/ajax/remove/singlecartmeta')); ?>&'+search, {
														method: 'POST', cache: 'no-cache', headers: {'Content-Type': 'application/json'},
														body: JSON.stringify(meta)
													})
													.then(function(response) {return response.json();})
													.then(function(json) {
														if(json?.success) {
															switch (json.data?.type) {
																case 'cart_item':
																case 'cart_meta':
																case 'cart_item+meta':
																	if(confirm((json.data?.message??'Success')+"\nWant to reload window?")) {
																		location.reload();
																	}
																	break;
																default:
																	alert(json.data?.message??'Something Error happens');
																	break;
															}
														} else {
															alert(json.data?.message??'Something Error happens');
														}
													})
													.catch(function(err) {console.error(err)});
												}
												
												console.log(meta);
											});
										});
									}, 300);
									
									// var brTag = document.createElement('br');
									span.appendChild(label);// span.appendChild(brTag);
								});
								el.appendChild(span);
								if(el?.nextElementSibling && el.nextElementSibling.querySelector('.pp-recalculate-blur')) {
									el.nextElementSibling.querySelector('.pp-recalculate-blur').innerHTML = subTotal.toFixed(2);
								}
							}, 300);
						}
					});
					// clearInterval(theInterval);
				}
			}, 1000);
			var style = document.createElement('style');style.innerHTML = `.pp-item-label__meta {display: block;font-size: 12px;line-height: 16px;}.to_track_product, .pp-item-remove-meta {cursor: pointer;}.product_single_meta {gap: 5px;display: flex;align-items: center;}
			.blink-animation {animation: blink 0.6s step-start infinite;transition: all .3s ease;}@keyframes blink {50% {opacity: 0;}}
			`;document.head.appendChild(style);
			</script>
			<script>
				setInterval(function() {
					document.querySelectorAll('#pp-checkout .pp-tc-section:not([data-handled])').forEach(function(section) {
						if(!section.querySelector('#pp-tc-privacy')) {
							section.innerHTML = '';
							var input = document.createElement('input');
							input.id = 'pp-tc-privacy';input.type = 'checkbox';input.required = true;
							var label = document.createElement('label');label.setAttribute('for', input.id);
							label.classList.add('pp-tc-contents');label.innerHTML = `By completing the checkout, you `;
							var a = document.createElement('a');a.href = '<?php echo site_url('terms-and-condition'); ?>';a.target = '_blank';a.innerHTML = 'terms and conditions';label.appendChild(a);label.innerHTML += ' and ';
							var a = document.createElement('a');a.href = '<?php echo site_url('privacy-policy'); ?>';a.target = '_blank';a.innerHTML = 'privacy policy';label.appendChild(a);label.innerHTML += '.';
							section.appendChild(input);section.appendChild(label);
							// section.dataset.handled = true;

							document.querySelectorAll('button.pay-btn.btn.peachpay-integrated-btn.btn-shadow').forEach(function(el) {el.disabled = true;});
							input.addEventListener('change', function(event) {
								if(input.checked) {
									document.querySelectorAll('button.pay-btn.btn.peachpay-integrated-btn.btn-shadow').forEach(function(el) {el.disabled = false;});
								} else {
									document.querySelectorAll('button.pay-btn.btn.peachpay-integrated-btn.btn-shadow').forEach(function(el) {el.disabled = true;});
								}
							});
						}
					});
				}, 1000);
			</script>
			<?php
			$content .= ob_get_clean();
		}
		return $content;
	}
	public function peachpay_script_data($data) {
		if(isset($data['cart_calculation_response']) && isset($data['cart_calculation_response']['data']) && isset($data['cart_calculation_response']['data']['cart_calculation_record']) && isset($data['cart_calculation_response']['data']['cart_calculation_record'][0]) && isset($data['cart_calculation_response']['data']['cart_calculation_record'][0]['summary']) && isset($data['cart_calculation_response']['data']['cart_calculation_record'][0]['summary']['subtotal'])) {
			$data['cart_calculation_response']['data']['cart_calculation_record'] = $this->peachpay_calculate_carts($data['cart_calculation_response']['data']['cart_calculation_record'], false);
			$summary = $data['cart_calculation_response']['data']['cart_calculation_record'][0]['summary'];

			// $summary['total'] = ($summary['total'] + $this->calculated->allAddiTotal);
			// $summary['subtotal'] += $this->calculatedOnce;
			$summary['calculated'] = $this->calculatedOnce;
			// $summary['total'] = ($summary['subtotal'] + $summary['total_shipping'] + $summary['total_tax']);
			// $summary['total'] = 00;
			
			$data['cart_calculation_response']['data']['cart_calculation_record'][0]['summary'] = $summary;
			$cart = $data['cart_calculation_response']['data']['cart_calculation_record'][0]['cart'];
			foreach($cart as $i => $cart_row) {
				$cart_item = WC()->cart->get_cart_item($cart_row['item_key']);
				if($cart_item && !is_wp_error($cart_item) && isset($cart_item['custom_makeup'])) {
					$subTotal = 0;
					foreach($cart_item['custom_makeup'] as $row) {
						if($row && is_array($row)) {
							$data['cart_calculation_response']['data']['cart_calculation_record'][0]['cart'][$i]['meta_data'][] = [
								...$row,
								'key'	=> $row['item'],
								'value'	=> (float) $row['price']
							];
						}
					}
				}
			}
		}
		return $data;
	}
	public function peachpay_calculate_carts($calculation_record, $not_yet = true) {
		// if($not_yet) {return $calculation_record;}
		if($this->calculatedOnce) {return $calculation_record;}
		$calculatedSubTotal = 0;$calculatedRecorded = false;$withQtyAddiTotal = 0;
		if(isset($calculation_record['data']) && isset($calculation_record['data']['cart_calculation_record'])) {
			$calculatedRecorded = $calculation_record;
			$calculation_record = $calculation_record['data']['cart_calculation_record'];
		}
		if(isset($calculation_record[0]) && isset($calculation_record[0]['cart'])) {
			foreach($calculation_record[0]['cart'] as $i => $cart_row) {
				$cart_item = WC()->cart->get_cart_item($cart_row['item_key']);

				// print_r(["cart_item \n", is_wp_error($cart_item)]);

				if($cart_item && !is_wp_error($cart_item) && isset($cart_item['custom_makeup'])) {
					$subTotal = 0;
					foreach($cart_item['custom_makeup'] as $row) {
						// $subTotal += ($row['price'] * $cart_row['quantity']);
						if($row && is_array($row)) {
							if(!isset($row['product']) || !$row['product']) {
								$withQtyAddiTotal = ($withQtyAddiTotal + ($row['price'] * $cart_row['quantity']));
								$subTotal = ($subTotal + $row['price']);
							}
						}
					}
					$cart_row['subtotal'] = (float) $cart_row['subtotal'];
					// $cart_row['subtotal'] = ($cart_row['subtotal'] + $subTotal);
					$cart_row['total'] = (float) $cart_row['total'];
					// $cart_row['total'] = ($cart_row['total'] + $subTotal);
					$cart_row['price'] = (float) $cart_row['price'];
					// $cart_row['price'] += $subTotal;
					// $cart_row['display_price'] = (float) $cart_row['display_price'];
					$cart_row['display_price'] = ($cart_row['display_price'] + $subTotal);
					$calculation_record[0]['cart'][$i] = $cart_row;
					$calculatedSubTotal += $subTotal;
				}
			}
			if(isset($calculation_record[0]['summary'])) {
				$summary = $calculation_record[0]['summary'];
				// $summary['total'] = ($summary['subtotal'] + $summary['total_shipping'] + $summary['total_tax']);
				$summary['subtotal'] += $withQtyAddiTotal;
				// $summary['total'] += $withQtyAddiTotal;
				$summary['captured'] = $withQtyAddiTotal;
				$calculation_record[0]['summary'] = $summary;
			}
			$this->calculated->allAddiTotal = $withQtyAddiTotal;
			$this->calculatedOnce = $calculatedSubTotal;
		}
		if($calculatedRecorded && isset($calculatedRecorded['data'])) {
			$calculatedRecorded['data']['cart_calculation_record'] = $calculation_record;
			return $calculatedRecorded;
		}
		return $calculation_record;
	}
	public function peachpay_cart_page_line_item($pp_cart_item, $wc_line_item) {
		return $pp_cart_item;
	}
	public function remove_singlecartmeta() {
		$json = ['type' => 'error', 'message' => __('Falied to remove cart meta.', 'teddybearsprompts')];
		if(isset($_GET['cart_item_key']) && !empty($_GET['cart_item_key'])) {
			$cart_item = WC()->cart->get_cart_item($_GET['cart_item_key']);
			if($cart_item && !is_wp_error($cart_item)) {
				$is_removed = WC()->cart->remove_cart_item($_GET['cart_item_key']);
				$json = [...$json, 'type' => 'cart_item', 'message' => __('Successfully Removed cart Item.', 'teddybearsprompts')];
			}
		}
		if(isset($_GET['meta_item_cart_key']) && !empty($_GET['meta_item_cart_key'])) {
			$cart_item = WC()->cart->get_cart_item($_GET['meta_item_cart_key']);
			if($cart_item && !is_wp_error($cart_item) && isset($cart_item['custom_makeup'])) {
				$is_updated = false;
				$_GET['key'] = str_replace(['\\'], [''], $_GET['key']);
				$_GET['item'] = str_replace(['\\'], [''], $_GET['item']);
				foreach($cart_item['custom_makeup'] as $i => $row) {
					if(isset($row['item']) && in_array($row['item'], [$_GET['item'], $_GET['key']])) {
						unset($cart_item['custom_makeup'][$i]);$is_updated = true;
					}
				}
				if($is_updated) {
					$cart = WC()->cart->cart_contents;
					foreach($cart as $cart_item_id => $cart_item_row) {
						if($cart_item_id == $_GET['meta_item_cart_key']) {
							if($cart_item) {
								WC()->cart->cart_contents[$cart_item_id] = $cart_item;
								$json = [...$json, 'type' => 'cart_item+meta', 'message' => __('Successfully Removed Cart Item with a product', 'teddybearsprompts')];
							}
						}
					}
					WC()->cart->set_session();
				}
			}
		}
		$json['body'] = $_GET;
		if($json['type'] == 'error') {
			wp_send_json_error($json);
		} else {
			wp_send_json_success($json);
		}
		
	}
}
