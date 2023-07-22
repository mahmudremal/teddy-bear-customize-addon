<?php
/**
 * Elementor widget for Store Location
 *
 * @category	WordPress Plugin
 * @package TeddyBearCustomizeAddon
 * @author		FutureWordPress.com <info@futurewordpress.com/>
 * @copyright	Copyright (c) 2022-23
 * @link		https://futurewordpress.com/
 * @version		1.3.6
 * phpcs:disable PEAR.NamingConventions.ValidClassName
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc\Widget;

class VOICE_UPLOAD extends \THEMECOMPLETE_EPO_BUILDER_ELEMENT {

	/**
	 * Class Constructor
	 *
	 * @param string $name The element name.
	 * @since 6.0
	 */
	public function __construct( $name = '' ) {
		$this->element_name     = $name;
		$this->is_addon         = false;
		$this->namespace        = $this->elements_namespace;
		$this->name             = esc_html__( 'Voice', 'woocommerce-tm-extra-product-options' );
		$this->description      = '';
		$this->width            = 'w100';
		$this->width_display    = '100%';
		$this->icon             = 'tcfa-upload';
		$this->is_post          = 'post';
		$this->type             = 'single';
		$this->post_name_prefix = 'upload';
		$this->fee_type         = 'single';
		$this->tags             = 'price content';
		$this->show_on_backend  = true;
	}

	/**
	 * Initialize element properties
	 *
	 * @since 6.0
	 */
	public function set_properties() {
		$this->properties = $this->add_element(
			$this->element_name,
			[
				'enabled',
				'required',
				'price_type5',
				'lookuptable',
				'price',
				'sale_price',
				'fee',
				'hide_amount',
				'text_before_price',
				'text_after_price',
				'button_type',
			]
		);
	}
}

