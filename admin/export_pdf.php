<?php
require('includes/configuration.php');
require_once( 'tcpdf/config/lang/eng.php' ); 
require_once( 'tcpdf/tcpdf.php' ); 
require_once( 'classes/tcpdf.custom.class.php' );
extract($_REQUEST);

if( !isset( $_SESSION['report_query'] )){
	die('Report can\'t exported. Please try again.');	
} 

$reportData = array();
if( $_SESSION['report_query'][$page] ){
	$ssql = $_SESSION['report_query'][$page];
	$nototal = $dclass->numRows( $ssql );
	$restepm = $dclass->query( $ssql );
	$reportData = $dclass->fetchResults( $restepm );
}

if( !count( $reportData ) ) die('No data found.');

	
	function make_pdf( $filename = "", $file_data = "" ){
			
		global $pdf;
		
		//$file_data = parse_pdf_str( $file_data );
		
		// echo $file_data; exit;
		
		$pdf = new MYPDF( PDF_PAGE_ORIENTATION , PDF_UNIT , PDF_PAGE_FORMAT , true , 'UTF-8' , false );
			
		$pdf->setHeaderData( '', '', $title_pdf, $desc_pdf, array(0,0,0) );
		$pdf->setFooterData($tc = array(0,64,0), $lc=array(0,64,128));
		
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', 8));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		
		$pdf->setPrintHeader(true);
		$pdf->setPrintFooter(true); 
		
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		
		$pdf->SetMargins(5, 15, 5);
		$pdf->SetHeaderMargin(0);
		$pdf->SetFooterMargin(10);
		
		$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
		
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setLanguageArray($l);
		$pdf->setFontSubsetting(true);
		
		//$pdf->addTTFfont(PATH_PREFIX.'tcpdf/fonts/DroidSansFallback.ttf');
		//$pdf->SetFont('DroidSansFallback', '', 14, '', false);
		$pdf->SetFont('helvetica', '', 14, '', true);
		
		$pdf->AddPage();
		$pdf->setPageMark();
		
		$pdf->writeHTMLCell( $w = 0, $h =  0, $x = '5', $y = '15', $file_data, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'center', $autopadding = false );
		$pdf->Output( 'reports/pdf/'.$filename, 'FD' );
		
		return $filename;
		
	}


	if( $_REQUEST['page'] == 'top_drivers' ){
		$pdf_str = 
		'<table style="width:100%;" cellpadding="5" border="1" cellspacing="0" >
			<tr>
				<td style="background-color:#CCC; font-size:14px;" >Driver Name</td>
				<td style="background-color:#CCC; font-size:14px;" >Total Trip</td>
				<td style="background-color:#CCC; font-size:14px;" >Amount</td>
			</tr>';
			foreach( $reportData as $row ){
				$pdf_str .= 
				'<tr>';
					$pdf_str .= '<td style="font-size:13px;" >'.$row['v_name'].'</td>';
					$pdf_str .= '<td style="font-size:13px;" >'.$row['ride_count'].'</td>';
					$pdf_str .= '<td style="font-size:13px;" >'.$row['driver_earning'].'</td>';
				$pdf_str .= 
				'</tr>';
			}
			$pdf_str .= 
		'</table>';
	
	}
	if( $_REQUEST['page'] == 'warroom' ){
		$pdf_str = 
		'<table style="width:100%;" cellpadding="5" border="1" cellspacing="0" >
			<tr>
				<td style="background-color:#CCC; font-size:14px;" >No</td>
				<td style="background-color:#CCC; font-size:14px;" >Name</td>
				<td style="background-color:#CCC; font-size:14px;" >Vehicle Type</td>
				<td style="background-color:#CCC; font-size:14px;" >Vehicle No.</td>
				<td style="background-color:#CCC; font-size:14px;" >On Ride</td>
				<td style="background-color:#CCC; font-size:14px;" >Available</td>
				<td style="background-color:#CCC; font-size:14px;" >On Duty</td>
			</tr>';
			foreach( $reportData as $k => $row ){
				$pdf_str .= 
				'<tr>';
					$pdf_str .= '<td style="font-size:13px;" >'.( $k + 1 ).'</td>';
					$pdf_str .= '<td style="font-size:13px;" >'.$row['v_name'].'</td>';
					$pdf_str .= '<td style="font-size:13px;" >'.$row['vehicle_type'].'</td>';
					$pdf_str .= '<td style="font-size:13px;" >'.$row['vehicle_number'].'</td>';
					$pdf_str .= '<td style="font-size:13px;" >'.$row['is_onride'].'</td>';
					$pdf_str .= '<td style="font-size:13px;" >'.$row['v_token'].'</td>';
					$pdf_str .= '<td style="font-size:13px;" >'.$row['is_onduty'].'</td>';
				$pdf_str .= 
				'</tr>';
			}
			$pdf_str .= 
		'</table>';
	}
	
	$pdf = 
	'<table width="100%" cellpadding="2" cellspacing="0" >
		<tr>
			<td width="100%" >
				<table width="100%" border="1" cellpadding="10" cellspacing="0" >
					<tr><td width="100%" align="center" style="background-color:#CCC; font-size:16px;" ><b>'.$_REQUEST['page_title'].'</b></td></tr>
				</table>
			</td>
		</tr>
		<tr>
			<td with="100%">
				'.$pdf_str.'
			</td>
		</tr>
	</table>';

$FileName = $gnrl->seoText( $_REQUEST['page_title'] ).'-'.date('Y-m-d').'.pdf';
$filename = make_pdf( $FileName, $pdf );
exit;
?>