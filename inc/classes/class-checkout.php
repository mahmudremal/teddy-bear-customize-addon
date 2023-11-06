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
	private $calculatedOnce;
	protected function __construct() {
		// load class.
		$this->calculatedOnce = false;
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		add_filter('pre_kses', [$this, 'import_fontend_js_on_checkout'], 10, 3);
		add_filter('peachpay_script_data', [$this, 'peachpay_script_data'], 10, 1);
		add_filter('peachpay_calculate_carts', [$this, 'peachpay_calculate_carts'], 10, 1);
		add_filter('peachpay_cart_page_line_item', [$this, 'peachpay_cart_page_line_item'], 10, 2);
	}
	public function import_fontend_js_on_checkout($content, $allowed_html, $allowed_protocols) {
		if(strpos($content, 'var checkout_data') !== false) {
			$cart = WC()->cart;$json = [];
			foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
				$cart_item['custom_teddey_bear_makeup'] = isset($cart_item['custom_teddey_bear_makeup'])?$cart_item['custom_teddey_bear_makeup']:[];
				foreach($cart_item['custom_teddey_bear_makeup'] as $key => $meta) {
					$cart_item['custom_teddey_bear_makeup'][$key] = [
						'item'	=> $meta['item'],
						'price'	=> wc_price($meta['price'])
					];
				}
				$json[$cart_item_key] = $cart_item['custom_teddey_bear_makeup'];
			}
			$content .= '<script>var checkout_metadata=' . json_encode($json) . ';</script>';
			ob_start();
			?>
			<script>
			var theInterval = setInterval(function() {
				var cartLabels = document.querySelectorAll('.pp-order-summary .pp-order-summary-item .pp-cart-item-info .pp-item-label:not([data-handled])');
				if(cartLabels) {
					cartLabels.forEach(function(el, i) {
						el.dataset.handled = true;
						var cartMetas = Object.values(checkout_metadata)[i] || [];
						if(cartMetas) {
							var span = document.createElement('span');span.classList.add('pp-item-label__meta');
							Object.values(cartMetas).map(function(meta) {
								var label = document.createElement('span');
								label.innerHTML = meta.item + ': ' + meta.price;
								var brTag = document.createElement('br');
								span.appendChild(label);span.appendChild(brTag);
							});
							el.appendChild(span);
						}
					});
					// clearInterval(theInterval);
				}
			}, 1000);
			var style = document.createElement('style');style.innerHTML = `.pp-item-label__meta {display: block;font-size: 12px;line-height: 16px;}`;document.head.appendChild(style);
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

			// $summary['total'] += $this->calculatedOnce;
			// $summary['subtotal'] += $this->calculatedOnce;
			$summary['calculated'] = $this->calculatedOnce;
			$summary['total'] = ($summary['subtotal'] + $summary['total_shipping'] + $summary['total_tax']);
			// $summary['total'] = 00;
			
			$data['cart_calculation_response']['data']['cart_calculation_record'][0]['summary'] = $summary;
			$cart = $data['cart_calculation_response']['data']['cart_calculation_record'][0]['cart'];
			foreach($cart as $i => $cart_row) {
				$cart_item = WC()->cart->get_cart_item($cart_row['item_key']);
				if($cart_item && !is_wp_error($cart_item) && isset($cart_item['custom_teddey_bear_makeup'])) {
					$subTotal = 0;
					// foreach($cart_item['custom_teddey_bear_makeup'] as $row) {
					// 	$data['cart_calculation_response']['data']['cart_calculation_record'][0]['cart'][$i]['meta_data'][] = [
					// 		'key'	=> $row['item'],
					// 		'value'	=> (float) $row['price']
					// 	];
					// }
				}
			}
		}
		return $data;
	}
	public function peachpay_calculate_carts($calculation_record, $not_yet = true) {
		// if($not_yet) {return $calculation_record;}
		if($this->calculatedOnce) {return $calculation_record;}
		$calculatedSubTotal = 0;$calculatedRecorded = false;
		if(isset($calculation_record['data']) && isset($calculation_record['data']['cart_calculation_record'])) {
			$calculatedRecorded = $calculation_record;
			$calculation_record = $calculation_record['data']['cart_calculation_record'];
		}
		if(isset($calculation_record[0]) && isset($calculation_record[0]['cart'])) {
			foreach($calculation_record[0]['cart'] as $i => $cart_row) {
				$cart_item = WC()->cart->get_cart_item($cart_row['item_key']);
				if($cart_item && !is_wp_error($cart_item) && isset($cart_item['custom_teddey_bear_makeup'])) {
					$subTotal = 0;
					foreach($cart_item['custom_teddey_bear_makeup'] as $row) {
						// $subTotal += ($row['price'] * $cart_row['quantity']);
						$subTotal = ($subTotal + ($row['price'] * $cart_row['quantity']));
					}
					$cart_row['subtotal'] = (float) $cart_row['subtotal'];
					// $cart_row['subtotal'] = ($cart_row['subtotal'] + $subTotal);
					$cart_row['total'] = (float) $cart_row['total'];
					// $cart_row['total'] = ($cart_row['total'] + $subTotal);
					$cart_row['price'] = (float) $cart_row['price'];
					// $cart_row['price'] += $subTotal;
					$cart_row['display_price'] = (float) $cart_row['display_price'];
					// $cart_row['display_price'] = ($cart_row['display_price'] + $subTotal);
					$calculation_record[0]['cart'][$i] = $cart_row;
					$calculatedSubTotal += $subTotal;
				}
			}
			if(isset($calculation_record[0]['summary'])) {
				$summary = $calculation_record[0]['summary'];
				$summary['total'] = ($summary['subtotal'] + $summary['total_shipping'] + $summary['total_tax']);
				$summary['captured'] = true;
				$calculation_record[0]['summary'] = $summary;
			}
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
}
