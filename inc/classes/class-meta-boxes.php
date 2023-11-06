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
		?>
		<div class="fwp-tabs__container">
			<button class="fwp-button fwppopspopup-open" type="button" <?php echo esc_attr(
				(
					apply_filters('teddybear/project/system/isactive', 'standard-forceglobal') && 
					apply_filters('teddybear/project/system/getoption', 'standard-global', 0) != get_the_ID()
				)?'disabled':''
			); ?>><?php esc_html_e('Customize', 'teddybearsprompts'); ?></button>
		</div>
		<?php
		$this->options = (array) get_post_meta($post->ID, '_teddy_custom_data', true);
		$fields = [
			'title'						=> __('General', 'teddybearsprompts'),
			'description'				=> __('Generel fields comst commonly used to changed.', 'teddybearsprompts'),
			'fields'					=> [
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
				...$this->get_available_badges()
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
			<?php
			foreach($fields['fields'] as $field) {
				$this->display_field(['field' => $field]);
			}
			?>
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
		/**
		 * When the post is saved or updated we get $_POST available
		 * Check if the current user is authorized
		 */
		if (! current_user_can('edit_post', $post_id)) {
			return;
		}
		$_key = '_teddy_custom_data';
		if (array_key_exists($_key, $_POST)) {
			update_post_meta($post_id, $_key, $_POST[$_key]);
		}
	}
	
	public function display_field($args) {
		$field = wp_parse_args($args['field'], [
			'placeholder'	=> ''
		]);
		$html = '';
		$option_name = "_teddy_custom_data[". $field['id']. "]";
		$field['default'] = isset($field['default']) ? $field['default'] : '';
		$data = (isset($this->options[$field['id']]))?$this->options[$field['id']]:$field['default'];
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
		}
		switch($field['type']) {
			case 'checkbox_multi':
			case 'radio':
			case 'select_multi':
				$html .= '<br/><span class="description">' . $field['description'] . '</span>';
			break;
			default:
				$html .= '<label for="' . esc_attr($field['id']) . '"><span class="description">' . $field['description'] . '</span></label>';
			break;
		}
		echo '<div class="fwp-form__field fwp-form__field__' . esc_attr($field['type']) . '">'.$html.'</div>';
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
