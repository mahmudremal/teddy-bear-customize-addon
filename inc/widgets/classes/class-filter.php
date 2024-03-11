<?php
/**
 * LoadmorePosts
 *
 * @package TeddyBearCustomizeAddon
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
class Filter {
	use Singleton;
	protected function __construct() {
		// load class.
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		add_action('pre_get_posts', [$this, 'pre_get_posts'], 10, 1);
		// add_filter('query_vars', [$this, 'query_vars'], 10, 1);
		add_filter('woolentor_order_by_opts', [$this, 'woolentor_order_by_opts'], 10, 1);
	}
	/**
	 * WooLander Filter widget implimented on Ultimate addon product grid widget.
	 * Query Variables
	 * wlsort: for Sorting. ASC | DESC
	 */
	public function pre_get_posts($query) {
		if(!is_admin()) {
			if($query->query && $query->get('post_type') == 'product') {
				$request = (object) wp_parse_args($_GET, [
					'q'						=> false,
					'wlsort'				=> false,
					'wlorder_by'			=> false,
					'min_price'				=> false,
					'max_price'				=> false,
					'_price_desc'			=> false,
					'_price_asc'			=> false,
				]);
				// $query->is_search = true;
				
				if($request->wlorder_by) {
					if($request->wlorder_by == '_price_desc') {$request->_price_desc = true;}
					if($request->wlorder_by == '_price_asc') {$request->_price_asc = true;}
				}
				if($request->_price_desc || $request->_price_asc) {$request->wlorder_by = '_price';}
				if($request->_price_desc) {$request->wlsort = 'DESC';}
				if($request->_price_asc) {$request->wlsort = 'ASC';}

				
				if($request->q) {$query->set('s', $request->q);}
				if($request->wlsort) {$query->set('order', $request->wlsort);}
				if($request->wlorder_by) {
					if(in_array($request->wlorder_by, ['_price', 'total_sales', '_wc_average_rating'])) {
                    	$query->set('orderby', 'meta_value_num');
						$query->set('meta_key', $request->wlorder_by);
					} else {
						$query->set('orderby', $request->wlorder_by);
					}
				}

				if($request->min_price && $request->max_price) {
					$meta_query = (array) $query->get('meta_query');
					$meta_query[] = [
						'key'		=> '_price',
						'value'		=> [(float) $request->min_price, (float) $request->max_price],
						'compare'	=> 'BETWEEN',
						'type'		=> 'NUMERIC'
					];
					$query->set('meta_query', $meta_query);
				}

				/**
				 * Avoid outof stock product from displaying.
				 */
				if(true) {
					$meta_query = (array) $query->get('meta_query');
					$meta_query[] = [
						'key' => '_stock_status',
						'value' => 'outofstock',
						'compare' => '!=',
					];
					$query->set('meta_query', $meta_query);
				}
			}
			
			// $zip_code = get_query_var('q', false);
		}
	}
	public function query_vars($queries) {
		$queries[] = 'q';
		return $queries;
	}
	public function woolentor_order_by_opts($options) {
		$excludes = ['comment_count', 'rand', '_wc_average_rating', ];
		foreach($excludes as $i => $exclude) {
			if(isset($options[$exclude])) {
				unset($options[$exclude]);
			}
		}
		/**
		 * Desclring an empty array means I removed previous array items.
		 */
		$options = [];
		$titled = [
			'ID'				=> __('New', 'teddybearsprompts'),
			'total_sales'		=> __('Most popular', 'teddybearsprompts'),
			// '_price'			=> __('Price', 'teddybearsprompts'),
			'_price_desc'		=> __('Price high to low', 'teddybearsprompts'),
			'_price_asc'		=> __('Price low to high', 'teddybearsprompts'),
		];
		foreach($titled as $key => $_title) {
			// if(isset($options[$key])) {$options[$key] = $_title;}
			$options[$key] = $_title;
		}
		return $options;
	}
}
