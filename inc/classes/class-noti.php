<?php
/**
 * LoadmorePosts
 *
 * @package TeddyBearCustomizeAddon
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
class Noti {
	use Singleton;
	private $notify_key;
	protected function __construct() {
		// load class.
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		$this->notify_key = 'teddybear/project/keys/notify';
		
		add_filter('teddybear/project/add/notify', [$this, 'add_notify'], 10, 3);
		add_filter('teddybear/project/get/notify', [$this, 'get_notify'], 10, 2);
		add_filter('teddybear/project/del/notify', [$this, 'del_notify'], 10, 1);

		add_filter('teddybear/project/assets/notifications', [$this, 'assets_notifications'], 10, 2);
	}
	/**
	 * Add notification using key
	 * @return bool
	 */
	public function add_notify($key, $data, $time) {
		$time = 60 * 60 * 12;
		return set_transient($key, $data, $time);
	}
	/**
	 * Get notification using key
	 * @return mixed
	 * @return bool
	 */
	public function get_notify($current, $key) {
		return get_transient($key);
	}
	/**
	 * Delete notifications using key.
	 * @return bool
	 */
	public function del_notify($key) {
		return delete_transient($key);
	}
	/**
	 * Add a type of notification key.
	 * @return bool
	 */
	public function add_types($key) {
		$option = get_option($this->notify_key, []);
		if (!in_array($key, (array) $option)) {
			$option[] = $key;
			update_option($this->notify_key, $option, true);
			return true;
		}
		return false;
	}
	/**
	 * Get all types of notifications.
	 * @return array will all notifications keys.
	 */
	public function get_types() {
		$option = get_option($this->notify_key, []);
		return (array) $option;
	}

	public function assets_notifications($notifications, $args) {
		foreach ($this->get_types() as $index => $key) {
			$notifications[] = apply_filters('teddybear/project/get/notify', [], $key);
		}
		return $notifications;
	}
}
