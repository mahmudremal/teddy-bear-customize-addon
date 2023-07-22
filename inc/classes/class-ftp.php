<?php
/**
 * File Synchronization Using FTP/SFTP
 * 
 * @package TeddyBearCustomizeAddon
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;

class Ftp {
	use Singleton;
	private $base;
	private $theTable;
	private $lastError;

	private $ftpConnect;
	private $ftpServer;
	private $ftpUsername;
	private $ftpPassword;
	private $ftpDirectory;
	private $localDirectory;
  
	private $theFiletoUpload;
	
	protected function __construct() {
		// global $wpdb;$this->theTable = $wpdb->prefix . 'fwp_ftplogs';
    $this->ftpServer = $this->ftpUsername = $this->ftpPassword = $this->ftpDirectory = $this->localDirectory = false;
    $this->setup_hooks();

	}
	protected function setup_hooks() {
    add_action( 'synchronization_ftp_files', [ $this, 'syncFiles' ], 10, 0 );
    add_filter( 'futurewordpress/project/teddybearpopupaddon/filesystem/mediadirectory', [ $this, 'getAllLocalFiles' ], 10, 2 );
		// add_action( 'init', [ $this, 'deleteFiles' ], 10, 0 );
	}
	public function initialize() {
    $this->lastError        = false;
    $this->ftpConnect       = false;
    $this->ftpServer        = apply_filters( 'futurewordpress/project/teddybearpopupaddon/system/getoption', 'ftp-server', false );
    $this->ftpUsername      = apply_filters( 'futurewordpress/project/teddybearpopupaddon/system/getoption', 'ftp-username', false );
    $this->ftpPassword      = apply_filters( 'futurewordpress/project/teddybearpopupaddon/system/getoption', 'ftp-password', false );
    $this->ftpDirectory     = apply_filters( 'futurewordpress/project/teddybearpopupaddon/system/getoption', 'ftp-remotedir', false );
    $this->localDirectory   = apply_filters( 'futurewordpress/project/teddybearpopupaddon/system/getoption', 'ftp-localdir', false );
    if( $this->localDirectory === false || empty( $this->localDirectory ) ) {
      $this->localDirectory = apply_filters( 'futurewordpress/project/teddybearpopupaddon/filesystem/uploaddir', false );
    }
	}
  public function syncFiles() {
    // if( ! apply_filters( 'futurewordpress/project/teddybearpopupaddon/system/isactive', 'ftp-enable' ) ) {return;}

    if( $this->ftpServer === false ) {$this->initialize();}
    if( $this->ftpServer === false ) {return;}

    $this->ftpConnect  = ftp_connect( $this->ftpServer ) or wp_die( "Could not connect to {$this->ftpServer}" );
    $login_result = ftp_login( $this->ftpConnect , $this->ftpUsername, $this->ftpPassword ) or wp_die( "Could not login" );
    // Enable passive mode
    ftp_pasv( $this->ftpConnect, true );
    // Get raw list of directory contents
    // $raw_list = ftp_rawlist( $this->ftpConnect, $this->ftpDirectory ) or wp_die( "Failed to get directory listing" );
    // print_r( $raw_list );

    $list = $this->scanFtpDirectory( $this->ftpDirectory );
    if( $list && is_array( $list ) && count( $list ) > 0 ) {
      $files = $this->scanLocalDirectory( $this->localDirectory );
      $deleted = $this->deleteFiles( $files, true );
    }
    $copied = $this->copyFtpFiles( $list );

    ftp_close( $this->ftpConnect );
  }
  private function scanFtpDirectory( $dir ) {
    $file_list = ftp_nlist( $this->ftpConnect , $dir );$output = [];
    foreach( $file_list as $item ) {
      if( ! in_array( str_replace( $dir, '', $item ), [ '.', '..', '/.', '/..' ] ) ) {
        $output[] = $item;
      }
    }
    return $output;
  }
  private function copyFtpFiles( $files ) {
    $this->lastError = [];
    foreach( $files as $file ) {
      $destination_file = $this->localDirectory . '/' . pathinfo( $file, PATHINFO_BASENAME );
      if( ftp_get( $this->ftpConnect, $destination_file, $file, FTP_BINARY ) ) {
        // echo "File copied successfully: " . $file . "<br>\n";
      } else {
        $error = error_get_last();
        print_r( $error );
        $this->lastError[] = $error;
        // echo "Failed to copy file: " . $file . ' '. __FILE__ . "<br>\n";
      }
    }
    return ( count( $this->lastError ) <= 0 );
  }
  public function scanLocalDirectory( $dir ) {
    $listed = [];$scan = scandir( $dir, SCANDIR_SORT_ASCENDING );
    foreach( $scan as $i ) {
      if( ! in_array( $i, [ '.', '..' ] ) && file_exists( $dir . '/' . $i ) && ! is_dir( $dir . '/' . $i ) ) {
        $listed[] = $dir . '/' . $i;
      }
    }
    return $listed;
  }
  public function deleteFiles( $files = false ) {
    foreach( $files as $file ) {
      if( file_exists( $file ) && ! is_dir( $file ) ) {
        unlink( $file );
      }
    }
    return true;
  }
  public function getAllLocalFiles( $default, $isUrl ) {
    if( $this->ftpServer === false ) {$this->initialize();}
    $listed=[];$files = $this->scanLocalDirectory( $this->localDirectory );
    if( $isUrl ) {
      $site_url = site_url( '/' );
      foreach( $files as $file ) {
        $listed[] = str_replace( ABSPATH, $site_url, $file );
      }
    } else {
      $listed = $files;
    }
    return $listed;
  }
}
