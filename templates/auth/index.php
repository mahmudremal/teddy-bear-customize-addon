<?php
/**
 * Checkout video clip shortner template.
 * 
 * @package TeddyBearCustomizeAddon
 */

if( ! is_user_logged_in() ) {
  $userInfo = wp_get_current_user();
  wp_redirect( apply_filters( 'futurewordpress/project/quizandfiltersearch/user/dashboardpermalink', $userInfo->ID, $userInfo->data->user_nicename ) );
} else {
  $auth_provider = get_query_var( 'auth_provider' );$behaveing = get_query_var( 'behaveing' );

  if( $behaveing == 'redirect' ) {
    // print_r
    wp_redirect( apply_filters( 'futurewordpress/project/quizandfiltersearch/socialauth/link', false, $auth_provider ) );
  } else if( $behaveing == 'capture' ) {
    // Handle Social Data from Callback $_GET[ 'code' ]. This data is access token.
    do_action( 'futurewordpress/project/quizandfiltersearch/googledrive/fetchauth', $_GET );
    if( is_user_logged_in() ) {
      $prev = get_user_meta( get_current_user_id(), 'google_auth_code', true );
      if( $prev ) {
        // update_user_meta( get_current_user_id(), 'google_auth_code', $_GET[ 'code' ], $prev );
        // echo 'OLD';
      } else {
        // add_user_meta( get_current_user_id(), 'google_auth_code', $_GET[ 'code' ] );
        // echo 'New';
      }
      wp_redirect( apply_filters( 'futurewordpress/project/quizandfiltersearch/user/dashboardpermalink', get_current_user_id(), 'me' ) );
    } else {
      /**
       * Need to get access token and using ti gather user information form api, then register, if exist, loggedin, then redirect tp dashboard.
       */
      // print_r( get_user_meta( get_current_user_id(), 'google_auth_code', true ) );
      // wp_die( __( 'Is not function yet', 'domain' ) );
    }
  } else {
    // Endpoint is not `redirect` | `capture`
  }
}
?>