<?php 
include('includes/configuration.php');
$gnrl->check_login();

// _P($_REQUEST);
// exit;
	extract( $_POST );
	$page_title = "Manage Languages";
	$page = "languages";
	$table = 'tbl_language';
	$title2 = 'Languages';
	// $folder = 'brand';
	// $upload_path = UPLOAD_PATH.$folder.'/';
	
	$script = ( isset( $_REQUEST['script'] ) && ( $_REQUEST['script'] == 'add' || $_REQUEST['script'] == 'edit' ) ) ? $_REQUEST['script'] : "";
	
	## Insert Record in database starts
	if( isset( $_REQUEST['submit_btn'] ) && $_REQUEST['submit_btn'] == 'Submit' ){
		$ins = array(
			'v_key'  => $v_key,
			'v_name' => $v_name,
			'e_status' 	=> $e_status,
		);
		$id = $dclass->insert( $table, $ins );
		$gnrl->redirectTo($page.".php?succ=1&msg=add");
	}

	## Delete Record from the database starts
	if( isset( $_REQUEST['a']) && $_REQUEST['a']==3) {
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
		if(isset($_REQUEST['id']) && $_REQUEST['id']!="") {

			$id = $_REQUEST['id'];
			if( isset( $_REQUEST['submit_btn'] ) && $_REQUEST['submit_btn'] == 'Update' ) {
				
				$ins = array(
					'v_key'   => $v_key,
					'v_name'  => $v_name,
					'e_status'=> $e_status,
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
					
						<?php include('_page_list_header.php'); ?>
						
                        <?php 
                        if( ( $script == 'add' || $script == 'edit' )){?>
                        	<form role="form" action="#" method="post" parsley-validate novalidate enctype="multipart/form-data" >
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="content">
                                            <div class="form-group">
                                                <label>Name  <span>*</span></label>
                                                <input type="text" class="form-control" id="v_name" name="v_name" value="<?php echo $v_name; ?>" required  />
                                            </div>
											<div class="form-group">
                                                <label>Key <span>*</span></label>
                                                <input type="text" class="form-control" id="v_key" name="v_key" value="<?php echo $v_key; ?>" required <?php echo $script == 'edit' ? 'readonly' : '' ?> />
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
                                        LOWER(v_key) like LOWER('%".$keyword."%') OR
                                         LOWER(e_status) like LOWER('%".$keyword."%')
                                    )";
                                }
                                
                                $ssql = "SELECT * FROM ".$table." WHERE true ".$wh;
                                            
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
                                                            </label>
                                                        </div>
                                                       <?php 
                                                                    if(isset($_REQUEST['keyword']) && $_REQUEST['keyword'] != ''){ ?>
                                                                            <a href="<?php echo $page ?>.php" class="fright" > Clear Search </a>
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
                                                    'v_key' => array( 'order' => 1, 'title' => 'Key' ),
                                                    'e_status' => array( 'order' => 1, 'title' => 'Status' ),
                                                    'action' => array( 'order' => 0, 'title' => 'Action' ),
                                                ));
                                                ?>
                                                <tbody>
                                                    <?php 
                                                    if($nototal > 0){
                                                            $i=0;
                                                        foreach($row_Data as $row){
                                                            $i++;
                                                            ?>
                                                            <tr>
                                                                <td>
                                                                    
                                                                        <?php echo $row['v_name']; ?>
                                                                  
                                                                </td>
                                                                <td><?php echo $row['v_key']; ?></td>
                                                                
                                                                <td><?php echo $row['e_status'];?></td>
                                                                
																<?php include('_page_list_action.php');?>
																
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
                        }?>
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
