<?php
/**
 * Block Patterns
 *
 * @package TeddyBearCustomizeAddon
 */

namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;

use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;

class Email {
	use Singleton;
	private $cusrev_reminder_sending = false;
	protected function __construct() {
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		// add_action('woocommerce_order_status_completed', [$this, 'woocommerce_order_status_completed'], 10, 2);
		// add_filter('woocommerce_email_classes', [$this, 'woocommerce_email_classes'], 10, 1);

		add_action('woocommerce_email_order_details', [$this, 'woocommerce_email_order_meta'], 10, 4);
		
		// add_action('admin_init', function() {
		// 	// echo $this->get_review_link_from_order_id(2888);
		// 	// wp_die('Hold on 2mins. Under constructions. Ref. Remal Mahmud (mahmudremal@yahoo.com)');
		// }, 10, 0);

		add_filter('cr_local_review_reminder_template', [$this, 'cr_local_review_reminder_template'], 10, 2);
		add_filter('pre_wp_mail', [$this, 'pre_wp_mail'], 10, 2);
	}
	public function cr_local_review_reminder_template($template, $attr) {
		if (isset($attr['review_form']) && $this->cusrev_reminder_sending === null) {
			$this->cusrev_reminder_sending = $attr['review_form'];
		}
		return $template;
	}
	public function pre_wp_mail($nulled, $attr) {
		/**
		 * Prevent Email by sending bool.
		 * Proceed event by sending NULL
		 */
		if ($this->cusrev_reminder_sending && !empty($this->cusrev_reminder_sending)) {
			return false;
		}
		return $nulled;
	}
	public function woocommerce_order_status_completed($order_id, $order) {
		if(!in_array($order->get_status(), ['completed'])) {return;}
		do_action('woocommerce_order_action_send_birth_certificates', $order);
	}
	public function woocommerce_email_classes($email_classes) {
		// $emails = wc()->mailer()->emails;
		$email_classes['WC_Email_New_Order']->template_base = TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/woocommerce/emails/';
		// print_r(
		// 	$email_classes['WC_Email_New_Order']
		// );
		return $email_classes;
	}
	public function get_review_link_from_order_id($order_id) {
		if (!class_exists('Ivole_Email')) {return false;}
		$this->cusrev_reminder_sending = null;
		$e = new \Ivole_Email($order_id);
		$result = $e->trigger2($order_id, null, false);
		if ($this->cusrev_reminder_sending && !empty($this->cusrev_reminder_sending)) {
			$link = $this->cusrev_reminder_sending;
			$this->cusrev_reminder_sending = false;
			return $link;
		}
		return false;
	}
	/**
	 * Add a customer reciew section after order summery
	 */
	public function woocommerce_email_order_meta($order, $sent_to_admin, $plain_text, $email) {
		// return; // paused this because client said not to attach this button on mail.
		global $wpdb;
		if ((bool) $sent_to_admin) {return;}
		if(!in_array($order->get_status(), ['completed'])) {return;}
		
		$review_link = $this->get_review_link_from_order_id($order->get_id());
		
		if ($review_link && !empty($review_link)) {
			include TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/email/section-cusrev.php';
		}
	}
	
}
