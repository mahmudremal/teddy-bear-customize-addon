<?php
/**
 * Checkout video clip shortner template.
 * 
 * @package TeddyBearCustomizeAddon
 */

$session_id = get_query_var( 'session_id' );
$payment_status = get_query_var( 'payment_status' );
// print_r( [
//   'session_id' => $session_id,
//   'payment_status' => $payment_status,
//   'result' => $result
//   ] );
$result = apply_filters( 'futurewordpress/project/quizandfiltersearch/payment/stripe/handlesuccess', $session_id );
// if( $result[ 'payment_status' ] == 'paid' ) {echo 'Success';} else {print_r( $result );}
if( isset( $result[ 'customer_details' ] ) && isset( $result[ 'customer_details' ][ 'email' ] ) && !empty( $result[ 'customer_details' ][ 'email' ] ) ) {
  $userInfo = get_user_by( 'email', $result[ 'customer_details' ][ 'email' ] );
}
if( ! isset( $userInfo ) || ! $userInfo ) {
  $userInfo = get_user_by( 'id', get_current_user_id() );
}
$userMeta = array_map( function( $a ){ return $a[0]; }, (array) get_user_meta( $userInfo->ID ) );
$userInfo = (object) wp_parse_args( $userInfo, [ 'meta' => (object) wp_parse_args( $userMeta, apply_filters( 'futurewordpress/project/quizandfiltersearch/usermeta/defaults', (array) $userMeta ) ) ] );
// print_r( $result );
?>


<?php if( isset( $result[ 'payment_status' ] ) ): ?>
  <?php get_header(); ?>
  <div class="wrapper">
    <section class="login-content overflow-hidden">
      <div class="row no-gutters align-items-center bg-white">      
        <div class="col-md-12 col-lg-6 align-self-center">
          <div class="navbar-brand d-flex align-items-center mb-3 justify-content-center text-primary">
            <div class="logo-normal d-none">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="24" height="24" viewBox="0 0 24 24">
                <path d="M13.0801 1H6C4.89543 1 4 1.89543 4 3V21C4 22.1046 4.89543 23 6 23H18C19.1046 23 20 22.1046 20 21V8.73981M13.0801 1C13.664 1 14.2187 1.25513 14.5986 1.69841L19.5185 7.43822C19.8292 7.80071 20 8.26239 20 8.73981M13.0801 1V5.73981C13.0801 7.39666 14.4232 8.73981 16.0801 8.73981H20" stroke="currentColor"></path>
                <path d="M9.15961 13.1986L9.15957 13.1986L9.15961 13.1986Z" stroke="currentColor" stroke-linecap="round"></path>
                <line x1="12.975" y1="12.6181" x2="11.2497" y2="18.6566" stroke="currentColor" stroke-linecap="round"></line>
                <path d="M15.1037 17.8012C15.1037 17.8012 15.1037 17.8013 15.1036 17.8014L15.1037 17.8013L15.1037 17.8012Z" stroke="currentColor" stroke-linecap="round"></path>
              </svg>
            </div>
            <h2 class="logo-title ms-3 mb-0" data-setting="app_name"><?php echo esc_html( ( $result[ 'payment_status' ] == 'paid' ) ? __( 'Payment Successful',   'quiz-and-filter-search-domain' ) : __( 'Payment Failed',   'quiz-and-filter-search-domain' ) );
            esc_html_e( '',   'quiz-and-filter-search-domain' ); ?></h2>
          </div>
          <div class="row justify-content-center pt-5">
            <div class="col-md-8">
              <div class="card  d-flex justify-content-center mb-0">
              <div class="card-body">
                  <h2 class="mt-3 mb-4"><?php echo esc_html( ( $result[ 'payment_status' ] == 'paid' ) ? __( 'Success !',   'quiz-and-filter-search-domain' ) : __( 'Failed !',   'quiz-and-filter-search-domain' ) ); ?></h2>
                  <p class="cnf-mail mb-1"><?php echo wp_kses_post( ( $result[ 'payment_status' ] == 'paid' ) ? 
                  __( 'Congratulations! Your subscription has been successfully processed. As a next step, please click here to sign the necessary documents and complete the process. Thank you for choosing us and we look forward to serving you. If you have any questions or concerns, please don\'t hesitate to reach out to us. We are always here to help.',   'quiz-and-filter-search-domain' ) : 
                  __( nl2br( "Unfortunately, your payment has failed.\nDon\'t worry,we understand that things happen. To try again, please click the button below. \nIf you need assistance or have any questions, please reach out to our support team for help. \n\nPlease note that until your payment is successful, you will not be able to proceed with the contract. We encourage you to try again as soon as possible. Thank you for your understanding." ),   'quiz-and-filter-search-domain' ) ); ?></p>
                  <div class="d-inline-block w-100">
                    <a href="<?php echo esc_url( apply_filters( 'futurewordpress/project/quizandfiltersearch/user/dashboardpermalink', $userInfo->ID, $userInfo->data->user_nicename ) ); ?>/sign/" class="btn btn-primary mt-3"><?php esc_html_e( 'Sign the Document',   'quiz-and-filter-search-domain' ); ?></a>
                  </div>
              </div>
            </div>
            </div>
          </div>   
        
      </div>
        <div class="col-lg-6 d-lg-block d-none bg-primary p-0  overflow-hidden">
          <!-- https://templates.iqonic.design/product/qompac-ui/html/dist/assets/images/auth/01.png -->
          <img src="<?php echo esc_url( TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_URI . '/img/img-to-contract.jpg' ); ?>" class="img-fluid gradient-main" alt="images" loading="lazy" >
        </div>
      </div>
    </section>
  </div>
  <?php get_footer(); ?>
<?php else:
  wp_die( 'Failed your payment. Please try again.' );
  ?>
<?php endif; ?>