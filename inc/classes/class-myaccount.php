<?php
/**
 * LoadmorePosts
 *
 * @package TeddyBearCustomizeAddon
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
use \WP_Query;
class Myaccount {
	use Singleton;
	protected function __construct() {
		// load class.
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		add_filter('woocommerce_account_menu_items', [$this, 'woocommerce_account_menu_items'], 99, 2);
		add_filter('wps_wpr_allowed_user_roles_points_features', [$this, 'wps_wpr_allowed_user_roles_points_features'], 99, 1);
		
		add_filter('body_class', [$this, 'invisible_my_account_points'], 10, 1);
		add_shortcode('members-club-link', [$this, 'members_club_link']);
	}
	public function woocommerce_account_menu_items($items, $endpoints) {
		if(isset($items['points']) && ! $this->check_user_role()) {
			unset($items['points']);
		}
		return $items;
	}
	public function wps_wpr_allowed_user_roles_points_features($allow) {
		return false; // $allow;
	}
	public function user_has_role($user_id, $role_name) {
		$user_meta = get_userdata($user_id);
		if(!$user_meta || is_wp_error($user_meta)) {
			return false;
		}
		$user_roles = $user_meta->roles;
		return in_array($role_name, (array) $user_roles);
	}
	public function check_user_role() {
		if(! is_user_logged_in()) {return false;}
		$user_is_subscriber = $this->user_has_role(get_current_user_id(), 'member');
		return $user_is_subscriber;
	}
	public function invisible_my_account_points($classes) {
		
		// $endpoint = WC()->query->get_current_endpoint();

		// var_dump($endpoint);
		// wp_die(
		// 	is_wc_endpoint_url('points')?'true':'false'
		// );
		
		if(is_wc_endpoint_url('points') || $this->_is_wc_endpoint('points')) {
			$classes[] = 'myac-points';
			if($this->check_user_role()) {
				$classes[] = 'is-role-member';
			}
		}
		return $classes;
	}
	public function _is_wc_endpoint($endpoint = '') {
		if(empty($endpoint)) {return is_wc_endpoint_url();}
		global $wp;
		if(empty($wp->query_vars)) {return false;}
		$queryVars = $wp->query_vars;
		if(
			!empty($queryVars['pagename'])
			&& $queryVars['pagename'] == 'my-account'
		) {
			if(isset($queryVars[$endpoint])) {return true;}
			if($endpoint == 'dashboard') {
				$requestParts = explode('/', trim($wp->request, ' \/'));
				if(end($requestParts) == 'my-account') {return true;}
			}
		}
		return false;
	}
	public function members_club_link($args) {
		$redirect = site_url('/my-account');
		// if(function_exists('WC')) {
		// 	$redirect = get_permalink(wc_get_page_id('myaccount'));
		// }
		if($this->check_user_role()) {
			$redirect = site_url('/account');
		}
		return $redirect;
		// wp_safe_redirect($redirect);
	}
	
}