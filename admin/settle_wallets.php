<?php 
include('includes/configuration.php');
$gnrl->check_login();
   
    extract( $_POST );
    $page_title = "Settle Driver Wallet";
    $page = "settle_wallets";
    $page2 = "driver_wallets";
    $table = 'tbl_wallet';
    $table2 = 'tbl_user';
    $table3 = 'tbl_wallet_transaction';
    $title2 = 'Settle Driver Wallet';
    // $v_role ='user';
    $script = ( isset( $_REQUEST['script'] ) && ( $_REQUEST['script'] == 'add' || $_REQUEST['script'] == 'edit'  ) ) ? $_REQUEST['script'] : "";
   
   
   // exit;
    ## Insert Record in database starts
    if(isset($_REQUEST['submit_btn']) && $_REQUEST['submit_btn']=='Submit'){
      
        $ssql 		= "SELECT * from ".$table." where id = ".$_REQUEST['id']." ";
        $restepm 	= $dclass->query( $ssql );
        $row 		= $dclass->fetchResults( $restepm );
        $row 		= $row[0];
		
        if( $row['f_amount'] > 0 ){
			$v_type 		= "company_paid";
			$f_receivable 	= 0;
			$f_payable 		= 0;
			$f_received 	= $_REQUEST['amount'];
			$f_amount 		= ( -1 * $f_received );
        }
		else if( $row['f_amount']  < 0 ){
			$v_type 		= "company_received";
			$f_receivable 	= 0;
			$f_payable 		= 0;
			$f_received 	= 0;
			$f_amount 		= $_REQUEST['amount'];
        }
		else{
            die('Already Settled Account.');
        }
		
		$ins = array(
			'i_wallet_id'  	=> $_REQUEST['id'],
			'i_user_id'  	=> $row['i_user_id'],
			'v_type' 		=> $v_type,
			'f_receivable'	=> $f_receivable,
			'f_payable'		=> $f_payable,
			'f_received'	=> $f_received,
			'f_amount'		=> $f_amount,
			'l_data'		=> json_encode( $_REQUEST['l_data'] ),
			'd_added' 		=> date('Y-m-d H:i:s'),
		);
		
		$id = $dclass->insert( $table3, $ins );
        
		$ssql = "UPDATE ".$table." SET f_amount = ( SELECT SUM( f_amount ) FROM ".$table3." WHERE i_wallet_id = ".$_REQUEST['id']." ) WHERE id = ".$_REQUEST['id']." ";
		$dclass->update_sql( $ssql );
		
		$gnrl->redirectTo($page2.".php?succ=1&msg=add_settle");
        
    }
    ## Edit Process
    if(isset($_REQUEST['a']) && $_REQUEST['a']==2) {
        if(isset($_REQUEST['id']) && $_REQUEST['id']!="") {

            $wallet_id=$_REQUEST['id'];
            // #GET WALLET DATA USING WALLET ID
            // $ssql3 = "SELECT * FROM ".$table." WHERE true AND id = ".$wallet_id."";
            // $restepm3 = $dclass->query($ssql3);
            // $wallet_Data = $dclass->fetchResults($restepm3);

            $ssql = "SELECT w.*,
                            u.v_name as driver_name
                          FROM ".$table." as w
                         LEFT JOIN ".$table2." 
                        as u ON w.i_user_id = u.id
                         WHERE true AND w.id=".$wallet_id." ";

            $restepm = $dclass->query($ssql);
            $row = $dclass->fetchResults($restepm);
            $row = $row[0];
            extract( $row );
             
        }
    }

    

?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include('_css.php');?>
</head>

<!-- Fixed navbar -->
<?php include('inc/header.php');?>
<div id="cl-wrapper" class="fixed-menu">
    <?php include('inc/sidebar.php'); ?>
    <div class="container-fluid" id="pcont">
        <?php include('all_page_head.php'); ?>

        <div class="cl-mcont">
            <?php include('all_alert_msg.php'); ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="block-flat">
                        <div class="header">
                            <h3>
                                <?php echo ucwords($title2);?>
                                <div class="pull-right">
                                    Total Amount = <mark><?php echo $f_amount; ?></mark> 
                                </div>
                            </h3>
                        </div>
                        
						<form role="form" action="#" method="post" parsley-validate novalidate enctype="multipart/form-data" >
							<div class="row">
								<div class="col-md-12 ">
									<div class="content">
									
										<div class="form-group">
											<h3>Driver Name : <?php echo $driver_name;?></h3>
										</div>
										<div class="form-group">
											<?php
											if( $f_amount > 0 ){
												echo '<h3>Driver Receivable Amount ('.$f_amount.') [Company Need To Pay Driver]</h3>';
											}
											else{
												echo '<h3>Driver Payable Amount ('.$f_amount.') [Driver Need To Pay Company]</h3>';
											}
											?>
										</div>
										<div class="form-group">
											<label>Paid Amount</label>
											<input type="text" class="form-control" id="amount" name="amount" value="" required />
										</div>
										<div class="form-group">
											<label>Information</label>
											<textarea class="form-control required" name="l_data[info]" required /></textarea>
										</div>
										<div class="form-group">
											<button class="btn btn-primary" type="submit" name="submit_btn" value="<?php echo ( $script == 'edit' ) ? 'Update' : 'Submit'; ?>"><?php echo ( $script == 'edit' ) ? 'Update' : 'Submit'; ?></button>
											<a href="<?php echo $page2?>.php"><button class="btn fright" type="button" name="submit_btn">Cancel</button></a> 
										</div>
									</div>
								</div>
							</div>
						</form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('_scripts.php');?>
<script type="text/javascript">
    function searchDriver(slug,val){
        window.document.location.href=window.location.pathname+'?'+slug+'='+val;
    }
</script>
<?php include('jsfunctions/jsfunctions.php');?>
</body>
</html>
