<?php
/**
 * LoadmorePosts
 *
 * @package TeddyBearCustomizeAddon
 */

namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;

use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;

class Category_Edit {
	use Singleton;
	protected function __construct() {
		$this->setup_hooks();
	}
	public function setup_hooks() {
		add_action('product_cat_edit_form_fields', [$this, 'add_global_product_field'], 10, 2);
		add_action('edited_product_cat', [$this, 'save_global_product_field'], 10, 2);
		// 
	}


    public function add_global_product_field($term, $taxonomy) {
		global $teddy_Plushies;
		$product_id_sitting = get_term_meta($term->term_id, 'global_product-sitting', true);
		$product_id_standing = get_term_meta($term->term_id, 'global_product-standing', true);
		$products = get_posts([
			'post_type'		=> 'product',
			'numberposts'	=> -1,
			'fields'		=> 'ID, post_title'
		]);
		?>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="global_product-sitting"><?php _e('Global Product (Sitting)', 'textdomain'); ?></label>
			</th>
			<td>
				<select name="global_product-sitting" id="global_product-sitting">
					<option value="0" <?php selected($product_id_sitting, '0'); ?>><?php _e('Select sitting product', 'textdomain'); ?></option>
					<?php foreach ($products as $product) :
						if ($teddy_Plushies->is_accessory($product->ID)) {continue;}
						?>
						<option value="<?php echo esc_attr($product->ID); ?>" <?php selected($product_id_sitting, $product->ID); ?>>
							<?php echo esc_html($product->post_title); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php _e('Select a sitting product of customization for all products under this category', 'textdomain'); ?></p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="global_product-standing"><?php _e('Global Product (Standing)', 'textdomain'); ?></label>
			</th>
			<td>
				<select name="global_product-standing" id="global_product-standing">
					<option value="0" <?php selected($product_id_standing, '0'); ?>><?php _e('Select standing product', 'textdomain'); ?></option>
					<?php foreach ($products as $product) :
						if ($teddy_Plushies->is_accessory($product->ID)) {continue;}
						?>
						<option value="<?php echo esc_attr($product->ID); ?>" <?php selected($product_id_standing, $product->ID); ?>>
							<?php echo esc_html($product->post_title); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php _e('Select a standing product of customization for all products under this category', 'textdomain'); ?></p>
			</td>
		</tr>
		<?php
	}
	public function save_global_product_field($term_id, $tt_id) {
		if (isset($_POST['global_product-sitting']) && '' !== $_POST['global_product-sitting']) {
			$global_product_sitting = intval($_POST['global_product-sitting']);
			update_term_meta($term_id, 'global_product-sitting', $global_product_sitting);
		}
		if (isset($_POST['global_product-standing']) && '' !== $_POST['global_product-standing']) {
			$global_product_standing = intval($_POST['global_product-standing']); 
			update_term_meta($term_id, 'global_product-standing', $global_product_standing);
		}
	}
}