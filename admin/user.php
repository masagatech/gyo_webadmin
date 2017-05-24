<?php 
include('includes/configuration.php');
$gnrl->check_login();


	extract( $_POST );
	$page_title = "Manage User";
	$page = "user";
	$table = 'tbl_user';
	$title2 = 'User';
	$v_role ='user';
    $folder='users';
	$script = ( isset( $_REQUEST['script'] ) && ( $_REQUEST['script'] == 'add' || $_REQUEST['script'] == 'edit' ) ) ? $_REQUEST['script'] : "";
	
	## Insert Record in database starts
	if(isset($_REQUEST['submit_btn']) && $_REQUEST['submit_btn']=='Submit'){
		
        $email_exit = $dclass->select('*',$table," AND v_email = '".$v_email."'");
        
        if(count($email_exit) && !empty($email_exit)){
            
             $gnrl->redirectTo($page.".php?succ=0&script=add&msg=email_exit");
        }else{

             if(isset($_FILES['v_image']) && $_FILES['v_image']['name'] != ''){
                $dest = UPLOAD_PATH.$folder."/";
                
                $file_name = $gnrl->removeChars( time().'-'.$_FILES['v_image']['name'] ); 
                if( move_uploaded_file( $_FILES['v_image']['tmp_name'], $dest.$file_name ) ){
                    // $ins['v_image'] = $file_name;
                }
             }
            $ins = array(
                'v_name'  => $v_name,
                'v_email' =>$v_email,
                'v_phone'   => $v_phone,
                'v_password'  => $v_password ? md5($v_password):'',
                'v_role'=> $v_role,
                'v_image' => $file_name ? $file_name : '',
                'v_gender' => $v_gender,
                'e_status' => $e_status ,
                'd_added' => date('Y-m-d H:i:s'),
                'd_modified' => date('Y-m-d H:i:s')
            );
            // exit;
            $id = $dclass->insert( $table, $ins );
            $gnrl->redirectTo($page.".php?succ=1&msg=add");
        }
		
	}

	## Delete Record from the database starts
	if(isset($_REQUEST['a']) && $_REQUEST['a']==3) {
		if(isset($_REQUEST['id']) && $_REQUEST['id']!="") {
			$id = $_REQUEST['id'];
			if($_REQUEST['chkaction'] == 'delete' ) {
                if( 1 ){
    				$dclass->delete( $table ," id = '".$id."'");
    				$gnrl->redirectTo($page.".php?succ=1&msg=del");
                }else{
                    $gnrl->redirectTo($page.".php?succ=0&msg=not_auth");
                }
			}
			// make records active
			else if($_REQUEST['chkaction'] == 'active'){
                 if( 1 ){
    				$ins = array('e_status'=>'active');
    				$dclass->update( $table, $ins, " id = '".$id."'");
    				$gnrl->redirectTo($page.".php?succ=1&msg=multiact");
                 }else{
                    $gnrl->redirectTo($page.".php?succ=0&msg=not_auth");
                 }
			}
			// make records inactive
			else if($_REQUEST['chkaction'] == 'inactive'){
                if( 1 ){
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
        // _P($_REQUEST);
        // _P($_FILES);
        // exit;
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
                     
                     // exit;
                     if(isset($_FILES['v_image']) && $_FILES['v_image']['name'] != ''){
                        $dest = UPLOAD_PATH.$folder."/";
                        
                        $file_name = $gnrl->removeChars( time().'-'.$_FILES['v_image']['name'] ); 
                        if( move_uploaded_file( $_FILES['v_image']['tmp_name'], $dest.$file_name ) ){
                            $ins['v_image'] = $file_name; 
                             @unlink( $dest.$oldname_v_image );
                        }
                     }
                    $ins['v_name'] = $v_name;
                    $ins['v_email'] = $v_email;
                    $ins['v_phone'] = $v_phone;
                    $ins['e_status'] = $e_status;
                    $ins['v_gender'] = $v_gender;
                    $ins['l_data'] = json_encode($l_data);
                    $ins['d_modified'] = date('Y-m-d H:i:s');

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
                                <?php if( !$script && 1){?>

                                <a href="<?php echo $page?>.php?script=add" class="fright">
                                    <button class="btn btn-primary" type="button">Add <?php echo ' '.ucfirst( $title2 );?></button>
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
                                                <label>Gender</label>
                                                <select class="select2" name="v_gender" id="v_gender">
                                                    <?php $gnrl->getDropdownList(array('male','female'),$v_gender); ?>
                                                </select>
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
                                                 <?php 
                                                    if( $putFile = _is_file( $folder, $v_image ) ){ //echo $putFile; ?>
                                                    <img class="edit_img" src="<?php echo $putFile;?>" >
                                                    <input type="hidden" name="oldname_v_image" value="<?php echo $v_image; ?>">
                                                <?php } ?>
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
                                       LOWER(v_name) like LOWER('%".$keyword."%')  OR
                                       LOWER(v_email) like LOWER('%".$keyword."%')  OR
                                       LOWER(v_role) like LOWER('%".$keyword."%')  OR
                                       LOWER(v_phone) like LOWER('%".$keyword."%')  OR
                                         LOWER(e_status) like LOWER('%".$keyword."%')
                                    )";
                                }
                                
                                $ssql = "SELECT * FROM ".$table." WHERE true AND v_role='".$v_role."'".$wh;
                                            
                                $sortby = ( isset( $_REQUEST['sb'] ) && $_REQUEST['sb'] != '') ? $_REQUEST['sb'] : 'v_name';
                                $sorttype = ( isset( $_REQUEST['st'] ) && $_REQUEST['st']!='') ? $_REQUEST['st'] : 'ASC';
                                
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
                                            
                                            <table class="table table-bordered" id="datatable" style="width:100%;" >
											
												<?php
												
												echo $gnrl->renderTableHeader(array(
													'v_name' => array( 'order' => 1, 'title' => 'Name' ),
													'v_email' => array( 'order' => 1, 'title' => 'Email' ),
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
                                                                
                                                                <td><?php echo $row['v_name']; ?></td>

                                                                <td><?php echo $row['v_email'];?></td>
                                                                
                                                                 <td><?php echo $row['v_phone'];?></td>
                                                                  <td><?php echo $gnrl->removeTimezone($row['d_added']) ; ?></td>
                                                                  <td><?php echo $row['e_status'];?></td>
                                                                <td>
                                                                    <?php 
                                                                        if(1){ ?>

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

                                                                       <?php }
                                                                    ?>
                                                                   
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
                            } 
                            else{}
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
