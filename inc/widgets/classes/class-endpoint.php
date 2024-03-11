<?php
/**
 * LoadmorePosts
 *
 * @package TeddyBearCustomizeAddon
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
class Endpoint {
	use Singleton;
	protected function __construct() {
		// load class.
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		/**
		 * Actions
		 */
		add_action('init', [$this, 'init_rewrite'], 10, 0);
		add_filter('query_vars', [$this, 'query_vars'], 10, 1);
		add_filter('template_include', [$this, 'template_include'], 10, 1);
	} 
	public function init_rewrite() {
		add_rewrite_rule('certificates/([^/]*)/([^/]*)/?', 'index.php?certificate_preview=1&certificate_order_id=$matches[1]&order_item_id=$matches[2]', 'top');

		// $result = get_option('_transient_string-locator-search-overview' );
		// print_r($result);
		// $result->search = 'PayPal נבחרה לקופה';
		// print_r($result);
		// update_option('_transient_string-locator-search-overview', $result);
		// wp_die();
  	}
	public function query_vars($query_vars ) {
		$query_vars[] = 'order_item_id';
		$query_vars[] = 'certificate_preview';
		$query_vars[] = 'certificate_order_id';
    	return $query_vars;
	}
	public function template_include($template) {
		$certificate_preview = get_query_var('certificate_preview');// $order_id = get_query_var('order_id');
		// wp_die();
		if (
			!empty(get_query_var('order_item_id')) && !empty(get_query_var('certificate_preview')) && !empty(get_query_var('certificate_order_id'))
		) {
			$template = TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/certificate/template.php';
		}

		return $template;
	}
}