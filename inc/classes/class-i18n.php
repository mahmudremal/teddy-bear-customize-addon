<?php
/**
 * Blocks
 *
 * @package TeddyBearCustomizeAddon
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
class I18n {
	use Singleton;
	protected function __construct() {
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		add_action('wp_ajax_nopriv_futurewordpress/project/ajax/i18n/js', [$this, 'js_translates'], 10, 0);
		add_action('wp_ajax_futurewordpress/project/ajax/i18n/js', [$this, 'js_translates'], 10, 0);
	}
	public function js_translates() {
		$translates = [
			// backend JS
			'continue' => __('Continue', 'teddybearsprompts'),
			'back' => __('Back', 'teddybearsprompts'),
			'selectatype' => __('Select a type', 'teddybearsprompts'),
			'proceed' => __('Proceed', 'teddybearsprompts'),
			'popup_subheading_text' => __('PopUp Sub-heading text', 'teddybearsprompts'),
			'popup_subheading' => __('PopUp Sub Heading', 'teddybearsprompts'),
			'select_image' => __('Select image', 'teddybearsprompts'),
			'select_image_desc' => __('Select an image for popup header. It should be less weight, vertical and optimized.', 'teddybearsprompts'),
			'popup_heading_text' => __('PopUp Heading text', 'teddybearsprompts'),
			'popup_heading' => __('PopUp Heading', 'teddybearsprompts'),
			'required' => __('Required', 'teddybearsprompts'),
			'placeholder_text' => __('Placeholder text', 'teddybearsprompts'),
			'placeholder_ordefault' => __('Additional cost', 'teddybearsprompts'),
			'input_label' => __('Input label', 'teddybearsprompts'),
			'add_new_group' => __('Add new group', 'teddybearsprompts'),
			'add_new_option' => __('Add new option', 'teddybearsprompts'),
			'teddy_name' => __('Teddy name', 'teddybearsprompts'),
			'teddy_birth' => __('Teddy birth', 'teddybearsprompts'),
			'teddy_sender' => __('Sender\'s Name', 'teddybearsprompts'),
			'teddy_reciever' => __('Reciever\'s Name', 'teddybearsprompts'),
			'remove' => __('Remove', 'teddybearsprompts'),
			'select_thumbnail' => __('Select thumbnail', 'teddybearsprompts'),
			'field_type' => __('Field type', 'teddybearsprompts'),
			'row_title' => __('Row title', 'teddybearsprompts'),
			'layer_order' => __('Layer Order', 'teddybearsprompts'),

			// frontend JS
			'somethingwentwrong' => __('Something went wrong!', 'teddybearsprompts'),
			'back' => __('Back', 'teddybearsprompts'),
			'checkout' => __('Checkout', 'teddybearsprompts'),
			'continue' => __('Continue', 'teddybearsprompts'),
			'record' => __('Record', 'teddybearsprompts'),
			'stop' => __('Stop', 'teddybearsprompts'),
			'remove' => __('Remove', 'teddybearsprompts'),
			'play' => __('play', 'teddybearsprompts'),
			'download' => __('Download', 'teddybearsprompts'),
			'pause' => __('Pause', 'teddybearsprompts'),
		];
		wp_send_json_success([
			'hooks'			=> ['ajaxi18nloaded'],
			'translates'	=> $translates
		]);
	}

}
