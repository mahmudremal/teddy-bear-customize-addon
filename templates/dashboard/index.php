<?php
/**
 * Checkout video clip shortner template.
 * 
 * @package TeddyBearCustomizeAddon
 */
is_user_logged_in() || auth_redirect();

global $currenttab;global $user_profile;global $userInfo;global $errorHappens;
$user_profile = get_query_var( 'user_profile' );
if( empty( $user_profile ) || $user_profile == 0 ) {
  wp_die( __( 'You\'ve visited on an outdated link, or your link is no longer valid. We didn\'t idetify the user with this permalink. That\'s all we know', 'quiz-and-filter-search-domain' ), __( 'Link broken', 'quiz-and-filter-search-domain' ) );
}
if( $user_profile == 'me' ) {
  $userInfo = wp_get_current_user();
  // print_r( $userInfo );
  wp_redirect( apply_filters( 'futurewordpress/project/quizandfiltersearch/user/dashboardpermalink', $userInfo->ID, $userInfo->data->user_nicename ) );
  exit;
}
$currenttab = get_query_var( 'currenttab' );
$allowedTabs = [ 'archive', 'payments', 'settings', 'contact' ]; // 'profile', 
$errorHappens = false;
// if( get_current_user_id() == $user_profile ) {}
// print_r( get_userdata( $user_profile ) );

// 'id | ID | slug | email | login ', $user_profile
$userInfo = get_user_by( apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'permalink-userby', 'id' ), $user_profile );

if( $currenttab && in_array( $currenttab, $allowedTabs ) ) {
  add_filter( 'futurewordpress/project/quizandfiltersearch/javascript/siteconfig', function( $args ) {
    global $currenttab;global $user_profile;global $userInfo;
    $args[ 'profile' ] = [
      'profilePath'         => apply_filters( 'futurewordpress/project/quizandfiltersearch/user/dashboardpermalink', $userInfo->ID, $userInfo->data->user_nicename ),
      'currentTab'          => $currenttab
    ];return $args;
  }, 10, 1 );
}
if( is_wp_error( $userInfo ) || $errorHappens ) :
  http_response_code( 404 );
  status_header( 404, 'Page not found' );
  add_filter( 'pre_get_document_title', function( $title ) {global $errorHappens;return $errorHappens;}, 99, 1 );
  wp_die( $errorHappens, __( 'Error Happens', 'quiz-and-filter-search-domain' ) );
else :
  $userMeta = array_map( function( $a ){ return $a[0]; }, (array) get_user_meta( $userInfo->ID ) );
  $userInfo = (object) wp_parse_args( $userInfo, [
  'id'      => '',
  'meta'      => (object) apply_filters( 'futurewordpress/project/quizandfiltersearch/usermeta/defaults', (array) $userMeta )
  ] );
  add_filter( 'pre_get_document_title', function( $title ) {
    global $userInfo;
    $title = apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'dashboard-title', __( 'User Dashbord', 'quiz-and-filter-search-domain' ) );
    $title = str_replace( [
      '{username}', '{sitename}'
    ], [
      $userInfo->meta->first_name . ' ' . $userInfo->meta->last_name,
      get_option( 'blogname', 'We Make Content' )
    ], $title );
    return $title;
  }, 99, 1 );
  if( $userInfo->meta->show_admin_bar_front === true ) {
    update_user_meta( $userInfo->ID, 'show_admin_bar_front', 'false' );
  }
  // echo '<pre style="display: none;">';print_r( $userInfo );echo '</pre>';
  
  if( empty( apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'dashboard-headerbg', '' ) ) ) {
    add_filter( 'body_class', function( $classes ) {
      $classes[] = 'no-header-bg';return $classes;
    }, 10, 1 );
  }
  get_header();
  ?>
<main class="main-content">
  <div class="position-relative iq-banner ">
  
    <div class="iq-navbar-header">
      <div class="container-fluid iq-container">
        <div class="row">
          <div class="col-md-12">
            <div class="flex-wrap d-flex justify-content-between align-items-center">
            </div>
          </div>
        </div>
      </div>
      <div class="iq-header-img" style="z-index: unset;">
        <?php if( ! empty( apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'dashboard-headerbg', '' ) ) ) : ?>
        <img src="<?php echo esc_url( apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'dashboard-headerbg', '' ) ); ?>" alt="header" class="theme-color-default-img img-fluid w-100 h-100 animated-scaleX" loading="lazy">
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="content-inner pb-0 container" id="page_layout">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
              <?php if( false ) : ?>
                <!-- Profile Avater and Image hidden and instead of it, tab buttons made bit more bigger -->
                <div class="d-flex flex-wrap align-items-center">
                  <div class="profile-img position-relative me-3 mb-3 mb-lg-0 profile-logo profile-logo1">
                    <img src="<?php echo esc_url( get_avatar_url( $userInfo->ID, ['size' => '100'] ) ); ?>" alt="userInfo-Profile" class="theme-color-default-img img-fluid rounded-pill avatar-100" loading="lazy">
                  </div>
                  <div class="d-flex flex-wrap align-items-center mb-3 mb-sm-0">
                  <h4 class="me-2 h4"><?php echo esc_html( $userInfo->meta->first_name . ' ' . $userInfo->meta->last_name ); ?></h4>
                  <span><?php echo esc_html( ! empty( $userInfo->meta->city ) ? ' - ' . $userInfo->meta->city : '' ); ?></span>
                  </div>
                </div>
              <?php endif; ?>
              <ul class="d-flex nav nav-pills mb-0 text-center profile-tab nav-slider" data-toggle="slider-tab" id="profile-pills-tab" role="tablist">
                <?php if( in_array( 'profile', $allowedTabs ) ) : ?>
                <li class="nav-item" role="presentation">
                  <a class="nav-link" data-bs-toggle="tab" href="#profile-profile" role="tab" aria-selected="true" tabindex="-1"><?php esc_html_e( 'Profile',   'quiz-and-filter-search-domain' ); ?></a>
                </li>
                <?php endif; ?>
                <?php if( in_array( 'archive', $allowedTabs ) ) : ?>
                <li class="nav-item" role="presentation">
                  <a class="nav-link active show" data-bs-toggle="tab" href="#profile-archive" role="tab" aria-selected="false"><?php esc_html_e( 'Submit Videos',   'quiz-and-filter-search-domain' ); ?></a>
                </li>
                <?php endif; ?>
                <?php if( in_array( 'payments', $allowedTabs ) ) : ?>
                <li class="nav-item" role="presentation">
                  <a class="nav-link" data-bs-toggle="tab" href="#profile-payments" role="tab" aria-selected="false" tabindex="-1"><?php esc_html_e( 'Payments',   'quiz-and-filter-search-domain' ); ?></a>
                </li>
                <?php endif; ?>
                <?php if( in_array( 'settings', $allowedTabs ) ) : ?>
                <li class="nav-item" role="presentation">
                  <a class="nav-link" data-bs-toggle="tab" href="#profile-settings" role="tab" aria-selected="false" tabindex="-1"><?php esc_html_e( 'Your Social Media Handles',   'quiz-and-filter-search-domain' ); ?></a>
                </li>
                <?php endif; ?>
                <?php if( in_array( 'contact', $allowedTabs ) && apply_filters( 'futurewordpress/project/quizandfiltersearch/system/isactive', 'social-contact' ) ) : ?>
                <li class="nav-item" role="presentation">
                  <a class="nav-link" data-bs-toggle="tab" href="#profile-contact" role="tab" aria-selected="false" tabindex="-1"><?php esc_html_e( 'Contact us now!',   'quiz-and-filter-search-domain' ); ?></a>
                </li>
                <?php endif; ?>
              </ul>
              <a href="<?php echo wp_logout_url(); ?>" class="btn btn-soft-danger btn-logout-confirm nav-link nav nav-pills mb-0 text-center profile-tab nav-slider"><?php esc_html_e( 'Logout', 'quiz-and-filter-search-domain' ); ?></a>
            </div>
          </div>
        </div>
      </div>
      <?php include TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/dashboard/cards/notices.php'; ?>
      <div class="col-lg-3">
        <div class="card h-12">
          <div class="card-header">
          <div class="header-title">
            <h4 class="card-title"><?php esc_html_e( 'Overview',   'quiz-and-filter-search-domain' ); ?></h4>
          </div>
          </div>
          <div class="card-body">
            
            <div class="prof ile-img profile-img-edit position-relative profile-logo1 mb-4 text-center m-auto" style="width: max-content;">
              <img src="<?php echo esc_url( get_avatar_url( $userInfo->ID, ['size' => '100'] ) ); ?>" alt="userInfo-Profile" class="theme-color-default-img img-fluid rounded-pill avatar-100" loading="lazy" data-default="<?php echo esc_url( get_avatar_url( $userInfo->ID, ['size' => '100'] ) ); ?>" id="profile-image-preview">
              <div class="upload-icone bg-primary">
                <svg class="upload-button icon-14" width="14" height="14" viewBox="0 0 24 24"><path fill="#333" d="M14.06,9L15,9.94L5.92,19H5V18.08L14.06,9M17.66,3C17.41,3 17.15,3.1 16.96,3.29L15.13,5.12L18.88,8.87L20.71,7.04C21.1,6.65 21.1,6 20.71,5.63L18.37,3.29C18.17,3.09 17.92,3 17.66,3M14.06,6.19L3,17.25V21H6.75L17.81,9.94L14.06,6.19Z"></path></svg>
                <input type="file" class="form-control file-upload profile-image-upload" name="profile-image" accept="image/*" data-preview="#profile-image-preview" data-lead="<?php echo esc_attr( $userInfo->ID ); ?>">
              </div>
            </div>
            <div class="align-items-center text-center mb-3">
              <h4 class="me-2 h4"><?php echo esc_html( $userInfo->meta->first_name . ' ' . $userInfo->meta->last_name ); ?></h4>
            </div>
            <div class="separator separator-dashed my-8"></div>
            <?php if( ! empty( $userInfo->meta->monthly_retainer ) ): ?>
            <div class="text-center m-auto">
              <h4><?php esc_html_e( 'Monthly Retainer', 'quiz-and-filter-search-domain' ); ?></h4>
              <h2 class="counter mb-2" style="<?php echo esc_attr( ( apply_filters( 'futurewordpress/project/quizandfiltersearch/system/isactive', 'stripe-cancelsubscription' ) && (bool) $userInfo->meta->subscribe === false ) ? 'text-decoration: line-through;' : '' ); ?>">$<?php echo esc_attr( number_format_i18n( $userInfo->meta->monthly_retainer, 2 ) ); ?></h2>
            </div>
            <p class="mt-4 text-center"><strong style="margin-right: 5px;"><?php esc_html_e( 'Joined:',   'quiz-and-filter-search-domain' ); ?></strong> <?php echo esc_html( wp_date( 'M d, Y', strtotime( $userInfo->data->user_registered ) ) ); ?></p>
            
            <?php $subsc = (array) apply_filters( 'futurewordpress/project/quizandfiltersearch/payment/stripe/getsubscriptionby', [], ['by' => 'email', 'email' => ( ! empty( $userInfo->data->user_email ) ? $userInfo->data->user_email : $userInfo->meta->email ) ] );
            if( isset( $subsc[ 'current_period_end' ] ) && ! empty( $subsc[ 'current_period_end' ] ) ) : ?>
            <p class="mt-4 text-center"><strong style="margin-right: 5px;"><?php esc_html_e( 'Next Retainer:',   'quiz-and-filter-search-domain' ); ?></strong> <?php echo esc_html( wp_date( 'M d, Y', (int) $subsc[ 'current_period_end' ] ) ); ?></p>
            <?php endif; ?>
            <div class="separator separator-dashed my-8"></div>
            <?php endif; ?>

            <ul class="list-inline m-0 p-0">
              <li class="d-flex mb-4 align-items-center active">
              <!-- Content creation_Flatline.svg -->
              <img src="<?php echo esc_url( TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_URI . '/icons/calendar-checklist-date-svgrepo-com.svg' ); ?>" alt="story-img" class="rounded-pill avatar-70 p-1 border bg-soft-light img-fluid" loading="lazy">
                <div class="ms-3">
                  <a class="" href="<?php echo empty( $userInfo->meta->content_calendar ) ? esc_attr( 'javascript:void(0)' ) : esc_url( $userInfo->meta->content_calendar ); ?>" target="<?php echo esc_attr( empty( $userInfo->meta->content_calendar ) ? '_self' : '_blank' ); ?>">
                    <h5><?php esc_html_e( 'Content Calendar',   'quiz-and-filter-search-domain' ); ?></h5>
                    <!-- <p class="mb-0">Added 1 hour ago</p> -->
                  </a>
                </div>
              </li>
              <li class="d-flex mb-4 align-items-center">
                <!-- https://templates.iqonic.design/product/qompac-ui/html/dist/assets/images/shapes/04.png | Information carousel_Monochromatic.svg -->
                <img src="<?php echo esc_url( TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_URI . '/icons/bookshelf-svgrepo-com.svg' ); ?>" alt="story-img" class="rounded-pill avatar-70 p-1 border img-fluid bg-soft-danger" loading="lazy">
                <div class="ms-3">
                  <a class="" href="<?php echo empty( $userInfo->meta->content_library ) ? esc_attr( 'javascript:void(0)' ) : esc_url( $userInfo->meta->content_library ); ?>" target="<?php echo esc_attr( empty( $userInfo->meta->content_library ) ? '_self' : '_blank' ); ?>">
                    <h5><?php esc_html_e( 'Content Library',   'quiz-and-filter-search-domain' ); ?></h5>
                    <!-- <p class="mb-0">Added 1 hour ago</p> -->
                  </a>
                </div>
              </li>
              <?php $doc = apply_filters( 'futurewordpress/project/quizandfiltersearch/esign/userdocument', false, $userInfo );$doc = ( $doc && is_array( $doc ) ) ? (object) $doc: $doc;if( $doc && $doc->permalink ) : ?>
              <li class="d-flex mb-4 align-items-center position-relative">
                <img src="<?php echo esc_url( TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_URI . '/icons/contract-document-svgrepo-com.svg' ); ?>" data-img="<?php echo esc_url( TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_URI . '/icons/Information carousel_Monochromatic.svg' ); ?>" alt="story-img" class="rounded-pill avatar-70 p-1 border img-fluid bg-soft-danger" loading="lazy">
                <?php if( $doc->document_status != 'signed' ) : ?>
                  <span class="badge border border-danger bg-danger" style="position: absolute;top: -10px;left: 30px;color: black;"><?php esc_html_e( 'Action Needed',   'quiz-and-filter-search-domain' ); ?></span>
                <?php endif; ?>
                <div class="ms-3">
                  <a class="" href="<?php echo esc_url( $doc->permalink ); ?>" target="_blank">
                    <h5><?php echo esc_html( ( $doc->document_status == 'signed' ) ? __( 'Document Signed',   'quiz-and-filter-search-domain' ) : __( 'Sign Document',   'quiz-and-filter-search-domain' ) ); ?></h5>
                    <!-- <p class="mb-0">Added 1 hour ago</p> -->
                  </a>
                </div>
              </li>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </div>
      <div class="col-lg-9">
        <div class="card h-12 profile-content tab-content iq-tab-fade-up">

          <?php if( in_array( 'profile', $allowedTabs ) ) : ?>
          <div id="profile-profile" class="tab-pane fade" role="tabpanel">
            <div class="card">
              <div class="card-header">
                <div class="header-title">
                  <h4 class="card-title">Profile</h4>
                </div>
              </div>
              <div class="card-body">
                <div class="text-center">
                  <div>
                  <!-- https://templates.iqonic.design/product/qompac-ui/html/dist/assets/images/avatars/01.png -->
                  <img src="<?php echo esc_url( get_avatar_url( $userInfo->ID, ['size' => '100'] ) ); ?>" alt="profile-img" class="rounded-pill avatar-130 img-fluid" loading="lazy">
                  </div>
                  <div class="mt-3">
                  <h3 class="d-inline-block"><?php echo esc_html( $userInfo->meta->first_name . ' ' . $userInfo->meta->last_name ); ?></h3>
                  <p class="d-inline-block pl-3"><?php echo esc_html( ! empty( $userInfo->meta->city ) ? ' - ' . $userInfo->meta->city : '' ); ?></p>
                  <p class="mb-0"><?php echo esc_html( $userInfo->meta->description ); ?></p>
                  </div>
                </div>
              </div>
            </div>
            <div class="card">
              <div class="card-header">
                <div class="header-title">
                  <h4 class="card-title"><?php echo esc_html( sprintf( __( 'About %s',   'quiz-and-filter-search-domain' ), $userInfo->data->display_name ) ); ?></h4>
                </div>
              </div>
              <div class="card-body">
                <div class="userInfo-bio">
                  <!-- <p>Tart I love sugar plum I love oat cake. Sweet roll caramels I love jujubes. Topping cake wafer.</p> -->
                </div>
                <div class="mt-2">
                  <h6 class="mb-1"><?php esc_html_e( 'Joined:',   'quiz-and-filter-search-domain' ); ?></h6>
                  <p> <?php echo esc_html( wp_date( 'M d, Y', strtotime( $userInfo->data->user_registered ) ) ); ?></p>
                </div>
                <div class="mt-2">
                <h6 class="mb-1"><?php esc_html_e( 'Lives:',   'quiz-and-filter-search-domain' ); ?></h6>
                <?php $country = apply_filters( 'futurewordpress/project/quizandfiltersearch/database/countries', [], $userInfo->meta->country ); ?>
                <p> <?php echo esc_html( $userInfo->meta->city . ' - ' . $country ); ?></p>
                </div>
                <div class="mt-2">
                <h6 class="mb-1"><?php esc_html_e( 'Email:',   'quiz-and-filter-search-domain' ); ?></h6>
                <p><a href="mailto:<?php echo esc_attr( ! empty( $userInfo->data->user_email ) ? $userInfo->data->user_email : $userInfo->meta->email ); ?>" class="text-body"> <?php echo esc_html( ! empty( $userInfo->data->user_email ) ? $userInfo->data->user_email : $userInfo->meta->email ); ?></a></p>
                </div>
                <div class="mt-2">
                <h6 class="mb-1"><?php esc_html_e( 'Url:',   'quiz-and-filter-search-domain' ); ?></h6>
                <p><a href="<?php echo esc_url( $userInfo->meta->website ); ?>" class="text-body" target="_blank"> <?php echo esc_html( $userInfo->meta->website ); ?> </a></p>
                </div>
                <div class="mt-2">
                <h6 class="mb-1"><?php esc_html_e( 'Contact:',   'quiz-and-filter-search-domain' ); ?></h6>
                <p><a href="tel:<?php echo esc_attr( $userInfo->meta->phone ); ?>" class="text-body"><?php echo esc_html( $userInfo->meta->phone ); ?></a></p>
                </div>
              </div>
            </div>
          </div>
          <?php endif; ?>
          <?php if( in_array( 'archive', $allowedTabs ) ) : ?>
          <div id="profile-archive" class="tab-pane fade active show" role="tabpanel">
            <div class="card">
              <div class="card-header d-flex justify-content-between">
                <div class="header-title">
                  <h4 class="card-title"><?php esc_html_e( 'Client Raw Video Archive',   'quiz-and-filter-search-domain' ); ?></h4>
                </div>
              </div>
              <div class="card-body">
                <div class="border rounded mb-3 p-2">
                  <form id="the-raw-video-archive-upload" class="form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="futurewordpress/project/quizandfiltersearch/action/submitarchives">
                    <input type="hidden" name="userid" value="<?php echo esc_attr( $userInfo->ID ); ?>">
                    <?php wp_nonce_field( 'futurewordpress/project/quizandfiltersearch/action/submitarchives', '_nonce', true, true ); ?>
                    <div class="row">
                      <div class="col-md-6">
                        <select class="form-select form-select-lg form-select-solid mb-2" name="month" data-control="select2" data-placeholder="<?php esc_attr_e( 'Upload for...', 'quiz-and-filter-search-domain' ); ?>">
                        <?php $months = [ 'Jan', 'Fab', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dev' ]; ?>
                        <?php foreach( $months as $i => $month ) :
                          $dateObj   = DateTime::createFromFormat( '!m', ( $i + 1 ) );
                          $monthName = $dateObj->format('F'); ?>
                          <option value="<?php echo esc_attr( $month ); ?>" <?php echo esc_attr( ( ( $i + 1 ) == date( 'm' ) ) ? 'selected' : '' ); ?>><?php echo esc_html( $monthName ); ?></option>
                        <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="col-md-6">
                        <select class="form-select form-select-lg form-select-solid mb-2" name="year" data-control="select2" data-placeholder="<?php esc_attr_e( 'Upload for...', 'quiz-and-filter-search-domain' ); ?>">
                        <?php for( $i = apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'dashboard-yearstart', date( 'Y' ) ); $i <= apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'dashboard-yearend', ( date( 'Y' ) + 3 ) ); $i++ ) : ?>
                          <option value="<?php echo esc_attr( $i ); ?>" <?php echo esc_attr( ( $i == date( 'Y' ) ) ? 'selected' : '' ); ?>><?php echo esc_html( $i ); ?></option>
                        <?php endfor; ?>
                        </select>
                      </div>
                      <!-- <input class="form-control form-control-solid fwp-flatpickr-field" placeholder="<?php echo esc_attr( wp_date( 'F-Y' ) ); ?>" data-config="<?php echo esc_attr( json_encode( ['enableTime' => false,'dateFormat' => 'F-Y'] ) ); ?>"/> -->
                      <div class="col-md-12">
                        <div class="fv-row my-2">
                          <!--begin::Dropzone-->
                          <div class="dropzone fwp-dropzone-field">
                          <!--begin::Message-->
                          <div class="dz-message needsclick">
                            <!--begin::Icon-->
                            <!-- <i class="bi bi-file-earmark-arrow-up text-primary fs-3x"></i> -->
                            <svg fill="#fff" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M8.46583 5.23624C8.24276 5.53752 8.26838 5.96727 8.5502 6.24C8.8502 6.54 9.3402 6.54 9.6402 6.24L11.2302 4.64V8.78H12.7702V4.64L14.3602 6.24L14.4464 6.31438C14.7477 6.53752 15.1775 6.51273 15.4502 6.24C15.6002 6.09 15.6802 5.89 15.6802 5.69C15.6802 5.5 15.6002 5.3 15.4502 5.15L12.5402 2.23L12.4495 2.14848C12.3202 2.0512 12.1602 2 12.0002 2C11.7902 2 11.6002 2.08 11.4502 2.23L8.5402 5.15L8.46583 5.23624ZM6.23116 8.78512C3.87791 8.89627 2 10.8758 2 13.2875V18.2526L2.00484 18.4651C2.1141 20.8599 4.06029 22.7802 6.45 22.7802H17.56L17.7688 22.7753C20.1221 22.6641 22 20.6843 22 18.2628V13.3078L21.9951 13.0945C21.8853 10.6909 19.93 8.7802 17.55 8.7802H12.77V14.8849L12.7629 14.9922C12.7112 15.3776 12.385 15.6683 12 15.6683C11.57 15.6683 11.23 15.3224 11.23 14.8849V8.7802H6.44L6.23116 8.78512Z" fill="currentColor" />
                            </svg>
                            <!--end::Icon-->
                            <!--begin::Info-->
                            <div class="ms-4">
                            <h3 class="fs-5 fw-bolder text-gray-900 mb-1">Drop files here or click to upload.</h3>
                            <span class="fs-7 fw-bold text-primary opacity-75">Upload up to 10 files</span>
                            </div>
                            <!--end::Info-->
                          </div>
                          </div>
                          <!--end::Dropzone-->
                        </div>
                      </div>
                      <div class="com-md-12">
                        <button type="button" class="btn btn-light-primary fw-bold col-12 mt-3 submit-archive-files" data-config="<?php echo esc_attr( json_encode( [
                          'title' => __( 'A short title', 'quiz-and-filter-search-domain' ),
                          'text' => __( 'Choose a title for your monthly archive. To update your archive, simply re-upload your files and resubmit - the new files will replace the previous ones.', 'quiz-and-filter-search-domain' ),
                          'icon' => 'info',
                          'confirmButtonText' => __( 'Submit', 'quiz-and-filter-search-domain' ),
                          'input' => 'text',
                          'inputAttributes' => [
                            'autocapitalize' => 'off'
                          ],
                          'showCancelButton' => true,
                          'showLoaderOnConfirm' => true
                        ] ) ); ?>" role="button"><?php esc_html_e( 'Submit & Upload', 'quiz-and-filter-search-domain' ); ?></button>
                      </div>
                    </div>
                  </form>
                </div>
                <?php $archives = apply_filters( 'futurewordpress/project/quizandfiltersearch/filesystem/ziparchives', [], $userInfo->ID ); ?>

                <div class="<?php echo esc_attr( ( count( $archives ) > 0 ) ? 'custom-table-effect' : '' ); ?> table-responsive  border rounded">
                  <table class="table mb-0 fwp-datatable-field" data-toggle="data-table">
                    <thead>
                      <tr class="bg-white">
                        <th scope="col"><?php esc_html_e( 'Uploaded time',   'quiz-and-filter-search-domain' ); ?></th>
                        <th scope="col"><?php esc_html_e( 'Attached title',   'quiz-and-filter-search-domain' ); ?></th>
                        <th scope="col"><?php esc_html_e( 'Download',   'quiz-and-filter-search-domain' ); ?></th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php if( count( $archives ) <= 0 ) : ?>
                        <tr>
                          <td colspan="3"><img src="<?php echo esc_url( TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_URI . '/icons/undraw_file_bundle_re_6q1e.svg' ); ?>" alt="<?php esc_attr_e( 'No Archives',   'quiz-and-filter-search-domain' ); ?>"></td>
                        </tr>
                      <?php else : ?>
                        <?php foreach( $archives as $i => $archive ) :?>
                        <tr>
                          <td class=""><?php echo esc_html( $archive->formonth ); ?></td>
                          <td class=""><?php echo esc_html( $archive->title ); ?></td>
                          <td>
                            <div class="d-flex justify-content-evenly">
                              <a class="btn btn-primary btn-icon btn-sm direct-download-btn" href="<?php echo esc_url( $archive->file_path ); ?>" role="button" target="_blank">
                                <span class="btn-inner">
                                  <svg fill="#fff" xmlns="http://www.w3.org/2000/svg" class="icon-32" width="32" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12.1535 16.64L14.995 13.77C15.2822 13.47 15.2822 13 14.9851 12.71C14.698 12.42 14.2327 12.42 13.9455 12.71L12.3713 14.31V9.49C12.3713 9.07 12.0446 8.74 11.6386 8.74C11.2327 8.74 10.896 9.07 10.896 9.49V14.31L9.32178 12.71C9.03465 12.42 8.56931 12.42 8.28218 12.71C7.99505 13 7.99505 13.47 8.28218 13.77L11.1139 16.64C11.1832 16.71 11.2624 16.76 11.3515 16.8C11.4406 16.84 11.5396 16.86 11.6386 16.86C11.7376 16.86 11.8267 16.84 11.9158 16.8C12.005 16.76 12.0842 16.71 12.1535 16.64ZM19.3282 9.02561C19.5609 9.02292 19.8143 9.02 20.0446 9.02C20.302 9.02 20.5 9.22 20.5 9.47V17.51C20.5 19.99 18.5 22 16.0446 22H8.17327C5.58911 22 3.5 19.89 3.5 17.29V6.51C3.5 4.03 5.4901 2 7.96535 2H13.2525C13.5 2 13.7079 2.21 13.7079 2.46V5.68C13.7079 7.51 15.1931 9.01 17.0149 9.02C17.4333 9.02 17.8077 9.02318 18.1346 9.02595C18.3878 9.02809 18.6125 9.03 18.8069 9.03C18.9479 9.03 19.1306 9.02789 19.3282 9.02561ZM19.6045 7.5661C18.7916 7.5691 17.8322 7.5661 17.1421 7.5591C16.047 7.5591 15.145 6.6481 15.145 5.5421V2.9061C15.145 2.4751 15.6629 2.2611 15.9579 2.5721C16.7203 3.37199 17.8873 4.5978 18.8738 5.63395C19.2735 6.05379 19.6436 6.44249 19.945 6.7591C20.2342 7.0621 20.0223 7.5651 19.6045 7.5661Z" fill="currentColor" /></svg>
                                </span>
                              </a>
                              <?php if( apply_filters( 'futurewordpress/project/quizandfiltersearch/system/isactive', 'general-archivedelete' ) ) : ?>
                              <a class="btn btn-danger btn-icon btn-sm archive-delete-btn" href="#" data-archive="<?php echo esc_attr( $archive->id ); ?>" data-userid="<?php echo esc_attr( $userInfo->ID ); ?>" role="button">
                                <span class="btn-inner">
                                  <svg class="icon-32" width="32" viewBox="0 0 24 24" fill="#fff" xmlns="http://www.w3.org/2000/svg">
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
                    <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <?php endif; ?>
          <?php if( in_array( 'payments', $allowedTabs ) ) : ?>
          <div id="profile-payments" class="tab-pane fade" role="tabpanel">
            <div class="row">
            <?php if( false && apply_filters( 'futurewordpress/project/quizandfiltersearch/system/isactive', 'stripe-cancelsubscription' ) ) : ?>
              <div class="col-lg-6 col-md-6">
                <div class="card text-center">
                  <div class="card-body payment-page-card">
                    <h3 class=""><?php esc_html_e( 'Pause Subscription',   'quiz-and-filter-search-domain' ); ?></h3>
                    <div class="mt-2 form-check form-check-custom form-check-solid form-switch">
                      <input class="form-check-input fwp-form-checkbox-pause-subscribe" type="checkbox" <?php echo esc_attr( in_array( $userInfo->meta->enable_subscription, [ 'on' ] ) ? 'checked' : '' ); ?> name="meta-enable_subscription" />
                    </div>
                  </div>
                </div>
              </div>
              <?php endif; ?>
              <?php if( apply_filters( 'futurewordpress/project/quizandfiltersearch/system/isactive', 'stripe-cancelsubscription' ) ) : ?>
              <div class="col-lg-6 col-md-6">
                <div class="card text-center">
                  <div class="card-body payment-page-card">
                    <h3 class=""><?php esc_html_e( 'Subscription',   'quiz-and-filter-search-domain' ); ?></h3>
                    <?php if( (bool) $userInfo->meta->subscribe !== false ) : ?>
                    <button type="button" class="btn btn-light-danger fw-bold btn-sm fwp-sweetalert-field" data-config="<?php echo esc_attr( json_encode( [
                      'title' => 'Attantion!',
                      'text' => 'Do you really want to cancel this Subscription?',
                      'icon' => 'error',
                      'confirmButtonText' => 'I Confirm it'
                    ] ) ); ?>" data-userid="<?php echo esc_html( $userInfo->ID ); ?>"><?php esc_html_e( 'Cancel', 'quiz-and-filter-search-domain' ); ?></button>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <?php endif; ?>
            </div>
            <div class="card">
              <div class="card-header d-flex justify-content-between">
                <div class="header-title pause-unpause-subscription-wrap">
                  <h4 class="card-title"><?php esc_html_e( 'Payment History',   'quiz-and-filter-search-domain' ); ?></h4>
                  
                  <?php if( ! apply_filters( 'futurewordpress/project/quizandfiltersearch/system/isactive', 'stripe-cancelsubscription' ) ) : ?>
                    <?php
                      $is_active = in_array( $userInfo->meta->enable_subscription, [ 'on' ] );
                      $lastchanged = get_usermeta( $userInfo->ID, 'subscription_last_changed', true );
                      $dida_pause = ( ! apply_filters( 'futurewordpress/project/quizandfiltersearch/payment/stripe/allowswitchpause', true, $is_active ? 'pause' : 'unpause', $userInfo->ID ) ); // ( $lastchanged && $lastchanged == date( 'M, Y' ) ) 
                      $dida_pause = false;
                      $is_pending = false; // ( $lastchanged && $lastchanged == date( 'M, Y' ) );
                      $config = [
                        'popup_title'       => __( 'Enter payment card details', 'quiz-and-filter-search-domain' ),
                        'card_number'       => __( 'Card Number:', 'quiz-and-filter-search-domain' ),
                        'expire_month'      => __( 'Expiration month:', 'quiz-and-filter-search-domain' ),
                        'expire_year'       => __( 'Expiration year:', 'quiz-and-filter-search-domain' ),
                        'card_ccv'          => __( 'CVC:', 'quiz-and-filter-search-domain' ),
                        'pls_fillall'       => __( 'Please fillup all fields first.', 'quiz-and-filter-search-domain' ),
                        'pls_fixwrngcdnm'   => __( 'Seems you\'ve inputed a wrong card number', 'quiz-and-filter-search-domain' ),
                        'pls_fillmonth'          => __( 'Please input Month in numeric format', 'quiz-and-filter-search-domain' ),
                        'pls_fixyear'          => __( 'Card expiration year should be future date.', 'quiz-and-filter-search-domain' ),
                        'pls_fixccv'          => __( 'Please provide valid CVC number.', 'quiz-and-filter-search-domain' ),
                      ];
                    ?>
                    <div class="btn-group btn-group-sm">
                      <button class="btn btn-soft-success border-active change-payment-card" type="button" role="button" name="change-payment-card" data-config="<?php echo esc_attr( json_encode( $config ) ); ?>" data-userid="<?php echo esc_attr( $userInfo->ID ); ?>"><?php esc_html_e( 'Change Payment Card',   'quiz-and-filter-search-domain' ); ?></button>
                      <button class="pause-unpause-subscription btn <?php echo esc_attr( $is_pending ? 'btn-light' : ( $is_active ? 'btn-outline-warning border-active' : 'btn-primary' ) ); ?>" type="button" role="button" name="meta-enable_subscription" data-current="<?php echo esc_attr( $is_active ? ( ( $dida_pause && $is_active ) ? 'pending' : 'pause' ) : 'unpause' ); ?>" data-pause-title="<?php esc_attr_e( 'Pause My Retainer', 'quiz-and-filter-search-domain' ); ?>" data-unpause-title="<?php esc_attr_e( 'Resume My Retainer', 'quiz-and-filter-search-domain' ); ?>" data-pending-title="<?php esc_attr_e( 'Pending', 'quiz-and-filter-search-domain' ); ?>" <?php echo esc_attr( $is_pending ? 'data-handled=true disabled' : '' ); ?>>
                        <?php echo esc_html( $is_pending ? __( 'Pending', 'quiz-and-filter-search-domain' ) : ( $is_active ? __( 'Pause My Retainer', 'quiz-and-filter-search-domain' ) : __( 'Resume My Retainer', 'quiz-and-filter-search-domain' ) ) ); ?>
                      </button>
                    </div>
                    <?php endif; ?>
                </div>
              </div>
              <div class="card-body">

                <?php $payments = apply_filters( 'futurewordpress/project/quizandfiltersearch/payment/stripe/payment_history', [], empty( $userInfo->data->user_email ) ?  $userInfo->meta->email : $userInfo->data->user_email );
                $payments[ 'data' ] = isset( $payments[ 'data' ] ) ? (array) $payments[ 'data' ] : [];
                ?>

                <div class="<?php echo esc_attr( ( count( $payments[ 'data' ] ) > 0 ) ? 'custom-table-effect' : '' ); ?> table-responsive  border rounded">
                  <table class="table mb-0 fwp-datatable-field" data-toggle="data-table">
                    <thead>
                      <tr class="bg-white">
                        <th><?php esc_html_e( 'Name',   'quiz-and-filter-search-domain' ); ?></th>
                        <th><?php esc_html_e( 'Email',   'quiz-and-filter-search-domain' ); ?></th>
                        <th><?php esc_html_e( 'Date',   'quiz-and-filter-search-domain' ); ?></th>
                        <th><?php esc_html_e( 'Currency',   'quiz-and-filter-search-domain' ); ?></th>
                        <th><?php esc_html_e( 'Amount',   'quiz-and-filter-search-domain' ); ?></th>
                        <th><?php esc_html_e( 'Status',   'quiz-and-filter-search-domain' ); ?></th>
                        <th><?php esc_html_e( 'Invoice',   'quiz-and-filter-search-domain' ); ?></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if( count( $payments[ 'data' ] ) <= 0 ) : ?>
                        <tr>
                          <td colspan="7"><img src="<?php echo esc_url( TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_URI . '/icons/Card Payment_Monochromatic.svg' ); ?>" alt="<?php esc_attr_e( 'No Payments',   'quiz-and-filter-search-domain' ); ?>"></td>
                        </tr>
                      <?php else : ?>
                        <?php // print_r( $payments[ 'data' ] ); ?>
                        <?php foreach( $payments[ 'data' ] as $i => $pay ) :
                          $pay = (array) $pay; ?>
                        <tr>
                          <td><?php echo esc_html( ! empty( $pay[ 'billing_details' ][ 'name' ] ) ? $pay[ 'billing_details' ][ 'name' ] : $userInfo->meta->first_name . ' ' . $userInfo->meta->last_name ); ?></td>
                          <td><?php echo esc_html( ! empty( $userInfo->data->user_email ) ? $userInfo->data->user_email : $userInfo->meta->email ); ?></td>
                          <td><?php echo esc_html( wp_date( 'M d, Y', $pay[ 'created' ] ) ); ?></td>
                          <td><?php echo esc_html( strtoupper( $pay[ 'currency' ] ) ); ?></td>
                          <td><?php echo esc_html( number_format_i18n( ( $pay[ 'amount' ] / 100 ), 2 ) ); ?></td>
                          <td><?php echo esc_html( ( $pay[ 'paid' ] ) ? __( 'Paid', 'quiz-and-filter-search-domain' ) : __( 'Unpaid', 'quiz-and-filter-search-domain' ) ); ?></td>
                          <td>
                            <div class="d-flex justify-content-evenly">
                              <a class="btn btn-primary btn-icon btn-sm rounded-pill" href="<?php echo esc_url( $pay[ 'receipt_url' ] ); ?>" role="button" target="_blank">
                                <span class="btn-inner">
                                  <svg class="icon-32" width="32" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#fff">
                                    <path d="M4 3C4 1.89543 4.89543 1 6 1H13.0801C13.664 1 14.2187 1.25513 14.5986 1.69841L19.5185 7.43822C19.8292 7.80071 20 8.26239 20 8.73981V21C20 22.1046 19.1046 23 18 23H6C4.89543 23 4 22.1046 4 21V3Z" fill="currentColor" fill-opacity="0.4"></path>
                                    <path d="M13.0801 1H6C4.89543 1 4 1.89543 4 3V21C4 22.1046 4.89543 23 6 23H18C19.1046 23 20 22.1046 20 21V8.73981M13.0801 1C13.664 1 14.2187 1.25513 14.5986 1.69841L19.5185 7.43822C19.8292 7.80071 20 8.26239 20 8.73981M13.0801 1V5.73981C13.0801 7.39666 14.4232 8.73981 16.0801 8.73981H20" stroke="currentColor"></path>
                                    <path d="M9.15961 13.1986L9.15957 13.1986L9.15961 13.1986Z" fill="currentColor" fill-opacity="0.4" stroke="currentColor" stroke-linecap="round"></path>
                                    <line x1="12.975" y1="12.6181" x2="11.2497" y2="18.6566" stroke="currentColor" stroke-linecap="round"></line>
                                    <path d="M15.1037 17.8012C15.1037 17.8012 15.1037 17.8013 15.1036 17.8014L15.1037 17.8013L15.1037 17.8012Z" fill="currentColor" fill-opacity="0.4" stroke="currentColor" stroke-linecap="round"></path>
                                  </svg>
                                </span>
                              </a>
                            </div>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <?php endif; ?>
          <?php if( in_array( 'settings', $allowedTabs ) ) : ?>
          <div id="profile-settings" class="tab-pane fade" role="tabpanel">
            <div class="card">
              <div class="card-header">
                <div class="header-title">
                <h4 class="card-title"><?php esc_html_e( 'Profile Settings',   'quiz-and-filter-search-domain' ); ?></h4>
                </div>
              </div>
              <div class="card-body">
                <?php include TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/dashboard/cards/content.php'; ?>
              </div>
            </div>
          </div>
          <?php endif; ?>
          <?php if( in_array( 'contact', $allowedTabs ) && apply_filters( 'futurewordpress/project/quizandfiltersearch/system/isactive', 'social-contact' ) ) : ?>
          <div id="profile-contact" class="tab-pane fade" role="tabpanel">
            
            <div class="card">
              <div class="card-header d-flex justify-content-between">
                <div class="header-title">
                  <h4 class="card-title"><?php esc_html_e( 'Contact us now!',   'quiz-and-filter-search-domain' ); ?></h4>
                </div>
              </div>
              <div class="card-body">

                <div class="row">

                  <?php $social = apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'social-telegram', false );if( $social && ! empty( $social ) ) : ?>
                  <div class="col-lg-4 col-md-6">
                    <div class="card bg-soft-warning">
                      <a class="card-body" href="<?php echo esc_url( apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'social-telegram', false ) ); ?>" target="_blank">
                        <div class="d-block justify-content-between align-items-center">
                          <div class="rounded p-3 text-center">
                            <svg width="100px" height="100px" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Telegram" role="img" viewBox="0 0 512 512"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><rect width="512" height="512" rx="15%" fill="#ffcf01"></rect><path fill="#c8daea" d="M199 404c-11 0-10-4-13-14l-32-105 245-144"></path><path fill="#a9c9dd" d="M199 404c7 0 11-4 16-8l45-43-56-34"></path><path fill="#f6fbfe" d="M204 319l135 99c14 9 26 4 30-14l55-258c5-22-9-32-24-25L79 245c-21 8-21 21-4 26l83 26 190-121c9-5 17-3 11 4"></path></g></svg>
                          </div>
                          <div class="text-center mt-3">
                            <h3 class="counter" style="visibility: visible;"><?php esc_html_e( 'Telegram',   'quiz-and-filter-search-domain' ); ?></h3>
                          </div>
                        </div>
                      </a>
                    </div>
                  </div>
                  <?php endif; ?>
                  <?php $social = apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'social-whatsapp', false );if( $social && ! empty( $social ) ) : ?>
                  <div class="col-lg-4 col-md-6">
                    <div class="card bg-soft-warning">
                      <a class="card-body" href="<?php echo esc_url( apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'social-whatsapp', false ) ); ?>" target="_blank">
                        <div class="d-block justify-content-between align-items-center">
                          <div class="rounded p-3 text-center">
                            <svg width="100px" height="100px" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="WhatsApp" role="img" viewBox="0 0 512 512" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><rect width="512" height="512" rx="15%" fill="#ffcf01"></rect><path fill="#ffcf01" stroke="#ffffff" stroke-width="26" d="M123 393l14-65a138 138 0 1150 47z"></path><path fill="#ffffff" d="M308 273c-3-2-6-3-9 1l-12 16c-3 2-5 3-9 1-15-8-36-17-54-47-1-4 1-6 3-8l9-14c2-2 1-4 0-6l-12-29c-3-8-6-7-9-7h-8c-2 0-6 1-10 5-22 22-13 53 3 73 3 4 23 40 66 59 32 14 39 12 48 10 11-1 22-10 27-19 1-3 6-16 2-18"></path></g></svg>
                          </div>
                          <div class="text-center mt-3">
                            <h3 class="counter" style="visibility: visible;"><?php esc_html_e( 'WhatsApp',   'quiz-and-filter-search-domain' ); ?></h3>
                          </div>
                        </div>
                      </a>
                    </div>
                  </div>
                  <?php endif; ?>
                  <?php $social = apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'social-email', false );if( $social && ! empty( $social ) ) : ?>
                  <div class="col-lg-4 col-md-6">
                    <div class="card bg-soft-warning">
                      <a class="card-body" href="mailto:<?php echo esc_attr( apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'social-email', false ) ); ?>">
                        <div class="d-block justify-content-between align-items-center">
                          <div class="rounded p-3 text-center">
                            <svg width="100px" height="100px" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Email" role="img" viewBox="0 0 512 512" stroke="#ffcf01"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><rect width="512" height="512" rx="15%" fill="#ffcf01"></rect><rect width="356" height="256" x="78" y="128" fill="#ffffff" rx="8%"></rect><path fill="none" stroke="#ffcf01" stroke-width="20" d="M434 128L269 292c-7 8-19 8-26 0L78 128m0 256l129-128m227 128L305 256"></path></g></svg>
                          </div>
                          <div class="text-center mt-3">
                            <h3 class="counter" style="visibility: visible;"><?php esc_html_e( 'Email',   'quiz-and-filter-search-domain' ); ?></h3>
                          </div>
                        </div>
                      </a>
                    </div>
                  </div>
                  <?php endif; ?>
                  <?php $social = apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'social-contactus', false );if( $social && ! empty( $social ) ) : ?>
                  <div class="col-lg-12 col-md-12">
                    <div class="card">
                      <a class="card-body" href="<?php echo esc_url( apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'social-contactus', false ) ); ?>" target="_blank">
                        <div class="d-block justify-content-between align-items-center">
                          <div class="rounded p-3 text-center">
                            <img src="<?php echo esc_url( TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_URI . '/icons/Team meeting_Monochromatic.svg' ); ?>" alt="Contact Us" height="" width="">
                          </div>
                          <div class="text-center mt-3">
                            <h3 class="counter" style="visibility: visible;"><?php esc_html_e( 'Contact US',   'quiz-and-filter-search-domain' ); ?></h3>
                          </div>
                        </div>
                      </a>
                    </div>
                  </div>
                  <?php endif; ?>

                </div>
                
              </div>
            </div>
          </div>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </div>
</main>
<style>
.content.fs-6.d-flex.flex-column-fluid {margin-top: 5rem;display: block;position: relative;}
.payment-page-card {display: flex;justify-content: space-evenly;flex-wrap: wrap;align-content: center;align-items: center;}
</style>



  <?php
endif;
get_footer();
?>
<?php if( false ) : ?>
<div class="d-flex flex-column flex-root">
  <div class="page d-flex flex-row flex-column-fluid">
  <div class="wrapper d-flex flex-column flex-row-fluid">
    <div class="d-flex flex-column flex-column-fluid">
    <?php // include TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/dashboard/cards/toolbar.php'; ?>

    <div class="content fs-6 d-flex flex-column-fluid">
      <div class="container-xxl">
      <div class="row g-5 g-xxl-12">
        <div class="col-xl-12">
        <?php include TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/dashboard/cards/content.php'; ?>
        </div>
      </div>
      </div>
    </div>

    <?php // include TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/dashboard/cards/footer.php'; ?>
    
    </div>
  </div>

  <?php // include TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/dashboard/cards/sidebar.php'; ?>

  </div>
</div>
<?php endif; ?>