<?php
/**
 * Option Page Render.
 * https://github.com/jeremyHixon/RationalOptionPages
 * 
 * @package ESignBindingAddons
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
class Option {
  use Singleton;
  private $dir;
  private $file;
	private $plugin_name;
	private $plugin_slug;
	private $textdomain;
	private $options;
	private $settings;
	private $general;
	protected function __construct() {
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		$this->file = TEDDY_BEAR_CUSTOMIZE_ADDON__FILE__;
		$this->plugin_slug = 'teddybearsprompts';
		$this->plugin_name = 'teddybearsprompts';
		$this->textdomain = 'teddybearsprompts'; // str_replace('_', '-', $plugin_slug);
		// Initialise settings
		add_action('init', [$this, 'init']);
		add_action('admin_init', array($this, 'admin_init'));
		// Add settings page to menu
		add_action('admin_menu' , array($this, 'add_menu_item'));
		// Add settings link to plugins page
		add_filter('plugin_action_links_' . plugin_basename($this->file) , array($this, 'add_settings_link'));

		add_filter('teddybear/project/system/getoption', [$this, 'getOption'], 10, 2);
		add_filter('teddybear/project/system/isactive', [$this, 'isActive'], 10, 1);
	}
	/**
	 * Initialise settings
	 * @return void
	 */
	public function init() {
		$this->general = (object) wp_parse_args(apply_filters('teddybear/project/settings/general', []), [
			'page_title'					=> __('Popup Configuration.', 'teddybearsprompts'),
			'menu_title'					=> __('Customized Popup', 'teddybearsprompts'),
			'role'							=> 'manage_options',
			'slug'							=> $this->plugin_slug,
			'page_header'					=> __('Teddy bear customization popup.', 'teddybearsprompts'),
			'page_subheader'				=> __('Customization popup configuration screen. Default values and truning on/off can be done from here.', 'teddybearsprompts'),
			'no_password'					=> __('A password is required.', 'teddybearsprompts'),
		]);
	}
	public function admin_init() {
		$this->settings = $this->settings_fields();
		$this->options = $this->get_options();
		$this->register_settings();
	}
	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item() {
		$page = add_options_page($this->general->page_title, $this->general->menu_title, $this->general->role, $this->general->slug,  [$this, 'settings_page']);
	}
	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link($links) {
		$settings_link = '<a href="options-general.php?page='.$this->general->slug.'">' . __('Settings', 'teddybearsprompts') . '</a>';
		// array_push($links, $settings_link);
		$links[] = $settings_link;
		// $links[] = '<a href="https://www.fiverr.com/mahmud_remal/" target="_blank">' . __('Developer Support', 'teddybearsprompts') . '</a>';
		return $links;
	}
	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields() {
		$settings = [];
		$settings = apply_filters('teddybear/project/settings/fields', $settings);
		return $settings;
	}
	/**
	 * Options getter
	 * @return array Options, either saved or default ones.
	 */
	public function get_options() {
		$options = get_option($this->general->slug);
		if(!$options && is_array($this->settings)) {
			$options = Array();
			foreach($this->settings as $section => $data) {
				foreach($data['fields'] as $field) {
					$options[$field['id']] = $field['default'];
				}
			}
			add_option($this->general->slug, $options);
		}
		return $options;
	}
	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings() {
		if(is_array($this->settings)) {
			register_setting($this->general->slug, $this->general->slug, array($this, 'validate_fields'));
			foreach($this->settings as $section => $data) {
				// Add section to page
				add_settings_section($section, $data['title'], array($this, 'settings_section'), $this->general->slug);
				foreach($data['fields'] as $field) {
					// Add field to page
					add_settings_field($field['id'], $field['label'], array($this, 'display_field'), $this->general->slug, $section, array('field' => $field));
				}
			}
		}
	}
	public function settings_section($section) {
		$html = '<p> ' . $this->settings[$section['id']]['description'] . '</p>' . "\n";
		echo $html;
	}
	/**
	 * Generate HTML for displaying fields
	 * @param  array $args Field data
	 * @return void
	 */
	public function display_field($args) {
		$field = wp_parse_args($args['field'], [
			'placeholder'	=> ''
		]);
		$html = '';
		$option_name = $this->general->slug ."[". $field['id']. "]";
		$field['default'] = isset($field['default'])?$field['default']:'';
		$data = (isset($this->options[$field['id']]))?$this->options[$field['id']]:$field['default'];
		switch($field['type']) {
			case 'text':
			case 'button':
			case 'email':
			case 'password':
			case 'number':
			case 'date':
			case 'time':
			case 'color':
			case 'url':
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
				$html .= apply_filters('teddybear/project/settings/fields/label', '<br/><span class="description">' . $field['description'] . '</span>', $field);
			break;
			default:
				$html .= apply_filters('teddybear/project/settings/fields/label', '<label for="' . esc_attr($field['id']) . '"><span class="description">' . $field['description'] . '</span></label>' . "\n", $field);
			break;
		}
		echo $html;
	}
	/**
	 * Validate individual settings field
	 * @param  array $data Inputted value
	 * @return array       Validated value
	 */
	public function validate_fields($data) {
		// $data array contains values to be saved:
		// either sanitize/modify $data or return false
		// to prevent the new options to be saved
		// Sanitize fields, eg. cast number field to integer
		// $data['number_field'] = (int) $data['number_field'];
		// Validate fields, eg. don't save options if the password field is empty
		// if($data['password_field'] == '') {
		// 	add_settings_error($this->general->slug, 'no-password', $this->general->no_password, 'error');
		// 	return false;
		// }
		return $data;
	}
	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page() {
		// Build page HTML output
		// If you don't need tabbed navigation just strip out everything between the <!-- Tab navigation --> tags.
		?>
	  <div class="wrap" id="<?php echo $this->general->slug; ?>">
	  	<h2><?php echo wp_kses_post($this->general->page_header); ?></h2>
	  	<p><?php echo wp_kses_post($this->general->page_subheader); ?></p>
			<!-- Tab navigation starts -->
			<h2 class="nav-tab-wrapper settings-tabs hide-if-no-js">
				<?php
				foreach($this->settings as $section => $data) {
					echo '<a href="#' . $section . '" class="nav-tab">' . $data['title'] . '</a>';
				}
				?>
			</h2>
			<?php $this->do_script_for_tabbed_nav(); ?>
			<!-- Tab navigation ends -->
			<form action="options.php" method="POST">
						<?php settings_fields($this->general->slug); ?>
						<div class="settings-container">
						<?php do_settings_sections($this->general->slug); ?>
					</div>
						<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
	/**
	 * Print jQuery script for tabbed navigation
	 * @return void
	 */
	private function do_script_for_tabbed_nav() {
		// Very simple jQuery logic for the tabbed navigation.
		// Delete this function if you don't need it.
		// If you have other JS assets you may merge this there.
		?>
		<script>
		jQuery(document).ready(function($) {
			var headings = jQuery('.settings-container > h2, .settings-container > h3');
			var paragraphs  = jQuery('.settings-container > p');
			var tables = jQuery('.settings-container > table');
			var triggers = jQuery('.settings-tabs a');
			triggers.each(function(i){
				triggers.eq(i).on('click', function(e){
					e.preventDefault();
					triggers.removeClass('nav-tab-active');
					headings.hide();
					paragraphs.hide();
					tables.hide();
					triggers.eq(i).addClass('nav-tab-active');
					headings.eq(i).show();
					paragraphs.eq(i).show();
					tables.eq(i).show();
				});
			})
			triggers.eq(0).click();
		});
		</script>
	<?php
	}
	public function attributes($field) {
		if(! isset($field['attr']) || ! is_array($field['attr']) || count($field['attr']) < 1) {return '';}
		$html = '';
		foreach($field['attr'] as $attr => $value) {
			$html .= $attr . '="' . $value . '" ';
		}
		return $html;
	}
	
	/**
	 * Get and option value, return default. Default false.
	 * 
	 * @return string
	 */
	public function getOption($option, $default) {
		return isset(TEDDY_BEAR_CUSTOMIZE_ADDON_OPTIONS[$option])?TEDDY_BEAR_CUSTOMIZE_ADDON_OPTIONS[$option]:$default;
	}
	/**
	 * Check if is active or not.
	 * 
	 * @return bool
	 */
	public function isActive($option) {
		return (isset(TEDDY_BEAR_CUSTOMIZE_ADDON_OPTIONS[$option]) && TEDDY_BEAR_CUSTOMIZE_ADDON_OPTIONS[$option] == 'on');
	}
}