<?php 
include('includes/configuration.php');
$gnrl->check_login();

extract( $_POST );
$page_title = "Manage Sections";
$page = "sections";
$table = 'tbl_sections';
$title2 = 'Sections';
// $v_role ='user';
$script = ( isset( $_REQUEST['script'] ) && ( $_REQUEST['script'] == 'add' || $_REQUEST['script'] == 'edit' ) ) ? $_REQUEST['script'] : "";

// _P($_REQUEST);
// exit;

## Insert Record in database starts
if(isset($_REQUEST['submit_btn']) && $_REQUEST['submit_btn']=='Submit'){

	$key_exist = $dclass->select('*',$table," AND v_key = '".$v_key."'");
    if(!empty($key_exist[0])){
        $gnrl->redirectTo($page.".php?script=add&succ=0&msg=key_exit");
    }else{
       
        $ins = array(
            'v_title'  => $v_title,
            'v_name' => $v_name,
            'v_key' => $v_key,

            'i_parent_id' =>$i_parent_id,
            'v_icon' =>$v_icon,
            'i_order' =>$i_order,
            'd_added' => date('Y-m-d H:i:s'),
            'd_modified' => date('Y-m-d H:i:s'),
            'e_status' => $e_status,
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
        // _P($_REQUEST);
        // exit;
		$id = $_REQUEST['id'];
		if( isset( $_REQUEST['submit_btn'] ) && $_REQUEST['submit_btn'] == 'Update' ) {

			$ins = array(
                'v_title'  => $v_title,
                'v_name' => $v_name,
                'v_key' => $v_key,

                'i_parent_id' =>$i_parent_id,
                'v_icon' =>$v_icon,
                'i_order' =>$i_order,
                'd_modified' => date('Y-m-d H:i:s'),
                'e_status' => $e_status,
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

##SELECT PARENT SECTION
$parent_arr=array();
$parent_section = $dclass->select( 'id, v_key, v_title, v_name', $table, "AND i_parent_id = '0' ORDER BY i_order ");
foreach ($parent_section as $s_key => $s_value) {
    $parent_arr[$s_value['id']]=$s_value['v_title'];
}
	
$checked="";
if( isset( $_REQUEST['deleted'] ) ){
    $checked="checked";
}else{
    $checked="";
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
                                <?php if( !$script ){?>
                                    <?php if( !$script && 1){?>
                                        <a href="<?php echo $page?>.php?script=add" class="fright">
                                            <button class="btn btn-primary" type="button">Add</button>
                                        </a>
                                    <?php } ?>
								<?php } ?>
                            </h3>
                            
                        </div>
                        <?php 
                        if( ($script == 'add' || $script == 'edit') && 1){?>
                        	<form role="form" action="#" method="post" parsley-validate novalidate enctype="multipart/form-data" >
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="content">
                                            <div class="form-group">
                                                <label>Title <span>*</span></label>
                                                <input type="text" class="form-control" id="v_title" name="v_title" value="<?php echo $v_title; ?>" required />
                                            </div>
                                            <div class="form-group">
                                                <label>Name <span>*</span></label>
                                                <input type="text" class="form-control" id="v_name" name="v_name" value="<?php echo $v_name; ?>" required />
                                            </div>
                                            <div class="form-group">
                                                <label>Key (unique) <span>*</span></label>
                                                <input type="text" class="form-control" id="v_key" name="v_key" value="<?php echo $v_key; ?>" required />
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Select Parent</label>
                                                <select class="select2" name="i_parent_id" id="i_parent_id">
													<option value="0" > - Parent - </option>
                                                    <?php echo $gnrl->get_keyval_drop( $parent_arr, $i_parent_id ); ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Icon ( favicon / glyphicon icon ) <span>*</span></label>
                                                <input type="text" class="form-control" id="v_icon" name="v_icon" value="<?php echo $v_icon; ?>"  required=""/>
                                            </div>
                                            <div class="form-group">
                                                <label>Order <span>*</span></label>
                                                <input type="number"  class="form-control" id="i_order" name="i_order" value="<?php echo $i_order; ?>" min="1"  required="" />
                                            </div>
                                            <div class="form-group">
                                                <label>Status <span>*</span></label>
                                                <select class="select2" name="e_status" id="e_status" required="">
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
						    if( 1 ){
								
                                $row_Data = $gnrl->getSections($checked);
								
                                ?>
                                <div class="content">
                                    <form name="frm" action="" method="get" >
                                        <div class="clearfix"></div>
                                            <label class="pull-right" style="margin-top: 10px; ">
                                             <div class="pull-right" style="">
                                                <input class="all_access" name="deleted" value=""  type="checkbox"  onclick="document.frm.submit();" <?php echo $checked; ?>>
                                                Show Deleted Data
                                            </div>
                                            </label>
                                        <div class="table-responsive">
                                        
										    <table class="table table-bordered" id="datatable" style="width:100%;" >
                                                <thead>
                                                    <tr>
                                                        <th>Section Title (Key)</th>
                                                        <th>File Name</th>
                                                        <th>Order</th>
                                                        <th>Status</th>
                                                        <th><span class="pull-right">Action</span></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    if( count( $row_Data ) ){
                                                        foreach( $row_Data as $row ){
                                                            ?>
                                                            <tr>
                                                                <td>
                                                                    <a href="<?php echo $page?>.php?a=2&script=edit&id=<?php echo $row['id'];?>">
																		<?php echo $row['v_title']; ?>
																	</a>
																	(<?php echo $row['v_key'];?>)
                                                                </td>
																<td><?php echo $row['v_name'];?></td>
                                                                <td><?php echo $row['i_order'];?></td>
                                                                <td><?php echo $row['e_status'];?></td>
                                                                <td class="text-right" >
                                                                    <?php if(1){?> 
                                                                        <div class="btn-group">
                                                                        <button class="btn btn-default btn-xs" type="button">Actions</button>
                                                                        <button data-toggle="dropdown" class="btn btn-xs btn-primary dropdown-toggle" type="button">
                                                                            <span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                                                                        </button>
                                                                        <ul role="menu" class="dropdown-menu pull-right">

                                                                            <?php
                                                                               if(isset($_REQUEST['deleted'])){ ?>
                                                                                <li><a href="javascript:;" onclick="confirm_restore('<?php echo $page;?>','<?php echo $row['id'];?>');">Restore</a></li>
                                                                                <?php  }else{ ?>
                                                                                <li><a href="<?php echo $page?>.php?a=2&script=edit&id=<?php echo $row['id'];?>">Edit</a></li>
                                                                                <li><a href="<?php echo $page;?>.php?a=3&amp;chkaction=active&amp;id=<?php echo $row['id'];?>">Active</a></li>
                                                                                <li><a href="<?php echo $page;?>.php?a=3&amp;chkaction=inactive&amp;id=<?php echo $row['id'];?>">Inactive</a></li>
                                                                                <li><a href="javascript:;" onclick="confirm_delete('<?php echo $page;?>','<?php echo $row['id'];?>');">Delete</a></li>
                                                                                <?php }
                                                                            ?>
                                                                            
                                                                        </ul>
                                                                    </div>
                                                                    <?php } ?>
                                                                    
                                                                </td>
                                                            </tr><?php 
															
															$parentRow = $row;
															
															if( count( $parentRow['childs'] ) ){
																foreach( $parentRow['childs'] as $row ){
																	?>
																	<tr>
																		<td>
																			<a href="<?php echo $page?>.php?a=2&script=edit&id=<?php echo $row['id'];?>">
																				<?php // echo $parentRow['v_title']; ?>
																				<?php echo '&nbsp;&nbsp;&nbsp; &raquo;&nbsp;';?>
																				<?php echo $row['v_title']; ?>
																			</a>
																			(<?php echo $row['v_key'];?>)
																		</td>
																		<td><?php echo $row['v_name'];?></td>
																		<td><?php echo $row['i_order'];?></td>
																		<td><?php echo $row['e_status'];?></td>
																		<td class="text-right" >
																			<?php if(1){?> 
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
																			<?php } ?>
																			
																		</td>
																	</tr><?php 
																} 
															 }
															
                                                        }
                                                    }
                                                    else{?>
                                                        <tr><td colspan="8">No Record found.</td></tr><?php 
                                                    }?>
                                                </tbody>
                                            </table>
                                            
                                            
                                        </div>
                                    </form>
                                </div> 
                            <?php }
                            else{}
                        }?>
                    </div>
                </div>
            </div>
        </div>
	</div>
</div>

<script>
function searchCity(val){
    window.document.location.href=window.location.pathname+'?city_sel='+val;
}
</script>
        

<?php include('_scripts.php');?>
<?php include('jsfunctions/jsfunctions.php');?>
</body>
</html>
