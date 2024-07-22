<?php
/**
 * Block Patterns
 *
 * @package TeddyBearCustomizeAddon
 */

namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;

use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;

class Ajax {
	use Singleton;
	protected function __construct() {
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		add_action('wp_ajax_teddybear/project/teddybearpopupaddon/action/get_autocomplete', [$this, 'get_autocomplete'], 10, 0);
		add_action('wp_ajax_nopriv_teddybear/project/teddybearpopupaddon/action/get_autocomplete', [$this, 'get_autocomplete'], 10, 0);

		add_action('wp_ajax_teddybear/project/ajax/search/product', [$this, 'search_product'], 10, 0);
		add_action('wp_ajax_nopriv_teddybear/project/ajax/search/product', [$this, 'search_product'], 10, 0);
		add_action('wp_ajax_teddybear/project/ajax/submit/popup', [$this, 'submit_popup'], 10, 0);
		add_action('wp_ajax_nopriv_teddybear/project/ajax/submit/popup', [$this, 'submit_popup'], 10, 0);

		add_action('wp_ajax_teddybear/project/ajax/edit/product', [$this, 'edit_product'], 10, 0);
		add_action('wp_ajax_teddybear/project/ajax/save/product', [$this, 'save_product'], 10, 0);

		add_action('wp_ajax_teddybear/project/ajax/search/popup', [$this, 'search_popup'], 10, 0);
		add_action('wp_ajax_nopriv_teddybear/project/ajax/search/popup', [$this, 'search_popup'], 10, 0);
		
		add_action('wp_ajax_teddybear/project/ajax/update/orderitem', [$this, 'update_orderitem'], 10, 0);

		add_action('wp_ajax_nopriv_teddybear/project/ajax/suggested/names', [$this, 'suggested_names'], 10, 0);
		add_action('wp_ajax_teddybear/project/ajax/suggested/names', [$this, 'suggested_names'], 10, 0);

		add_action('wp_ajax_nopriv_teddybear/project/ajax/checkout/fetch', [$this, 'checkout_fetch'], 10, 0);
		add_action('wp_ajax_teddybear/project/ajax/checkout/fetch', [$this, 'checkout_fetch'], 10, 0);

		// add_action('init', [$this, 'remove_customizations'], 10, 0);
	}
	public function get_autocomplete() {
		do_action('teddybear/project/nonce/check', $_GET['_nonce']);
		global $wpdb;
		switch ($_GET['term']) {
			case 'product':
				$_GET['query'] = '%'.$_GET['query'].'%';
				$res = $wpdb->get_results(
					"SELECT ttx.term_id, trm.name FROM {$wpdb->prefix}term_taxonomy ttx left join {$wpdb->prefix}terms trm on trm.term_id=ttx.term_id where ttx.taxonomy='listing_product' and trm.name like '%a%' order by ttx.term_id desc limit 0, 50;"
				);
				break;
			case 'location':
				$_GET['query'] = '%'.$_GET['query'].'%';
				$res = $wpdb->get_results(
					"SELECT post.post_title, pstm.meta_value as name FROM {$wpdb->prefix}posts post left join {$wpdb->prefix}postmeta pstm on pstm.post_id=post.ID WHERE post.post_type='listing' and pstm.meta_key='_friendly_address' and pstm.meta_value like '{$_GET['query']}' order by post.post_title desc limit 0, 50;"
				);
				break;
			default:
				$res = [];
				break;
		}
		
		// $res = [];for ($i=0; $i < 10; $i++) {$res[] = ['name'=>'Result '.$i,'value'=>'result_'.$i];}
		wp_send_json_success( $res, 200 );
	}
	public function search_product() {
		do_action('teddybear/project/nonce/check', $_POST['_nonce']);
		// $json = json_decode(file_get_contents(TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/sample.product.json'));
		// wp_send_json($json);

		global $wpdb;global  $woocommerce;global $teddy_Product;global $teddy_Plushies;
		// check_ajax_referer('teddybear/project/teddybearpopupaddon/verify/nonce', '_nonce', true);
		$dataset = $request = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', stripslashes(html_entity_decode(isset($_POST['dataset'])?$_POST['dataset']:'{}'))), true);
		
		$_product = wc_get_product($request['product_id']);
		$productData = ($_product && !is_wp_error($_product))?[
			'id'		=> $_product->get_id(),
			'type'		=> $_product->get_type(),
			// 'title'		=> get_the_title($_product->get_id()),
			'name'		=> $_product->get_name(),
			'slug'		=> $_product->get_slug(),
			'link'		=> get_permalink($_product->get_id()),
			'price'		=> $_product->get_price(),
			'currency'	=> get_woocommerce_currency_symbol(),
			'priceHtml'	=> $_product->get_price_html()
		]:[];

		$json = [
			'hooks' => ['gotproductpopupresult'],
			'header' => [
				'product_photo' => 'empty',
			],
			'user' => [
				'sellerLoggedIn' => is_user_logged_in(),
				'telephone' => null,
				'userLoggedIn' => is_user_logged_in(),
				'userName' => is_user_logged_in()?wp_get_current_user()->display_name:null
			],
			'country' => false,
			'product' => [
				'id'		=> $productData['id'],
				'name'		=> $productData['name'],
				'link'		=> $productData['link'],
				'slug'		=> $productData['slug'],
				'type'		=> $productData['type'],
				'price'		=> $productData['price'],
				'currency'	=> $productData['currency'],
				'priceHtml'	=> $productData['priceHtml'],
				'positions' => [
					'standing' => apply_filters('teddybear/project/system/getoption', 'standard-standingdoll', ''),
					'sitting' => apply_filters('teddybear/project/system/getoption', 'standard-sittingdoll', ''),
				],
				'is_parent' => false,
				'toast'		=> false, // '<strong>' . count($requested) . '</strong> people requested this service in the last 10 minutes!',
				'thumbnail'	=> ['1x' => '', '2x' => ''],
				'custom_data'	=> get_post_meta($productData['id'], '_teddy_custom_data', true),
				'custom_fields' => $teddy_Product->get_frontend_product_json($dataset['product_id'])
			],
		];

		if (!$json['product']['custom_fields'] || count($json['product']['custom_fields']) <= 0) {
			$json['product']['empty_image'] = TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_URI . '/icons/Team meeting_Monochromatic.svg';
		}

		wp_send_json_success( $json, 200 );
	}
	public function submit_popup() {
		do_action('teddybear/project/nonce/check', $_POST['_nonce']);
		// check_ajax_referer('teddybear/project/teddybearpopupaddon/verify/nonce', '_nonce', true);
		$request = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', stripslashes(html_entity_decode($_POST['dataset']))), true);
		$json = [
			'hooks' => ['popup_submitting_done'],
			'message' => __( 'Popup submitted successfully. Hold on unil you\'re redirecting to searh results.', 'teddybearsprompts' )
		];
		
		if(isset($request['product']) && !empty($request['product'])) {
			$request['product'] = (int) $request['product'];
			$term_link = get_term_link($request['product'], 'listing_product');
			if(!$term_link || is_wp_error($term_link)) {$term_link = false;}
			$json['redirectedTo'] = $term_link;
		}
		if(isset($request['field']["9002"]) && ! is_user_logged_in()) {
			$user_email = $request['field']["9002"];
			$user_name = $request['field']["9003"];
			$user_pass = $request['field']["9004"];
			$user = get_user_by_email($user_email);
			if($user) {
				$user_id = $user->ID;
				wp_set_current_user($user_id, $user->user_login);
				wp_set_auth_cookie($user_id);
				do_action('wp_login', $user->user_login, $user);
			} else {
				$user_id = username_exists($user_name);
				if(!$user_id && false == email_exists($user_email)) {
					$user_id = wp_create_user($user_name, $user_pass, $user_email );
					if(!is_wp_error($user_id)) {
						$user = get_user_by('id', $user_id);
						wp_set_current_user($user_id, $user->user_login);
						wp_set_auth_cookie($user_id);
						do_action('wp_login', $user->user_login, $user);
					}
					
				} else {
					$random_password = __( 'User already exists.  Password inherited.', 'textdomain' );
				}
				
			}
		}
		
		wp_send_json_success( $json, 200 );
	}
	public function search_popup() {
		do_action('teddybear/project/nonce/check', $_POST['_nonce']);
		global $wpdb;
		$json = ['hooks' => ['popup_searching_done']];
		// check_ajax_referer('teddybear/project/teddybearpopupaddon/verify/nonce', '_nonce', true);
		$request = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', stripslashes(html_entity_decode($_POST['formdata']))), true);
		// 
		wp_send_json_error($json);
	}
	public function save_product() {
		do_action('teddybear/project/nonce/check', $_POST['_nonce']);
		$result = [];$result['hooks'] = ['product_updated'];$result['message'] = __( 'Popup updated Successfully', 'teddybearsprompts' );
		$request = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', stripslashes(html_entity_decode($_POST['dataset']))), true);
		$result['json'] = $request;
		$product_id = $_POST['product_id'];
		update_post_meta($product_id, '_product_custom_popup', $request);
		wp_send_json_success($result, 200);
	}
	public function edit_product() {
		do_action('teddybear/project/nonce/check', $_POST['_nonce']);
		global $teddy_Product;$json = [];global $teddy_Plushies;// $teddy_Product->
		$json['product'] = (array) get_post_meta($_POST['product_id'], '_product_custom_popup', true);
		$json['hooks'] = ['gotproductpopupresult'];
		$json['product'] = ($json['product'] && !empty($json['product']))?(array)$json['product']:[];
		foreach($json['product'] as $_posi => $_position) {
			foreach($json['product'][$_posi] as $i => $_prod) {
				if(isset($_prod['headerbg']) && !empty(trim($_prod['headerbg']))) {
					$json['product'][$_posi][$i]['headerbgurl'] = wp_get_attachment_url($_prod['headerbg']);
				}
				if(isset($_prod['stepicon_id']) && !empty(trim($_prod['stepicon_id']))) {
					$json['product'][$_posi][$i]['stepicon'] = apply_filters('teddy/library/icon', '', (int) $_prod['stepicon_id']);
				}
				if(isset($_prod['product']) && !empty(trim($_prod['product']))) {
					$_product = wc_get_product($_prod['product']);
					if($_product && !is_wp_error($_product)) {
						$json['product'][$_posi][$i]['product_title'] = $_product->get_name();
						// isset($_prod['cost']) && !$_prod['cost'] || empty($_prod['cost'])
						if(true) {
							$json['product'][$_posi][$i]['cost'] = $_product->get_price();
						}
					}
				}
				if(isset($_prod['options'])) {
					$_prod['options'] = (!empty($_prod['options']))?(array)$_prod['options']:[];
					foreach($_prod['options'] as $j => $option) {
						if(isset($option['image']) && !empty($option['image'])) {
							$json['product'][$_posi][$i]['options'][$j]['imageUrl'] = wp_get_attachment_url($option['image']);
						}
					}
				}
				if(isset($_prod['groups'])) {
					foreach($_prod['groups'] as $k => $group) {
						if(isset($group['options'])) {
							foreach($group['options'] as $l => $option) {
								if(isset($option['image']) && !empty($option['image'])) {
									$json['product'][$_posi][$i]['groups'][$k]['options'][$l]['imageUrl'] = wp_get_attachment_url($option['image']);
								}
								if(isset($option['thumb']) && !empty($option['thumb'])) {
									$json['product'][$_posi][$i]['groups'][$k]['options'][$l]['thumbUrl'] = wp_get_attachment_url($option['thumb']);
								}
							}
						}
					}
				}
			}
		}
		$json['info'] = [
			'prod_title' => get_the_title($_POST['product_id'])
		];
		$args = [
			'post_type'			=> 'product',
			'post_status'		=> 'publish',
			'posts_per_page'	=> -1,
			'meta_query' => [
				'relation'		=> 'OR'
			]
		];
		foreach($teddy_Plushies->get_accessories_terms() as $_key => $_text) {
			$args['meta_query'][] = [
				'key' => $_key,
				'value' => 'on',
				'compare' => '='
			];
		}
		$json['accessories'] = [];
		$_posts = get_posts($args);
		foreach($_posts as $_post) {
			$json['accessories'][] = [
				'ID'		=> $_post->ID,
				'title'		=> get_the_title($_post),
				'thumbnail'	=> get_the_post_thumbnail_url($_post)
			];
		}
		$json['categories'] = [];
		$args = [
			'taxonomy'     => 'product_cat',
			'orderby'      => 'name',
			'show_count'   => true,
			'pad_counts'   => true,
			'hierarchical' => false,
			'hide_empty'   => false,
			'title_li'     => ''
		];
		$_categories = get_categories($args);
		foreach($_categories as $_cat) {
			$json['categories'][] = [
				'ID'		=> $_cat->term_id,
				'title'		=> $_cat->name,
				'thumbnail'	=> false,
				'count'		=> $_cat->count,
				'link'		=> get_term_link($_cat->slug, 'product_cat'),
			];
		}
		wp_send_json_success($json, 200);
	}
	public function merge_customfields($fields) {
		$fieldID = 9000;
		if(!$fields || $fields == "") {return $fields;}
		$fields = (array) $fields;
		$fields[] = [
			'fieldID' => $fieldID,
			'type' => 'text',
			'headerbg' => '',
			'heading' => 'Where do you need the {{product.name}}?',
			'subtitle' => 'The postcode or town for the address where you want the {{product.name}}.',
			'placeholder' => 'Enter your postcode or town',
			'steptitle' => '',
			'headerbgurl' => false,
		];
		$fieldID++;
		$fields[] = [
			'fieldID' => $fieldID,
			'type' => 'confirm',
			'headerbg' => '',
			'steptitle' => '',
			'heading' => 'Great - we\'ve got pros ready and available.',
			'icon'	=> '<span class="fa fa-check"></span>',
			'headerbgurl' => false,
		];
		if(!is_user_logged_in()) {
			$fieldID++;
			$fields[] = [
				'fieldID' => $fieldID,
				'type' => 'text',
				'headerbg' => '',
				'heading' => 'Which email address would you like quotes sent to?',
				'subtitle' => 'Give here your email address that will help us to create an account for you or auto login for an existing account.',
				'placeholder' => 'Email-address',
				'steptitle' => '',
				'headerbgurl' => false,
			];
			$fieldID++;
			$fields[] = [
				'fieldID' => $fieldID,
				'type' => 'text',
				'headerbg' => '',
				'heading' => 'Your full name?',
				'subtitle' => '',
				'steptitle' => '',
				'label' => 'Please tell us your name:',
				'placeholder' => 'Full name',
				'footer'	=> sprintf(__('By continuing, you confirm your agreement to our %sTerms & Conditions%s', 'teddybearsprompts'), '<a href="'.esc_url(get_privacy_policy_url()).'" target="_blank">', '</a>'),
				'headerbgurl' => false,
				'extra_fields' => [
					[
						'fieldID' => ($fieldID+1),
						'type' => 'checkbox',
						'subtitle' => '',
						'headerbgurl' => false,
						'options'	=> [
							['label' => 'I am happy to receive occasional marketing emails.', 'input' => false, 'next' => false]
						]
					]
				]
			];
			$fieldID++;$fieldID++;
			$fields[] = [
				'fieldID' => $fieldID,
				'type' => 'password',
				'headerbg' => '',
				'heading' => 'Give here the password',
				'steptitle' => '',
				'subtitle' => 'Password help to keep your account secure for anonymouse attack and third party tracking.',
				'placeholder' => '%^8;fd&!87"af$',
				'headerbgurl' => false,
			];
			$fieldID++;
			$fields[] = [
				'fieldID' => $fieldID,
				'type' => 'text',
				'headerbg' => '',
				'heading' => 'What is your phone number?',
				'subtitle' => 'Professionals will be able to contact you directly to find out more about your request.',
				'placeholder' => 'Phone number. Eg. +880 1814-118 328',
				'steptitle' => '',
				'headerbgurl' => false,
			];
		} else {
			$fieldID = ($fieldID + 4);
		}
		return $fields;
	}

	public function update_orderitem() {
		do_action('teddybear/project/nonce/check', $_POST['_nonce']);
		global $teddy_Product;global $teddy_Meta;global $wpdb;
		$json = ['hooks' => ['order_item_update_failed'], 'message' => __('Something went wrong. Please review your request again.', 'teddybearsprompts')];
		// if(!isset($_POST['order_id']) || empty($_POST['order_id']) || !isset($_POST['item_id']) || empty($_POST['item_id']) || !isset($_POST['teddyname']) || empty($_POST['teddyname'])) {
		// 	wp_send_json_error($json);
		// }

		$order_id = $_POST['order_id']??false;
		$order_item_id = $_POST['item_id']??false;
		$askedteddyinfo = explode(',', $_POST['askedteddyinfo']??'');
		$failed = true; // $json['post'] = $_POST;
		$order = wc_get_order($order_id);
		foreach($order->get_items() as $item_id => $order_item) {
			if(!in_array($item_id, $askedteddyinfo)) {continue;}
			$product_id = $order_item->get_product_id();
			// $custom_popup = $teddy_Product->get_order_pops_meta($order, $order_item, $product_id);
			$_dataset = $teddy_Meta->get_order_item_dataset($order_item, $order);
			$_infoIndex = array_search('info', array_column($_dataset, 'type'));
			if (isset($_dataset[$_infoIndex]) && isset($_dataset[$_infoIndex]['type']) && $_dataset[$_infoIndex]['type'] == 'info') {
				$_dataset[$_infoIndex]['infos'] = $_dataset[$_infoIndex]['infos']??[];
				$_infos = $_POST['item-' . $item_id]??[];
				if (empty($_infos)) {continue;}
				$_dataset[$_infoIndex]['infos'] = [
					...$_dataset[$_infoIndex]['infos'],
					...$_infos
				];
				$wpdb->update(
					"{$wpdb->prefix}woocommerce_order_itemmeta",
					[
						'meta_value'		=> maybe_serialize($_dataset)
					],
					[
						'meta_key'		=> 'custom_dataset',
						'order_item_id'	=> $item_id
					],
					['%s'],
					['%s', '%d']
				);
				$json['message'] = __('Successfully Updated your teddy bear name.', 'teddybearsprompts');
				$json['hooks'] = ['order_item_update_success'];
				$failed = false;
			}
		}
		if(!$failed) {
			wp_send_json_success($json, 200);
		} else {
			wp_send_json_error($json);
		}
	}
	public function suggested_names($return_obj = false) {
		if (!$return_obj) {do_action('teddybear/project/nonce/check', $_POST['_nonce']);}
		$args = ['names' => [], 'hooks' => ['namesuggestionloaded']];
		$filteredKeys = array_keys(TEDDY_BEAR_CUSTOMIZE_ADDON_OPTIONS);
		$filteredData = [];
		foreach($filteredKeys as $key) {
			if(strpos($key, 'teddy-name-') !== false) {
				$filteredData[] = TEDDY_BEAR_CUSTOMIZE_ADDON_OPTIONS[$key];
			}
		}
		foreach($filteredData as $i => $name) {
			$args['names'][] = $name;
		}
		
		if (apply_filters('teddybear/project/system/isactive', 'names-randomize')) {
			shuffle($args['names']);
		}
		if (!$return_obj) {wp_send_json_success($args);}
		return $args['names'];
	}

	public function checkout_fetch() {
		do_action('teddybear/project/nonce/check', $_POST['_nonce']);
		$json = ['hooks' => ['checkout-fetch-failed'], 'message' => __('Something went wrong. Please review your request again.', 'teddybearsprompts')];
		if(isset($_POST['_meta_id'])) {
			$json['metas'] = [];$_meta_id = explode(',', $_POST['_meta_id']);

			foreach($_meta_id as $i => $meta_key) {
				$cart_item = WC()->cart->get_cart_item($meta_key);
				if($cart_item && !is_wp_error($cart_item)) {
					$pops_meta = $cart_item['data']->get_meta_data();
					if($pops_meta && !is_wp_error($pops_meta)) {
						$json['metas'][] = $pops_meta;
					}
				}
			}
			
			$json['hooks'] = ['checkout-fetch-success'];
			wp_send_json_success($json);
		}
		wp_send_json_error($json);
	}
	public function remove_customizations() {
		global $wpdb;
		$meta_key = '';
		$table = $wpdb->prefix . 'postmeta';
		$res = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE meta_key=%s LIMIT 50;",
				'_product_custom_popup'
			)
		);
		print_r($res);wp_die();
	}
}
