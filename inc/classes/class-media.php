<?php
/**
 * Block Patterns
 *
 * @package TeddyBearCustomizeAddon
 */

namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;

use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;

class Media {
	use Singleton;
	protected function __construct() {
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		add_action('wp_ajax_futurewordpress/project/teddybearpopupaddon/action/import_images_from_data', [$this, 'import_images_from_data'], 10, 0);
		add_action('wp_ajax_futurewordpress/project/teddybearpopupaddon/action/import_image_from_blob', [$this, 'import_image_from_blob'], 10, 0);
	}
	public function import_images_from_data() {
		$data = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', stripslashes(html_entity_decode(isset($_POST['dataset'])?$_POST['dataset']:'{}'))), true);
		$imported_images = [];
		foreach ($data as $i => $item) {
			$image_url = $item['image'];
			$thumb_url = $item['thumb'];
			if (!empty($image_url)) {
				// Import the main image
				$image_id = media_sideload_image($image_url, 0);
				if (!is_wp_error($image_id)) {
					$imported_images[$i]['image'] = $image_id;
				}
			}
			if (!empty($thumb_url)) {
				// Import the thumbnail image
				$thumb_id = media_sideload_image($thumb_url, 0);
				if (!is_wp_error($thumb_id)) {
					$imported_images[$i]['thumb'] = $thumb_id;
				}
			}
		}
		wp_send_json_success($imported_images);
		// return $imported_images;
	}
	public function import_image_from_blob() {
		$blob_data = '';
		$file_name = $_POST['imageName'];
		wp_send_json_success([$_POST, $_FILES]);
		$upload_dir = wp_upload_dir();
		$file_path = $upload_dir['path'] . '/' . $file_name;
		$file_url = $upload_dir['url'] . '/' . $file_name;
	
		// Check if the file already exists in the media library
		$existing_attachment_id = attachment_url_to_postid($file_url);
	
		if ($existing_attachment_id !== 0) {
			// The file already exists, return the attachment ID
			return $existing_attachment_id;
		}
	
		// Save the blob data to the uploads directory
		if (file_put_contents($file_path, $blob_data) !== false) {
			$attachment = array(
				'guid'           => $file_url,
				'post_mime_type' => mime_content_type($file_path),
				'post_title'     => preg_replace('/\.[^.]+$/', '', $file_name),
				'post_content'   => '',
				'post_status'    => 'inherit'
			);
	
			// Insert the image attachment into the media library
			$attachment_id = wp_insert_attachment($attachment, $file_path);
	
			if (!is_wp_error($attachment_id)) {
				require_once ABSPATH . 'wp-admin/includes/image.php';
	
				// Generate image metadata and create various image sizes
				$attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
				wp_update_attachment_metadata($attachment_id, $attachment_data);
	
				// Return the attachment ID
				return $attachment_id;
			}
		}
	
		// Failed to import the image
		return false;
	}
	
	
}
