<?php 
	include('includes/configuration.php');
	$gnrl->check_login();
	$gnrl->isPageAccess(BASE_FILE);
	
	extract( $_POST );
	$page_title = "Manage Notification Template";
	$page = "notification_template";
	$table = 'tbl_push_notification';
	$title2 = 'Notification Template';
	$script = ( isset( $_REQUEST['script'] ) && ( $_REQUEST['script'] == 'add' || $_REQUEST['script'] == 'edit' ) ) ? $_REQUEST['script'] : "";
	
	## Insert Record in database starts
	if( isset( $_REQUEST['submit_btn'] ) && $_REQUEST['submit_btn'] == 'Submit' ){
		
		
		$j_title = json_encode( $j_title );
		$j_content = json_encode( $j_content );
		
		$ins = array(
			'v_name'    => $v_name,
			'j_title'   => $j_title,
            'j_content' => $j_content,
            'v_type'    => $v_type,
            'e_status'   => $e_status ,
            'd_added'   => date('Y-m-d H:i:s'),
            'd_modified' => date('Y-m-d H:i:s')
		);
		$id = $dclass->insert( $table, $ins );
		$gnrl->redirectTo($page.".php?succ=1&msg=add");

	}

	## Delete Record from the database starts
	if(isset($_REQUEST['a']) && $_REQUEST['a']==3) {
		if(isset($_REQUEST['id']) && $_REQUEST['id']!="") {
			$id = $_REQUEST['id'];
			if($_REQUEST['chkaction'] == 'delete') {
				if($gnrl->checkAction('delete') == '1'){
					$dclass->delete( $table ," id = '".$id."'");
					$gnrl->redirectTo($page.".php?succ=1&msg=del");
				}else{
					$gnrl->redirectTo($page.".php?succ=0&msg=not_auth");
				}
			}
			// make records active
			else if($_REQUEST['chkaction'] == 'active'){
				if($gnrl->checkAction('edit') == '1'){
					$ins = array('e_status'=>'active');
					$dclass->update( $table, $ins, " id = '".$id."'");
					$gnrl->redirectTo($page.".php?succ=1&msg=multiact");
				}else{
					$gnrl->redirectTo($page.".php?succ=0&msg=not_auth");
				}
				
			}
			// make records inactive
			else if($_REQUEST['chkaction'] == 'inactive'){
				if($gnrl->checkAction('edit') == '1'){
					$ins = array( 'e_status' => 'inactive' );
					$dclass->update( $table, $ins, " id = '".$id."'");
					$gnrl->redirectTo($page.".php?succ=1&msg=multiinact");
				}else{
					$gnrl->redirectTo($page.".php?succ=0&msg=not_auth");
				}
			}
		}	
	}
	
	## Edit Process
	if(isset($_REQUEST['a']) && $_REQUEST['a']==2) {
		if(isset($_REQUEST['id']) && $_REQUEST['id']!="") {

			$id = $_REQUEST['id'];
			if( isset( $_REQUEST['submit_btn'] ) && $_REQUEST['submit_btn'] == 'Update' ) {
				
				
				$j_title = json_encode( $j_title );
				$j_content = json_encode( $j_content );
		
				$ins = array(
                    'v_name'     => $v_name,
					'j_title'    => $j_title,
		            'j_content'  => $j_content,
		            'v_type'     => $v_type,
		            'e_status'   => $e_status ,
		            'd_added'    => date('Y-m-d H:i:s'),
		            'd_modified' => date('Y-m-d H:i:s')
                );
				$dclass->update( $table, $ins, " id = '".$id."' ");
				$gnrl->redirectTo($page.'.php?succ=1&msg=edit&a=2&script=edit&id='.$_REQUEST['id']);

			}
			else {
				$row = $dclass->select('*',$table," AND id = '".$id."'");
				$row = $row[0];
				extract( $row );
			}
		}
	}	

?>
<!DOCTYPE html>
<html lang="en">
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
                                <?php echo $script ? ucfirst( $script ).' '.ucfirst( $title2 ) : 'List of '.' '.ucfirst( $title2 ).''; ?> 
                                <?php if( !$script ){?>
                               		<?php if( !$script && $gnrl->checkAction('add') == '1'){?>
                                        <a href="<?php echo $page?>.php?script=add" class="fright">
                                            <button class="btn btn-primary" type="button">Add <?php echo ' '.ucfirst( $title2 );?></button>
                                        </a>
                                    <?php } ?>
								<?php } ?>
                            </h3>
                        </div>
                        <?php 
                        if( ($script == 'add' || $script == 'edit') &&  $gnrl->checkAction($script) == '1' ){?>
                        	<form role="form" action="#" method="post" parsley-validate novalidate enctype="multipart/form-data" >
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="content">
											
											<div class="row" >
												<div class="col-md-12">
													<a href="javascript:;" class="md-trigger fright" data-modal="form-primary">
														<span class="label label-primary">Keywords</span>
													</a>
												</div>
											</div>
											
										
											<div class="row" >

												<div class="col-md-12">
													<div class="form-group">
														<label>Name</label>
														<?php $key = 'v_name';?>
														<input type="text" class="form-control" name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $$key?>" required />
													</div>	
												</div>

												<div class="col-md-12">
													<div class="form-group">
														<label>Assign Notification</label>
														<?php $key = 'v_type';?>
														<select class="select2" name="<?php echo $key;?>" id="<?php echo $key;?>" >
															<option value="" >- Select -</option>
															<?php echo $gnrl->get_keyval_drop($globNotificationTypes,$$key); ?>
														</select>
													</div>
												</div>
												
												<div class="col-md-12">
													<?php
													$valArr = json_decode( $j_title, true );
													foreach( $globLangArr as $_langK => $_langV ){ 
														$key = 'j_title';?>
													
														<div class="form-group"> 
															<label>Title (<?php echo $_langV?>)</label>
															<input type="text" class="form-control" name="<?php echo $key;?>[<?php echo $_langK?>]" value="<?php echo $valArr[$_langK];?>" required />
														</div>
														 <?php
													} ?>
												</div>
												
												<div class="col-md-12">
													<?php
													$valArr = json_decode( $j_content, true );
													foreach( $globLangArr as $_langK => $_langV ){ 
														$key = 'j_content';?>
														<div class="form-group"> 
															<label>Body (<?php echo $_langV?>)</label>
															<textarea name="<?php echo $key;?>[<?php echo $_langK?>]" class="form-control"><?php echo $valArr[$_langK];?></textarea>
														</div> <?php
													} ?>
												</div>
												
												<div class="col-md-12">
													<div class="form-group">
														<label>Status</label>
														<select class="select2" name="e_status" id="e_status">
															<?php $gnrl->getDropdownList(array('active','inactive'),$e_status); ?>
														</select>
													</div>
												</div>
												
												<div class="col-md-12">
													<div class="form-group">
														<button class="btn btn-primary" type="submit" name="submit_btn" value="<?php echo ( $script == 'edit' ) ? 'Update' : 'Submit'; ?>"><?php echo ( $script == 'edit' ) ? 'Update' : 'Submit'; ?></button>
														<a href="<?php echo $page?>.php"><button class="btn fright" type="button" name="submit_btn">Cancel</button></a> 
													</div>
												</div>
												
											</div>
										
                                           
                                        </div>
                                    </div>
                                </div>
							</form>
							<?php 
                        }
                        else{
							if($gnrl->checkAction($script) == '1'){
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
	                                   LOWER(v_type) like LOWER('%".$keyword."%')  OR
	                                   LOWER(e_status) like LOWER('%".$keyword."%') 
	                                )";
	                            }
	                            
	                           	$ssql = "SELECT * FROM ".$table." WHERE true ".$wh;
	                                        
	                            $sortby = ( isset( $_REQUEST['sb'] ) && $_REQUEST['sb'] != '') ? $_REQUEST['sb'] : 'id';
	                            $sorttype = ( isset( $_REQUEST['st'] ) && $_REQUEST['st']=='0') ? 'ASC' : 'DESC';
	                            
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
	                                                    <?php 
						                                    if(isset($_REQUEST['keyword']) && $_REQUEST['keyword'] != ''){ ?>
						                                    	<a href="<?php echo $page ?>.php" class="fright" style="margin: -10px 0px 20px 0px ;" > Clear Search </a>
						                                <?php } ?>
	                                                </div>
	                                                <div class="pull-left">
	                                                    <div id="datatable_length" class="dataTables_length">
	                                                        <label><?php $pagen->writeLimitBox(); ?></label>
	                                                    </div>
	                                                </div>
	                                                <div class="clearfix"></div>
	                                            </div>
	                                        </div>
	                                        
	                                        <!-- <?php chk_all('drop');?> -->
	                                        <table class="table table-bordered" id="datatable" style="width:100%;" >
	                                            <thead>
	                                                <tr>
														<th>Name</th>
														<th>Notification Type</th>
	                                                    <th>Added Date</th>
	                                                    <th>Status</th>
	                                                    <th><span class="pull-right">Action</span>
	                                                </tr>
	                                            </thead>
	                                            <tbody>
	                                                <?php 
	                                                if($nototal > 0){
	                                                    	$i=0;
	                                                    foreach($row_Data as $row){
	                                                    	$i++;
	                                                    	?>
	                                                        <tr>
																<td>	
																	<a href="<?php echo $page?>.php?a=2&script=edit&id=<?php echo $row['id'];?>">
																		<?php echo $row['v_name'];?>
																	</a>
																</td>
																<td>
																	<?php //echo $globNotificationTypes[$row['v_type']];?>
																	<!--<br>-->
																	<?php echo $row['v_type'];?>
																</td>
																<?php 
	                                                            	$d_added = substr($row['d_added'], 0, strpos($row['d_added'], "+"));
	                                                            ?>
	                                                            <td><?php echo $d_added; ?></td>
	                                                            <td><?php echo $row['e_status'];?></td>
	                                                            <td class="text-right" >
	                                                             	<?php 
	                                                             		if($gnrl->checkAction('edit') == '1'){?>
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
	                                                             		<?php }?>
	                                                                
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
	                        <?php
                            }else{ ?>
                                    <h3>
                                        <a href="<?php echo $page?>.php" class="fright">
                                            <button class="btn btn-primary" type="button">Back</button>
                                        </a>
                                    </h3>
                                    <h2 class="text-danger">You Have Not Permission to Access this Section.</h2>
                        	<?php 
                        	}
                        }?>
                    </div>
                </div>
            </div>
        </div>
	</div>
</div>

<?php include('_email_keywords.php');?>
<?php include('_scripts.php');?>
<?php include('jsfunctions/jsfunctions.php');?>

</body>
</html>
