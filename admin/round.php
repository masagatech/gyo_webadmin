<?php 
include('includes/configuration.php');
$gnrl->check_login();

// _P($_REQUEST);
// exit;
	extract( $_POST );
	$page_title = "Manage Rounds";
	$page 	= "round";
	$table 	= "tbl_round";
	$title2 = 'Round';
	// $folder = 'brand';
	// $upload_path = UPLOAD_PATH.$folder.'/';

    
    
	$script = ( isset( $_REQUEST['script'] ) && ( $_REQUEST['script'] == 'add' || $_REQUEST['script'] == 'edit' ) ) ? $_REQUEST['script'] : "";
	
	## Insert Record in database starts
	if(isset($_REQUEST['submit_btn']) && $_REQUEST['submit_btn']=='Submit'){
		
		$ins = array(
			'v_name'  => $v_name,
			'l_data' => json_encode($l_data),
			'e_status' 	=> $e_status,
            'i_order'  => $i_order,
            'd_added' => date('Y-m-d H:i:s'),
            'd_modified' => date('Y-m-d H:i:s'),
		);
		
		$id = $dclass->insert( $table, $ins );
		$gnrl->redirectTo($page.".php?succ=1&msg=add");
	}

	## Delete Record from the database starts
	if(isset($_REQUEST['a']) && $_REQUEST['a']==3) {
		if(isset($_REQUEST['id']) && $_REQUEST['id']!="") {
			$id = $_REQUEST['id'];
			if($_REQUEST['chkaction'] == 'delete') {
                if(1){
                    $ins = array('i_delete'=>'1');
                    $dclass->update( $table, $ins, " id = '".$id."'");
                    $gnrl->redirectTo($page.".php?succ=1&msg=del");
                }else{
                    $gnrl->redirectTo($page.".php?succ=0&msg=not_auth");
                }
            }
            // make records active
            else if($_REQUEST['chkaction'] == 'active'){
                if(1){
                    $ins = array('e_status'=>'active');
                    $dclass->update( $table, $ins, " id = '".$id."'");
                    $gnrl->redirectTo($page.".php?succ=1&msg=multiact");
                }else{
                    $gnrl->redirectTo($page.".php?succ=0&msg=not_auth");
                }
            }
            // make records inactive
            else if($_REQUEST['chkaction'] == 'inactive'){
                if(1){
                    $ins = array( 'e_status' => 'inactive' );
                    $dclass->update( $table, $ins, " id = '".$id."'");
                    $gnrl->redirectTo($page.".php?succ=1&msg=multiinact");
                }else{
                    $gnrl->redirectTo($page.".php?succ=0&msg=not_auth");
                }
            }
            // make records active
            else if($_REQUEST['chkaction'] == 'delete_image'){
                $ins = array('v_image'=>'');
                $dclass->update($table,$ins," id='$id'");
                $gnrl->redirectTo($page.".php?succ=1&msg=multiact");
            }
			
		}	
	}
	
	## Edit Process
	if(isset($_REQUEST['a']) && $_REQUEST['a']==2) {
		if(isset($_REQUEST['id']) && $_REQUEST['id']!="") {
			$id = $_REQUEST['id'];
			if( isset( $_REQUEST['submit_btn'] ) && $_REQUEST['submit_btn'] == 'Update' ) {
				
				if(empty($l_data['buzz_time'])){
					$l_data['buzz_time']='1';
				}
				$ins = array(
                    'v_name'  => $v_name,
                    'l_data' => json_encode($l_data),
                    'e_status'  => $e_status,
                    'i_order'  => $i_order,
                    'd_modified' => date('Y-m-d H:i:s'),
                );
				$dclass->update( $table, $ins, " id = '".$id."' ");
				$gnrl->redirectTo($page.'.php?succ=1&msg=edit&a=2&script=edit&id='.$_REQUEST['id']);
			}
			else {
				$row = $dclass->select('*',$table," AND id = '".$id."'");
				$row = $row[0];
                extract( $row );
                $l_data = json_decode( $l_data, true );
				// _p( $l_data );
			}
		}
	}
	
	
	$l_data_entities = array(  
        'premium_driver' => 'Premium Driver (Value = 0:will not check, 1:will check)',
		'lowest_trip' => 'Lowest Trip (Trip Count)',
		'max_dry_run' => 'Max Dry Run (In Km)',
		'nearest' => 'Nearest',
		'already_offered' => 'Already Offered',
		'rating' => 'Rating',
    );
	

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
                                <?php echo $script ? ucfirst( $script ).' '.ucfirst( $title2 ) : 'List Of '.' '.ucfirst( $title2 ); ?> 
                                <?php if( !$script ){?>
	                                <?php if( !$script && 1){?>
                                        <a href="<?php echo $page?>.php?script=add" class="fright">
                                            <button class="btn btn-primary" type="button">Add</button>
                                        </a>
                                    <?php } ?>

                                <?php 
                                    if(isset($_REQUEST['keyword']) && $_REQUEST['keyword'] != ''){ ?>
										<a href="<?php echo $page ?>.php" class="fright" >
		                                    <button class="btn btn-primary" type="button">Clear Search</button>
		                                </a>
	                                <?php }
	                                ?>
								<?php } ?>
                            </h3>
                        </div>
                        <?php 
                        if( ($script == 'add' || $script == 'edit') && 1 ){?>
                        	<form role="form" action="#" method="post" parsley-validate novalidate enctype="multipart/form-data" >
                                
								<div class="content">
									<div class="form-group">
										<label>Name</label>
										<input type="text" class="form-control" id="v_name" name="v_name" value="<?php echo $v_name; ?>" required />
									</div>
									<div class="form-group">
										<label>Order</label>
										<input type="text" class="form-control" id="i_order" name="i_order" value="<?php echo $i_order; ?>" required />
									</div>
									<div class="form-group">
										<label>Status</label>
										<select class="select2" name="e_status" id="e_status">
											<?php $gnrl->getDropdownList(array('active','inactive'),$e_status); ?>
										</select>
									</div>
									
									
									<div class="row" >
										<div class="col-md-12" >
											<h3>Round Buzz Time (Only Even and Positive Numbers )</h3>
											<div class="col-md-12" >
												<div class="form-group">
													<input type="number" min="1" class="form-control" id="" name="l_data[buzz_time]" value="<?php echo $l_data['buzz_time'];?>" />
												</div>
											</div> 
										</div>
									</div>

									<div class="row" >
										<div class="col-md-12" >
											<h3>Send Buzz Counter </h3>
											<div class="col-md-12" >
												<div class="form-group">
													<input type="number" min="1" class="form-control" id="" name="l_data[buzz_count]" value="<?php echo $l_data['buzz_count'];?>" />
												</div>
											</div> 
										</div>
									</div>
									
									
									<div class="row" >
										<div class="col-md-12" >
											<h3>Round Entities</h3>
											<?php 
											foreach( $l_data_entities as $d_key => $d_value ){ 
												$entityName = 'l_data[entity]['.$d_key.']';
												$entityValues = $l_data['entity'][$d_key];
												?>
												<div class="col-md-12" >
													<div class="form-group">
														<h4><?php echo $d_value;?></h4>
														<div class="col-md-6" >
															<div class="form-group">
																<label>Check Order? [0 - will not check] [> 0 - will check]</label>
																<input type="text" class="form-control" name="<?php echo $entityName;?>[check]" value="<?php echo $entityValues['check']?>" />
															</div>
														</div>
														<div class="col-md-6" >
															<div class="form-group">
																<label>Value</label>
																<input type="text" class="form-control" name="<?php echo $entityName;?>[value]" value="<?php echo $entityValues['value']?>" />
															</div>
														</div>
														
													</div>
												</div> <?php 
											} ?>
										</div>
									</div>
									
									<div class="form-group">
										<button class="btn btn-primary" type="submit" name="submit_btn" value="<?php echo ( $script == 'edit' ) ? 'Update' : 'Submit'; ?>"><?php echo ( $script == 'edit' ) ? 'Update' : 'Submit'; ?></button>
										<a href="<?php echo $page?>.php"><button class="btn fright" type="button" name="submit_btn">Cancel</button></a> 
									</div>
								</div>
                                    
							</form>
							
							<?php 
                        }
                        else{
							if( 1 ){
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
									$wh = " AND ( 
									   LOWER(v_name) like LOWER('%".$keyword."%')  OR
										LOWER(e_status) like LOWER('%".$keyword."%')
									)";
	                            }
	                            $checked="";
                                if( isset( $_REQUEST['deleted'] ) ){
                                    $wh .= " AND i_delete='1'";
                                    $checked="checked";
                                }else{
                                    $wh .= " AND i_delete='0'";
                                }
	                          	$ssql = "SELECT * FROM ".$table." WHERE true ".$wh;
	                               
	                            $sortby = $_REQUEST['sb'] = ( $_REQUEST['st'] ? $_REQUEST['sb'] : 'i_order' );
                                $sorttype = $_REQUEST['st'] = ( $_REQUEST['st'] ? $_REQUEST['st'] : 'ASC' );
	                            
	                            $nototal = $dclass->numRows($ssql);
	                            $pagen = new vmPageNav($nototal, $limitstart, $limit, $form ,"black");
	                            $sqltepm = $ssql." ORDER BY ".$sortby." ".$sorttype." OFFSET ".$limitstart." LIMIT ".$limit;
	                            $restepm = $dclass->query($sqltepm);
	                            $row_Data = $dclass->fetchResults($restepm);
	                            
	                            
	                            ?>
	                            <div class="content">
	                                <form name="frm" action="" method="get" >
	                                    <div class="table-responsive">
	                                    
	                                        <div class="row">
	                                            <div class="col-sm-12">
	                                                <div class="pull-right">
	                                                    <div class="dataTables_filter" id="datatable_filter">
	                                                        <label>
	                                                            <input type="text" aria-controls="datatable" class="form-control fleft" placeholder="Search" name="keyword" value="<?php echo isset( $_REQUEST['keyword'] ) ? $_REQUEST['keyword'] : ""?>" style="width:auto;"/>
	                                                            <button type="submit" class="btn btn-primary fleft" style="margin-left:0px;"><span class="fa fa-search"></span></button>
	                                                        </label>
	                                                    </div>
	                                                </div>
	                                                <div class="pull-left">
	                                                    <div id="datatable_length" class="dataTables_length">
	                                                        <label><?php $pagen->writeLimitBox(); ?></label>
	                                                    </div>
	                                                </div>
	                                                <label style="margin: 20px 20px;">
                                                        <div class="clearfix"></div> 

                                                        <div class="pull-left" style="">
                                                            <input class="all_access" name="deleted" value=""  type="checkbox"  onclick="document.frm.submit();" <?php echo $checked; ?>>
                                                            Show Deleted Data
                                                        </div>
                                                    </label>
	                                                <div class="clearfix"></div>
	                                            </div>
	                                        </div>
	                                        
	                                        <!-- <?php chk_all('drop');?> -->
	                                        <table class="table table-bordered" id="datatable" style="width:100%;" >
	                                        	<?php
                                                echo $gnrl->renderTableHeader(array(
                                                    'v_name' => array( 'order' => 1, 'title' => 'Name' ),
                                                    'e_status' => array( 'order' => 1, 'title' => 'Status' ),
                                                    'i_order' => array( 'order' => 1, 'title' => 'Order' ),
                                                    'action' => array( 'order' => 0, 'title' => 'Action' ),
                                                ));
                                                ?> 
	                                            <tbody>
	                                                <?php 
	                                                if($nototal > 0){
	                                                    	$i=0;
	                                                    foreach($row_Data as $row){
	                                                    	$i++;
	                                                    	?>
	                                                        <tr>
																<td>
																	<?php echo $row['v_name'];?>
																</td>
	                                                            <td><?php echo $row['e_status'];?></td>
	                                                            <td><?php echo $row['i_order'];?></td>
	                                                            <td>
	                                                                <div class="btn-group">
	                                                                    <button class="btn btn-default btn-xs" type="button">Actions</button>
	                                                                    <button data-toggle="dropdown" class="btn btn-xs btn-primary dropdown-toggle" type="button">
	                                                                        <span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
	                                                                    </button>
	                                                                    <ul role="menu" class="dropdown-menu pull-right">
	                                                                        <li><a href="<?php echo $page?>.php?a=2&script=edit&id=<?php echo $row['id'];?>">Edit</a></li>
	                                                                        <li><a href="<?php echo $page;?>.php?a=3&amp;chkaction=active&amp;id=<?php echo $row['id'];?>">Active</a></li>
	                                                                        <li><a href="<?php echo $page;?>.php?a=3&amp;chkaction=inactive&amp;id=<?php echo $row['id'];?>">Inactive</a></li>
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
	                                </form>
	                            </div> 
							<?php }
                            else{ ?>
                                    
                            <?php 
                            }
                        }?>
                    </div>
                </div>
            </div>
        </div>
	</div>
</div>
<?php include('_scripts.php');?>
<?php include('jsfunctions/jsfunctions.php');?>
</body>
</html>
