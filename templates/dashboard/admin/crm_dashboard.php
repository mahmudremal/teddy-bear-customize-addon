
<?php do_action( 'futurewordpress/project/quizandfiltersearch/parts/call', 'before_content' ); ?>
<div class="content-inner container-fluid pb-0" id="page_layout">
    <?php do_action( 'futurewordpress/project/quizandfiltersearch/admin/notices', 'content' ); ?>
    <?php do_action( 'futurewordpress/project/quizandfiltersearch/parts/call', 'content' ); ?>
</div>
<?php do_action( 'futurewordpress/project/quizandfiltersearch/parts/call', 'aft_ercontent' ); ?>

<?php
add_action( 'admin_footer', function() {
    ?>
    <link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <?php if( false ) : ?>
        <!-- SwiperSlider Script -->
        <script src="https://templates.iqonic.design/product/qompac-ui/html/dist/assets/vendor/swiperSlider/swiper-bundle.min.js"></script>
        <script src="https://templates.iqonic.design/product/qompac-ui/html/dist/assets/js/plugins/swiper-slider.js" defer></script>
        <!-- Lodash Utility -->
        <script src="https://templates.iqonic.design/product/qompac-ui/html/dist/assets/vendor/lodash/lodash.min.js"></script>
        <!-- Utilities Functions -->
        <script src="https://templates.iqonic.design/product/qompac-ui/html/dist/assets/js/iqonic-script/utility.min.js"></script>
        <!-- Settings Script -->
        <script src="https://templates.iqonic.design/product/qompac-ui/html/dist/assets/js/iqonic-script/setting.min.js"></script>
        <!-- Settings Init Script -->
        <script src="https://templates.iqonic.design/product/qompac-ui/html/dist/assets/js/setting-init.js"></script>
        <!-- External Library Bundle Script -->
        <script src="https://templates.iqonic.design/product/qompac-ui/html/dist/assets/js/core/external.min.js"></script>
        <!-- Widgetchart Script -->
        <script src="https://templates.iqonic.design/product/qompac-ui/html/dist/assets/js/charts/widgetcharts.js?v=1.0.1" defer></script>
        <!-- Dashboard Script -->
        <script src="https://templates.iqonic.design/product/qompac-ui/html/dist/assets/js/charts/dashboard.js?v=1.0.1" defer></script>
        <!-- qompacui Script -->
        <script src="https://templates.iqonic.design/product/qompac-ui/html/dist/assets/js/qompac-ui.js?v=1.0.1" defer></script>
        <script src="https://templates.iqonic.design/product/qompac-ui/html/dist/assets/js/sidebar.js?v=1.0.1" defer></script>
    <?php endif; ?>
    <?php
}, 100, 0 );
?>