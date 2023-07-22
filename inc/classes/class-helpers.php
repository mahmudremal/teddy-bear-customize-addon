<?php
/**
 * Theme Sidebars.
 *
 * @package TeddyBearCustomizeAddon
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
/**
 * Class Widgets.
 */
 class Helpers {
	use Singleton;
	private $theUploadDir;
	private $dateFormate;
	/**
	 * Construct method.
	 */
	protected function __construct() {
		$this->theUploadDir = false;
		$this->dateFormate = 'd-M-Y H:i:s';
		$this->setup_hooks();
	}
	/**
	 * To register action/filter.
	 *
	 * @return void
	 */
	protected function setup_hooks() {
		if( ! defined( 'TEDDY_BEAR_CUSTOMIZE_ADDON_OPTIONS' ) ) {define( 'TEDDY_BEAR_CUSTOMIZE_ADDON_OPTIONS', (array) get_option( 'teddybearsprompts', [] ) );}
		add_filter( 'futurewordpress/project/teddybearpopupaddon/system/getoption', [ $this, 'getOption' ], 10, 2 );
		add_filter( 'futurewordpress/project/teddybearpopupaddon/system/isactive', [ $this, 'isActive' ], 10, 1 );

		add_filter( 'futurewordpress/project/teddybearpopupaddon/database/countries', [ $this, 'databaseCountries' ], 10, 2 );
		add_filter( 'futurewordpress/project/teddybearpopupaddon/database/countryflags', [ $this, 'countryFlags' ], 10, 1 );

		add_filter( 'futurewordpress/project/teddybearpopupaddon/user/dashboardpermalink', [ $this, 'dashboardPermalink' ], 10, 2 );
		add_filter( 'futurewordpress/project/teddybearpopupaddon/user/visitorip', [ $this, 'visitorIP' ], 10, 0 );


		add_filter( 'futurewordpress/project/teddybearpopupaddon/filter/server/time', [ $this, 'serverTime' ], 10, 2 );
		add_filter( 'futurewordpress/project/teddybearpopupaddon/filter/string/random', [ $this, 'generateRandomString' ], 10, 2 );

		add_filter( 'futurewordpress/project/teddybearpopupaddon/filesystem/filemtime', [ $this, 'filemtime' ], 10, 2 );
		add_filter( 'futurewordpress/project/teddybearpopupaddon/filesystem/uploaddir', [ $this, 'uploadDir' ], 10, 2 );
		add_filter( 'futurewordpress/project/teddybearpopupaddon/mailsystem/sendmail', [ $this, 'sendMail' ], 10, 1 );

		add_filter( 'futurewordpress/project/teddybearpopupaddon/notices/manager', [ $this, 'noticeManager' ], 10, 3 );
		
		add_action( 'wp_ajax_futurewordpress/project/teddybearpopupaddon/filesystem/upload', [ $this, 'uploadFile' ], 10, 0 );
		add_action( 'wp_ajax_nopriv_futurewordpress/project/teddybearpopupaddon/filesystem/upload', [ $this, 'uploadFile' ], 10, 0 );
		add_action( 'wp_ajax_futurewordpress/project/teddybearpopupaddon/filesystem/remove', [ $this, 'removeFile' ], 10, 0 );
		add_action( 'wp_ajax_nopriv_futurewordpress/project/teddybearpopupaddon/filesystem/remove', [ $this, 'removeFile' ], 10, 0 );
		add_action( 'admin_post_futurewordpress/project/teddybearpopupaddon/filesystem/download', [ $this, 'downloadFile' ], 10, 0 );
		add_action( 'admin_post_nopriv_futurewordpress/project/teddybearpopupaddon/filesystem/download', [ $this, 'downloadFile' ], 10, 0 );

	}
	/**
	 * Get and option value, return default. Default false.
	 * 
	 * @return string
	 */
	public function getOption( $option, $default ) {
    return isset( TEDDY_BEAR_CUSTOMIZE_ADDON_OPTIONS[ $option ] ) ? TEDDY_BEAR_CUSTOMIZE_ADDON_OPTIONS[ $option ] : $default;
	}
	/**
	 * Check if is active or not.
	 * 
	 * @return bool
	 */
	public function isActive( $option ) {
    return ( isset( TEDDY_BEAR_CUSTOMIZE_ADDON_OPTIONS[ $option ] ) && TEDDY_BEAR_CUSTOMIZE_ADDON_OPTIONS[ $option ] == 'on' );
	}
	/**
	 * Given a date in the timezone of the site, returns that date in UTC.
	 * 
	 * @return string
	 */
	public function serverTime( $time, $args = [] ) {
    return get_gmt_from_date( date( $this->dateFormate, $time ), $this->dateFormate );
  }
	/**
	 * Given a date in UTC or GMT timezone, returns that date in the timezone of the site.
	 * 
	 * @return string
	 */
	private function getLocalTime( $time, $args = [] ) {
    return get_date_from_gmt( date( $this->dateFormate, $time ), $this->dateFormate );
  }
	/**
	 * File Modification time.
	 * 
	 * @return string
	 */
	public function filemtime( $version, $path ) {
		return ( file_exists( $path ) && ! is_dir( $path ) ) ? filemtime( $path ) : $version;
	}
	/**
	 * Sending mail using filter.
	 * 
	 * @return void
	 */
	public function sendMail( $args = [] ) {
		$request = wp_parse_args( $args, [
			'id' => 0, 'to' => '', 'name' => '', 'email' => '', 'subject' => '', 'message' => '', 'type' => 'text/plain'
		] );
		// can be verify by "id" as company ID Author ID
		$to = $request[ 'to' ];
		$subject = $request[ 'subject' ];
		$body = $request[ 'message' ];
		$headers = [ 'Content-Type: ' . $request[ 'type' ] . '; charset=UTF-8' ];
		$headers[] = 'Reply-To: ' . $request[ 'name' ] . ' <' . $request[ 'email' ] . '>';

		$mail_sent = wp_mail( $to, $subject, $body, $headers );
		// $msg = [ 'status' => 'success', 'message' => __( get_FwpOption( 'msg_profile_edit_success_txt', 'Changes saved' ), FUTUREWORDPRESS_PROJECT_TEXT_DOMAIN ) ];
		// set_transient( 'status_successed_message-' . get_current_user_id(), $msg, 300 );
		// wp_safe_redirect( wp_get_referer() );

		return ( $mail_sent );
  }

	/**
	 * Filesystem Uploading contents.
	 * 
	 * @return string
	 */
	public function uploadDir( $file = false, $force = false ) {
		$uploadDir = $this->theUploadDir;
		if( $this->theUploadDir === false ) {
			$uploadDir = wp_get_upload_dir(); // wp_send_json_error( $uploadDir, 200 );
			$uploadDir[ 'basedir' ] = $uploadDir[ 'basedir' ] . '/' . apply_filters( 'futurewordpress/project/teddybearpopupaddon/system/getoption', 'ftp-mediadir', 'futurewordpress' );
			$uploadDir[ 'baseurl' ] = $uploadDir[ 'baseurl' ] . '/' . apply_filters( 'futurewordpress/project/teddybearpopupaddon/system/getoption', 'ftp-mediadir', 'futurewordpress' );
			if( ! is_dir( $uploadDir[ 'basedir' ] ) ) {wp_mkdir_p( $uploadDir[ 'basedir' ] );}
			$this->theUploadDir = $uploadDir;
		}
		// wp_die( print_r( $uploadDir ) );
		$basedir = $uploadDir[ 'basedir' ];
		return ( $file && file_exists( $basedir . '/' . $file ) ) ? $basedir . '/' . $file : ( ( $force ) ? $basedir . '/' . $file : $basedir );
	}
	public function uploadFile() {
		check_ajax_referer( 'futurewordpress/project/teddybearpopupaddon/verify/nonce', '_nonce' );
		
		if( isset( $_FILES[ 'blobFile' ] ) || isset( $_FILES[ 'file' ] ) ) {
			$file = isset( $_FILES[ 'blobFile' ] ) ? $_FILES[ 'blobFile' ] : $_FILES[ 'file' ];
			$blobInfo = isset( $_POST[ 'blobInfo' ] ) ? (array) json_decode( $_POST[ 'blobInfo' ] ) : [];
			// ABSPATH . WP_CONTENT_URL . 
			$file[ 'name' ][0] = isset( $file[ 'name' ][0] ) ? (
				( $file[ 'name' ][0] == 'blob' ) ? ( isset( $blobInfo[ 'name' ] ) ? $blobInfo[ 'name' ] : 'captured.webm' ) : $file[ 'name' ][0]
			) : 'captured.' . explode( '/', $file[ 'type' ][0] )[1];
			$file[ 'full_path' ] = $this->uploadDir( time() . '-' . basename( $file[ 'name' ][0] ), true );$error = false;
			
			// if( $file[ 'size' ][0] > 5000000000 ) {
			// 	$error = sprintf( __( 'File is larger then allowed range. (%d)', 'teddybearsprompts' ), $file[ 'size' ][0] );
			// }
			$extension = strtolower( pathinfo( $file[ 'name' ][0], PATHINFO_EXTENSION ) );
			// $mime = mime_content_type( $file[ 'tmp_name' ][0] );$extension = empty( $extension ) ? $mime : $extension;
			// if( ! in_array( $extension, [ 'mp4', 'text/html' ] ) && ! strstr( $mime, "video/" ) ) {
			// 	$error = sprintf( __( 'File format (%s) is not allowed.', 'teddybearsprompts' ), $extension );
			// }

			// wp_send_json_error( $file, 200 );

			if( $error === false && move_uploaded_file( $file[ 'tmp_name' ][0], $file[ 'full_path' ] ) ) {
				$file[ 'full_url' ] = str_replace( [ $this->theUploadDir[ 'basedir' ] ], [ $this->theUploadDir[ 'baseurl' ] ], $file[ 'full_path' ] );
				$meta = [
					// 'time' => time(),
					'date' => date( 'Y:M:d H:i:s' ),
					'wp_date' => wp_date( 'Y:M:d H:i:s' ),
					...$file
				];
				$oldMeta = (array) WC()->session->get( 'uploaded_files_to_archive' );
				// if( isset( $oldMeta[ 'full_path' ] ) && ! empty( $oldMeta[ 'full_path' ] ) && file_exists( $oldMeta[ 'full_path' ] ) && ! is_dir( $oldMeta[ 'full_path' ] ) ) {unlink( $oldMeta[ 'full_path' ] );}
				// $meta['type'] = apply_filters( 'futurewordpress/project/teddybearpopupaddon/validate/format', $meta['type'], $meta );
				$oldMeta[] = $meta;
				WC()->session->set( 'uploaded_files_to_archive', $oldMeta );
				wp_send_json_success( [
					'message'			=> __( 'Uploaded successfully', 'teddybearsprompts' ),
					'dropZone'		=> $meta
				], 200 );
			} else {
				$error = ( $error ) ? $error : __( 'Something went wrong while tring to upload short clip video.', 'teddybearsprompts' );
				wp_send_json_error( $error, 200 );
			}
		}
		wp_send_json_error( __( 'Error happens.', 'teddybearsprompts' ) );
	}
	public function removeFile() {
		check_ajax_referer( 'futurewordpress/project/teddybearpopupaddon/verify/nonce', '_nonce' );
		$fileInfo = isset( $_POST[ 'fileinfo' ] ) ? (array) json_decode( str_replace( "\\", "", $_POST[ 'fileinfo' ] ) ) : [];

		// if( isset( $fileInfo[ 'full_path' ] ) ) {$_POST[ 'todelete' ] = $fileInfo[ 'full_path' ];}
		// if( isset( $_POST[ 'todelete' ] ) && file_exists( $this->uploadDir( basename( $_POST[ 'todelete' ] ), true ) ) && ! is_dir( $this->uploadDir( basename( $_POST[ 'todelete' ] ), true ) ) ) {

		if( isset( $fileInfo[ 'full_path' ] ) && file_exists( $fileInfo[ 'full_path' ] ) && ! is_dir( $fileInfo[ 'full_path' ] ) ) {
			// unlink( $this->uploadDir( basename( $fileInfo[ 'full_path' ] ), true ) );
			unlink( $fileInfo[ 'full_path' ] );
			$newMeta = (array) WC()->session->get( 'uploaded_files_to_archive' );
			foreach( $newMeta as $i => $meta ) {
				if( $meta[ 'full_path' ] == $fileInfo[ 'full_path' ] ) {
					WC()->session->set( 'uploaded_files_to_archive', $newMeta );
				}
			}
			wp_send_json_success( __( 'File removed from server.', 'teddybearsprompts' ), 200 );
		} else {
			wp_send_json_error( __( 'Failed to delete. Maybe File not found on server or your request doesn\'t contain file data enough.', 'teddybearsprompts' ), 200 );
		}
	}
	public function downloadFile() {
		check_ajax_referer( 'futurewordpress/project/teddybearpopupaddon/verify/nonce', '_nonce' );
		$order_id = isset( $_GET[ 'order_id' ] ) ? $_GET[ 'order_id' ] : false;$fileInfo = [];
		$meta = get_post_meta( $order_id, 'uploaded_files_to_archive', true );
		if( $meta && !empty( $meta ) && isset( $meta[ 'name' ] ) ) {$fileInfo = $meta;}

		if( isset( $fileInfo[ 'full_url' ] ) && isset( $fileInfo[ 'full_path' ] ) && file_exists( $fileInfo[ 'full_path' ] ) && ! is_dir( $fileInfo[ 'full_path' ] ) ) {
			wp_redirect( $fileInfo[ 'full_url' ] );
		} else {
			print_r( $fileInfo );
			wp_die( __( 'File not found', 'teddybearsprompts' ), __( '404 not found', 'teddybearsprompts' ) );
		}
	}
	public function databaseCountries( $countries, $specific = false ) {
		if( function_exists( 'WC' ) ) {
			$countries = WC()->countries->get_countries();
			if( isset( $countries[ 'IL' ] ) ) {
				unset( $countries[ 'IL' ] );
			}
		}
		return ( $specific === false ) ? $countries : ( isset( $countries[ $specific ] ) ? $countries[ $specific ] : false );
	}
	public function countryFlags( $country ) {
		$country = empty( $country ) ? false : $country;
		// https://countryflagsapi.com/svg/
		return ( $country ) ? 'https://flagpedia.net/data/flags/icon/36x27/' . strtolower( $country ) . '.webp' : false;
	}
	public function dashboardPermalink( $id, $user = 'me' ) {
		if( ! defined( 'FUTUREWORDPRESS_PROJECT_DASHBOARDPERMALINK' ) ) {
			$dashboard_permalink = apply_filters( 'futurewordpress/project/teddybearpopupaddon/system/getoption', 'permalink-dashboard', 'dashboard' );
			$dashboard_permalink = site_url( $dashboard_permalink );
			define( 'FUTUREWORDPRESS_PROJECT_DASHBOARDPERMALINK', $dashboard_permalink );
		}
		$profile = ( apply_filters( 'futurewordpress/project/teddybearpopupaddon/system/getoption', 'permalink-userby', 'id' ) == 'id' ) ? FUTUREWORDPRESS_PROJECT_DASHBOARDPERMALINK . '/' . ( ( $id ) ? $id : 'me' ) : FUTUREWORDPRESS_PROJECT_DASHBOARDPERMALINK . '/' . $user;
		return $profile . '/' . apply_filters( 'futurewordpress/project/teddybearpopupaddon/profile/defaulttab', 'profile' );
	}
	public function visitorIP() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
	public function noticeManager( $action, $type, $data ) {
		$notices = get_option( 'fwp_we_make_content_admin_notice', [] );
		if( $action == 'get' ) {
			// foreach( $notices as $i => $notice ) {
			// 	if( $notice[ 'data' ][ 'time' ] && date_create('15 days ago') >= $notice[ 'data' ][ 'time' ] ) {
			// 		unset( $notices[ $i ] );
			// 	}
			// }
			// update_option( 'fwp_we_make_content_admin_notice', $notices );
			return $notices;
		}
		if( $action == 'add' ) {$notices[] = (object) $data;update_option( 'fwp_we_make_content_admin_notice', $notices );}
		if( $action == 'filter' ) {$sortedNotices = [];
			foreach( $notices as $i => $notice ) {
				if( $notice->type == $type ) {
					$sortedNotices[] = $notice;
				}
			}
			return $sortedNotices;
		}
	}

	public function generateRandomString( $default, $length = 10 ) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
	}

}
