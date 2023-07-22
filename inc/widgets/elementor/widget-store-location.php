<?php

/**

 * Elementor widget for Store Location

 *

 * @category	Litivo Listing Site

 * @package TeddyBearCustomizeAddon

 * @author		FutureWordPress.com <info@futurewordpress.com/>

 * @copyright	Copyright (c) 2022-23

 * @link		https://futurewordpress.com/

 * @version		1.3.6

 */

namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;

use Elementor\Widget_Base;

use Elementor\Controls_Manager;

use Elementor\Repeater;

use Elementor\Utils;

if( ! defined( 'ABSPATH' ) ) {exit;} // Exit if accessed directly.



class Widgets_CustomCategory extends \Elementor\Widget_Base {

	/**

	 * Class constructor.

	 *

	 * @param array $data Widget data.

	 * @param array $args Widget arguments.

	 */

	public function __construct( $data = [], $args = null ) {

		parent::__construct( $data, $args );

		// wp_register_style( 'listivo-child-widgets', TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_CSS_URI . '/widgets.css', [], apply_filters( 'futurewordpress/project/quizandfiltersearch/filesystem/filemtime', false, TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_CSS_DIR_PATH . '/widgets.css' ) );

		// wp_register_script( 'swiper', site_url( '/wp-content/plugins/elementor/assets/lib/swiper/swiper.min.js' ), [], apply_filters( 'futurewordpress/project/quizandfiltersearch/filesystem/filemtime', false, ABSPATH . '/wp-content/plugins/elementor/assets/lib/swiper/swiper.min.js' ), true );

		// wp_register_script( 'listivo-child-widgets', TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_JS_URI . '/widgets.js', [ 'swiper' ], apply_filters( 'futurewordpress/project/quizandfiltersearch/filesystem/filemtime', false, TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_JS_DIR_PATH . '/widgets.js' ), true );

	}
quiz-and-filter-search-domain
	/**

	 * Widget key.

	 * 

	 * @return string

	 */

	public function getKey() {

		return 'listivo_store_location';

	}

	/**

	 * Retrieve the widget name.

	 *

	 * @since 1.0.0

	 *

	 * @access public

	 *

	 * @return string Widget name.

	 */

	public function get_name() {

		return 'listivo_store_location';

	}

	/**

	 * Retrieve the widget title.

	 *

	 * @since 1.0.0

	 *

	 * @access public

	 *
quiz-and-filter-search-domain
	 * @return string Widget title.

	 */

	public function get_title() {
quiz-and-filter-search-domain
		return __( 'Store Location', 'quiz-and-filter-search-domain' );

	}

	/**

	 * Retrieve the widget icon.

	 *

	 * @since 1.0.0

	 *

	 * @access public

	 *

	 * @return string Widget icon.

	 */

	public function get_icon() {

		return esc_url( 'https://www.svgrepo.com/show/55743/slider.svg' );

	}

	/**

	 * Retrieve the list of categories the widget belongs to.

	 *

	 * Used to determine where to display the widget in the editor.

	 *

	 * Note that currently Elementor supports only one category.

	 * When multiple categories passed, Elementor uses the first one.

	 *

	 * @since 1.0.0

	 *

	 * @access public

	 *

	 * @return array Widget categories.

	 */

	public function get_categories() {

		return [ 'general' ];

	}

	

	/**

	 * Enqueue styles.

	 */

	public function get_style_depends() {

		return []; // return [ 'listivo-child-widgets' ];

	}

	public function get_script_depends() {

		return []; // [ 'swiper', 'listivo-child-widgets' ];

	}

	/**

	 * Register the widget controls.

	 *

	 * Adds different input fields to allow the user to change and customize the widget settings.

	 *

	 * @since 1.0.0

	 *

	 * @access protected

	 */

	protected function register_controls() {

		$this->start_controls_section(

			'section_content',

			array(

				'label' => __( 'Content', 'quiz-and-filter-search-domain' ),

			)

		);

		$this->add_control(

			'show_the_map',

			[

				'label' => esc_html__('Enable Map', 'quiz-and-filter-search-domain'),

				'type' => Controls_Manager::SWITCHER,

				'return_value' => '1',

				'default' => '1',

			]

		);

		$this->end_controls_section();

	}

	/**

	 * Render the widget output on the frontend.

	 *

	 * Written in PHP and used to generate the final HTML.

	 *

	 * @since 1.0.0

	 *

	 * @access protected

	 */

	protected function render() {

		$settings = $this->get_settings_for_display();

		if( $settings[ 'show_the_map' ] != '1' ) {return;}

		// print_r( $settings );

		// $this->add_inline_editing_attributes( 'view_all_title', 'none' );

		// $this->add_inline_editing_attributes( 'content', 'basic' );

		// $this->add_inline_editing_attributes( 'view_all_text', 'advanced' );

		// $settings[ 'view_all_image' ] = $settings[ 'view_all_image' ] ?? [];

		// $image = $settings[ 'view_all_image' ][ 'url' ] ?? false;

		$mapConfig = [];

		?>

		<div class="FutureWordPressScratchProjectchildmap" data-map-config="<?php echo esc_attr( json_encode( $mapConfig ) ); ?>" data-map-reload="true"></div>

		<?php

	}

	/**

	 * Render the widget output in the editor.

	 *

	 * Written as a Backbone JavaScript template and used to generate the live preview.

	 *

	 * @since 1.0.0

	 *

	 * @access protected

	 */

	protected function _content_template() {

		// <# if ( settings.show_the_map == '1' ) { #>has-view_all<# } else { #>no-view_all<# } #>

		// settings.view_all_image

		// view.addInlineEditingAttributes( 'view_all_title', 'none' );

		?>

		<# if ( settings.show_the_map == '1' ) { #>

			<div class="FutureWordPressScratchProjectchildmap" data-map-config="{{{json_encode( $mapConfig )}}}" data-map-reload="true"></div>

		<# } #>

		<?php

	}

}

// end of line

?>