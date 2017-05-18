<?php 
include('includes/configuration.php');
$gnrl->check_login();
$gnrl->isPageAccess(BASE_FILE);
// _P($_REQUEST);
// exit;
	extract( $_POST );
	$page_title = "Manage Driver Wallets";
	$page = "driver_wallets";
    $page2 = "settle_wallets";
	$table = 'tbl_wallet';
	$title2 = 'Driver Wallet';
	// $v_role ='user';
	$script = ( isset( $_REQUEST['script'] ) && ( $_REQUEST['script'] == 'add' || $_REQUEST['script'] == 'edit' || $_REQUEST['script'] == 'view' ) ) ? $_REQUEST['script'] : "";
	
	## Insert Record in database starts
	if(isset($_REQUEST['submit_btn']) && $_REQUEST['submit_btn']=='Submit'){
		
        $email_exit = $dclass->select('*',$table," AND v_email = '".$v_email."'");
        
        if(count($email_exit) && !empty($email_exit)){
            
             $gnrl->redirectTo($page.".php?succ=0&script=add&msg=email_exit");
        }else{
            $ins = array(
                'v_name'  => $v_name,
                'v_email' =>$v_email,
                'v_phone'   => $v_phone,
                'v_password'  => $v_password ? md5($v_password):'',
                'v_role'=> $v_role,
                'e_status' => $e_status ,
                'd_added' => date('Y-m-d H:i:s'),
                'd_modified' => date('Y-m-d H:i:s')
            );
            $id = $dclass->insert( $table, $ins );
            $gnrl->redirectTo($page.".php?succ=1&msg=add");
        }
		
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
                    $ins['e_status'] = $e_status;
                    $ins['l_data'] = json_encode($l_data);
                    $ins['d_modified'] = date('Y-m-d H:i:s');

                    $dclass->update( $table, $ins, " id = '".$id."' ");
                    $gnrl->redirectTo($page.'.php?succ=1&msg=edit&a=2&script=edit&id='.$_REQUEST['id']);
                }
			}
			else {
				$row = $dclass->select('*',$table," AND id = '".$id."'");

				$row = $row[0];
				extract( $row );
                // $l_data=json_decode($l_data,true);
			}
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
                                <?php 
                                    if($script=='view'){
                                        echo "View Driver Transaction";
                                    }else{
                                        echo $script ? ucfirst( $script ).' '.ucfirst( $title2 ) : 'List Of '.' '.ucfirst( $title2 ).'s';
                                    }
                                ?>
                                <?php  ?> 
                                <?php if( !$script ){?>
                               <!--  <?php if( !$script && $gnrl->checkAction('add') == '1'){?>
                                        <a href="<?php echo $page?>.php?script=add" class="fright">
                                            <button class="btn btn-primary" type="button">Add</button>
                                        </a>
                                    <?php } ?>  -->
								
								<?php } ?>
                                <?php 
                                    if(isset($_REQUEST['keyword']) && $_REQUEST['keyword'] != '' || isset($_REQUEST['search_element']) && $_REQUEST['search_element'] != '' || isset($_REQUEST['driver']) && $_REQUEST['driver'] != ''  ){ ?>
                                        <a href="<?php echo $page ?>.php" class="fright" >
                                            <button class="btn btn-primary" type="button">Clear Search</button>
                                        </a>
                                <?php } ?>
                            </h3>
                        </div>
                        <?php 
                        if( ($script == 'add' || $script == 'edit') && $gnrl->checkAction($script) == '1' ){
                           
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
                                                <label>Phone</label>
                                                <input type="text" class="form-control" id="v_phone" name="v_phone" value="<?php echo $v_phone; ?>" required />
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
                                                <label>Image</label>
                                                <input type="file" class="form-control" style="height:auto;" id="v_image" name="v_image" value="<?php echo $v_image; ?>">
                                                 <img src="<?php echo 'assets/images/logo_image/'.$v_image; ?>" class="admin_uploaded_img" style="height:100px; width:100px;">
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
                        }elseif ($script == 'view' && $gnrl->checkAction($script) == '1') {
                            
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
                            
                           $ssql = "SELECT t1.*,
                                        t2.v_name as user_name
                                    FROM 
                                        tbl_wallet_transaction  t1
                                    LEFT JOIN tbl_user as t2 ON t1.i_user_id = t2.id
 
                                     WHERE true AND t1.i_user_id=".$_REQUEST['id']." ".$wh;
                                        
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
                                                    <th width="25%">Name</th>
                                                    <th width="10%">Type</th>
                                                    <th width="10%">Amount</th>
                                                    <th width="10%">Receivable<br>Amount</th>
                                                    <th width="10%">Payable<br>Amount</th>
                                                    <th width="10%">Received<br>Amount</th>
                                                    <th width="5%">Running<br>Balance</th>
                                                    <th width="5%">Date<br> Time</th>
                                                    <!-- <th width="14%"><span class="pull-right"></span></th> -->
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                if($nototal > 0){
                                                        
                                                    foreach($row_Data as $row){
                                                        
                                                        ?>
                                                        <tr>
                                                            <?php $l_data=json_decode($row['l_data'],true); ?>
                                                            <td><a href="javascript:;" onclick="rideInfo(<?php echo $l_data['ride_id']; ?>);"><?php echo $row['user_name']; ?></a></td>

                                                            <td><?php echo $row['v_type'];?></td>
                                                            <td><?php echo $row['f_amount'];?></td>
                                                            <td><?php echo $row['f_receivable'];?></td>
                                                            <td><?php echo $row['f_payable'];?></td>
                                                            <td><?php echo $row['f_received'];?></td>
                                                            <td><?php echo $row['f_running_balance'];?></td>
                                                             <td><?php echo $gnrl->removeTimezone($row['d_added']) ; ?></td>
                                                            
                                                            <td>
                                                                <!-- <div class="btn-group">
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
                                                                </div> -->
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
                        else{
						    if( $gnrl->checkAction($script) == '1' ){
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

                                if( isset( $_REQUEST['search_element'] ) && $_REQUEST['search_element'] != '' ){
                                    $keyword =  trim( $_REQUEST['search_element'] );
                                    if($keyword=="payeble"){
                                     $wh =" AND t1.f_amount > 0 ";
                                    }else{
                                         $wh =" AND t1.f_amount < 0 ";
                                    }
                                }
                                if( isset( $_REQUEST['driver'] ) && $_REQUEST['driver'] != '' ){
                                    $keyword =  trim( $_REQUEST['driver'] );
                                    $wh = " AND t1.i_user_id = '".$keyword."'";
                                }
                                $ssql = "SELECT t1.*,
                                            t2.v_name as user_name
                                        FROM 
                                            ".$table."  t1
                                        LEFT JOIN tbl_user as t2 ON t1.i_user_id = t2.id
     
                                         WHERE true AND t1.v_type='driver' ".$wh;
                                            
                                $sortby = ( isset( $_REQUEST['sb'] ) && $_REQUEST['sb'] != '') ? $_REQUEST['sb'] : 'id';
                                $sorttype = ( isset( $_REQUEST['st'] ) && $_REQUEST['st']=='0') ? 'ASC' : 'DESC';
                                
                                $nototal = $dclass->numRows($ssql);
                                $pagen = new vmPageNav($nototal, $limitstart, $limit, $form ,"black");
                                $sqltepm = $ssql." ORDER BY ".$sortby." ".$sorttype." OFFSET ".$limitstart." LIMIT ".$limit;
                                $restepm = $dclass->query($sqltepm);
                                $row_Data = $dclass->fetchResults($restepm);

                                #USE FOR DRIVER DROPDOWN MENU
                                $ssql2 = "SELECT id,v_name FROM tbl_user WHERE true AND v_role= 'driver' ORDER BY v_name ASC ";
                                $restepm2 = $dclass->query($ssql2);
                                $driver_Data = $dclass->fetchResults($restepm2);
                                foreach ($driver_Data as $d_key => $d_value) {
                                    $driver_name_arr[$d_value['id']]= $d_value['v_name'];
                                }
                               
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

                                                    <div class="pull-left" style="margin: 20px;">
                                                        <div>
                                                         <select class="select2" name="payeble_dr" id="payeble_dr" onChange="searchDriver('search_element',this.options[this.selectedIndex].value)">
                                                         <option value="">-- Select --</option>
                                                                 <?php  $gnrl->getDropdownList(array('payeble','receivable'),$_GET['search_element']); ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <label style="margin-left:15px">Driver wise : 
                                                         <div class="clearfix"></div>
                                                            <div class="pull-left" style="">
                                                            <div>
                                                             <select class="select2" name="driver_sel" id="driver_sel" onChange="searchDriverName(this.options[this.selectedIndex].value)">
                                                                    <option value="">--Select--</option>
                                                                     <?php echo $gnrl->get_keyval_drop($driver_name_arr,$_GET['driver']); ?>
                                                                    </select>
                                                            </div>
                                                        </div>
                                                    </label>
                                                    <div class="clearfix"></div>
                                                </div>
                                            </div>
                                            
                                            <!-- <?php chk_all('drop');?> -->
                                            <table class="table table-bordered" id="datatable" style="width:100%;" >
                                                <thead>
                                                    <tr>
                                                        <th width="45%">Name</th>
                                                        <th width="10%">Type</th>
                                                        <th width="5%">Amount</th>
                                                        <th width="14%"><span class="pull-right">Action</span></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    if($nototal > 0){
                                                            
                                                        foreach($row_Data as $row){
                                                            
                                                            ?>
                                                            <tr>
                                                                
                                                                <td><a href="<?php echo $page?>.php?a=2&script=view&id=<?php echo $row['i_user_id'];?>"><?php echo $row['user_name']; ?></a></td>

                                                                <td><?php echo $row['v_type'];?></td>
                                                                <td><?php echo $row['f_amount'];?></td>
                                                                
                                                                <td>
                                                                    <div class="btn-group">
                                                                        <button class="btn btn-default btn-xs" type="button">Actions</button>
                                                                        <button data-toggle="dropdown" class="btn btn-xs btn-primary dropdown-toggle" type="button">
                                                                            <span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                                                                        </button>
                                                                        <ul role="menu" class="dropdown-menu pull-right">
                                                                             <?php 
                                                                            if($row['f_amount']!='0'){ ?>
                                                                                 <li><a href="<?php echo $page2?>.php?a=2&script=settle&id=<?php echo $row['i_user_id'];?>">Settle</a></li>
                                                                            <?php }
                                                                            ?>
                                                                           
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
<div class="md-modal colored-header  md-effect-9" id="ride-info-modal" >
        <div class="md-content">
            <div class="modal-header">
                <h3>Ride Info</h3>
                <button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body form" style="max-height: 400px; overflow: auto;" >
                    <div id="rideInfoDisplay">
                        
                    </div>
                   
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
    <div class="md-overlay"></div>
<?php include('_scripts.php');?>
<script type="text/javascript">
    function searchDriver(slug,val){
        window.document.location.href=window.location.pathname+'?'+slug+'='+val;
    }
    function searchDriverName(val){
        window.document.location.href=window.location.pathname+'?driver='+val;
    }
</script>
<?php include('jsfunctions/jsfunctions.php');?>
</body>
</html>
