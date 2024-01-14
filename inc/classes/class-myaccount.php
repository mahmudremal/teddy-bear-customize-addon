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
	private $endPoints;
	protected function __construct() {
		// load class.
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		add_filter('woocommerce_account_menu_items', [$this, 'woocommerce_account_menu_items'], 99, 2);
		add_filter('wps_wpr_allowed_user_roles_points_features', [$this, 'wps_wpr_allowed_user_roles_points_features'], 99, 1);
		
		add_filter('body_class', [$this, 'invisible_my_account_points'], 10, 1);
		add_shortcode('members-club-link', [$this, 'members_club_link']);

		add_action('init', [$this, 'init'], 10, 0);

		add_action('admin_post_woocommerce-remove-account', [$this, 'woocommerce_remove_account'], 10, 0);

		add_action('init', [$this, 'init_memberpress'], 10, 0);

		add_action('mepr_account_nav', [$this, 'mepr_account_nav'], 10, 1);
		add_action('mepr_account_nav_content', [$this, 'mepr_account_nav_content'], 10, 1);

		add_action('woocommerce_order_item_meta_end', [$this, 'woocommerce_order_item_add_certificate_link'], 10, 4);

	}
	/**
	 * Fired on init hook for registering endpoints
	 */
	public function init() {
		$this->endPoints = ['membersclub' => __('Members Club', 'domain')];
		foreach ($this->endPoints as $key => $row) {
			add_rewrite_endpoint($key, EP_PAGES);
			add_action('woocommerce_account_' . $key . '_endpoint', [$this, 'woocommerce_account_' . str_replace(['-'], ['_'], $key) . '_endpoint'], 10, 0);
		}
		
		add_action('woocommerce_account_edit-account_endpoint', [$this, 'woocommerce_account_edit_account_endpoint'], 10, 0);
	}
	public function init_memberpress() {
		if(!class_exists('MeprHooks')) {return;}
		/**
		 * Adding into memberpress hook.
		 */
		
	}
	/**
	 * Adding Custom menus on my-account navigation.
	 */
	public function woocommerce_account_menu_items($items, $endpoints) {
		if(isset($items['points']) && ! $this->check_user_role()) {
			unset($items['points']);
		}

		/**
		 * Add custom menu for Switching to membership screen.
		 */
		$items = array_slice($items, 0, 5, true) 
		+ $this->endPoints
		+ array_slice($items, 5, NULL, true);
		
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
		$is_roled = $this->user_has_role(get_current_user_id(), 'member');
		// if(! $is_roled) {$is_roled = $this->user_has_role(get_current_user_id(), 'administrator');}
		return $is_roled;
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
		$redirect = site_url('/membership');
		// $redirect = site_url('/my-account');
		// if(function_exists('WC')) {
		// 	$redirect = get_permalink(wc_get_page_id('myaccount'));
		// }
		if($this->check_user_role()) {
			$redirect = site_url('/account');
		}
		return $redirect;
		// wp_safe_redirect($redirect);
	}



	public function woocommerce_account_edit_account_endpoint() {
		?>
		<form class="woocommerce-removeAccountForm delete-account" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
			<p>
				<?php wp_nonce_field('woocommerce-remove-account', '_nonce', true, true); ?>
				<input type="hidden" name="action" value="woocommerce-remove-account">
				<button type="submit" class="woocommerce-delete-account button" name="delete_account_details" value="<?php esc_attr_e('Delete Account', 'domain'); ?>"><?php esc_html_e('Delete Account', 'domain'); ?></button>
				<input type="hidden" name="remove-account" value="unconfirmed">
				<span style="display: block;"><?php esc_html_e('Deleting account by confirming will permanently erase all of your information form database. Proceed with caution.', 'domain'); ?></span>
			</p>
		</form>
		<style>form.woocommerce-removeAccountForm.delete-account {margin: auto;margin-top: 50px;border: 1px solid #ddd;padding: 10px;}</style>
		<?php
	}
	public function woocommerce_account_membersclub_endpoint() {
		?>
		<script>location.replace('<?php echo esc_url(site_url('/account/')); ?>');</script>
		<?php
	}

	/**
	 * Remove an user from WordPress database that won't be undone or recovered.
	 */
	public function woocommerce_remove_account() {
		/**
		 * Verify nonce request authentication and validity.
		 */
		if(!isset($_POST['_nonce']) || !wp_verify_nonce($_POST['_nonce'], 'woocommerce-remove-account')) {wp_die(__('Suspecious request identified!', 'domain'), __('Fatal Failed!', 'domain'));}

		/**
		 * Start removing all informations.
		 * This will remove user from user table, user metas, posts by that user of all post types.
		 */
		$current_user_id = get_current_user_id();
		$operation_done = wp_delete_user($current_user_id);
		if ($operation_done) {
			/**
			 * Return to the edit account screen.
			 */
			$noti = apply_filters('teddybear/project/add/notify', 'user_removed', ['message' => __('User has been removed successfully.', 'domain'), 'user' => $current_user_id], false);
			wp_safe_redirect(wp_get_referer());
		} else {
			wp_die(__('Something error happens while trying to remove your account.', 'domain'), __('Failed!', 'domain'));
		}

		/**
		 * This can be done by this way.
		 */
		// require_once( ABSPATH.'wp-admin/includes/user.php' );
		// $current_user = wp_get_current_user();
		// wp_delete_user( $current_user->ID );
		
	}

	/**
	 * Memberpress add custom nav menu.
	 */
	public function mepr_account_nav($user) {
		if(!class_exists('MeprOptions')) {return;}
		 $support_active = (isset($_GET['action']) && $_GET['action'] == 'wcmyaccount')?'mepr-active-nav-tab':'';

		 $mepr_options = \MeprOptions::fetch();
		 $profile_url = $mepr_options->account_page_url() . '?action=wcmyaccount';
		?>
		<span class="mepr-nav-item mepr_bbpress_myaccount <?php echo esc_attr($support_active); ?>">
			<a href="<?php echo esc_url($profile_url); ?>" id="mepr-account-bbpress-myaccount"><?php esc_html_e('My Account', 'domain'); ?></a>
		</span>
		<?php
	}
	public function mepr_account_nav_content($action) {
		if($action == 'wcmyaccount') {
			?>
			<script>location.replace('<?php echo esc_url(site_url('/my-account/')); ?>');</script>
			<?php
		}
	}

	public function woocommerce_order_item_add_certificate_link($item_id, $order_item, $order, $plain_text) {
		global $teddy_Certificate;
		if (!apply_filters('teddybear/project/system/isactive', 'certificate-enable')) {return;}
		/**
		 * Check whether it it frontend or not.
		 */
		if (is_admin()) {return;}
		if (function_exists('is_wc_endpoint_url') && !is_wc_endpoint_url('view-order')) {return;}
		if (!in_array($order->get_status(), explode(',', str_replace(' ', '', apply_filters('teddybear/project/system/getoption', 'certificate-onstatuses', 'completed, shipped'))))) {return;}
		if (!apply_filters('teddybear/project/system/isactive', 'certificate-myacc-enable')) {return;}
		if (!$teddy_Certificate->get_single_certificates($order, $order_item)) {return;}
		$order_id = $order->get_id();
		?>
		<a href="<?php echo esc_url(site_url('/?certificate_preview=' . $order_id . '-' . $item_id)); ?>" class="button btn" target="_blank"><?php esc_html_e('View certificate', 'teddybearsprompts'); ?></a>
		<?php
	}
	
}