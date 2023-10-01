<?php
/**
 * LoadmorePosts
 *
 * @package TeddyBearCustomizeAddon
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
use \WP_Query;
class Addons {
	use Singleton;
	protected function __construct() {
		// load class.
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		add_filter('tm_epo_fields', [$this, 'tm_epo_fields'], 10, 1);
		// add_action('woocommerce_review_order_before_payment', [$this, 'woocommerce_review_order_before_payment'], 10, 0);

		add_action('wp_ajax_nopriv_futurewordpress/project/ajax/add/wrapping', [$this, 'add_wrapping'], 10, 0);
		add_action('wp_ajax_futurewordpress/project/ajax/add/wrapping', [$this, 'add_wrapping'], 10, 0);

		add_action('woocommerce_cart_calculate_fees', [$this, 'woocommerce_cart_calculate_fees'], 10, 0);
		
	}
	public function tm_epo_fields($field_types) {
		require_once(untrailingslashit(TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH).'/inc/widgets/elementor/widget-voice-upload.php');
		$field_types['voice_upload'] = '\TEDDYBEAR_CUSTOMIZE_ADDON\inc\Widget\VOICE_UPLOAD';
		return $field_types;
	}
	public function woocommerce_review_order_before_payment() {
		if(!apply_filters('teddybear/project/system/isactive', 'addons-enable')) {return;}
		$thumbnailURL = apply_filters('teddybear/project/system/getoption', 'addons-thumbnail', '');
		if(empty(trim($thumbnailURL))) {$thumbnailURL = get_site_icon_url(512, '', 0);}
		?>
		<div class="wc_add_wrapping">
			<h3 class="h3">For the ultimate surprise include:</h3>
			<div class="wc_add_wrapping__row">
				<div class="wc_add_wrapping__left">
					<img src="<?php echo esc_url($thumbnailURL); ?>" alt="">
				</div>
				<div class="wc_add_wrapping__right">
					<h4 class="h4"><?php echo esc_html(apply_filters('teddybear/project/system/getoption', 'addons-subtitle', '')); ?></h4>
					<?php wc_price(3.00); ?>
					<p class="text-muted"><?php echo esc_html(apply_filters('teddybear/project/system/getoption', 'addons-text', '')); ?></p>
					<button class="btn button btn-rounded" type="button" data-mode="<?php echo esc_attr(
						(WC()->session->get('added_wrapping') == 'yes')?'del':'add'
					); ?>">
						<i class="fa fa-<?php echo esc_attr(
							(WC()->session->get('added_wrapping') == 'yes')?'minus':'plus'
						); ?>"></i>
						<?php echo esc_html(
							(WC()->session->get('added_wrapping') == 'yes')?__('Remove', 'teddybearsprompts'):__('Add', 'teddybearsprompts')
						); ?>
					</button>
				</div>
			</div>
		</div>
		<?php
	}
	public function add_wrapping() {
		$json = ['message' => __('Something went wrong', 'teddybearsprompts'), 'hooks' => ['wrapping_adding_failed']];
		if(!apply_filters('teddybear/project/system/isactive', 'addons-enable')) {
			wp_send_json_error($json);
		}
		if(isset($_POST['_quantity']) && isset($_POST['_mode'])) {
			switch($_POST['_mode']) {
				case 'add':
					if(isset($_POST['cartitemkey']) && !empty($_POST['cartitemkey'])) {
						$cart = WC()->cart;$cart_item_key = $_POST['cartitemkey'];
						$cart_contents = $cart->get_cart();
						if(isset($cart_contents[$cart_item_key])) {
							// Add additional data to the cart item
							$cart_item = $cart_contents[$cart_item_key];
							$cart_item['custom_teddey_bear_makeup'] = isset($cart_item['custom_teddey_bear_makeup'])?(array) $cart_item['custom_teddey_bear_makeup']:[];
							foreach($cart_contents[$cart_item_key]['custom_teddey_bear_makeup'] as $i => $row) {
								if($row['item'] == apply_filters('teddybear/project/system/getoption', 'addons-feetitle', 'Wrapping box')) {
									unset($cart_contents[$cart_item_key]['custom_teddey_bear_makeup'][$i]);
								}
							}
							$cart_contents[$cart_item_key]['custom_teddey_bear_makeup'][] = [
								'item' => apply_filters('teddybear/project/system/getoption', 'addons-feetitle', 'Wrapping box'),
								'price' => apply_filters('teddybear/project/system/getoption', 'addons-feeamount', 0.00)
							];

							// Update the cart with the modified item
							$cart->set_cart_contents($cart_contents);

							// Optionally, you can also update the cart totals
							$cart->calculate_totals();
						} else {
							wp_send_json_error($json);
						}
					} else {
						WC()->session->set('added_wrapping', 'yes');
					}
					$json['message'] = __('Wrapping added successfully!', 'teddybearsprompts');
					$json['hooks'] = ['wrapping_adding_success'];
					wp_send_json_success($json);
					break;
				case 'del':
					if(isset($_POST['cartitemkey']) && !empty($_POST['cartitemkey'])) {
						$cart = WC()->cart;$cart_item_key = $_POST['cartitemkey'];
						$cart_contents = $cart->get_cart();
						if(isset($cart_contents[$cart_item_key])) {
							// Add additional data to the cart item
							$cart_item = $cart_contents[$cart_item_key];
							$cart_item['custom_teddey_bear_makeup'] = isset($cart_item['custom_teddey_bear_makeup'])?(array) $cart_item['custom_teddey_bear_makeup']:[];
							foreach($cart_item['custom_teddey_bear_makeup'] as $i => $fee) {
								if(isset($fee['item']) && $fee['item'] == apply_filters('teddybear/project/system/getoption', 'addons-feetitle', 'Wrapping box')) {
									unset($cart_item['custom_teddey_bear_makeup'][$i]);
									$cart_contents[$cart_item_key] = $cart_item;
									// Update the cart with the modified item
									$cart->set_cart_contents($cart_contents);
									// Optionally, you can also update the cart totals
									$cart->calculate_totals();
								}
							}
						} else {
							wp_send_json_error($json);
						}
					} else {
						WC()->session->set('added_wrapping', false);
					}
					$json['message'] = __('Wrapping removed successfully!', 'teddybearsprompts');
					$json['hooks'] = ['wrapping_removing_success'];
					wp_send_json_error($json);
					break;
				default:
					break;
			}
		}
		
	}
	public function woocommerce_cart_calculate_fees() {
		if(WC()->session->get('added_wrapping') != 'yes') {return;}
		WC()->cart->add_fee(
			apply_filters('teddybear/project/system/getoption', 'addons-feetitle', 'wrapping box'), 
			floatval(apply_filters('teddybear/project/system/getoption', 'addons-feeamount', 0))
		);
	}
}