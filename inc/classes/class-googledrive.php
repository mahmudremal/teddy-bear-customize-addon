<?php
/**
 * Blocks
 *
 * var_dump( $response );
 * var_dump(curl_error( $ch));
 * var_dump(error_get_last());
 * 
 * @package TeddyBearCustomizeAddon
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;

class GoogleDrive {
	use Singleton;
	private $base;
	private $theTable;
	private $clientId;
	private $lastError;
	private $accessCode;
	private $redirectURL;
	private $accessToken;
	private $clientSecret;
	private $refreshToken;
	private $userDirectory;
	private $parentDirectory;
	private $theFiletoUpload;
	private $authorizationCode;
	
	protected function __construct() {
		global $wpdb;$this->theTable = $wpdb->prefix . 'fwp_googledrive';
		$this->clientId							= false;
		$this->clientSecret					= false;
		$this->redirectURL					= false;$this->lastError = false;
		$this->base									= get_option( 'fwp_google_auth_code', [] );
		// isset( $_GET[ 'code' ] ) ? $_GET[ 'code' ] : get_user_meta( get_current_user_id(), 'google_auth_code', true );
		$this->authorizationCode 		= isset( $this->base[ 'auth_code' ] ) ? $this->base[ 'auth_code' ] : false;
		$this->refreshToken					= isset( $this->base[ 'refresh_token' ] ) ? $this->base[ 'refresh_token' ] : false;
		$this->accessToken					= isset( $this->base[ 'access_token' ] ) ? $this->base[ 'access_token' ] : false;
		$this->accessCode						= isset( $this->base[ 'access_code' ] ) ? $this->base[ 'access_code' ] : false;
		$this->theFiletoUpload			= false;$this->parentDirectory = false;$this->userDirectory = false;
		
		// $this->refreshToken 				= '1//04qYI33SqU8W2CgYIARAAGAQSNwF-L9IrZq6NbCsRFe9Unnw8Jf6FQoQsIbFjv17cEeKGqiwbTSjtJHncdYfC5jbxaRtv5eAqnIU';

		$this->setup_hooks();

		
		// if( isset( $this->base[ 'token' ] ) ) {
		// 	$this->base[ 'auth_code' ] = $this->base[ 'token' ];unset( $this->base[ 'token' ] );
		// 	update_option( 'fwp_google_auth_code', $this->base );
		// }

	}
	protected function setup_hooks() {
		add_action( 'init', [ $this, 'initialize' ], 1, 0 );
		add_action( 'init', [ $this, 'developmentMode' ], 10, 0 );
		add_action( 'wp_ajax_futurewordpress/project/teddybearpopupaddon/action/submitarchives', [ $this, 'submitArchives' ], 10, 0 );
		add_action( 'wp_ajax_futurewordpress/project/teddybearpopupaddon/action/deletearchives', [ $this, 'deleteArchives' ], 10, 0 );
		add_filter( 'futurewordpress/project/teddybearpopupaddon/filesystem/ziparchives', [ $this, 'zipArchives' ], 10, 2 );
		add_action( 'futurewordpress/project/teddybearpopupaddon/googledrive/fetchauth', [ $this, 'fetchAndSaveAuthCode' ], 0, 1 );

		add_filter( 'futurewordpress/project/teddybearpopupaddon/socialauth/link', [ $this, 'goForAuthCode' ], 10, 2 );
	}
	public function initialize() {
		$this->redirectURL					= apply_filters( 'futurewordpress/project/teddybearpopupaddon/socialauth/redirect', '/handle/google', 'google' );
		$this->parentDirectory			= apply_filters( 'futurewordpress/project/teddybearpopupaddon/system/getoption', 'auth-googledrivefolder', '1MliOhH16m413OmiBGJM90cTmWUcwNUP4' );
		$this->clientSecret					= apply_filters( 'futurewordpress/project/teddybearpopupaddon/system/getoption', 'auth-googleclientsecret', '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' );
		$this->clientId							= apply_filters( 'futurewordpress/project/teddybearpopupaddon/system/getoption', 'auth-googleclientid', '719552948296-d14739tknbd33tv16b932pb859kcvums.apps.googleusercontent.com' );
	}
	public function developmentMode() {
		if( ! isset( $_GET[ 'googletest' ] ) ) {return;}
		
		// $this->parentDirectory = '1YGPIeK95atQJpgOCqmgcexVj7zZthcVG';
		// $this->fetchAccessToken();
		
		// $this->get_access_token( isset( $_GET[ 'code' ] ) ? $_GET[ 'code' ] : false );
		// print_r( $this->uploadToGoogleDrive( $this->theFiletoUpload ) );
		// print_r( get_option( 'fwp_google_auth_code', [] ) );

		// print_r( $this->uploadToGoogleDrive( $this->theFiletoUpload ) );
		
		try{
			// $user_id = get_current_user_id();
			// $user_data = get_userdata( $user_id );
			// $user_dir = ( ! empty( $user_data->user_email ) ) ? $user_data->user_email : get_user_meta( $user_id, 'email', true );
			// $this->userDirectory = $this->createDirectory( $user_dir );
			// $drive_file_move = $this->uploadFileWithMeta( TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_PATH . '/img/img-to-contract.jpg' );
			// print_r( $drive_file_move? $user_dir : 'Failed' );
		} catch(\Exception $e) {
			print_r( $e->getMessage() );
		}
		wp_die();


	}
	public function moveFile( $file_id ) {
		if( ! $this->refreshAccessToken() ) {
			return $this->lastError;
    }
		$url = 'https://www.googleapis.com/drive/v3/files/' . $file_id;
		$data = array(
			'addParents' => $this->userDirectory,
			'removeParents' => 'root',
		);
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => 'PATCH',
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bearer ' . $this->accessToken,
				'Content-Type: application/json'
			),
		));
		$response = curl_exec($curl);
		$error = curl_error($curl);
		curl_close($curl);
		print_r( $response );
		if ($error) {
			echo "Error: " . $error;
		} else {
			echo "File moved successfully!";
		}
		return true;
	}


	public function zipArchives( $default, $user_id ) {
		global $wpdb;
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$this->theTable} WHERE user_id=%d ORDER BY id DESC LIMIT 0, 500;", $user_id ) );
		// print_r( $rows );
		return $rows;
	}
	public function submitArchives() {
		global $wpdb;
		$data = (object) wp_parse_args( $_POST, [ 'title' => '', 'month' => date( 'M' ), 'year' => date( 'Y' ), 'userid' => get_current_user_id() ] );
		$month = $data->month . ' ' . $data->year;
		$data->title = stripslashes( $data->title );
		$newMeta = (array) WC()->session->get( 'uploaded_files_to_archive' );$file_list = [];
		foreach( $newMeta as $i => $meta ) {
			if( isset( $meta[ 'full_path' ] ) && file_exists( $meta[ 'full_path' ] ) && ! is_dir( $meta[ 'full_path' ] ) ) {
				$file_list[] = $meta[ 'full_path' ];
			}
		}
		
		$user_id = is_admin() ? $data->userid : get_current_user_id();
		$fileName = 'archive-' . $data->userid . '-' . strtolower( $month );
		$record_count = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->theTable} WHERE user_id=%d AND formonth=%s;", $user_id, $month ) );
		if( $record_count && ! empty( $record_count->drive_id ) ) {
			$fileName = $fileName . '-' . date( 'd-H' );
		}
		$archive_path = apply_filters( 'futurewordpress/project/teddybearpopupaddon/filesystem/uploaddir', false ) . '/' . $fileName . '.zip';
		$result = $this->archiveFiles( $file_list, $archive_path );$donotDeletePreviousFile = true;
		if( $result ) {
			// $record_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$this->theTable} WHERE user_id=%d AND formonth=%s;", $user_id, $month ) );
			
			if( ! $record_count || $donotDeletePreviousFile ) {
				$wpdb->insert( $this->theTable, [
					'user_id' => $user_id,
					'title' => $data->title,
					'formonth' => $month,
					'drive_id' => '',
					'file_path' => site_url( str_replace( [ ABSPATH ], [ '' ], $archive_path ) ),
					'status' => 'active',
					'archived' => maybe_serialize( json_encode( $newMeta ) ) // Temporary Meta value. But infuture, google drive info
				], [ '%d', '%s', '%s', '%s', '%s', '%s', '%s' ] );
			} else {
				/**
				 * First delete previous drive file. Local file is replaced by Archive class.
				 * Need to code.
				 */
				if( ! empty( $record_count->drive_id ) ) {
					try {$is_success = $this->deleteFileOnDrive( $record_count->drive_id );}
					catch(\Exception $e) {$this->lastError = $e->getMessage();}
				}
				$wpdb->update( $this->theTable, [
					'title' => $data->title,
					'drive_id' => '',
					'file_path' => site_url( str_replace( [ ABSPATH ], [ '' ], $archive_path ) ),
					'status' => 'active',
					'archived' => maybe_serialize( json_encode( $newMeta ) )
				], [
					'user_id' => $user_id,
					'formonth' => $month,
				], [ '%d', '%s' ] );
			}
			foreach( $file_list as $file) {if( file_exists( $file ) && ! is_dir( $file ) ) {unlink( $file );}}
			
			$user_data = get_userdata( $user_id );
			$user_dir = ( ! empty( $user_data->user_email ) ) ? $user_data->user_email : get_user_meta( $user_id, 'email', true );
			$user_dir = empty( $user_dir ) ? 'user-id-' . $user_id : $user_dir;
			
			$this->userDirectory = $this->createDirectory( $user_dir );
			
			$is_sent = $this->sendToDrive( $archive_path, $data->title );
			
			if( $is_sent && isset( $is_sent[ 'id' ] ) ) {
				$newMeta = [ 'local' => $newMeta, 'drive' => $is_sent, 'info' => [ 'time' => time() ] ];
				$wpdb->update( $this->theTable, [
					'drive_id' 	=> $is_sent[ 'id' ],
					'file_path' => 'https://drive.google.com/uc?id=' . $is_sent[ 'id' ] . '&export=download',
					'archived' 	=> maybe_serialize( json_encode( $newMeta ) )
				], [
					'user_id' => $user_id,
					'formonth' => $month,
				] );
				unlink( $archive_path );
			}
			WC()->session->set( 'uploaded_files_to_archive', [] );
			wp_send_json_success( [ 'message' => __( 'Archived Successfully.', 'teddybearsprompts' ), 'hooks' => [ 'reload-page' ] ], 200 );
		} else {
			wp_send_json_error( __( 'Problem detected while creating archive.', 'teddybearsprompts' ), 200 );
		}
	}
	public function deleteArchives() {
		global $wpdb;if( ! apply_filters( 'futurewordpress/project/teddybearpopupaddon/system/isactive', 'general-archivedelete' ) ) {return;}
		$user_id = is_admin() ? $_POST[ 'userid' ] : get_current_user_id();
		$archive = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->theTable} WHERE user_id=%d AND id=%s;", $user_id, $_POST[ 'archive' ] ) );
		$file_path = str_replace( [ site_url( '/' ) ], [ ABSPATH ], $archive->file_path );

		if( ! empty( $archive->drive_id ) ) {
			try {
				$is_success = $this->deleteFileOnDrive( $archive->drive_id );
				// wp_send_json_error( $is_success );
			} catch(\Exception $e) {
				$this->lastError = $e->getMessage();
				// wp_send_json_error( $this->lastError );
			}
		}
		if( ! empty( $archive->drive_id ) && file_exists( $file_path ) && ! is_dir( $file_path ) ) {unlink( $file_path );}
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$this->theTable} WHERE user_id=%d AND id=%s;", $user_id, $_POST[ 'archive' ] ) );
		wp_send_json_success( [ 'message' => __( 'Archive removed from server successfully!', 'teddybearsprompts' ), 'hooks' => [ 'reload-page' ] ], 200 );
	}
	public function archiveFiles( $file_list, $destination ) {
		// error_reporting(E_ALL);
		// ini_set('display_errors', true);
		$zip = new \ZipArchive();$errors = false;
		if( $zip->open( $destination, \ZIPARCHIVE::CREATE | \ZIPARCHIVE::OVERWRITE ) !== TRUE ) {
			return false;
		}
		foreach( $file_list as $file) {
			if( $zip->addFile( $file, basename( $file ) ) ) {
				// unlink( $file );// Unlink from here is not creating archive.
			} else {
				// echo "Error adding file: " . basename( $file ) . "\n";
				$errors = true;
			}
		}
		$zip->close();
		return ( ! $errors );
	}
	private function sendToDrive( $file, $description = '' ) {
		$this->theFiletoUpload = $file;
		// if( ! $this->refreshAccessToken() ) {}
		// $access_token = $this->accessToken;
		// $file_content = file_get_contents(  );
		// $mime_type = mime_content_type( $this->theFiletoUpload );
		
		try {
			// $drive_file_id = $this->uploadFileToDrive( $file_content, $mime_type );
			// if( $drive_file_id ) {
			// 	$file_meta = [
			// 		'name'						=> basename( $this->theFiletoUpload ),
			// 		'mimeType'				=> $mime_type,
			// 		'addParents'			=> $this->userDirectory,
			// 		'removeParents'		=> null, // 'root',
			// 		// 'parents'					=> [ $this->createDirectory( $this->userDirectory ) ]
			// 	];
			// 	$drive_file_meta = $this->updateFileMeta( $drive_file_id, $file_meta );
			// 	return ( $drive_file_meta ) ? $drive_file_meta : false;
			$is_success = $this->uploadFileWithMeta( $this->theFiletoUpload, $description );
			return $is_success;
		} catch(\Exception $e) {
			$this->lastError = $e->getMessage();
			return false;
		}
		
	}



	public function goForAuthCode( $default, $provider ) {
		if( $provider !== 'drive' ) {
			return $default;
		} else {
			$googleOauthURL = "https://accounts.google.com/o/oauth2/auth?response_type=code&client_id={$this->clientId}&redirect_uri={$this->redirectURL}&scope=" . urlencode( 'https://www.googleapis.com/auth/drive' ) . "&access_type=offline&prompt=consent\r\n";
			// $googleOauthURL = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode( 'https://www.googleapis.com/auth/drive' ) . '&redirect_uri=' . $this->redirectURL . '&response_type=code&client_id=' . $this->clientId . '&access_type=online';
			return $googleOauthURL;
		}
	}
	public function refreshAccessToken() {
		if( $this->refreshToken === false ) {
			$token = $this->get_access_token();
			if( $token !== false ) {
				// Can proceed
			}
		}
		$url = 'https://www.googleapis.com/oauth2/v4/token';
		$body = [
			'refresh_token' => $this->refreshToken,
			'client_id' => $this->clientId,
			'client_secret' => $this->clientSecret,
			'grant_type' => 'refresh_token'
		];

		$curl = curl_init();
		curl_setopt_array( $curl, array(
			CURLOPT_URL => "https://oauth2.googleapis.com/token",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => http_build_query( $body),
			CURLOPT_HTTPHEADER => array(
				"Content-Type: application/x-www-form-urlencoded"
			),
		));
		// Send the request and get the response
		$response = curl_exec( $curl);
		$err = curl_error( $curl);
		curl_close( $curl);

		// Check for errors
		if( $err) {
			$this->lastError = "cURL Error #:" . $err;
			return false;
		} else {
			// Parse the response and return the access token
			$response = json_decode( $response, true);
			// return $response;
			if(isset( $response['access_token'] ) ) {
				$this->accessToken = $response['access_token'];
				$this->base[ 'access_token' ] = $this->accessToken;
				update_option( 'fwp_google_auth_code', $this->base );
				return true;
			} else if( $response['error'] == 'invalid_grant') {
				// If the error is 'invalid_grant', the refresh token may be expired or invalid
				// Obtain a new authorization code and repeat the authorization flow to obtain a new refresh token
				$this->lastError = "Error: Invalid refresh token";
				return false;
		} else {
				// Handle other errors
				$this->lastError = $response['error'];
				return false;
			}
		}
	}
	public function fetchAndSaveAuthCode( $request ) {
		if( ! isset( $request[ 'code' ] ) ) {return;}
		update_option( 'fwp_google_auth_code', [
      'auth_code'     => $request[ 'code' ],
      'time'      => time(),
			...$request
    ] );
		// print_r( get_option( 'fwp_google_auth_code', [] ) );
	}
	public function get_access_token() {
		// Set up the cURL request
		$body = [
			'code'					=> $this->authorizationCode,
			'client_id'			=> $this->clientId,
			'client_secret'	=> $this->clientSecret,
			'redirect_uri'	=> $this->redirectURL,
			'grant_type'		=> 'authorization_code'
		];
		$curl = curl_init();
		curl_setopt_array( $curl, array(
			CURLOPT_URL => "https://oauth2.googleapis.com/token",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => http_build_query( $body ),
			CURLOPT_HTTPHEADER => array(
				"Content-Type: application/x-www-form-urlencoded"
			),
		) );
		// Send the request and get the response
		$response = curl_exec( $curl);
		$err = curl_error( $curl);
		curl_close( $curl);
		// print_r( [ $response, $body ] );
		// Check for errors
		if( $err ) {
			$this->lastError = "cURL Error #:" . $err;
			return false;
		} else {
			// Parse the response and return the access token
			$response = json_decode( $response, true );
			if( isset( $response[ 'access_token' ] ) ) {
				$this->accessToken = $response[ 'access_token' ];
				$this->refreshToken = $response[ 'refresh_token' ];
				$this->base = wp_parse_args( $response, $this->base );
				update_option( 'fwp_google_auth_code', $this->base );
			}
			return $response[ 'access_token' ];
		}
	}

	
	public function uploadFileToDrive( $file_content, $mime_type ) {
		$apiURL = 'https://www.googleapis.com/upload/drive/v3/files' . '?uploadType=media';
		 
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $apiURL );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type: '.$mime_type, 'Authorization: Bearer '. $this->accessToken ) );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $file_content );
		$data = json_decode(curl_exec( $ch), true );
		$http_code = curl_getinfo( $ch,CURLINFO_HTTP_CODE );
		 
		if ( $http_code != 200) {
			$error_msg = 'Failed to upload file to Google Drive';
			if (curl_errno( $ch)) {
				$error_msg = curl_error( $ch );
			}
			throw new \Exception('Error '.$http_code.': '.$error_msg );
		}
		return $data['id'];
	}
	public function updateFileMeta( $file_id, $file_meatadata ) {
		$apiURL = 'https://www.googleapis.com/drive/v3/files/' . $file_id;
		$apiURL = "https://www.googleapis.com/drive/v3/files/" . $file_id . "?fields=id,parents";
		 
		$ch = curl_init( );
		curl_setopt( $ch, CURLOPT_URL, $apiURL );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer '. $this->accessToken ) );
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'PATCH' );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $file_meatadata) );
		$data = json_decode( curl_exec( $ch), true );
		$http_code = curl_getinfo( $ch,CURLINFO_HTTP_CODE );
		 
		print_r( $data );
		
		if ( $http_code != 200) {
			$error_msg = 'Failed to update file metadata';
			if (curl_errno( $ch)) {
				$error_msg = curl_error( $ch );
			}
			throw new \Exception('Error '.$http_code.': '.$error_msg );
		}

		return $data;
	}
	public function uploadFileWithMeta( $file_path, $description = '' ) {
		if( ! $this->refreshAccessToken() ) {
			return false;
    }
		$access_token = $this->accessToken;
		$folder_id = $this->userDirectory;
		$url = "https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart";
		$headers = array(
			"Authorization: Bearer $access_token",
			"Content-Type: multipart/related; boundary=file-upload"
		);

		$file_name = basename($file_path);
		$file_content = file_get_contents($file_path);

		$body = "--file-upload\r\n";
		$body .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
		$body .= json_encode(array(
			'name'						=> $file_name,
			'parents'					=> array($folder_id),
			'description'			=> $description
		)) . "\r\n";
		$body .= "--file-upload\r\n";
		$body .= "Content-Type: application/octet-stream\r\n\r\n";
		$body .= $file_content . "\r\n";
		$body .= "--file-upload--";

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $body,
			CURLOPT_HTTPHEADER => $headers,
		));

		$response = curl_exec($curl);
		$error = curl_error($curl);

		$result = json_decode( $response, true );
		// print_r( $result );

		curl_close($curl);

		if ($error) {
			throw new \Exception('Error '.$error );
			return false;
		} else {
			return $result;
		}
	}
	public function deleteFileOnDrive( $fileId ) {
    if( ! $this->refreshAccessToken() ) {
			return $this->lastError;
    }

    $ch = curl_init();
    $url = 'https://www.googleapis.com/drive/v3/files/'.$fileId;
    $headers = array(
        'Authorization: Bearer ' . $this->accessToken
    );
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 204) {
			return true;
    } else {
			$error_msg = ( $http_code == 404 ) ? __( 'File Not found', 'teddybearsprompts' ) : __( 'Failed to delete file from drive.', 'teddybearsprompts' );
			throw new \Exception('Error '.$http_code.': ' . $error_msg );
    }
	}
	public function createDirectory( $folderName ) {
		$directory_exists = $this->searchDirectory( $folderName );
		if( $directory_exists ) {
			return $directory_exists;
		}
		if( ! $this->refreshAccessToken() ) {
			return $this->lastError;
    }
		$url = "https://www.googleapis.com/drive/v3/files";
		$headers = array(
			"Authorization: Bearer " . $this->accessToken,
			"Content-Type: application/json"
		);
		$data = array(
			'name'				=> $folderName,
			'parents'			=> [ $this->parentDirectory ],
			'mimeType'		=> 'application/vnd.google-apps.folder'
		);
		$jsonData = json_encode($data);
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_POST => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_POSTFIELDS => $jsonData
		));
		$response = curl_exec($curl);
		$response = json_decode( $response, true );
		// print_r( $response );
		$error_msg = __( 'Error creating folder:', 'teddybearsprompts' ) . ( ! empty( curl_error( $curl ) ) ? ': ' . curl_error( $curl ) : '' );
		curl_close($curl);
		if (curl_errno($curl)) {
			// throw new \Exception( $error_msg );
			return false;
		} else {
			// echo 'Folder created successfully';
			return $response[ 'id' ];
		}
	}
	public function searchDirectory( $folderName ) {
		if( ! $this->refreshAccessToken() ) {
			return $this->lastError;
    }
		$searchQuery = "mimeType='application/vnd.google-apps.folder' and trashed = false and name='".$folderName."'";
		$url = "https://www.googleapis.com/drive/v3/files?q=" . urlencode( $searchQuery );
		$headers = array(
			"Authorization: Bearer " . $this->accessToken,
			"Content-Type: application/json"
		);
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => $headers,
		));
		$response = curl_exec($curl);
		if(curl_errno($curl)) {
			return false;
		} else {
			$result = json_decode($response, true);
			if( $result[ 'files' ] && count( $result[ 'files' ] ) > 0 ) {
				return $result[ 'files' ][0][ 'id' ];
			} else {
				return false;
			}
		}
		curl_close($curl);
	}
	public function downloadFileFromGoogleDrive($fileId, $saveTo) {
		$url = "https://drive.google.com/uc?export=download&id=" . $fileId;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers = curl_exec($ch);
		preg_match('/filename="(.*?)"/', $headers, $matches);
		$filename = $matches[1];

		$saveFilePath = $saveTo . '/' . $filename;
		$fp = fopen($saveFilePath, 'w');
		
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
		return true;
	}
	
	
}
