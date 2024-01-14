<?php
/**
 * Loadmore Single Posts
 *
 * @package TeddyBearCustomizeAddon
 */

namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;

use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;

class Hooks {
	use Singleton;
	protected function __construct() {
		$this->setup_hooks();
	}
	protected function setup_hooks() {
    add_action('woocommerce_after_add_to_cart_button', [$this, 'woocommerce_after_add_to_cart_button'], 10, 0);
    // add_action('woocommerce_loop_add_to_cart_link', [$this, 'woocommerce_loop_add_to_cart_link'], 10, 3);
  }
  /**
   * woocommerce_before_add_to_cart_button | 
   * woocommerce_after_add_to_cart_button | 
   * woocommerce_after_add_to_cart_quantity | 
   * woocommerce_before_add_to_cart_quantity | 
   * woocommerce_before_add_to_cart_form | 
   * woocommerce_after_add_to_cart_form
   */
  public function woocommerce_after_add_to_cart_button() {
    global $product;global $teddy_Plushies;
    if(! apply_filters('teddybear/project/system/isactive', 'standard-enable')) {return;}
    if($teddy_Plushies->is_accessory($product->get_id())) {return;}
    $config = ['id' => $product->get_id()];
    ?>
    <button type="button" class="init_cusomizeaddtocartbtn" data-config="<?php echo esc_attr(json_encode($config)); ?>"><?php esc_html_e('Customize', 'teddybearsprompts'); ?></button>
    <?php
  }
  public function woocommerce_loop_add_to_cart_link($add_to_cart_html, $product, $args) {
    ob_start();$this->woocommerce_after_add_to_cart_button();$add_to_cart_html .= ob_get_clean();
    return $add_to_cart_html;
  }

}
