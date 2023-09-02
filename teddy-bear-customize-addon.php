<?php
/**
 * This plugin ordered by a client and done by Remal Mahmud (fiverr.com/mahmud_remal). Authority dedicated to that cient.
 *
 * @wordpress-plugin
 * Plugin Name:       Teddy Bear Customization
 * Plugin URI:        https://github.com/mahmudremal/quiz-and-filter-search-extension/
 * Description:       Customize teddy bear's apearence before making order.
 * Version:           1.0.2
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Remal Mahmud
 * Author URI:        https://github.com/mahmudremal/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       teddybearsprompts
 * Domain Path:       /languages
 * 
 * @package QuizAndFilterSearch
 * @author  Remal Mahmud (https://github.com/mahmudremal)
 * @version 1.0.2
 * @link https://github.com/mahmudremal/quiz-and-filter-search-extension
 * @category	WooComerce Plugin
 * @copyright	Copyright (c) 2023-25
 * 
 */

/**
 * Bootstrap the plugin.
 */



defined('TEDDY_BEAR_CUSTOMIZE_ADDON__FILE__') || define('TEDDY_BEAR_CUSTOMIZE_ADDON__FILE__', untrailingslashit(__FILE__));
defined('TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH') || define('TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH', untrailingslashit(plugin_dir_path(TEDDY_BEAR_CUSTOMIZE_ADDON__FILE__)));
defined('TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_URI') || define('TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_URI', untrailingslashit(plugin_dir_url(TEDDY_BEAR_CUSTOMIZE_ADDON__FILE__)));
defined('TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_URI') || define('TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_URI', untrailingslashit(TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_URI ) . '/assets/build');
defined('TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_PATH') || define('TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_PATH', untrailingslashit(TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH ) . '/assets/build');
defined('TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_JS_URI') || define('TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_JS_URI', untrailingslashit(TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_URI ) . '/assets/build/js');
defined('TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_JS_DIR_PATH') || define('TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_JS_DIR_PATH', untrailingslashit(TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH ) . '/assets/build/js');
defined('TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_IMG_URI') || define('TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_IMG_URI', untrailingslashit(TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_URI ) . '/assets/build/src/img');
defined('TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_CSS_URI') || define('TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_CSS_URI', untrailingslashit(TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_URI ) . '/assets/build/css');
defined('TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_CSS_DIR_PATH') || define('TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_CSS_DIR_PATH', untrailingslashit(TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH ) . '/assets/build/css');
defined('TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_LIB_URI') || define('TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_LIB_URI', untrailingslashit(TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_URI ) . '/assets/build/library');
defined('TEDDY_BEAR_CUSTOMIZE_ADDON_ARCHIVE_POST_PER_PAGE') || define('TEDDY_BEAR_CUSTOMIZE_ADDON_ARCHIVE_POST_PER_PAGE', 9);
defined('TEDDY_BEAR_CUSTOMIZE_ADDON_SEARCH_RESULTS_POST_PER_PAGE') || define('TEDDY_BEAR_CUSTOMIZE_ADDON_SEARCH_RESULTS_POST_PER_PAGE', 9);
defined('TEDDY_BEAR_CUSTOMIZE_ADDON_OPTIONS') || define('TEDDY_BEAR_CUSTOMIZE_ADDON_OPTIONS', get_option('teddybearsprompts'));
defined('TEDDY_BEAR_CUSTOMIZE_ADDON_UPLOAD_DIR') || define('TEDDY_BEAR_CUSTOMIZE_ADDON_UPLOAD_DIR', wp_upload_dir()['basedir'].'/custom_popup/');
defined('TEDDY_BEAR_CUSTOMIZE_ADDON_AUDIO_DURATION') || define('TEDDY_BEAR_CUSTOMIZE_ADDON_AUDIO_DURATION', 20);

require_once TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/inc/helpers/autoloader.php';
// require_once TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/inc/helpers/template-tags.php';

if( ! function_exists( 'teddybearcustomizeaddon_get_theme_instance' ) ) {
	function teddybearcustomizeaddon_get_theme_instance() {\TEDDYBEAR_CUSTOMIZE_ADDON\inc\Project::get_instance();}
	teddybearcustomizeaddon_get_theme_instance();
}




