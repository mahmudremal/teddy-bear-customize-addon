<?php
/**
 * LoadmorePosts
 *
 * @package TeddyBearCustomizeAddon
 */

namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;

use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
class Certificate {
	use Singleton;
	protected function __construct() {
		$this->setup_hooks();
	}
	public function setup_hooks() {
		add_filter('teddybearpopupaddon_generate_certificate', [$this, 'teddybearpopupaddon_generate_certificate'], 1, 2);
		add_action('teddybearpopupaddon_mail_certificates', [$this, 'teddybearpopupaddon_mail_certificates'], 1, 2);
		add_action('init', function() {
			global $teddyBear__Order;
			if(isset($_GET['certificate_preview'])) {
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
				$teddyBear__Order->woocommerce_order_action_send_birth_certificates($order, $item_id);
			}
		}, 10, 0 );
		
	}
	public function do_certificate__blank($args) {
		require_once(TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/inc/tcpdf/examples/tcpdf_include.php');
	
		$pdf_path = TEDDY_BEAR_CUSTOMIZE_ADDON_UPLOAD_DIR . $args->pdf;
		// && filesize($pdf_path) >= 1
		if($args->pdf && !empty($args->pdf) && file_exists($pdf_path) && !is_dir($pdf_path)) {
			return $pdf_path;
		}

		// Create a new TCPDF instance with A4 page format
		$pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
	

		// Set document information
		$pdf->SetCreator('mahmud_remal');
		$pdf->SetAuthor(get_bloginfo('name'));
		$pdf->SetTitle(__('Teddy Birth Certificate', 'teddybearsprompts'));
		$pdf->setSubject(__('Teddy Birth Certificate', 'teddybearsprompts'));
		$pdf->setKeywords('Certificate', 'Birth certificate');

		// Disable default header and footer
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
	
		// Add a page
		$pdf->AddPage();
	
		// Set background image
		$imagePath = TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_IMG_URI . '/blank_certificate.jpg';
	
		// Calculate dimensions for image scaling (as shown in previous examples)
		$imageWidth = $pdf->getPageWidth();
		$imageHeight = $pdf->getPageHeight();
	
		list($origWidth, $origHeight) = getimagesize($imagePath);
		if ($origWidth > $origHeight) {
			// Landscape image
			$imageHeight = $pdf->getPageWidth() * ($origHeight / $origWidth);
		} else {
			// Portrait or square image
			$imageWidth = $pdf->getPageHeight() * ($origWidth / $origHeight);
		}
	
		// Calculate image position to center it horizontally
		$imageX = (($pdf->getPageWidth() - $imageWidth)/2);
		$imageY = 5;
	
		$pdf->Image($imagePath, $imageX, $imageY, ($imageWidth-2), $imageHeight, '', '', '', false, 300, '', false, false, 0);

		
		$text = 'Lovely Teddy';
		$pdf->SetFont('times', 'BI', 35);
		$pdf->Write(130, $text, '', 0, 'C', true, 0, false, false, 0);
		
		$text = sprintf(__('Eye Color: %s', 'teddybearsprompts'), $args->eye);
		// $textX = (($pdf->getPageWidth() - $pdf->GetStringWidth($text))/2) + 150;
		$textX = 25;
		$textY = 90;
		$pdf->setFont('helvetica', '', 14);
		$pdf->SetXY($textX, $textY);
		$pdf->Write(0, $text, '', 0, 'L', true, 0, false, false, 0);
		$pdf->SetXY(($textX+0), ($textY+10));
		$text = sprintf(__('Eyebrow: %s', 'teddybearsprompts'), $args->brow);
		$pdf->Write(0, $text, '', 0, 'L', true, 0, false, false, 0);
		// $pdf->Multicell(0,2, $text);
		

		// Set text content and position
		$text = wp_date('F d, Y', strtotime("Aug 10, 2023"));
		// $textX = ($pdf->getPageWidth() - $pdf->GetStringWidth($text)) / 2;
		$textX = 0;$textY = 120;
		$pdf->SetXY(60, 125);
		$pdf->Write(10, $text, '', 0, 'L', true, 0, false, false, 0);

		// $pdf->SetXY(0, 120);
		// $pdf->SetFont('times', false, 14);
		// $pdf->Cell(160, 20, $text, 0, 1, 'C');
	
		// Set signature image
		$signatureImagePath = TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_IMG_URI . '/signature.png';

		$pdf->Image($signatureImagePath, 105, 120, 50, 20, '', '', '', false, 300, '', false, false, 0);
	
		// $args->preview = true;
		if($args->preview) {
			// Output the PDF to the browser
			$pdf->Output('certificate.pdf', 'I');exit;
		} else {
			// Return the PDF contents
			$args->pdf = ($args->pdf && !empty($args->pdf))?$args->pdf:'certificate-'.uniqid().'.pdf';
			$pdf_path = TEDDY_BEAR_CUSTOMIZE_ADDON_UPLOAD_DIR . $args->pdf;
			// ob_start();
			$pdf->Output($pdf_path, 'F');
			// $pdfContent = ob_get_clean();
			// file_put_contents($pdf_path, $pdfContent);
			return $pdf_path;
		}
		
	}
	public function do_certificate($args) {
		require_once(TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/inc/tcpdf/examples/tcpdf_include.php');
		require_once(TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/inc/widgets/class-tcpdfcertificate.php');
	
		$pdf_path = TEDDY_BEAR_CUSTOMIZE_ADDON_UPLOAD_DIR . $args->pdf;
		// && filesize($pdf_path) >= 1
		if($args->pdf && !empty($args->pdf) && file_exists($pdf_path) && !is_dir($pdf_path)) {
			if($args->preview) {
				$this->pdf_preview_or_redirect($pdf_path);
			} else {
				return $pdf_path;
			}
		}

		// Create a new TCPDF instance with A4 page format
		// $pdf = new \TCPDF('P', 'mm', 'A5', true, 'UTF-8', false);
		$pdf = new CertificatePDF('L', PDF_UNIT, 'A5', true, 'UTF-8', false);
	

		// Set document information
		$pdf->SetCreator('mahmud_remal');
		$pdf->SetAuthor(get_bloginfo('name'));
		$pdf->SetTitle(__('Teddy Birth Certificate', 'teddybearsprompts'));
		$pdf->setSubject(__('Teddy Birth Certificate', 'teddybearsprompts'));
		$pdf->setKeywords('Certificate', 'Birth certificate');

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
		
		// Add a page
		$pdf->AddPage();
		$pdf->SetFont('dejavusans', '', 10);
		
		$this->certificate_writing_style_2($pdf, $args);

		// Erasing something
		// $styleB = ['width' => 0.254, 'cap' => 'butt', 'join' => 'miter', 'dash' => 3, 'color' => [255, 255, 255]];
		// $pdf->setFillColor(255, 255, 255);
		// $pdf->Polygon(array(15, 18, 70, 18, 70, 40, 15, 40), 'DF', [$styleB, $styleB, $styleB, $styleB]);
		
		$args->pdf = ($args->pdf && !empty($args->pdf))?$args->pdf:'certificate-'.uniqid().'.pdf';
		$pdf_path = TEDDY_BEAR_CUSTOMIZE_ADDON_UPLOAD_DIR . $args->pdf;
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
			'birth'			=> '',
			'eye'			=> '',
			'brow'			=> '',

			'teddyname'		=> '',
			'id_num'		=> '',
			'birth'			=> '',
			'belongto'		=> '',
			'gift_by'		=> '',

			'weight'		=> '',
			'height'		=> '',

			'pdf'			=> false,
			'preview'		=> false,
			// 'single'		=> false,
									
		]);
		if(empty($args->teddyname)) {wp_die(__('Ensure teddy\'s name before making certificate. Because once certificate generated, you can\'t change it anymore.', 'teddybearsprompts'));return false;}
		return $this->do_certificate($args);
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
	
	public function certificate_writing_style_1($pdf, $args) {
		// $fontSize = $pdf->GetStringWidth($text);

		// Left Side
		$stringX = 32;$stringY = 55.5;
		
		$text = sprintf(__('Belongs to: %s', 'teddybearsprompts'), $args->belongto);
		$pdf->SetXY($stringX, $stringY);$stringY += 13;
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		$text = sprintf(__('ID number: %s', 'teddybearsprompts'), $args->id_num);
		$pdf->SetXY($stringX, $stringY);$stringY += 13;
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		$text = sprintf(__('Weight: %s', 'teddybearsprompts'), $args->weight);
		$pdf->SetXY($stringX, $stringY);$stringY += 13;
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		$text = sprintf(__('Fur color: %s', 'teddybearsprompts'), $args->brow);
		$pdf->SetXY($stringX, $stringY);$stringY += 13;
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		$text = __('Signature', 'teddybearsprompts');
		$pdf->SetXY(($stringX + 25), $stringY);$stringY += 13;
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);

		// Right Side
		$stringX = 91;$stringY = 55.5;

		$text = sprintf(__('Name: %s', 'teddybearsprompts'), $args->teddyname);
		$pdf->SetXY($stringX, $stringY);$stringY += 13;
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		$text = sprintf(__('Date of birth: %s', 'teddybearsprompts'), $args->birth);
		$pdf->SetXY($stringX, $stringY);$stringY += 13;
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		$text = sprintf(__('Height: %s', 'teddybearsprompts'), $args->height);
		$pdf->SetXY($stringX, $stringY);$stringY += 13;
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		$text = sprintf(__('Eye color: %s', 'teddybearsprompts'), $args->eye);
		$pdf->SetXY($stringX, $stringY);$stringY += 13;
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		$text = sprintf(__('Created with love by: %s', 'teddybearsprompts'), $args->gift_by);
		$pdf->SetXY($stringX, $stringY);$stringY += 13;
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		
	}
	public function certificate_writing_style_2($pdf, $args) {
		// $stringWidth = $pdf->GetStringWidth($text);

		// Left Side
		$stringX = 32;$stringY = 55.5;
		
		$text = __('Belongs to:', 'teddybearsprompts');$pdf->setFillColor(250, 0, 0);
		$pdf->SetXY($stringX, ($stringY - 4.5));$pdf->SetFont('dejavusans', '', 8);
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		$pdf->SetFont('dejavusans', '', 10);
		$text = $args->belongto;
		$pdf->SetXY($stringX, $stringY);$stringY += 13;
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);

		$text = __('ID number:', 'teddybearsprompts');$pdf->setFillColor(250, 0, 0);
		$pdf->SetXY($stringX, ($stringY - 4.5));$pdf->SetFont('dejavusans', '', 8);
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		$pdf->SetFont('dejavusans', '', 10);
		$text = $args->id_num;
		$pdf->SetXY($stringX, $stringY);$stringY += 13;
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);

		$text = __('Weight:', 'teddybearsprompts');$pdf->setFillColor(250, 0, 0);
		$pdf->SetXY($stringX, ($stringY - 4.5));$pdf->SetFont('dejavusans', '', 8);
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		$pdf->SetFont('dejavusans', '', 10);
		$text = $args->weight;
		$pdf->SetXY($stringX, $stringY);$stringY += 13;
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);

		$text = __('Fur color:', 'teddybearsprompts');$pdf->setFillColor(250, 0, 0);
		$pdf->SetXY($stringX, ($stringY - 4.5));$pdf->SetFont('dejavusans', '', 8);
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		$pdf->SetFont('dejavusans', '', 10);
		$text = $args->brow;
		$pdf->SetXY($stringX, $stringY);$stringY += 13;
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		
		$text = __('Signature', 'teddybearsprompts');
		$pdf->SetXY(($stringX + 25), $stringY);$stringY += 13;
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);

		// ===================================  Right Side =================================== //
		$stringX = 91;$stringY = 55.5;

		
		$text = __('Name:', 'teddybearsprompts');$pdf->setFillColor(250, 0, 0);
		$pdf->SetXY($stringX, ($stringY - 4.5));$pdf->SetFont('dejavusans', '', 8);
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		$pdf->SetFont('dejavusans', '', 10);
		$text = $args->teddyname;
		$pdf->SetXY($stringX, $stringY);$stringY += 13;
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);

		$text = __('Date of birth:', 'teddybearsprompts');$pdf->setFillColor(250, 0, 0);
		$pdf->SetXY($stringX, ($stringY - 4.5));$pdf->SetFont('dejavusans', '', 8);
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		$pdf->SetFont('dejavusans', '', 10);
		$text = $args->birth;
		$pdf->SetXY($stringX, $stringY);$stringY += 13;
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		
		
		$text = __('Height:', 'teddybearsprompts');$pdf->setFillColor(250, 0, 0);
		$pdf->SetXY($stringX, ($stringY - 4.5));$pdf->SetFont('dejavusans', '', 8);
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		$pdf->SetFont('dejavusans', '', 10);
		$text = $args->height;
		$pdf->SetXY($stringX, $stringY);$stringY += 13;
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		
		$text = __('Eye color:', 'teddybearsprompts');$pdf->setFillColor(250, 0, 0);
		$pdf->SetXY($stringX, ($stringY - 4.5));$pdf->SetFont('dejavusans', '', 8);
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		$pdf->SetFont('dejavusans', '', 10);
		$text = $args->eye;
		$pdf->SetXY($stringX, $stringY);$stringY += 13;
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		
		$text = __('Created with love by:', 'teddybearsprompts');$pdf->setFillColor(250, 0, 0);
		$pdf->SetXY($stringX, ($stringY - 4.5));$pdf->SetFont('dejavusans', '', 8);
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
		$pdf->SetFont('dejavusans', '', 10);
		$text = $args->gift_by;
		$pdf->SetXY($stringX, $stringY);$stringY += 13;
		$pdf->Write(0, $text, '', 0, '', true, 0, false, false, 0);
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
	
}
