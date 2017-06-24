<?php 
include('includes/configuration.php');
$gnrl->check_login();
    
	extract( $_POST );
	$page_title = "Manage Push Notification";
	$page = "push_notification";
	$table = 'tbl_push_notification';
    $table2 = 'tbl_track_push_notification';
	$title2 = 'Push Notification';
	// $v_role ='user';
	$script = ( isset( $_REQUEST['script'] ) && ( $_REQUEST['script'] == 'add' || $_REQUEST['script'] == 'edit'  || $_REQUEST['script'] == 'view' || $_REQUEST['script'] == 'send_noti' ) ) ? $_REQUEST['script'] : "";
	

    // dYDweui-PJk:APA91bHOF0Wj6mOH-H7bh3PoeQNJ25-QPG0INfLGyt8bxFjpOTCagUYnGvPkrEC64Vd8uZUBBOxQYNILsLfyqNj-cGwTPAMHATnAgUPS4S2bozQ7VeQGIN3aukGnex34vz05N467cWE3
	## Insert Record in database starts
	if(isset($_REQUEST['submit_btn']) && $_REQUEST['submit_btn']=='Submit'){

        $j_content = str_replace( '\r', '', str_replace( '\n', '', json_encode( $j_content ) ) );
        $j_title = str_replace( '\r', '', str_replace( '\n', '', json_encode( $j_title ) ) );
        $ins = array(
            'v_name'    => $v_name,
            'j_title'  => $j_title,
            'j_content'  => $j_content,
            'd_added'    => date('Y-m-d H:i:s')
        );
        $id = $dclass->insert( $table, $ins );
        $gnrl->redirectTo($page.".php?succ=1&msg=add");
	}
    ## Insert Record in database starts
    if(isset($_REQUEST['send_noti_btn']) && $_REQUEST['send_noti_btn']=='Send'){
        $id =$_REQUEST['id'];
        $row = $dclass->select('*',$table," AND id = '".$id."'");
        $row = $row[0];
        extract( $row );
        //NOTIFICATION CODE   

        $l_data =array();
        $l_data['type']= $v_type;
        $l_data['title']= json_decode($j_title,true);
        $l_data['content']= json_decode($j_content,true);
		$l_data['i_view']= 0;
		$l_data['lang']= 'en';
        $i_status='1';
        
        foreach( $L_USER as $key => $value ){
            $user_row = $dclass->select('*','tbl_user'," AND id = '".$value."'");
            $user_row=$user_row[0];
            $device_token= $user_row['v_device_token'];
            $notification_data=array(
                'type' => 'custom',
                'title' => $l_data['title']['en'],
                'content' => $l_data['content']['en'],
            );

            if($user_row['v_role'] == 'user'){
                $authentication_key=USER_NOTIFICATION_KEY;
            }else{
                $authentication_key=DRIVER_NOTIFICATION_KEY;
            }

            $is_send=$gnrl->sendNotificationAndroid($device_token,$notification_data,$authentication_key);
           
            if($is_send){
                $i_status=1;
                 $l_data['error']='';
            }else{
                $i_status=0;
                $l_data['error']='Fail';
            }
            ## DB INSERT QUERY
            $l_data=json_encode($l_data);
            $inc_arr[] = "('".$value."', '".$id."','".$i_status."','".date('Y-m-d H:i:s')."','".$l_data."','custom')";

        }
        $inc_str = implode(',',$inc_arr);
        $insert_str = "INSERT INTO ".$table2." (i_user_id,i_push_notification_id,i_status,d_added,l_data,v_type) VALUES ".$inc_str.";";
        
        $restepm = $dclass->query($insert_str);
        $row_Data = $dclass->fetchResults($restepm);
        $gnrl->redirectTo($page.".php?succ=1&msg=success_push");
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

                $j_title = json_encode( $j_title );
                $j_title = str_replace( '\r', '', $j_title );
                $j_title = str_replace( '\n', '', $j_title );

                $j_content = json_encode( $j_content );
                $j_content = str_replace( '\r', '', $j_content );
                $j_content = str_replace( '\n', '', $j_content );

				$ins = array(
                    'v_name'    => $v_name,
                    'j_title'  => $j_title,
                    'j_content'  => $j_content
                );
				$dclass->update( $table, $ins, " id = '".$id."' ");
				$gnrl->redirectTo($page.'.php?succ=1&msg=edit&a=2&script=edit&id='.$_REQUEST['id']);
			}
			else {
				$row = $dclass->select('*',$table," AND id = '".$id."'");

                $row = $row[0];
                // exit;
                extract( $row );
                $j_title=json_decode($j_title,true);
                $j_content=json_decode($j_content,true);
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
                                    if($script == 'send_noti'){
                                        echo "Send Notification";
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
                            </h3>
                        </div>
                        <?php if( ($script == 'add' || $script == 'edit') && 1 ){?>
                            <form role="form" action="#" method="post" parsley-validate novalidate enctype="multipart/form-data" >
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="content">
                                            <div class="form-group">
                                               <div class="form-group">
                                                    <label>Name <?php echo $gnrl->getAstric(); ?></label>
                                                    <input type="text" class="form-control" id="v_name" name="v_name" value="<?php echo $v_name ?>" required />
                                                </div>
                                            </div>
                                                <?php
                                                    foreach( $globLangArr as $_langK => $_langV ){$key = 'j_title';
                                                        ?>
                                                        <div class="form-group"> 
                                                            <label>Notification Title (<?php echo $_langV?>) <?php echo $gnrl->getAstric(); ?></label>
                                                            <input name="<?php echo $key;?>[<?php echo $_langK?>]" class="form-control" value="<?php echo $j_title[$_langK];?>" required />
                                                        </div>
                                                         <?php
                                                } ?>
                                                <?php
                                                    foreach( $globLangArr as $_langK => $_langV ){$key = 'j_title';
                                                        ?>
                                                        <?php $key = 'j_content'; ?>
                                                        <div class="form-group"> 
                                                            <label>Notification Content (<?php echo $_langV?>) <?php echo $gnrl->getAstric(); ?></label>
                                                            <textarea name="<?php echo $key;?>[<?php echo $_langK?>]" class="form-control" required><?php echo $j_content[$_langK];?></textarea>
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
                        <?php } else if($script == 'view' && 1){
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
                                   LOWER(u.v_name) like LOWER('%".$keyword."%')  OR
                                   LOWER(u.v_role) like LOWER('%".$keyword."%')  OR
                                   LOWER(l_message) like LOWER('%".$keyword."%')  OR
                                   LOWER(v_subject) like LOWER('%".$keyword."%')  
                                     
                                )";
                            }
                            $ssql = "SELECT 
                                        ".$table2.".*,
                                        u.v_name AS u_name
                                        FROM ".$table2." 
                                        LEFT JOIN tbl_user as u ON ".$table2.".i_user_id = u.id
                                         WHERE true AND ".$table2.".i_push_notification_id=".$id."".$wh;
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
                                        
                                        <!-- <?php chk_all('drop');?> -->
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
                                        <input type="hidden" name="a" value="<?php echo @$_REQUEST['a'];?>" />
                                        <input type="hidden" name="st" value="<?php echo @$_REQUEST['st'];?>" />
                                        <input type="hidden" name="sb" value="<?php echo @$_REQUEST['sb'];?>" />
                                        <input type="hidden" name="np" value="<?php //echo @$_SERVER['HTTP_REFERER'];?>" />
                                    </div>
                                </form>
                            </div> <?php 
                        } else if($script == "send_noti" && 1){
                            $id=$_REQUEST['id'];
                            if($script == 'send_noti' && $_GET['select_user'] == 'user'){
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
                                                <select class="select2" name="select_user" id="select_user" onchange="window.location = 'push_notification.php?script=send_noti&id=<?php echo $id; ?>&select_user='+this.options[this.selectedIndex].value;">
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
                                            <button class="btn btn-primary" type="submit" name="send_noti_btn" value="Send">Send</button>
                                            <a href="<?php echo $page?>.php"><button class="btn fright" type="button" name="submit_btn">Cancel</button></a> 
                                        </div>
                                    </div>
                            </form>
                        <?php 
                        } else {
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
                                       LOWER(v_name) like LOWER('%".$keyword."%')
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
                                                                     <div class="pull-right" style="">
                                                                        <input class="all_access" name="deleted" value=""  type="checkbox"  onclick="document.frm.submit();" <?php echo $checked; ?>>
                                                                        Show Deleted Data
                                                                </div>
                                                                <div class="clearfix"></div>
                                                                <?php 
                                                                    if(isset($_REQUEST['keyword']) && $_REQUEST['keyword'] != ''){ ?>
                                                                            <a href="<?php echo $page ?>.php" class="fright" style="" > Clear Search </a>
                                                                <?php } ?>
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
                                                    'v_name' => array( 'order' => 1, 'title' => 'Name' ),
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
                                                                <td>
                                                                    <?php echo $row['v_name']; ?>
                                                                </td>
                                                               <td><?php echo $gnrl->displaySiteDate($row['d_added']) ; ?></td>
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
                                                                                    <li><a href="<?php echo $page?>.php?a=2&script=edit&id=<?php echo $row['id'];?>">Edit</a></li>
                                                                                    <li><a href="<?php echo $page?>.php?script=send_noti&id=<?php echo $row['id'];?>">Send Notification</a></li>
                                                                                    <li><a href="<?php echo $page?>.php?a=4&script=view&id=<?php echo $row['id'];?>">View</a></li>
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

<?php include('_scripts.php');?>
<?php include('jsfunctions/jsfunctions.php');?>
</body>
</html>
