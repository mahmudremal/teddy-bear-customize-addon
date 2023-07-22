<?php
/**
 * Checkout video clip shortner template.
 * 
 * @package TeddyBearCustomizeAddon
 */
// print_r( hex2bin( get_query_var( 'pay_retainer' ) ) );
$userInfo = get_user_by( 'id', hex2bin( get_query_var( 'pay_retainer' ) ) );
if( $userInfo ) {
  $userMeta = array_map( function( $a ){ return $a[0]; }, (array) get_user_meta( $userInfo->ID ) );
  $userInfo = (object) wp_parse_args( $userInfo, [
    'meta'          => (object) apply_filters( 'futurewordpress/project/quizandfiltersearch/usermeta/defaults', (array) $userMeta )
  ] );
  $errorHappens = false;


  $payment_link = ( empty( $userInfo->meta->monthly_retainer ) || $userInfo->meta->monthly_retainer <= 0 ) ? false : apply_filters( 'futurewordpress/project/quizandfiltersearch/payment/stripe/paymentlink', [
    'quantity'	=> 1,
    'price_data' => [
      'currency' => apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'stripe-currency', 'usd' ),
      'unit_amount' => (int) ( $userInfo->meta->monthly_retainer * 100 ), // Unit amount in cent | number_format( $userInfo->meta->monthly_retainer, 2 ),
      'product_data' => [
        'name' => apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'stripe-productname', __( 'Subscription',   'quiz-and-filter-search-domain' ) ),
        'description' => apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'stripe-productdesc', __( 'Payment for',   'quiz-and-filter-search-domain' ) . ' ' . get_option( 'blogname', 'We Make Content' ) ),
        'images' => [ apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'stripe-productimg', esc_url( TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_URI . '/icons/Online payment_Flatline.svg' ) ) ],
      ],
    ]
  ], true );
  if( $payment_link && ! empty( $payment_link ) ) {
    wp_redirect( $payment_link );
  } else {
    wp_die( __( 'Something error happens with the backend. please contact with site administrative, for this coincident.',   'quiz-and-filter-search-domain' ), __( 'Technical error',   'quiz-and-filter-search-domain' ) );
  }
} else {
  wp_die( __( 'We\'re in a trap. or maybe we did some wrong thing.',   'quiz-and-filter-search-domain' ), __( 'Permalink structure mismatched.',   'quiz-and-filter-search-domain' ) );
}


// https://example.net/pay_retainer/3234/
?>
