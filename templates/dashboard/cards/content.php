<!--begin::Form-->
<form class="form d-flex flex-center" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
  <input type="hidden" name="action" value="futurewordpress/project/quizandfiltersearch/action/dashboard">
  <input type="hidden" name="userid" value="<?php echo esc_attr( $userInfo->ID ); ?>">
  <?php wp_nonce_field( 'futurewordpress/project/quizandfiltersearch/nonce/dashboard', '_nonce', true, true ); ?>
  <div class="card-body mw-800px">
    <?php if( $alert = (array) get_transient( 'futurewordpress/project/quizandfiltersearch/transiant/dashboard/' . get_current_user_id() ) && isset( $alert[ 'type' ] ) && isset( $alert[ 'message' ] ) ) : ?>
      <?php delete_transient( 'futurewordpress/project/quizandfiltersearch/transiant/dashboard/' . get_current_user_id() ); ?>
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
          <h4 class="mb-1 text-dark">This is an alert</h4>
          <!--end::Title-->
          <!--begin::Content-->
          <span><?php echo wp_kses_post( $alert[ 'message' ] ); ?></span>
          <!--end::Content-->
        </div>
        <!--end::Wrapper-->
      </div>
      <!--end::Alert-->
    <?php endif; ?>
    
    <!--begin::Form row-->
    <div class="row mb-8">
      <label class="col-lg-3 col-form-label"><?php esc_html_e( 'Email Address', 'quiz-and-filter-search-domain' ); ?></label>
      <div class="col-lg-9">
        <div class="input-group mb-5">
          <input type="text" class="form-control form-control-lg form-control-solid" name="data-user_email" placeholder="example@gmail.com" aria-label="example@gmail.com" aria-describedby="basic-editopen-contact" oldautocomplete="remove" autocomplete="off" disabled value="<?php echo esc_attr( empty( $userInfo->data->user_email ) ? $userInfo->meta->email : $userInfo->data->user_email ); ?>">
          <span class="input-group-text" id="basic-editopen-contact">
            <i class="fas fa-pencil-alt fs-4"></i>
          </span>
        </div>
        
        <?php if( $text = apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'docs-emailaddress', false ) ) : ?>
        <div class="form-text py-2"><?php echo wp_kses_post( $text ); ?> 
          <?php if( $url = apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'docs-emailaddressurl', false ) ) : ?>
          <a href="<?php echo esc_url( $url ); ?>" class="fw-boldk"><?php esc_html_e( 'Learn more', 'quiz-and-filter-search-domain' ); ?></a>.
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <!--end::Form row-->
    <!--begin::Form row-->
    <div class="row mb-8">
      <label class="col-lg-3 col-form-label"><?php esc_html_e( 'Contact Number', 'quiz-and-filter-search-domain' ); ?></label>
      <div class="col-lg-9">
        <div class="input-group mb-5">
          <input type="text" class="form-control form-control-lg form-control-solid" placeholder="123 456 789" aria-label="123 456 789" aria-describedby="basic-editopen-contact" oldautocomplete="remove" autocomplete="off" disabled value="<?php echo esc_attr( $userInfo->meta->phone ); ?>" name="meta-phone" >
          <span class="input-group-text" id="basic-editopen-contact">
            <i class="fas fa-pencil-alt fs-4"></i>
          </span>
        </div>
        <?php if( $text = apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'docs-contactnumber', false ) ) : ?>
        <div class="form-text py-2"><?php echo wp_kses_post( $text ); ?> 
          <?php if( $url = apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'docs-contactnumberurl', false ) ) : ?>
          <a href="<?php echo esc_url( $url ); ?>" class="fw-boldk"><?php esc_html_e( 'Learn more', 'quiz-and-filter-search-domain' ); ?></a>.
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <!--end::Form row-->
    <!--begin::Form row-->
    <div class="row mb-8">
      <label class="col-lg-3 col-form-label"><?php esc_html_e( 'Website URL', 'quiz-and-filter-search-domain' ); ?></label>
      <div class="col-lg-9">
        <div class="input-group mb-5 spinner spinner-sm spinner-primary spinner-right">
          <input class="form-control form-control-lg form-control-solid" type="url" value="<?php echo esc_attr( $userInfo->meta->website ); ?>" name="meta-website" disabled />
          <span class="input-group-text" id="basic-editopen-website">
            <i class="fas fa-pencil-alt fs-4"></i>
          </span>
        </div>
        <?php if( $text = apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'docs-website', false ) ) : ?>
        <div class="form-text py-2"><?php echo wp_kses_post( $text ); ?> 
          <?php if( $url = apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'docs-websiteurl', false ) ) : ?>
          <a href="<?php echo esc_url( $url ); ?>" class="fw-boldk"><?php esc_html_e( 'Learn more', 'quiz-and-filter-search-domain' ); ?></a>.
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <!--end::Form row-->

    
    <div class="separator separator-dashed my-10"></div>
    <!--begin::Form row-->
    <div class="row mb-8">
      <label class="col-lg-3 col-form-label"><?php esc_html_e( 'Instagram Handles', 'quiz-and-filter-search-domain' ); ?></label>
      <div class="col-lg-9">
        <div class="input-group input-group-solid mb-5">
          <span class="input-group-text" id="basic-pre-addons-1"><?php echo esc_html( 'https://instagram.com/' ); ?></span>
          <input type="text" class="form-control form-control-solid" id="basic-url-1" aria-describedby="basic-addon3" value="<?php echo esc_attr( $userInfo->meta->instagram_url ); ?>" name="meta-instagram_url" placeholder="<?php echo esc_attr( '@username' ); ?>" disabled>
          <span class="input-group-text" id="basic-editopen-instagram">
            <i class="fas fa-pencil-alt fs-4"></i>
          </span>
        </div>
      </div>
    </div>
    <!--end::Form row-->
    <!--begin::Form row-->
    <div class="row mb-8">
      <label class="col-lg-3 col-form-label"><?php esc_html_e( 'TikTok Handles', 'quiz-and-filter-search-domain' ); ?></label>
      <div class="col-lg-9">
        <div class="input-group input-group-solid mb-5">
          <span class="input-group-text" id="basic-pre-addons-2"><?php echo esc_html( 'https://tiktok.com/' ); ?></span>
          <input type="text" class="form-control form-control-solid" id="basic-url-2" aria-describedby="basic-addon3" value="<?php echo esc_attr( $userInfo->meta->tiktok ); ?>" name="meta-tiktok" placeholder="<?php echo esc_attr( '@username' ); ?>" disabled>
          <span class="input-group-text" id="basic-editopen-tiktok">
            <i class="fas fa-pencil-alt fs-4"></i>
          </span>
        </div>
      </div>
    </div>
    <!--end::Form row-->
    <!--begin::Form row-->
    <div class="row mb-8">
      <label class="col-lg-3 col-form-label"><?php esc_html_e( 'YouTube Channel', 'quiz-and-filter-search-domain' ); ?></label>
      <div class="col-lg-9">
        <div class="input-group input-group-solid mb-5">
          <span class="input-group-text" id="basic-pre-addons-3"><?php echo esc_html( 'https://youtube.com/' ); ?></span>
          <input type="text" class="form-control form-control-solid" id="basic-url-3" aria-describedby="basic-addon3" value="<?php echo esc_attr( $userInfo->meta->YouTube_url ); ?>" name="meta-YouTube_url" placeholder="<?php echo esc_attr( '@username' ); ?>" disabled>
          <span class="input-group-text" id="basic-editopen-youtube">
            <i class="fas fa-pencil-alt fs-4"></i>
          </span>
        </div>
      </div>
    </div>
    <!--end::Form row-->

    
    <div class="separator separator-dashed my-10"></div>
    
    <!--begin::Form row-->
    <div class="row mb-8">
      <label class="col-lg-3 col-form-label"><?php esc_html_e( 'Change Password', 'quiz-and-filter-search-domain' ); ?></label>
      <div class="col-lg-9">
        <div class="spinner spinner-sm spinner-primary spinner-right">
          <!-- <input class="form-control form-control-lg form-control-solid" id="change-password-field" type="password" value="" placeholder="******************" /> -->
          <button type="button" class="btn btn-light-primary btn-outline fw-bold btn-sm" data-bs-toggle="modal" data-bs-target="#changePasswordform"><?php esc_html_e( 'Click to change Password', 'quiz-and-filter-search-domain' ); ?></button>
        </div>
        
        <?php if( $text = apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'docs-changepassword', false ) ) : ?>
        <div class="form-text py-2"><?php echo wp_kses_post( $text ); ?> 
          <?php if( $url = apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'docs-changepasswordurl', false ) ) : ?>
          <a href="<?php echo esc_url( $url ); ?>" class="fw-boldk"><?php esc_html_e( 'Learn more', 'quiz-and-filter-search-domain' ); ?></a>.
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <!--end::Form row-->
    
    <!-- Modal -->
    <div class="modal fade" id="changePasswordform" tabindex="-1" role="dialog" aria-labelledby="changePasswordformTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordformTitle">Change your Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
              <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
                <input type="hidden" name="action" value="futurewordpress/project/quizandfiltersearch/action/changepassword">
                <input type="hidden" name="userid" value="<?php echo esc_attr( $userInfo->ID ); ?>">
                <?php wp_nonce_field( 'futurewordpress/project/quizandfiltersearch/nonce/dashboard', '_nonce', true, true ); ?>
                <div class="form-group my-1">
                    <label class="form-label" for="pwd"><?php esc_html_e( 'New Password:', 'quiz-and-filter-search-domain' ); ?></label>
                    <div class="input-group has-validation">
                      <span class="input-group-text password-toggle" id="passwordGroupToggler">
                        <svg class="icon-32 shown" width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path opacity="0.4" fill-rule="evenodd" clip-rule="evenodd" d="M17.7366 6.04606C19.4439 7.36388 20.8976 9.29455 21.9415 11.7091C22.0195 11.8924 22.0195 12.1067 21.9415 12.2812C19.8537 17.1103 16.1366 20 12 20H11.9902C7.86341 20 4.14634 17.1103 2.05854 12.2812C1.98049 12.1067 1.98049 11.8924 2.05854 11.7091C4.14634 6.87903 7.86341 4 11.9902 4H12C14.0683 4 16.0293 4.71758 17.7366 6.04606ZM8.09756 12C8.09756 14.1333 9.8439 15.8691 12 15.8691C14.1463 15.8691 15.8927 14.1333 15.8927 12C15.8927 9.85697 14.1463 8.12121 12 8.12121C9.8439 8.12121 8.09756 9.85697 8.09756 12Z" fill="currentColor"></path><path d="M14.4308 11.997C14.4308 13.3255 13.3381 14.4115 12.0015 14.4115C10.6552 14.4115 9.5625 13.3255 9.5625 11.997C9.5625 11.8321 9.58201 11.678 9.61128 11.5228H9.66006C10.743 11.5228 11.621 10.6695 11.6601 9.60184C11.7674 9.58342 11.8845 9.57275 12.0015 9.57275C13.3381 9.57275 14.4308 10.6588 14.4308 11.997Z" fill="currentColor"></path></svg>
                        <svg class="icon-32 hiden" width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path opacity="0.4" fill-rule="evenodd" clip-rule="evenodd" d="M11.9902 3.88184H12C13.3951 3.88184 14.7512 4.21657 16 4.84567L12.7415 8.13491C12.5073 8.09553 12.2537 8.066 12 8.066C9.8439 8.066 8.09756 9.82827 8.09756 12.004C8.09756 12.26 8.12683 12.516 8.16585 12.7523L4.5561 16.3949C3.58049 15.2529 2.73171 13.8736 2.05854 12.2895C1.98049 12.1123 1.98049 11.8957 2.05854 11.7087C4.14634 6.80583 7.86341 3.88184 11.9902 3.88184ZM18.4293 6.54985C19.8439 7.8494 21.0439 9.60183 21.9415 11.7087C22.0195 11.8957 22.0195 12.1123 21.9415 12.2895C19.8537 17.1924 16.1366 20.1262 12 20.1262H11.9902C10.1073 20.1262 8.30244 19.506 6.71219 18.3738L9.80488 15.2529C10.4293 15.6753 11.1902 15.9322 12 15.9322C14.1463 15.9322 15.8927 14.1699 15.8927 12.004C15.8927 11.1869 15.639 10.419 15.2195 9.78889L18.4293 6.54985Z" fill="currentColor"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M18.4296 6.54952L20.2052 4.75771C20.4979 4.4722 20.4979 3.99964 20.2052 3.71413C19.9223 3.42862 19.4637 3.42862 19.1711 3.71413L18.254 4.63957C18.2442 4.65926 18.2247 4.67895 18.2052 4.69864C18.1954 4.71833 18.1759 4.73802 18.1564 4.75771L17.2881 5.63491L14.1954 8.7558L3.72715 19.3186L3.69789 19.358C3.50276 19.6435 3.54179 20.0383 3.78569 20.2844C3.92228 20.4311 4.1174 20.5 4.30276 20.5C4.48813 20.5 4.6735 20.4311 4.81984 20.2844L15.2198 9.78855L18.4296 6.54952ZM12.0004 14.4555C13.337 14.4555 14.4297 13.3529 14.4297 12.0041C14.4297 11.5906 14.3321 11.1968 14.1565 10.8621L10.8687 14.1798C11.2004 14.3571 11.5907 14.4555 12.0004 14.4555Z" fill="currentColor"></path></svg>
                      </span>
                      <input type="password" name="password[new]" class="form-control" aria-describedby="passwordGroupToggler" required>
                    </div>
                </div>
                <div class="form-group my-1">
                    <label class="form-label" for="pwd"><?php esc_html_e( 'Old Password:', 'quiz-and-filter-search-domain' ); ?></label>
                    <div class="input-group has-validation">
                      <span class="input-group-text password-toggle" id="passwordGroupToggler">
                        <svg class="icon-32 shown" width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path opacity="0.4" fill-rule="evenodd" clip-rule="evenodd" d="M17.7366 6.04606C19.4439 7.36388 20.8976 9.29455 21.9415 11.7091C22.0195 11.8924 22.0195 12.1067 21.9415 12.2812C19.8537 17.1103 16.1366 20 12 20H11.9902C7.86341 20 4.14634 17.1103 2.05854 12.2812C1.98049 12.1067 1.98049 11.8924 2.05854 11.7091C4.14634 6.87903 7.86341 4 11.9902 4H12C14.0683 4 16.0293 4.71758 17.7366 6.04606ZM8.09756 12C8.09756 14.1333 9.8439 15.8691 12 15.8691C14.1463 15.8691 15.8927 14.1333 15.8927 12C15.8927 9.85697 14.1463 8.12121 12 8.12121C9.8439 8.12121 8.09756 9.85697 8.09756 12Z" fill="currentColor"></path><path d="M14.4308 11.997C14.4308 13.3255 13.3381 14.4115 12.0015 14.4115C10.6552 14.4115 9.5625 13.3255 9.5625 11.997C9.5625 11.8321 9.58201 11.678 9.61128 11.5228H9.66006C10.743 11.5228 11.621 10.6695 11.6601 9.60184C11.7674 9.58342 11.8845 9.57275 12.0015 9.57275C13.3381 9.57275 14.4308 10.6588 14.4308 11.997Z" fill="currentColor"></path></svg>
                        <svg class="icon-32 hiden" width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path opacity="0.4" fill-rule="evenodd" clip-rule="evenodd" d="M11.9902 3.88184H12C13.3951 3.88184 14.7512 4.21657 16 4.84567L12.7415 8.13491C12.5073 8.09553 12.2537 8.066 12 8.066C9.8439 8.066 8.09756 9.82827 8.09756 12.004C8.09756 12.26 8.12683 12.516 8.16585 12.7523L4.5561 16.3949C3.58049 15.2529 2.73171 13.8736 2.05854 12.2895C1.98049 12.1123 1.98049 11.8957 2.05854 11.7087C4.14634 6.80583 7.86341 3.88184 11.9902 3.88184ZM18.4293 6.54985C19.8439 7.8494 21.0439 9.60183 21.9415 11.7087C22.0195 11.8957 22.0195 12.1123 21.9415 12.2895C19.8537 17.1924 16.1366 20.1262 12 20.1262H11.9902C10.1073 20.1262 8.30244 19.506 6.71219 18.3738L9.80488 15.2529C10.4293 15.6753 11.1902 15.9322 12 15.9322C14.1463 15.9322 15.8927 14.1699 15.8927 12.004C15.8927 11.1869 15.639 10.419 15.2195 9.78889L18.4293 6.54985Z" fill="currentColor"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M18.4296 6.54952L20.2052 4.75771C20.4979 4.4722 20.4979 3.99964 20.2052 3.71413C19.9223 3.42862 19.4637 3.42862 19.1711 3.71413L18.254 4.63957C18.2442 4.65926 18.2247 4.67895 18.2052 4.69864C18.1954 4.71833 18.1759 4.73802 18.1564 4.75771L17.2881 5.63491L14.1954 8.7558L3.72715 19.3186L3.69789 19.358C3.50276 19.6435 3.54179 20.0383 3.78569 20.2844C3.92228 20.4311 4.1174 20.5 4.30276 20.5C4.48813 20.5 4.6735 20.4311 4.81984 20.2844L15.2198 9.78855L18.4296 6.54952ZM12.0004 14.4555C13.337 14.4555 14.4297 13.3529 14.4297 12.0041C14.4297 11.5906 14.3321 11.1968 14.1565 10.8621L10.8687 14.1798C11.2004 14.3571 11.5907 14.4555 12.0004 14.4555Z" fill="currentColor"></path></svg>
                      </span>
                      <input type="password" name="password[old]" class="form-control" aria-describedby="passwordGroupToggler" required>
                    </div>
                </div>
                <div class="form-group my-1">
                    <label class="form-label" for="pwd"><?php esc_html_e( 'Confirm Password:', 'quiz-and-filter-search-domain' ); ?></label>
                    <div class="input-group has-validation">
                      <span class="input-group-text password-toggle" id="passwordGroupToggler">
                        <svg class="icon-32 shown" width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path opacity="0.4" fill-rule="evenodd" clip-rule="evenodd" d="M17.7366 6.04606C19.4439 7.36388 20.8976 9.29455 21.9415 11.7091C22.0195 11.8924 22.0195 12.1067 21.9415 12.2812C19.8537 17.1103 16.1366 20 12 20H11.9902C7.86341 20 4.14634 17.1103 2.05854 12.2812C1.98049 12.1067 1.98049 11.8924 2.05854 11.7091C4.14634 6.87903 7.86341 4 11.9902 4H12C14.0683 4 16.0293 4.71758 17.7366 6.04606ZM8.09756 12C8.09756 14.1333 9.8439 15.8691 12 15.8691C14.1463 15.8691 15.8927 14.1333 15.8927 12C15.8927 9.85697 14.1463 8.12121 12 8.12121C9.8439 8.12121 8.09756 9.85697 8.09756 12Z" fill="currentColor"></path><path d="M14.4308 11.997C14.4308 13.3255 13.3381 14.4115 12.0015 14.4115C10.6552 14.4115 9.5625 13.3255 9.5625 11.997C9.5625 11.8321 9.58201 11.678 9.61128 11.5228H9.66006C10.743 11.5228 11.621 10.6695 11.6601 9.60184C11.7674 9.58342 11.8845 9.57275 12.0015 9.57275C13.3381 9.57275 14.4308 10.6588 14.4308 11.997Z" fill="currentColor"></path></svg>
                        <svg class="icon-32 hiden" width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path opacity="0.4" fill-rule="evenodd" clip-rule="evenodd" d="M11.9902 3.88184H12C13.3951 3.88184 14.7512 4.21657 16 4.84567L12.7415 8.13491C12.5073 8.09553 12.2537 8.066 12 8.066C9.8439 8.066 8.09756 9.82827 8.09756 12.004C8.09756 12.26 8.12683 12.516 8.16585 12.7523L4.5561 16.3949C3.58049 15.2529 2.73171 13.8736 2.05854 12.2895C1.98049 12.1123 1.98049 11.8957 2.05854 11.7087C4.14634 6.80583 7.86341 3.88184 11.9902 3.88184ZM18.4293 6.54985C19.8439 7.8494 21.0439 9.60183 21.9415 11.7087C22.0195 11.8957 22.0195 12.1123 21.9415 12.2895C19.8537 17.1924 16.1366 20.1262 12 20.1262H11.9902C10.1073 20.1262 8.30244 19.506 6.71219 18.3738L9.80488 15.2529C10.4293 15.6753 11.1902 15.9322 12 15.9322C14.1463 15.9322 15.8927 14.1699 15.8927 12.004C15.8927 11.1869 15.639 10.419 15.2195 9.78889L18.4293 6.54985Z" fill="currentColor"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M18.4296 6.54952L20.2052 4.75771C20.4979 4.4722 20.4979 3.99964 20.2052 3.71413C19.9223 3.42862 19.4637 3.42862 19.1711 3.71413L18.254 4.63957C18.2442 4.65926 18.2247 4.67895 18.2052 4.69864C18.1954 4.71833 18.1759 4.73802 18.1564 4.75771L17.2881 5.63491L14.1954 8.7558L3.72715 19.3186L3.69789 19.358C3.50276 19.6435 3.54179 20.0383 3.78569 20.2844C3.92228 20.4311 4.1174 20.5 4.30276 20.5C4.48813 20.5 4.6735 20.4311 4.81984 20.2844L15.2198 9.78855L18.4296 6.54952ZM12.0004 14.4555C13.337 14.4555 14.4297 13.3529 14.4297 12.0041C14.4297 11.5906 14.3321 11.1968 14.1565 10.8621L10.8687 14.1798C11.2004 14.3571 11.5907 14.4555 12.0004 14.4555Z" fill="currentColor"></path></svg>
                      </span>
                      <input type="password" name="password[confirm]" class="form-control" aria-describedby="passwordGroupToggler" required>
                    </div>
                </div>
                <div class="form-group mt-3">
                  <button type="submit" class="btn btn-primary"><?php esc_html_e( 'Submit', 'quiz-and-filter-search-domain' ); ?></button>
                  <button type="button" class="btn btn-danger"  data-bs-dismiss="modal"><?php esc_html_e( 'Cancel', 'quiz-and-filter-search-domain' ); ?></button>
                </div>
              </form>
            </div>
          </div>
      </div>
    </div>



    <!--begin::Form row-->
    <!-- <div class="row">
      <label class="col-lg-3 col-form-label"></label>
      <div class="col-lg-9">
        <button type="submit" class="btn btn-primary fw-bolder px-6 py-3 me-3"><?php esc_html_e( 'Save Changes', 'quiz-and-filter-search-domain' ); ?></button>
        <button type="reset" class="btn btn-color-gray-600 btn-active-light-primary fw-bolder px-6 py-3"><?php esc_html_e( 'Cancel', 'quiz-and-filter-search-domain' ); ?></button>
      </div>
    </div> -->
    <!--end::Form row-->
  </div>
</form>
<!--end::Form-->
<?php // include TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/dashboard/cards/model.php'; ?>