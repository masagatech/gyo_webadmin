<?php 
include('includes/configuration.php');
$gnrl->check_login();



	extract( $_POST );
	$page_title = "Manage Payment History";
	$page = "payment_history";
	$table = 'tbl_ride';
	$title2 = 'Payment History';
	$folder = 'vehicle_type';
	
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
							
							<?php 
                        }else{
							
							if ( isset( $_REQUEST['pageno'] ) && $_REQUEST['pageno'] != '' ){
								$limit = $_REQUEST['pageno'];
							}
							else{
								$limit = $gnrl->getSettings('RECORD_PER_PAGE');
							}
					
							$form = 'frm';
							
							if ( isset($_REQUEST['limitstart']) && $_REQUEST['limitstart'] != '' ){
								$limitstart = $_REQUEST['limitstart'];
							}
							else{
								$limitstart = 0;
							}
							
							$wh = '';
							if( isset( $_REQUEST['keyword'] ) && $_REQUEST['keyword'] != '' ){
								$keyword =  trim( $_REQUEST['keyword'] );
								$wh .= " AND ( 
								   LOWER(d.v_name) like LOWER('%".$keyword."%')  OR
								   LOWER(u.v_name) like LOWER('%".$keyword."%')  OR
								   LOWER(v.v_type) like LOWER('%".$keyword."%')  OR
								   LOWER(v.v_vehicle_number) like LOWER('%".$keyword."%')  OR
								   LOWER(a.e_status) like LOWER('%".$keyword."%') OR
								   LOWER(a.v_ride_code) like LOWER('%".$keyword."%') 
									 
								)";
							}


							if( isset( $_REQUEST['d_start_date'] ) && $_REQUEST['d_start_date'] != ''){
								if(isset($_REQUEST['d_end_date']) && $_REQUEST['d_end_date']){
									$end= $_REQUEST['d_end_date'];
								}else{
									$end=date('Y-m-d');
								}
								$start =  trim( $_REQUEST['d_start_date'] );
								// $wh .= " AND  a.d_time BETWEEN  '".$start."' AND  '".$end."' ";
							}else{
								$start =  date('Y-m-d');
								$end=date('Y-m-d');
							}

							if( isset( $_REQUEST['srch_driver'] ) && $_REQUEST['srch_driver'] != '' ){
								$keyword =  trim( $_REQUEST['srch_driver'] );
								$wh .= " AND a.i_driver_id = '".$keyword."'";
							}
							if( isset( $_REQUEST['srch_filter_city'] ) && $_REQUEST['srch_filter_city'] != '' ){
								$keyword =  trim( $_REQUEST['srch_filter_city'] );
								$wh .= " AND ( dr.i_city_id = '".$keyword."' ) ";
							}
							
							
							$ssql4="SELECT
										dr.id AS driver_id,
										dr.l_data->>'bank_info' AS bank_info,
										dr.v_id AS driver_v_id,
										dr.v_name AS driver_name,							
										( SELECT COUNT(id)  FROM tbl_ride WHERE true AND i_driver_id = dr.id ) AS total_ride,
										( SELECT COUNT(id)  FROM tbl_wallet_transaction WHERE true AND i_user_id = dr.id AND v_type = 'ride_cancel' ) AS ride_cancel ,
										( SELECT SUM(f_amount)  FROM tbl_wallet_transaction WHERE true AND i_user_id = dr.id AND ( v_type = 'company_paid' OR v_type = 'company_received' ) ) AS manual_adjustment,
										( SELECT SUM(f_amount)  FROM tbl_wallet_transaction WHERE true AND i_user_id = dr.id AND v_type = 'ride_dry_run' ) AS dry_run ,
										( SELECT SUM(f_amount)  FROM tbl_wallet_transaction WHERE true AND i_user_id = dr.id AND v_type = 'referral' ) AS referral,
										( SELECT SUM(f_amount)  FROM tbl_wallet_transaction WHERE true AND i_user_id = dr.id  ) AS total_fare,
										( SELECT SUM(f_amount)  FROM tbl_wallet_transaction WHERE true AND i_user_id = dr.id  ) AS wallet_transaction,
										( SELECT SUM(f_amount)  FROM tbl_wallet_transaction WHERE true AND i_user_id = dr.id AND v_type = 'company_paid' ) AS paid_by_goyo,
										( SELECT SUM(f_amount)  FROM tbl_wallet_transaction WHERE true AND i_user_id = dr.id AND v_type = 'company_received' ) AS goyo_cut
									FROM tbl_user dr 

									left join  tbl_wallet wlt 
										on wlt.i_user_id = dr.id 
									left join  tbl_wallet_transaction wlt_tr 
										on wlt_tr.i_wallet_id = wlt.id  
									left join  tbl_ride tblride 
										on tblride.i_driver_id = dr.id  

										WHERE true AND
									dr.v_role = 'driver' AND wlt.f_amount > 0 AND wlt.v_wallet_type = 'money' 
									AND tblride.d_time >=  '".$start." 00:00:00' AND tblride.d_time <= '".$end." 23:59:59'".$wh."
									group by dr.id ";

							
							$sortby = $_REQUEST['sb'] = ( $_REQUEST['st'] ? $_REQUEST['sb'] : 'dr.id' );
							$sorttype = $_REQUEST['st'] = ( $_REQUEST['st'] ? $_REQUEST['st'] : 'DESC' );
							
							$nototal = $dclass->numRows( $ssql4 );
							$pagen = new vmPageNav( $nototal, $limitstart, $limit, $form ,"black" );

							if($_REQUEST['D'] == '1'){
								echo $sqltepm = $ssql4." ORDER BY ".$sortby." ".$sorttype." OFFSET ".$limitstart." LIMIT ".$limit;					                            	
							}

							$sqltepm = $ssql4." ORDER BY ".$sortby." ".$sorttype." OFFSET ".$limitstart." LIMIT ".$limit;
							$restepm = $dclass->query($sqltepm);
							$row_Data = $dclass->fetchResults($restepm);
							#For Report
							$_SESSION['report_query'][$page] = $sqltepm;
							
							if($_REQUEST['D'] == '1'){
								_P($row_Data);
								exit;
							}
							
							

							#USE FOR DRIVER DROPDOWN MENU
							$ssql2 = "SELECT id,v_name FROM tbl_user WHERE true AND v_role= 'driver' ORDER BY v_name ASC";
							$restepm2 = $dclass->query($ssql2);
							$driver_Data = $dclass->fetchResults($restepm2);
							foreach ($driver_Data as $d_key => $d_value) {
								$driver_name_arr[$d_value['id']]= $d_value['v_name'];
							}

							## vehicle type dropdown array
							$vehicle_row = $dclass->select('*','tbl_vehicle_type', " ORDER BY v_name ");
							$vehicle_arr=array();
							foreach($vehicle_row as $key => $val){
								$vehicle_arr[$val['v_type']] =$val['v_name'];
							}
						   
							?>
							<div class="content">
								<form name="frm" action="" method="get" >
									<div class="table-responsive">
									
										<div class="row">
											<div class="col-sm-12">

												<div class="pull-right">
													<div class="dataTables_filter" id="datatable_filter">
														<label style="margin-top: 20px;">

															<input type="text" aria-controls="datatable" class="form-control fleft" placeholder="Search" name="keyword" value="<?php echo isset( $_REQUEST['keyword'] ) ? $_REQUEST['keyword'] : ""?>" style="width:auto;"/>
															<button type="submit" class="btn btn-primary fleft" style="margin-left:0px;"><span class="fa fa-search"></span></button>
															

														</label>

													</div>
												</div>
												
												<div class="pull-left">
													<div id="" class="dataTables_length">
														<label><?php $pagen->writeLimitBox(); ?></label>
													</div>
												</div>
											  	<label style="margin-left:15px;" >
													City 
													<div class="clearfix"></div> 
													<div class="pull-left" style="">
														<select class="select2" name="srch_filter_city" id="srch_filter_city" onChange="document.frm.submit();">
															<option value="">--Select--</option>
															<?php echo $gnrl->getCityDropdownList($_GET['srch_filter_city']); ?>
														</select>
													</div>
												</label>
												<label style="margin-left:15px; width:150px;" >
													Start Date
													<div class="clearfix"></div> 
													<div class="pull-left" style="">
														<div class="input-group date datetime" data-min-view="2" data-date-format="yyyy-mm-dd">
															<input class="form-control" type="date" id="d_start_date" name="d_start_date" value="<?php echo ($_REQUEST['d_start_date'])?$_REQUEST['d_start_date']:date('Y-m-d'); ?>" data-date-format="yyyy-mm-dd" readonly="" onChange="document.frm.submit();" placeholder="select">
															<span class="input-group-addon btn btn-primary"><span class="glyphicon glyphicon-th"></span></span>
														  </div>
													</div>
												</label>
											   
												<label style="margin-left:15px;width:150px;">
													End Date
													<div class="clearfix"></div> 
													<div class="pull-left" style="">
														<div class="input-group date datetime" data-min-view="2" data-date-format="yyyy-mm-dd">
															<input class="form-control" type="date" id="d_end_date" name="d_end_date"  value="<?php echo ($_REQUEST['d_end_date'])?$_REQUEST['d_end_date']:date('Y-m-d'); ?>" data-date-format="yyyy-mm-dd" readonly="" onclick="datetimepicker()" onChange="document.frm.submit();" placeholder="select">
															<span class="input-group-addon btn btn-primary"><span class="glyphicon glyphicon-th"></span></span>
														</div>
													</div>
												</label>
												<!-- <div class="clearfix"></div> -->

											</div>
										
										<table class="table table-bordered" id="datatable" style="width:100%;" >
											
											<?php 
											$columnArr = array(
												'dr.v_id' => array( 'order' => 1, 'title' => 'Driver ID' ),
												'c.v_name' => array( 'order' => 1, 'title' => 'Driver Name' ),
												'TOTAL_RIDE' => array( 'order' => 1, 'title' => 'Total Rides' ),
												'user_name' => array( 'order' => 1, 'title' => 'Total Cancelled Ride' ),
												'manual_adjustment' => array( 'order' => 1, 'title' => 'Manual Adjustment (Amt) '),
												'dry_run' => array( 'order' => 1, 'title' => 'Dry Run (Amt)' ),
												'referrel' => array( 'order' => 1, 'title' => 'Referral' ),
												'total_fare' => array( 'order' => 1, 'title' => 'Total Fare' ),
												// 'cash_received' => array( 'order' => 1, 'title' => 'Cash Received' ),
												'wallet_trans' => array( 'order' => 1, 'title' => 'Wallet Transaction' ),
												'paid_by_goyo' => array( 'order' => 1, 'title' => 'Paid By Goyo' ),
												'goyo_cut' => array( 'order' => 1, 'title' => 'Goyo Cut' ),
												// 'to_be' => array( 'order' => 1, 'title' => 'To be paid By Goyo' ),
												'bank_info' => array( 'order' => 1, 'title' => 'Bank Info' ),
												'action' => array( 'order' => 0, 'title' => 'Action' ),
											);
											if( $_REQUEST['col'] == 1 ){
												$columnArr['custom'] = array( 'order' => 0, 'title' => 'Custom' );
											}
											echo $gnrl->renderTableHeader($columnArr);
											?>
											<tbody>
												<?php 
												if( $nototal > 0 ){
													$i = 0;
													foreach( $row_Data as $row ){
														$row['l_data'] = json_decode( $row['l_data'], true );
														$i++;
														?>
														<tr>
															<td><?php echo $row['driver_v_id'];?></td>
															<td><?php echo $row['driver_name'];?></td>
															<td><?php echo $row['total_ride']; ?></td>
															<td><?php echo $row['ride_cancel']; ?></td>
															<td><?php echo _price($row['manual_adjustment']); ?></td>
															<td><?php echo _price($row['dry_run']);?></td>
															<td><?php echo _price($row['referral']);?></td>
															<td><?php echo _price($row['total_fare']);?></td>
															<!-- <td><?php //echo "-";?></td> -->
															<td><?php echo _price($row['wallet_transaction']);?></td>
															<td><?php echo _price($row['paid_by_goyo']);?></td>
															<td><?php echo _price($row['goyo_cut']);?></td>
															<!-- <td><?php //echo "-";?></td> -->
															<td>
																<?php 
																	if(isset($row['bank_info']) && !empty($row['bank_info'])){
																		$bank_info= json_decode($row['bank_info'],true);
																		if(!empty($bank_info)){
																			foreach ($bank_info as $b_key => $b_value) {
																				echo "<strong>".ucwords(str_replace('_', ' ', $b_key))."</strong> : ".$b_value;echo "</br>";
																			}
																		}
																	}else{
																		echo "-";
																	}
																?>
															</td>
															<td class="text-right" >
																<div class="btn-group">
																	<button class="btn btn-default btn-xs" type="button">Actions</button>
																	<button data-toggle="dropdown" class="btn btn-xs btn-primary dropdown-toggle" type="button">
																		<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
																	</button>
																	<ul role="menu" class="dropdown-menu pull-right">
																		<li><a href="outstanding_view.php?a=2&script=edit&id=<?php echo $row['driver_id'];?>" target="_blank">View</a></li>
																		<li><a href="javascript:;" onclick="confirm_delete('<?php echo $page;?>','<?php echo $row['id'];?>');">Delete</a></li>
																	</ul>
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
										<div class="row">
											<div class="col-sm-12">
												<div class="pull-left"> <?php echo $pagen->getPagesCounter();?> </div>
												<div class="pull-right">
													<div class="dataTables_paginate paging_bs_normal">
														<ul class="pagination">
															<?php $pagen->writePagesLinks(); ?>
														</ul>
													</div>
												</div>
												<div class="clearfix"></div>
											</div>
										</div>
										<input type="hidden" name="a" value="<?php echo @$_REQUEST['a'];?>" />
										<input type="hidden" name="st" value="<?php echo @$_REQUEST['st'];?>" />
										<input type="hidden" name="sb" value="<?php echo @$_REQUEST['sb'];?>" />
										<input type="hidden" name="np" value="<?php //echo @$_SERVER['HTTP_REFERER'];?>" />
									</div>
									</div>
								</form>
							</div>
						<?php 
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
