<?php
/**
 * Theme Sidebars.
 *
 * @package TeddyBearCustomizeAddon
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
/**
 * Class Widgets.
 */
class Rewrite {
	use Singleton;
	/**
	 * Construct method.
	 */
	protected function __construct() {
		$this->setup_hooks();
	}
	/**
	 * To register action/filter.
	 *
	 * @return void
	 */
	protected function setup_hooks() {
		/**
		 * Actions
		 */
		add_action( 'init', [ $this, 'init' ], 10, 1 );
		add_filter( 'query_vars', [ $this, 'query_vars' ], 10, 1 );
		add_filter( 'template_include', [ $this, 'template_include' ], 10, 1 );
	}
  public function init() {
		// add_rewrite_rule( 'clip/([^/]*)/([^/]*)/?', 'index.php?user_profile=$matches[1]&order_id=$matches[2]', 'top' );
		add_rewrite_rule( 'pay_retainer/([^/]*)/?', 'index.php?pay_retainer=$matches[1]&redirect=true', 'top' );
		add_rewrite_rule( stripslashes( apply_filters( 'futurewordpress/project/teddybearpopupaddon/system/getoption', 'permalink-dashboard', 'dashboard' ) ) . '/([^/]*)/([^/]*)/?', 'index.php?user_profile=$matches[1]&currenttab=$matches[2]', 'top' );
		foreach( apply_filters( 'futurewordpress/project/teddybearpopupaddon/rewrite/rules', [] ) as $rule ) {
			add_rewrite_rule( $rule[ 0 ], $rule[ 1 ], $rule[ 2 ] );
		}
  }
	public function query_vars( $query_vars  ) {
		$query_vars[] = 'user_profile';
		$query_vars[] = 'currenttab';
		$query_vars[] = 'lead_registration';
		$query_vars[] = 'registration';
    return $query_vars;
	}
	public function template_include( $template ) {
    $user_profile = get_query_var( 'user_profile' );// $order_id = get_query_var( 'order_id' );
		if ( $user_profile == false || $user_profile == '' ) {
      return $template;
    } else {
			$file = TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/dashboard/index.php';
			if( file_exists( $file ) && ! is_dir( $file ) ) {
          return $file;
        } else {
          return $template;
        }
		}
	}
}
