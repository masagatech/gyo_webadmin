<?php 
include('includes/configuration.php');
$gnrl->check_login();

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
										
										$driverName = $dclass->select( '*', 'tbl_user', " AND id = ( SELECT i_user_id FROM tbl_wallet WHERE id = '".$_REQUEST['id']."' ) " );
										
                                        echo 'Wallet History Of Driver "'.$driverName[0]['v_name'].'"';
                                    }else{
                                        echo $script ? ucfirst( $script ).' '.ucfirst( $title2 ) : 'List Of '.' '.ucfirst( $title2 );
                                    }
                                ?>
                                <?php  ?> 
                                <?php if( !$script ){?>
                               <!--  <?php if( !$script && 1){?>
                                        <a href="<?php echo $page?>.php?script=add" class="fright">
                                            <button class="btn btn-primary" type="button">Add</button>
                                        </a>
                                    <?php } ?>  -->
                                
                                <?php } ?>
                               
                            </h3>
                        </div>
                        <?php 
                        if( ( $script == 'add' || $script == 'edit' ) && 1 ){
                           
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
                        }elseif ($script == 'view' && 1) {
                            
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
                                    v_type = '".$keyword."'
                                )";
                            }
                            
                            
                           $ssql = "SELECT * FROM tbl_wallet_transaction WHERE true AND i_wallet_id = ".$_REQUEST['id']." ".$wh;
						   

                            $sortby = $_REQUEST['sb'] = ( $_REQUEST['st'] ? $_REQUEST['sb'] : 'd_added' );
                            $sorttype = $_REQUEST['st'] = ( $_REQUEST['st'] ? $_REQUEST['st'] : 'DESC' );            
                            /*$sortby = ( isset( $_REQUEST['sb'] ) && $_REQUEST['sb'] != '') ? $_REQUEST['sb'] : 'id';
                            $sorttype = ( isset( $_REQUEST['st'] ) && $_REQUEST['st']=='0') ? 'ASC' : 'DESC';*/
                            
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
                                            <?php
                                                echo $gnrl->renderTableHeader(array(
                                                    'v_type' => array( 'order' => 1, 'title' => 'Type' ),
                                                    'f_receivable' => array( 'order' => 1, 'title' => 'Receivable' ),
													'f_payable' => array( 'order' => 1, 'title' => 'Payable' ),
													'f_received' => array( 'order' => 1, 'title' => 'Received' ),
													'f_amount' => array( 'order' => 1, 'title' => 'Amount' ),
													'l_data' => array( 'order' => 0, 'title' => 'Information' ),
													'd_added' => array( 'order' => 1, 'title' => 'Date' ),
                                                ));
                                            ?> 
                                            <tbody>
                                                <?php 
                                                if( $nototal > 0 ){
                                                    foreach( $row_Data as $row){
                                                        ?>
                                                        <tr>
                                                            <?php $l_data = json_decode( $row['l_data'], true );?>
                                                            <td>
																<?php echo $globalWalletActionTypes[$row['v_type']];?>
																<br>(<?php echo $row['v_type'];?>)
															</td>
                                                            <td><?php echo _price($row['f_receivable']);?></td>
															<td><?php echo _price($row['f_payable']);?></td>
															<td><?php echo _price($row['f_received']);?></td>
															<td><?php echo _price($row['f_amount']);?></td>
															<td>
																<?php
																if( in_array( $row['v_type'], array( 'ride_cancel', 'ride_dry_run', 'ride' ) ) ){ ?>
                                                                   
																	<a href="driver_trips.php?a=2&script=edit&v_ride_code=<?php echo $l_data['ride_code']; ?>" target="_blank" >Ride : <?php echo $l_data['ride_code']; ?></a>
																<?php }
																else{
																	echo $l_data['info'] ? nl2br( $l_data['info'] ) : '-';
																}?>
															</td>
															<td><?php echo $gnrl->displaySiteDate($row['d_added']) ; ?></td>
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
										
										<input type="hidden" name="id" value="<?php echo @$_REQUEST['id'];?>" />
										<input type="hidden" name="script" value="<?php echo @$_REQUEST['script'];?>" />
                                        <input type="hidden" name="a" value="<?php echo @$_REQUEST['a'];?>" />
                                        <input type="hidden" name="st" value="<?php echo @$_REQUEST['st'];?>" />
                                        <input type="hidden" name="sb" value="<?php echo @$_REQUEST['sb'];?>" />
                                        <input type="hidden" name="np" value="<?php //echo @$_SERVER['HTTP_REFERER'];?>" />
                                    </div>
                                </form>
                            </div> <?php 
                        }else{
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
                                       LOWER(v_email) like LOWER('%".$keyword."%')  OR
                                       LOWER(v_role) like LOWER('%".$keyword."%')  OR
                                       LOWER(v_phone) like LOWER('%".$keyword."%')  OR
                                         LOWER(e_status) like LOWER('%".$keyword."%')
                                    )";
                                }

                                if( isset( $_REQUEST['payable'] ) && $_REQUEST['payable'] != '' ){
                                    $keyword =  trim( $_REQUEST['payable'] );
                                    if($keyword=="payable"){
                                     $wh .=" AND t1.f_amount > 0 ";
                                    }else{
                                         $wh =" AND t1.f_amount < 0 ";
                                    }
                                }
                                if( isset( $_REQUEST['filter_driver'] ) && $_REQUEST['filter_driver'] != '' ){
                                    $keyword =  trim( $_REQUEST['filter_driver'] );
                                    $wh .= " AND t1.i_user_id = '".$keyword."'";
                                }
                                if( isset( $_REQUEST['filter_wallet_type'] ) && $_REQUEST['filter_wallet_type'] != '' ){
                                    $keyword =  trim( $_REQUEST['filter_wallet_type'] );
                                    $wh .= " AND t1.v_wallet_type = '".$keyword."'";
                                }
                                if( isset( $_REQUEST['deleted'] ) ){
                                    $keyword =  trim( $_REQUEST['keyword'] );
                                    $wh .= " AND t1.i_delete='1'";
                                    $checked="checked";
                                }else{
                                    $wh .= " AND t1.i_delete='0'";
                                }
                                $ssql = "SELECT t1.*,
                                            t2.v_name as user_name
                                        FROM 
                                            ".$table."  t1
                                        LEFT JOIN tbl_user as t2 ON t1.i_user_id = t2.id
     
                                         WHERE true AND t1.v_type='driver' ".$wh;
                                            
                                $sortby = $_REQUEST['sb'] = ( $_REQUEST['st'] ? $_REQUEST['sb'] : 't2.v_name' );
                                $sorttype = $_REQUEST['st'] = ( $_REQUEST['st'] ? $_REQUEST['st'] : 'ASC' );
                                
                                $nototal = $dclass->numRows($ssql);
                                $pagen = new vmPageNav($nototal, $limitstart, $limit, $form ,"black");
                                $sqltepm = $ssql." ORDER BY ".$sortby." ".$sorttype." OFFSET ".$limitstart." LIMIT ".$limit;
                                $restepm = $dclass->query($sqltepm);
                                $row_Data = $dclass->fetchResults($restepm);

                                #USE FOR DRIVER DROPDOWN MENU
                                $ssql2 = "SELECT id,v_name FROM tbl_user WHERE true AND v_role= 'driver' ORDER BY v_name ASC ";
                                $restepm2 = $dclass->query($ssql2);
                                $driver_Data = $dclass->fetchResults($restepm2);
                                $driver_name_arr=array();
                                foreach ($driver_Data as $d_key => $d_value) {
                                    $driver_name_arr[$d_value['id']]= $d_value['v_name'];
                                }

                                #USE FOR WALLET TYPE DROPDOWN MENU
                                $ssql3 = "SELECT id,v_name,v_key FROM tbl_wallet_types WHERE true AND i_delete = '0' ORDER BY v_name ASC ";
                                $restepm3 = $dclass->query($ssql3);
                                $wallet_type_Data = $dclass->fetchResults($restepm3);
                                $wallet_type_arr=array();
                                foreach ($wallet_type_Data as $w_key => $w_value) {
                                    $wallet_type_arr[$w_value['v_key']]= $w_value['v_name'];
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
                                                                <div class="clearfix"></div> 
                                                                <div class="pull-right" style="">
                                                                    <input class="all_access" name="deleted" value=""  type="checkbox"  onclick="document.frm.submit();" <?php echo $checked; ?>>
                                                                    Show Deleted Data
                                                                </div>
                                                                <div class="clearfix"></div> 
                                                                <?php 
                                                                    if(isset($_REQUEST['keyword']) && $_REQUEST['keyword'] != '' || isset($_REQUEST['filter_driver']) && $_REQUEST['filter_driver'] != '' || isset($_REQUEST['payable']) && $_REQUEST['payable'] != ''  ){ ?>
                                                                        <a href="<?php echo $page ?>.php" class="fright" style="margin: -10px 0px 20px 0px ;" >
                                                                            <h4> Clear Search </h4></a>
                                                                <?php } ?>
                                                            </label>

                                                        </div>
                                                    </div>
                                                    <div class="pull-left">
                                                        <div id="datatable_length" class="dataTables_length">
                                                            <label><?php $pagen->writeLimitBox(); ?></label>
                                                        </div>
                                                    </div>
                                                    <label style="margin-left:15px">Payeble/Receivable: 
                                                        <div class="clearfix"></div>
                                                        <div class="pull-left">
                                                            <div>
                                                             <select class="select2" name="payable" id="payable" onChange="document.frm.submit();">
                                                             <option value="">-- Select --</option>
                                                                     <?php  $gnrl->getDropdownList(array('payable','receivable'),$_GET['payable']); ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </label>
                                                    <label style="margin-left:15px">Driver wise : 
                                                         <div class="clearfix"></div>
                                                            <div class="pull-left" style="">
                                                            <div>
                                                             <select class="select2" name="filter_driver" id="filter_driver" onChange="document.frm.submit();">
                                                                    <option value="">--Select--</option>
                                                                     <?php echo $gnrl->get_keyval_drop($driver_name_arr,$_GET['filter_driver']); ?>
                                                                    </select>
                                                            </div>
                                                        </div>
                                                    </label>
                                                    <label style="margin-left:15px">Wallet Type : 
                                                         <div class="clearfix"></div>
                                                            <div class="pull-left" style="">
                                                            <div>
                                                             <select class="select2" name="filter_wallet_type" id="filter_wallet_type" onChange="document.frm.submit();">
                                                                    <option value="">--Select--</option>
                                                                     <?php echo $gnrl->get_keyval_drop($wallet_type_arr,$_GET['filter_wallet_type']); ?>
                                                                    </select>
                                                            </div>
                                                        </div>
                                                    </label>
                                                    <div class="clearfix"></div>
                                                </div>
                                            </div>
                                            
                                            <!-- <?php chk_all('drop');?> -->
                                            <table class="table table-bordered" id="datatable" style="width:100%;" >
                                                <?php
                                                
                                                echo $gnrl->renderTableHeader(array(
                                                    't2.v_name' => array( 'order' => 1, 'title' => 'Name' ),
                                                    't1.v_wallet_type' => array( 'order' => 1, 'title' => 'Wallet Type' ),
                                                    't1.f_amount' => array( 'order' => 1, 'title' => 'Amount' ),
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
                                                                    <?php echo $row['user_name']; ?>
                                                                </td>
                                                                <td><?php echo $row['v_wallet_type'];?></td>
                                                                <td><?php echo _price($row['f_amount']);?></td>
                                                                
                                                                <td>
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
                                                                                    <?php 
                                                                                        if($row['f_amount']!='0'){ ?>
                                                                                             <li><a href="<?php echo $page2?>.php?a=2&script=settle&id=<?php echo $row['id'];?>">Settle</a></li>
                                                                                        <?php }
                                                                                    ?>
                                                                                    <li><a href="<?php echo $page?>.php?a=2&script=view&id=<?php echo $row['id'];?>" target="_blank" >View Transaction</a></li>
                                                                                    <li><a href="<?php echo $page;?>.php?a=3&amp;chkaction=active&amp;id=<?php echo $row['id'];?>">Active</a></li>
                                                                                    <li><a href="<?php echo $page;?>.php?a=3&amp;chkaction=inactive&amp;id=<?php echo $row['id'];?>">Inactive</a></li>
                                                                                    <li><a href="javascript:;" onclick="confirm_delete('<?php echo $page;?>','<?php echo $row['id'];?>');">Delete</a></li>
                                                                                <?php }
                                                                            ?>
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

<?php include('jsfunctions/jsfunctions.php');?>
</body>
</html>
