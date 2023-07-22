<main class="main-content">
<!-- content-inner pb-0 container -->
  <div class="" id="page_layout">
    <div class="wrapper">
      <section class="login-content overflow-hidden">
         <div class="row no-gutters align-items-center bg-white">            
            <div class="col-md-12 col-lg-6 align-self-center">
              <div class="row justify-content-center pt-5">
                <div class="col-md-8">
                  <div class="card  d-flex justify-content-center mb-0">
                    <div class="card-body">
                      <h2 class="mt-3 mb-4"><?php esc_html_e( 'Select Registration', 'quiz-and-filter-search-domain' ); ?></h2>
                      <p class="cnf-mail mb-1">
                      <?php esc_html_e( 'We\'re sorry, but it seems like the admin hasn\'t selected a specific registration type for you. In order to proceed with the registration process, please choose the appropriate registration type from the available options on the registration page. Thank you for your cooperation.', 'quiz-and-filter-search-domain' ); ?>
                      </p>
                      <div class="d-inline-block w-100 mt-3">
                        <div class="form-group">
                          <select class="form-select fwp-select-registration-type-to-proceed" data-trigger name="metadata[contract_type]" data-userid="<?php echo esc_attr( $userInfo->ID ); ?>">
                            <option value="" selected><?php esc_html_e( 'Select A Registration Type', 'quiz-and-filter-search-domain' ); ?></option>
                            <?php foreach( $contractForms as $key => $text ) : ?>
                              <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $text ); ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>   
            </div>
             <div class="col-lg-6 d-lg-block d-none bg-primary p-0  overflow-hidden">
               <img src="https://templates.iqonic.design/product/qompac-ui/html/dist/assets/images/auth/01.png" class="img-fluid gradient-main" alt="images" loading="lazy" >
            </div>
         </div>
      </section>
    </div>
  </div>
</main>
