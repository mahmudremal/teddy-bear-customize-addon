<?php
/**
 * File Synchronization Using FTP/SFTP
 * 
 * @package TeddyBearCustomizeAddon
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;

class Events {
	use Singleton;
	private $base;
	protected function __construct() {
		// global $wpdb;$this->theTable = $wpdb->prefix . 'fwp_ftplogs';
    $this->base = [];
		$this->setup_hooks();

	}
	protected function setup_hooks() {
    add_filter( 'cron_schedules', [ $this, 'add_custom_intervals' ], 99, 1 );
    // register_activation_hook( TEDDY_BEAR_CUSTOMIZE_ADDON__FILE__, [ $this, 'register_activation_hook' ] );
    register_deactivation_hook( TEDDY_BEAR_CUSTOMIZE_ADDON__FILE__, [ $this, 'register_deactivation_hook' ] );
    add_action( 'wp', [ $this, 'wp_schedule_event' ], 10, 0 );
	}
  public function register_activation_hook() {
    // print_r( apply_filters( 'futurewordpress/project/teddybearpopupaddon/system/getoption', 'ftp-interval', 'hourly' )); wp_die();
    wp_schedule_event( time(), apply_filters( 'futurewordpress/project/teddybearpopupaddon/system/getoption', 'ftp-interval', 'hourly' ), 'synchronization_ftp_files' );
  }
  public function register_deactivation_hook() {
    wp_clear_scheduled_hook( 'synchronization_ftp_files' );
  }
  public function wp_schedule_event() {
    if( ! wp_next_scheduled( 'synchronization_ftp_files' ) ) {
      // hourly | every_two_hours | every_three_hours.
      wp_schedule_event( time(), apply_filters( 'futurewordpress/project/teddybearpopupaddon/system/getoption', 'ftp-interval', 'hourly' ), 'synchronization_ftp_files' );
    }
  }
  /**
   * You can customize the interval to suit your needs by defining a different custom interval using the wp_get_schedules() function. For example, you could define an interval to run every 3 hours by modifying the wp_get_schedules() function as follows.
   * 
   * Then, you can modify the wp_schedule_event() function to use the every_three_hours interval.
   * wp_schedule_event(time(), 'every_three_hours', 'my_function_hook');
   */
  public function add_custom_intervals( $schedules ) {
    switch( apply_filters( 'futurewordpress/project/teddybearpopupaddon/system/getoption', 'ftp-interval', 'hourly' ) ) {
      case '5mins':
        $schedules[ '5mins' ] = isset( $schedules[ '5mins' ] ) ? $schedules[ '5mins' ] : ['interval' => 300, 'display' => __( 'Every 5 Minutes', 'teddybearsprompts' )];
        break;
      case 'hourly':
        $schedules[ 'hourly' ] = isset( $schedules[ 'hourly' ] ) ? $schedules[ 'hourly' ] : ['interval' => 3600, 'display' => __( 'Hourly', 'teddybearsprompts' )];
        break;
      case '2hours':
        $schedules[ '2hours' ] = isset( $schedules[ '2hours' ] ) ? $schedules[ '2hours' ] : ['interval' => 7200, 'display' => __( 'Every Two hours', 'teddybearsprompts' )];
        break;
      case '3hours':
        $schedules[ '3hours' ] = isset( $schedules[ '3hours' ] ) ? $schedules[ '3hours' ] : ['interval' => 10800, 'display' => __( 'Every Three hours', 'teddybearsprompts' )];
        break;
      case '4hours':
        $schedules[ '4hours' ] = isset( $schedules[ '4hours' ] ) ? $schedules[ '4hours' ] : ['interval' => 14400, 'display' => __( 'Every Four hours', 'teddybearsprompts' )];
        break;
      case 'daily':
        $schedules[ 'daily' ] = isset( $schedules[ 'daily' ] ) ? $schedules[ 'daily' ] : ['interval' => 86400, 'display' => __( 'Daily', 'teddybearsprompts' )];
        break;
      case 'weekly':
        $schedules[ 'weekly' ] = isset( $schedules[ 'weekly' ] ) ? $schedules[ 'weekly' ] : ['interval' => 604800, 'display' => __( 'Weekly', 'teddybearsprompts' )];
        break;
      case 'fifteendays':
        $schedules[ 'fifteendays' ] = isset( $schedules[ 'fifteendays' ] ) ? $schedules[ 'fifteendays' ] : ['interval' => 1296000, 'display' => __( 'fifteendays', 'teddybearsprompts' )];
        break;
      case 'monthly':
        $schedules[ 'monthly' ] = isset( $schedules[ 'monthly' ] ) ? $schedules[ 'monthly' ] : ['interval' => 2592000, 'display' => __( 'Monthly', 'teddybearsprompts' )];
        break;
      default:
        break;
    }
    // if( isset( $_GET[ 'test-mode' ] ) ) {
    //   print_r( $schedules );wp_die();
    // }
    
    return $schedules;
  }
  
}
