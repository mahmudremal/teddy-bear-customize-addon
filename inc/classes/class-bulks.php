<?php
/**
 * Archive Settings
 *
 * @package TeddyBearCustomizeAddon
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
class Bulks {
	use Singleton;
	private $args;
	protected function __construct() {
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		$this->args = [
			'cpt'			=> 'listivo_listing',
			'column'	=> 'status',
			'options'	=> [
				''       => __( 'All', 'teddybearsprompts' ),
				'open'   => __( 'Open', 'teddybearsprompts' ),
				'closed' => __( 'Closed', 'teddybearsprompts' ),
			]
		];
		/**
		 * Filters thst will work on post list.
		 * https://wordpress.stackexchange.com/questions/351432/add-column-and-post-filter-for-a-custom-post-type-field-on-the-edit-php-page
		 */
		add_filter( 'manage_' . $this->args[ 'cpt' ] . '_posts_columns', [ $this, 'manage_posts_columns' ], 10, 1 );
		add_filter( 'manage_' . $this->args[ 'cpt' ] . '_posts_custom_column', [ $this, 'manage_posts_custom_column' ], 10, 2 );
		add_filter( 'restrict_manage_posts', [ $this, 'restrict_manage_posts' ], 10, 2 );
		add_filter( 'parse_query', [ $this, 'parse_query' ], 10, 1 );

		/**
		 * Switch post status here.
		 */
		add_action( 'wp_ajax_futurewordpress/project/teddybearpopupaddon/status/switch', [ $this, 'ajax_switch' ], 10, 0 );
		add_action( 'wp_ajax_nopriv_futurewordpress/project/teddybearpopupaddon/status/switch', [ $this, 'ajax_switch' ], 10, 0 );
	}
	/**
	 * Register a column on edit screen.
	 * 
	 * @return array
	 */
	public function manage_posts_columns( $columns ) {
		$columns = (array) $columns;
		$columns[ $this->args[ 'column' ] ] = __( 'Status', 'teddybearsprompts' );
		return $columns;
	}
	/**
	 * Function over those registered column.
	 * 
	 * @return void
	 */
	public function manage_posts_custom_column( $column, $post_id ) {
		if( $this->args[ 'column' ] == $column ) {
			$args = [
				'confirm'	=> true,
				'request'	=> [
					'action'		=> 'futurewordpress/project/teddybearpopupaddon/status/switch',
					'post_id'		=> $post_id,
					'status'		=> false
				]
			];
			$meta = get_post_status( $post_id ); // get_post_meta( $post_id, $this->args[ 'column' ], true );
			if( $meta == 'publish' ) {
				echo '<i class="fwp-button fwp-btn-icononly btn-ajax-switch" data-status="on" data-events="' . esc_attr( json_encode( $args ) ) . '" data-on-finished="document.getElementById( \'post-' . $post_id . '\' ).remove();"></i>';
			} else {
				echo '<i class="fwp-button fwp-btn-icononly btn-ajax-switch" data-status="off" data-events="' . esc_attr( json_encode( $args ) ) . '" data-on-finished="document.getElementById( \'post-' . $post_id . '\' ).remove();"></i>';
			}
		}
	}
	/**
	 * Add a filter select to the posts lists
	 * 
	 * @return void
	 */
	public function restrict_manage_posts( $post_type, $which ) {
		if ( $this->args[ 'cpt' ] === $post_type ) {
			$meta_key = $this->args[ 'column' ];
	
			echo "<select name='{$meta_key}' id='{$meta_key}' class='postform'>";
			foreach ( $this->args[ 'options' ] as $value => $name ) {
				printf(
					'<option value="%1$s" %2$s>%3$s</option>',
					esc_attr($value),
					( ( isset( $_GET[$meta_key] ) && ( $_GET[$meta_key] === $value ) ) ? ' selected="selected"' : '' ),
					esc_html($name)
				);
			}
			echo '</select>';
		}
	}
	/**
	 * Use `parse_query` to filter posts based on the selected status value.
	 * 
	 * @return array
	 */
	public function parse_query( $query ) {
		global $pagenow;

		$meta_key			= $this->args[ 'column' ];
		$valid_status	= array_keys( $this->args[ 'options' ] );
		$status				= ( ! empty( $_GET[$meta_key] ) && in_array( $_GET[$meta_key], $valid_status ) ) ? $_GET[$meta_key] : '';
		if( is_admin() && 'edit.php' === $pagenow && isset( $_GET['post_type'] ) && $this->args[ 'cpt' ] === $_GET['post_type'] && $status ) {
			$query->query_vars['meta_key'] = $meta_key;
			$query->query_vars['meta_value'] = $status;
		}
	}

	/**
	 * Recieve Ajax request and output json.
	 * 
	 * @return string
	 */
	public function ajax_switch() {
		if( ! isset( $_POST[ 'post_id' ] ) || empty( $_POST[ 'post_id' ] ) ) {
			return;
		}
		$args = [
			'post_type'			=> $this->args[ 'cpt' ],
			'ID'						=> $_POST[ 'post_id' ],
			'post_status'		=> $_POST[ 'status' ]
		];
		$isSuccess = wp_update_post( $args, false );
		if( is_wp_error( $isSuccess ) ) {
			wp_send_json_error(  [
				'status'			=> false,
				'message'			=> $isSuccess->get_error_message()
			], 200 );
		} else {
			wp_send_json_success( [
				'status'			=> true
			], 200 );
		}
	}
}
