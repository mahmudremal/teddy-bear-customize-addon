<?php
/**
 * LoadmorePosts
 *
 * @package TeddyBearCustomizeAddon
 */

namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;

use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
use \WP_Query;

class Roles {

	use Singleton;

	protected function __construct() {

		// load class.
		$this->setup_hooks();
	}

	protected function setup_hooks() {
		add_action( 'init', [ $this, 'wp_init' ], 10, 0 );
		add_action( 'admin_init', [ $this, 'admin_init' ], 10, 0 );
		add_filter( 'pre_get_posts', [ $this, 'pre_get_posts' ], 10, 1 );
	}
	public function wp_init() {
			 // This user will NOT be able to  delete published pages.
		$cap = [ 'read'  => true, 'delete_posts'  => true, 'delete_published_posts' => true, 'edit_posts'   => true, 'publish_posts' => true, 'upload_files'  => true, 'edit_pages'  => true, 'edit_published_pages'  =>  true, 'publish_pages'  => true, 'delete_published_pages' => false, ];
		add_role( 'lead', __( 'Lead',   'teddybearsprompts' ), $cap );
		$role = get_role( 'lead' );

    $role->add_cap( 'read' );
    $role->add_cap( 'read_events' );
    $role->add_cap( 'read_private_events' );

    $role->add_cap( 'edit_events' );
    $role->add_cap( 'edit_published_events' );
    $role->add_cap( 'publish_events' );

    $role->add_cap( 'delete_events' );
    $role->add_cap( 'delete_private_events' );
    $role->add_cap( 'delete_published_events' );
	}
	public function admin_init() {
		// Add the roles you'd like to administer the custom post types
    $roles = [ 'lead' ];
    // Loop through each role and assign capabilities
    foreach ($roles as $the_role) {
			$role = get_role($the_role);

			$role->add_cap('read');
			$role->add_cap('read_event');
			// $role->add_cap('read_private_events');
			// $role->add_cap('edit_event');
			// $role->add_cap('edit_events');
			// $role->add_cap('edit_others_events');
			// $role->add_cap('edit_published_events');
			// $role->add_cap('publish_events');
			// $role->add_cap('delete_others_events');
			// $role->add_cap('delete_private_events');
			// $role->add_cap('delete_published_events');
		}
	}
	public function pre_get_posts( $query ) {
		global $pagenow;
		if( 'edit.php' != $pagenow || ! $query->is_admin ) {
			return $query;
		}
		if( ! current_user_can( 'edit_others_posts' ) ) {
			global $user_ID;
			$query->set('author', $user_ID);
		}
		return $query;
	}
}
