<?php
/**
 * Register Custom Taxonomies
 *
 * @package TeddyBearCustomizeAddon
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
class Taxonomies {
	use Singleton;
	protected function __construct() {
		// load class.
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		/**
		 * Actions.
		 */
		// add_action( 'init', [ $this, 'create_genre_taxonomy' ] );
		$this->mediaConfig = [
			'title'						=> __( 'Select Icon. Recomment to keep image around of 40px/40px.', 'teddybearsprompts' ),
			'library'					=> [
				'type'					=> 'image'
			],
			'button'					=> [
				'text'					=> __( 'Use this Icon', 'teddybearsprompts' ),
			],
			'multiple'				=> false
		];
		$taxonomy = 'listing_category';
		add_action( "{$taxonomy}_add_form_fields", [ $this, "{$taxonomy}_add_form_fields" ], 10, 1 );
		add_action( "{$taxonomy}_edit_form_fields", [ $this, "{$taxonomy}_edit_form_fields" ], 10, 2 );
		// add_action( "created_{$taxonomy}", [ $this, "save_{$taxonomy}" ], 10, 1 );
		// add_action( "edited_{$taxonomy}", [ $this, "save_{$taxonomy}" ], 10, 1 );
	}
	// Register Taxonomy Genre
	public function create_genre_taxonomy() {
		$labels = [
			'name'              => _x( 'Genres', 'taxonomy general name', 'teddybearsprompts' ),
			'singular_name'     => _x( 'Genre', 'taxonomy singular name', 'teddybearsprompts' ),
			'search_items'      => __( 'Search Genres', 'teddybearsprompts' ),
			'all_items'         => __( 'All Genres', 'teddybearsprompts' ),
			'parent_item'       => __( 'Parent Genre', 'teddybearsprompts' ),
			'parent_item_colon' => __( 'Parent Genre:', 'teddybearsprompts' ),
			'edit_item'         => __( 'Edit Genre', 'teddybearsprompts' ),
			'update_item'       => __( 'Update Genre', 'teddybearsprompts' ),
			'add_new_item'      => __( 'Add New Genre', 'teddybearsprompts' ),
			'new_item_name'     => __( 'New Genre Name', 'teddybearsprompts' ),
			'menu_name'         => __( 'Genre', 'teddybearsprompts' ),
		];
		$args   = [
			'labels'             => $labels,
			'description'        => __( 'Movie Genre', 'teddybearsprompts' ),
			'hierarchical'       => true,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => true,
			'show_tagcloud'      => true,
			'show_in_quick_edit' => true,
			'show_admin_column'  => true,
			'show_in_rest'       => true,
		];
		register_taxonomy( 'genre', [ 'movies' ], $args );
	}

	public function listing_category_add_form_fields( $taxonomy ) {
		$this->listing_category_form_fields__popupspopup( false, $taxonomy );
	}
	public function listing_category_edit_form_fields( $term, $taxonomy ) {
		// 
		?>
		<tr class="form-field form-requi-red term-name-wrap">
			<th scope="row"><label><?php esc_html_e( 'Setup Popups', 'teddybearsprompts' ); ?></label></th>
			<td>
				<?php $this->listing_category_form_fields__popupspopup( $term, $taxonomy ); ?>
				<p class="description"><?php esc_html_e( 'Setup your popup and it\'s related data by clicking this button.', 'teddybearsprompts' ); ?></p>
			</td>
		</tr>
		<?php
	}
	private function listing_category_form_fields__popupspopup( $term, $taxonomy ) {
		?>
			<div class="fwppopspopup-button-wrap">
				<button type="button" class="button btn fwppopspopup-open" title="<?php esc_attr_e( 'Customize Popup', 'teddybearsprompts' ); ?>"><?php esc_html_e( 'Customize Popup', 'teddybearsprompts' ); ?></button>
			</div>
		<?php
	}
}
