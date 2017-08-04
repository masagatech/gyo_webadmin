<?php 
include('includes/configuration.php');
$gnrl->check_login();
require_once 'classes/PHPExcel/Classes/PHPExcel.php';
require_once 'classes/PHPExcel/Classes/PHPExcel/Writer/Excel2007.php';

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

	extract( $_POST );
	$page_title = "Upload Outstanding";
	$page = "upload_outstanding";
	$table = 'tbl_ride';
	$table2 = 'tbl_upload_outstanding_log';
	$table3 = 'tbl_wallet_transaction';
	$table4 = 'tbl_wallet';
	$folder = "upload_outstanding";
	$script = ( isset( $_REQUEST['script'] ) && ( $_REQUEST['script'] == 'add' || $_REQUEST['script'] == 'edit' || $_REQUEST['script'] == 'force_close' || $_REQUEST['script'] == 'citywise' ) ) ? $_REQUEST['script'] : "";
	
	## Insert Record in database starts
    if(isset($_REQUEST['submit_btn']) && $_REQUEST['submit_btn']=='Submit'){
        
        if(isset($_FILES['v_upload_file']) && $_FILES['v_upload_file']['name'] != ''){
            $dest = "uploads/".$folder."/";
            $file_name = $gnrl->removeChars( time().'-'.$_FILES['v_upload_file']['name'] ); 
            $FileType = pathinfo($file_name,PATHINFO_EXTENSION);
            if($FileType == "xlsx") {
                if( move_uploaded_file( $_FILES['v_upload_file']['tmp_name'], $dest.$file_name ) ){


		           
                	#read xls File
					try {

					    $inputFileType = PHPExcel_IOFactory::identify($dest.$file_name);
					    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
					    $objPHPExcel = $objReader->load($dest.$file_name);

					} catch(Exception $e) {

					    die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());

					}
					//  Get worksheet dimensions
					$sheet = $objPHPExcel->getSheet(0); 
					$highestRow = $sheet->getHighestDataRow(); 
					$highestColumn = $sheet->getHighestDataColumn();
					//  Loop through each row of the worksheet in turn
					$i=0;
					for ($row = 1; $row <= $highestRow; $row++){ 
					
					     // Read a row of data into an array
					    $rowData[] = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,NULL,TRUE,FALSE);
					    if($i != 0){

					    	#$rowData[$i][0][0] here 0 no. index is excel sheet index where 0 no. is driver id column 
						    $driver_id = $rowData[$i][0][0];
						    #$rowData[$i][0][17] here 17 no. index is excel sheet index where 17 no. is settelement column 
						    $settlement_amount = $rowData[$i][0][17];
						    #$rowData[$i][0][18] here 18 no. index is excel sheet index where 18 no. is transaction info column
						    $transaction_info = $rowData[$i][0][18];

						    $ssql = "SELECT 
						    			* 
						    		from tbl_user t1
										WHERE true 
									AND t1.v_id = '".$driver_id."'   ";
					
							$restepm = $dclass->query( $ssql );
							$user_data = $dclass->fetchResults( $restepm );
							$user_data=$user_data[0];

							if(isset($user_data) && !empty($user_data)){

								#wallet table data
								$ssql = "SELECT * from ".$table4." where i_user_id = ".$user_data['id']." AND v_wallet_type ='money' ";
						        $restepm = $dclass->query($ssql);
						        $wallet_data = $dclass->fetchResults($restepm);
						        $wallet_data=$wallet_data[0];

						        if(isset($wallet_data) && !empty($wallet_data)){
						        	//Wallet Transaction Table Entry
									$ins = array(
							            'i_user_id'  => $user_data['id'],
							            'v_type' => 'company_paid',
							            'f_amount'=> (-1 * $settlement_amount),
							            'd_added' => date('Y-m-d H:i:s'),
							            'l_data' => json_encode($transaction_info),
							            'f_receivable'=> '0',
							            'f_payable'=> '0',
							            'f_received'=> $settlement_amount,
							            'i_wallet_id' => $wallet_data['id'],
							        );
							        
							        $id = $dclass->insert( $table3, $ins );
							        if($id > 0){
							            ##Sum of all transaction 
							            $ssql = "SELECT SUM(f_amount) as TOTAL from ".$table3." where i_wallet_id = ".$wallet_data['id']." ";
							            $restepm = $dclass->query($ssql);
							            $transaction_sum = $dclass->fetchResults($restepm);
							            $transaction_sum = $transaction_sum[0];
							            
							            ## update the wallet
							          	$ssql2="UPDATE ".$table4." SET f_amount = ".$transaction_sum['total']." WHERE id = ".$wallet_data['id']." ";
							            $restepm2 = $dclass->update_sql($ssql2);
							            $_SESSION[$page]['success_msg'][] = "Successfully settle the wallet of driver ".$driver_id." ." ;
							        }
						        }else{
						        	$_SESSION[$page]['error_msg'][] = "This Driver ".$driver_id." wallet not available.so not settel amount of this driver." ;
						        	$gnrl->redirectTo($page.".php?succ=0&msg=wallet_unavailable");
						        }
							}else{
								$_SESSION[$page]['error_msg'][] = "This Driver ".$driver_id." is not available.so not settel amount of this driver." ;
								//$gnrl->redirectTo($page.".php?succ=0&msg=driver_unavailable");
							}


					    }
					    $i++;
					}
					$l_data = array();
					$l_data ['error']= $_SESSION['upload_outstanding']['error_msg'];
					$l_data ['success']= $_SESSION['upload_outstanding']['success_msg'];

					$ins = array(
		                'i_admin_id'  => $_SESSION['adminid'],
		                'v_ip_address' => $gnrl->getRealIpAddr(),
		                'v_upload_file'   => $file_name,
		                'v_status'  => 'done',
		                'd_added' => date('Y-m-d H:i:s'),
		                'l_data' => json_encode($l_data),
		            );
		            $id = $dclass->insert( $table2, $ins );
					$gnrl->redirectTo($page.".php");
                }
            }else{
                $gnrl->redirectTo($page.".php?succ=0&msg=xlstype");
            }
            
         }
        
        
    }
	## Edit Process
	if(isset($_REQUEST['a']) && $_REQUEST['a']==2) {
		if(isset($_REQUEST['id']) && $_REQUEST['id']!="") {
			
			$id = $_REQUEST['id'];
			
			$ssql = "SELECT 
						t1.*,
						t2.v_name as uploader_name
					FROM tbl_upload_outstanding_log t1

					LEFT JOIN tbl_admin t2
					 on t2.id= t1.i_admin_id
						WHERE 
					true 
					AND t1.id = '".$id."'   ";
			
			$restepm = $dclass->query( $ssql );
			$row = $dclass->fetchResults( $restepm );
			extract($row[0]);
			$l_data = json_decode($l_data,true);
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
                            <?php 
                            	if($script == 'edit'){?>
                            		<h3>View Uploaded Outstanding</h3>
                            	<?php }
                            ?>
                        </div>
                       
                        
					   
                        <?php 
                        if( ($script == 'add' || $script == 'edit') && 1 ){?>
							
                    		<style>
								.viewtable th{ background:#EEE; }
							</style>
						
                        	<form role="form" action="#" method="post" parsley-validate novalidate enctype="multipart/form-data" >
                               
							    <!-- Ride Information-->
								<div class="row">
                                    <div class="col-md-12">
										<table class="table table-bordered viewtable" style="width:100%;" >
											<tr><th class="text-center" colspan="2" ><h4><strong>Uploaded Information</strong></h4></th></tr>
											<tr><td width="20%" >Uploader Name</td><td width="80%" ><?php echo $uploader_name;?></td></tr>
											<tr><td>IP Address</td><td><?php echo $v_ip_address; ?></td></tr>
											<tr><td>Uploaded File</td><td><a href="uploads/upload_outstanding/<?php echo $v_upload_file; ?>" download=""> <?php echo $v_upload_file; ?></a></td></tr>
											<tr><td>Added Date</td><td><?php echo $gnrl->displaySiteDate( $d_added );?></td></tr>
											<tr><td>Transaction Info</td><td>
											<table class="table table-bordered viewtable" style="width:100%;" >
														<tr>
															<td width="50%" >Successfully Settled</td>
															<td width="50%" >
															<?php 
																if(isset($l_data['success']) && !empty($l_data['success'])){
																	foreach ($l_data['success'] as $key => $value) {
																		echo $value."</br>";
																	}
																}
															?>
															</td>
														</tr>
														<tr>
															<td>Error in Settled</td>
															<td width="50%" >
															<?php 
																if(isset($l_data['error']) && !empty($l_data['error'])){
																	foreach ($l_data['error'] as $key => $value) {
																		echo $value."</br>";
																	}
																}
															?>
															</td>
														</tr>
													</table>
											</td></tr>
											<tr><td>Status</td><td><?php echo $v_status; ?></td></tr>
											
                                		</table>
									</div>
								</div>
							</form>

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
							
							$ssql4="SELECT
										t1.*,
										t2.v_name as admin_name
									FROM tbl_upload_outstanding_log  t1

									left join  tbl_admin t2 
										on t2.id = t1.i_admin_id 
									WHERE true";

							$sortby = $_REQUEST['sb'] = ( $_REQUEST['st'] ? $_REQUEST['sb'] : 't1.id' );
							$sorttype = $_REQUEST['st'] = ( $_REQUEST['st'] ? $_REQUEST['st'] : 'DESC' );
							
							$nototal = $dclass->numRows( $ssql4 );
							$pagen = new vmPageNav( $nototal, $limitstart, $limit, $form ,"black" );

							$sqltepm = $ssql4." ORDER BY ".$sortby." ".$sorttype." OFFSET ".$limitstart." LIMIT ".$limit;
							$restepm = $dclass->query($sqltepm);
							$row_Data = $dclass->fetchResults($restepm);
							#For Report
							$_SESSION['report_query'][$page] = $sqltepm;
							
							?>
							<div class="row">
							   	<form role="form" action="" method="post" parsley-validate novalidate enctype="multipart/form-data" >
				                    <div class="col-sm-12 col-md-12">
				                        <div class="block-flat">
				                            <div class="header"><h3>Upload Outstanding Settlement File </h3></div>
				                            <div class="content">
												<div class="form-group"> 
				                                    <label>Upload File (Only ".XLSX" supported)</label>
				                                    <input class="form-control" type="file" id="v_upload_file" name="v_upload_file" style="height:auto;" >
				                                    <span id="file_error_span" style="color:#cc0000;display:none;" ></span>
				                                </div>
				                                
												
				                                <div class="form-group">
				                                    <button class="btn btn-primary" type="submit" name="submit_btn" value="Submit" >Submit</button>
				                                </div>
				                            </div>                            
				                        </div>
				                    </div>
				                    
				                </form>
	                        </div>
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
												'no' => array( 'order' => 1, 'title' => 'No' ),
												'admin_name' => array( 'order' => 1, 'title' => 'Name' ),
												'v_ip_address' => array( 'order' => 1, 'title' => 'IP Address' ),
												'v_upload_file' => array( 'order' => 1, 'title' => 'Uploaded File' ),
												'v_status' => array( 'order' => 1, 'title' => 'Status'),
												'd_added' => array( 'order' => 1, 'title' => 'Added Date' ),
												'action' => array( 'order' => 0, 'title' => 'Action' ),
											);
											
											echo $gnrl->renderTableHeader($columnArr);
											?>
											<tbody>
												<?php 
												if( $nototal > 0 ){
													$i = 0;
													foreach( $row_Data as $row ){
														$i++;
														?>
														<tr>
															<td><?php echo $i;?></td>
															<td><?php echo $row['admin_name'];?></td>
															<td><?php echo $row['v_ip_address']; ?></td>
															<td><a href="uploads/upload_outstanding/<?php echo $row['v_upload_file']; ?>" download=""> <?php echo $row['v_upload_file']; ?></a></td>
															<td><?php echo $row['v_status']; ?></td>
															<td><?php echo $row['d_added'];?></td>
															<td class="text-right" >
																<div class="btn-group">
																	<button class="btn btn-default btn-xs" type="button">Actions</button>
																	<button data-toggle="dropdown" class="btn btn-xs btn-primary dropdown-toggle" type="button">
																		<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
																	</button>
																	<ul role="menu" class="dropdown-menu pull-right">
																		<li><a href="upload_outstanding.php?a=2&script=edit&id=<?php echo $row['id'];?>" target="_blank">View</a></li>
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
