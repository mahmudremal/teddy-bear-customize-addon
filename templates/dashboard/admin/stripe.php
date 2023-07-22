<?php
$subscribers = get_users( [ 'role__in' => [ 'lead' ] ] ); // 'author',  subscriber
$date_formate = get_option( 'date_format' );global $wpdb;
// print_r( $subscribers );wp_die();

$logs = $wpdb->get_results( $wpdb->prepare(
  "SELECT * FROM {$wpdb->prefix}fwp_stripe_payments WHERE status=%s OR status=%s ORDER BY id DESC LIMIT 0, 500;",
  'paid', 'success'
) );

?>
<div>
  <div class="row">
    <div class="col-12">
      <div class="card card-full-width">

        <div class="card-body">
          <div class="fancy-table table-responsive border rounded">
            <table class="table table-striped mb-0">
              <thead>
                <tr>
                  <th scope="col"><?php esc_html_e( 'Date', 'quiz-and-filter-search-domain' ); ?></th>
                  <th scope="col"><?php esc_html_e( 'Email', 'quiz-and-filter-search-domain' ); ?></th>
                  <th scope="col"><?php esc_html_e( 'Profiles', 'quiz-and-filter-search-domain' ); ?></th>
                  <th scope="col"><?php esc_html_e( 'Retainer Amount', 'quiz-and-filter-search-domain' ); ?></th>
                  <th scope="col"><?php esc_html_e( 'Action', 'quiz-and-filter-search-domain' ); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php if( count( $logs ) <= 0 ) : ?>
                  <tr>
                    <td colspan="7"><img src="<?php echo esc_url( TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_URI . '/icons/not-found.svg' ); ?>" alt="<?php esc_attr_e( 'Noting found', 'quiz-and-filter-search-domain' ); ?>" style="width: 100%;height: auto;max-height: 400px;"></td>
                  </tr>
                <?php endif; ?>
                <?php foreach( $logs as $log ) :
                  $userInfo = get_user_by( 'id', $log->user_id );
                  $userMeta = array_map( function( $a ){ return $a[0]; }, (array) get_user_meta( $userInfo->ID ) );
                  $userInfo = (object) wp_parse_args( $userInfo, [ 'meta' => (object) wp_parse_args( $userMeta, apply_filters( 'futurewordpress/project/quizandfiltersearch/usermeta/defaults', (array) $userMeta ) ) ] );
                  // print_r( $userInfo );
                  ?>
                  <tr id="stripelog-<?php echo esc_html( $userInfo->ID ); ?>">
                    <td class="text-dark"><?php echo esc_html( wp_date( 'd M Y H:i', strtotime( $log->created_at ) ) ); ?></td>
                    <td class="text-dark"><?php echo esc_html( empty( $log->customer_email ) ? $userInfo->data->user_email : $log->customer_email ); ?></td>
                    <td>
                      <div class="d-flex align-items-center">
                        <img class="rounded img-fluid avatar-60 me-3" src="<?php echo esc_url( get_avatar_url( $user->ID, ['size' => '100'] ) ); ?>" alt="" loading="lazy">
                        <div class="media-support-info">
                        <h5 class="iq-sub-label"><?php echo esc_html( $userInfo->meta->first_name . ' ' . $userInfo->meta->last_name ); ?></h5>
                        <p class="mb-0">@<?php echo esc_html( $userInfo->data->user_nicename ); ?></p>
                        <?php $flag = apply_filters( 'futurewordpress/project/quizandfiltersearch/database/countryflags', get_user_meta( $userInfo->ID, 'country', true ) );if( $flag ) : ?>
                        <img width="18" class="me-2" src="<?php echo esc_url( $flag ); ?>"/>
                        <?php endif;echo esc_html( $userInfo->meta->country ); ?>
                        </div>
                      </div>
                    </td>
                    <td class="text-dark"><?php echo esc_html( strtoupper( $log->currency ) . ' ' . ( $log->amount / 100 ) ); ?></td>
                    <td>
                      <div class="d-flex justify-content-evenly">
                        <a class="btn btn-primary btn-icon btn-sm rounded-pill" href="<?php echo esc_url( admin_url( 'admin.php?page=crm_dashboard&path=payments/stripe_logs/' . $log->id . '/' ) ); ?>" role="button">
                          <span class="btn-inner">
                            <svg class="icon-32" width="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path opacity="0.4" fill-rule="evenodd" clip-rule="evenodd" d="M17.7366 6.04606C19.4439 7.36388 20.8976 9.29455 21.9415 11.7091C22.0195 11.8924 22.0195 12.1067 21.9415 12.2812C19.8537 17.1103 16.1366 20 12 20H11.9902C7.86341 20 4.14634 17.1103 2.05854 12.2812C1.98049 12.1067 1.98049 11.8924 2.05854 11.7091C4.14634 6.87903 7.86341 4 11.9902 4H12C14.0683 4 16.0293 4.71758 17.7366 6.04606ZM8.09756 12C8.09756 14.1333 9.8439 15.8691 12 15.8691C14.1463 15.8691 15.8927 14.1333 15.8927 12C15.8927 9.85697 14.1463 8.12121 12 8.12121C9.8439 8.12121 8.09756 9.85697 8.09756 12Z" fill="currentColor"></path>
                              <path d="M14.4308 11.997C14.4308 13.3255 13.3381 14.4115 12.0015 14.4115C10.6552 14.4115 9.5625 13.3255 9.5625 11.997C9.5625 11.8321 9.58201 11.678 9.61128 11.5228H9.66006C10.743 11.5228 11.621 10.6695 11.6601 9.60184C11.7674 9.58342 11.8845 9.57275 12.0015 9.57275C13.3381 9.57275 14.4308 10.6588 14.4308 11.997Z" fill="currentColor"></path>
                            </svg>
                          </span>
                        </a>
                        <a class="btn btn-primary btn-icon btn-sm delete-stripe-log ms-2" href="#" data-id="<?php echo esc_attr( $log->id ); ?>" role="button">
                          <span class="btn-inner">
                            <svg class="icon-32" width="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path opacity="0.4" d="M19.643 9.48851C19.643 9.5565 19.11 16.2973 18.8056 19.1342C18.615 20.8751 17.4927 21.9311 15.8092 21.9611C14.5157 21.9901 13.2494 22.0001 12.0036 22.0001C10.6809 22.0001 9.38741 21.9901 8.13185 21.9611C6.50477 21.9221 5.38147 20.8451 5.20057 19.1342C4.88741 16.2873 4.36418 9.5565 4.35445 9.48851C4.34473 9.28351 4.41086 9.08852 4.54507 8.93053C4.67734 8.78453 4.86796 8.69653 5.06831 8.69653H18.9388C19.1382 8.69653 19.3191 8.78453 19.4621 8.93053C19.5953 9.08852 19.6624 9.28351 19.643 9.48851Z" fill="currentColor"></path>
                              <path d="M21 5.97686C21 5.56588 20.6761 5.24389 20.2871 5.24389H17.3714C16.7781 5.24389 16.2627 4.8219 16.1304 4.22692L15.967 3.49795C15.7385 2.61698 14.9498 2 14.0647 2H9.93624C9.0415 2 8.26054 2.61698 8.02323 3.54595L7.87054 4.22792C7.7373 4.8219 7.22185 5.24389 6.62957 5.24389H3.71385C3.32386 5.24389 3 5.56588 3 5.97686V6.35685C3 6.75783 3.32386 7.08982 3.71385 7.08982H20.2871C20.6761 7.08982 21 6.75783 21 6.35685V5.97686Z" fill="currentColor"></path>
                            </svg>
                          </span>
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>
  
</div>
