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
	private $lastCustomData;
	protected $confirmMailTrack = false;
	/**
	 * Construct method.
	 */
	protected function __construct() {
		global $teddyBear__Order;
		$teddyBear__Order = $this;
		$this->lastCustomData = false;
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

		add_action('woocommerce_order_item_meta_end', [$this, 'woocommerce_order_item_add_certificate_link'], 11, 4);

		add_action('woocommerce_order_item_meta_end', [$this, 'woocommerce_order_item_meta_end'], 10, 4);
		add_filter('woocommerce_email_classes', [$this, 'custom_track_order_confirmation_email'], 1, 1);

		add_filter('woocommerce_order_actions', [$this, 'woocommerce_order_actions'], 10, 1);
		add_action('woocommerce_order_action_send_birth_certificates', [$this, 'woocommerce_order_action_send_birth_certificates'], 10, 1);

		add_action('woocommerce_checkout_create_order_line_item', [$this, 'woocommerce_checkout_create_order_line_item'], 10, 4);
		add_action('woocommerce_new_order_item', [$this, 'woocommerce_new_order_item'], 10, 3);
		// add_action('woocommerce_order_item_get_internal_meta_keys', [$this, 'woocommerce_order_item_get_internal_meta_keys'], 10, 1);
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
				foreach($order->get_items() as $order_item_id => $order_item) {
					$item_name = $order_item->get_name();
					$item_meta_data = $order_item->get_meta_data();
					$name_required = $this->is_name_required($order, $order_item);
					if($name_required) {
						$teddyNameRequired[] = [
							'prod_name'			=> $item_name,
							'order_id'			=> $order_id,
							'item_id'			=> $order_item_id,
						];
					}
					if(!empty($item_meta_data)) {
						?>
						<span class="fwp-outfit__product"><?php echo esc_html(sprintf('Item: %s', $item_name)); ?></span>
						<ul class="fwp-outfit__list">
						<?php
						foreach($item_meta_data as $meta) {

							if(is_array($meta->value)) {continue;}
							$thumbnailImage = $voiceFileExists = $voiceFileArgs = false;
							
							// Getting Icons.
							$custom_data = (array) $this->get_order_item_meta($order_item->get_id(), 'custom_teddey_bear_data');
							if($custom_data && isset($custom_data['field'])) {
								foreach((array) $custom_data['field'] as $i => $iRow) {
									if(is_array($iRow)) {
										foreach($iRow as $j => $jRow) {

											if(isset($jRow['voice']) && !empty($jRow['voice'])) {
												// $voiceFileExists = $target_dir.$jRow['voice'];
												$voiceFileArgs = ['url' => $target_dir.$jRow['voice'], 'key' => $jRow['title'], 'name' => $jRow['voice']];
												// print_r($voiceFileExists);
											}
											$jRow = (object) $jRow;
											if(
												isset($jRow->value) && isset($jRow->price) && isset($jRow->image) && 
												!empty($jRow->image) && 
												$jRow->value == $meta->key // && $jRow->price == $meta->value
											) {
												$thumbnailImage = $jRow->image;
												$attachment_id = attachment_url_to_postid($thumbnailImage);
												if($attachment_id) {
													$thumbnailImage = wp_get_attachment_thumb_url($attachment_id);
												} else {
													// print_r('Not found');
												}
											}
										}
									}
								}
							}
							
							// $target_file = (file_exists($target_dir.$meta->value) && !is_dir($target_dir.$meta->value))?$target_dir.$meta->value:false;
							$target_file = ($voiceFileExists && file_exists($voiceFileExists) && !is_dir($voiceFileExists))?$voiceFileExists:false;
							?>
							<li class="fwp-outfit__items">
								<?php if($thumbnailImage): ?><img src="<?php echo esc_url($thumbnailImage); ?>" alt="<?php echo esc_attr($meta->value); ?>" class="fwp-outfit__image" data-product="<?php echo esc_attr($item_name); ?>" data-item="<?php echo esc_attr($meta->key); ?>" data-price="<?php echo esc_attr($meta->value); ?>"><?php endif; ?>
								<span class="fwp-outfit__title"><?php echo esc_html($meta->key); ?></span>
								<span class="fwp-outfit__price"><?php echo wp_kses_post($meta->value); ?></span>
							</li>
							<?php
							if(isset($voiceFileArgs['url']) && isset($voiceFileArgs['key']) && !empty($voiceFileArgs['key']) && $meta->key == $voiceFileArgs['key']) {
								?>
								<li class="fwp-outfit__items fwp-outfit__audio">
									<div class="fwp-outfit__player" data-audio="<?php echo esc_url(site_url(str_replace([ABSPATH], [''], $voiceFileArgs['url']))); ?>" title="<?php echo esc_attr($meta->value); ?>"></div>
								</li>
								<?php
							}
						}
						?>
						<?php if($custom_data): ?>
							<li class="fwp-outfit__items <?php echo esc_attr(($target_file)?'fwp-outfit__certificate':''); ?>">
								<a href="<?php echo esc_url(home_url('?certificate_preview='. $order_id .'-'.$order_item_id)); ?>" class="btn button link" target="_blank"><?php esc_html_e('Certificate', 'teddybearsprompts'); ?></a>
							</li>
						<?php endif; ?>
						</ul>
						<?php
					} else {
						// echo '<p>No custom meta data found for this item.</p>';
					}
				}
				if(count($teddyNameRequired) >= 1) {
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
		if(isset($email_classes['WC_Email_Customer_Processing_Order'])) {
			// $email_classes['WC_Email_New_Order']->is_order_confirmation = true;
			$email_classes['WC_Email_Customer_Processing_Order']->is_order_confirmation = true;
			$this->confirmMailTrack = true;
		}
		return $email_classes;
	}
	public function woocommerce_order_item_meta_end($item_id, $item, $order, $plain_text) {
		// if(!isset($order->is_order_confirmation) || $order->is_order_confirmation !== true) {return;}
		if($this->confirmMailTrack !== true) {return;}global $teddyProduct;
		$order_item = $item;
		$order_id  = $order->get_id();
		$product_id = $item->get_product_id();
		$custom_popup = $teddyProduct->get_order_pops_meta($order, $order_item, $product_id);
		if(!$custom_popup || empty($custom_popup)) {return;}
		
		$item_metas = $item->get_meta_data();
		foreach($custom_popup as $posI => $posRow) {
			foreach($posRow as $row) {
				if($row['type'] == 'voice') {
					$voiceShouldExists = $voiceFileExists = false;
					foreach($item_metas as $meta) {
						if($meta->key == $row['steptitle']) {
							$voiceShouldExists = $row['steptitle'];
						}
					}
					if($voiceShouldExists) {
						$meta_data = $item->get_meta('custom_teddey_bear_data', true);
						if(isset($meta_data['field'])) {
							if( ! is_array($meta_data['field'])) {
								$meta_data = (array) $meta_data;
							}
							foreach($meta_data['field'] as $field) {
								foreach($field as $i => $row) {
									if($row['title'] == $voiceShouldExists) {
										$voiceFileExists = true;
									}
								}
							}
						}
					}
					if($voiceShouldExists && ! $voiceFileExists) {
						// $uploadVoiceURL = site_url('upload-voice/'.$order_id.'/'.$item_id.'/');
						$uploadVoiceURL = 'mailto:'.get_option('admin_email').'?subject='.esc_attr(__('Voice Record', 'teddybearsprompts')).'&body='.esc_attr(sprintf(__('Order #%d, Cart Item: #%d, Item Subtotal: %s %s Product: %s', 'teddybearsprompts'), $order_id, $item_id, $item->get_subtotal(), '%0D%0A', get_the_title($product_id)));
						?>
						<a href="<?php echo esc_attr($uploadVoiceURL); ?>" target="_blank" style="color: #fff;font-weight:normal;text-decoration:underline;background: #7f54b3;padding: 10px 15px;border-radius: 5px;line-height: 40px;text-decoration: none;"><?php esc_html_e('Send Recorded voice', 'teddybearsprompts'); ?></a>
						<?php
					}
				}
			}
		}
	}
	public function woocommerce_order_item_add_certificate_link($item_id, $item, $order, $plain_text) {
		if(!is_wc_endpoint_url('view-order')) {return;}
		if(!in_array($order->get_status(), ['completed'])) {return;}
		$order_id = $order->get_id();
		?>
		<a href="<?php echo esc_url(site_url('/?certificate_preview=' . $order_id . '-' . $item_id)); ?>" class="button btn" target="_blank"><?php esc_html_e('View certificate', 'teddybearsprompts'); ?></a>
		<?php
	}
	
	public function woocommerce_checkout_create_order_line_item($item, $cart_item_key, $cart_item, $order) {
		if(isset($cart_item['custom_teddey_bear_makeup'])) {
			foreach($cart_item['custom_teddey_bear_makeup'] as $meta) {
				if(!empty($meta['item']) && !empty($meta['price']) && is_numeric($meta['price'])) {
					$item->add_meta_data(esc_html($meta['item']), wc_price($meta['price']), true);
				}
			}
		}
		if(isset($cart_item['custom_teddey_bear_data'])) {
			$this->lastCustomData = $cart_item['custom_teddey_bear_data'];
		}
	}
	public function woocommerce_order_item_get_internal_meta_keys($keys) {
		// $keys[] = '';
		return $keys;
	}
	public function woocommerce_new_order_item($item_id, $item, $order_id) {
		global $wpdb;$meta_key = 'custom_teddey_bear_data';
		if($this->lastCustomData) {
			$meta_value = maybe_serialize($this->lastCustomData);
			$this->lastCustomData = false;
			// Check if the meta key already exists for the order item
			$existing_meta = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT meta_id FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE order_item_id = %d AND meta_key = %s",
					$item_id, $meta_key
				)
			);
			if(!$existing_meta || empty($existing_meta)) {
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


	/**
	 * Order actions.
	 */
	public function woocommerce_order_actions($actions) {
		$actions['send_birth_certificates'] = __('Send Birth Certificates', 'teddybearsprompts');
    	return $actions;
	}
	public function woocommerce_order_action_send_birth_certificates($order, $preview = false) {
		$certificates = [];global $teddyProduct;
		$order_id = $order->get_id();
		// $order->get_status();
		
		foreach($order->get_items() as $order_item_id => $order_item) {
			if($preview && $preview != $order_item_id) {continue;}
			$item_id = $order_item->get_id();
			// $item_name = $order_item->get_name();
			$product_id = $order_item->get_product_id();
			// $product = $order_item->get_product();
			// $quantity = $order_item->get_quantity();
			$popup_meta = $teddyProduct->get_order_pops_meta($order, $order_item, $product_id);
			if(!$popup_meta || !is_array($popup_meta) || count($popup_meta) <= 0) {continue;}
			
			foreach($popup_meta as $posI => $posRow) {
				foreach($posRow as $i => $field) {
					if($field['type'] == 'info') {
						$item_meta_data = $order_item->get_meta('custom_teddey_bear_data', true);
						if(!$item_meta_data) {continue;}
						foreach($item_meta_data['field'] as $i => $iRow) {
							foreach($iRow as $j => $jRow) {
								// if($jRow['title'] == 'Voice') {}
								if($field['steptitle'] == $jRow['title'] && $j == 0) {
									$custom_data = wp_parse_args((array) get_post_meta($product_id, '_teddy_custom_data', true), [
										'eye'				=> '',
										'brow'				=> '',
										'weight'			=> '',
										'height'			=> '',
									]);
									$args = [
										'eye'			=> $custom_data['eye'],
										'brow'			=> $custom_data['brow'],

										'id_num'		=> $order_id,
										// bin2hex($order_id), // strtolower(base_convert($order_id, 10, 36) . base_convert($item_id, 10, 36) . '-' . rand(1, 9999)),

										'teddyname'		=> $iRow[0]['value'],
										'birth'			=> $iRow[1]['value'],
										'belongto'		=> $iRow[2]['value'],
										'gift_by'		=> $iRow[3]['value'],

										'weight'		=> $custom_data['weight'],
										'height'		=> $custom_data['height'],

										'preview'		=> (bool) $preview,
										// 'single'		=> ($preview)?$preview:false,
										
										'pdf'			=> 'certificate-'.$item_id.'-'.$order_id.'.pdf'
									];
									// print_r([$args]);wp_die($item_id);
									$certificates[] = apply_filters('teddybearpopupaddon_generate_certificate', false, $args);
								}
							}
						}
					}
				}
			}
		}
		
		try {
			if(count($certificates) >= 1) {
				do_action('teddybearpopupaddon_mail_certificates', $certificates, [
					'to'		=> $order->get_billing_email()
				]);
			} else {
				wp_die(__('No certificate found.', 'teddybearsprompts'));
			}
		} catch (\Exception $e) {
			wp_die($e->getMessage(), __('Error happens', 'teddybearsprompts'));
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
		global $teddyProduct;
		$order_id = $order->get_id();
		$item_id = $order_item->get_id();
		$product_id = $order_item->get_product_id();
		$popup_meta = $teddyProduct->get_order_pops_meta($order, $order_item, $product_id);
		foreach($popup_meta as $posI => $posRow) {
			foreach($posRow as $i => $field) {
				if($field['type'] == 'info') {
					$item_meta_data = $order_item->get_meta('custom_teddey_bear_data', true);
					if(!$item_meta_data) {continue;}
					foreach($item_meta_data['field'] as $i => $iRow) {
						foreach($iRow as $j => $jRow) {
							if($field['steptitle'] == $jRow['title'] && $j == 0) {
								return (isset($iRow[0]) && isset($iRow[0]['value']) && trim($iRow[0]['value']) == '');
							}
						}
					}
				}
			}
		}
	}
}
