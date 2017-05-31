<?php 
include('includes/configuration.php');
$gnrl->check_login();
// _P($_REQUEST);
// exit;
	extract( $_POST );
	$page_title = "Manage Messages";
	$page = "messages";
	$table = 'tbl_messages';
    $table2 = 'tbl_track_messages';
	$title2 = 'Message';
	// $v_role ='user';
	$script = ( isset( $_REQUEST['script'] ) && ( $_REQUEST['script'] == 'add' || $_REQUEST['script'] == 'edit' || $_REQUEST['script'] == 'view' || $_REQUEST['script'] == 'send_msg' ) ) ? $_REQUEST['script'] : "";
	
	## Insert Record in database starts
	if(isset($_REQUEST['submit_btn']) && $_REQUEST['submit_btn']=='Submit'){

            $j_subject = str_replace( '\r', '', str_replace( '\n', '', json_encode( $j_subject ) ) );
            $j_message = str_replace( '\r', '', str_replace( '\n', '', json_encode( $j_message ) ) );
            $ins = array(
                'v_name'    => $v_name,
                'j_subject'  => $j_subject,
                'j_message'  => $j_message,
                'd_added'    => date('Y-m-d H:i:s'),
            );
            $id = $dclass->insert( $table, $ins );
            $gnrl->redirectTo($page.".php?succ=1&msg=add");

	}
    ## Insert Record in database starts
    if(isset($_REQUEST['send_msg_btn']) && $_REQUEST['send_msg_btn']=='Send'){
       
        // exit;
        $id =$_REQUEST['id'];
        $row = $dclass->select('*',$table," AND id = '".$id."'");
        $row = $row[0];
        // _P($row);
        // exit;
        // exit;
        extract( $row );
        $l_data =array();
        $l_data['name']= $v_name;
        $l_data['j_subject']= json_decode($j_subject,true);
        $l_data['j_message']= json_decode($j_message,true);
        $l_data['i_view']= 0;
        $l_data['lang']= 'en';
        foreach( $L_USER as $key => $value ){
            $user_row = $dclass->select('*','tbl_user'," AND id = '".$value."'");
            $user_row=$user_row[0];
            ##SEND SMS CODE
            // $url = 'http://sms.cell24x7.com:1111/mspProducerM/sendSMS?user=Goyo&pwd=goyo123&sender=GoYooo';
            $url = 'http://sms.cell24x7.com:1111/mspProducerM/sendSMS?user='.SMS_USERNAME.'&pwd='.SMS_PASSWORD.'&sender='.SMS_SENDERNAME.'';
            $url .= '&mt=2';
            $url .= '&mobile=8758857048';
            $url .= '&msg=Testing..';  //8758857048

            try{
                $is_send=$gnrl->sendSMS( $url );
                if((substr('MSP-9574118641-1494581847192-86034-MSP11', 0, 3) == 'MSP')){
                    $i_status='1';
                }else{
                    $i_status='0';
                }
            }
            catch( Exception $e ){
                _p($e);
            }
           
            $l_data=json_encode($l_data);
            $inc_arr[] = "('".$id."','".$value."','".$i_status."','".date('Y-m-d H:i:s')."','".$l_data."')";
        }

        
        $inc_str = implode(',',$inc_arr);
        $insert_str = "INSERT INTO ".$table2." (i_messages_id,i_user_id,i_status,d_added,l_data) VALUES ".$inc_str.";";
        
        $restepm = $dclass->query($insert_str);
        $row_Data = $dclass->fetchResults($restepm);
        $gnrl->redirectTo($page.".php?succ=1&msg=success_msg");
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

                $j_subject = json_encode( $j_subject );
                $j_subject = str_replace( '\r', '', $j_subject );
                $j_subject = str_replace( '\n', '', $j_subject );

                $j_message = json_encode( $j_message );
                $j_message = str_replace( '\r', '', $j_message );
                $j_message = str_replace( '\n', '', $j_message );

				$ins = array(
                    'v_name'   => $v_name,
                    'j_subject'  => $j_subject,
                    'j_message' =>$j_message,
                );
				$dclass->update( $table, $ins, " id = '".$id."' ");
				$gnrl->redirectTo($page.'.php?succ=1&msg=edit&a=2&script=edit&id='.$_REQUEST['id']);
			}
			else {
				$row = $dclass->select('*',$table," AND id = '".$id."'");
				$row = $row[0];
                extract( $row );
                $j_subject=json_decode($j_subject,true);
                $j_message=json_decode($j_message,true);
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
                                    if($script == 'send_msg'){
                                        echo "Send Message";
                                    }else{
                                        echo $script ? ucfirst( $script ).' '.ucfirst( $title2 ) : 'List Of '.' '.ucfirst( $title2 );
                                    }
                                ?>
                                <?php if( !$script ){?>
                                    <?php if( !$script && 1){?>
                                        <a href="<?php echo $page?>.php?script=add" class="fright">
                                            <button class="btn btn-primary" type="button">Add</button>
                                        </a>
                                    <?php } ?>
								<?php } ?>
                                  <?php 
                                    if(isset($_REQUEST['keyword']) && $_REQUEST['keyword'] != ''){ ?>
                                        <a href="<?php echo $page ?>.php" class="fright" >
                                            <button class="btn btn-primary" type="button">Clear Search</button>
                                        </a>
                                <?php } ?>
                            </h3>
                        </div>
                        <?php 
                        if( ($script == 'add' || $script == 'edit') && 1){ ?>
                            <form role="form" action="#" method="post" parsley-validate novalidate enctype="multipart/form-data" >
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="content">
                                            
                                               <div class="form-group">
                                                    <label>Name</label>
                                                    <input type="text" class="form-control" id="v_name" name="v_name" value="<?php echo $v_name ?>" required />
                                                </div>
                                                <?php
                                                    foreach( $globLangArr as $_langK => $_langV ){$key = 'j_subject';
                                                        ?>
                                                        <div class="form-group"> 
                                                            <label>Message Subject (<?php echo $_langV?>)</label>
                                                            <input name="<?php echo $key;?>[<?php echo $_langK?>]" class="form-control" value="<?php echo $j_subject[$_langK];?>" />
                                                        </div>
                                                         <?php
                                                } ?>
                                                <?php
                                                    foreach( $globLangArr as $_langK => $_langV ){
                                                        ?>
                                                        <?php $key = 'j_message'; ?>
                                                        <div class="form-group"> 
                                                            <label>Message Content (<?php echo $_langV?>)</label>
                                                            <textarea name="<?php echo $key;?>[<?php echo $_langK?>]" class="form-control"><?php echo $j_message[$_langK];?></textarea>
                                                        </div>
                                                         <?php
                                                } ?>
                                        </div>
                                        <div class="col-md-12">
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
                            $id = $_REQUEST['id'];
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
                                   LOWER(u.v_name) like LOWER('%".$keyword."%')
                                )";
                            }
                            $ssql = "SELECT 
                                        ".$table2.".*,
                                        u.v_name AS u_name
                                        FROM ".$table2." 
                                        LEFT JOIN tbl_user as u ON ".$table2.".i_user_id = u.id
                                         WHERE true AND ".$table2.".i_messages_id=".$id."".$wh;
                            // $ssql = "SELECT * FROM tbl_track_push_notification WHERE true".$wh;
                                        
                            $sortby = ( isset( $_REQUEST['sb'] ) && $_REQUEST['sb'] != '') ? $_REQUEST['sb'] : 'id';
                            $sorttype = ( isset( $_REQUEST['st'] ) && $_REQUEST['st']=='0') ? 'ASC' : 'DESC';
                            
                            $nototal = $dclass->numRows($ssql);
                            $pagen = new vmPageNav($nototal, $limitstart, $limit, $form ,"black");
                            $sqltepm = $ssql." ORDER BY ".$sortby." ".$sorttype." OFFSET ".$limitstart." LIMIT ".$limit;
                            $restepm = $dclass->query($sqltepm);
                            $row_Data = $dclass->fetchResults($restepm);
                            // _P($row_Data);
                            // exit;
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
                                        
                                        
                                        <table class="table table-bordered" id="datatable" style="width:100%;" >
                                            <thead>
                                                <tr>
                                                    <th width="30%">User Name</th>
                                                    <th width="10%">Notification Title</th>
                                                    <th width="5%">Added Date</th>
                                                    <th width="5%"><span class="pull-right">Action</span></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                if($nototal > 0){
                                                    foreach($row_Data as $row){
                                                        ?>
                                                        <tr>
                                                        <td ><a href="javascript:;"><?php echo $row['u_name']; ?></a></td>

                                                        <td><?php echo $row['j_title'];?></td>
                                                        <td><?php echo $gnrl->displaySiteDate($row['d_added']) ; ?></td>
                                                        <td>
                                                            <div class="btn-group pull-right">
                                                                <button class="btn btn-default btn-xs" type="button">Actions</button>
                                                                <button data-toggle="dropdown" class="btn btn-xs btn-primary dropdown-toggle" type="button">
                                                                    <span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                                                                </button>
                                                                <ul role="menu" class="dropdown-menu pull-right">
                                                                    <!-- <li><a href="<?php echo $page?>.php?a=2&script=edit&id=<?php echo $row['id'];?>">Edit</a></li>
                                                                    <li><a href="<?php echo $page?>.php?a=4&script=view&id=<?php echo $row['id'];?>">View</a></li>
                                                                    <li><a href="javascript:;" onclick="confirm_delete('<?php echo $page;?>','<?php echo $row['id'];?>');">Delete</a></li> -->
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
                                        <input type="hidden" name="script" value="<?php echo @$_REQUEST['script'];?>" />
                                        <input type="hidden" name="id" value="<?php echo @$_REQUEST['id'];?>" />
                                        <input type="hidden" name="a" value="<?php echo @$_REQUEST['a'];?>" />
                                        <input type="hidden" name="st" value="<?php echo @$_REQUEST['st'];?>" />
                                        <input type="hidden" name="sb" value="<?php echo @$_REQUEST['sb'];?>" />
                                        <input type="hidden" name="np" value="<?php //echo @$_SERVER['HTTP_REFERER'];?>" />
                                    </div>
                                </form>
                            </div>
                        <?php 
                        }elseif ($script == 'send_msg' && 1) { 
                            $id=$_REQUEST['id'];
                            if($script == 'send_msg' && $_GET['select_user'] == 'user'){
                                $select_user="user";
                            }else{
                                $select_user="driver";
                            } ?>
                            <form role="form" action="#" method="post" parsley-validate novalidate enctype="multipart/form-data" >
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="content">
                                            <div class="form-group">
                                                <label>Select User/Driver</label>
                                                <select class="select2" name="select_user" id="select_user" onchange="window.location = 'messages.php?script=send_msg&id=<?php echo $id; ?>&select_user='+this.options[this.selectedIndex].value;">
                                                    <?php $gnrl->getDropdownList(array('user','driver'),$select_user); ?>
                                                </select>
                                            </div>
                                             
                                        </div>
                                    </div>
                                </div>
                                <!-- User List -->
                                <div class="row">
                                    <?php
                                        $ssql = "SELECT * FROM tbl_user WHERE true AND v_role= '".$select_user."' ".$wh;
                                         $restepm = $dclass->query($ssql);
                                        $row_Data = $dclass->fetchResults($restepm);

                                    ?>    
                                    <div class="col-md-12">
                                        <div class="header">
                                            <h3>Select <?php echo ucfirst($select_user.'\'s'); ?>
                                            </h3>
                                        </div>
                                        <div class="content">
                                            <div class="row" style="margin-top:0; margin-bottom:0;" >
                                                <div class="form-group col-md-5">
                                                    <label>All <?php echo ucfirst($select_user.'\'s'); ?></label>
                                                    <?php $key = 'L_USER_all'; ?>
                                                    
                                                    <select class="left_right" id="<?php echo $key;?>" name="<?php echo $key;?>[]" multiple >
                                                        <?php
                                                            foreach( $row_Data as $temp_row ){ ?>
                                                                <option value="<?php echo $temp_row["id"]?>" >
                                                                    <?php echo $temp_row["v_name"];?>
                                                                </option> <?php
                                                            
                                                        } ?>
                                                    </select>
                                                </div>
                                                <?php $key = 'L_USER'; ?>
                                                <div class="form-group col-md-2" style="text-align:center;" >
                                                    <label>Actions</label>
                                                    <div class="clear" style="height:10px;" ></div>
                                                    <button class="btn btn-info" type="button" onClick="left_right( '<?php echo $key;?>', 'add' );" ><i class="fa fa-arrow-right"></i></button>
                                                    <div class="clear" style="height:10px;" ></div>
                                                    <button class="btn btn-info" type="button" onClick="left_right( '<?php echo $key;?>', '' );" ><i class="fa fa-arrow-left"></i></button>
                                                </div>
                                                <div class="form-group col-md-5">
                                                    <label>Selected <?php echo ucfirst($select_user.'\'s'); ?></label>
                                                    <select class="left_right" id="<?php echo $key;?>" name="<?php echo $key;?>[]" multiple >
                                                       
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <button class="btn btn-primary" type="submit" name="send_msg_btn" value="Send">Send</button>
                                            <a href="<?php echo $page?>.php"><button class="btn fright" type="button" name="submit_btn">Cancel</button></a> 
                                        </div>
                                    </div>
                            </form>
                            
                        <?php }

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
                                       LOWER(u.v_name) like LOWER('%".$keyword."%')  OR
                                       LOWER(u.v_role) like LOWER('%".$keyword."%')  OR
                                       LOWER(l_message) like LOWER('%".$keyword."%')  OR
                                       LOWER(v_subject) like LOWER('%".$keyword."%')  
                                         
                                    )";
                                }
                                $checked="";
                                if( isset( $_REQUEST['deleted'] ) ){
                                    $keyword =  trim( $_REQUEST['keyword'] );
                                    $wh .= " AND i_delete='1'";
                                    $checked="checked";
                                }else{
                                    $wh .= " AND i_delete='0'";
                                }
                               // $ssql = "SELECT 
                               //          ".$table.".*,
                               //          u.v_name AS u_name,
                               //          u.v_role AS u_role
                               //          FROM ".$table." 
                               //          LEFT JOIN tbl_user as u ON ".$table.".i_user_id = u.id
                               //           WHERE true".$wh;
                                $ssql = "SELECT * FROM ".$table." WHERE true".$wh;
                                            
                                $sortby = $_REQUEST['sb'] = ( $_REQUEST['st'] ? $_REQUEST['sb'] : 'v_name' );
                                $sorttype = $_REQUEST['st'] = ( $_REQUEST['st'] ? $_REQUEST['st'] : 'ASC' );
                                
                                $nototal = $dclass->numRows($ssql);
                                $pagen = new vmPageNav($nototal, $limitstart, $limit, $form ,"black");
                                $sqltepm = $ssql." ORDER BY ".$sortby." ".$sorttype." OFFSET ".$limitstart." LIMIT ".$limit;
                                $restepm = $dclass->query($sqltepm);
                                $row_Data = $dclass->fetchResults($restepm);
                                // _P($row_Data);
                                // exit;
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
                                                                <div class="pull-left" style="">
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
                                                    <label style="margin: 20px 20px;">
                                                        
                                                    </label>
                                                    <div class="clearfix"></div>
                                                </div>
                                            </div>
                                            <!-- <?php chk_all('drop');?> -->
                                            <table class="table table-bordered" id="datatable" style="width:100%;" >
                                                <?php
                                                echo $gnrl->renderTableHeader(array(
                                                    'v_name' => array( 'order' => 1, 'title' => 'Name' ),
                                                    'subject' => array( 'order' => 1, 'title' => 'Subject' ),
                                                    'message' => array( 'order' => 1, 'title' => 'Message' ),
                                                    'd_added' => array( 'order' => 1, 'title' => 'Added Date' ),
                                                    'action' => array( 'order' => 0, 'title' => 'Action' ),
                                                ));
                                                ?> 
                                                <tbody>
                                                    <?php 
                                                    if($nototal > 0){
                                                        
                                                        foreach($row_Data as $row){
                                                            
                                                            ?>
                                                            <tr>
                                                                <td><a id="<?php echo 'v_name'.$row['id'] ?>"href="javascript:;" class="md-trigger" data-modal="view_modal" onclick="view_message(<?php echo $row['id']; ?>);"><?php echo $row['v_name']; ?></a></td>
                                                                 <td></td>
                                                                  <td></td>
                                                                <td><?php echo $gnrl->displaySiteDate($row['d_added']) ; ?></td>
                                                                <td>
                                                                    <div class="btn-group pull-right">
                                                                        <button class="btn btn-default btn-xs" type="button">Actions</button>
                                                                        <button data-toggle="dropdown" class="btn btn-xs btn-primary dropdown-toggle" type="button">
                                                                            <span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                                                                        </button>
                                                                        <ul role="menu" class="dropdown-menu pull-right">
                                                                             <li><a href="<?php echo $page?>.php?a=2&script=edit&id=<?php echo $row['id'];?>">Edit</a></li>
                                                                            <li><a href="<?php echo $page?>.php?a=4&script=view&id=<?php echo $row['id'];?>">View</a></li>

                                                                            <li><a href="<?php echo $page?>.php?script=send_msg&id=<?php echo $row['id'];?>">Send Message</a></li>
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
<div class="md-modal colored-header custom-width md-effect-9" id="view_modal" style="width:50% !important;" >
        <div class="md-content">
            <div class="modal-header">
                <h3>View Message</h3>
                <button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body form" style="max-height:300px; overflow:auto;" >
                <div class="row" style="margin-top:0;" >
                    <div class="form-group col-md-12">
                        <div class="content">
                            <div class="row" >
                                <div class="form-group col-sm-3">
                                    <label>User Name</label>
                                    <input type="text" class="form-control" value="" readonly id="modal_uname">
                                </div>
                                 <div class="form-group col-sm-3">
                                    <label>User Role</label>
                                    <input type="text" class="form-control" value="" id="modal_role" readonly >
                                </div>
                                 <div class="form-group col-sm-4">
                                    <label>Subject</label>
                                    <input type="text" class="form-control" value="" id="modal_subject" readonly >
                                </div>
                                 <div class="form-group col-sm-8" >
                                    <label>Message</label>
                                    <textarea class="form-control" id="modal_message" readonly></textarea>
                                </div>
                                <div class="form-group col-sm-4" >
                                    <label>Date</label>
                                    <input type="text" class="form-control" value="" id="modal_date" readonly >
                                </div>
                            </div>
                        </div>
                    </div>
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
