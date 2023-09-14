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
		add_action('woocommerce_review_order_before_payment', [$this, 'woocommerce_review_order_before_payment'], 10, 0);

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
		if(isset($_POST['_quantity']) && isset($_POST['_mode'])) {
			switch($_POST['_mode']) {
				case 'add':
					$json['message'] = __('Wrapping added successfully!', 'teddybearsprompts');
					$json['hooks'] = ['wrapping_adding_success'];
					WC()->session->set('added_wrapping', 'yes');
					wp_send_json_success($json);
					break;
				case 'del':
					$json['message'] = __('Wrapping removed successfully!', 'teddybearsprompts');
					$json['hooks'] = ['wrapping_adding_success'];
					WC()->session->set('added_wrapping', false);
					wp_send_json_error($json);
					break;
				default:
					break;
			}
		}
		wp_send_json_error($json);
	}
	public function woocommerce_cart_calculate_fees() {
		if(WC()->session->get('added_wrapping') != 'yes') {return;}
		WC()->cart->add_fee(
			apply_filters('teddybear/project/system/getoption', 'addons-feetitle', 'wrapping box'), 
			floatval(apply_filters('teddybear/project/system/getoption', 'addons-feeamount', 0))
		);
	}
}