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
	private $lastOrderItemPopSet;
	private $lastOrderItemMakeup;
	private $lastOrderItemDataSet;
	protected $confirmMailTrack = false;
	/**
	 * Construct method.
	 */
	protected function __construct() {
		$this->lastOrderItemPopSet = [];
		$this->lastOrderItemMakeup = [];
		$this->lastOrderItemDataSet = [];
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
		// add_shortcode( 'checkout_video', [ $this, 'checkout_video' ] );
		add_action('add_meta_boxes', [$this, 'add_custom_meta_box']);

		add_action('woocommerce_order_item_meta_end', [$this, 'woocommerce_order_item_meta_end'], 10, 4);
		add_filter('woocommerce_email_classes', [$this, 'custom_track_order_confirmation_email'], 1, 1);

		add_filter('woocommerce_order_actions', [$this, 'woocommerce_order_actions'], 10, 1);
		add_action('woocommerce_order_action_send_birth_certificates', [$this, 'woocommerce_order_action_send_birth_certificates'], 10, 1);
		// add_filter('woocommerce_order_status_changed', [$this, 'woocommerce_order_status_changed'], 10, 3);

		add_action('woocommerce_checkout_create_order_line_item', [$this, 'woocommerce_checkout_create_order_line_item'], 10, 4);
		add_action('woocommerce_new_order_item', [$this, 'woocommerce_new_order_item'], 10, 3);

		/**
		 * Paused subtotal calculation for an issue.
		 */
		add_filter('woocommerce_order_get_subtotal', [$this, 'woocommerce_order_get_subtotal'], 10, 2);

		add_filter('woocommerce_order_get_total', [$this, 'woocommerce_order_get_total'], 10, 2);
		// add_filter('woocommerce_order_amount_item_subtotal', [$this, 'woocommerce_order_amount_item_subtotal'], 10, 5);
		// add_filter('woocommerce_order_amount_line_subtotal', [$this, 'woocommerce_order_amount_line_subtotal'], 10, 5);
		// add_filter('woocommerce_get_order_item_totals', [$this, 'woocommerce_get_order_item_totals'], 10, 3);
	}
	public function add_custom_meta_box() {
		$screens = ['shop_order'];
		foreach ($screens as $screen) {
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
		global $teddy_Certificate;global $teddy_Voices;global $teddy_Meta;global $teddy_Plushies;
		$order_id = $post->ID;
		$order = wc_get_order($order_id);
		$target_dir = TEDDY_BEAR_CUSTOMIZE_ADDON_UPLOAD_DIR;
		?>
		<div class="fwp-outfit__container">
			<div class="fwp-outfit__header">
				<span class="fwp-outfit__title"><?php esc_html_e('Customized order', 'teddybearsprompts'); ?></span>
			</div>
			<div class="fwp-outfit__body">
				<?php
				$teddyNameRequired = [];
				foreach ($order->get_items() as $order_item_id => $order_item) {
					$item_name = $order_item->get_name();
					$item_meta_data = $order_item->get_meta_data();
					
					$name_required = $this->is_name_required($order, $order_item);
					if ($name_required) {
						$teddyNameRequired[] = [
							'prod_name'			=> $item_name,
							'order_id'			=> $order_id,
							'item_id'			=> $order_item_id,
							'info'				=> $name_required
						];
					}
					if (!empty($item_meta_data)) {
						?>
						<span class="fwp-outfit__product"><?php echo esc_html(sprintf('Item: %s', $item_name)); ?></span>
						<ul class="fwp-outfit__list">
						<?php
						// Getting Icons.
						$custom_dataset = $teddy_Meta->get_order_item_dataset($order_item, $order);
						$custom_charges = $teddy_Meta->get_order_item_charges($order_item, $order);
						$custom_popset = $teddy_Meta->get_order_item_popset($order_item, $order);
						
						foreach ($item_meta_data as $meta) {
							if (!$this->is_allowed_meta_tag($meta->key, $meta, $item_meta_data)) {continue;}

							if (is_array($meta->value)) {continue;}
							$thumbnailImage = $voiceFileExists = $voiceFileArgs = false;
							
							
							if ($custom_dataset && isset($custom_dataset['field'])) {
								foreach ((array) $custom_dataset['field'] as $i => $iRow) {
									if (is_array($iRow)) {
										foreach ($iRow as $j => $jRow) {
											$jRow = (object) $jRow;
											if (
												isset($jRow->value) && isset($jRow->price) && isset($jRow->image) && 
												!empty($jRow->image) && strtolower($jRow->value) == strtolower($meta->key)
												// && $jRow->price == $meta->value
											) {
												$thumbnailImage = $jRow->image;
												$attachment_id = attachment_url_to_postid($thumbnailImage);
												if ($attachment_id) {
													$thumbnailImage = wp_get_attachment_thumb_url($attachment_id);
												} else {
													// print_r('Not found');
												}
											}
										}
									}
								}
							}
							?>
							<li class="fwp-outfit__items">
								<?php if ($thumbnailImage): ?><img src="<?php echo esc_url($thumbnailImage); ?>" alt="<?php echo esc_attr($meta->value); ?>" class="fwp-outfit__image" data-product="<?php echo esc_attr($item_name); ?>" data-item="<?php echo esc_attr($meta->key); ?>" data-price="<?php echo esc_attr($meta->value); ?>"><?php endif; ?>
								<span class="fwp-outfit__title"><?php echo esc_html($meta->key); ?></span>
								<span class="fwp-outfit__price"><?php echo wp_kses_post($meta->value); ?></span>
							</li>
							<?php
						}
						
						if ($teddy_Voices->should_exists_voices($order, $order_item)) {
							if ($teddy_Voices->has_single_voices($order, $order_item)) {
								foreach ($teddy_Voices->get_single_voices($order, $order_item) as $voiceURL) {
									?>
									<li class="fwp-outfit__items fwp-outfit__audio">
										<div class="fwp-outfit__player" data-audio="<?php echo esc_url(site_url(str_replace([ABSPATH], [''], $voiceURL))); ?>" title="<?php esc_attr_e('Voice', 'teddybearsprompts'); ?>"></div>
									</li>
									<?php
								}
							} else {
								?>
								<li class="fwp-outfit__items fwp-outfit__emailvoice">
									<?php esc_html_e('User have to send their voices.', 'teddybearsprompts'); ?>
								</li>
								<?php
							}
						}
						?>
						<?php $certificate_preview = site_url('/certificates/' . $order_id . '/' . $order_item_id . '/'); ?>
						<?php if ($custom_dataset): ?>
							<?php
								$order_item_product = $order_item->get_product();
								if (!$teddy_Plushies->is_accessory($order_item_product->get_id())): ?>
									<li class="fwp-outfit__items <?php echo esc_attr((true)?'fwp-outfit__certificate':''); ?>">
										<a href="<?php echo esc_url($certificate_preview); ?>" class="btn button link" data-certificate="<?php echo esc_attr($order_item_id); ?>" target="_blank"><?php esc_html_e('Certificate', 'teddybearsprompts'); ?></a>
									</li>
							<?php endif; ?>
						<?php else: ?>
							<li class="fwp-outfit__items <?php echo esc_attr((true)?'fwp-outfit__certificate':''); ?>">
								<a href="<?php echo esc_url($certificate_preview); ?>" class="link" target="_blank"><?php esc_html_e('Necessery information missing on this item.', 'teddybearsprompts'); ?></a>
							</li>
						<?php endif; ?>
						</ul>
						<?php
					} else {
						// echo '<p>No custom meta data found for this item.</p>';
					}
				}
				if (count($teddyNameRequired) >= 1) {
					?>
					<script>window.teddyNameRequired = <?php echo json_encode($teddyNameRequired); ?>;</script>
					<?php
				}
				?>
			</div>
			<div class="fwp-outfit__footer"></div>
		</div>
		<?php
	}





	/**
	 * Work on wooommerce order email.
	 */
	public function woocommerce_email_order_meta($order, $sent_to_admin, $plain_text) {}
	public function woocommerce_email_order_meta_fields($fields, $sent_to_admin, $order) {}
	public function woocommerce_email_order_meta_keys($fields, $sent_to_admin) {}

	public function woocommerce_email_after_order_table($order, $sent_to_admin, $plain_text, $email) {}
	public function custom_track_order_confirmation_email($email_classes) {
		if (isset($email_classes['WC_Email_Customer_Processing_Order'])) {
			// $email_classes['WC_Email_New_Order']->is_order_confirmation = true;
			$email_classes['WC_Email_Customer_Processing_Order']->is_order_confirmation = true;
			$this->confirmMailTrack = true;
		}
		return $email_classes;
	}
	public function woocommerce_order_item_meta_end($item_id, $order_item, $order, $plain_text) {
		global $teddy_Product;global $teddy_Voices;global $teddy_Order;
		if (!apply_filters('teddybear/project/system/isactive', 'voice-reminder_enable')) {return;}
		// if (!isset($order->is_order_confirmation) || $order->is_order_confirmation !== true) {return;}
		// if ($this->confirmMailTrack !== true) {return;}
		
		if (in_array($order->get_status(), explode(',', str_replace(' ', '', apply_filters('teddybear/project/system/getoption', 'order-avoid_askvoice', 'shipped, completed'))))) {return;}
		// if (!in_array($order->get_status(), explode(',', str_replace(' ', '', apply_filters('teddybear/project/system/getoption', 'voice-reminder_orderstatuses', 'shipped, completed'))))) {return;}
		if (!$teddy_Voices->should_exists_voices($order, $order_item)) {return;}
		if (!$teddy_Voices->has_single_voices($order, $order_item)) {
			$to_email = apply_filters('teddybear/project/system/getoption', 'voice-reminder_reciever', get_option('admin_email'));
			$uploadVoiceURL = 'mailto:' . $to_email . '?subject=' . urlencode(
				str_replace(
					['{{order_id}}', '{{item_id}}'],
					[$order->get_id(), $order_item->get_id()],
					apply_filters('teddybear/project/system/getoption', 'voice-reminder_subject', sprintf(__('Order #%s', 'teddybearsprompts'), '{{order_id}}'))
				)
			) . '&body=' . esc_attr(sprintf(__('Order #%d, Cart Item: #%d, Item Subtotal: %s %s Product: %s', 'teddybearsprompts'), $order->get_id(), $order_item->get_id(), $teddy_Order->get_order_item_subtotal($order_item, $order->get_id()), '%0D%0A', get_the_title($order_item->get_product_id())));

			echo '<a href="' . esc_attr($uploadVoiceURL) . '" target="_blank" style="color: ' . esc_attr(apply_filters('teddybear/project/system/getoption', 'voice-reminder_color', '#fff')) . ';background: ' . esc_attr(apply_filters('teddybear/project/system/getoption', 'voice-reminder_bg', '#7f54b3')) . ';font-weight: normal;text-decoration: none;padding: 5px 10px;border-radius: 5px;line-height: 15px;display: block;width: fit-content;">' . esc_html(
				apply_filters('teddybear/project/system/getoption', 'voice-reminder_label', __('Send Recorded voice', 'teddybearsprompts'))
			) . '</a>';
		}
	}
	
	/**
	 * Add seperate meta data after order item name or product name.
	 */
	public function woocommerce_checkout_create_order_line_item($item, $cart_item_key, $cart_item, $order) {
		/**
		 * Here are the problem. Instead of handling Main product, it catched first product.
		 */
		$makeup = $cart_item['custom_makeup']??[];
		if ($makeup && count($makeup) >= 1) {
			foreach ($makeup as $meta) {
				/**
				 * Avoid product items from adding meta key & price.
				 */
				if (isset($meta['product']) && !empty($meta['product']) && is_numeric($meta['product'])) {continue;}
				if (!empty($meta['item']) && !empty($meta['price']) && is_numeric($meta['price'])) {
					$item->add_meta_data(esc_html($meta['item']), wc_price($meta['price']), true);
				}
			}
			$this->lastOrderItemMakeup[$cart_item_key] = $cart_item['custom_makeup']??[];
			$this->lastOrderItemDataSet[$cart_item_key] = $cart_item['custom_dataset']??[];
			$this->lastOrderItemPopSet[$cart_item_key] = $cart_item['custom_popset']??[];
		}
	}
	public function woocommerce_new_order_item($item_id, $order_item, $order_id) {
		global $wpdb;
		$order_item_key = $order_item->legacy_cart_item_key??false;
		if ($order_item_key && isset($this->lastOrderItemDataSet[$order_item_key])) {
			// print_r([$order_item_key, array_keys($this->lastOrderItemPopSet)]);
			$sets = [
				'custom_popset'		=> maybe_serialize($this->lastOrderItemPopSet[$order_item_key]??[]),
				'custom_makeup'		=> maybe_serialize($this->lastOrderItemMakeup[$order_item_key]??[]),
				'custom_dataset'	=> maybe_serialize($this->lastOrderItemDataSet[$order_item_key]??[]),
			];
			if (isset($this->lastOrderItemPopSet[$order_item_key])) {unset($this->lastOrderItemPopSet[$order_item_key]);}
			if (isset($this->lastOrderItemDataSet[$order_item_key])) {unset($this->lastOrderItemDataSet[$order_item_key]);}
			if (isset($this->lastOrderItemMakeup[$order_item_key])) {unset($this->lastOrderItemMakeup[$order_item_key]);}
			foreach ($sets as $meta_key => $meta_value) {
				// Check if the meta key already exists for the order item
				$existing_meta = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT meta_id FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE order_item_id = %d AND meta_key = %s",
						$item_id, $meta_key
					)
				);
				if (!$existing_meta || empty($existing_meta)) {
					// Insert the new meta data
					$wpdb->insert(
						$wpdb->prefix . 'woocommerce_order_itemmeta',
						[
							'order_item_id' => $item_id,
							'meta_key'      => $meta_key,
							'meta_value'    => $meta_value
						],
						[
							'%d',
							'%s',
							'%s'
						]
					);
				} else {
					// Update the existing meta data
					$wpdb->update(
						$wpdb->prefix . 'woocommerce_order_itemmeta',
						['meta_value' => $meta_value],
						['order_item_id' => $item_id, 'meta_key' => $meta_key],
						['%s'],
						['%d', '%s']
					);
				}
			}
		}
	}


	/**
	 * Order actions.
	 */
	public function woocommerce_order_actions($actions) {
		$actions['send_birth_certificates'] = __('Send Birth Certificates', 'teddybearsprompts');
    	return $actions;
	}
	public function woocommerce_order_action_send_birth_certificates($order, $preview = false) {
		global $teddy_Product;global $teddy_Certificate;
		$order_id = $order->get_id();
		// $order->get_status();
		
		$certificates = $teddy_Certificate->get_all_certificates($order, $preview);
		$notFoundError = true;$errorMessage = false;
		try {
			// 
			// print_r([$certificates, $preview]);wp_die('Hi');
			// 
			if (count($certificates) >= 1) {
				$notFoundError = false;
				do_action('teddybearpopupaddon_mail_certificates', $certificates, [
					'to'		=> $order->get_billing_email()
				]);
			} else {
				wp_die(__('No certificate found.', 'teddybearsprompts'));
			}
		} catch (\Exception $e) {
			$errorMessage = $e->getMessage();
		}
		if ($notFoundError) {
			get_header();
			do_shortcode('[elementor-template id="3135"]');
			get_footer();
		}
	}
	public function woocommerce_order_status_changed($order_id, $old_status, $new_status) {
		// $old_status === 'wc_on_hold' && 
		if ($new_status === 'wc_completed') {
			$order = wc_get_order($order_id);
			$this->woocommerce_order_action_send_birth_certificates($order);
		}
	}
	public function get_order_item_meta($order_item_id, $meta_key) {
		global $wpdb;
		$existing_meta = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_value FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE order_item_id = %d AND meta_key = %s",
				$order_item_id, $meta_key
			)
		);
		return maybe_unserialize($existing_meta);
	}
	public function is_name_required($order, $order_item) {
		global $teddy_Product;global $teddy_Meta;
		$order_id = $order->get_id();
		$item_id = $order_item->get_id();
		$product_id = $order_item->get_product_id();
		$popup_meta = $teddy_Product->get_order_pops_meta($order, $order_item, $product_id);
		foreach ($popup_meta as $posI => $posRow) {
			foreach ($posRow as $i => $field) {
				if ($field['type'] == 'info') {
					$item_meta_data = $teddy_Meta->get_order_item_dataset($order_item, $order);
					if (!$item_meta_data || !isset($item_meta_data['field'])) {continue;}
					foreach ($item_meta_data['field'] as $i => $iRow) {
						foreach ($iRow as $j => $jRow) {
							if ($field['steptitle'] == $jRow['title'] && $j == 0) {
								return (
									(isset($iRow[0]) && isset($iRow[0]['value']) && empty(trim($iRow[0]['value']))) || 
									(isset($iRow[1]) && isset($iRow[1]['value']) && empty(trim($iRow[1]['value']))) || 
									(isset($iRow[2]) && isset($iRow[2]['value']) && empty(trim($iRow[2]['value']))) || 
									(isset($iRow[3]) && isset($iRow[3]['value']) && empty(trim($iRow[3]['value'])))
								)?[
									'teddyname'		=> ($iRow[0]??[])['value']??'',
									'teddybirth'	=> empty(($iRow[1]??[])['value']??'')?'':date('Y-m-d', strtotime(
										($iRow[1]??[])['value']??''
									)),
									'recievername'	=> ($iRow[2]??[])['value']??'',
									'createdby'		=> ($iRow[3]??[])['value']??'',
								]:false;
							}
						}
					}
				}
			}
		}
		return false;
	}
	public function get_order_item_subtotal($order_item, $order_id, $additionalOnly = false) {
		global $teddy_Meta;$order = wc_get_order($order_id);$additionalAmount = 0;
		$charges = (array) $teddy_Meta->get_order_item_charges($order_item, $order);
		foreach ($charges as $row) {
			if (isset($row['product']) && is_numeric($row['product']) && get_post_type((int) $row['product']) == 'product') {continue;}
			elseif (isset($row['price']) && is_numeric($row['price'])) {
				$row['price'] = (float) $row['price'];
				$additionalAmount += ($row['price'] * ($row['quantity']??1));
			} else {}
		}
		return (
			(
				($additionalOnly)?$additionalAmount:($order_item->get_subtotal() + $additionalAmount)
			) * $order_item->get_quantity()
		);
	}
	public function woocommerce_order_get_subtotal($subTotal, $order) {
		$newSubTotal = 0;$order_id = $order->get_id();
		foreach ($order->get_items() as $item_id => $item) {
			$_subtotal = $this->get_order_item_subtotal($item, $order_id, true);
			if ($_subtotal && $_subtotal > 0) {
				$newSubTotal = ($newSubTotal + $_subtotal);
			}
		}
		return ($subTotal + $newSubTotal);
	}
	public function woocommerce_order_get_total($total, $order) {
		$newTotal = 0;$order_id = $order->get_id();
		foreach ($order->get_items() as $item_id => $item) {
			$_total = $this->get_order_item_subtotal($item, $order_id, true);
			if ($_total && $_total > 0) {
				$newTotal = ($newTotal + $_total);
			}
		}
		return ($total + $newTotal);
	}
	/**
	 * Subtotal for individual product without sonsidering quantity
	 */
	public function woocommerce_order_amount_item_subtotal($subtotal, $order, $item, $inc_tax, $round) {
		return $subtotal;
	}
	/**
	 * Subtotal with considering quantity.
	 */
	public function woocommerce_order_amount_line_subtotal($subtotal, $order, $item, $inc_tax, $round) {
		return $subtotal;
	}
	public function woocommerce_get_order_item_totals($total_rows, $order, $tax_display) {
		return $total_rows;
	}
	/**
	 * Check whether if meta tag is not allowed.
	 */
	public function is_allowed_meta_tag($meta_key, $meta, $custom_data) {
		// print_r([$custom_data]);
		$except = ['_reduced_stock'];
		return (!in_array($meta_key, $except));
	}
}
