<?php
/**
 * Certificate Preview and other sections
 */

$order_id = get_query_var('certificate_order_id');
$_bg_type = get_query_var('certificate_bg_type');
$item_id = get_query_var('order_item_id');
$item_type = $item_id;
// $order_id = $order->get_id();


try {
    /**
     * Print Header Section
     */
    // get_header();
    do_action('teddybear/project/certificate/preview', $order_id, $item_id, $_bg_type);
    /**
     * Print Footer Section
     */
    // get_footer();
    ?>
    <style>
        /* #certificate_preview_frame {width: 100%;height: 100%;display: block;position: relative;min-height: 100vh;} */
        #certificate_preview_frame {top: 0;left: 0;width: 100%;height: 100%;position: fixed;z-index: 9999999;}
    </style>
    <?php
} catch (\Exception $e) {
    /**
     * Print Header Section
     */
    get_header();
    $errorMessage = $e->getMessage();
    $template_404 = apply_filters('teddybear/project/system/getoption', 'certificate-404template', '');
    echo do_shortcode(sprintf('[elementor-template id="%s"]', esc_attr($template_404)));
    /**
     * Print Footer Section
     */
    get_footer();
}


