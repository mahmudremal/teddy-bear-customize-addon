<?php
/**
 * Enqueue theme assets
 *
 * @package TeddyBearCustomizeAddon
 */

namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
class Assets {
	use Singleton;
	protected function __construct() {
		// load class.
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		add_action('wp_enqueue_scripts', [$this, 'register_styles']);
		add_action('wp_enqueue_scripts', [$this, 'register_scripts']);
		
		add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts'], 10, 1);
		add_filter('teddybear/project/teddybearpopupaddon/javascript/siteconfig', [$this, 'siteConfig'], 1, 2);
		// add_filter('style_loader_src', [$this, 'style_loader_src'], 10, 2);
		// add_filter('script_loader_src', [$this, 'style_loader_src'], 10, 2);
	}
	public function register_styles() {
		// Register styles.
		$version = $this->filemtime(TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_CSS_DIR_PATH . '/public.css');
		wp_register_style('teddybearaddon-public', TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_CSS_URI . '/public.css', [], $version, 'all');
		// Enqueue Styles.
		wp_enqueue_style('teddybearaddon-public');
		// if($this->allow_enqueue()) {}
	}
	public function register_scripts() {
		// Register scripts.
		// wp_enqueue_script('tailwindcss', 'https://cdn.tailwindcss.com', [], null, true);
		wp_enqueue_script('react', 'https://unpkg.com/react@18/umd/react.development.js', [], null, true);
		wp_enqueue_script('react-dom', 'https://unpkg.com/react-dom@18/umd/react-dom.development.js', ['react'], null, true);
		wp_enqueue_script('teddybearaddon-public', TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_JS_URI . '/popup.js', ['jquery', 'react', 'react-dom'], $this->filemtime(TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_JS_DIR_PATH.'/popup.js'), true);
		
		wp_enqueue_style('teddybearaddon-tailwind', TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_URI . '/assets/tailwind.css', [], $this->filemtime(TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/assets/tailwind.css'), 'all');
		
		
		// $version = $this->filemtime(TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_JS_DIR_PATH.'/public.js');
		// wp_enqueue_script('teddybearaddon-public', TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_JS_URI . '/public.js', ['jquery'], $version.'.'.rand(0, 999), true);
		wp_localize_script('teddybearaddon-public', 'fwpSiteConfig', apply_filters('teddybear/project/teddybearpopupaddon/javascript/siteconfig', []));
	}
	private function allow_enqueue() {
		return (function_exists('is_checkout') && (is_checkout() || is_order_received_page() || is_wc_endpoint_url('order-received')));
	}
	/**
	 * Enqueue editor scripts and styles.
	 */
	public function enqueue_editor_assets() {
		$asset_config_file = sprintf('%s/assets.php', TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_PATH);
		if (! file_exists($asset_config_file)) {
			return;
		}
		$asset_config = require_once $asset_config_file;
		if (empty($asset_config['js/editor.js'])) {
			return;
		}
		$editor_asset    = $asset_config['js/editor.js'];
		$js_dependencies = (! empty($editor_asset['dependencies'])) ? $editor_asset['dependencies'] : [];
		$version         = (! empty($editor_asset['version'])) ? $editor_asset['version'] : $this->filemtime($asset_config_file);
		// Theme Gutenberg blocks JS.
		if (is_admin()) {
			wp_enqueue_script(
				'aquila-blocks-js',
				TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_JS_URI . '/blocks.js',
				$js_dependencies,
				$version,
				true
			);
		}
		// Theme Gutenberg blocks CSS.
		$css_dependencies = [
			'wp-block-library-theme',
			'wp-block-library',
		];
		wp_enqueue_style(
			'aquila-blocks-css',
			TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_CSS_URI . '/blocks.css',
			$css_dependencies,
			$this->filemtime(TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_CSS_DIR_PATH . '/blocks.css'),
			'all'
		);
	}
	public function admin_enqueue_scripts($curr_page) {
		global $post;
		if(!in_array($curr_page, ['post-new.php', 'post.php', 'edit.php', 'settings_page_teddybearsprompts'])) {return;}
		wp_register_style('teddybearaddon-admin', TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_CSS_URI . '/admin.css', [], $this->filemtime(TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_CSS_DIR_PATH . '/admin.css'), 'all');
		wp_register_script('teddybearaddon-admin', TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_JS_URI . '/admin.js', ['jquery'], $this->filemtime(TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_JS_DIR_PATH . '/admin.js'), true);
		
		// if(!in_array($curr_page, ['settings_page_teddybearsprompts'])) {}
		wp_enqueue_style('teddybearaddon-admin');
		wp_enqueue_script('teddybearaddon-admin');
		wp_enqueue_style('teddybearaddon-public');wp_enqueue_script('teddybearaddon-admin');
		wp_localize_script('teddybearaddon-admin', 'fwpSiteConfig', apply_filters('teddybear/project/teddybearpopupaddon/javascript/siteconfig', [
			'config' => [
				'product_id' => isset($_GET['post'])?(int) $_GET['post']:get_query_var('post',false)
			]
		], true));
	}
	private function filemtime($path) {
		return (file_exists($path)&&!is_dir($path))?filemtime($path):false;
	}
	public function siteConfig($args, $is_admin = false) {
		$args = wp_parse_args([
			'ajaxUrl'    		=> admin_url('admin-ajax.php'),
			'ajax_nonce' 		=> apply_filters('teddybear/project/nonce/create', false, 'ajax_nonce'),
			'is_admin' 			=> is_admin(),
			'buildPath'  		=> TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_URI,
			'audioDuration'  	=> TEDDY_BEAR_CUSTOMIZE_ADDON_AUDIO_DURATION,
			'siteLogo'			=> apply_filters('teddybear/project/system/getoption', 'standard-sitelogo', false),
			'i18n'					=> [
				'pls_wait'			=> __('Please wait...', 'teddybearsprompts'),
			],
			'currencySign'		=> get_woocommerce_currency_symbol(),
			'local'				=> apply_filters('teddybear/project/system/get_locale', get_user_locale()),
			'tooltip'			=> apply_filters('teddybear/project/system/isactive', 'addons-tooltip', false),
			'print_request'		=> apply_filters('teddybear/project/system/isactive', 'addons-printcert', false),
			'onatc'				=> apply_filters('teddybear/project/system/isactive', 'standard-add2cart', false),
		], (array) $args);
		
		if ($is_admin) {
			// admin scripts here
		} else {
			// public scripts here.
			$args['notifications'] = apply_filters('teddybear/project/assets/notifications', false, []);
		}
		
		return $args;
	}
	public function wp_denqueue_scripts() {}
	public function admin_denqueue_scripts() {
		if(! isset($_GET['page']) ||  $_GET['page'] !='crm_dashboard') {return;}
		wp_dequeue_script('qode-tax-js');
	}
	public function style_loader_src($src, $handle) {
		if ($handle === 'teddybearaddon-public') {
			$version = $this->filemtime(str_replace(site_url('/'),ABSPATH,$src));
			// $src = add_query_arg('ver', $version, $src);
			$src = $src.'v'.$version;
		}
		return $src;
	}
}
