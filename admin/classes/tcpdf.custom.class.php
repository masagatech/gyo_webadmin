<?php
	class MYPDF extends TCPDF {
		## Page header
		public function Header() {
			// Logo
			//$image_file = K_PATH_IMAGES.'logo_example.jpg';
			//$this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
					
			$headerData = 'GOYO ('.date('d/m/Y h:i A').')';
			
			
			
			$this->SetFont('helvetica', 'N', 8);
			$this->Cell( 0, 8, $headerData, 0, false, 'L', 0, '', 0, false, 'T', 'M' );
			
			$headerData = '_______________________________________________________________________________________________________________________________';
			$this->Cell( 0, 10, $headerData, 0, false, 'R', 0, '', 0, false, 'T', 'M');
			
		}
		
		## Page footer
		public function Footer() {
			
			$this->SetY( -15 );
			$this->SetTextColor( 0, 0, 0 );
			$this->SetFont('helvetica', 'N', 9 );
			$this->SetAlpha( 1 );
			$footerData = '_________________________________________________________________________________________________________________';
			$this->Cell(0, 10, $footerData , 0, false, 'C', 0, '', 0, false, 'T', 'M');
			
			
			$this->SetY( -10 );
			$this->SetTextColor( 0, 0, 0 );
			$this->SetFont('helvetica', 'N', 8.5);
			$this->SetAlpha( 1 );
			
			
			
			$footerData = array(
				'GOYO ('.date('d/m/Y h:i A').')',
			
			);
			
			$footerData = implode( ' ', $footerData );
			$this->Cell(0, 10, $footerData , 0, false, 'L', 0, '', 0, false, 'T', 'M');
			
			
			$this->SetY( -10 );
			$this->SetTextColor( 0, 0, 0 );
			$this->SetFont('helvetica', 'N', 8.5);
			$this->SetAlpha( 1 );
			$this->Cell(0, 10, "Page : ".$this->getAliasNumPage().'/'.$this->getAliasNbPages() , 0, false, 'R', 0, '', 0, false, 'T', 'M');
			
		}
	}
?>