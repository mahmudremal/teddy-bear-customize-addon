<?php
/**
 * LoadmorePosts
 *
 * @package TeddyBearCustomizeAddon
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
use \WP_Query;
class Export {
	use Singleton;
	protected function __construct() {
		// load class.
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		add_action('wp_ajax_teddybear/project/ajax/export/product', [$this, 'export_product'], 10, 0);
		add_action('wp_ajax_nopriv_teddybear/project/ajax/export/product', [$this, 'export_product'], 10, 0);
		add_action('wp_ajax_teddybear/project/ajax/export/content', [$this, 'export_content'], 10, 0);
		add_action('wp_ajax_nopriv_teddybear/project/ajax/export/content', [$this, 'export_content'], 10, 0);

		add_action('init', [$this, 'plugins_loaded'], 10, 0);
		// add_filter('query_vars', [$this, 'query_vars'], 10, 1);
	}
	public function plugins_loaded() {
		// print_r(get_post_types([], 'names'));wp_die();
		foreach (get_post_types([], 'names') as $post_type) {
			add_action(sprintf('wp_ajax_teddybear/project/ajax/export/%s', $post_type), [$this, 'export_cpts'], 10, 0);
			add_action(sprintf('wp_ajax_nopriv_teddybear/project/ajax/export/%s', $post_type), [$this, 'export_cpts'], 10, 0);
		}
	}
	public function query_vars($queries) {
		$queries[] = 'paged';
		return $queries;
	}
	public function export_product() {
		do_action('teddybear/project/nonce/check', $_POST['_nonce']);
		$json = ['hooks' => ['exports_response'], 'message' => false];
		// Get Requested page number
		$paged = $_POST['paged']??1;
		// Set up arguments for the WooCommerce product query
		$args = [
			'posts_per_page'	=> 24,
			'orderby'			=> 'ID',
			'order'				=> 'DESC',
			'paged'				=> $paged,
			'post_type'			=> 'product',
		];
		// Get products using the query arguments
		$products = new WP_Query($args);
		// Create an array to store the exported data
		$export_data = [];
		// Loop through each product
		if ($products->have_posts()) {
			while ($products->have_posts()) {
				$products->the_post();
	
				// Get the product ID
				$product_id = get_the_ID();
	
				// Get the product metadata
				$product_meta = get_post_meta($product_id);
	
				// Get the product taxonomy terms
				$product_terms = wp_get_post_terms($product_id, 'product_cat');
	
				// Get the product comments
				$product_comments = get_comments(array(
					'post_id' => $product_id,
					'status' => 'approve',
				));
	
				// Create an array to store the product data
				$product_data = array(
					'ID' => $product_id,
					'Title' => get_the_title(),
					'Metadata' => $product_meta,
					'Terms' => $product_terms,
					'Comments' => $product_comments,
				);
	
				// Add the product data to the export array
				$export_data[] = $product_data;
			}
		}
		// Restore the global post data
		wp_reset_postdata();
	
		// Convert the export data to a JSON string
		// $export_json = json_encode($export_data);
	
		// Set the export headers
		// header('Content-Type: application/json');
		// header('Content-Disposition: attachment; filename="woocommerce_export.json"');
	
		$json['exports'] = $export_data;
		
		$json['pagination'] = [
			'current'		=> $paged,
			'totalposts'	=> $products->found_posts,
			'total'			=> $products->max_num_pages,
		];

		// Output the export data
		wp_send_json_success($json);
	
		// Prevent any further output
		// exit;
	}
	public function export_content() {
		do_action('teddybear/project/nonce/check', $_POST['_nonce']);
		$json = ['hooks' => ['exports_response'], 'message' => false]; // __('Operation Failed', 'teddybearsprompts')
		// Set up arguments for the WooCommerce product query
		$attachment_ids = isset($_POST['attachment_ids'])?explode(',', $_POST['attachment_ids']):false;
		if(true) {
			// Get Requested page number
			$paged = $_POST['paged']??1;
			// Set up arguments for the WooCommerce product query
			$args = [
				'posts_per_page'	=> 24,
				'orderby'			=> 'ID',
				'order'				=> 'DESC',
				'paged'				=> $paged,
				'post_status'		=> 'inherit',
				'post_type'			=> 'attachment',
				// 'post__in'       => $attachment_ids,
			];
			if($attachment_ids) {$args['post__in'] = $attachment_ids;}
			// Get products using the query arguments
			$attachments = new WP_Query($args);
			// Create an array to store the exported data
			$export_data = [];
			// Loop through each attachment
			if ($attachments->have_posts()) {
				while ($attachments->have_posts()) {
					$attachments->the_post();
		
					// Get the attachment ID
					$attachment_id = get_the_ID();
		
					// Get the attachment metadata
					$attachment_meta = get_post_meta($attachment_id);
		
					// Get the attachment taxonomy terms
					$attachment_terms = wp_get_post_terms($attachment_id, 'attachment_cat');
		
					// Get the attachment comments
					$attachment_comments = get_comments(array(
						'post_id' => $attachment_id,
						'status' => 'approve',
					));
		
					// Create an array to store the attachment data
					$attachment_data = array(
						'ID' => $attachment_id,
						'Title' => get_the_title(),
						'Metadata' => $attachment_meta,
						'Terms' => $attachment_terms,
						'Comments' => $attachment_comments,
					);
		
					// Add the attachment data to the export array
					$export_data[] = $attachment_data;
				}
		
				// Restore the global post data
				wp_reset_postdata();
	
				// Added to the output query
				$json['exports'] = $export_data;

				$json['pagination'] = [
					'current'		=> $paged,
					'totalposts'	=> $attachments->found_posts,
					'total'			=> $attachments->max_num_pages,
				];
				
				// Output the export data
				wp_send_json_success($json);
			}
		}
		wp_send_json_error($json);
	}
	public function export_cpts() {
		do_action('teddybear/project/nonce/check', $_POST['_nonce']);
		$json = ['hooks' => ['exports_response'], 'message' => false]; // __('Operation Failed', 'teddybearsprompts')
		// Set up arguments for the WooCommerce product query
		$post_in = isset($_POST['post_in'])?explode(',', $_POST['post_in']):false;
		if(true) {
			// Get Requested page number
			$paged = $_POST['paged']??1;
			// Set up arguments for the WooCommerce product query
			$args = [
				'posts_per_page'	=> 24,
				'orderby'			=> 'ID',
				'order'				=> 'DESC',
				'paged'				=> $paged,
				'post_status'		=> 'any',
				'post_type'			=> $_POST['type'],
			];
			if($post_in) {$args['post__in'] = $post_in;}
			// Get products using the query arguments
			$posts = new WP_Query($args);
			// Create an array to store the exported data
			$export_data = [];
			// Loop through each attachment
			if ($posts->have_posts()) {
				while ($posts->have_posts()) {
					$posts->the_post();
		
					// Get the attachment ID
					$post_id = get_the_ID();
		
					// Get the attachment metadata
					$post_meta = get_post_meta($post_id);
		
					// Get the attachment taxonomy terms
					$post_taxs = get_post_taxonomies($post_id);
					$post_terms = wp_get_post_terms($post_id, 'post_cat');
		
					// Get the attachment comments
					$post_comments = get_comments(array(
						'post_id' => $post_id,
						'status' => 'approve',
					));
		
					// Create an array to store the attachment data
					$post_data = array(
						'ID' => $post_id,
						'Title' => get_the_title(),
						'Status' => get_post_status(),
						'Content' => get_the_content(),
						'Created' => get_the_date(),
						'Created' => get_last_updated(),
					);
					if ($post_taxs && !is_wp_error($post_taxs)) {
						$post_data['taxonomies'] = $post_taxs;
					}
					if ($post_terms && !is_wp_error($post_terms)) {
						$post_data['Terms'] = $post_terms;
					}
					if ($post_comments && !is_wp_error($post_comments)) {
						$post_data['Comments'] = $post_comments;
					}
					if ($post_meta && !is_wp_error($post_meta)) {
						$post_data['Metadata'] = $post_meta;
					}
		
					// Add the attachment data to the export array
					$export_data[] = $post_data;
				}
		
				// Restore the global post data
				wp_reset_postdata();
	
				// Added to the output query
				$json['exports'] = $export_data;

				$json['pagination'] = [
					'current'		=> $paged,
					'totalposts'	=> $posts->found_posts,
					'total'			=> $posts->max_num_pages,
				];
				
				// Output the export data
				wp_send_json_success($json);
			}
		}
		wp_send_json_error($json);
	}
	
}
