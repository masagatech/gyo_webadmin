	<!--
    <div class="alert alert-warning alert-white rounded">
    	<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
    	<div class="icon"><i class="fa fa-warning"></i></div>
    	<strong>Notice Board!</strong> <?php //echo $gnrl->getSettings('NOTICE_BOARD'); ?>
    </div>
    -->
    <div id="response">
        <?php

		$msg = isset( $_REQUEST['msg'] ) ? $_REQUEST['msg'] : ( isset( $_SESSION['msg'] ) ? $_SESSION['msg'] : "" );
		$succ = isset( $_REQUEST['succ'] ) ? $_REQUEST['succ'] : ( isset( $_SESSION['succ'] ) ? $_SESSION['succ'] : "" );
		

        if( $msg != "" && isset( $mess[ $msg ] ) && trim( $mess[ $msg ] ) ){
            if( $succ == 1 ){
               
                echo $gnrl->getSuccessMsg( $mess[ $msg ] );
            } 
			else if( $succ == 0 ){
               
                echo $gnrl->getErrorMsg( $mess[ $msg ] );
            }
			$_SESSION['msg'] = "";
			$_SESSION['succ'] = "";
        }?>
    </div>
	
	<?php
	$no_image_span = '<span style="padding:25px;display:inline-block;">No Image</span>';
	?>
    