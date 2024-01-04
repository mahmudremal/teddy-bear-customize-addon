<?php
/**
 * LoadmorePosts
 *
 * @package TeddyBearCustomizeAddon
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
class Price {
	use Singleton;
	protected function __construct() {
		// load class.
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		// add_filter('cr_price', [$this, 'cr_price'], 10, 4);
	}
	/**
	 * Customize to ensure customer review prices.
	 * @return string
	 */
	public function cr_price($return, $price, $args, $unformatted_price) {
		return $return;
	}
}
