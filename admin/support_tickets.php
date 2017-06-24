<?php 
include('includes/configuration.php');
$gnrl->check_login();
// _P($_REQUEST);
// exit;
	extract( $_POST );
	$page_title = "Manage Support Tickets";
	$page = "support_tickets";
	$table = 'tbl_support_ticket';
	$title2 = 'Manage Support Ticket';
	// $v_role ='user';
	$script = ( isset( $_REQUEST['script'] ) && ( $_REQUEST['script'] == 'add' || $_REQUEST['script'] == 'edit' ) ) ? $_REQUEST['script'] : "";
	
	
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
			// make records restore
	        if($_REQUEST['chkaction'] == 'restore') {
	            $ins = array('i_delete'=>'0');
	            $dclass->update( $table, $ins, " id = '".$id."'");
	            $gnrl->redirectTo($page.".php?succ=1&msg=del");
	        }
			// make records active
			else if($_REQUEST['chkaction'] == 'pending' || $_REQUEST['chkaction'] == 'resolved'){
				if(1){
					$ins = array( 'e_status'=> $_REQUEST['chkaction'] );
					$dclass->update( $table, $ins, " id = '".$id."'");
					$gnrl->redirectTo($page.".php?succ=1&msg=multiact");
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
				
				$row = $dclass->select('*',$table," AND id = '".$id."'");
				$row = $row[0];
				$l_data = $row['l_data'] = json_decode( $row['l_data'], true );
				
				$user = $dclass->select( '*', 'tbl_user'," AND id = '".$row['i_user_id']."'");
				$user = $user[0];
				$user['l_data'] = json_decode( $user['l_data'], true );

				$isSendEmail = $gnrl->_EMAIL( array(
					'_to' 			=> $user['v_email'],
					'_key' 			=> 'ticket_resolved',
					'_subject' 		=> '',
					'_body' 		=> '',
					'_user_id' 		=> $user['id'],
					'_user_lang' 	=> $user['l_data']['lang'],					
					'_replace_arr' 	=> array(
						'[user_name]' 			=> $user['v_name'],
						'[support_inq_id]' 		=> $row['v_support_id'],
						'[support_inq_text]' 	=> $v_reply
					),
				) );
				
				$isSMSSend = $gnrl->_SMS( array(
					'_to' 			=> $user['v_phone'],
					'_key' 			=> 'ticket_resolved',
					'_body' 		=> '',
					'_user_id' 		=> $user['id'],
					'_user_lang' 	=> $user['l_data']['lang'],
					'_replace_arr' 	=> array(
						'[user_name]' 			=> $user['v_name'],
						'[support_inq_id]' 		=> $row['v_support_id'],
						'[support_inq_text]' 	=> $v_reply
					),
				) );
				
				$ins = array(
					" e_status = '".$e_status."' ",
					" d_modified = '".date('Y-m-d H:i:s')."' ",
					" l_data = l_data || '".json_encode(array(
						'v_reply' => $v_reply,
					))."' "
				);
				
				$dclass->updateJsonb( $table, $ins, " id = '".$id."' ");
				
				$gnrl->redirectTo($page.'.php?succ=1&msg=edit&a=2&script=edit&id='.$_REQUEST['id']);
			}
			else {
				$row = $dclass->select('*',$table," AND id = '".$id."'");
				$row = $row[0];
                extract( $row );
				$l_data = json_decode( $l_data, true );
				
				$user = $dclass->select( '*', 'tbl_user'," AND id = '".$i_user_id."'");
				$user = $user[0];
				
				
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
                                <?php if( !$script ){
                               		    if( !$script && 1){?>
                                        <a href="<?php echo $page?>.php?script=add" class="fright">
                                            <button class="btn btn-primary" type="button">Add New</button>
                                        </a>
                                    <?php } 
                                    }
                                ?>
                            </h3>

                        </div>
                        <?php 
                        if( ( $script == 'add' || $script == 'edit' ) && 1 ){?>
                        	<form role="form" action="#" method="post" parsley-validate novalidate enctype="multipart/form-data" >
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="content">
										
											<div class="row" >
												
												<div class="col-md-12">
													<table class="table table-bordered viewtable" id="datatable" style="width:100%;">
														<thead>
															<tr>
																<th width="20%"><strong>Field</strong></th>
																<th width="80%"><strong>Data</strong></th>
															</tr>
														</thead>
														<tbody>
															<tr>
																<td>Ticket ID</td><td><?php echo $v_support_id?></td>
															</tr>
															<tr>
																<td>Ticket Type</td><td><?php echo $v_type == 'faq' ? 'FAQ' : 'Support Type'?></td>
															</tr>
															<tr>
																<td>Ticket Title</td><td><?php echo $l_data['j_title']?></td>
															</tr>
															<tr>
																<td>Ticket Text</td><td><?php echo $l_data['j_text']?></td>
															</tr>
															
															<tr>
																<td>User Name</td><td><?php echo $user['v_name']?></td>
															</tr>
															<tr>
																<td>User Email</td><td><?php echo $user['v_email']?></td>
															</tr>
															<tr>
																<td>User Phone</td><td><?php echo $user['v_phone']?></td>
															</tr>
															<tr>
																<td>User TEXT</td><td><?php echo $l_data['v_support_text']?></td>
															</tr>											
														</tbody>
													</table>
												</div>
												
												<div class="col-md-12">
													<div class="form-group">
														<label>Reply Text <span>*</span></label>
														<textarea class="form-control" id="v_reply" name="v_reply" style="min-height:150px" required ><?php echo $l_data['v_reply'];?></textarea>
													</div>
												</div>
												
												<div class="col-md-12">
													<div class="form-group">
														<label>Status</label>
														<select class="select2" name="e_status" id="e_status">
															<?php $gnrl->getDropdownList(array('pending','resolved'),$e_status); ?>
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
							if(1){
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
										LOWER( v_support_id ) like LOWER('%".$keyword."%')  OR
	                                   LOWER( a.l_data->>'j_title' ) like LOWER('%".$keyword."%')  OR
	                                   LOWER( a.l_data->>'v_support_text' ) like LOWER('%".$keyword."%')  OR
	                                   LOWER( a.e_status ) like LOWER('%".$keyword."%')
	                                )";
	                            }
								 if( isset( $_REQUEST['srch_filter'] ) && $_REQUEST['srch_filter'] != '' ){
	                                $srch_filter =  trim( $_REQUEST['srch_filter'] );
									$wh = " AND ( 
									   LOWER( a.e_status ) like LOWER('%".$srch_filter."%')
	                                )";
	                            }
	                            
	                            $ssql = "SELECT a.*,
									b.v_name AS username
								FROM 
									".$table." a 
									LEFT JOIN tbl_user b ON b.id = a.i_user_id
								WHERE true ".$wh;
	                                        
	                            $sortby = $_REQUEST['sb'] = ( $_REQUEST['st'] ? $_REQUEST['sb'] : 'd_added' );
                            	$sorttype = $_REQUEST['st'] = ( $_REQUEST['st'] ? $_REQUEST['st'] : 'DESC' );
	                            
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
	                                                            <div class="clearfix"></div>
	                                                            
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
															<label style="margin-left:5px">Status 
																 <div class="clearfix"></div>
																	<div class="pull-left" style="">
																	<div>
																	<select class="select2" name="srch_filter" id="srch_filter" onChange="document.frm.submit();">
																			<option value="" >--Select--</option>
																			 <?php $gnrl->getDropdownList(array('pending','resolved'),$_GET['srch_filter']); ?>
																	</select>
																	</div>
																</div>
															</label>
														</div>
														
	                                                </div>
	                                                <div class="clearfix"></div>
	                                            </div>
	                                        </div>
	                                        <!-- <?php chk_all('drop');?> -->
	                                        <table class="table table-bordered" id="datatable" style="width:100%;" >
	                                        	 <?php
                                                echo $gnrl->renderTableHeader(array(
													"v_support_id" => array( 'order' => 1, 'title' => 'Ticket ID' ),
                                                    "b.v_name" => array( 'order' => 1, 'title' => 'User Name' ),
													"l_data->>'j_title'" => array( 'order' => 1, 'title' => 'Ticket Title' ),
                                                    "l_data->>'v_support_text'" => array( 'order' => 1, 'title' => 'Ticket Text' ),
                                                    'd_added' => array( 'order' => 1, 'title' => 'Added Date' ),
                                                    'e_status' => array( 'order' => 1, 'title' => 'Status' ),
                                                    'action' => array( 'order' => 0, 'title' => 'Action' ),
                                                ));
                                                ?>
	                                             
	                                            <tbody>
	                                                <?php 
	                                                if($nototal > 0){
	                                                    $i=0;
	                                                    foreach($row_Data as $row){
	                                                    	$i++;
															$row['l_data'] = json_decode( $row['l_data'], true );
	                                                    	?>
	                                                        <tr>
																<td><?php echo $row['v_support_id']; ?></td>
																<td><?php echo $row['username']; ?></td>
																<td><?php echo $row['l_data']['j_title']; ?></td>
																<td><?php echo $row['l_data']['v_support_text'];?></td>
	                                                            <td><?php echo $gnrl->displaySiteDate($row['d_added']) ; ?></td>
	                                                            <td><?php echo $row['e_status'];?></td>
	                                                            <td class="text-right" >
	                                                            	 <?php
                                                                         if(1){?>
	                                                                         <div class="btn-group">
		                                                                    <button class="btn btn-default btn-xs" type="button">Actions</button>
		                                                                    <button data-toggle="dropdown" class="btn btn-xs btn-primary dropdown-toggle" type="button">
		                                                                        <span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
		                                                                    </button>
		                                                                    <ul role="menu" class="dropdown-menu pull-right">

		                                                                    	<?php
		                                                                           if(isset($_REQUEST['deleted'])){ ?>
		                                                                                <li><a href="javascript:;" onclick="confirm_restore('<?php echo $page;?>','<?php echo $row['id'];?>');">Restore</a></li>
		                                                                            <?php  
		                                                                            }else{ ?>
		                                                                                <li><a href="<?php echo $page?>.php?a=2&script=edit&id=<?php echo $row['id'];?>">Edit</a></li>
		                                                                            <?php }
		                                                                        ?>
		                                                                        
		                                                                    </ul>
		                                                                </div>
                                                                        <?php } ?>
	                                                                
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
