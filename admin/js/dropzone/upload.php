<?php

include('../../../v2_classes/general.class.php');
include('../../resize_image.php');
$ds = DIRECTORY_SEPARATOR;  //1
$folder = $_GET['folder'];
$gnrl = new general();

	if( $folder ){ 
		$storeFolder = '../../../uploads/'.$folder.'/';   //2
	}
	else {
		$storeFolder = '../../../uploads/other/';   //2
	}
	
	if ( !empty( $_FILES ) ) {
		
		$tempFile = $_FILES['file']['tmp_name'];          //3
		$targetPath = dirname( __FILE__ ) . $ds. $storeFolder . $ds;  //4
		
		##AddedByD
		if( $folder == 'product' ){
			$targetFile =  $storeFolder.$_FILES['file']['name'];  //5
		}
		else{
			$targetFile =  $storeFolder. strtolower( $gnrl->seoText( $_FILES['file']['name'] ) );  //5
		}
		
		move_uploaded_file($tempFile, $targetFile); //6
		chmod( $targetFile ); 
	}

?>