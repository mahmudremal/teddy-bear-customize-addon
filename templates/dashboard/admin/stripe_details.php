<?php
$subscribers = get_users( [ 'role__in' => [ 'lead' ] ] ); // 'author',  subscriber
$date_formate = get_option( 'date_format' );global $wpdb;
// print_r( $subscribers );wp_die();

$log = $wpdb->get_row( $wpdb->prepare(
  "SELECT * FROM {$wpdb->prefix}fwp_stripe_payments WHERE id=%s;",
  $args[ 'split' ][2]
) );
if( $log && method_exists( $log, 'archived' ) ) {
  
}
$log->archived = json_decode( maybe_unserialize( $log->archived ), true );
$userInfo = get_user_by( 'id', $log->user_id );
$userMeta = array_map( function( $a ){ return $a[0]; }, (array) get_user_meta( $userInfo->ID ) );
$userInfo = (object) wp_parse_args( $userInfo, [ 'meta' => (object) wp_parse_args( $userMeta, apply_filters( 'futurewordpress/project/quizandfiltersearch/usermeta/defaults', (array) $userMeta ) ) ] );
// print_r( $log );
?>
<div>
  <div class="row">
    <div class="col-lg-12">
    <div class="card card-full-width rounded m-auto to-print-this-card">
        <div class="card-body">
        <div class="row">
          <div class="col-sm-12">  
          <h4 class="mb-3"><?php esc_html_e( 'Invoice', 'quiz-and-filter-search-domain' ); ?>  #<?php echo esc_html( substr( $log->session_id, -20 ) ); ?></h4>
          <h3 class="mb-5"><?php echo esc_html( $userInfo->meta->first_name . ' ' . $userInfo->meta->last_name ); ?></h3>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-4">
          <h5>Bill to:</h5>
          <p><?php echo esc_html( $userInfo->meta->first_name . ' ' . $userInfo->meta->last_name ); ?></p>
          <p><?php echo esc_html( apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'general-address', 'Address not available' ) ); ?> <br>
          <?php echo esc_html( get_option( 'admin_email', '' ) ); ?></p>
          </div>
          <div class="col-lg-3">
          <h5><?php esc_html_e( 'Bill from:', 'quiz-and-filter-search-domain' ); ?></h5>
          <p><?php echo esc_html( $userInfo->meta->first_name . ' ' . $userInfo->meta->last_name ); ?></p>
          <p><?php echo esc_html( ! empty( $userInfo->meta->address1 ) ? $userInfo->meta->address1 : (
            ! empty( $userInfo->meta->address2 ) ? $userInfo->meta->address2 : __( 'Address not available', 'quiz-and-filter-search-domain' )
          ) ); ?><br>
            <?php echo esc_html( $log->customer_email ); ?> </p>
          </div>
          <div class="col-lg-3">
          <h5><?php esc_html_e( 'Bill fromAmount:', 'quiz-and-filter-search-domain' ); ?></h5>
          <h4>$<?php echo esc_html( number_format_i18n( ( $log->amount / 100 ), 2 ) ); ?></h4>
          </div>
          <div class="col-lg-2 text-end">
          <h5><?php esc_html_e( 'Invoice Date', 'quiz-and-filter-search-domain' ); ?></h5>
          <p class=""><?php echo esc_html( wp_date( 'd M Y', strtotime( $log->created_at ) ) ); ?></p>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-12 mt-4">
          <div class="table-responsive-lg">
              <table class="table billing">
              <thead>
                <tr>
                  <th scope="col"><?php esc_html_e( 'Description', 'quiz-and-filter-search-domain' ); ?></th>
                  <th scope="col"><?php esc_html_e( 'Price', 'quiz-and-filter-search-domain' ); ?></th>
                  <th scope="col"><?php esc_html_e( 'Quantity', 'quiz-and-filter-search-domain' ); ?></th>
                  <th class="text-end" scope="col"><?php esc_html_e( 'Sub-Total', 'quiz-and-filter-search-domain' ); ?></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="border-bottom-0">
                    <h6 class="mb-0"><?php echo esc_html( ( $text = apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'stripe-productname', '' ) && ! empty( $text ) ) ? $text : 'Monthly Retainer' ); ?></h6>
                    <p class="mb-0"><?php echo esc_html( ( $text = apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'stripe-productdesc', '' ) && ! empty( $text ) ) ? $text : '' ); ?></p>
                  </td>
                  <td class="border-bottom-0">$<?php echo esc_html( number_format_i18n( ( $log->amount / 100 ), 2 ) ); ?></td>
                  <td class="border-bottom-0">1</td>
                  <td class="text-end border-bottom-0">$<?php echo esc_html( number_format_i18n( ( $log->amount / 100 ), 2 ) ); ?></td>
                </tr>
                <tr><td colspan="4" class="border-bottom-0"></td></tr>
                <tr><td colspan="4"></td></tr>
                <tr>
                  <td>
                    <h5 class="mb-0"><b><?php esc_html_e( 'Net Amount', 'quiz-and-filter-search-domain' ); ?></b></h5>
                  </td>
                  <td></td>
                  <td></td>
                  <td class="text-end"><b>$<?php echo esc_html( number_format_i18n( ( $log->amount / 100 ), 2 ) ); ?></b></td>
                </tr>
              </tbody>
              </table>
          </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-12">
          <div class="d-flex justify-content-center mt-4">
              <button type="button" class="btn btn-primary print-this-page" data-print=".to-print-this-card" role="button">Print</button>
          </div>
          </div>
        </div>
        </div>
    </div>
    </div>
  </div>
</div>
