<?php
/**
 * Theme Sidebars.
 *
 * @package TeddyBearCustomizeAddon
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
/**
 * Class Shortcode.
 */
class Order {
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
		add_shortcode( 'checkout_video', [ $this, 'checkout_video' ] );
		add_action('add_meta_boxes',[$this, 'add_custom_meta_box']);
	}
	public function add_custom_meta_box() {
		$screens = ['shop_order'];
		foreach($screens as $screen) {
			add_meta_box(
				'teddybear_meta_data',           				// Unique ID
				__( 'Appearances', 'teddybearsprompts' ),  // Box title
				[ $this, 'custom_meta_box_html' ],  		// Content callback, must be of type callable
				$screen,                   							// Post type
				'side'                   								// context
			);
		}
	}
	public function custom_meta_box_html($post) {
		$order_id = $post->ID;
		$meta_data = get_post_meta($order_id, 'custom_teddey_bear_makeup', true);
		$order = wc_get_order($order_id);
		?>
		<div class="fwp-outfit__container">
			<div class="fwp-outfit__header">
				<span class="fwp-outfit__title"><?php esc_html_e('Customized order', 'teddybearsprompts'); ?></span>
			</div>
			<div class="fwp-outfit__body">
				<pre><?php print_r($meta_data); ?></pre>
				
				<?php
					foreach($order->get_items() as $order_item_id => $order_item) {
						$item_name = $order_item->get_name();
						$item_meta_data = $order_item->get_meta_data();
						if(!empty($item_meta_data)) {
							?>
							<span class="fwp-outfit__product"><?php echo esc_html(sprintf('Item: %s', $item_name)); ?></span>
							<ul class="fwp-outfit__list">
							<?php
							foreach ($item_meta_data as $meta) {
								?>
								<li class="fwp-outfit__items">
									<img src="" alt="" class="fwp-outfit__image">
									<span class="fwp-outfit__title"><?php echo esc_html($meta->key); ?></span>
									<span class="fwp-outfit__price"><?php echo esc_html($meta->value); ?></span>
								</li>
								<?php
							}
							?>
							</ul>
							<?php
						} else {
							echo '<p>No custom meta data found for this item.</p>';
						}
					}
				?>
			</div>
			<div class="fwp-outfit__footer"></div>
		</div>
		<?php
	}
}
