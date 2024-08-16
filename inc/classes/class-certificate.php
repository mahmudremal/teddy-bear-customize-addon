<?php
/**
 * LoadmorePosts
 *
 * @package TeddyBearCustomizeAddon
 */

namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;

use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
class Certificate {
	private $bgImages;
	use Singleton;
	protected function __construct() {
		$this->bgImages = ['he' => 'hebrew-birth-certificate.png', 'en' => 'english-birth-certificate.png', 'blank' => 'blank-birth-certificate.jpg'];
		$this->setup_hooks();
	}
	public function setup_hooks() {
		add_filter('teddybearpopupaddon_generate_certificate', [$this, 'teddybearpopupaddon_generate_certificate'], 1, 2);
		add_action('teddybearpopupaddon_mail_certificates', [$this, 'teddybearpopupaddon_mail_certificates'], 1, 2);
		add_action('init', [$this, 'teddybearpopupaddon_preview_certificate'], 10, 0);
		/**
		 * Removed autometic birth certificate on order completed on 29 July 2024
		 */
		// add_action('woocommerce_order_status_completed', [$this, 'woocommerce_order_status_completed'], 10, 2);
		add_filter('woocommerce_email_attachments', [$this, 'woocommerce_email_attachments'], 10, 3);

		add_filter('teddy/project/certificate/background', [$this, 'certificate_background'], 10, 2);

		add_action('teddybear/project/certificate/preview', [$this, 'certificate_preview'], 10, 3);
	}
	public function certificate_language() {
		$siteLang = explode('-', get_locale())[0];
		$siteLang = explode('_', $siteLang)[0];
		$siteLang = isset($this->bgImages[$siteLang])?$siteLang:'en';
		return $siteLang;
	}
	public function certificate_background($img_file, $lang = false) {
		global $birthCertificateType;
		$images = $this->bgImages;
		if ($lang == 'nobg') {$lang = 'blank';}
		if ($lang && isset($images[$lang])) {
			$_bg_path = TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_IMG_PATH . '/'. $images[$lang];
			$_bg_path = apply_filters('teddybear/project/slashes/fix', $_bg_path);
			if (file_exists($_bg_path) && !is_dir($_bg_path)) {
				$img_file = $_bg_path;
			}
		} else {
			$siteLang = $this->certificate_language();
			$_bg_path = TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_IMG_PATH . '/'. $images[$siteLang];
			$_bg_path = apply_filters('teddybear/project/slashes/fix', $_bg_path);
			if (file_exists($_bg_path) && !is_dir($_bg_path)) {$img_file = $_bg_path;}
			if ($birthCertificateType && isset($images[$birthCertificateType])) {
				$_bg_path = TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_IMG_PATH . '/'. $images[$birthCertificateType];
				$_bg_path = apply_filters('teddybear/project/slashes/fix', $_bg_path);
				if (file_exists($_bg_path) && !is_dir($_bg_path)) {
					$img_file = $_bg_path;
				}
			}
		}
		return $img_file;
	}
	public function do_certificate($args) {
		require_once(TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/inc/tcpdf/tcpdf.php');
		require_once(TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/inc/widgets/class-tcpdfcertificate.php');
	
		// defined('TEDDY_SITE_LANGUAGE_4_PDF') || define('TEDDY_SITE_LANGUAGE_4_PDF', get_locale()); // get_locale() || get_bloginfo('language')
		$pdf_path = apply_filters('teddybear/project/slashes/fix', TEDDY_BEAR_CUSTOMIZE_ADDON_UPLOAD_DIR . $args->pdf);
		// && filesize($pdf_path) >= 1
		if($args->pdf && !empty($args->pdf) && file_exists($pdf_path) && !is_dir($pdf_path)) {
			if($args->preview) {
				$this->pdf_preview_or_redirect($pdf_path);
			} else {
				return $pdf_path;
			}
		}
		global $certificate_args;$certificate_args = $args;
		$pdf = new CertificatePDF('L', PDF_UNIT, 'A5', true, 'UTF-8', false);
		// Create a new TCPDF instance with A4 page format
		// $pdf = new \TCPDF('P', 'mm', 'A5', true, 'UTF-8', false);
		// if ($args->bg_type == 'nobg') {
		// 	$pdf = new \TCPDF('L', PDF_UNIT, 'A5', true, 'UTF-8', false);
		// } else {
		// 	$pdf = new CertificatePDF('L', PDF_UNIT, 'A5', true, 'UTF-8', false);
		// }
	
		// Set document information
		$pdf->SetCreator('mahmud_remal');
		$pdf->SetAuthor(get_bloginfo('name'));
		$pdf->SetTitle(__('Teddy Birth Certificate', 'teddybearsprompts'));
		$pdf->setSubject(sprintf(
			__('Teddy Birth Certificate issued on %s on %s. Technical assistance by %s.', 'teddybearsprompts'), get_bloginfo('name'), home_url(), 'Remal Mahmud (mahmudremal@yahoo.com)'
		));
		$pdf->setKeywords(
			__('Certificate', 'teddybearsprompts'),
			__('Birth certificate', 'teddybearsprompts')
		);

		// Disable default header and footer
		// $pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		// $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		// set default monospaced font
		// $pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->setDefaultMonospacedFont('dejavusans');
		// set default font subsetting mode
		$pdf->setFontSubsetting(false);
		// set margins
		$pdf->setMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->setHeaderMargin(0);
		$pdf->setFooterMargin(0);
		// remove default footer
		$pdf->setPrintFooter(false);
		// set auto page breaks
		$pdf->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		
		$pdf->SetFont('dejavusans', '', 10);
		
		$pages = ($args->multiple)?$args->pages:[$args];
		// if (method_exists($args, 'bg_type')) {
		// 	foreach ($pages as $pageI => $page) {
		// 		if (!method_exists($page, 'bg_type')) {continue;}
		// 		$pages[$pageI]->bg_type = $args->bg_type;
		// 	}
		// }
		// 
		if ($this->certificate_language() == 'he') {
			global $l;
			$l = ($l && !empty($l))?$l:[];
			$l['a_meta_charset'] = 'UTF-8';
			// $l['a_meta_dir'] = 'rtl';
			$l['a_meta_language'] = 'he';
			$l['w_page'] = __('Page', 'teddybearsprompts');
			$pdf->setLanguageArray($l);
			// 
			foreach ($pages as $index => $page) {
				$this->certificate_writing_style_he($pdf, (object) $page);
			}
		} else {
			global $l;
			$l = ($l && !empty($l))?$l:[];
			$l['a_meta_charset'] = 'UTF-8';
			$l['a_meta_dir'] = 'ltr';
			$l['a_meta_language'] = 'en';
			$l['w_page'] = 'page';
			$pdf->setLanguageArray($l);
			// 
			foreach ($pages as $index => $page) {
				$this->certificate_writing_style_en($pdf, (object) $page);
			}
		}
		// 
		$args->pdf = ($args->pdf && !empty($args->pdf))?$args->pdf:'certificate-'.uniqid().'.pdf';
		$pdf_path = apply_filters('teddybear/project/slashes/fix', TEDDY_BEAR_CUSTOMIZE_ADDON_UPLOAD_DIR . $args->pdf);
		$pdf->Output($pdf_path, 'F');
		if($args->preview) {
			$this->pdf_preview_or_redirect($pdf_path);
		} else {
			return $pdf_path;
		}
		
	}
	public function teddybearpopupaddon_generate_certificate($pdf, $args) {
		// if(!is_admin()) {return false;}
		$args = (object) wp_parse_args($args, [
			'eye'				=> '',
			'brow'				=> '',

			'teddy_name'		=> '',
			'teddy_birth'		=> '',
			'teddy_reciever'	=> '',
			'teddy_sender'		=> '',
			'id_num'			=> '',
			'birth'				=> '',

			'weight'			=> '',
			'height'			=> '',

			'pdf'				=> false,
			'preview'			=> false,
			// 'single'			=> false,
			'debuggOn'			=> true,
			'item_id'			=> false,
			'order_id'			=> false,
			'dataset'			=> [],
			'product_id'		=> false,
			'multiple'			=> false,
			'pages'				=> [],
			'bg_type'			=> 'bg'
		]);
		if(empty($args->teddy_name)) {
			if($args->debuggOn) {
				wp_die(__('Ensure teddy\'s name before making certificate. Because once certificate generated, you can\'t change it anymore.', 'teddybearsprompts'));return false;
			}
		} else {
			return $this->do_certificate($args);
		}
		return false;
	}
	public function teddybearpopupaddon_mail_certificates($PDFs, $args) {
		$args = wp_parse_args($args, [
			'to'			=> false,
			'subject'		=> __('Certificate Email', 'teddybearsprompts'),
			'message'		=> __('Please find the certificate attached.', 'teddybearsprompts'),
		]);
		if(true) {
			ob_start();
			include TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/email/birth-certificates.php';
			$args['message'] = ob_get_clean();
		}
		if(count($PDFs) <= 0) {
			throw new \ErrorException(__('Not attached found. Certificate required.', 'teddybearsprompts'));
		}
		if(!$args['to']) {
			throw new \ErrorException(__('Recipients not found.', 'teddybearsprompts'));
		}
		$headers = ['Content-Type: text/html; charset=UTF-8'];
		$attachments = [];
		foreach($PDFs as $i => $pdf) {
			// $attachments[] = [$pdf => 'certificate-'.$i.'.pdf'];
			if($pdf && !empty($pdf) && file_exists($pdf) && !is_dir($pdf)) {
				$attachments[] = $pdf;
			}
		}
		
		if(count($attachments) <= 0) {
			throw new \ErrorException(__('Certificate not found or this could happens probably if path not matching or permission issue.', 'teddybearsprompts'));
		}
		$email_sent = wp_mail($args['to'], $args['subject'], $args['message'], $headers, $attachments);
		if($email_sent) {
			// echo 'Email sent successfully.';
			// echo $args['message'];wp_die();
			// print_r($args);wp_die();
		} else {
			throw new \ErrorException(__('Something went wrong. Email not sent.', 'teddybearsprompts'));
		}
	}
	public function teddybearpopupaddon_preview_certificate() {
		global $teddy_Order;
		if(isset($_GET['certificate_preview']) && !empty($_GET['certificate_preview'])) {
			$parts = explode('-', $_GET['certificate_preview']);
			// $parts = explode('', $parts[0]);
			// $order_id = base_convert($parts[0], 36, 10);
			// $item_id = base_convert($parts[1], 36, 10);

			$order_id = $parts[0];
			$item_id = $parts[1];
			$order = wc_get_order((int) $order_id);
			if(!$order || is_wp_error($order)) {
				wp_die(__('Certificate not found!', 'teddybearsprompts'));
			}
			$teddy_Order->woocommerce_order_action_send_birth_certificates($order, $item_id);
			// do_action('woocommerce_order_action_send_birth_certificates', $order, $item_id);
		}
	}
	
	public function stringX_position($stringX, $string, $pdf) {
		$stringX -= $pdf->GetStringWidth($string);
		return $stringX;
	}
	public function certificate_writing_style_he($pdf, $args) {
		global $certificate_args;
		// Add a page
		$pdf->AddPage();
		// $stringWidth = $pdf->GetStringWidth($text);
		
		// ===================================  Left Side =================================== //
		$stringX = 23;$stringY = 39.0;
		

		$pdf->SetFont('dejavusans', '', 10);$pdf->setFillColor(250, 0, 0);
		// $stringY += 15;$pdf->SetXY($stringX, $stringY);
		// 
		$stringY += 15;$pdf->SetXY($this->stringX_position($stringX + 40, $args->teddy_reciever, $pdf), $stringY);
		$pdf->Write(0, $args->teddy_reciever, '', 0, '', true, 0, false, false, 0);

		$text = apply_filters('teddybear/project/system/translate/number', $args->id_num);
		$stringY += 15;$pdf->SetXY($this->stringX_position($stringX + 34, $text, $pdf), $stringY);
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);

		// $text = apply_filters('teddybear/project/system/translate/string', $args->weight, 'teddybearsprompts', $args->weight . ' - input field');
		$text = $args->weight;
		$stringY += 15;$pdf->SetXY($this->stringX_position($stringX + 40, $text, $pdf), $stringY);
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);

		// $text = apply_filters('teddybear/project/system/translate/string', $args->brow, 'teddybearsprompts', $args->brow . ' - input field');
		$text = $args->brow;
		$stringY += 15;$pdf->SetXY($this->stringX_position($stringX + 34, $text, $pdf), $stringY);
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		
		// ===================================  Right Side =================================== //
		$stringX = 90;$stringY = 39.0;

		// Draw a QR Code for this certificate
		if (false) {
			if ($args->order_id && $args->item_id) {
				$certificate_url = site_url(sprintf('/certificates/%s/%s/bg/', $args->order_id, $args->item_id));
				$pdf->write2DBarcode($certificate_url, 'QRCODE,Q', $stringX + 30, $stringY - 35, 20, 20, [
					'border' => 2,
					'vpadding' => 'auto',
					'hpadding' => 'auto',
					'fgcolor' => [230,90,93],
					'bgcolor' => false, // [255,255,255],
					'module_width' => 1,
					'module_height' => 1,
					'position'	=> 'S',
					// 'padding' => 0,
				], 'N');
			}
		}
		// Draw Canvas frame
		if ($certificate_args->bg_type != 'nobg') {
			$_canvas = false;
			foreach ($args->dataset as $key => $dataRow) {
				if (isset($dataRow['_canvas']) && !$_canvas) {
					$_canvas = (array) $dataRow['_canvas'];
				}
			}
			$_canvas = ($_canvas == false)?[]:$_canvas;
			foreach ((array) $_canvas as $_index => $_path) {
				$image_path = apply_filters('teddybear/project/slashes/fix', ABSPATH . $_path);
				// $pdf->Error($image_path);
				if (file_exists($image_path) && !is_dir($image_path)) {
					$canvas_url = ($args->product_id)?get_the_permalink($args->product_id):'';
					$pdf->Image($image_path, $stringX - 5, $stringY - 40, 25, 25, '', $canvas_url, 'T', false, 300, '', false, false, 1, false, false, false);
				}
			}
		}
		
		$stringY += 15;$pdf->SetXY($this->stringX_position($stringX + 45, $args->teddy_name, $pdf), $stringY);
		$pdf->Write(0, $args->teddy_name, '', 0, '', true, 0, false, false, 0);

		$text = wp_date('d/m/Y', strtotime($args->teddy_birth));
		$stringY += 15;$pdf->SetXY($this->stringX_position($stringX + 34, $text, $pdf), $stringY);
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		
		
		// $text = apply_filters('teddybear/project/system/translate/string', $args->height, 'teddybearsprompts', $args->height . ' - input field');
		$text = $args->height;
		$stringY += 15;$pdf->SetXY($this->stringX_position($stringX + 45, $text, $pdf), $stringY);
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		
		// $text = apply_filters('teddybear/project/system/translate/string', $args->eye, 'teddybearsprompts', $args->eye . ' - input field');
		$text = $args->eye;
		$stringY += 15;$pdf->SetXY($this->stringX_position($stringX + 34, $text, $pdf), $stringY);
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);

		$stringY += 16;$pdf->SetXY($this->stringX_position($stringX + 25, $args->teddy_sender, $pdf), $stringY);
		$pdf->Write(0, $args->teddy_sender, '', 0, '', true, 0, false, false, 0);
	}
	public function certificate_writing_style_en($pdf, $args) {
		global $certificate_args;
		// Add a page
		$pdf->AddPage();
		// $stringWidth = $pdf->GetStringWidth($text);

		// ===================================  Left Side =================================== //
		$stringX = 23;$stringY = 39.0;
		

		$pdf->SetFont('dejavusans', '', 10);$pdf->setFillColor(250, 0, 0);
		// 
		$text = sprintf(__('Belongs to: %s', 'teddybearsprompts'), $args->teddy_reciever);
		$stringY += 15;$pdf->SetXY($stringX, $stringY);
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);

		$text = sprintf(__('ID number: %s', 'teddybearsprompts'), apply_filters('teddybear/project/system/translate/number', $args->id_num));
		$stringY += 15;$pdf->SetXY($stringX, $stringY);
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);

		$text = sprintf(__('Weight: %s', 'teddybearsprompts'), apply_filters('teddybear/project/system/translate/string', $args->weight, 'teddybearsprompts', $args->weight . ' - input field'));
		$stringY += 15;$pdf->SetXY($stringX, $stringY);
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);

		$text = sprintf(__('Fur color: %s', 'teddybearsprompts'), apply_filters('teddybear/project/system/translate/string', $args->brow, 'teddybearsprompts', $args->brow . ' - input field'));
		$stringY += 15;$pdf->SetXY($stringX, $stringY);
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		
		$text = __('Signature', 'teddybearsprompts');
		$stringY += 12;$pdf->SetXY(($stringX + 25), $stringY);
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);

		// ===================================  Right Side =================================== //
		$stringX = 90;$stringY = 39.0;

		// Draw a QR Code for this certificate
		if (true) {
			if ($args->order_id && $args->item_id) {
				$certificate_url = site_url(sprintf('/certificates/%s/%s/bg/', $args->order_id, $args->item_id));
				$pdf->write2DBarcode($certificate_url, 'QRCODE,Q', $stringX + 30, $stringY - 35, 20, 20, [
					'border' => 2,
					'vpadding' => 'auto',
					'hpadding' => 'auto',
					'fgcolor' => [230,90,93],
					'bgcolor' => false, // [255,255,255],
					'module_width' => 1,
					'module_height' => 1,
					'position'	=> 'S',
					// 'padding' => 0,
				], 'N');
			}
		}
		// Draw Canvas frame
		if ($certificate_args->bg_type != 'nobg') {
			$_canvas = false;
			foreach ($args->dataset as $key => $dataRow) {
				if (isset($dataRow['_canvas']) && !$_canvas) {
					$_canvas = (array) $dataRow['_canvas'];
				}
			}
			$_canvas = ($_canvas == false)?[]:$_canvas;
			foreach ((array) $_canvas as $_index => $_path) {
				$image_path = apply_filters('teddybear/project/slashes/fix', ABSPATH . $_path);
				// $pdf->Error($image_path);
				if (file_exists($image_path) && !is_dir($image_path)) {
					$canvas_url = ($args->product_id)?get_the_permalink($args->product_id):'';
					$pdf->Image($image_path, $stringX - 5, $stringY - 40, 25, 25, '', $canvas_url, 'T', false, 300, '', false, false, 1, false, false, false);
				}
			}
		}
		
		// 
		
		$text = sprintf(__('Name: %s', 'teddybearsprompts'), $args->teddy_name);
		$stringY += 15;$pdf->SetXY($stringX, $stringY);
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);

		$text = sprintf(__('Date of birth: %s', 'teddybearsprompts'), wp_date('m/d/Y', strtotime($args->teddy_birth)));
		$stringY += 15;$pdf->SetXY($stringX, $stringY);
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		
		
		$text = sprintf(__('Height: %s', 'teddybearsprompts'), apply_filters('teddybear/project/system/translate/string', $args->height, 'teddybearsprompts', $args->height . ' - input field'));
		$stringY += 15;$pdf->SetXY($stringX, $stringY);
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		
		$text = sprintf(__('Eye color: %s', 'teddybearsprompts'), apply_filters('teddybear/project/system/translate/string', $args->eye, 'teddybearsprompts', $args->eye . ' - input field'));
		$stringY += 15;$pdf->SetXY($stringX, $stringY);
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);

		
		// $text = sprintf(__('Created with love by: %s', 'teddybearsprompts'), $args->teddy_sender);
		// $pdf->SetXY($stringX, $stringY);$stringY += 1;
		// $pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		
		$text = __('Created with love by:', 'teddybearsprompts');
		$stringY += 11;$pdf->SetXY($stringX, $stringY);
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		$stringY += 5;$pdf->SetXY($stringX, $stringY);
		$pdf->Write(0, $args->teddy_sender, '', 0, '', true, 0, false, false, 0);
		// 
	}
	public function pdf_preview_or_redirect($pdf_path) {
		if( false && !headers_sent()) {
			header('Content-type: application/pdf');
			header('Content-Disposition: inline; filename="' . __('Certificate Preview', 'teddybearsprompts') . '"');
			header('Content-Length: ' . filesize($pdf_path));
			readfile($pdf_path);exit;
		} else {
			$pdf_url = str_replace(ABSPATH, home_url('/'), $pdf_path);
			wp_redirect($pdf_url);exit;
		}
	}
	public function woocommerce_order_status_completed($order_id, $order) {
		if(!in_array($order->get_status(), explode(',', str_replace([' '], [''], apply_filters('teddybear/project/system/getoption', 'order-certificate_email', 'completed'))))) {return;}
		do_action('woocommerce_order_action_send_birth_certificates', $order);
	}
	public function woocommerce_email_attachments($attachments , $email_id, $order) {
		if(!in_array($order->get_status(), explode(',', str_replace([' '], [''], apply_filters('teddybear/project/system/getoption', 'order-attach_status', 'shipped'))))) {return $attachments;}
		$email_ids = [
			'completed' => 'customer_completed_order',
			// 'shipped'	=> 'customer_shipped_order',
			// 'invoice'	=> 'customer_invoice',
		];
		if (in_array($order->get_status(), array_keys($email_ids)) && !in_array($email_id, array_values($email_ids))) {return $attachments;}
		// if (in_array($order->get_status(), ['completed', 'processing']) && !in_array($email_id, ['customer_invoice']) {}
		// 
		$certificates = $this->get_all_certificates($order);
		// 
		foreach($certificates as $certificate) {
			if($certificate && !empty($certificate) && file_exists($certificate) && !is_dir($certificate)) {
				$attachments[] = $certificate;
			}
		}
		// 
		// print_r($attachments);wp_die();
		// 
		return $attachments;
	}
	public function get_all_certificates($order, $preview = false) {
		$certificates = [];
		foreach($order->get_items() as $order_item_id => $order_item) {
			if($preview && $preview != $order_item_id) {continue;}
			$singles = $this->get_single_certificates($order, $order_item, $preview);
			foreach($singles as $single) {$certificates[] = $single;}
		}
		return $certificates;
	}
	public function get_single_certificates($order, $order_item, $preview = false, $args_only = false) {
		global $teddy_Product;global $teddy_Meta;$certificates = [];
		$order_id = $order->get_id();
		$item_id  = $order_item->get_id();
		// $item_name = $order_item->get_name();
		// $product = $order_item->get_product();
		// $quantity = $order_item->get_quantity();
		$product_id = $order_item->get_product_id();
		$_dataset = $teddy_Meta->get_order_item_dataset($order_item, $order);
		if(!$_dataset || !is_array($_dataset) || count($_dataset) <= 0) {return $certificates;}
		$_infoIndex = array_search('info', array_column($_dataset, 'type'));
		if (isset($_dataset[$_infoIndex]) && isset($_dataset[$_infoIndex]['type']) && $_dataset[$_infoIndex]['type'] == 'info') {
			$_infos = $_dataset[$_infoIndex]['infos']??[];
			$custom_data = wp_parse_args((array) get_post_meta($product_id, '_teddy_custom_data', true), [
				'eye'				=> '',
				'brow'				=> '',
				'weight'			=> '',
				'height'			=> '',
			]);
			$args = [
				...$_infos,
				'eye'			=> $custom_data['eye'],
				'brow'			=> $custom_data['brow'],
				'id_num'		=> $order_id,
				// bin2hex($order_id), // strtolower(base_convert($order_id, 10, 36) . base_convert($item_id, 10, 36) . '-' . rand(1, 9999)),
				'weight'		=> $custom_data['weight'],
				'height'		=> $custom_data['height'],

				'preview'		=> (bool) $preview,
				// 'single'		=> $preview,
				
				'pdf'			=> 'certificate-'.$item_id.'-'.$order_id.'.pdf',
				'debuggOn'		=> $preview,
				'item_id'		=> $item_id,
				'order_id'		=> $order_id,
				'dataset'		=> $_dataset,
				'product_id'	=> $product_id
			];
			$certificates[] = ($args_only)?$args:apply_filters('teddybearpopupaddon_generate_certificate', false, $args);
		}
		return $certificates;
	}
	public function certificate_preview($order_id, $order_item_id, $_certificate_type = 'bg') {
		global $teddy_Order;global $teddy_Product;global $teddy_Certificate;
		$abspath = apply_filters('teddybear/project/slashes/fix', ABSPATH);
		if (empty($order_id) || empty($order_item_id)) {
			throw new \Exception(__('Invalid request. Please check your permalink.', 'teddybearsprompts'));
		}
		$order = wc_get_order((int) $order_id);
		if (!$order || is_wp_error($order)) {
			throw new \Exception(__('Your request invalid or the contents might removed or moved permanantly.', 'teddybearsprompts'));
		}
		$certificates = [];$previewPDFUrl = false;
		if (in_array($order_item_id, ['print'])) {
			// 
			foreach($order->get_items() as $orderItem_id => $order_item) {
				$singles = $teddy_Certificate->get_single_certificates($order, $order_item, false, true);
				foreach($singles as $single) {$certificates[] = $single;}
			}
			// 
			if (count($certificates) > 0) {
				$args = [
					'teddy_name'    => '1',
					'multiple'      => true,
					'bg_type'		=> $_certificate_type,
					'pages'         => $certificates,
					'pdf'           => sprintf('certificate-%d-%s-%s.pdf', $order_id, $order_item_id, $_certificate_type)
				];
				$certificte_path = apply_filters('teddybearpopupaddon_generate_certificate', false, $args);
				if ($certificte_path) {
					$previewPDFUrl = str_replace([$abspath, DIRECTORY_SEPARATOR], [site_url('/'), '/'], $certificte_path);
				}
			}
			// 
		} else if (!is_numeric($order_item_id)) {
			throw new \Exception(sprintf(__('Your requested printout for (%s) currently not available. Please contact with support for further assistance.', 'teddybearsprompts'), $order_item_id));
		} else {
			foreach($order->get_items() as $orderItem_id => $order_item) {
				if ($orderItem_id != (int) $order_item_id) {continue;}
				$singles = $teddy_Certificate->get_single_certificates($order, $order_item, false, false);
				foreach($singles as $single) {$certificates[] = $single;}
			}
			if (count($certificates) >= 1 && isset($certificates[0]) && !empty($certificates[0])) {
				// ptint_r($certificates);
				$previewPDFUrl = str_replace([$abspath, DIRECTORY_SEPARATOR], [site_url('/'), '/'], $certificates[0]);
			}
		}
		// echo '<pre>';print_r($previewPDFUrl);echo '<pre>';wp_die();
		// 
		/**
		 * Two ways. One is to preview and another is to redirect.
		 * https://dubidof.com/certificates/3133/782/
		 */
		if ($previewPDFUrl && !empty($previewPDFUrl)) {
			// wp_safe_redirect();
			// wp_redirect($previewPDFUrl);
			echo '<iframe id="certificate_preview_frame" src="' . esc_url($previewPDFUrl) . '" frameborder="0"></iframe>';
		} else {
			throw new \Exception(__('No certificate found.', 'teddybearsprompts'));
		}
	}
	
}
