<?php
/**
 * LoadmorePosts
 *
 * @package TeddyBearCustomizeAddon
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;

class Icon_Library {
	private $post_type = 'dubido_icons';
	private $storePath = false;
	private $storeURI = false;
	use Singleton;
	protected function __construct() {
		// load class.
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		add_action('init', [$this, 'teddy_icon_cpt'], 10, 0);
		add_filter('teddy/library/icon', [$this, 'teddy_library_icon'], 0, 2);
		add_action('wp_ajax_teddy/library/icons', [$this, 'teddy_library_icons'], 10, 0);
		add_action('wp_ajax_nopriv_teddy/library/icons', [$this, 'teddy_library_icons'], 10, 0);
		
		add_action('wp_ajax_teddy/library/icon/add', [$this, 'teddy_library_icon_add'], 10, 0);
		add_action('wp_ajax_teddy/library/icon/remove', [$this, 'teddy_library_icon_remove'], 10, 0);
	}
	public function teddy_icon_cpt() {
		$args = [
			"label" => __("Dubido Icons", "hidden_cpt"),
			"labels" => [
				"name" => __("Dubido Icons", "hidden_cpt"),
				"singular_name" => __("Dubido Icon", "hidden_cpt"),
				"menu_name" => __("Dubido Icons", "hidden_cpt"),
			],
			"public" => true,
			"publicly_queryable" => true,
			"show_ui" => true,
			"show_in_rest" => true,
			"rest_controller_class" => "WP_REST_Posts_Controller",
			"has_archive" => true,
			"show_in_menu" => true, 
			"show_in_nav_menus" => true,
			"delete_with_user" => true,
			"exclude_from_search" => true,
			"capability_type" => "post",
			"map_meta_cap" => true,
			"hierarchical" => true,
			"rewrite" => true,
			"query_var" => true,
			"supports" => ["title", "thumbnail"],
		];
		register_post_type($this->post_type, $args);
		// 
		// 
		if (!$this->storePath) {
			$upload_dir = wp_upload_dir();$custom_dir = 'custom_popup';
			$target_dir = $upload_dir['basedir'].'/'.$custom_dir.'/';
			if (!file_exists($target_dir)) {mkdir($target_dir, 0755, true);}
			$this->storeURI = $upload_dir['baseurl'].'/'.$custom_dir.'/';
			$this->storePath = $target_dir;
		}
	}
	public function teddy_library_icon($icon, $icon_id) {
		$info = (object) wp_parse_args((array) get_post_meta($icon_id, '__icon_info', true), [
			'name' => false
		]);
		// return get_the_title($icon_id);
		if ($info->name) {
			$iconPath = $this->storePath . get_the_title($icon_id);
			if ($iconPath && file_exists($iconPath) && !is_dir($iconPath)) {
				// $icon = file_get_contents($iconPath);
				$icon = $this->storeURI . get_the_title($icon_id);
			}
		}
		return $icon;
	}
	public function teddy_library_icons() {
		do_action('teddybear/project/nonce/check', $_POST['_nonce']);
		$paged = $_POST['paged']??1;
		$per_page = $_POST['per_page']??24;
		$_includings = explode(',', $_POST['includings']??'');$includings = [];
		foreach ($_includings as $id) {
			if (!empty($id) && (int) $id > 0) {
				$includings[] = (int) $id;
			}
		}
		// 
		// get_posts
		$args = [
            'orderby' => 'ID',
            'order' => 'DESC',
			'fields' => 'ids',
			'post_status' => 'any',
			'paged'	=> (int) $paged,
			'post_type' => $this->post_type,
            'posts_per_page' => (int) $per_page,
		];
		if (!empty($includings)) {
			// $args['orderby'] = 'post__in';
			// $args['post__in'] = $includings;
			$args['dubido_prepend_must'] = $includings;
			// $args['ignore_sticky_posts'] = true;
			add_filter('posts_clauses', [$this, 'prioritize_posts'], 10, 2);
		}
		$icons = new \WP_Query($args);
		if (!empty($includings)) {
			remove_filter('posts_clauses', [$this, 'prioritize_posts'], 10, 2);
		}
		if ($icons->have_posts()) {
			$resIcons = [];
			while ( $icons->have_posts() ) {
				$icons->the_post();
				$uploaded_file = get_the_title();
                $resIcons[] = [
					'icon_id'	=> get_the_ID(),
					'name'		=> $uploaded_file,
					'url'		=> $this->storeURI . $uploaded_file,
					'path'		=> $this->storePath . $uploaded_file,
					'_meta'		=> get_post_meta(get_the_ID(), '__icon_info', true),
					// 'content'	=> apply_filters('teddy/library/icon', '', get_the_ID()),
				];
            }
			wp_reset_postdata();
			wp_send_json([
				'success'		=> true,
				'data'			=> $resIcons,
				'pagination'	=> [
					'perpage'	=> $per_page,
					'total'		=> $icons->found_posts,
					'pages'		=> $icons->max_num_pages,
					'current'	=> max(1, (int) $paged),
					'comments'	=> $icons->comment_count,
				]
			]);
		} else {
			wp_send_json_error(__('No icons found', 'teddybearsprompts'));
		}
	}
	public function prioritize_posts($clauses, $wp_query) {
		global $wpdb;
		if ($wp_query->get('dubido_prepend_must')) {
			$dubido_prepend_must = implode(',', array_map('absint', $wp_query->get('dubido_prepend_must')));
			$clauses['orderby'] = "FIELD({$wpdb->posts}.ID, {$dubido_prepend_must}) DESC, {$wpdb->posts}.post_date DESC";
		}
		return $clauses;
	}
	public function teddy_library_icon_add() {
		do_action('teddybear/project/nonce/check', $_POST['_nonce']);
		$icon_files = (array) $_FILES['_icon_file']??false;
		$icon_infos = [];
		foreach ($_POST['icon_info'] as $icon_info) {
			// $icon_infos[] = (array) json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', stripslashes(html_entity_decode($icon_info))), true);
			$icon_infos[] = (array) json_decode(stripslashes(html_entity_decode($icon_info)), true);
		}
		if ($icon_files) {
			$icon_files_unsort = $icon_files;$icon_files = [];
			foreach ($icon_files_unsort as $key => $row) {
				foreach ($row as $index => $item) {
					$icon_files[$index] = isset($icon_files[$index])?$icon_files[$index]:[];
					$icon_files[$index][$key] = $item;
				}
			}
			$iconUploaded = [];
			foreach ($icon_files as $icon_file) {
				try {
					$iconInfo = [];
					foreach ($icon_infos as $icon_info) {
						if ($icon_info['name'] == $icon_file['name'] && $icon_info['type'] == $icon_file['type']) {
							$iconInfo = $icon_info;
						}
					}
					$uploaded_file = $this->custom_upload_icon($icon_file);
					if ($uploaded_file) {
						$icon_id = wp_insert_post([
							'post_title' => pathinfo($uploaded_file, PATHINFO_BASENAME),
							'post_type' => $this->post_type,
							'post_status' => 'publish',
							'meta_input' => [
								'__icon_info' => $iconInfo
							]
						]);
						if ($icon_id && !is_wp_error($icon_id)) {
							$iconUploaded[] = [
								'icon_id'		=> $icon_id,
								'name'			=> $uploaded_file,
								'url'			=> $this->storeURI . $uploaded_file,
								'path'			=> $this->storePath . $uploaded_file
							];
						}
					}
				} catch (\Exception $th) {
					wp_send_json_error($th);
				}
			}
			if (count($iconUploaded)) {
				wp_send_json_success($iconUploaded);
			}
		}
		wp_send_json_error(__('Something went wrong!', 'teddybearsprompts'));
	}
	public function teddy_library_icon_remove() {
		do_action('teddybear/project/nonce/check', $_POST['_nonce']);
		if (isset($_POST['icon_id'])) {
			$icon_id = (int) $_POST['icon_id'];
			$icon_path = $this->storePath . get_the_title($icon_id);
			if ($icon_path && file_exists($icon_path) && !is_dir($icon_path)) {
				$is_removed = wp_delete_post($icon_id, true);
				if ($is_removed && !is_wp_error($is_removed)) {
					unlink($icon_path);
					wp_send_json_success();
				}
			}
		}
		wp_send_json_error();
	}
	public function custom_upload_icon($file) {
		$file_name = uniqid(pathinfo($file['name'], PATHINFO_EXTENSION)) . '-' . $file['name'];$file_tmp = $file['tmp_name'];$file_type = $file['type'];
		$allowed_regex = '/^(text|image|html)\/(.*?)/i';
		if (!preg_match($allowed_regex, $file_type)) {
			throw new \Exception(__('Error: Only image files are allowed.', 'teddybearsprompts'));
		}
		$max_file_size = 4 * 1024 * 1024;
		if ($file['size'] > $max_file_size) {
			throw new \Exception(__('Error: File size exceeds the maximum limit of 4 MB.', 'teddybearsprompts'));
		}
		$target_file = $this->storePath . $file_name;
		if (!move_uploaded_file($file_tmp, $target_file)) {
			throw new \Exception(__('Error uploading file.', 'teddybearsprompts'));
		}
		return $file_name;
	}
}
