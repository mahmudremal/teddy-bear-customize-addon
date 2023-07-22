<?php
/**
 * WP E-Signature integration plugin.
 *
 * @package TeddyBearCustomizeAddon
 */

namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;

use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;

class Log {

	use Singleton;
	private $prefix;

	protected function __construct() {

		// load class.
		$this->setup_hooks();
	}

	protected function setup_hooks() {
		$this->prefix = 'esig';
		// add_action( 'init', [ $this, 'wp_init' ], 10, 0 );
	}
	public function wp_init() {}

}
