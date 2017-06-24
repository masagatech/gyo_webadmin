<?php 
include('includes/configuration.php');
$gnrl->check_login();

    extract( $_POST );
    $page_title = "Manage Cancel Reason";
    $page = "cancel_reason";
    $table = 'tbl_ride_cancel_reason';
    $title2 = 'Cancel Reason';
    // $v_role ='user';
    // $folder='users';
    $script = ( isset( $_REQUEST['script'] ) && ( $_REQUEST['script'] == 'add' || $_REQUEST['script'] == 'edit' ) ) ? $_REQUEST['script'] : "";
      
    
    ## Insert Record in database starts
    if(isset($_REQUEST['submit_btn']) && $_REQUEST['submit_btn']=='Submit'){
       
      
        if($i_status=='active'){
            $i_status=1;
        }else{
            $i_status=0;
        }
        $ins = array(
            'j_title'  => json_encode($j_title),
            'v_type' =>$v_type,
            'i_order'   => $i_order,
            'i_status'  => 1,
            'd_added' => date('Y-m-d H:i:s'),
            'd_modified' => date('Y-m-d H:i:s')
        );
        // exit;
        $id = $dclass->insert( $table, $ins );
        $gnrl->redirectTo($page.".php?succ=1&msg=add");
       
        
    }

    ## Delete Record from the database starts
    if(isset($_REQUEST['a']) && $_REQUEST['a']==3) {
        if(isset($_REQUEST['id']) && $_REQUEST['id']!="") {
            $id = $_REQUEST['id'];
            if($_REQUEST['chkaction'] == 'delete' ) {
                if( 1 ){
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
                 if( 1 ){
                    $ins = array('i_status'=>'1');
                    $dclass->update( $table, $ins, " id = '".$id."'");
                    $gnrl->redirectTo($page.".php?succ=1&msg=multiact");
                 }else{
                    $gnrl->redirectTo($page.".php?succ=0&msg=not_auth");
                 }
            }
            // make records inactive
            else if($_REQUEST['chkaction'] == 'inactive'){
                if( 1 ){
                    $ins = array( 'i_status' => '0' );
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
                
                    if($i_status=='active'){
                        $i_status=1;
                    }else{
                        $i_status=0;
                    }
                
                    $ins = array(
                        'j_title'  => json_encode($j_title),
                        'v_type' =>$v_type,
                        'i_order'   => $i_order,
                        'i_status'  => $i_status,
                        'd_modified' => date('Y-m-d H:i:s')
                    );
                    $dclass->update( $table, $ins, " id = '".$id."' ");
                    $gnrl->redirectTo($page.'.php?succ=1&msg=edit&a=2&script=edit&id='.$_REQUEST['id']);
              
            }
            else {
                $row = $dclass->select('*',$table," AND id = '".$id."'");

                $row = $row[0];
                // _P($row);
                // exit;
                extract( $row );
                $j_title=json_decode($j_title,true);
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
                                            <?php 
                                                foreach ($globLangArr as $l_key => $l_value) { ?>
                                                <div class="form-group">
                                                    <label>Reason [<?php echo $l_key ?>] <?php echo  $gnrl->getAstric(); ?></label>
                                                    <input type="text" class="form-control" id="j_title[<?php echo $l_key; ?>]" name="j_title[<?php echo $l_key; ?>]" value="<?php echo $j_title[$l_key]; ?>" required />
                                                </div>
                                            <?php }
                                            ?>
                                            
                                            <div class="form-group">
                                                <label>User/Driver <?php echo $gnrl->getAstric(); ?></label>
                                                <select class="select2 required" name="v_type" id="v_type">
                                                <option value="">--Select--</option>
                                                    <?php $gnrl->getDropdownList(array('user','driver'),$v_type); ?>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                    <label>Order <?php echo  $gnrl->getAstric(); ?></label>
                                                    <input type="number" class="form-control" id="i_order" name="i_order" value="<?php echo $i_order; ?>" required />
                                            </div>
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select class="select2" name="i_status" id="i_status">
                                                    <?php
                                                        $status_arr=array(
                                                            '1' =>'active',
                                                            '0' =>'inactive'
                                                        );
                                                     ?>
                                                     <?php 
                                                        if($i_status==0){
                                                            $i_status='inactive';
                                                        }else{
                                                            $i_status='active'; 
                                                        }
                                                     ?>
                                                    <?php $gnrl->getDropdownList($status_arr,$i_status); ?>
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
                                       j_title->>'en' LIKE '".$keyword."' OR
                                       v_type LIKE '".$keyword."'
                                    )";
                                }
                                if( isset( $_REQUEST['deleted'] ) ){
                                    $keyword =  trim( $_REQUEST['keyword'] );
                                    $wh .= " AND i_delete='1'";
                                    $checked="checked";
                                }else{
                                    $wh .= " AND i_delete='0'";
                                }
                                $ssql = "SELECT * FROM ".$table." WHERE true ".$wh;
                                

                                $sortby = $_REQUEST['sb'] = ( $_REQUEST['st'] ? $_REQUEST['sb'] : 'i_order' );
                                $sorttype = $_REQUEST['st'] = ( $_REQUEST['st'] ? $_REQUEST['st'] : 'ASC' );

                                
                                
                                $nototal = $dclass->numRows($ssql);
                                $pagen = new vmPageNav($nototal, $limitstart, $limit, $form ,"black");
                                $sqltepm = $ssql." ORDER BY ".$sortby." ".$sorttype." OFFSET ".$limitstart." LIMIT ".$limit;
                                $restepm = $dclass->query($sqltepm);
                                $row_Data = $dclass->fetchResults($restepm);
                                // _P($row_Data);
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
                                                    'j_title' => array( 'order' => 1, 'title' => 'Reason' ),
                                                    'v_type' => array( 'order' => 1, 'title' => 'Type' ),
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
                                                                <?php 
                                                                   $j_title = json_decode($row['j_title'],true);
                                                                ?>
                                                                <td><?php echo $j_title['en'];  ?></td>
                                                                <td><?php echo $row['v_type'];?></td>
                                                                <td><?php echo $gnrl->displaySiteDate($row['d_added']) ; ?></td>
                                                                <td>
                                                                    <?php 
                                                                        if(1){ ?>

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
                                                                                    <li><a href="<?php echo $page;?>.php?a=3&amp;chkaction=active&amp;id=<?php echo $row['id'];?>">Active</a></li>
                                                                                    <li><a href="<?php echo $page;?>.php?a=3&amp;chkaction=inactive&amp;id=<?php echo $row['id'];?>">Inactive</a></li>
                                                                                    <li><a href="javascript:;" onclick="confirm_delete('<?php echo $page;?>','<?php echo $row['id'];?>');">Delete</a></li>
                                                                                <?php }
                                                                            ?>
                                                                           
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
