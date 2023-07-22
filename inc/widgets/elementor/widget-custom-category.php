<?php

/**

 * Elementor widget for youtube playlist displaying

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

		wp_register_style( 'listivo-child-widgets', TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_CSS_URI . '/widgets.css', [], apply_filters( 'futurewordpress/project/quizandfiltersearch/filesystem/filemtime', false, TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_CSS_DIR_PATH . '/widgets.css' ) );

		wp_register_script( 'swiper', site_url( '/wp-content/plugins/elementor/assets/lib/swiper/swiper.min.js' ), [], apply_filters( 'futurewordpress/project/quizandfiltersearch/filesystem/filemtime', false, ABSPATH . '/wp-content/plugins/elementor/assets/lib/swiper/swiper.min.js' ), true );

		wp_register_script( 'listivo-child-widgets', TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_JS_URI . '/widgets.js', [ 'swiper' ], apply_filters( 'futurewordpress/project/quizandfiltersearch/filesystem/filemtime', false, TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_JS_DIR_PATH . '/widgets.js' ), true );

	}
quiz-and-filter-search-domain
	/**

	 * Widget key.

	 * 

	 * @return string

	 */

	public function getKey() {

		return 'listivo_categories_custom_slider';

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

		return 'listivo_categories_custom_slider';

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
		return __( 'Custom Category Slider', 'quiz-and-filter-search-domain' );

	}

	/**

	 * Retrieve the widget icon.

	 *quiz-and-filter-search-domain

	 * @since 1.0.0
quiz-and-filter-search-domain
	 *quiz-and-filter-search-domain

	 * @access public

	 *

	 * @return string Widget icon.

	 */

	public function get_icon() {
quiz-and-filter-search-domain
		return esc_url( 'https://www.svgrepo.com/show/55743/slider.svg' );
quiz-and-filter-search-domain
	}

	/**

	 * Retrieve the list of categories the widget belongs to.

	 *

	 * Used to determine where to display thequiz-and-filter-search-domainor.

	 *

	 * Note that currently Elementor supports only one category.

	 * When multiple categories passed, Elementor uses the first one.

	 *
quiz-and-filter-search-domain
	 * @since 1.0.0

	 *

	 * @access public

	 *

	 * @return array Widget categories.

	 */quiz-and-filter-search-domain

	public function get_categories() quiz-and-filter-search-domain

		return [ 'general' ];

	}

	

	/**
quiz-and-filter-search-domain
	 * Enqueue styles.

	 */

	public function get_style_depends() {

		return [ 'listivo-child-widgets' ];

	}

	public function get_script_depends() quiz-and-filter-search-domain

		return [ 'swiper', 'listivo-child-widgets' ];

	}

	/**

	 * Register the widget controls.

	 *

	 * Adds different input fields to allow the user to change and customize the widget settings.

	 *

	 * @since 1.0.0

	 *

	 * @access protected
quiz-and-filter-search-domain
	 */

	protected function register_controls() {

		$this->start_controls_section(

			'section_content',quiz-and-filter-search-domain

			array(

				'label' => __( 'Content', 'quiz-and-filter-search-domain' ),

			)

		);

		$this->add_control(quiz-and-filter-search-domain

			'show_view_all',quiz-and-filter-search-domain

			[

				'label' => esc_html__('Display "View All" button', 'quiz-and-filter-search-domain'),

				'type' => Controls_Manager::SWITCHER,quiz-and-filter-search-domain

				'return_value' => '1',quiz-and-filter-search-domain

				'default' => '1',

			]

		);quiz-and-filter-search-domain

		$this->add_control(

			'view_all_style',

			[
quiz-and-filter-search-domain
				'label' => esc_html__('Style', 'quiz-and-filter-search-domain'),

				'type' => Controls_Manager::SELECT,

				'options' => [

					'left' => esc_html__('Left', 'quiz-and-filter-search-domain'),

					'right' => esc_html__('Right', 'quiz-and-filter-search-domain'),

				],

				'default' => 'left',

				'condition' => [

					'show_view_all' => '1',

				]

			]

		);

		$this->add_control(

			'view_all_title',

			[

				'label'   => __( 'Heading Text', 'quiz-and-filter-search-domain' ),

				'type'    => Controls_Manager::TEXT,

				'default' => __( 'Heading Text', 'quiz-and-filter-search-domain' ),

				'condition' => [

					'show_view_all' => '1',

				]

			]

		);

		$this->add_control(

			'show_viewall_btn',

			[

				'label' => esc_html__('Show button', 'quiz-and-filter-search-domain'),

				'type' => Controls_Manager::SWITCHER,

				'return_value' => '1',

				'default' => '1',

			]

		);

		$this->add_control(

			'view_all_text',

			[

				'label' => esc_html__('View All Text', 'quiz-and-filter-search-domain'),

				'type' => Controls_Manager::WYSIWYG, // Controls_Manager::TEXTAREA,

				'default' => "Didn't find the right category?",

				'condition' => [

					'show_view_all' => '1',

				]

			]

		);

		$this->add_control(

			'view_all_btntxt',

			[

				'label'   => __( 'Button Text', 'quiz-and-filter-search-domain' ),

				'type'    => Controls_Manager::TEXT,

				'default' => __( 'View all', 'quiz-and-filter-search-domain' ),

				'condition' => [

					'show_view_all' => '1',

				]

			]

		);

		$this->add_control(

			'view_all_link',

			[

				'label'   => __( 'View all button link', 'quiz-and-filter-search-domain' ),

				'type'    => Controls_Manager::TEXT,

				'default' => '',

				'condition' => [

					'show_view_all' => '1',

				]

			]

		);

		$this->add_control(

				'view_all_image',

				[

						'label' => esc_html__('Image', 'quiz-and-filter-search-domain'),

						'type' => Controls_Manager::MEDIA,

						'condition' => [

								'show_view_all' => '1',

								'view_all_style' => ['left', 'right'],

						]

				]

		);

		$this->add_group_control(

			\Elementor\Group_Control_Background::get_type(),

			[

				'name' => 'bg',

				'types' => [ 'classic', 'gradient', 'video' ],

				'selector' => '{{WRAPPER}} .fwp-custom-category-rightside',

			]

		);

		$this->end_controls_section();



		$this->start_controls_section(

			'section_slider',

			array(

				'label' => __( 'Slider', 'quiz-and-filter-search-domain' ),

			)

		);

		$repeater = new Repeater();

		$repeater->add_control(

			'image',

			[

				'label' => esc_html__('Image', 'quiz-and-filter-search-domain'),

				'type' => Controls_Manager::MEDIA,

				'default' => [

					'url' => Utils::get_placeholder_image_src(),

				],

			]

		);

		$repeater->add_control(

			'label',

			[

				'label' => esc_html__('Category Label', 'quiz-and-filter-search-domain'),

				'type' => Controls_Manager::TEXT,

				'placeholder' => __( 'Title Here', 'quiz-and-filter-search-domain' ),

			]

		);

		$repeater->add_control(

			'link',

			[

				'label' => esc_html__('Category URL', 'quiz-and-filter-search-domain'),

				'type' => Controls_Manager::TEXT,

				'placeholder' => __( 'Full URL of this category.', 'quiz-and-filter-search-domain' ),

			]

		);

		$repeater->add_control(

			'color',

			[

				'label' => esc_html__('Background Color', 'quiz-and-filter-search-domain'),

				'type' => Controls_Manager::COLOR,

			]

		);

		$this->add_control(

			'categories',

			[

				'label' => esc_html__('Category Items', 'quiz-and-filter-search-domain'),

				'type' => Controls_Manager::REPEATER,

				'fields' => $repeater->get_controls(),

				'prevent_empty' => false,

				'title_field'		=> '{{{label}}}'

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

		// print_r( $settings );

		$this->add_inline_editing_attributes( 'view_all_title', 'none' );

		$this->add_inline_editing_attributes( 'content', 'basic' );

		$this->add_inline_editing_attributes( 'view_all_text', 'advanced' );

		$settings[ 'view_all_image' ] = $settings[ 'view_all_image' ] ?? [];

		$image = $settings[ 'view_all_image' ][ 'url' ] ?? false;

		$lazyload = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAMAAAACCAQAAAA3fa6RAAAADklEQVR42mNkAANGCAUAACMAA2w/AMgAAAAASUVORK5CYII=';

		?>

		<div class="fwp-widget-custom-category <?php echo esc_attr( ( $settings[ 'view_all_style' ] == 'left' ) ? 'show-view-all-first' : '' ); ?>">

			<div class="listivo-categories-v1 listivo-swiper-slider <?php echo esc_attr( ( $settings[ 'show_view_all' ] == '1' ) ? 'has-view_all' : 'no-view_all' ); ?>">

				<div class="swiper-container">

					<div class="swiper-wrapper">

						<?php foreach( $settings[ 'categories' ] as $index => $item ) : ?>

							<a class="listivo-category-v1 swiper-slide" href="<?php echo esc_attr( $item[ 'link' ] ); ?>" style="background-color: <?php echo esc_attr( $item[ 'color' ] ); ?>;">

								<div class="listivo-child-category-overaly-wrap">

									<div class="category-sider-row">

										<div class="category-left-sliders">

											<span><?php echo esc_html( $item[ 'label' ] ); ?></span>

										</div>

										<div class="category-right-sliders">

											<img class="lazyload" src="<?php echo esc_attr( $lazyload ); ?>" alt="<?php echo esc_attr( $item[ 'label' ] ); ?>" data-src="<?php echo esc_attr( isset( $item[ 'image' ][ 'url' ] ) ? $item[ 'image' ][ 'url' ] : $lazyload ); ?>">

										</div>

									</div>

								</div>

							</a>

						<?php endforeach; ?>

					</div>

					<div class="slider-controls">

						<!-- <div class="next-ctrl"></div>

						<div class="prev-ctrl"></div> -->

						<!-- If we need pagination -->

						<div class="swiper-pagination"></div>

						<!-- If we need navigation buttons -->

						<div class="swiper-button-prev">

							<svg id="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30">

								<path id="chevron" d="M11.91,22.29a1.08,1.08,0,0,1-.78-.33,1.1,1.1,0,0,1,0-1.57L16.52,15,11.13,9.61A1.11,1.11,0,1,1,12.7,8l6.18,6.17a1.14,1.14,0,0,1,0,1.58L12.7,22A1.11,1.11,0,0,1,11.91,22.29Z"></path>

							</svg>

						</div>

						<div class="swiper-button-next">

							<svg id="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30">

								<path id="chevron" d="M11.91,22.29a1.08,1.08,0,0,1-.78-.33,1.1,1.1,0,0,1,0-1.57L16.52,15,11.13,9.61A1.11,1.11,0,1,1,12.7,8l6.18,6.17a1.14,1.14,0,0,1,0,1.58L12.7,22A1.11,1.11,0,0,1,11.91,22.29Z"></path>

							</svg>

						</div>

						<!-- If we need scrollbar -->

						<div class="swiper-scrollbar"></div>

					</div>

				</div>

			</div>

			<?php if( $settings[ 'show_view_all' ] == '1' ) : ?>

			<div class="fwp-custom-category-rightside" style="<?php echo esc_attr( 'background-image: url(' . $image . ')' ); ?>">

				<div class="listivo-category-v1 listivo-category-v1--view-all listivo-category-v1--view-all-style-2 <?php echo esc_attr( ( $image ) ? 'has-background' : '' ); ?>">

					<div class="listivo-category-v1__circle listivo-category-v1__circle--first"></div>

					<div class="listivo-category-v1__circle listivo-category-v1__circle--second"></div>

					<div class="listivo-category-v1__plus">

						<svg xmlns="http://www.w3.org/2000/svg" width="28" height="23" viewBox="0 0 28 23" fill="none">

							<path d="M0.43225 11.184L0.43225 6L27.5683 6V11.184H0.43225ZM11.3763 -5.648L16.6883 -5.648V22.832H11.3763L11.3763 -5.648Z" fill="#FDFDFE" fill-opacity="0.95" />

						</svg>

					</div>

					<div class="listivo-category-v1__x">

						<svg xmlns="http://www.w3.org/2000/svg" width="31" height="31" viewBox="0 0 31 31" fill="none">

							<path d="M9.76705 24.1579L7.01782 21.4086L21.4089 7.0176L24.1581 9.76684L9.76705 24.1579ZM6.64446 9.42742L9.46158 6.61031L24.5654 21.7141L21.7483 24.5312L6.64446 9.42742Z" fill="#FDFDFE" fill-opacity="0.95" />

						</svg>

					</div>

					<div class="listivo-category-v1__view-all" <?php echo $this->get_render_attribute_string( 'view_all_title' ); ?>><?php echo wp_kses( $settings['view_all_title'], [] ); ?>

					<br/><br/>

						<small <?php echo $this->get_render_attribute_string( 'view_all_text' ); ?>><?php echo wp_kses( $settings['view_all_text'], [] ); ?></small>

						<?php if( $settings[ 'show_viewall_btn' ] == '1' ) : ?>

						<div class="listivo-category-v1__button">

							<a class="listivo-button listivo-button--primary-1" href="<?php echo esc_url( $settings['view_all_link'] ); ?>" <?php echo $this->get_render_attribute_string( 'view_all_link' ); ?>>

								<span <?php echo $this->get_render_attribute_string( 'view_all_btntxt' ); ?>> <?php echo esc_html( $settings['view_all_btntxt'] ); ?> <svg xmlns="http://www.w3.org/2000/svg" width="12" height="11" viewBox="0 0 12 11" fill="none">

										<path d="M7.13805 10.4713C7.00772 10.6017 6.83738 10.6667 6.66671 10.6667C6.49605 10.6667 6.32571 10.6017 6.19538 10.4713C5.93504 10.211 5.93504 9.78898 6.19538 9.52865L9.72407 5.99996H0.666672C0.298669 5.99996 0 5.70129 0 5.33329C0 4.96528 0.298669 4.66662 0.666672 4.66662H9.72407L6.19538 1.13792C5.93504 0.877589 5.93504 0.455586 6.19538 0.195251C6.45571 -0.0650838 6.87771 -0.0650838 7.13805 0.195251L11.8047 4.86195C12.0651 5.12229 12.0651 5.54429 11.8047 5.80462L7.13805 10.4713Z" fill="#FDFDFE" />

									</svg>

								</span>

							</a>

						</div>

						<?php endif; ?>

					</div>

				</div>

			</div>

			<?php endif; ?>

		</div>

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

		?>

		<#

		view.addInlineEditingAttributes( 'view_all_title', 'none' );

		view.addInlineEditingAttributes( 'view_all_image', 'basic' );

		view.addInlineEditingAttributes( 'view_all_text', 'advanced' );

		view.addInlineEditingAttributes( 'view_all_link', 'none' );

		settings.view_all_image = settings.view_all_image ?? [];

		settings.view_all_image.url = settings.view_all_image.url ?? '';

		var lazyload = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAMAAAACCAQAAAA3fa6RAAAADklEQVR42mNkAANGCAUAACMAA2w/AMgAAAAASUVORK5CYII=';

		#>

		<div class="fwp-widget-custom-category <# if ( settings.view_all_style == 'left' ) { #>show-view-all-first<# } #>">

			<div class="listivo-categories-v1 listivo-swiper-slider <# if ( settings.show_view_all == '1' ) { #>has-view_all<# } else { #>no-view_all<# } #>">

				<div class="swiper-container">

					<div class="swiper-wrapper">

						<# _.each( settings.categories, function( item, index ) {

							var repeater_setting_key = view.getRepeaterSettingKey( 'text', 'categories', index );

							view.addRenderAttribute( repeater_setting_key, 'class', 'elementor-list-widget-text' );

							view.addInlineEditingAttributes( repeater_setting_key );

							#>

							<a class="listivo-category-v1 swiper-slide" href="{{{item.link}}}" style="background-color: {{{item.color}}};">

								<div class="listivo-child-category-overaly-wrap">

									<div class="category-sider-row">

										<div class="category-left-sliders">

											<span>{{{item.label}}}</span>

										</div>

										<div class="category-right-sliders">

											<img class="lazyload" src="{{{lazyload}}}" alt="{{{item.label}}}" data-src="<# if ( item.image.url ) { #>{{{item.image.url}}}<# } else { #>{{{lazyload}}}<# } #>">

										</div>

									</div>

								</div>

							</a>

						<# } ); #>

					</div>

					<div class="slider-controls">

						<div class="next-ctrl"></div>

						<div class="prev-ctrl"></div>

						<!-- If we need pagination -->

						<div class="swiper-pagination"></div>

						<!-- If we need navigation buttons -->

						<div class="swiper-button-prev"></div>

						<div class="swiper-button-next"></div>

						<!-- If we need scrollbar -->

						<div class="swiper-scrollbar"></div>

					</div>

				</div>

			</div>

			<# if ( settings.show_view_all == '1' ) { #>

			<div class="fwp-custom-category-rightside" style="<# if ( settings.view_all_image.url !== false ) { #>background-image: url({{{ settings.view_all_image.url }}})<# } #>">

				<div class="listivo-category-v1 listivo-category-v1--view-all listivo-category-v1--view-all-style-2 <# if ( settings.view_all_image.url !== false ) { #>has-background<# } #>">

					<div class="listivo-category-v1__circle listivo-category-v1__circle--first"></div>

					<div class="listivo-category-v1__circle listivo-category-v1__circle--second"></div>

					<div class="listivo-category-v1__plus">

						<svg xmlns="http://www.w3.org/2000/svg" width="28" height="23" viewBox="0 0 28 23" fill="none">

							<path d="M0.43225 11.184L0.43225 6L27.5683 6V11.184H0.43225ZM11.3763 -5.648L16.6883 -5.648V22.832H11.3763L11.3763 -5.648Z" fill="#FDFDFE" fill-opacity="0.95" />

						</svg>

					</div>

					<div class="listivo-category-v1__x">

						<svg xmlns="http://www.w3.org/2000/svg" width="31" height="31" viewBox="0 0 31 31" fill="none">

							<path d="M9.76705 24.1579L7.01782 21.4086L21.4089 7.0176L24.1581 9.76684L9.76705 24.1579ZM6.64446 9.42742L9.46158 6.61031L24.5654 21.7141L21.7483 24.5312L6.64446 9.42742Z" fill="#FDFDFE" fill-opacity="0.95" />

						</svg>

					</div>

					<div class="listivo-category-v1__view-all" {{{ view.getRenderAttributeString( 'view_all_title' ) }}}>{{{ settings.view_all_title }}}

					<br/><br/>

						<small {{{ view.getRenderAttributeString( 'view_all_text' ) }}}>{{{ settings.view_all_text }}}</small>

						<# if ( settings.show_viewall_btn == '1' ) { #>

						<div class="listivo-category-v1__button">

							<a class="listivo-button listivo-button--primary-1" href="{{{ settings.view_all_link }}}" {{{ view.getRenderAttributeString( 'view_all_link' ) }}}>

								<span {{{ view.getRenderAttributeString( 'view_all_btntxt' ) }}}> {{{ settings.view_all_btntxt }}} <svg xmlns="http://www.w3.org/2000/svg" width="12" height="11" viewBox="0 0 12 11" fill="none">

										<path d="M7.13805 10.4713C7.00772 10.6017 6.83738 10.6667 6.66671 10.6667C6.49605 10.6667 6.32571 10.6017 6.19538 10.4713C5.93504 10.211 5.93504 9.78898 6.19538 9.52865L9.72407 5.99996H0.666672C0.298669 5.99996 0 5.70129 0 5.33329C0 4.96528 0.298669 4.66662 0.666672 4.66662H9.72407L6.19538 1.13792C5.93504 0.877589 5.93504 0.455586 6.19538 0.195251C6.45571 -0.0650838 6.87771 -0.0650838 7.13805 0.195251L11.8047 4.86195C12.0651 5.12229 12.0651 5.54429 11.8047 5.80462L7.13805 10.4713Z" fill="#FDFDFE" />

									</svg>

								</span>

							</a>

						</div>

						<# } #>

					</div>

				</div>

			</div>

			<# } #>

		</div>

		<?php

	}

}

// end of line

?>