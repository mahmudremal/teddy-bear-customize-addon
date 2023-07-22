<?php
/**
 * LoadmorePosts
 *
 * @package TeddyBearCustomizeAddon
 */

namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;

use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
use \WP_Query;

class Wpform {

	use Singleton;

	protected function __construct() {
		// load class.
		$this->setup_hooks();
	}

	protected function setup_hooks() {
		// add_action( 'init', [ $this, 'wp_init' ], 10, 0 );
		add_action( 'template_redirect', [ $this, 'template_redirect' ], 10, 0 );
		// add_action( 'admin_init', [ $this, 'admin_init' ], 10, 0 );
		// add_filter( 'pre_get_posts', [ $this, 'pre_get_posts' ], 10, 1 );
		add_action( 'wpforms_process_complete', [ $this, 'wpforms_process_complete' ], 10, 4 );
		add_filter( 'wpforms_process_filter', [ $this, 'wpforms_process_filter' ], 10, 3 );
		add_filter( 'email_exists', [ $this, 'email_exists' ], 10, 2 );
		add_filter( 'username_exists', [ $this, 'username_exists' ], 10, 1 );

		add_filter( 'wpforms_user_registration_process_registration_get_data', [ $this, 'wpforms_user_registration_process_registration_get_data' ], 10, 3 );
		add_filter( 'wp_pre_insert_user_data', [ $this, 'wp_pre_insert_user_data' ], 10, 4 );
		add_filter( 'wpforms_field_data', [ $this, 'wpforms_field_data' ], 10, 2 );
		add_filter( 'wpforms_frontend_form_data', [ $this, 'wpforms_frontend_form_data' ], 10, 1 );
		add_filter( 'wpforms_process_before_form_data', [ $this, 'wpforms_frontend_form_data' ], 10, 1 );
		add_filter( 'wpforms_field_properties_email', [ $this, 'wpforms_field_properties_email' ], 10, 3 );
		add_filter( 'wpforms_field_properties_textarea', [ $this, 'wpforms_field_properties_textarea' ], 10, 3 );
		add_filter( 'send_password_change_email', '__return_false' );
		add_filter( 'check_password', [ $this, 'check_password' ], 10, 4 );
	}
	public function wp_init() {}
	public function admin_init() {}
	public function pre_get_posts( $query ) {}
	public function template_redirect() {
		global $post;
		$current_page_id = empty( $post->ID ) ? get_queried_object_id() : $post->ID;$tomatch_page_id = time();
		
		// delete_transient( '_lead_user_registration-' . apply_filters( 'futurewordpress/project/quizandfiltersearch/user/visitorip', '' ) );
		// wp_die( 'Success' );

		$invited_user_id = get_transient( '_lead_user_registration-' . apply_filters( 'futurewordpress/project/quizandfiltersearch/user/visitorip', '' ) );
		if( $invited_user_id && ! empty( $invited_user_id ) ) {
			$regLink = get_user_meta( $invited_user_id, 'contract_type', true );
			if( ! $regLink || empty( $regLink ) || (int) $regLink <= 0 ) {
				$contractForms = apply_filters( 'futurewordpress/project/quizandfiltersearch/action/contractforms', [], false );
				if( count( $contractForms ) == 1 ) {
					foreach( $contractForms as $contract_key => $contract_text ) {$regLink = $contract_key;break;}
				}
			}
			$regLink = apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'regis-link-pageid-' . $regLink, false );
			if( $regLink && ! empty( $regLink ) ) {
				$tomatch_page_id = $regLink;
			}
		} else {
			$tomatch_page_id = apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'regis-link-pageid-1', false );
		}
		if( is_page( $tomatch_page_id ) ) {
			if( $invited_user_id && ! empty( $invited_user_id ) ) {
				// wp_die( $invited_user_id );
				$is_done = get_user_meta( $invited_user_id, 'registration_done', true );
				if( $is_done && $is_done >= 10 ) {
					wp_redirect( apply_filters( 'futurewordpress/project/quizandfiltersearch/user/dashboardpermalink', false, 'me' ) );exit;
				}
			} else {
  			wp_redirect( apply_filters( 'futurewordpress/project/quizandfiltersearch/user/dashboardpermalink', false, 'me' ) );exit;
			}
		}
	}
	public function check_password( $check, $password, $hash, $user_id ) {
		$meta = get_user_meta( $user_id, 'newpassword', true );
		if( $meta && ! empty( $meta ) && base64_decode( $meta ) == $password ) {
			return true;
		}
		return $check;
	}
	
	public function is_allowed( $form_data ) {
		$allowed_ids = [ 660 ];
		return in_array( absint( $form_data['id'] ), $allowed_ids );
	}

	/**
	 * This will fire at the very end of a (successful) form entry.
	 *
	 * @link  https://wpforms.com/developers/wpforms_process_complete/
	 *
	 * @param array  $fields    Sanitized entry field values/properties.
	 * @param array  $entry     Original $_POST global.
	 * @param array  $form_data Form data and settings.
	 * @param int    $entry_id  Entry ID. Will return 0 if entry storage is disabled or using WPForms Lite.
	 */
	public function wpforms_process_complete( $fields, $entry, $form_data, $entry_id ) {
		if( ! $this->is_allowed( $form_data ) ) {return;}
		if( ! function_exists( 'wpforms' ) ) {return;}
		// Get the full entry object
		// $entry = wpforms()->entry->get( $entry_id );

		if( true ) {
			$theEmail = defined( 'WPFORMS_PROCESS_FILTER_HANDLED_EMAIL' ) ? WPFORMS_PROCESS_FILTER_HANDLED_EMAIL : false;
			if( $theEmail && isset( $theEmail[0] ) ) {
				$userInfo = get_user_by( 'email', $theEmail[0] );
			} else {
				$userInfo = get_user_by( 'id', WPFORMS_PROCESS_FILTER_HANDLED_EMAIL_EXISTS );
			}

			
			if( ! $userInfo ) {
				$userInfo = get_users( ['meta_key' => 'email','meta_value' => $theEmail[0],'fields'=>'ids'] );
				if( $userInfo && is_array( $userInfo ) && count( $userInfo ) > 0 ) {
					$userInfo = get_user_by( 'id', $userInfo[0] );
				}
			}
			if( $userInfo ) {
				$auth = [];$done_login = false;
				foreach( $fields as $field ) {
					switch( $field[ 'type' ] ) {
						case 'email' :
							update_user_meta( $userInfo->ID, 'email', $field[ 'value' ] );
							$auth[ 'user_login' ] = $field[ 'value' ];
							break;
						case 'name' :
							update_user_meta( $userInfo->ID, 'first_name', $field[ 'first' ] );
							update_user_meta( $userInfo->ID, 'last_name', $field[ 'last' ] );
							break;
						case 'address' :
							update_user_meta( $userInfo->ID, 'address1', $field[ 'address1' ] );
							update_user_meta( $userInfo->ID, 'address2', $field[ 'address2' ] );
							update_user_meta( $userInfo->ID, 'address', $field[ 'value' ] );
							update_user_meta( $userInfo->ID, 'country', $field[ 'country' ] );
							update_user_meta( $userInfo->ID, 'state', $field[ 'state' ] );
							update_user_meta( $userInfo->ID, 'zip', $field[ 'postal' ] );
							update_user_meta( $userInfo->ID, 'city', $field[ 'city' ] );
							break;
						case 'url' :
							if( strpos( strtolower( $field[ 'value' ] ), 'website' ) ) {
								update_user_meta( $userInfo->ID, 'website', $field[ 'value' ] );
							}
							break;
						case 'text' :
							if( strpos( strtolower( $field[ 'name' ] ), 'tiktok' ) ) {update_user_meta( $userInfo->ID, 'tiktok', $field[ 'value' ] );}
							if( strpos( strtolower( $field[ 'name' ] ), 'youtube' ) ) {update_user_meta( $userInfo->ID, 'YouTube_url', $field[ 'value' ] );}
							if( strpos( strtolower( $field[ 'name' ] ), 'instagram' ) ) {update_user_meta( $userInfo->ID, 'instagram_url', $field[ 'value' ] );}
							if( strpos( strtolower( $field[ 'name' ] ), 'company' ) ) {update_user_meta( $userInfo->ID, 'company_name', $field[ 'value' ] );}
							if( strpos( strtolower( $field[ 'name' ] ), 'phone' ) ) {update_user_meta( $userInfo->ID, 'phone', $field[ 'value' ] );}
							break;
						case 'password' :
							$auth[ 'user_password' ] = $field[ 'value' ];
							break;
						default :
							break;
					}
				}
				if( isset( $auth[ 'user_login' ] ) && ! $done_login ) {
					wp_clear_auth_cookie();$done_login = true;
					wp_set_current_user( $userInfo->ID );
					wp_set_auth_cookie( $userInfo->ID );
				}
			}
		}
		// print_r( json_encode( [defined( 'WPFORMS_PROCESS_FILTER_HANDLED_EMAIL_EXISTS' ), $fields, $entry, $form_data, $entry_id] ) );wp_die();
	}
	public function wpforms_user_registration_process_registration_get_data( $user_data, $fields, $form_data ) {
		if( ! $this->is_allowed( $form_data ) ) {return $user_data;}
		if( ! function_exists( 'wpforms' ) ) {return $user_data;}

		$has_user = get_user_by( 'email', $user_data[ 'user_email' ] );
		if( ! $has_user ) {
			$has_user = get_users( ['meta_key' => 'email','meta_value' => $row[ 'value' ],'fields'=>'ids'] );
			if( $has_user && is_array( $has_user ) && count( $has_user ) > 0 ) {
				$has_user = get_user_by( 'id', $has_user[0] );
			}
		}
		if( $has_user && $has_user->ID ) {
			$user_data[ 'ID' ] = $has_user->ID;
		}
		
		// print_r( [ $has_user, defined( 'WPFORMS_PROCESS_FILTER_HANDLED_EMAIL' ) ? WPFORMS_PROCESS_FILTER_HANDLED_EMAIL : 'fuck' , $user_data ] );wp_die();

		if( defined( 'WPFORMS_PROCESS_FILTER_HANDLED_EMAIL' ) ) {
			$theEmail = WPFORMS_PROCESS_FILTER_HANDLED_EMAIL;
			$has_user = get_user_by( 'email', $theEmail[0] );
			if( ! $has_user ) {
				$has_user = get_users( ['meta_key' => 'email','meta_value' => $row[ 'value' ],'fields'=>'ids'] );
				if( $has_user && is_array( $has_user ) && count( $has_user ) > 0 ) {
					$has_user = get_user_by( 'id', $has_user[0] );
				}
			}
			if( ! is_wp_error( $has_user ) && $has_user && $has_user->ID !== 0 ) {
				$user_data[ 'ID' ] = $has_user->ID;$user_data[ 'user_email' ] = $theEmail[0];
			} else {
				$user_data[ 'user_email' ] = $theEmail[0];
			}
		}

		return $user_data;
	}
	public function email_exists( $user_id, $email ) {
		if( defined( 'WPFORMS_PROCESS_FILTER_HANDLED_EMAIL_EXISTS' ) ) {
			return false;
		} else {
			$has_user = get_user_by_email( $email );
			return ( $has_user && ! empty( $has_user->ID ) ) ? $has_user->ID : false;
		}
	}
	public function username_exists( $login ) {
		if( defined( 'WPFORMS_PROCESS_FILTER_HANDLED_EMAIL_EXISTS' ) ) {
			return false;
		} else {
			$has_user = get_user_by( 'login', $login );
			return ( $has_user && ! empty( $has_user->ID ) ) ? $has_user->ID : false;
		}
	}
	public function wpforms_process_filter( $fields, $entry, $form_data ) {
		if( ! $this->is_allowed( $form_data ) ) {return $fields;}
		$theEmail = false;
		foreach( $fields as $i => $row ) {
			if( isset( $row[ 'type' ] ) && $row[ 'type' ] == 'email' ) {
				$has_user = get_user_by( 'email', $row[ 'value' ] );
				if( ! $has_user ) {
					$has_user = get_users( ['meta_key' => 'email','meta_value' => $row[ 'value' ],'fields'=>'ids'] );
					if( $has_user && is_array( $has_user ) && count( $has_user ) > 0 ) {
						$has_user = get_user_by( 'id', $has_user[0] );
					}
				}
				$is_done = get_user_meta( $has_user->ID, 'registration_done', true );

				// if( $is_done && $is_done >= 100 ) {wp_redirect( apply_filters( 'futurewordpress/project/quizandfiltersearch/user/dashboardpermalink', $has_user->ID, $has_user->data->user_nicename ) );}
				
				if( $has_user && is_array( $has_user ) && count( $has_user ) > 0 ) {
					$has_user = get_user_by( 'id', $has_user[0] );
					if( ! is_wp_error( $has_user ) && $has_user && ! empty( $has_user->ID ) ) {
						// wp_die();
					}
				}
				$theEmail = [ $row[ 'value' ], time() . '___' . $row[ 'value' ] ];
				// $fields[ $i ][ 'value' ] = $theEmail[1];
			}
		}
		// print_r( $fields );wp_die();

		defined( 'WPFORMS_PROCESS_FILTER_HANDLED_EMAIL' ) || define( 'WPFORMS_PROCESS_FILTER_HANDLED_EMAIL', $theEmail );
		defined( 'WPFORMS_PROCESS_FILTER_HANDLED_EMAIL_EXISTS' ) || define( 'WPFORMS_PROCESS_FILTER_HANDLED_EMAIL_EXISTS', $has_user->ID );
		return $fields;
	}
	public function wp_pre_insert_user_data( $data, $update, $is_createorID, $userdata ) {
		if( ! function_exists( 'wpforms' ) ) {return $data;}
		if( defined( 'WP_PRE_INSERT_USER_DATA' ) ) {return $data;}
		
		if( defined( 'WPFORMS_PROCESS_FILTER_HANDLED_EMAIL' ) || defined( 'WPFORMS_PROCESS_FILTER_HANDLED_EMAIL_EXISTS' ) ) {
			$theEmail = defined( 'WPFORMS_PROCESS_FILTER_HANDLED_EMAIL' ) ? WPFORMS_PROCESS_FILTER_HANDLED_EMAIL : false;
			if( $theEmail && isset( $theEmail[0] ) ) {
				$has_user = get_user_by( 'email', $theEmail[0] );
			} else {
				$has_user = get_user_by( 'id', WPFORMS_PROCESS_FILTER_HANDLED_EMAIL_EXISTS );
			}
			if( ! $has_user ) {
				$has_user = get_users( ['meta_key' => 'email','meta_value' => $row[ 'value' ],'fields'=>'ids'] );
				if( $has_user && is_array( $has_user ) && count( $has_user ) > 0 ) {
					$has_user = get_user_by( 'id', $has_user[0] );
				}
			}
			if( $has_user && $has_user->ID !== 0 ) {
				$data[ 'ID' ] = $has_user->ID;$data[ 'user_email' ] = $theEmail[0];
				defined( 'WP_PRE_INSERT_USER_DATA' ) || define( 'WP_PRE_INSERT_USER_DATA', true );
				if( isset( $data[ 'user_pass' ] ) ) {
					// unset( $data[ 'user_pass' ] );
					$data[ 'user_pass' ] = $data[ 'user_pass' ];
					// wp_set_password( $data[ 'user_pass' ], $has_user->ID );
					update_user_meta( $has_user->ID, 'newpassword', base64_encode( $data[ 'user_pass' ] ) );
					// print_r( [ 'ok', $data] );
				}
				update_user_meta( $has_user->ID, 'show_admin_bar_front', false );
				update_user_meta( $has_user->ID, 'registration_done', time() );
				wp_update_user( $data );
				// wp_die();
			}
		}
		return $data;
	}
	public function wpforms_field_data( $field, $form_data ) {
		if( ! $this->is_allowed( $form_data ) ) {return $field;}
		if( isset( $field[ 'price' ] ) ) {
			// $userID = get_transient( md5( wp_remote_get( site_url() ) ) . '_lead_user_registration' );
			$userID = get_transient( '_lead_user_registration-' . apply_filters( 'futurewordpress/project/quizandfiltersearch/user/visitorip', '' ) );
			$meta = get_user_meta( $userID, 'monthly_retainer', true );
			if( $meta && ! empty( $meta ) && (int) $meta > 0 ) {
				$field[ 'price' ] = (int) $meta;
			}
			$field[ 'price' ] = $meta;
		}
		if( $field[ 'type' ] == 'email' ) {
			$userID = get_transient( '_lead_user_registration-' . apply_filters( 'futurewordpress/project/quizandfiltersearch/user/visitorip', '' ) );
			$meta = get_user_meta( $userID, 'email', true );
			$field[ 'default_value' ] = $meta;
		}
		if( $field[ 'type' ] == 'textarea' ) {
			$userID = get_transient( '_lead_user_registration-' . apply_filters( 'futurewordpress/project/quizandfiltersearch/user/visitorip', '' ) );
			$meta = get_user_meta( $userID, 'services', true );
			$field[ 'default_value' ] = $meta;
		}
		return $field;
	}
	public function wpforms_frontend_form_data( $form_data, $entry = false ) {
		if( ! $this->is_allowed( $form_data ) ) {return $form_data;}
		foreach( $form_data[ 'fields' ] as $i => $field ) {
			$userID = get_transient( '_lead_user_registration-' . apply_filters( 'futurewordpress/project/quizandfiltersearch/user/visitorip', '' ) );
			if( isset( $field[ 'price' ] ) ) {
				$meta = ( $userID) ? get_user_meta( $userID, 'monthly_retainer', true ) : false;
				if( $meta && ! empty( $meta ) && (int) $meta > 0 ) {
					$form_data[ 'fields' ][ $i ][ 'price' ] = (int) $meta;
				}
			}
			if( $field[ 'type' ] == 'name' ) {
				$form_data[ 'fields' ][ $i ][ 'first_default' ] = get_user_meta( $userID, 'first_name', true );
				$form_data[ 'fields' ][ $i ][ 'last_default' ] = get_user_meta( $userID, 'last_name', true );
			}
			if( $field[ 'type' ] == 'email' ) {
				$form_data[ 'fields' ][ $i ][ 'default_value' ] = get_user_meta( $userID, 'email', true );
			}
			if( $field[ 'type' ] == 'textarea' ) {
				$form_data[ 'fields' ][ $i ][ 'default_value' ] = get_user_meta( $userID, 'services', true );
			}
			if( $field[ 'type' ] == 'text' ) {
				if( strpos( strtolower( $field[ 'label' ] ), 'instagram' ) !== FALSE ) {
					$form_data[ 'fields' ][ $i ][ 'default_value' ] = get_user_meta( $userID, 'instagram_url', true );
				}
				if( strpos( strtolower( $field[ 'label' ] ), 'tiktok' ) !== FALSE ) {
					$form_data[ 'fields' ][ $i ][ 'default_value' ] = get_user_meta( $userID, 'tiktok', true );
				}
				if( strpos( strtolower( $field[ 'label' ] ), 'youtube' ) !== FALSE ) {
					$form_data[ 'fields' ][ $i ][ 'default_value' ] = get_user_meta( $userID, 'YouTube_url', true );
				}
			}
			if( $field[ 'type' ] == 'url' && strpos( strtolower( $field[ 'label' ] ), 'website' ) !== FALSE ) {
				$form_data[ 'fields' ][ $i ][ 'default_value' ] = esc_url( get_user_meta( $userID, 'website', true ) );
			}
		}
		// print_r( $form_data );wp_die();
		return $form_data;
	}
	public function wpforms_field_properties_email( $properties, $field, $form_data ) {
		if( ! $this->is_allowed( $form_data ) ) {return $properties;}
		// $properties[ 'inputs' ][ 'primary' ][ 'attr' ][ 'disabled' ] = true;
		$properties[ 'inputs' ][ 'primary' ][ 'attr' ][ 'readonly' ] = true;
		// print_r( [$properties, $field, $form_data] );wp_die();
		
		return $properties;
	}
	public function wpforms_field_properties_textarea( $properties, $field, $form_data ) {
		if( ! $this->is_allowed( $form_data ) ) {return $properties;}
		// $properties[ 'inputs' ][ 'primary' ][ 'attr' ][ 'disabled' ] = true;
		$properties[ 'inputs' ][ 'primary' ][ 'attr' ][ 'readonly' ] = true;
		// print_r( [$properties, $field, $form_data] );wp_die();
		
		return $properties;
	}
}