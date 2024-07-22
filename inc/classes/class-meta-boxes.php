<?php
/**
 * Register Meta Boxes
 *
 * @package TeddyBearCustomizeAddon
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
/**
 * Class Meta_Boxes
 */
class Meta_Boxes {
	use Singleton;
	private $options;
	protected function __construct() {
		// load class.
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		/**
		 * Actions.
		 */
		add_action('add_meta_boxes', [$this, 'add_custom_meta_box']);
		add_action('save_post', [$this, 'save_post_meta_data']);
	}
	/**
	 * Add custom meta box.
	 *
	 * @return void
	 */
	public function add_custom_meta_box() {
		$screens = ['product'];
		foreach ($screens as $screen) {
			add_meta_box(
				'product_customization_prompts',           				// Unique ID
				__('Customize Popup', 'teddybearsprompts'),  // Box title
				[$this, 'custom_meta_box_html'],  		// Content callback, must be of type callable
				$screen,                   							// Post type
				'side'                   								// context
			);
		}
	}
	/**
	 * Custom meta box HTML(for form)
	 *
	 * @param object $post Post.
	 *
	 * @return void
	 */
	public function custom_meta_box_html($post) {
		global $teddy_Plushies;
		$this->options = (array) get_post_meta($post->ID, '_teddy_custom_data', true);
		$_opened_tab = get_post_meta($post->ID, '_dubido_opened_tab', true);
		$_tabs = [
			'accessories' => __('Accessories', 'teddybearsprompts'),
			'custompops' => __('Customization', 'teddybearsprompts'),
		];
		if (!$_opened_tab) {$_opened_tab = array_keys($_tabs)[0];}
		?>
		<div class="fwp-tabs__container">
			<input type="hidden" name="_dubido_opened_tab" value="<?php echo esc_attr($_opened_tab); ?>">
			<div class="fwp-tabs__wrap">
				<div class="fwp-tabs__navs">
					<?php foreach ($_tabs as $_tabKey => $_text) : ?>
					<div class="fwp-tabs__nav-item <?php echo esc_attr(($_opened_tab == $_tabKey)?'active':''); ?>" data-target="#the-<?php echo esc_attr($_tabKey); ?>" data-tab-key="<?php echo esc_attr($_tabKey); ?>"><?php echo esc_html($_text); ?></div>
					<?php endforeach; ?>
				</div>
				<div class="fwp-tabs__tabs-field">
					<div class="fwp-tabs__content <?php echo esc_attr(($_opened_tab == 'accessories')?'active':''); ?>" id="the-accessories">
						<div class="fwp-form-wraper">
							<div class="fwp-form-wrap">
								<?php
								$fields = [
									'title'						=> __('General', 'teddybearsprompts'),
									'description'				=> __('Generel fields comst commonly used to changed.', 'teddybearsprompts'),
									'fields'					=> []
								];
								?>
								<?php
								$post_id = get_the_ID();
								foreach($teddy_Plushies->get_accessories_terms() as $_key => $_term) {
									$fields['fields'][] = [
										'id' 					=> $_key,
										'label'					=> $_term['title'],
										'description'			=> $_term['title'],
										'type'					=> 'checkbox',
										'default'				=> false
									];
									$fields['fields'][] = [
										'id' 					=> $_key . '_thumb',
										'label'					=> $_term['title'],
										'description'			=> $_term['title'],
										'type'					=> 'button',
										'text'					=> __('Select Image', 'teddybearsprompts'),
										'default'				=> '',
										'conditions'			=> [
											[
												'field'			=> $_key,
												'value'			=> 'on',
												'compare'		=> '=='
											]
										],
										'attr'					=> [
											'data-image-select'		=> true,
											'data-selected-image'	=> get_post_meta($post_id, $_key . '_thumb', true)
										]
									];
								} ?>
								<div class="fwp-form">
									<?php
									foreach($fields['fields'] as $field) {
										$this->display_field(['field' => $field], true);
									}
									?>
								</div>
							</div>
						</div>
					</div>
					<div class="fwp-tabs__content <?php echo esc_attr(($_opened_tab == 'custompops')?'active':''); ?>" id="the-custompops">
						<?php
							$post_meta = get_post_meta($post_id, '_teddy_custom_data', true);
							$global_key = (isset($post_meta['product_type']) && $post_meta['product_type'] == 'sitting')?'sitting-global':'standing-global';
							$global_post_id = apply_filters('teddybear/project/system/getoption', $global_key, 0);
						?>
						<button class="fwp-button fwppopspopup-open" type="button" <?php echo esc_attr(
							(
								apply_filters('teddybear/project/system/isactive', 'standard-forceglobal') && 
								$global_post_id != get_the_ID()
							)?'disabled':''
						); ?>><?php esc_html_e('Customize', 'teddybearsprompts'); ?></button>
						<?php
						$fields = [
							'title'						=> __('General', 'teddybearsprompts'),
							'description'				=> __('Generel fields comst commonly used to changed.', 'teddybearsprompts'),
							'fields'					=> [
								[
									'id' 					=> 'product_type',
									'label'					=> __('Select product type', 'teddybearsprompts'),
									'description'			=> __('Mark if this is a Standing / Sitting product.', 'teddybearsprompts'),
									'type'					=> 'radio',
									'default'				=> false,
									'options'				=> [
										'sitting'	=> __('Sitting product', 'teddybearsprompts'),
										'standing'	=> __('Standing product', 'teddybearsprompts')
									]
								],
								[
									'id' 					=> 'eye',
									'label'					=> __('Eye color', 'teddybearsprompts'),
									'description'			=> __('Teddy\'s eye color', 'teddybearsprompts'),
									'type'					=> 'text',
									'default'				=> apply_filters('teddybear/project/system/getoption', 'default-eye', '')
								],
								[
									'id' 					=> 'brow',
									'label'					=> __('Fur color', 'teddybearsprompts'),
									'description'			=> __('Teddy\'s Fur color.', 'teddybearsprompts'),
									'type'					=> 'text',
									'default'				=> apply_filters('teddybear/project/system/getoption', 'default-brow', '')
								],
								[
									'id' 					=> 'weight',
									'label'					=> __('Weight', 'teddybearsprompts'),
									'description'			=> __('Product Weight. This will only effect on certificate', 'teddybearsprompts'),
									'type'					=> 'text',
									'default'				=> apply_filters('teddybear/project/system/getoption', 'default-weight', '')
								],
								[
									'id' 					=> 'height',
									'label'					=> __('Height', 'teddybearsprompts'),
									'description'			=> __('Product Height with unit. This will only effect on certificate.', 'teddybearsprompts'),
									'type'					=> 'text',
									'default'				=> apply_filters('teddybear/project/system/getoption', 'default-height', '')
								],
								[
									'id' 					=> 'accessoriesUrl',
									'label'					=> __('Accessories Url', 'teddybearsprompts'),
									'description'			=> __('Accessories url that will be displayed after product is added to the cart.', 'teddybearsprompts'),
									'type'					=> 'text',
									'default'				=> apply_filters('teddybear/project/system/getoption', 'default-accessoriesUrl', '')
								],
								...$this->get_available_badges(),
								// [
								// 	'id' 					=> 'isFeatured',
								// 	'label'					=> __('Featured', 'teddybearsprompts'),
								// 	'description'			=> __('Mark to make it featured product. This won\'t effect search query.', 'teddybearsprompts'),
								// 	'type'					=> 'checkbox',
								// 	'default'				=> false
								// ],
								// [
								// 	'id' 					=> 'isBestSeller',
								// 	'label'					=> __('Best Seller', 'teddybearsprompts'),
								// 	'description'			=> __('Mark to make it Best Seller product. This won\'t effect search query.', 'teddybearsprompts'),
								// 	'type'					=> 'checkbox',
								// 	'default'				=> false
								// ],
								// [
								// 	'id' 					=> 'onSale',
								// 	'label'					=> __('On Sale', 'teddybearsprompts'),
								// 	'description'			=> __('Give here a discount badge text. Leave it blank to not to apear badge. You must set product price as well from woocommerce meta box. Such as "Sale 30%"', 'teddybearsprompts'),
								// 	'type'					=> 'text',
								// 	'default'				=> ''
								// ],
							]
						];
						?>
						<div class="fwp-form">
							<?php foreach($fields['fields'] as $field) {$this->display_field(['field' => $field]);} ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	/**
	 * Save post meta into database
	 * when the post is saved.
	 *
	 * @param integer $post_id Post id.
	 *
	 * @return void
	 */
	public function save_post_meta_data($post_id) {
		global $teddy_Plushies;
		/**
		 * When the post is saved or updated we get $_POST available
		 * Check if the current user is authorized
		 */
		if (! current_user_can('edit_post', $post_id)) {
			return;
		}
		$_key = '_teddy_custom_data';
		if(array_key_exists($_key, $_POST)) {
			update_post_meta($post_id, $_key, $_POST[$_key]);
			/**
			 * Update all accessory items either remove.
			 */
			foreach($teddy_Plushies->get_accessories_terms() as $_key => $_text) {
				if(isset($_POST[$_key])) {
					update_post_meta($post_id, $_key, $_POST[$_key]);
					if(isset($_POST[$_key . '_thumb'])) {
						update_post_meta($post_id, $_key . '_thumb', $_POST[$_key . '_thumb']);
					}
				} else {
					delete_post_meta($post_id, $_key);
					// if(!isset($_POST[$_key . '_thumb'])) {
					// 	delete_post_meta($post_id, $_key . '_thumb', $_POST[$_key . '_thumb']);
					// }
				}
			}
			
		}
		$_key = '_dubido_opened_tab';
		if(array_key_exists($_key, $_POST) && get_post_type($post_id) == 'product') {
			update_post_meta($post_id, $_key, $_POST[$_key]);
		}
	}
	
	public function display_field($args, $isParent = false) {
		$field = wp_parse_args($args['field'], [
			'placeholder'	=> ''
		]);
		$html = '';
		$option_name = ($isParent)?$field['id']:"_teddy_custom_data[" . $field['id'] . "]";
		$field['default'] = isset($field['default'])?$field['default']:'';
		if($isParent) {
			$data = get_post_meta(get_the_ID(), $field['id'], true);
			// $data = ($data && !is_wp_error($data))?$data:$field['default'];
			// $data = ($data && !is_wp_error($data))?$data:$field['default'];

			$data = ($data && !empty($data))?$data:$field['default'];
			
		} else {
			$data = (isset($this->options[$field['id']]))?$this->options[$field['id']]:$field['default'];
		}
		
		switch($field['type']) {
			case 'text':case 'email':case 'password':case 'number':case 'date':case 'time':case 'color':case 'url':
				$html .= '<input id="' . esc_attr($field['id']) . '" type="' . $field['type'] . '" name="' . esc_attr($option_name) . '" placeholder="' . esc_attr($field['placeholder']) . '" value="' . esc_attr($data) . '"' . $this->attributes($field) . '/>' . "\n";
			break;
			case 'text_secret':
				$html .= '<input id="' . esc_attr($field['id']) . '" type="text" name="' . esc_attr($option_name) . '" placeholder="' . esc_attr($field['placeholder']) . '" value="" ' . $this->attributes($field) . '/>' . "\n";
			break;
			case 'textarea':
				$html .= '<textarea id="' . esc_attr($field['id']) . '" rows="5" cols="50" name="' . esc_attr($option_name) . '" placeholder="' . esc_attr($field['placeholder']) . '" ' . $this->attributes($field) . '>' . $data . '</textarea><br/>'. "\n";
			break;
			case 'checkbox':
				$checked = '';
				if(($data && 'on' == $data) || $field['default'] == true) {
					$checked = 'checked="checked"';
				}
				$html .= '<input id="' . esc_attr($field['id']) . '" type="' . $field['type'] . '" name="' . esc_attr($option_name) . '" ' . $checked . ' ' . $this->attributes($field) . '/>' . "\n";
			break;
			case 'checkbox_multi':
				foreach($field['options'] as $k => $v) {
					$checked = false;
					if(is_array($data) && in_array($k, $data)) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr($field['id'] . '_' . $k) . '"><input type="checkbox" ' . checked($checked, true, false) . ' name="' . esc_attr($option_name) . '[]" value="' . esc_attr($k) . '" id="' . esc_attr($field['id'] . '_' . $k) . '" /> ' . $v . '</label> ';
				}
			break;
			case 'radio':
				$html .= ($field['label'] && !empty($field['label']))?'<span style="display: block;">' . $field['label'] . '</span>':'';
				foreach($field['options'] as $k => $v) {
					$checked = false;
					if($k == $data) {$checked = true;}
					if(! $checked && $k == $field['default']) {$checked = true;}
					$html .= '<label for="' . esc_attr($field['id'] . '_' . $k) . '"><input type="radio" ' . checked($checked, true, false) . ' name="' . esc_attr($option_name) . '" value="' . esc_attr($k) . '" id="' . esc_attr($field['id'] . '_' . $k) . '" ' . $this->attributes($field) . '/> ' . $v . '</label> ';
				}
			break;
			case 'select':
				$html .= '<select name="' . esc_attr($option_name) . '" id="' . esc_attr($field['id']) . '" ' . $this->attributes($field) . '>';
				foreach($field['options'] as $k => $v) {
					$selected = ($k == $data);
					if(empty($data) && ! $selected && $k == $field['default']) {$selected = true;}
					$html .= '<option ' . selected($selected, true, false) . ' value="' . esc_attr($k) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
			break;
			case 'select_multi':
				$html .= '<select name="' . esc_attr($option_name) . '[]" id="' . esc_attr($field['id']) . '" multiple="multiple" ' . $this->attributes($field) . '>';
				foreach($field['options'] as $k => $v) {
					$selected = false;
					if(in_array($k, $data)) {
						$selected = true;
					}
					$html .= '<option ' . selected($selected, true, false) . ' value="' . esc_attr($k) . '">' . $v . '</option> ';
				}
				$html .= '</select> ';
			break;
			case 'button':
				$html .= '<button id="' . esc_attr($field['id']) . '" type="button" data-name="' . esc_attr($option_name) . '" data-value="' . esc_attr($data) . '"' . $this->attributes($field) . '>' . $field['text'] . '</button>' . "\n";
			break;
			default:
			break;
		}
		switch($field['type']) {
			case 'checkbox_multi':
			case 'radio':
			case 'select_multi':
				$html .= '<br/><span class="description">' . $field['description'] . '</span>';
			break;
			case 'button':
				$thumbUrl = wp_get_attachment_image_url($data);
				$html .= '<label for="' . esc_attr($field['id']) . '">
					<span class="description">' . $field['description'] . '</span>
					<div class="imgpreview">
						<input type="hidden" value="' . esc_attr($data) . '" name="' . esc_attr($option_name) . '" />
						<img src="' . esc_url($thumbUrl) . '" alt="" />
						<div class="dashicons-before dashicons-dismiss" title="Remove"></div>
					</div>
				</label>';
			break;
			default:
				$html .= '<label for="' . esc_attr($field['id']) . '"><span class="description">' . $field['description'] . '</span></label>';
			break;
		}
		echo '<div class="fwp-form__field fwp-form__field__' . esc_attr($field['type']) . '" data-condition="' . esc_attr(
			json_encode(isset($field['conditions'])?$field['conditions']:[])
		) . '">'.$html.'</div>';
	}
	public function attributes($field) {
		if(! isset($field[ 'attr' ]) || ! is_array($field[ 'attr' ]) || count($field[ 'attr' ]) < 1) {return '';}
		$html = '';
		foreach($field[ 'attr' ] as $attr => $value) {
			$html .= $attr . '="' . $value . '" ';
		}
		return $html;
	}
	public function get_available_badges() {
		$args = [];$filteredData = [];$filteredRow = [];
		foreach((array) TEDDY_BEAR_CUSTOMIZE_ADDON_OPTIONS as $key => $value) {
			if(strpos($key, 'teddy-badge-') !== false) {
				$filteredData[$key] = $value;
			}
		}
		foreach($filteredData as $key => $value) {
			$key = substr($key, 12);$split = explode('-', $key);
			$filteredRow[$split[1]] = isset($filteredRow[$split[1]])?$filteredRow[$split[1]]:[];
			$filteredRow[$split[1]][$split[0]] = $value;
		}
		foreach($filteredRow as $i => $badge) {
			if(!isset($badge['enable']) || empty($badge['enable']) || ! $badge['enable']) {return;}
			$args[] = [
				'id' 					=> 'badge-' . $i,
				'label'					=> sprintf(__('Enable "%s"', 'teddybearsprompts'), $badge['label']),
				'description'			=> sprintf(__('Mark to make this (%s) badge visible to the product card.', 'teddybearsprompts'), '<b>' . $badge['label'] . '</b>'),
				'type'					=> 'checkbox',
				'value'					=> (isset($this->options['badge-' . $i]) && $this->options['badge-' . $i]),
				'default'				=> false // $badge['enable']
			];
		}
		return $args;
	}
}
