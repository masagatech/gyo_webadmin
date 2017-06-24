<?php
// include configuration file
require('includes/configuration.php');
// check for login
if(!$gnrl->checkLogin()){
	$gnrl->redirectTo("index.php?msg=logfirst");
}	
extract($_REQUEST);
require_once 'classes/PHPExcel/Classes/PHPExcel.php';
require_once 'classes/PHPExcel/Classes/PHPExcel/Writer/Excel2007.php';


if($_SESSION['report_query']){
	if($_SESSION['report_query'][$page]){
		$ssql = $_SESSION['report_query'][$page];
		$nototal = $dclass->numRows( $ssql );
		$restepm = $dclass->query( $ssql );
		$reportData = $dclass->fetchResults( $restepm );
	}

}

if( !count( $reportData ) ) die('No data found.');

$titleArr = array();
if( $_REQUEST['page'] == 'top_drivers' ){
	$titleArr = array(
       'v_name' => 'Driver',
       'ride_count' => 'Total Trip',
       'driver_earning' => 'Amount',
    );
}

if( $_REQUEST['page'] == 'warroom' ){
	$titleArr = array(
       'v_name' => 'Name',
       'vehicle_type' => 'Vehicle Type',
       'vehicle_number' => 'Vehicle No.',
    );
}


$FileName = $gnrl->seoText( $_REQUEST['page_title'] ).'-'.date('Y-m-d').'.xlsx';

$objPHPExcel = new PHPExcel();
$objPHPExcel->setActiveSheetIndex(0); 

$char = 'A';
$count = '1';
foreach( $titleArr as $value ){
    $objPHPExcel->getActiveSheet()->SetCellValue( $char.$count, $value );
	
	$tempStyle = array(
		'fill' => array(
			'type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array( 'rgb' => 'D2FFFF' ),
		),
		'font'  => array(
			// 'bold'  => true, 'color' => array( 'rgb' => 'FF0000' ), 'size'  => 15, 'name'  => 'Verdana'
		),
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array( 'rgb' => '000000' )
			)
		)
	);
	$objPHPExcel->getActiveSheet()->getStyle( $char.$count )->applyFromArray( $tempStyle );
	
	$char++;
}

$count = 2;
foreach( $reportData as $rowValue ){
	$char = "A";
	foreach( $titleArr as $titleKey => $titleValue ){
		$objPHPExcel->getActiveSheet()->SetCellValue( $char.$count, $rowValue[$titleKey] );
		
		$tempStyle = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID, // 'color' => array( 'rgb' => 'FF0000' ),
			),
			'font'  => array(
				// 'bold'  => true, 'color' => array( 'rgb' => 'FF0000' ), 'size'  => 15, 'name'  => 'Verdana'
			),
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array( 'rgb' => '000000' )
				)
			)
		);
		$objPHPExcel->getActiveSheet()->getStyle( $char.$count )->applyFromArray( $tempStyle );
		
		$char++;
	}
	$count++;
}

// exit;
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.$FileName.'"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');
$objWriter = new PHPExcel_Writer_Excel2007( $objPHPExcel );
$objWriter->save('php://output');
exit;
?>