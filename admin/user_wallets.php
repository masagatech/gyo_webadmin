<?php 
include('includes/configuration.php');
$gnrl->check_login();

// _P($_REQUEST);
// exit;
    extract( $_POST );
    $page_title = "Manage User Wallets";
    $page = "user_wallets";
    $table = 'tbl_wallet';
    $table2 = 'tbl_wallet_transaction';
    $title2 = 'User Wallet';
    // $v_role ='user';
    $script = ( isset( $_REQUEST['script'] ) && ( $_REQUEST['script'] == 'add' || $_REQUEST['script'] == 'edit' || $_REQUEST['script'] == 'view' || $_REQUEST['script'] == 'manual' ) ) ? $_REQUEST['script'] : "";
    
    if( isset( $_REQUEST['submit_btn'] ) && $_REQUEST['submit_btn'] == 'manual_submit' ){
		
        $i_wallet_id = $_REQUEST['id'];
        
		## Sum of all transaction 
        $ssql 			= "SELECT * from ".$table." where id = ".$i_wallet_id." ";
        $restepm 		= $dclass->query($ssql);
        $wallet_data 	= $dclass->fetchResults($restepm);
        $wallet_data	= $wallet_data[0];
        // _p( $wallet_data ); exit;
        
        if( $amount < 0 ){
            
        } 
		else if( $amount > 0 ){
            
        }
		else{
            $gnrl->redirectTo( $page.".php?succ=0&msg=wallet_error&a=2&script=manual&id=".$_REQUEST['id'] );
        }
        $ins = array(
            'i_user_id' 	=> $wallet_data['i_user_id'],
            'v_type' 		=> 'custom',
            'f_amount'		=> $amount,
            'l_data'		=> json_encode( $l_data ),
            'd_added' 		=> date('Y-m-d H:i:s'),
            'i_wallet_id' 	=> $i_wallet_id,
        );
        $id = $dclass->insert( $table2, $ins );
		
		##Sum of all transaction 
		$ssql 		= "SELECT SUM( f_amount ) as TOTAL from ".$table2." where i_wallet_id = ".$i_wallet_id." ";
		$restepm 	= $dclass->query($ssql);
		$row 		= $dclass->fetchResults($restepm);
		$row 		= $row[0];
		
		## update the wallet
		$ssql2 		= "UPDATE ".$table." SET f_amount = ".$row['total']." WHERE id = ".$i_wallet_id." ";
		$restepm2 	= $dclass->update_sql( $ssql2 );
		
		#get user data
		$user_info = $dclass->select( '*', 'tbl_user', " AND id = '".$wallet_data['i_user_id']."' " );
		$user_info = $user_info[0];
		
		$isSMSSend = $gnrl->_SMS( array(
			'_to' 			=> $user_info['v_phone'],
			'_key' 			=> 'user_manual_wallet_update',
			'_body' 		=> '',
			'_user_id' 		=> $user_info['id'],
			'_user_lang' 	=> $user_info['lang'],
			'_replace_arr' 	=> array(
				'[user_name]' => $user_info['v_name'],
				'[free_text]' => $l_data['description'],
			),
		) );
		
		$isSendEmail = $gnrl->_EMAIL( array(
			'_to' 			=> $user_info['v_email'],
			'_key' 			=> 'user_manual_wallet_update',
			'_subject' 		=> '',
			'_body' 		=> '',
			'_user_id' 		=> $user_info['id'],
			'_user_lang' 	=> $user_info['lang'],
			'_replace_arr' 	=> array(
				'[user_name]' => $user_info['v_name'],
				'[free_text]' => $l_data['description'],
			),
		) );
		
		$gnrl->redirectTo($page.".php?succ=1&msg=wallet_upd");
            
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
            }
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
                                <?php 
                                    if($script=='view'){
                                        echo "View User Transaction";
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
                                <?php 
                                    if(isset($_REQUEST['keyword']) && $_REQUEST['keyword'] != '' || isset($_REQUEST['driver']) && $_REQUEST['driver'] != ''){ ?>
                                        <a href="<?php echo $page ?>.php" class="fright" >
                                            <button class="btn btn-primary" type="button">Clear Search</button>
                                        </a>
                                <?php } ?>
                            </h3>
                        </div>
                        <?php 
                        if( ($script == 'add' || $script == 'edit') && 1 ){
                           
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
 
                                    WHERE true AND t1.i_wallet_id =".$_REQUEST['id']." ".$wh;

                            $sortby = $_REQUEST['sb'] = ( $_REQUEST['st'] ? $_REQUEST['sb'] : 't1.d_added' );
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
                                                    't2.v_name' => array( 'order' => 1, 'title' => 'Name' ),
                                                    't1.v_type' => array( 'order' => 1, 'title' => 'Type' ),
                                                    't1.f_amount' => array( 'order' => 1, 'title' => 'Amount' ),
                                                    't1.d_added' => array( 'order' => 1, 'title' => 'Date<br> Time' ),
                                                    'action' => array( 'order' => 0, 'title' => 'Action' ),
                                                ));
                                            ?> 
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
                                                           
                                                             <td><?php echo $gnrl->displaySiteDate($row['d_added']) ; ?></td>
                                                            
                                                            <td>
                                                                <div class="btn-group">
                                                                    <button class="btn btn-default btn-xs" type="button">Actions</button>
                                                                    <button data-toggle="dropdown" class="btn btn-xs btn-primary dropdown-toggle" type="button">
                                                                        <span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                                                                    </button>
                                                                    <!-- <ul role="menu" class="dropdown-menu pull-right">
                                                                        <li><a href="<?php echo $page?>.php?a=2&script=edit&id=<?php echo $row['id'];?>">Edit</a></li>
                                                                        <li><a href="<?php echo $page;?>.php?a=3&amp;chkaction=active&amp;id=<?php echo $row['id'];?>">Active</a></li>
                                                                        <li><a href="<?php echo $page;?>.php?a=3&amp;chkaction=inactive&amp;id=<?php echo $row['id'];?>">Inactive</a></li>
                                                                        <li><a href="javascript:;" onclick="confirm_delete('<?php echo $page;?>','<?php echo $row['id'];?>');">Delete</a></li>
                                                                    </ul> -->
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
                        }elseif( ($script == 'manual') && 1 ){
                            // $row = $dclass->select('*','tbl_user'," AND id = '".$id."'");
                            // $row = $row[0];
                            // extract( $row );
                            $ssql = "SELECT t1.*,
                                        t2.v_name as user_name
                                    FROM 
                                        tbl_wallet  t1
                                    LEFT JOIN tbl_user as t2 ON t1.i_user_id = t2.id
 
                                     WHERE true AND t1.id=".$_REQUEST['id']." ".$wh;
                            $restepm = $dclass->query($ssql);
                            $user_data = $dclass->fetchResults($restepm);
                            extract($user_data[0]);
                            
                        ?>
                           
                                        
                        <form role="form" action="#" method="post" parsley-validate novalidate enctype="multipart/form-data" >
                            <div class="row">
                                <div class="col-md-6 ">
                                    <div class="content">
                                        <div class="form-group">
                                            <label>Driver Name</label>
                                            <input type="text" class="form-control" id="v_name" name="v_name" value="<?php echo $user_name; ?>" readOnly="" />
                                        </div>
                                        <div class="form-group">
                                            <label> Amount</label>
                                            <input type="text"  pattern="\d" title="Only digits" class="form-control" id="amount" name="amount" value="" required />
                                        </div>
                                        <div class="form-group">
                                            <label> Description</label>
                                            <textarea class="form-control" name="l_data[description]"></textarea>
                                        </div>
                                       
                                        <div class="form-group">
                                            <button class="btn btn-primary" type="submit" name="submit_btn" value="manual_submit">Submit</button>
                                            <a href="<?php echo $page?>.php"><button class="btn fright" type="button" name="submit_btn">Cancel</button></a> 
                                        </div>
                                    </div>
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
                                    $wh .= " AND ( 
                                       LOWER(v_name) like LOWER('%".$keyword."%')  OR
                                       LOWER(v_email) like LOWER('%".$keyword."%')  OR
                                       LOWER(v_role) like LOWER('%".$keyword."%')  OR
                                       LOWER(v_phone) like LOWER('%".$keyword."%')  OR
                                       LOWER(e_status) like LOWER('%".$keyword."%')
                                    )";
                                }
                                if( isset( $_REQUEST['deleted'] ) ){
                                    $keyword =  trim( $_REQUEST['keyword'] );
                                    $wh .= " AND t1.i_delete='1'";
                                    $checked="checked";
                                }else{
                                    $wh .= " AND t1.i_delete='0'";
                                }
                                if( isset( $_REQUEST['filter_wallet_type'] ) && $_REQUEST['filter_wallet_type'] != '' ){
                                    $keyword =  trim( $_REQUEST['filter_wallet_type'] );
                                    $wh .= " AND t1.v_wallet_type = '".$keyword."'";
                                }
                                

                                $ssql = "SELECT t1.*,
                                            t2.v_name as user_name
                                        FROM 
                                            ".$table."  t1
                                        LEFT JOIN tbl_user as t2 ON t1.i_user_id = t2.id
     
                                         WHERE true AND t1.v_type='user' ".$wh;
                                            
                                $sortby = $_REQUEST['sb'] = ( $_REQUEST['st'] ? $_REQUEST['sb'] : 't2.v_name' );
                                $sorttype = $_REQUEST['st'] = ( $_REQUEST['st'] ? $_REQUEST['st'] : 'ASC' );
                                
                                $nototal = $dclass->numRows($ssql);
                                $pagen = new vmPageNav($nototal, $limitstart, $limit, $form ,"black");
                                $sqltepm = $ssql." ORDER BY ".$sortby." ".$sorttype." OFFSET ".$limitstart." LIMIT ".$limit;
                                $restepm = $dclass->query($sqltepm);
                                $row_Data = $dclass->fetchResults($restepm);

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
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="pull-left">
                                                        <div id="datatable_length" class="dataTables_length">
                                                            <label><?php $pagen->writeLimitBox(); ?></label>
                                                        </div>
                                                    </div>
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
                                                                <td><?php echo $row['user_name'];?></td>
                                                                <td><?php echo $row['v_wallet_type'];?></td>
                                                                <td><?php echo $row['f_amount'];?></td>
                                                                <td>
                                                                    <div class="btn-group pull-right">
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
                                                                                    <li><a href="<?php echo $page?>.php?a=2&script=view&id=<?php echo $row['id'];?>"  target="_blank">View Transaction</a></li>
                                                                                    <li><a href="<?php echo $page?>.php?a=2&script=manual&id=<?php echo $row['id'];?>" target="_blank">Manual adjustment</a></li>
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
