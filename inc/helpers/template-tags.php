<?php
/**
 * Custom template tags for the theme.
 *
 * @package TeddyBearCustomizeAddon
 */
if( ! function_exists( 'is_FwpActive' ) ) {
  function is_FwpActive( $opt ) {
    if( ! defined( 'TEDDY_BEAR_CUSTOMIZE_ADDON_OPTIONS' ) ) {return false;}
    return ( isset( TEDDY_BEAR_CUSTOMIZE_ADDON_OPTIONS[ $opt ] ) && TEDDY_BEAR_CUSTOMIZE_ADDON_OPTIONS[ $opt ] == 'on' );
  }
}
if( ! function_exists( 'get_FwpOption' ) ) {
  function get_FwpOption( $opt, $def = false ) {
    if( ! defined( 'TEDDY_BEAR_CUSTOMIZE_ADDON_OPTIONS' ) ) {return false;}
    return isset( TEDDY_BEAR_CUSTOMIZE_ADDON_OPTIONS[ $opt ] ) ? TEDDY_BEAR_CUSTOMIZE_ADDON_OPTIONS[ $opt ] : $def;
  }
}
if( ! function_exists( 'teddy_fixSlashes' ) ) {
  function teddy_fixSlashes($path) {
    return str_replace(['/', '\/'], [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $path);
  }
}

