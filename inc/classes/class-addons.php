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
	}
	public function tm_epo_fields($field_types) {
		require_once(untrailingslashit(TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH).'/inc/widgets/elementor/widget-voice-upload.php');
		$field_types['voice_upload'] = '\TEDDYBEAR_CUSTOMIZE_ADDON\inc\Widget\VOICE_UPLOAD';
		wp_die('I got it');
		return $field_types;
	}
}