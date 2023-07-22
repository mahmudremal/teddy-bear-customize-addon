<?php do_action( 'futurewordpress/project/quizandfiltersearch/parts/call', 'before_rootnav' ); ?>
<div class="<?php echo esc_attr( implode( ' ', apply_filters( 'futurewordpress/project/quizandfiltersearch/classes/rootnav', [ 'position-relative' ] ) ) ); ?>">
    <?php do_action( 'futurewordpress/project/quizandfiltersearch/parts/call', 'before_nav' ); ?>
    <!--Nav Start-->
    <nav class="nav navbar navbar-expand-xl navbar-light iq-navbar">
        <div class="container-fluid navbar-inner">
            <a href="../dashboard/index.html" class="navbar-brand">
                
                <!--Logo start-->
                <div class="logo-main">
                    <div class="logo-normal">
                        <img src="<?php echo esc_url( TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_URI . '/icons/crm-crm (1).svg' ); ?>" style="height: 30px;width: 40px;">
                    </div>
                    <div class="logo-mini">
                        <img src="<?php echo esc_url( TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_URI . '/icons/crm-crm (1).svg' ); ?>" style="height: 30px;width: 40px;">
                    </div>
                </div>
                <!--logo End-->
                <h4 class="logo-title d-block d-xl-none" data-setting="app_name"><?php esc_html_e( 'CRM Dashboard', 'quiz-and-filter-search-domain' ); ?></h4>
            </a>
            <div class="sidebar-toggle" data-toggle="sidebar" data-active="true">
                <i class="icon d-flex">
                <svg class="icon-20" width="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M4,11V13H16L10.5,18.5L11.92,19.92L19.84,12L11.92,4.08L10.5,5.5L16,11H4Z" />
                </svg>
                </i>
            </div>
            <div class="d-flex align-items-center justify-content-between product-offcanvas">
                <div class="breadcrumb-title border-end me-3 pe-3 d-none d-xl-block">
                <small class="mb-0 text-capitalize"><?php esc_html_e( 'Home', 'quiz-and-filter-search-domain' ); ?></small>
                </div>
                <div class="offcanvas offcanvas-end shadow-none iq-product-menu-responsive" tabindex="-1" id="offcanvasBottom">
                <div class="offcanvas-body">
                    <ul class="iq-nav-menu list-unstyled">
                        <?php
                        $pageRoot = '/admin.php?page=crm_dashboard';$current_path = isset( $_GET[ 'path' ] ) ? $_GET[ 'path' ] : '';
                        $menus = apply_filters( 'futurewordpress/project/quizandfiltersearch/admin/pagetree', [] );$current_pathinfo = explode( '/', $current_path );
                        for($i=0;$i<=2;$i++) {$current_pathinfo[$i] = isset( $current_pathinfo[$i] ) ? $current_pathinfo[$i] : false;}
                        // print_r( $current_pathinfo );wp_die();
                        foreach( $menus as $menu_id => $menu ) :
                            $menu[ 'submenu' ] = isset( $menu[ 'submenu' ] ) ? $menu[ 'submenu' ] : [];
                        ?>
                        <li class="nav-item ">
                            <?php // ( count( $menu[ 'submenu' ] ) >= 1 ) ? esc_attr( '#' . $menu_id ) :  ?>
                            <a class="nav-link menu-arrow justify-content-start <?php echo esc_attr( in_array( $menu_id, [ $current_path, $current_pathinfo[0] ] ) ? 'active' : '' ); ?>" data-bs-toggle="collapse" href="<?php echo esc_url( admin_url( $pageRoot . '&path=' . $menu_id ) ); ?>" role="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $menu_id ); ?>">
                                <?php echo isset( $menu[ 'icon' ] ) ? $menu[ 'icon' ] : ''; ?>
                                <span class="nav-text ms-2"><?php echo esc_html( isset( $menu[ 'title' ] ) ? $menu[ 'title' ] : '' ); ?></span>
                            </a>
                            <?php if( count( $menu[ 'submenu' ] ) >= 1 ) : ?>
                            <ul class="iq-header-sub-menu list-unstyled collapse" id="<?php echo esc_attr( $menu_id ); ?>">
                                <?php foreach( $menu[ 'submenu' ] as $submenu_id => $submenu ) :
                                    $submenu[ 'submenu' ] = isset( $submenu[ 'submenu' ] ) ? $submenu[ 'submenu' ] : []; ?>
                                    <?php // ( count( $submenu[ 'submenu' ] ) >= 1 ) ? esc_attr( '#' . $submenu_id ) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link menu-arrow <?php echo esc_attr( in_array( $menu_id . '/' . $submenu_id, [ $current_path, $current_pathinfo[0] . '/' . $current_pathinfo[1] ] ) ? 'active' : '' ); ?>" data-bs-toggle="collapse" href="<?php echo esc_url( admin_url( $pageRoot . '&path=' . $menu_id . '/' . $submenu_id ) ); ?>" role="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $submenu_id ); ?>">
                                            <?php echo esc_html( isset( $submenu[ 'title' ] ) ? $submenu[ 'title' ] : '' ); ?>
                                            <?php if( count( $submenu[ 'submenu' ] ) >= 1 ) : ?>
                                            <i class="right-icon">
                                                <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.5 5L15.5 12L8.5 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                            </i>
                                            <?php endif; ?>
                                        </a>
                                        <?php if( count( $submenu[ 'submenu' ] ) >= 1 ) : ?>
                                        <ul aria-expanded="false" class="iq-header-sub-menu left list-unstyled collapse" id="<?php echo esc_attr( $submenu_id ); ?>">
                                            <?php foreach( $submenu[ 'submenu' ] as $subsubmenu_id => $subsubmenu ) :
                                                $subsubmenu[ 'submenu' ] = isset( $subsubmenu[ 'submenu' ] ) ? $subsubmenu[ 'submenu' ] : []; ?>
                                                <li class="nav-item"><a class="nav-link <?php echo esc_attr( in_array( $menu_id . '/' . $submenu_id . '/' . $subsubmenu_id, [ $current_path, $current_pathinfo[0] . '/' . $current_pathinfo[1] . '/' . $current_pathinfo[2] ] ) ? 'active' : '' ); ?>" href="<?php echo esc_url( admin_url( $pageRoot . '&path=' . $menu_id . '/' . $submenu_id . '/' . $subsubmenu_id ) ); ?>"><?php echo esc_html( isset( $subsubmenu[ 'title' ] ) ? $subsubmenu[ 'title' ] : '' ); ?></a></li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>   
                </div>
            </div>
            <div class="d-flex align-items-center">
                <button id="navbar-toggle" class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon">
                <span class="navbar-toggler-bar bar1 mt-1"></span>
                <span class="navbar-toggler-bar bar2"></span>
                <span class="navbar-toggler-bar bar3"></span>
                </span>
                </button>
            </div>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="mb-2 navbar-nav ms-auto align-items-center navbar-list mb-lg-0 ">
                    <li class="nav-item dropdown">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=crm_dashboard&path=notices' ) ); ?>" class="nav-link">
                            <svg class="icon-24" width="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19.7695 11.6453C19.039 10.7923 18.7071 10.0531 18.7071 8.79716V8.37013C18.7071 6.73354 18.3304 5.67907 17.5115 4.62459C16.2493 2.98699 14.1244 2 12.0442 2H11.9558C9.91935 2 7.86106 2.94167 6.577 4.5128C5.71333 5.58842 5.29293 6.68822 5.29293 8.37013V8.79716C5.29293 10.0531 4.98284 10.7923 4.23049 11.6453C3.67691 12.2738 3.5 13.0815 3.5 13.9557C3.5 14.8309 3.78723 15.6598 4.36367 16.3336C5.11602 17.1413 6.17846 17.6569 7.26375 17.7466C8.83505 17.9258 10.4063 17.9933 12.0005 17.9933C13.5937 17.9933 15.165 17.8805 16.7372 17.7466C17.8215 17.6569 18.884 17.1413 19.6363 16.3336C20.2118 15.6598 20.5 14.8309 20.5 13.9557C20.5 13.0815 20.3231 12.2738 19.7695 11.6453Z" fill="currentColor"></path>
                                <path opacity="0.4" d="M14.0088 19.2283C13.5088 19.1215 10.4627 19.1215 9.96275 19.2283C9.53539 19.327 9.07324 19.5566 9.07324 20.0602C9.09809 20.5406 9.37935 20.9646 9.76895 21.2335L9.76795 21.2345C10.2718 21.6273 10.8632 21.877 11.4824 21.9667C11.8123 22.012 12.1482 22.01 12.4901 21.9667C13.1083 21.877 13.6997 21.6273 14.2036 21.2345L14.2026 21.2335C14.5922 20.9646 14.8734 20.5406 14.8983 20.0602C14.8983 19.5566 14.4361 19.327 14.0088 19.2283Z" fill="currentColor"></path>
                            </svg>
                            <span class="bg-danger dots"></span>
                        </a>
                    </li>
                    <li class="nav-item iq-full-screen d-none  d-xl-block border-end" id="fullscreen-item">
                        <a href="#"  class="nav-link pe-3" id="btnFullscreen" data-bs-toggle="dropdown" >
                            <span class="btn-inner">
                            <svg class="normal-screen icon-24" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M18.5528 5.99656L13.8595 10.8961" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M14.8016 5.97618L18.5524 5.99629L18.5176 9.96906" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M5.8574 18.896L10.5507 13.9964" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M9.60852 18.9164L5.85775 18.8963L5.89258 14.9235" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                <svg class="full-normal-screen icon-32 d-none" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M13.7542 10.1932L18.1867 5.79319" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M17.2976 10.212L13.7547 10.1934L13.7871 6.62518" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M10.4224 13.5726L5.82149 18.1398" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M6.74391 13.5535L10.4209 13.5723L10.3867 17.2755" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!--Nav End--> 
    <?php do_action( 'futurewordpress/project/quizandfiltersearch/parts/call', 'after_nav' ); ?>
</div>
<?php do_action( 'futurewordpress/project/quizandfiltersearch/parts/call', 'after_rootnav' ); ?>