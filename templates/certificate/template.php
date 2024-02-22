<?php
/**
 * Certificate Preview and other sections
 */
global $teddy_Order;global $teddy_Product;global $teddy_Certificate;
/**
 * Print Header Section
 */
get_header();

$order_id = get_query_var('certificate_order_id');
$item_id = get_query_var('order_item_id');
$order = wc_get_order((int) $order_id);
$template_404 = apply_filters('teddybear/project/system/getoption', 'certificate-404template', '');
if(!$order || is_wp_error($order)) {
    echo do_shortcode('[elementor-template id="' . $template_404 . '"]');
}
$order_id = $order->get_id();

$certificates = $teddy_Certificate->get_all_certificates($order, $item_id);
try {
    if (count($certificates) >= 1 && isset($certificates[0]) && !empty($certificates[0])) {
        $pdf_url = str_replace(ABSPATH, home_url('/'), $certificates[0]);
        /**
         * Two ways. One is to preview and another is to redirect.
         * https://dubidof.com/certificates/3133/782/
         */
        if (true) {
            wp_redirect($pdf_url);
        } else {
            ?>
            <iframe src="<?php echo esc_url($pdf_url); ?>" frameborder="0"></iframe>
            <?php
        }
    } else {
        $errorMessage = __('No certificate found.', 'teddybearsprompts');
        echo do_shortcode('[elementor-template id="' . $template_404 . '"]');
    }
} catch (\Exception $e) {
    $errorMessage = $e->getMessage();
    echo do_shortcode('[elementor-template id="' . $template_404 . '"]');
}

/**
 * Print Footer Section
 */
get_footer();
