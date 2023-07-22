<?php if( ! empty( $userInfo->meta->message ) ) : ?>
  <!-- Notices goes here -->
  
<div class="col-lg-12">
  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-lg-12">
          <div class="d-flex align-items-center mb-4">
            <h5><?php esc_html_e( 'Message form Administrative', 'quiz-and-filter-search-domain' ); ?></h5>
          </div>
          <div class="d-flex align-items-center">
            <div class="pe-3 border-end">
              <?php echo wp_kses_post( $userInfo->meta->message ); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>