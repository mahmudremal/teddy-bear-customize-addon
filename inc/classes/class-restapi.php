<?php
/**
 * LoadmorePosts
 *
 * @package TeddyBearCustomizeAddon
 */

namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;

use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
use \WP_Query;

class Restapi {

	use Singleton;

	protected function __construct() {
		// load class.
		$this->setup_hooks();
	}

	protected function setup_hooks() {
		add_action( 'rest_api_init', [ $this, 'rest_api_init' ], 10, 0 );
	}
	public function rest_api_init() {
		register_rest_route( 'futurewordpress/project/teddybearpopupaddon/rest', '/userserver/(?P<id>\d+)', [
			'methods' => 'POST',
			'callback' => [ $this, 'createuser' ],
		] );
		register_rest_route( 'futurewordpress/project/teddybearpopupaddon/rest', '/system/(?P<object>[a-zA-Z0-9-]+)', [
			'methods' => 'GET',
			'callback' => [ $this, 'systemObject' ],
		] );
	}
	public function createuser( $data ) {
		$request = $_POST;$restData = [];
		// $userMeta = (object) apply_filters( 'futurewordpress/project/teddybearpopupaddon/usermeta/defaults', [] );
		// foreach( $userMeta as $meta_key => $meta_value ) {
		// 	if( ! isset( $request[ $meta_key ] ) ) {
		// 		$restData[ $meta_key ] = $meta_value;
		// 	}
		// }
		$restResponse = __( 'Essential information missing to proceed request. Try with atleast email and username.', 'teddybearsprompts' );
		if( isset( $request[ 'email' ] ) && ! empty( $request[ 'email' ] ) ) {
			$except = [ 'display_name' ];
			$is_update_allowed = apply_filters( 'futurewordpress/project/teddybearpopupaddon/system/isactive', 'rest-updateprofile' );
			$is_create_allowed = apply_filters( 'futurewordpress/project/teddybearpopupaddon/system/isactive', 'rest-createprofile' );
			$is_email_prevent = apply_filters( 'futurewordpress/project/teddybearpopupaddon/system/isactive', 'rest-preventemail' );
			$is_default_pass = apply_filters( 'futurewordpress/project/teddybearpopupaddon/system/getoption', 'rest-defaultpass', time() );
			$userInfo = get_user_by_email( $request[ 'email' ] );
			$userMeta = array_map( function( $a ){ return $a[0]; }, (array) get_user_meta( $userInfo->ID ) );
			$userMeta = apply_filters( 'futurewordpress/project/teddybearpopupaddon/usermeta/defaults', $userMeta );
			foreach( $userMeta as $meta_key => $meta_value ) {
				if( ! in_array( $meta_key, [ 'password' ] ) ) {
					$userMeta[ $meta_key ] = isset( $request[ $meta_key ] ) ? $request[ $meta_key ] : $meta_value;
				}
				if( true ) {}
			}
			$userData = [
				'user_login'		=> isset( $request[ 'user_login' ] ) ? $request[ 'user_login' ] : '',
				'display_name'	=> isset( $request[ 'display_name' ] ) ? $request[ 'display_name' ] : '',
				'first_name'		=> isset( $request[ 'first_name' ] ) ? $request[ 'first_name' ] : '',
				'last_name'			=> isset( $request[ 'last_name' ] ) ? $request[ 'last_name' ] : '',
				'user_email'		=> $request[ 'email' ],
				'user_pass'			=> isset( $request[ 'password' ] ) ? $request[ 'password' ] : $is_default_pass,
				'meta_input'		=> $userMeta
			];
			// if( $userInfo ) {$userInfo->meta_input = $userMeta;}
			// return new \WP_REST_Response( $userInfo );
			
			if( $userInfo ) {
				$restResponse = $userInfo;
				$userData[ 'ID' ] = $userInfo->ID;
				$newUserAttempts = false;
			} else {
				/**
				 * Stop sending mail with account creation password link or information
				 */
				if( $is_email_prevent ) {add_action( 'register_new_user', [ $this, 'register_new_user' ], 10, 0 );}
				
				$userData[ 'role' ] = 'lead';
				$newUserAttempts = true;
			}
			
			$is_executed = ( $newUserAttempts ) ? (
				( $is_create_allowed ) ? wp_insert_user( $userData ) : false
			) : (
				( $is_update_allowed ) ? wp_update_user( $userData ) : false
			); // Paused updating cause client requirments.

			if ( ! is_wp_error( $is_executed ) ) {
				if( $is_executed ) {
					$restResponse = ( $is_create_allowed ) ? sprintf( __( 'New User created: %s', 'teddybearsprompts' ), $is_executed) : __( 'Account not matched and no permission to create one.', 'teddybearsprompts' );
				} else {
					$restResponse = __( 'En Existing user profile information matched and nothing happend with them.', 'teddybearsprompts' );
				}
			} else {
				$restResponse = $is_executed->get_error_message();
				if( empty( $restResponse ) ) {
					$restResponse = ( $is_update_allowed ) ? __( 'En Existing user profile information updated.', 'teddybearsprompts' ) : __( 'En Existing user profile information matched and nothing happend with them.', 'teddybearsprompts' );
				}
			}
		}
		return new \WP_REST_Response( $restResponse );
		// return new \WP_REST_Response( json_encode($restData) );
		// return $request;
		// return $data['id'];
	}
	public function register_new_user() {
		remove_action( 'register_new_user', 'wp_send_new_user_notifications' );
	}
	public function systemObject( $request ) {
		if( isset( $request[ 'object' ] ) ) {
			switch ( $request[ 'object' ] ) {
				case 'countries':
					return new \WP_REST_Response( apply_filters( 'futurewordpress/project/teddybearpopupaddon/database/countries', [], false ) );
					break;
				case 'countries-name':
					return new \WP_REST_Response( array_values( (array) apply_filters( 'futurewordpress/project/teddybearpopupaddon/database/countries', [], false ) ) );
					break;
				case 'countries-code':
					return new \WP_REST_Response( array_keys( (array) apply_filters( 'futurewordpress/project/teddybearpopupaddon/database/countries', [], false ) ) );
					break;
				default:
				return new \WP_REST_Response( 'Nothing found' );
					break;
			}
		} else {
			return new \WP_REST_Response( 'Something went wrong' );
		}
	}
}
