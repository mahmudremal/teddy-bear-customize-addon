<?php
/**
 * 
 */

namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;

class CertificatePDF extends \TCPDF {
	//Page header
	public function Header() {
		// get the current page break margin
		$bMargin = $this->getBreakMargin();
		// get current auto-page-break mode
		$auto_page_break = $this->AutoPageBreak;
		// disable auto-page-break
		$this->setAutoPageBreak(false, 0);
		// set bacground image
		$img_file = apply_filters('teddy/project/certificate/background', false);
		if ($img_file) {
			$this->Image($img_file, null, 0, $this->getPageWidth(), $this->getPageHeight(), '', '', '', false, 300, 'C', false, false, 0);
		}
		// restore auto-page-break status
		$this->setAutoPageBreak($auto_page_break, $bMargin);
		// set the starting point for the page content
		$this->setPageMark();
	}
}