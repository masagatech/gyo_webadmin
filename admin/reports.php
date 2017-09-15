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
       'is_onride' => 'On Ride',
       'v_token' => 'Available',
       'is_onduty' => 'On Duty',
    );
}

if( $_REQUEST['page'] == 'outstanding' ){
	$titleArr = array(
       'driver_v_id' => 'Driver ID',
       'driver_name' => 'Driver Name',
       'total_ride' => 'Total Rides',
       'ride_cancel' => 'Total Cancelled Ride',
       'manual_adjustment' => 'Manual Adjustment (Amt)',
       'dry_run' => 'Dry Run (Amt)',
       'referral' => 'Referral',
       'total_fare' => 'Total Fare',
       'wallet_transaction' => 'Wallet Transaction',
       'paid_by_goyo' => 'Paid By Goyo',
       'goyo_cut' => 'Goyo Cut',
       'pan_card' => 'Pan Card',
       'bank_name' => 'Bank Name',
       'ifsc_code' => 'IFSC Code',
       'account_name' => 'Account Name',
       'current_ac_no' => 'Current Ac No',
       'branch_address' => 'Branch Address',
       'settlement_amount' =>'Settlement Amount',
       'transaction_info' =>'Transaction Info',
    );
}

if( $_REQUEST['page'] == 'payment_history' ){
	$titleArr = array(
       'driver_v_id' => 'Driver ID',
       'driver_name' => 'Driver Name',
       'total_ride' => 'Total Rides',
       'ride_cancel' => 'Total Cancelled Ride',
       'manual_adjustment' => 'Manual Adjustment (Amt)',
       'dry_run' => 'Dry Run (Amt)',
       'referral' => 'Referral',
       'total_fare' => 'Total Fare',
       'wallet_transaction' => 'Wallet Transaction',
       'paid_by_goyo' => 'Paid By Goyo',
       'goyo_cut' => 'Goyo Cut',
       'pan_card' => 'Pan Card',
       'bank_name' => 'Bank Name',
       'ifsc_code' => 'IFSC Code',
       'account_name' => 'Account Name',
       'current_ac_no' => 'Current Ac No',
       'branch_address' => 'Branch Address',
    );
}

if( $_REQUEST['page'] == 'outstanding_view' ){
	$titleArr = array(
       	'd_added' => 'Tx Date',
		'driver_id' => 'Driver ID',
		'driver_name' => 'Driver Name',
		'particular' => 'Particular',
		'ride_code' => 'Ride ID',
		'cutomer_name' => 'Customer Name',
		'payment_method' => 'Payment Method',
		'debit' => 'Debit',
		'credit' => 'Credit',
		'outstanding' => 'Outstanding',
    );
}

if( $_REQUEST['page'] == 'referred' ){
	$titleArr = array(
       	'v_id' => 'Customer/Driver ID',
		'v_name' => 'Customer/Driver Name',
		'referral_user_id' => 'Referred By ID',
		'referral_user_name' => 'Referred By Name',
		'referral_amount' => 'Referral Amount',
		'referral_wallet_apply' => 'Type',
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
	// _P($rowValue);
	// exit;
	$l_data = json_decode($rowValue['l_data'],true);
	$char = "A";
	// if($_REQUEST['page'] == 'outstanding'){
	// 	if(isset($rowValue['bank_info']) && !empty($rowValue['bank_info'])){
	// 		$bank_info = json_decode($rowValue['bank_info'],true);
	// 	}
	// }
	foreach( $titleArr as $titleKey => $titleValue ){

		if( $_REQUEST['page'] == 'referred' ){

			if($titleKey  == 'referral_amount' || $titleKey == 'referral_wallet_apply'){

				$objPHPExcel->getActiveSheet()->SetCellValue( $char.$count, $l_data[$titleKey] );		

			}else{

				$objPHPExcel->getActiveSheet()->SetCellValue( $char.$count, $rowValue[$titleKey] );

			}

		}else{

			$objPHPExcel->getActiveSheet()->SetCellValue( $char.$count, $rowValue[$titleKey] );

		}
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
// _P($objPHPExcel);
// exit;
// exit;
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.$FileName.'"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');
$objWriter = new PHPExcel_Writer_Excel2007( $objPHPExcel );
// _P($objWriter);
// exit;
$objWriter->save('php://output');
exit;
?>