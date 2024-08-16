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
				<span class="fwp-outfit__actions">
					<div>
						<?php
							echo wp_kses(
								sprintf(
									__('Printout Certificates %s With BG %s or %s without BG %s', 'teddybearsprompts'),
									sprintf('<a href="%s" target="_blank">', esc_url(site_url('certificates/'. $order_id .'/print/bg/'))), '</a>',
									sprintf('<a href="%s" target="_blank">', esc_url(site_url('certificates/'. $order_id .'/print/nobg/'))), '</a>',
								),
								[
									'a' => [
                                        'href' => [],
                                        'target' => '_blank',
                                    ],
								]
							);
						?>
					</div>
				</span>
			</div>
			<div class="fwp-outfit__body">
				<?php
				$teddyNameRequired = [];
				foreach ($order->get_items() as $order_item_id => $order_item) {
					$item_name = $order_item->get_name();
					$item_meta_data = $order_item->get_meta_data();
					$name_required = $this->is_name_required($order, $order_item);
					// 
					if ($name_required) {
						$name_required = wp_parse_args((array) $name_required, [
							'teddy_birth' => ''
						]);
						$name_required['teddybirth'] = $name_required['teddy_birth'];
						if (empty(trim($name_required['teddy_birth']))) {
							$name_required['teddy_birth'] = date('d-M-Y H:i:s');
						}
						$name_required['teddy_birth'] = date('Y-m-d', strtotime($name_required['teddy_birth']));
						$teddyNameRequired[] = [
							'order_id'			=> $order_id,
							'prod_name'			=> $item_name,
							'item_id'			=> $order_item_id,
							'info'				=> $name_required
						];
					}
					$_dataset = $teddy_Meta->get_order_item_dataset($order_item, $order);
					if (!empty($_dataset)) {
						// echo '<pre style="display: none;">';print_r($_dataset);echo '</pre>';
						?>
						<span class="fwp-outfit__product"><?php echo esc_html(sprintf(__('Item: %s', 'teddybearsprompts'), $item_name)); ?></span>
						<ol class="fwp-outfit__list">
							<?php
							$_object = [];$_canvas = false;
							foreach ($_dataset as $key => $dataRow) {
								if (!is_array($dataRow)) {continue;}
								if (!isset($dataRow['type'])) {continue;}
								if (isset($dataRow['_canvas']) && !$_canvas) {
									$_canvas = (array) $dataRow['_canvas'];
								}
								switch ($dataRow['type']) {
									case 'radio':
									case 'checkbox':
									case 'select':
										if (isset($dataRow['options'])) {
											foreach ($dataRow['options'] as $oIndex => $option) {
												// if (isset($option['product']) && !empty($option['product'])) {continue;}
												$_object[] = [
													'product'		=> isset($option['product'])?$option['product']:false,
													'cost'			=> isset($option['cost'])?floatval($option['cost']):0,
													'label'			=> isset($option['label'])?$option['label']:false,
													'type'			=> $dataRow['type']
												];
											}
										}
										break;
									case 'outfit':
										if (isset($dataRow['groups'])) {
											foreach ($dataRow['groups'] as $gIndex => $group) {
												if (isset($group['options'])) {
													foreach ($group['options'] as $oIndex => $option) {
														// if (isset($option['product']) && !empty($option['product'])) {continue;}
														$_object[] = [
															'product'		=> isset($option['product'])?$option['product']:false,
															'cost'			=> isset($option['cost'])?floatval($option['cost']):0,
															'label'			=> isset($option['label'])?$option['label']:false,
															'type'			=> $dataRow['type']
														];
													}
												}
											}
										}
										break;
									case 'voice':
										$option = $dataRow;
										$_object[] = [
											'product'		=> isset($option['product'])?$option['product']:false,
											'cost'			=> isset($option['cost'])?floatval($option['cost']):0,
											'label'			=> isset($option['label'])?$option['label']:false,
											'attaced'		=> isset($option['attaced'])?$option['attaced']:[],
											'type'			=> $dataRow['type']
										];
										break;
									default:
										$option = $dataRow;
										$_object[] = [
											'product'		=> isset($option['product'])?$option['product']:false,
											'cost'			=> isset($option['cost'])?floatval($option['cost']):0,
											'label'			=> isset($option['label'])?$option['label']:false,
											'attaced'		=> isset($option['attaced'])?$option['attaced']:[],
											'infos'			=> isset($option['infos'])?$option['infos']:false,
											'type'			=> $dataRow['type'],
										];
										break;
								}
							}
							foreach ($_object as $_index => $_row) {
								if (empty(trim($_row['label']))) {
									if (isset($_row['product']) && !empty($_row['product'])) {
										$product = wc_get_product((int) $_row['product']);
										$_object[$_index]['label'] = $_row['label'] = $product->get_title();
									}
									if ($_row['type'] == 'info') {
										$_object[$_index]['label'] = $_row['label'] = __('Information', 'teddybearsprompts');
									}
								}
								?>
								<li class="fwp-outfit__items fwp-outfit__<?php echo esc_attr($_row['type']); ?>">
									<div class="fwp-outfit__thumb">
										<?php
										$_product_id = (int) $_row['product']; // is_int((int) $_row['product'])?wc_get_product_id((int) $_row['product']):false;
										if ((int) $_row['product'] > 0 && $_product_id && !is_wp_error($_product_id)) : ?>
											<img src="<?php echo esc_url(apply_filters('teddy/clean_url', wp_get_attachment_thumb_url(get_post_meta($_product_id, '_thumbnail_id', true)))); ?>" alt="" srcset="">
										<?php endif ?>
									</div>
									<<?php echo esc_attr(
										(isset($_row['product']) && $_row['product'] > 0)?'a':'span'
									); ?> href="<?php echo esc_url(get_edit_post_link((int) $_row['product'])); ?>" target="_blank"><?php echo esc_html(sprintf(__('%s:', 'teddybearsprompts'), ucfirst($_row['label']))); ?></<?php echo esc_attr(
										(isset($_row['product']) && $_row['product'] > 0)?'a':'span'
									); ?>>
									<?php
									switch ($_row['type']) {
										case 'voice':
											if (isset($_row['attaced']) && is_array($_row['attaced'])) {
												if (isset($_row['attaced']['later'])) {
													?>
													<span><?php echo esc_html(__('Voice will be sent through email.', 'teddybearsprompts')); ?></span>
													<?php
												} elseif (isset($_row['attaced']['skip'])) {
													?>
													<span><?php echo esc_html(__('Voice Skipped.', 'teddybearsprompts')); ?></span>
													<?php
												} elseif (isset($_row['attaced']['blob'])) {
													$voicePath = TEDDY_BEAR_CUSTOMIZE_ADDON_UPLOAD_DIR . $_row['attaced']['blob'];
													if (file_exists($voicePath) && !is_dir($voicePath)) {
														$voiceInfo = (object) [
															'path'			=> $voicePath,
															'size'			=> filesize($voicePath),
															'name'			=> basename($voicePath),
															// 'exist'		=> file_exists($voicePath),
															'type'        	=> mime_content_type($voicePath),
															'url'			=> str_replace(ABSPATH, site_url('/'), $voicePath),
															'alt'			=> sprintf(__('Voice Attachment: %s', 'teddybearsprompts'), basename($voicePath))
														];
														?>
														<div class="voicegrid">
															<span class="voicegrid__method"><?php echo esc_html(sprintf(__('Method: %s', 'teddybearsprompts'), ucfirst($_row['attaced']['method']))); ?></span>
															<div class="voicegrid__block">
																<div class="voicegrid__player" data-config="<?php echo esc_attr(wp_json_encode($voiceInfo)); ?>"></div>
															</div>
														</div>
														<?php
													}
												} else {
													?><span><?php echo esc_html(__('No voice attached for this item.', 'teddybearsprompts')); ?></span><?php
												}
											}
											break;
										case 'info':
											// btn button 
											?><a href="<?php echo esc_url(site_url(sprintf('/certificates/%d/%d/bg/', $order_id, $order_item_id))); ?>" class="link" data-certificate="<?php echo esc_attr($order_item_id); ?>" target="_blank"><?php echo esc_html(__('Certificate', 'teddybearsprompts')); ?></a><?php
											// 
											// print_r($_row);
											// 
											if (isset($_row['infos']) && isset($_row['infos']['teddy_print']) && $_row['infos']['teddy_print']) {
												?><span class="printout"><?php echo esc_html(__('Printout requested', 'teddybearsprompts')); ?></span><?php
											}
											break;
										default:
											?><span><?php echo wc_price($_row['cost']); ?></span><?php
											break;
									}
									?>
								</li>
								<?php
							}
							$_canvas = ($_canvas == false)?[]:$_canvas;
							foreach ((array) $_canvas as $_index => $_row) {
								?>
								<li class="fwp-outfit__items fwp-outfit__canvas">
									<span class="fwp-outfit__title"><?php echo esc_html(__('Canvas preview', 'teddybearsprompts')); ?></span>
									<img src="<?php echo esc_url(site_url($_row)); ?>" alt="<?php echo esc_attr(pathinfo($_row, PATHINFO_BASENAME)); ?>">
								</li>
								<?php
							}
							?>
						</ol>
						<?php
					} else {
						// echo '<p>No custom meta data found for this item.</p>';
					}
				}
				if (count($teddyNameRequired) >= 1) {
					global $teddy_Ajax;
					?>
					<script>window.teddyNameRequired = <?php echo json_encode($teddyNameRequired); ?>;</script>
					<script>window.teddySuggestedNames = <?php echo json_encode($teddy_Ajax->suggested_names(true)); ?>;</script>
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
		if ($teddy_Voices->has_add_later($order, $order_item)) {
			$to_email = apply_filters('teddybear/project/system/getoption', 'voice-reminder_reciever', get_option('admin_email'));
			$uploadVoiceURL = 'mailto:' . $to_email . '?subject=' . urlencode(
				str_replace(
					['{{order_id}}', '{{item_id}}'],
					[$order->get_id(), $order_item->get_id()],
					apply_filters('teddybear/project/system/getoption', 'voice-reminder_subject', sprintf(__('Order #%s', 'teddybearsprompts'), '{{order_id}}'))
				)
			) . '&body=' . esc_attr(sprintf(__('Order #%d, Cart Item: #%d, Item Subtotal: %s %s Product: %s', 'teddybearsprompts'), $order->get_id(), $order_item->get_id(), $teddy_Order->get_order_item_subtotal($order_item, $order->get_id()), '%0D%0A', get_the_title($order_item->get_product_id())));

			// print_r($uploadVoiceURL);wp_die('Remal Mahmud (mahmudremal@yahoo.com)');

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
		$_dataset = $cart_item['custom_dataset']??[];
		if ($_dataset && count($_dataset) >= 1) {
			foreach ($_dataset as $dataRow) {
				if ($dataRow && is_array($dataRow)) {
					try {
						switch ($dataRow['type']) {
							case 'radio':
							case 'checkbox':
							case 'select':
								if (isset($dataRow['options'])) {
									foreach ($dataRow['options'] as $index => $option) {
										if (isset($option['cost'])) {
											$option['label'] = empty($option['label'])?$dataRow['type']:$dataRow['label'];
											$option['cost'] = is_string($option['cost'])?floatval($option['cost']):$option['cost'];
											/* Avoid product items from adding meta key & price. */
											if (isset($option['product']) && !empty($option['product']) && is_numeric($option['product'])) {
												// 
											} else if (!empty($option['label']) && !empty($option['cost']) && is_numeric($option['cost'])) {
												$item->add_meta_data(esc_html($option['label']), wc_price($option['cost']), true);
											} else {
												// 
											}
										}
									}
								}
								break;
							case 'outfit':
								if (isset($dataRow['groups'])) {
									foreach ($dataRow['groups'] as $gIndex => $group) {
										if (isset($group['options'])) {
											foreach ($group['options'] as $oIndex => $option) {
												if (isset($option['cost'])) {
													$option['label'] = empty($option['label'])?$dataRow['type']:$dataRow['label'];
													$option['cost'] = is_string($option['cost'])?floatval($option['cost']):$option['cost'];
													/* Avoid product items from adding meta key & price. */
													if (isset($option['product']) && !empty($option['product']) && is_numeric($option['product'])) {
														// 
													} else if (!empty($option['label']) && !empty($option['cost']) && is_numeric($option['cost'])) {
														$item->add_meta_data(esc_html($option['label']), wc_price($option['cost']), true);
													} else {
														// 
													}
												}
											}
										}
									}
								}
								break;
							default:
								$option = $dataRow;
								if (isset($option['cost'])) {
									$option['label'] = empty($option['label'])?$dataRow['type']:$dataRow['label'];
									$option['cost'] = is_string($option['cost'])?floatval($option['cost']):$option['cost'];
									/* Avoid product items from adding meta key & price. */
									if (isset($option['product']) && !empty($option['product']) && is_numeric($option['product'])) {
										// 
									} else if (!empty($option['label']) && !empty($option['cost']) && is_numeric($option['cost'])) {
										$item->add_meta_data(esc_html($option['label']), wc_price($option['cost']), true);
									} else {}
								}
								break;
						}
					} catch (\Error $th) {}
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
		$requiredFields = ['teddy_name' => '', 'teddy_birth' => '', 'teddy_reciever' => '', 'teddy_sender' => ''];
		$order_id = $order->get_id();
		$item_id = $order_item->get_id();
		$product_id = $order_item->get_product_id();
		$_dataset = $teddy_Meta->get_order_item_dataset($order_item, $order);
		// 
		if ($_dataset && !empty($_dataset)) {
			foreach ($_dataset as $index => $row) {
				if (!isset($row['type'])) {continue;}
				switch ($row['type']) {
					case 'info':
						if (!isset($row['infos'])) {return $requiredFields;}
						foreach ($requiredFields as $rfield => $value) {
							if (!isset($row['infos'][$rfield]) || empty(trim($row['infos'][$rfield]))) {
								return [
									...$requiredFields,
									...$row['infos']
								];
							}
							// $row = ['teddyname' => '', 'teddybirth' => '', 'recievername' => '', 'createdby' => ''];
						}
						break;
					default:
						break;
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
