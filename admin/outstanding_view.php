<?php 
include('includes/configuration.php');
$gnrl->check_login();



	extract( $_POST );
	$page_title = "Manage Outstanding View";
	$page = "outstanding_view";
	$page2 = "track";
	$table = 'tbl_ride';

	$script = ( isset( $_REQUEST['script'] ) && ( $_REQUEST['script'] == 'add' || $_REQUEST['script'] == 'edit' || $_REQUEST['script'] == 'force_close' || $_REQUEST['script'] == 'citywise' ) ) ? $_REQUEST['script'] : "";
	
	## Edit Process
	if(isset($_REQUEST['a']) && $_REQUEST['a']==2) {
		if(isset($_REQUEST['id']) && $_REQUEST['id']!="") {

			$id = $_REQUEST['id'];
			
			$ssql = "SELECT 
						wt.*,
						u.v_id as driver_id,
						u.v_name as driver_name,
						wt.l_data->> 'ride_code' as ride_code ,
						u.v_name as driver_name,
						r.i_user_id as user_id

					FROM 
						tbl_wallet_transaction wt
					LEFT JOIN tbl_user as u 
						ON wt.i_user_id = u.id
					LEFT JOIN tbl_wallet as w 
						ON w.id = wt.i_wallet_id
					LEFT JOIN tbl_ride as r 
						ON r.v_ride_code = wt.l_data->> 'ride_code'
					WHERE 
						true 
					AND w.v_wallet_type = 'money' AND wt.i_user_id= '".$id."'   ";
			
			$restepm = $dclass->query( $ssql );
			$_SESSION['report_query'][$page] = $ssql;
			$row = $dclass->fetchResults( $restepm );
		}
	}
	

?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include('_css.php');?>
</head>
<body>

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
                            	<?php if( $script == 'edit' ){ ?>
                            		View Transaction
								<?php } else { ?>
									<?php echo $page_title; ?>
								<?php } ?>
								<a href="reports.php?page=<?php echo $page; ?>&page_title=<?php echo $page_title; ?> " class="fright btn_reports">
									<button class="btn btn-primary" type="button">Export Excel </button>
								</a>
								<a href="table.php?page=<?php echo $page; ?>&page_title=<?php echo $page_title; ?> " class="fright btn_reports">
									<button class="btn btn-primary" type="button">Export PDF </button>
								</a>

                            </h3>
                        </div>
                        <?php 
                        if( ($script == 'add' || $script == 'edit') && 1 ){?>
							<style>
								.viewtable th{ background:#EEE; }
							</style>
						
	                        	<div class="content">
									<form name="frm" action="" method="get" >
										<div class="table-responsive">
											<div class="row">
												<div class="col-sm-12">
													<table class="table table-bordered" id="datatable" style="width:100%;" >
														
														<?php 
														$columnArr = array(
															'no' => array( 'order' => 1, 'title' => 'No' ),
															'tx_date' => array( 'order' => 1, 'title' => 'Tx Date' ),
															'driver_id' => array( 'order' => 1, 'title' => 'Driver ID' ),
															'driver_name' => array( 'order' => 1, 'title' => 'Driver Name' ),
															'particular' => array( 'order' => 1, 'title' => 'Particular' ),
															'ride_id' => array( 'order' => 1, 'title' => 'Ride ID'),
															'cutomer_name' => array( 'order' => 1, 'title' => 'Customer Name' ),
															'payment_method' => array( 'order' => 1, 'title' => 'Payment Method' ),
															'debit' => array( 'order' => 1, 'title' => 'Debit' ),
															'credit' => array( 'order' => 1, 'title' => 'Credit' ),
															'outstanding' => array( 'order' => 1, 'title' => 'Outstanding' ),
															'action' => array( 'order' => 0, 'title' => 'Action' ),
														);
														if( $_REQUEST['col'] == 1 ){
															$columnArr['custom'] = array( 'order' => 0, 'title' => 'Custom' );
														}
														echo $gnrl->renderTableHeader($columnArr);
														?>
														<tbody>
															<?php 
															if( !empty($row) ){
																$i = 0;
																foreach( $row as $row ){
																	$row['l_data'] = json_decode( $row['l_data'], true );
																	$i++;
																	?>
																	<tr>
																		<td><?php echo $i;?></td>
																		<td><?php echo $gnrl->displaySiteDate($row['d_added']) ; ?></td>
																		<td><?php echo $row['driver_id'];?></td>
																		<td><?php echo $row['driver_name']; ?></td>
																		<td><?php echo " - "; ?></td>
																		<td><?php echo $row['ride_code']; ?></td>
																		<td><?php echo " - "; ?></td>
																		<td><?php echo " - " ;?></td>
																		<td><?php echo " - ";?></td>
																		<td><?php echo " - ";?></td>
																		<td><?php echo " - ";?></td>
																		<td class="text-right" >
																			<div class="btn-group">
																			</div>
																		</td>
																	</tr><?php 
																}
															}
															else{?>
																<tr><td colspan="8">No Record found.</td></tr><?php 
															}?>
														</tbody>
													</table>
												</div>
											</div>
										</div>
									</form>
								</div>
							<?php 
                        }else{
                        }?>
                    </div>
                </div>
            </div>
        </div>
	</div>
</div>
<div class="md-modal colored-header  md-effect-9" id="form-location" >
        <div class="md-content">
            <div class="modal-header">
                <h3>View On Map</h3>
                <button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body form"  >
                <div id="dvMap" style="width: 590px; height: 300px"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
            </div>
        </div>
</div>
<div class="md-overlay"></div>
<?php include('_scripts.php');?>
<script>

// START DATE END DATE VALIDATION
function datetimepicker(){

    var startdate = $('#d_start_date').val();
    var enddate = $('#d_end_date').val();
    $("#d_start_date").datetimepicker('setEndDate', enddate);
    $("#d_end_date").datetimepicker('setStartDate', startdate);

}
</script>
<?php include('jsfunctions/jsfunctions.php');?>

</body>
</html>
