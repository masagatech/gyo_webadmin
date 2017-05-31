<?php 
include('includes/configuration.php');
$gnrl->check_login();

// _P($_REQUEST);
// exit;
    extract( $_POST );
    $page_title = "Manage Admin";
    $page = "admin";
    $table = 'tbl_admin';
    $title2 = 'Admin';
    // $v_role ='user';
    $script = ( isset( $_REQUEST['script'] ) && ( $_REQUEST['script'] == 'add' || $_REQUEST['script'] == 'edit' ) ) ? $_REQUEST['script'] : "";
    
    ## Insert Record in database starts
    if(isset($_REQUEST['submit_btn']) && $_REQUEST['submit_btn']=='Submit'){
        // _P($_REQUEST);
        // exit;
        $email_exit = $dclass->select('*',$table," AND v_email = '".$v_email."'");
        
        if(count($email_exit) && !empty($email_exit)){
            
             $gnrl->redirectTo($page.".php?succ=0&script=add&msg=email_exit");
        }else{

            $ins = array(
                'v_name'  => $v_name,
                'v_email' =>$v_email,
                'v_phone'   => $v_phone,
                'v_password'  => $v_password ? md5($v_password):'',
                'v_username' => $v_username,
                'v_role'    =>$v_role,
                'i_city_id' => $i_city_id,
                'l_data' => json_encode($l_data),
                'e_status' => $e_status ,
                'd_added' => date('Y-m-d H:i:s'),
                'd_modified' => date('Y-m-d H:i:s')

            );

            // _P($ins);
            // exit;
            $id = $dclass->insert( $table, $ins );
            $gnrl->redirectTo($page.".php?succ=1&msg=add");
        }
		
	}

	## Delete Record from the database starts
	if(isset($_REQUEST['a']) && $_REQUEST['a']==3) {
		if(isset($_REQUEST['id']) && $_REQUEST['id']!="") {
			$id = $_REQUEST['id'];
			if($_REQUEST['chkaction'] == 'delete') {
				$ins = array('i_delete'=>'1');
                $dclass->update( $table, $ins, " id = '".$id."'");
				$gnrl->redirectTo($page.".php?succ=1&msg=del");
			}
			// make records active
			else if($_REQUEST['chkaction'] == 'active'){
				$ins = array('e_status'=>'active');
				$dclass->update( $table, $ins, " id = '".$id."'");
				$gnrl->redirectTo($page.".php?succ=1&msg=multiact");
			}
			// make records inactive
			else if($_REQUEST['chkaction'] == 'inactive'){
				$ins = array( 'e_status' => 'inactive' );
				$dclass->update( $table, $ins, " id = '".$id."'");
				$gnrl->redirectTo($page.".php?succ=1&msg=multiinact");
			}
		}	
	}
	
	## Edit Process
	if(isset($_REQUEST['a']) && $_REQUEST['a']==2) {
        // _P($_REQUEST);
        // exit;
		if(isset($_REQUEST['id']) && $_REQUEST['id']!="") {

			$id = $_REQUEST['id'];
			if( isset( $_REQUEST['submit_btn'] ) && $_REQUEST['submit_btn'] == 'Update' ) {
				
				// _p( $_REQUEST ); exit;
				
                $email_exit = $dclass->select('*',$table," AND id != ".$id." AND v_email = '".$v_email."'");
               
                if(count($email_exit) && !empty($email_exit)){
                     $gnrl->redirectTo($page.'.php?succ=0&msg=email_exit&a=2&script=edit&id='.$_REQUEST['id']);
                }else{

                    $ins = array();
                     if(!empty($v_password)){
                        #FOR CHECK PASSWORD
                         $check_pass = $dclass->select('*',$table," AND id = ".$id."");
                         if(count($check_pass)){
                            if(md5($v_password) == $check_pass[0]['v_password']){
                            }else{
                                $ins['v_password']= md5($v_password);
                            }
                         }
                     }
                    $ins['v_name'] = $v_name;
                    $ins['v_email'] = $v_email;
                    $ins['v_phone'] = $v_phone;
                    $ins['v_username'] = $v_username;
                    $ins['v_role'] = $v_role;
                    $ins['i_city_id'] = $i_city_id;
                    $ins['l_data'] = json_encode($l_data);
                    $ins['e_status'] = $e_status;
                    $ins['d_modified'] = date('Y-m-d H:i:s');
                    
                    // _P($ins);
                    // exit;
                    $dclass->update( $table, $ins, " id = '".$id."' ");
                    $gnrl->redirectTo($page.'.php?succ=1&msg=edit&a=2&script=edit&id='.$_REQUEST['id']);
                }
			}
			else {
				$row = $dclass->select('*',$table," AND id = '".$id."'");

				$row = $row[0];

                // _P($row);
                // exit;
				extract( $row );
                $l_data = json_decode( $l_data, true );
                $i_city_ids = $l_data['L_CITY'];
				$page_access = $l_data['pages'];

                $check_box_arr=array();
                if(count($l_data['pages'])){
                    foreach ($l_data['pages'] as $page_key => $page_value) {
                        $check_box_arr[]= $page_key;
                    }     
                }
			}
		}
	}
    # GET ALL CITY
    $cities = $dclass->select('*','tbl_city');
	


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
                                <a href="<?php echo $page?>.php?script=add" class="fright">
                                    <button class="btn btn-primary" type="button">Add <?php echo ' '.ucfirst( $title2 );?></button>
                                </a>
								<!--<a href="manage_ordering.php?type=brand" class="fright" >
                                    <button class="btn btn-primary" type="button">Manage Ordering</button>
                                </a>-->
								<?php } ?>
                               
                            </h3>
                        </div>
                        <?php 
                        if( $script == 'add' || $script == 'edit' ){
                                
                            ?>
                        	<form role="form" action="#" method="post" parsley-validate novalidate enctype="multipart/form-data" >
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="content">
                                            <div class="form-group">
                                                <label>Name</label>
                                                <input type="text" class="form-control" id="v_name" name="v_name" value="<?php echo $v_name; ?>" required />
                                            </div>
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input type="email" class="form-control" id="v_email" name="v_email" value="<?php echo $v_email; ?>" required />
                                            </div>
                                            <div class="form-group">
                                                <label>Username</label>
                                                <input type="text" class="form-control" id="v_username" name="v_username" value="<?php echo $v_username; ?>" required />
                                            </div>
                                            <div class="form-group">
                                                <label>Password</label>
                                                <?php 
                                                $required="";
                                                if($script=='add'){
                                                    $required='required';
                                                } ?>
                                                <input type="password" class="form-control" id="v_password" name="v_password" value="" <?php echo $required ?> />
                                            </div>
                                            <div class="form-group">
                                                <label>Phone</label>
                                                <input type="text" class="form-control" id="v_phone" name="v_phone" value="<?php echo $v_phone; ?>" required />
                                            </div>
                                             <div class="form-group">
                                                <label>City</label>
                                                <select class="select2" name="i_city_id" id="i_city_id">
                                                    <?php $gnrl->getCityDropdownList($i_city_id); ?>
                                                </select>
                                            </div>

                                             <div class="form-group">
                                                <label>Role</label>
                                                <select class="select2" name="v_role" id="v_role">
                                                    <?php echo $gnrl->get_keyval_drop($globalAdminRole,$v_role); ?>
                                                </select>
                                            </div>
											
											
											<h3>Select Cities</h3>
											<div class="row" style="margin-top:0; margin-bottom:0;" >
													<div class="form-group col-md-5">
														<label>All Cities</label>
														<?php $key = 'L_CITY_all'; ?>
														
														<select class="left_right" id="<?php echo $key;?>" name="<?php echo $key;?>[]" multiple >
															<?php
																foreach( $cities as $temp_row ){ 
																	if(!in_array($temp_row['id'], $i_city_ids)){ ?>
																	<option value="<?php echo $temp_row["id"]?>" >
																		<?php echo $temp_row["v_name"];?>
																	</option> 
																<?php } } ?>
														</select>
													</div>
													<?php $key = 'L_CITY'; ?>
													<div class="form-group col-md-2" style="text-align:center;" >
														<label>Actions</label>
														<div class="clear" style="height:10px;" ></div>
														<button class="btn btn-info" type="button" onClick="left_right( '<?php echo $key;?>', 'add' );" ><i class="fa fa-arrow-right"></i></button>
														<div class="clear" style="height:10px;" ></div>
														<button class="btn btn-info" type="button" onClick="left_right( '<?php echo $key;?>', '' );" ><i class="fa fa-arrow-left"></i></button>
													</div>
													<div class="form-group col-md-5">
														<label>Selected Cities</label>
														<select class="left_right" id="<?php echo $key;?>" name="l_data[<?php echo $key;?>][]" multiple >
														<?php
															foreach( $cities as $temp_row2 ){ 
																if(in_array($temp_row2['id'], $i_city_ids)){ ?>
																	<option value="<?php echo $temp_row2["id"]?>" selected="selected">
																		<?php echo $temp_row2["v_name"];?>
																	</option> 
																<?php } ?>
																<?php
															
														} ?>
														</select>
													</div>
											</div>
											
                                            <?php 
                                            $sectionArr = $gnrl->getSections();
											$page_arr = array();
											foreach( $sectionArr as $rowSection ){
												$page_arr[$rowSection['v_key']] = $rowSection['v_title'];
												if( $rowSection['childs'] ){
													foreach( $rowSection['childs'] as $rowSection2 ){
														$page_arr[$rowSection2['v_key']] = '&nbsp;&nbsp;&nbsp; &raquo; &nbsp;&nbsp;'.$rowSection2['v_title'];
													}
												}
											}
                                            ?>
											<div class="row" >
												<div class="col-md-12">
													<h3>
														Page Access
														(<input type="checkbox" value="1" onClick="if( this.checked ){ jQuery('.all_access').prop('checked',true); } else { jQuery('.all_access').prop('checked',false); }" /> Check All)
													</h3>
													<div class="row" >
														<div class="col-md-12">
															<table class="table table-bordered" id="datatable" style="width:100%;" >
																<thead>
																	<tr>
																		<th>Section Title</th>
																		<th colspan="5" >Access</th>
																	</tr>
																</thead>
																<tbody>
																	<?php 
																	foreach( $page_arr as $page_key => $page_title ){ ?>
																		<tr>
																			<td width="50%" ><strong><?php echo $page_title?></strong></td>
																			<?php foreach( $globalUserAction as $actionKey => $actionTitle ){ ?>
																			<td width="10%" >
																				<label>
																					<input class="all_access" name="l_data[pages][<?php echo $page_key;?>][]" value="<?php echo $actionKey;?>" type="checkbox" <?php echo in_array( $actionKey, $page_access[$page_key] ) ? 'checked' : ''?> />
																					<?php echo $actionTitle;?>
																				</label>
																			</td>
																			<?php } ?>
																		</tr> <?php
																	} ?>		
																</tbody>
															</table>
													   	</div>
													</div>
												</div>
											</div>
											


                                            <div class="form-group">
                                                <label>Status</label>
                                                <select class="select2" name="e_status" id="e_status">
                                                    <?php $gnrl->getDropdownList(array('active','inactive'),$e_status); ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <button class="btn btn-primary" type="submit" name="submit_btn" value="<?php echo ( $script == 'edit' ) ? 'Update' : 'Submit'; ?>"><?php echo ( $script == 'edit' ) ? 'Update' : 'Submit'; ?></button>
                                                <a href="<?php echo $page?>.php"><button class="btn fright" type="button" name="submit_btn">Cancel</button></a> 
                                            </div>
                                        </div>
                                    </div>
                                </div>
							</form>
							<?php 
                        }
                        else{
						
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
                                   LOWER(v_email) like LOWER('%".$keyword."%')  OR
                                   LOWER(v_role) like LOWER('%".$keyword."%')  OR
                                   LOWER(v_phone) like LOWER('%".$keyword."%')  OR
                                     LOWER(e_status) like LOWER('%".$keyword."%')
                                )";
                            }

                            if( isset( $_REQUEST['deleted'] ) ){
                                $wh .= " AND i_delete='1'";
                                $checked="checked";
                            }else{
                                $wh .= " AND i_delete='0'";
                            }
                            
                            $ssql = "SELECT * FROM ".$table." WHERE true".$wh;
                                        
                            $sortby = $_REQUEST['sb'] = ( $_REQUEST['st'] ? $_REQUEST['sb'] : 'v_name' );
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
                                                            <div class="clearfix"></div>
                                                                 <div class="pull-right" style="">
                                                                    <input class="all_access" name="deleted" value=""  type="checkbox"  onclick="document.frm.submit();" <?php echo $checked; ?>>
                                                                    Show Deleted Data
                                                            </div>
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
                                        
                                        <table class="table table-bordered" id="datatable" style="width:100%;" >
                                            <?php
                                                echo $gnrl->renderTableHeader(array(
                                                    'v_name' => array( 'order' => 1, 'title' => 'Name' ),
                                                    'v_email' => array( 'order' => 1, 'title' => 'Email' ),
                                                    'v_role' => array( 'order' => 1, 'title' => 'Role' ),
                                                    'v_phone' => array( 'order' => 1, 'title' => 'Phone' ),
                                                    'd_added' => array( 'order' => 1, 'title' => 'Added Date' ),
                                                    'e_status' => array( 'order' => 1, 'title' => 'Status' ),
                                                    'action' => array( 'order' => 0, 'title' => 'Action' ),
                                                ));
                                                ?>
                                            <tbody>
                                                <?php 
                                                if($nototal > 0){
                                                    	
                                                    foreach($row_Data as $row){
                                                    	
                                                    	?>
                                                        <tr>
                                                        	<td>
                                                                <?php echo $row['v_name']; ?>
                                                            </td>
                                                            <td><?php echo $row['v_email'];?></td>
                                                            <td><?php echo $row['v_role'];?></td>
                                                            <td><?php echo $row['v_phone'];?></td>
                                                            <td><?php echo $gnrl->displaySiteDate($row['d_added']) ; ?></td>
                                                            <td><?php echo $row['e_status'];?></td>
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
                            </div> <?php 
                        }
						?>
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
