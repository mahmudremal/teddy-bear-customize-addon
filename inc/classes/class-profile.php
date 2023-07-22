<?php
/**
 * LoadmorePosts
 *
 * @package TeddyBearCustomizeAddon
 */

namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;

use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
use \WP_Query;

class Profile {

	use Singleton;

	protected function __construct() {
		// load class.
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		// add_filter( 'get_avatar', [ $this, 'get_avatar' ], 9999, 5 );
		// add_filter( 'get_avatar_url', [ $this, 'get_avatar_url' ], 99, 3 );
		add_filter( 'get_avatar_data', [ $this, 'get_avatar_data' ], 99, 2 );
		add_filter( 'futurewordpress/project/teddybearpopupaddon/filesystem/set_avater', [ $this, 'set_avater' ], 10, 2 );

		add_action( 'wp_ajax_futurewordpress/project/teddybearpopupaddon/filesystem/uploadavater', [ $this, 'uploadAvater' ], 10, 0 );

		add_filter( 'login_redirect', [ $this, 'login_redirect' ], 199, 0 );
		add_action( 'after_setup_theme', [ $this, 'remove_admin_bar' ], 10, 0 );

		// add_filter( 'woocommerce_get_myaccount_page_permalink', [ $this, 'woocommerce_get_myaccount_page_permalink' ], 10, 1 );
		add_action( 'woocommerce_account_navigation', [ $this, 'woocommerce_account_navigation' ], 10, 0 );
		add_filter( 'woocommerce_login_redirect', [ $this, 'woocommerce_login_redirect' ], 10, 2 );

		add_filter( 'futurewordpress/project/teddybearpopupaddon/profile/defaulttab', [ $this, 'defaultTab' ], 1, 1 );
	}
	public function get_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
		$user = false;
		if( is_numeric( $id_or_email ) ) {
			$id = (int) $id_or_email;
			$user = get_user_by( 'id', $id );
		} elseif( is_object( $id_or_email ) ) {
			if( ! empty( $id_or_email->user_id ) ) {
				$id = (int) $id_or_email->user_id;
				$user = get_user_by( 'id', $id );
			}
		} else {
			$user = get_user_by( 'email', $id_or_email ); 
		}
		if( $user && is_object( $user ) ) {
			// Set your custom avatar URL
			$custom_avatar = get_user_meta( $user->ID, 'custom_avatar', true );
			if( $custom_avatar ) {
				$avatar = '<img alt="' . $alt . '" src="' . $custom_avatar . '" class="avatar avatar-' . $size . ' photo" height="' . $size . '" width="' . $size . '" />';
			}
		}
		return $avatar;
	}
	public function get_avatar_url( $url, $id_or_email, $args ) {
		$userInfo = get_user_by( ( is_string( $id_or_email ) ? 'email' : 'id' ), $id_or_email );
		if( $userInfo && ! is_wp_error( $userInfo ) && $custom_avatar = get_user_meta( $userInfo->ID, 'custom_avatar', true ) && ! empty( $custom_avatar ) ) {
			$url = $custom_avatar;
		} else {
			$url = 'https://templates.iqonic.design/product/qompac-ui/html/dist/assets/images/shapes/02.png?';
		}
		return $url;
	}
	public function defaultTab( $default ) {
		return 'archive';
	}

	public function get_avatar_data( $args, $id_or_email ) {
		$user = false;
		if( is_numeric( $id_or_email ) ) {
			$id = (int) $id_or_email;
			$user = get_user_by( 'id', $id );
		} elseif( is_object( $id_or_email ) ) {
			if( ! empty( $id_or_email->user_id ) ) {
				$id = (int) $id_or_email->user_id;
				$user = get_user_by( 'id', $id );
			}
		} else {
			$user = get_user_by( 'email', $id_or_email ); 
		}
		if( $user && ! is_wp_error( $user ) ) {
			$custom_avatar = get_user_meta( $user->ID, 'custom_avatar', true );
			$args[ 'url' ] = empty( $custom_avatar ) ? $args[ 'url' ] : $custom_avatar;
			$args[ 'found_avatar' ] = true;
		}
		return $args;
	}
	public function set_avater( $status, $file ) {
		if( ! is_user_logged_in() ) {return;}
		$current_user = wp_get_current_user();
		if( ! current_user_can( 'upload_files' ) ) {return;}
		if( isset( $file['name'] ) ) {
			// Handle the uploaded file | $_FILES['my_custom_avatar_file']
			if( ! empty( $file['name'] ) ) {
				$file = $file;
				$upload_overrides = array( 'test_form' => false );
				$movefile = wp_handle_upload( $file, $upload_overrides );
				if( $movefile && ! isset( $movefile['error'] ) ) {
					update_user_meta( $current_user->ID, 'custom_avatar', $movefile['url'] );
					return $movefile;
				} else {
					return false;
				}
			}
		}
	}
	public function uploadAvater() {
		check_ajax_referer( 'futurewordpress/project/teddybearpopupaddon/verify/nonce', '_nonce' );
		$user_id = is_admin() ? $_POST[ 'lead' ] : get_current_user_id();
		$upload_dir = apply_filters( 'futurewordpress/project/teddybearpopupaddon/filesystem/uploaddir', false );
		$custom_avatar = get_user_meta( $user_id, 'custom_avatar', true );
		$oldFilePATH = str_replace( [site_url('/')], [ABSPATH], $custom_avatar );
		if( $oldFilePATH && ! empty( $oldFilePATH ) && file_exists( $oldFilePATH ) && ! is_dir( $oldFilePATH ) ) {
			unlink( $oldFilePATH );
		}
		$randomstring = apply_filters( 'futurewordpress/project/teddybearpopupaddon/filter/string/random', '', 10 );
		$newFileName = time() . '-' . $randomstring . '-' . $_FILES[ 'avater' ][ 'name' ];
		// wp_send_json_success( $newFileName );
		if( $upload_dir && move_uploaded_file( $_FILES[ 'avater' ][ 'tmp_name' ], $upload_dir . '/' . $newFileName ) ) {
			$newFileURL = str_replace( [ABSPATH], [site_url('/')], $upload_dir . '/' . $newFileName );
			update_user_meta( $user_id, 'custom_avatar', $newFileURL );
			// '<img src="' . $newFileURL . '" alt="" class="mr-2" height="30px" width="auto" />' . 
			wp_send_json_success( __( 'Profile Image Updated Successfully.', 'teddybearsprompts' ), 200 );
		} else {
			wp_send_json_error( __( 'Profile Image upload failed.', 'teddybearsprompts' ) );
		}
	}
	public function login_redirect() {
		$who = 'me'; // isset( $_POST['log'] ) ? strtolower( sanitize_user( $_POST['log'] ) ) : 'me';
    $redirect_to = apply_filters( 'futurewordpress/project/teddybearpopupaddon/user/dashboardpermalink', false, $who );
    return $redirect_to;
	}
	public function remove_admin_bar() {
		if( ! current_user_can( 'administrator' ) && ! is_admin() ) {
			show_admin_bar( false );
		}
	}
	public function woocommerce_get_myaccount_page_permalink( $permalink ) {
		return apply_filters( 'futurewordpress/project/teddybearpopupaddon/user/dashboardpermalink', false, 'me' );
	}
	public function woocommerce_account_navigation() {
		if( ! apply_filters( 'futurewordpress/project/teddybearpopupaddon/system/isactive', 'dashboard-disablemyaccount' ) ) {return;}
		$link = apply_filters( 'futurewordpress/project/teddybearpopupaddon/user/dashboardpermalink', false, 'me' );
		wp_redirect( $link );
		?>
		<script>location.replace( "<?php echo esc_url( $link ); ?>" );</script>
		<?php
	}
	public function woocommerce_login_redirect( $redirect, $user ) {
		return apply_filters( 'futurewordpress/project/teddybearpopupaddon/user/dashboardpermalink', false, 'me' );
	}
}
