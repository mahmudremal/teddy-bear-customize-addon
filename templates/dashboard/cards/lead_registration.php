<?php
/**
 * Checkout video clip shortner template.
 * 
 * @package TeddyBearCustomizeAddon
 */
$userInfo = get_user_by( 'id', hex2bin( get_query_var( 'lead_registration' ) ) );
if( is_user_logged_in() ) {
  $user_slug = apply_filters( 'futurewordpress/project/quizandfiltersearch/user/dashboardpermalink', $userInfo->ID, $userInfo->data->user_nicename );
  wp_redirect( $user_slug );exit;
}
// $_SESSION[ 'current-lead' ] = $userInfo->ID;
if( get_transient( '_lead_user_registration-' . apply_filters( 'futurewordpress/project/quizandfiltersearch/user/visitorip', '' ) ) ) {
  delete_transient( '_lead_user_registration-' . apply_filters( 'futurewordpress/project/quizandfiltersearch/user/visitorip', '' ) );
}
set_transient( '_lead_user_registration-' . apply_filters( 'futurewordpress/project/quizandfiltersearch/user/visitorip', '' ), $userInfo->ID, 7200 );
// if( function_exists( 'setcookie' ) ) {setcookie( '_lead_user_registration', $userInfo->ID, time()+31556926 );}

$is_done = get_user_meta( $userInfo->ID, 'registration_done', true );
if( $is_done && $is_done >= 10 ) {
  wp_redirect( apply_filters( 'futurewordpress/project/quizandfiltersearch/user/dashboardpermalink', $userInfo->ID, $userInfo->data->user_nicename ) );exit;
}
$needToSelect = false;
$regLink = get_user_meta( $userInfo->ID, 'contract_type', true );
if( ! $regLink || empty( $regLink ) || (int) $regLink <= 0 ) {
  /**
   * Woonna code here.
   */
  $contractForms = apply_filters( 'futurewordpress/project/quizandfiltersearch/action/contractforms', [], false );
  if( count( $contractForms ) == 1 ) {
    foreach( $contractForms as $contract_key => $contract_text ) {$regLink = $contract_key;break;}
  } else {
    // $needToSelect = true;
    // wp_die( 'Template to select a contract' );
  }
}

$regLink = apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'regis-link-url-' . $regLink, false );
// if( $regLink && ! empty( $regLink ) ) {$regLink = get_the_permalink( $regLink );}

if( $regLink && ! empty( $regLink ) && ! $needToSelect ) {
  $regLink = str_replace( [ '{{nonce}}' ], [ time() ], $regLink );
  // wp_die( esc_url( $regLink ) );
  wp_redirect( esc_url( $regLink ) );exit;
} else {
  $defaultContract = apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'signature-defaultcontract', '' );
  if( ! empty( $defaultContract ) ) {
    wp_redirect( $defaultContract );exit;
  } else {
    wp_die( __( 'Regisatration link not found.', 'quiz-and-filter-search-domain' ) );
  }
}


$userMeta = array_map( function( $a ){ return $a[0]; }, (array) get_user_meta( $userInfo->ID ) );
$userInfo = (object) wp_parse_args( $userInfo, [
  'id'            => '',
  'meta'          => (object) apply_filters( 'futurewordpress/project/quizandfiltersearch/usermeta/defaults', (array) $userMeta )
] );
$errorHappens = false;
// if( get_current_user_id() == $user_profile ) {}
// print_r( $userInfo );

// 'id | ID | slug | email | login ', $user_profile

if( is_wp_error( $userInfo ) || $errorHappens ) :
  http_response_code( 404 );
  status_header( 404, 'Page not found' );
  add_filter( 'pre_get_document_title', function( $title ) {global $errorHappens;return $errorHappens;}, 10, 1 );
  wp_die( $errorHappens, __( 'Error Happens', 'quiz-and-filter-search-domain' ) );
else :
  add_filter( 'pre_get_document_title', function( $title ) {
    $title = apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'dashboard-title', __( 'Registration Field', 'quiz-and-filter-search-domain' ) );
    return $title;
  }, 10, 1 );
  get_header();
  ?>
  
<?php if( true ) : ?>
  <?php include TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/dashboard/cards/select_registration_page.php'; ?>
<?php else: ?>
  <div class="d-flex flex-column flex-root d-none">
    <div class="page d-flex flex-row flex-column-fluid">
      <div class="wrapper d-flex flex-column flex-row-fluid">
        <div class="d-flex flex-column flex-column-fluid">
          <?php // include TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/dashboard/cards/toolbar.php'; ?>
          <div class="content fs-6 d-flex flex-column-fluid">
            <div class="container-xxl">
              <div class="row g-5 g-xxl-12">
                <div class="col-xl-12">
                  <?php // include TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/dashboard/cards/form-wizard.php'; ?>
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
  <style>
    .content.fs-6.d-flex.flex-column-fluid {
      margin-top: 5rem;
      display: block;
      position: relative;
    }
  </style>
<?php endif; ?>

  <?php
endif;
get_footer();
?>