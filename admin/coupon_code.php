<?php 

include('includes/configuration.php');
$gnrl->check_login();


// ini_set("display_errors", "1");
// error_reporting(E_ALL);
// _P($_REQUEST);
// exit;
    extract( $_POST );
    $page_title = "Manage Coupon Code";
    $page = "coupon_code";
    $table = 'tbl_coupon_code';
    $title2 = 'Coupon Code';
    // $v_role ='user';
    $script = ( isset( $_REQUEST['script'] ) && ( $_REQUEST['script'] == 'add' || $_REQUEST['script'] == 'edit' ) ) ? $_REQUEST['script'] : "";

    ## Insert Record in database starts
    if(isset($_REQUEST['submit_btn']) && $_REQUEST['submit_btn']=='Submit'){
        // _P($_REQUEST);
        // exit;
        $ins = array(
            'v_title'       => $v_title,
            'v_code'        => $v_code,
            'v_type'        => $v_type,
            'discount_amount' => $discount_amount, 
            'upto_amount' => $upto_amount,
            'd_start_date'  => $d_start_date,
            'd_end_date'    => $d_end_date,
            'd_added'       => date('Y-m-d H:i:s'),
            'd_modified'    => date('Y-m-d H:i:s'),
            'e_status'      => $e_status,
            'i_city_ids'     => implode(',', $L_CITY),
            // 'i_user_ids'     => implode(',', $L_USER),
            'l_description' => $l_description,
        );
        // _P($ins);
        // exit;
        $id = $dclass->insert( $table, $ins );
        $gnrl->redirectTo($page.".php?succ=1&msg=add");
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
                    'v_title'       => $v_title,
                    'v_code'        => $v_code,
                    'v_type'        => $v_type,
                    'upto_amount' => $upto_amount,
                    'discount_amount' => $discount_amount, 
                    'd_start_date'  => $d_start_date,
                    'd_start_date'  => $d_start_date,
                    'd_end_date'    => $d_end_date,
                    'd_modified'    => date('Y-m-d H:i:s'),
                    'e_status'      => $e_status,
                    'i_city_ids'     => implode(',', $L_CITY),
                    // 'i_user_ids'     => implode(',', $L_USER),
                    'l_description' => $l_description,
                );
                $dclass->update( $table, $ins, " id = '".$id."' ");
                $gnrl->redirectTo($page.'.php?succ=1&msg=edit&a=2&script=edit&id='.$_REQUEST['id']);
            }
            else {
                $row = $dclass->select('*',$table," AND id = '".$id."'");
                $row = $row[0];
                // _P($row);
                // exit();
                extract( $row );
                $i_city_ids= explode(',',$i_city_ids);
                $i_user_ids= explode(',',$i_user_ids);

            }
        }
    }
    # GET ALL CITY
    $cities = $dclass->select('*','tbl_city');
    $users = $dclass->select('*','tbl_user',"AND v_role = 'user' ");
  
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
                        if( ($script == 'add' || $script == 'edit') && 1 ){?>
                            <form role="form" action="#" method="post" parsley-validate novalidate enctype="multipart/form-data" >
                                <div class="row">
                                    <div class="col-md-10">
                                        <div class="content">

                                            <div class="form-group">
                                                <label>Type <?php echo $gnrl->getAstric(); ?></label>
                                                <select class="select2 required" name="v_type" id="v_type">
                                                    <option>--Select--</option>
                                                    <?php echo $gnrl->get_keyval_drop($globalPromotionType,$v_type); ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Title <?php echo $gnrl->getAstric(); ?></label>
                                                <input type="text" class="form-control" id="v_title" name="v_title" value="<?php echo $v_title; ?>" required />
                                            </div>
                                            <div class="form-group">
                                                <label>Discount [ Flat (5) OR In Percentage (5%) ] <?php echo $gnrl->getAstric(); ?></label>
                                                <input type="text" class="form-control" id="discount_amount" name="discount_amount" value="<?php echo $discount_amount; ?>" required />
                                            </div>
                                            <div class="form-group">
                                                <label>Upto Amount <?php echo $gnrl->getAstric(); ?></label>
                                                <input type="text" class="form-control" id="upto_amount" name="upto_amount" value="<?php echo $upto_amount; ?>" required />
                                            </div>
                                            <div class="form-group">
                                                <label>Code <?php echo $gnrl->getAstric(); ?></label>
                                                <input type="text" class="form-control" id="v_code" name="v_code" value="<?php echo $v_code; ?>" required />
                                            </div>
                                            <div class="form-group">
                                                <label>Description</label>
                                                <textarea class="form-control" id="l_description" name="l_description"><?php echo $l_description; ?></textarea>
                                            </div>
                                            <?php 
                                                if($script=="edit"){
													
													$d_start_date = date('Y-m-d H:i:s', strtotime($d_start_date));
													$d_end_date = date('Y-m-d H:i:s', strtotime($d_end_date));
													
                                                }else{
                                                    $d_start_date='';
                                                    $d_end_date='';
                                                }

                                            ?>
                                            <div class="form-group">
                                                <label>Start Date <?php echo $gnrl->getAstric(); ?></label>
                                                <input class="form-control" size="16" type="text" id="d_start_date" name="d_start_date" 
												value="<?php echo $d_start_date ? $d_start_date : date('Y-m-d H:i:s'); ?>" readonly="" 
												data-date-format="yyyy-mm-dd hh:ii:ss"
												/>
												
                                            </div>
                                            <div class="form-group">
                                                <label>End Date</label>
                                                <input class="form-control" size="16" type="text" id="d_end_date" name="d_end_date" 
												value="<?php echo $d_end_date ? $d_end_date : date('Y-m-d H:i:s'); ?>" readonly="" 
												data-date-format="yyyy-mm-dd hh:ii:ss"
												/>
                                            </div>
											

                                            <!-- <div class="form-group col-md-6">
                                                <label>Start Date</label>
                                                <div class="input-group date datetime " data-show-meridian="true"  data-date="<?php echo date(Y-m-d); ?>" data-date-format="yyyy-mm-dd  HH:ii" >
                                                <span class="input-group-addon btn btn-primary"><span class="glyphicon glyphicon-th"></span></span>
                                                <input class="form-control" size="16" type="text" value="" id="d_start_date" name="d_start_date" value="<?php echo $gnrl->displaySiteDate($d_start_date); ?>" data-date-format="yyyy-mm-dd hh:ii" readonly="">
                                                </div>
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>End Date</label>
                                                <div class="input-group date datetime " data-show-meridian="true"  data-date="<?php echo date(Y-m-d); ?>" data-date-format="yyyy-mm-dd  HH:ii" >
                                                <span class="input-group-addon btn btn-primary"><span class="glyphicon glyphicon-th"></span></span>
                                                <input class="form-control" size="16" type="text" value="" id="d_end_date" name="d_end_date" value="<?php echo $gnrl->displaySiteDate($d_end_date); ?>" data-date-format="yyyy-mm-dd hh:ii" readonly="" onclick="datetimepicker()">
                                                </div>
                                            </div> -->
<!-- 
                                            <div class="form-group col-md-6">
                                                <label>End Date</label>
                                                <input class="form-control datetime" size="16" type="text" id="d_end_date" name="d_end_date" value="<?php echo $gnrl->displaySiteDate($d_end_date); ?>" data-date-format="yyyy-mm-dd hh:ii" readonly="" onclick="datetimepicker()" />
                                            </div> -->

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
                                                        <select class="left_right" id="<?php echo $key;?>" name="<?php echo $key;?>[]" multiple >
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

                                             <!-- <h3>Select Users</h3>
                                            <div class="row" style="margin-top:0; margin-bottom:0;" >
                                                    <div class="form-group col-md-5">
                                                        <label>All Users</label>
                                                        <?php $key = 'L_USER_all'; ?>
                                                        
                                                        <select class="left_right" id="<?php echo $key;?>" name="<?php echo $key;?>[]" multiple >
                                                            <?php
                                                                foreach( $users as $temp_row ){ 
                                                                    if(!in_array($temp_row['id'], $i_user_ids)){ ?>
                                                                    <option value="<?php echo $temp_row["id"]?>" >
                                                                        <?php echo $temp_row["v_name"];?>
                                                                    </option> 
                                                                <?php } } ?>
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
                                                        <label>Selected Cities</label>
                                                        <select class="left_right" id="<?php echo $key;?>" name="<?php echo $key;?>[]" multiple >
                                                        <?php
                                                            foreach( $users as $temp_row2 ){ 
                                                                if(in_array($temp_row2['id'], $i_user_ids)){ ?>
                                                                    <option value="<?php echo $temp_row2["id"]?>" selected="selected">
                                                                        <?php echo $temp_row2["v_name"];?>
                                                                    </option> 
                                                                <?php } ?>
                                                                <?php
                                                            
                                                        } ?>
                                                        </select>
                                                    </div>
                                            </div> -->
                                           
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
                                       LOWER(v_title) like LOWER('%".$keyword."%')  OR
                                       LOWER(v_code) like LOWER('%".$keyword."%')  OR
                                       LOWER(v_type) like LOWER('%".$keyword."%')  OR
                                       LOWER(e_status) like LOWER('%".$keyword."%') 
                                         
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
                                
                                $ssql = "SELECT * FROM ".$table." WHERE true ".$wh;
                                $sortby = $_REQUEST['sb'] = ( $_REQUEST['st'] ? $_REQUEST['sb'] : 'v_title' );
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
                                                                <a href="<?php echo $page ?>.php" class="fright" style="margin: -10px 0px 20px 0px ;" >
                                                                    Clear Search
                                                                </a>
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
                                                <?php
                                                echo $gnrl->renderTableHeader(array(
                                                    'v_title' => array( 'order' => 1, 'title' => 'Title' ),
                                                    'v_type' => array( 'order' => 1, 'title' => 'Type' ),
                                                    'v_code' => array( 'order' => 1, 'title' => 'Code' ),
                                                    'd_start_date' => array( 'order' => 1, 'title' => 'Start Date' ),
                                                    'd_end_date' => array( 'order' => 1, 'title' => 'End Date' ),
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
                                                                    <?php echo $row['v_title']; ?>
                                                                </td>
                                                                <td><?php echo 
                                                                ucwords(str_replace('_', ' ', $row['v_type']));?>
                                                                </td>
                                                                <td><?php echo $row['v_code'];?></td>
                                                                <td><?php echo $gnrl->displaySiteDate($row['d_start_date']) ; ?></td>
                                                                 <td><?php echo $gnrl->displaySiteDate($row['d_end_date']) ; ?></td>
                                                                  <td><?php echo $gnrl->displaySiteDate($row['d_added']) ; ?></td>
                                                                   <td><?php echo $row['e_status'];?></td>
                                                                <td style="width: 101px;">
                                                                    <?php
                                                                     if(1){ ?>
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
                                                                                    <li><a href="<?php echo $page;?>.php?a=3&amp;chkaction=active&amp;id=<?php echo $row['id'];?>">Active</a></li>
                                                                                    <li><a href="<?php echo $page;?>.php?a=3&amp;chkaction=inactive&amp;id=<?php echo $row['id'];?>">Inactive</a></li>
                                                                                    <li><a href="javascript:;" onclick="confirm_delete('<?php echo $page;?>','<?php echo $row['id'];?>');">Delete</a></li>
                                                                                <?php }
                                                                            ?>
                                                                           
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

<?php include('_scripts.php');?>

<script type="text/javascript">
$(function () {
	
	var startDate = 0;
	var endDate = 0;
    
	$("#d_start_date").datetimepicker({
		autoclose : true,
		startDate : '<?php echo date('Y-m-d H:i'); ?>',
		
    }).on('changeDate', function( ev ){
		
		var newDate = $("#d_start_date").val();
		
		var DT = new Date( newDate );
		
		var	Y = DT.getFullYear();
		var	M = DT.getMonth() + 1;
		var	D = DT.getDate();
		var	H = DT.getHours();
		var	I = DT.getMinutes();
		var	S = DT.getSeconds();
		
		if( M < 10 ) M = '0'+M;
		if( D < 10 ) D = '0'+D;
		if( H < 10 ) H = '0'+H;
		if( I < 10 ) I = '0'+I;
		if( S < 10 ) S = '0'+S;
		
		var startDate = Y+'-'+M+'-'+D+' '+H+':'+I;
		
		$("#d_end_date").datetimepicker('remove');
		$("#d_end_date").datetimepicker({
			autoclose : true,
			startDate : startDate,
		});
		
		if( Date.parse( new Date( $("#d_start_date").val() ) ) > Date.parse( new Date( $("#d_end_date").val() ) ) ){
			$('#d_end_date').datetimepicker('setDate', DT );
		}
		
	});
	
	$("#d_end_date").datetimepicker({
		autoclose : true,
		startDate : '<?php echo date('Y-m-d H:i'); ?>',
	});
	
	
	
});
</script>

<?php include('jsfunctions/jsfunctions.php');?>
</body>
</html>
