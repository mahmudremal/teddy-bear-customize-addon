<?php
$subscribers = get_users( [ 'role__in' => [ 'lead' ] ] ); // 'author',  subscriber
$date_formate = get_option( 'date_format' );
// print_r( $subscribers );wp_die();
$userContracts = apply_filters( 'futurewordpress/project/quizandfiltersearch/action/contracts', [
  'no-action'       => __( 'No Contract fetched', 'quiz-and-filter-search-domain' )
], false );
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
                  <th scope="col"><?php esc_html_e( 'Email', 'quiz-and-filter-search-domain' ); ?></th>
                  <th scope="col"><?php esc_html_e( 'Profiles', 'quiz-and-filter-search-domain' ); ?></th>
                  <th scope="col"><?php esc_html_e( 'Client Status', 'quiz-and-filter-search-domain' ); ?></th>
                  <th scope="col"><?php esc_html_e( 'Meeting Time & link', 'quiz-and-filter-search-domain' ); ?></th>
                  <th scope="col"><?php esc_html_e( 'Retainer Amount', 'quiz-and-filter-search-domain' ); ?></th>
                  <!-- Contract Options -->
                  <th scope="col"><?php esc_html_e( 'Status', 'quiz-and-filter-search-domain' ); ?></th>
                  <th scope="col"><?php esc_html_e( 'Action', 'quiz-and-filter-search-domain' ); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php if( count( $subscribers ) <= 0 ) : ?>
                  <tr>
                    <td colspan="7"><img src="<?php echo esc_url( TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_URI . '/icons/not-found.svg' ); ?>" alt="<?php esc_attr_e( 'Noting found', 'quiz-and-filter-search-domain' ); ?>" style="width: 100%;height: auto;max-height: 400px;"></td>
                  </tr>
                <?php endif; ?>
                <?php foreach( $subscribers as $subscriber ) :
                  $userInfo = $subscriber;
                  $userMeta = array_map( function( $a ){ return $a[0]; }, (array) get_user_meta( $userInfo->ID ) );
                  $userInfo = (object) wp_parse_args( $userInfo, [
                    'id'      => '',
                    'meta'      => (object) wp_parse_args( $userMeta, apply_filters( 'futurewordpress/project/quizandfiltersearch/usermeta/defaults', (array) $userMeta ) )
                  ] );
                  $activeSubscription = in_array( $userInfo->meta->enable_subscription, [ 'on' ] );
                  $doc = apply_filters( 'futurewordpress/project/quizandfiltersearch/esign/userdocument', false, $userInfo );$doc = ( $doc && is_array( $doc ) ) ? (object) $doc: $doc;
                  if( $doc && ! empty( $doc->document_status ) ) {$is_signed = ( $doc->document_status == 'signed' );} else {$is_signed = null;}
                    //   Missing
                  $is_signed = ( $is_signed === null ) ? __( 'Pending', 'quiz-and-filter-search-domain' ) : (
                    ( $is_signed === true ) ? __( 'Signed', 'quiz-and-filter-search-domain' ) : __( 'Pending', 'quiz-and-filter-search-domain' )
                  );
                  $is_active = $activeSubscription ? __( 'Active', 'quiz-and-filter-search-domain' ) : __( 'Paused', 'quiz-and-filter-search-domain' );

                  $contractStatus = $activeSubscription ? $is_signed : $is_active;
                  ?>
                  <tr id="lead-<?php echo esc_html( $userInfo->ID ); ?>">
                    <td class="text-dark"><?php echo esc_html( empty( $userInfo->meta->email ) ? $userInfo->data->user_email : $userInfo->meta->email ); ?></td>
                    <td>
                      <div class="d-flex align-items-center">
                        <img class="rounded img-fluid avatar-60 me-3" src="<?php echo esc_url( get_avatar_url( $userInfo->ID, ['size' => '100'] ) ); ?>" alt="" loading="lazy">
                        <div class="media-support-info">
                        <h5 class="iq-sub-label"><?php echo esc_html( $userInfo->meta->first_name . ' ' . $userInfo->meta->last_name ); ?></h5>
                        <p class="mb-0">@<?php echo esc_html( $userInfo->data->user_nicename ); ?></p>
                        <?php $flag = apply_filters( 'futurewordpress/project/quizandfiltersearch/database/countryflags', get_user_meta( $userInfo->ID, 'country', true ) );if( $flag ) : ?>
                        <img width="18" class="me-2" src="<?php echo esc_url( $flag ); ?>"/>
                        <?php endif;echo esc_html( $userInfo->meta->country ); ?>
                        </div>
                      </div>
                    </td>
                    <td id="lead-status-<?php echo esc_html( $userInfo->ID ); ?>">
                      <?php $status = apply_filters( 'futurewordpress/project/quizandfiltersearch/action/statuses', [], $userInfo->meta->status ); ?>
                      <?php $status = is_string( $status ) ? $status : ''; ?>
                      <span class="badge bg-soft-success p-2 text-success"><?php echo esc_html( $status ); ?></span>
                      <?php // echo apply_filters( 'futurewordpress/project/quizandfiltersearch/widgets/statustab', '', $userInfo ); ?>
                      
                      <button type="button" class="btn btn-sm btn-icon btn-color-primary btn-active-light-primary leadstatusswitcher" data-value="<?php echo esc_attr( $userInfo->ID ); ?>">
                        <!--begin::Svg Icon | path: icons/duotune/general/gen024.svg-->
                        <span class="svg-icon svg-icon-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24">
                          <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                          <rect x="5" y="5" width="5" height="5" rx="1" fill="#000000"></rect>
                          <rect x="14" y="5" width="5" height="5" rx="1" fill="#000000" opacity="0.3"></rect>
                          <rect x="5" y="14" width="5" height="5" rx="1" fill="#000000" opacity="0.3"></rect>
                          <rect x="14" y="14" width="5" height="5" rx="1" fill="#000000" opacity="0.3"></rect>
                          </g>
                        </svg>
                        </span>
                        <!--end::Svg Icon-->
                      </button>
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="media-support-info">
                          <?php if( ! empty( $userInfo->meta->next_meeting ) ) : ?>
                            <!-- <?php // echo esc_html( date( 'M-d H:i', strtotime( $userInfo->meta->next_meeting ) ) ); ?> -->
                          <h5 class="iq-sub-label"><?php echo esc_html( $userInfo->meta->next_meeting ); ?></h5>
                          <?php endif; ?>
                          <?php if( ! empty( $userInfo->meta->meeting_link ) ) : ?>
                          <a class="mb-0" href="<?php echo esc_url( $userInfo->meta->meeting_link ); ?>" target="_blank">@<?php esc_html_e( 'Meeting link', 'quiz-and-filter-search-domain' ); ?></a>
                          <?php endif; ?>
                        </div>
                      </div>
                    </td>
                    <td class="text-dark"><?php echo esc_html( number_format_i18n( (int) $userInfo->meta->monthly_retainer, 2 ) ); ?></td>
                    <?php // isset( $userContracts[ $userInfo->meta->contract_type ] ) ? $userContracts[ $userInfo->meta->contract_type ] : $userInfo->meta->contract_type ?>
                    <td class="text-dark"><?php echo esc_html( $contractStatus ); ?></td>
                    <td>
                      <div class="d-flex justify-content-evenly">
                        <a class="btn btn-primary btn-icon btn-sm rounded-pill" href="<?php echo esc_url( apply_filters( 'futurewordpress/project/quizandfiltersearch/user/dashboardpermalink', $userInfo->ID, $userInfo->data->user_nicename ) ); ?>" role="button" target="_blank">
                          <span class="btn-inner">
                            <svg class="icon-32" width="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path opacity="0.4" d="M21.101 9.58786H19.8979V8.41162C19.8979 7.90945 19.4952 7.5 18.999 7.5C18.5038 7.5 18.1 7.90945 18.1 8.41162V9.58786H16.899C16.4027 9.58786 16 9.99731 16 10.4995C16 11.0016 16.4027 11.4111 16.899 11.4111H18.1V12.5884C18.1 13.0906 18.5038 13.5 18.999 13.5C19.4952 13.5 19.8979 13.0906 19.8979 12.5884V11.4111H21.101C21.5962 11.4111 22 11.0016 22 10.4995C22 9.99731 21.5962 9.58786 21.101 9.58786Z" fill="currentColor"></path>
                              <path d="M9.5 15.0156C5.45422 15.0156 2 15.6625 2 18.2467C2 20.83 5.4332 21.5001 9.5 21.5001C13.5448 21.5001 17 20.8533 17 18.269C17 15.6848 13.5668 15.0156 9.5 15.0156Z" fill="currentColor"></path>
                              <path opacity="0.4" d="M9.50023 12.5542C12.2548 12.5542 14.4629 10.3177 14.4629 7.52761C14.4629 4.73754 12.2548 2.5 9.50023 2.5C6.74566 2.5 4.5376 4.73754 4.5376 7.52761C4.5376 10.3177 6.74566 12.5542 9.50023 12.5542Z" fill="currentColor"></path>
                            </svg>
                          </span>
                        </a>
                        <a class="btn btn-primary btn-icon btn-sm rounded-pill ms-2" href="<?php echo esc_url( admin_url( 'admin.php?page=crm_dashboard&path=leads/edit/' . $userInfo->ID . '/' ) ); ?>" role="button">
                          <span class="btn-inner">
                            <svg class="icon-32" width="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path opacity="0.4" d="M19.9927 18.9534H14.2984C13.7429 18.9534 13.291 19.4124 13.291 19.9767C13.291 20.5422 13.7429 21.0001 14.2984 21.0001H19.9927C20.5483 21.0001 21.0001 20.5422 21.0001 19.9767C21.0001 19.4124 20.5483 18.9534 19.9927 18.9534Z" fill="currentColor"></path>
                              <path d="M10.309 6.90385L15.7049 11.2639C15.835 11.3682 15.8573 11.5596 15.7557 11.6929L9.35874 20.0282C8.95662 20.5431 8.36402 20.8344 7.72908 20.8452L4.23696 20.8882C4.05071 20.8903 3.88775 20.7613 3.84542 20.5764L3.05175 17.1258C2.91419 16.4915 3.05175 15.8358 3.45388 15.3306L9.88256 6.95545C9.98627 6.82108 10.1778 6.79743 10.309 6.90385Z" fill="currentColor"></path>
                              <path opacity="0.4" d="M18.1208 8.66544L17.0806 9.96401C16.9758 10.0962 16.7874 10.1177 16.6573 10.0124C15.3927 8.98901 12.1545 6.36285 11.2561 5.63509C11.1249 5.52759 11.1069 5.33625 11.2127 5.20295L12.2159 3.95706C13.126 2.78534 14.7133 2.67784 15.9938 3.69906L17.4647 4.87078C18.0679 5.34377 18.47 5.96726 18.6076 6.62299C18.7663 7.3443 18.597 8.0527 18.1208 8.66544Z" fill="currentColor"></path>
                            </svg>
                          </span>
                        </a>
                        <?php if( apply_filters( 'futurewordpress/project/quizandfiltersearch/system/isactive', 'general-leaddelete' ) ) : ?>
                        <a class="btn btn-primary btn-icon btn-sm delete-lead-user ms-2" href="#" data-id="<?php echo esc_attr( $userInfo->ID ); ?>" data-user-info="<?php echo esc_attr( json_encode( [ 'displayname' => $userInfo->display_name, 'role' => $userInfo->user_role ]) ); ?>" role="button">
                          <span class="btn-inner">
                            <svg class="icon-32" width="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path opacity="0.4" d="M19.643 9.48851C19.643 9.5565 19.11 16.2973 18.8056 19.1342C18.615 20.8751 17.4927 21.9311 15.8092 21.9611C14.5157 21.9901 13.2494 22.0001 12.0036 22.0001C10.6809 22.0001 9.38741 21.9901 8.13185 21.9611C6.50477 21.9221 5.38147 20.8451 5.20057 19.1342C4.88741 16.2873 4.36418 9.5565 4.35445 9.48851C4.34473 9.28351 4.41086 9.08852 4.54507 8.93053C4.67734 8.78453 4.86796 8.69653 5.06831 8.69653H18.9388C19.1382 8.69653 19.3191 8.78453 19.4621 8.93053C19.5953 9.08852 19.6624 9.28351 19.643 9.48851Z" fill="currentColor"></path>
                              <path d="M21 5.97686C21 5.56588 20.6761 5.24389 20.2871 5.24389H17.3714C16.7781 5.24389 16.2627 4.8219 16.1304 4.22692L15.967 3.49795C15.7385 2.61698 14.9498 2 14.0647 2H9.93624C9.0415 2 8.26054 2.61698 8.02323 3.54595L7.87054 4.22792C7.7373 4.8219 7.22185 5.24389 6.62957 5.24389H3.71385C3.32386 5.24389 3 5.56588 3 5.97686V6.35685C3 6.75783 3.32386 7.08982 3.71385 7.08982H20.2871C20.6761 7.08982 21 6.75783 21 6.35685V5.97686Z" fill="currentColor"></path>
                            </svg>
                          </span>
                        </a>
                        <?php endif; ?>
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
