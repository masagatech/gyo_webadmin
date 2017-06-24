<?php
require('includes/configuration.php');
require_once( 'tcpdf/config/lang/eng.php' ); 
require_once( 'tcpdf/tcpdf.php' ); 
require_once( 'classes/tcpdf.custom.class.php' );
extract($_REQUEST);

if( !isset( $_SESSION['report_query'] )){
	die('Report can\'t exported. Please try again.');	
} 

if($_SESSION['report_query']){
	if($_SESSION['report_query'][$page]){
		$ssql = $_SESSION['report_query'][$page];
		$nototal = $dclass->numRows( $ssql );
		$restepm = $dclass->query( $ssql );
		$reportData = $dclass->fetchResults( $restepm );
	}

}
// _P($reportData);
// exit;

if( !count( $reportData ) ) die('No data found.');

if($_REQUEST['page'] == 'top_drivers'){

	$pdf_str = "<table class='table table-bordered' id='datatable' style='width:100%;' >
            <thead>
            	<tr>
            		<td>Driver Name</td>
            		<td>Total Trip</td>
            		<td>Amount</td>
            	</tr>
            </thead>
            <tbody>";
			if( $nototal > 0 ){
					$i = 0;
					foreach( $reportData as $row ){
			        	$i++;
			        	
			            $pdf_str .= "<tr><td>".$row['v_name']."</td>
							<td>".$row['ride_count']."</td>
							<td>".$row['driver_earning']."</td>
			            </tr>";
			        }
			}
			else{
			       $pdf_str .=   "<tr><td colspan='8'>No Record found.</td></tr>";
			}
			$pdf_str .= "</tbody></table>"  ;

}
if($_REQUEST['page'] == 'warroom'){

	$pdf_str = "<table class='table table-bordered' id='datatable' style='width:100%;' >
            <thead>
            	<tr>
            		<td>No</td>
            		<td>Name</td>
            		<td>Vehicle Type</td>
            		<td>Vehicle No.</td>
            		<td>On Ride</td>
            		<td>Available</td>
            		<td>On Duty</td>
            	</tr>
            </thead>
            <tbody>";
           
			if( $nototal > 0 ){
					$i = 0;
					foreach( $reportData as $row ){
			        	$i++;
			        	
			            $pdf_str .= "<tr><td>".$i."</td>
							<td>".$row['v_name']."</td>
							<td>".$row['vehicle_type']."</td>
							<td>".$row['vehicle_number']."</td>
							<td>".$row['is_onride']."</td>
							<td>".$row['v_token']."</td>
							<td>".$row['is_onduty']."</td>
			            </tr>";
			        }
			}
			else{
			       $pdf_str .=   "<tr><td colspan='8'>No Record found.</td></tr>";
			}
			$pdf_str .= "</tbody></table>";

}
$FileName = $gnrl->seoText( $_REQUEST['page_title'] ).'-'.date('Y-m-d').'.pdf';

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
		$pdf->Output('reports/pdf/'.$filename, 'FD');
		
		return $filename;
		
	}
$filename = make_pdf( $FileName, $pdf_str);
// $gnrl->redirectTo($page.".php?succ=1&msg=add");
?>