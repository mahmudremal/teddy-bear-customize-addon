<?php
/**
 * Loadmore Single Posts
 *
 * @package TeddyBearCustomizeAddon
 */

namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;

use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
use \WP_Query;

class Dashboard {
	use Singleton;
	public $args = null;
	public $allowedPages = null;
	public $dashboardEndpoint = null;
	protected function __construct() {
		// load class.
    $this->allowedPages = [ 'crm_dashboard' ];
    $this->args = [];
		$this->setup_hooks();
	}
	protected function setup_hooks() {
    add_filter( 'futurewordpress/project/quizandfiltersearch/admin/allowedpage', [ $this, 'allowedpage' ], 10, 0 );
    add_filter( 'futurewordpress/project/quizandfiltersearch/admin/pagetree', [ $this, 'pageTree' ], 10, 1 );
    add_filter( 'futurewordpress/project/quizandfiltersearch/admin/title', [ $this, 'adminTitle' ], 10, 2 );
		add_action( 'admin_menu', [ $this, 'admin_menu' ], 10, 0 );
    add_action( 'wp_after_admin_bar_render', [ $this, 'wp_after_admin_bar_render' ], 10, 0 );

    add_action( 'futurewordpress/project/quizandfiltersearch/parts/call', [ $this, 'partsCall' ], 10, 1 );
    add_filter( 'futurewordpress/project/quizandfiltersearch/classes/rootnav', [ $this, 'rootnavClasses' ], 10, 1 );

    
    add_action( 'futurewordpress/project/quizandfiltersearch/parts/split', [ $this, 'partSplit' ], 10, 1 );
    add_action( 'futurewordpress/project/quizandfiltersearch/admin/notices', [ $this, 'adminNotices' ], 10, 1 );

    add_filter( 'futurewordpress/project/quizandfiltersearch/usermeta/defaults', [ $this, 'defaultUserMeta' ], 10, 1 );

    add_filter( 'futurewordpress/project/quizandfiltersearch/widgets/statustab', [ $this, 'statusTab' ], 10, 2 );
	}
  public function admin_menu() {
    add_menu_page(
      __(  'CRM Dashboard', 'quiz-and-filter-search-domain' ),     // page title
      __(  'Dashboard', 'quiz-and-filter-search-domain' ),     // menu title
      'manage_options',   // capability
      'crm_dashboard',     // menu slug  
      [ $this, 'crm_dashboard' ], // callback function
      TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_URI . '/icons/dashboard-alt.svg',
      10
    );
  }
  public function crm_dashboard() {
    global $title;
    ?>
    <div class="wrap dashboard__body-start">
      <?php include TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/dashboard/admin/crm_dashboard.php'; ?>
    </div>
    <?php
  }
  public function wp_after_admin_bar_render() {
    if( ! isset( $_GET[ 'page' ] ) || ! in_array( $_GET[ 'page' ], $this->allowedPages ) ) {return;}
    include TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/dashboard/admin/bar_render.php';
  }
  public function allowedpage() {
    return $this->allowedPages;
  }
  public function pageTree( $args ) {
    return [
      'dashboard' => [
        'title'   => __(  'Dashboard', 'quiz-and-filter-search-domain' ),
        'icon'    => '<svg class="icon-20" width="20" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M9.14373 20.7821V17.7152C9.14372 16.9381 9.77567 16.3067 10.5584 16.3018H13.4326C14.2189 16.3018 14.8563 16.9346 14.8563 17.7152V20.7732C14.8562 21.4473 15.404 21.9951 16.0829 22H18.0438C18.9596 22.0023 19.8388 21.6428 20.4872 21.0007C21.1356 20.3586 21.5 19.4868 21.5 18.5775V9.86585C21.5 9.13139 21.1721 8.43471 20.6046 7.9635L13.943 2.67427C12.7785 1.74912 11.1154 1.77901 9.98539 2.74538L3.46701 7.9635C2.87274 8.42082 2.51755 9.11956 2.5 9.86585V18.5686C2.5 20.4637 4.04738 22 5.95617 22H7.87229C8.19917 22.0023 8.51349 21.8751 8.74547 21.6464C8.97746 21.4178 9.10793 21.1067 9.10792 20.7821H9.14373Z" fill="currentColor"></path></svg>',
        'submenu' => []
      ],
      'leads'     => [
        'title'   => __(  'Leads from Facebook', 'quiz-and-filter-search-domain' ),
        'icon'    => '<svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.9488 14.54C8.49884 14.54 5.58789 15.1038 5.58789 17.2795C5.58789 19.4562 8.51765 20.0001 11.9488 20.0001C15.3988 20.0001 18.3098 19.4364 18.3098 17.2606C18.3098 15.084 15.38 14.54 11.9488 14.54Z" fill="currentColor"></path><path opacity="0.4" d="M11.949 12.467C14.2851 12.467 16.1583 10.5831 16.1583 8.23351C16.1583 5.88306 14.2851 4 11.949 4C9.61293 4 7.73975 5.88306 7.73975 8.23351C7.73975 10.5831 9.61293 12.467 11.949 12.467Z" fill="currentColor"></path><path opacity="0.4" d="M21.0881 9.21923C21.6925 6.84176 19.9205 4.70654 17.664 4.70654C17.4187 4.70654 17.1841 4.73356 16.9549 4.77949C16.9244 4.78669 16.8904 4.802 16.8725 4.82902C16.8519 4.86324 16.8671 4.90917 16.8895 4.93889C17.5673 5.89528 17.9568 7.0597 17.9568 8.30967C17.9568 9.50741 17.5996 10.6241 16.9728 11.5508C16.9083 11.6462 16.9656 11.775 17.0793 11.7948C17.2369 11.8227 17.3981 11.8371 17.5629 11.8416C19.2059 11.8849 20.6807 10.8213 21.0881 9.21923Z" fill="currentColor"></path><path d="M22.8094 14.817C22.5086 14.1722 21.7824 13.73 20.6783 13.513C20.1572 13.3851 18.747 13.205 17.4352 13.2293C17.4155 13.232 17.4048 13.2455 17.403 13.2545C17.4003 13.2671 17.4057 13.2887 17.4316 13.3022C18.0378 13.6039 20.3811 14.916 20.0865 17.6834C20.074 17.8032 20.1698 17.9068 20.2888 17.8888C20.8655 17.8059 22.3492 17.4853 22.8094 16.4866C23.0637 15.9589 23.0637 15.3456 22.8094 14.817Z" fill="currentColor"></path><path opacity="0.4" d="M7.04459 4.77973C6.81626 4.7329 6.58077 4.70679 6.33543 4.70679C4.07901 4.70679 2.30701 6.84201 2.9123 9.21947C3.31882 10.8216 4.79355 11.8851 6.43661 11.8419C6.60136 11.8374 6.76343 11.8221 6.92013 11.7951C7.03384 11.7753 7.09115 11.6465 7.02668 11.551C6.3999 10.6234 6.04263 9.50765 6.04263 8.30991C6.04263 7.05904 6.43303 5.89462 7.11085 4.93913C7.13234 4.90941 7.14845 4.86348 7.12696 4.82926C7.10906 4.80135 7.07593 4.78694 7.04459 4.77973Z" fill="currentColor"></path><path d="M3.32156 13.5127C2.21752 13.7297 1.49225 14.1719 1.19139 14.8167C0.936203 15.3453 0.936203 15.9586 1.19139 16.4872C1.65163 17.4851 3.13531 17.8066 3.71195 17.8885C3.83104 17.9065 3.92595 17.8038 3.91342 17.6832C3.61883 14.9167 5.9621 13.6046 6.56918 13.3029C6.59425 13.2885 6.59962 13.2677 6.59694 13.2542C6.59515 13.2452 6.5853 13.2317 6.5656 13.2299C5.25294 13.2047 3.84358 13.3848 3.32156 13.5127Z" fill="currentColor"></path></svg>',
        'submenu' => [
          // 'spages' => [
          //   'title'   => __(  'pages', 'quiz-and-filter-search-domain' ),
          //   'icon'    => '<svg class="icon-20" width="20" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M9.14373 20.7821V17.7152C9.14372 16.9381 9.77567 16.3067 10.5584 16.3018H13.4326C14.2189 16.3018 14.8563 16.9346 14.8563 17.7152V20.7732C14.8562 21.4473 15.404 21.9951 16.0829 22H18.0438C18.9596 22.0023 19.8388 21.6428 20.4872 21.0007C21.1356 20.3586 21.5 19.4868 21.5 18.5775V9.86585C21.5 9.13139 21.1721 8.43471 20.6046 7.9635L13.943 2.67427C12.7785 1.74912 11.1154 1.77901 9.98539 2.74538L3.46701 7.9635C2.87274 8.42082 2.51755 9.11956 2.5 9.86585V18.5686C2.5 20.4637 4.04738 22 5.95617 22H7.87229C8.19917 22.0023 8.51349 21.8751 8.74547 21.6464C8.97746 21.4178 9.10793 21.1067 9.10792 20.7821H9.14373Z" fill="currentColor"></path></svg>',
          //   'submenu' => []
          // ],
        ]
      ],
      // 'payments' => [
      //   'title'   => __(  'Payments', 'quiz-and-filter-search-domain' ),
      //   'icon'    => '<svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M21.9964 8.37513H17.7618C15.7911 8.37859 14.1947 9.93514 14.1911 11.8566C14.1884 13.7823 15.7867 15.3458 17.7618 15.3484H22V15.6543C22 19.0136 19.9636 21 16.5173 21H7.48356C4.03644 21 2 19.0136 2 15.6543V8.33786C2 4.97862 4.03644 3 7.48356 3H16.5138C19.96 3 21.9964 4.97862 21.9964 8.33786V8.37513ZM6.73956 8.36733H12.3796H12.3831H12.3902C12.8124 8.36559 13.1538 8.03019 13.152 7.61765C13.1502 7.20598 12.8053 6.87318 12.3831 6.87491H6.73956C6.32 6.87664 5.97956 7.20858 5.97778 7.61852C5.976 8.03019 6.31733 8.36559 6.73956 8.36733Z" fill="currentColor"></path><path opacity="0.4" d="M16.0374 12.2966C16.2465 13.2478 17.0805 13.917 18.0326 13.8996H21.2825C21.6787 13.8996 22 13.5715 22 13.166V10.6344C21.9991 10.2297 21.6787 9.90077 21.2825 9.8999H17.9561C16.8731 9.90338 15.9983 10.8024 16 11.9102C16 12.0398 16.0128 12.1695 16.0374 12.2966Z" fill="currentColor"></path><circle cx="18" cy="11.8999" r="1" fill="currentColor"></circle></svg>',
      //   'submenu' => [
      //     'stripe_logs' => [
      //       'title'   => __(  'Stripe logs', 'quiz-and-filter-search-domain' ),
      //       'submenu' => []
      //     ],
      //   ]
      // ],
      'archives' => [
        'title'   => __(  'Archives', 'quiz-and-filter-search-domain' ),
        'icon'    => '<svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path opacity="0.4" d="M2 11.0786C2.05 13.4166 2.19 17.4156 2.21 17.8566C2.281 18.7996 2.642 19.7526 3.204 20.4246C3.986 21.3676 4.949 21.7886 6.292 21.7886C8.148 21.7986 10.194 21.7986 12.181 21.7986C14.176 21.7986 16.112 21.7986 17.747 21.7886C19.071 21.7886 20.064 21.3566 20.836 20.4246C21.398 19.7526 21.759 18.7896 21.81 17.8566C21.83 17.4856 21.93 13.1446 21.99 11.0786H2Z" fill="currentColor"></path><path d="M11.2451 15.3843V16.6783C11.2451 17.0923 11.5811 17.4283 11.9951 17.4283C12.4091 17.4283 12.7451 17.0923 12.7451 16.6783V15.3843C12.7451 14.9703 12.4091 14.6343 11.9951 14.6343C11.5811 14.6343 11.2451 14.9703 11.2451 15.3843Z" fill="currentColor"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M10.211 14.5565C10.111 14.9195 9.762 15.1515 9.384 15.1015C6.833 14.7455 4.395 13.8405 2.337 12.4815C2.126 12.3435 2 12.1075 2 11.8555V8.38949C2 6.28949 3.712 4.58149 5.817 4.58149H7.784C7.972 3.12949 9.202 2.00049 10.704 2.00049H13.286C14.787 2.00049 16.018 3.12949 16.206 4.58149H18.183C20.282 4.58149 21.99 6.28949 21.99 8.38949V11.8555C21.99 12.1075 21.863 12.3425 21.654 12.4815C19.592 13.8465 17.144 14.7555 14.576 15.1105C14.541 15.1155 14.507 15.1175 14.473 15.1175C14.134 15.1175 13.831 14.8885 13.746 14.5525C13.544 13.7565 12.821 13.1995 11.99 13.1995C11.148 13.1995 10.433 13.7445 10.211 14.5565ZM13.286 3.50049H10.704C10.031 3.50049 9.469 3.96049 9.301 4.58149H14.688C14.52 3.96049 13.958 3.50049 13.286 3.50049Z" fill="currentColor"></path></svg>',
        'submenu' => []
      ],
    ];
  }

  public function partsCall( $position ) {
    $pageRoot = '/admin.php?page=crm_dashboard';$current_path = isset( $_GET[ 'path' ] ) ? $_GET[ 'path' ] : '';
    $current_pathinfo = explode( '/', $current_path );
    for($i=0;$i<=5;$i++) {$current_pathinfo[$i] = isset( $current_pathinfo[$i] ) ? $current_pathinfo[$i] : false;}
    $this->dashboardEndpoint = $current_pathinfo;
    $this->args = [ 'page' => $pageRoot, 'path' => $current_path,  'split' => $current_pathinfo ];
    
    switch( $position ) {
      case 'after_nav':
        include TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/dashboard/admin/after_nav.php';
        break;
      case 'content':
        if( in_array( 'dashboard', [ $current_pathinfo[0], '' ] ) ) {
          include TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/dashboard/admin/content.php';
        } else {
          do_action( 'futurewordpress/project/quizandfiltersearch/parts/split', $this->args );
        }
        break;
      case 'homecontent':
        if( in_array( 'dashboard', [ $current_pathinfo[0], '' ] ) ) {
          // include TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/dashboard/admin/tasks.php';
          include TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/dashboard/admin/leads.php';
        } else {
          do_action( 'futurewordpress/project/quizandfiltersearch/parts/split', $this->args );
        }
        break;
      default:
        break;
    }
  }
  public function rootnavClasses( $classes ) {
    $classes[] = 'iq-banner';
    return $classes;
  }
  public function partSplit( $args ) {
    if( $args[ 'split' ][0] == 'leads' && $args[ 'split' ][1] == 'edit' || $args[ 'split' ][1] == 'add' ) {
      include TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/dashboard/admin/editprofile.php';
    } else if( $args[ 'split' ][0] == '' || $args[ 'split' ][0] == 'leads' && $args[ 'split' ][1] === false ) {
      include TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/dashboard/admin/leads.php';
    } else if( $args[ 'split' ][0] == 'payments' && $args[ 'split' ][1] == false ) {
      include TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/dashboard/admin/stripe.php';
    } else if( $args[ 'split' ][1] == 'stripe_logs' ) {
      include TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/dashboard/admin/' . ( ( $args[ 'split' ][2] === false ) ? 'stripe_logs' : 'stripe_details' ) . '.php';
    } else if( $args[ 'split' ][0] == 'archives' ) {
      include TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/dashboard/admin/' . ( ( $args[ 'split' ][2] === false ) ? 'archives' : 'archive' ) . '.php';
    } else if( $args[ 'split' ][0] == 'notices' ) {
      include TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/dashboard/admin/notices.php';
    } else {}
  }
  public function adminTitle( $default, $args ) {
    if( ! $args || ! isset( $args[ 'split' ] ) ) {
      $args = $this->args;
    }
    if( $args[ 'split' ][0] == 'leads' && $args[ 'split' ][1] == 'edit' || $args[ 'split' ][1] == 'add' ) {
      return __( 'Edit Lead', 'quiz-and-filter-search-domain' );
    } else if( $args[ 'split' ][0] == '' || $args[ 'split' ][0] == 'leads' && $args[ 'split' ][1] === false ) {
      return __( 'Leads', 'quiz-and-filter-search-domain' );
    } else if( $args[ 'split' ][0] == 'payments' && $args[ 'split' ][1] == false ) {
      return __( 'Payments', 'quiz-and-filter-search-domain' );
    } else if( $args[ 'split' ][1] == 'stripe_logs' ) {
      return __( 'Stripe Logs', 'quiz-and-filter-search-domain' );
    } else if( $args[ 'split' ][0] == 'archives' ) {
      return __( 'Archive Uploaded', 'quiz-and-filter-search-domain' );
    } else if( $args[ 'split' ][0] == 'notices' ) {
      return __( 'Notices', 'quiz-and-filter-search-domain' );
    } else {return  $default;}
  }
  public function adminNotices() {
    $alert = (array) get_transient( 'futurewordpress/project/quizandfiltersearch/transiant/admin/' . get_current_user_id() );
    if( isset( $alert[ 'type' ] ) && isset( $alert[ 'message' ] ) ) {
      delete_transient( 'futurewordpress/project/quizandfiltersearch/transiant/admin/' . get_current_user_id() );
      ?>
      <!--begin::Alert-->
      <div class="alert alert-<?php echo esc_attr( $alert[ 'type' ] ); ?> d-flex align-items-center p-5 mb-10">
        <!--begin::Icon-->
        <span class="svg-icon svg-icon-2hx svg-icon-<?php echo esc_attr( $alert[ 'type' ] ); ?> me-4">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path opacity="0.3" d="M20.5543 4.37824L12.1798 2.02473C12.0626 1.99176 11.9376 1.99176 11.8203 2.02473L3.44572 4.37824C3.18118 4.45258 3 4.6807 3 4.93945V13.569C3 14.6914 3.48509 15.8404 4.4417 16.984C5.17231 17.8575 6.18314 18.7345 7.446 19.5909C9.56752 21.0295 11.6566 21.912 11.7445 21.9488C11.8258 21.9829 11.9129 22 12.0001 22C12.0872 22 12.1744 21.983 12.2557 21.9488C12.3435 21.912 14.4326 21.0295 16.5541 19.5909C17.8169 18.7345 18.8277 17.8575 19.5584 16.984C20.515 15.8404 21 14.6914 21 13.569V4.93945C21 4.6807 20.8189 4.45258 20.5543 4.37824Z" fill="black"></path><path d="M10.5606 11.3042L9.57283 10.3018C9.28174 10.0065 8.80522 10.0065 8.51412 10.3018C8.22897 10.5912 8.22897 11.0559 8.51412 11.3452L10.4182 13.2773C10.8099 13.6747 11.451 13.6747 11.8427 13.2773L15.4859 9.58051C15.771 9.29117 15.771 8.82648 15.4859 8.53714C15.1948 8.24176 14.7183 8.24176 14.4272 8.53714L11.7002 11.3042C11.3869 11.6221 10.874 11.6221 10.5606 11.3042Z" fill="black"></path></svg>
        </span>
        <!--end::Icon-->
        <!--begin::Wrapper-->
        <div class="d-flex flex-column">
          <!--begin::Title-->
          <!-- <h4 class="mb-1 text-dark">This is an alert</h4> -->
          <!--end::Title-->
          <!--begin::Content-->
          <span><?php echo wp_kses_post( $alert[ 'message' ] ); ?></span>
          <!--end::Content-->
        </div>
        <!--end::Wrapper-->
      </div>
      <!--end::Alert-->
      <?php
    }
  }
  public function defaultUserMeta( $meta = [] ) {
    return wp_parse_args( (array) $meta, [
      'subscribe'           => false,
      'enable_subscription' => 'on',
      'subscription_id'     => '',
      'monthly_retainer'    => '',
      'content_calendar'    => '',
      'content_library'     => '',

      'first_name'          => '',
      'last_name'           => '',

      'status'              => 'call_scheduled',
      'tiktok'              => '',
      'YouTube_url'         => '',
      'instagram_url'       => '',
      'website'             => '',

      'address1'            => '',
      'address2'            => '',
      'phone'               => '',
      'phone1'              => '',
      'email'               => '',
      'zip'                 => '',
      'country'             => '',
      'city'                => '',
      'state'               => '',
      'newpassword'         => '',
      'custom_avatar'       => '',

      'company_name'        => '',
      'next_meeting'        => '',
      'meeting_link'        => '',

      'question1'           => '',
      'question2'           => '',
      'question3'           => '',
      'question4'           => '',

      'document_type'       => '',
      'contract_type'       => '',
      'services'            => '',
      'message'             => '',
      'stripe_customer_id'  => '',
      'stripe_subsc_id'     => ''
    ] );
  }
  public function statusTab( $html, $userInfo ) {
    ob_start();
    ?>
    <?php
    return ob_get_clean();
  }

}
