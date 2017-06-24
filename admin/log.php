<?php 
include('includes/configuration.php');
$gnrl->check_login();

// _P($_REQUEST);
// exit;
    extract( $_POST );
    $page_title = "Manage Log";
    $page = "log";
    $table = 'tbl_user_log';
    $title2 = 'Log';
    // $v_role ='user';
    $script = ( isset( $_REQUEST['script'] ) && ( $_REQUEST['script'] == 'add' || $_REQUEST['script'] == 'edit' ) ) ? $_REQUEST['script'] : "";
   


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
                            </h3>
                        </div>
                        <?php 
                            $id = $_REQUEST['id'];
                            $v_type = $_REQUEST['v_type'];
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
                                   LOWER(d_loged_in) like LOWER('%".$keyword."%')  OR
                                   LOWER(d_loged_in) like LOWER('%".$keyword."%')  
                                )";
                            }
                             $ssql = "SELECT l.*,
                                        u.v_name as u_name
                                        FROM ".$table." as l
                                        LEFT JOIN tbl_user as u
                                        ON l.i_user_id=u.id 
                                    WHERE i_user_id='".$id."' AND v_type='".$v_type."' ".$wh;
                                        
                            $restepm = $dclass->query($ssql);
                            $row_Data = $dclass->fetchResults($restepm);
                            $nototal=count($row_Data);
                            $type_arr = array(
                                'login' => 'Login Log',
                                'duty' =>'Available Log'
                            );
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
                                                    <label style="margin-left:5px">Type 
                                                         <div class="clearfix"></div>
                                                            <div class="pull-left" style="">
                                                            <div>
                                                             <select class="select2" name="v_type" id="v_type" onChange="document.frm.submit();">
                                                                    <option value="" >--Select--</option>
                                                                     <?php echo $gnrl->get_keyval_drop($type_arr,$_GET['v_type']); ?>
                                                                    </select>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                                <div class="clearfix"></div>
                                                  
                                                  
                                            </div>
                                        </div>
                                        <div class="pull-left">
                                            <h3>
                                                Driver Name : <?php echo $row_Data[0]['u_name']; ?>
                                            </h3>
                                            
                                        </div>
                                        <table class="table table-bordered" id="datatable" style="width:100%;" >
                                            <?php
                                                echo $gnrl->renderTableHeader(array(
                                                    'd_loged_in' => array( 'order' => 0, 'title' => 'Loged-In Time' ),
                                                    'd_loged_out' => array( 'order' => 0, 'title' => 'Loged-Out Time' ),
                                                    'action' => array( 'order' => 0, 'title' => 'Total Hours' ),
                                                ));
                                                ?>
                                            <tbody>
                                                <?php 
                                                if($nototal > 0){
                                                    	
                                                    foreach($row_Data as $row){
                                                    	
                                                    	?>
                                                        <tr>
                                                        	<td>
                                                                <?php echo $row['d_loged_in']; ?>
                                                            </td>
                                                            <td><?php echo $row['d_loged_out'];?></td>
                                                            <td><?php
                                                                echo $t_hours = $gnrl->date_Difference($row['d_loged_out'],$row['d_loged_in']);
                                                            ?></td>
                                                        </tr><?php 
                                                        $g_total_hours += $t_hours;
                                                    } ?>
                                                    <div class="pull-right">
                                                        <h3>
                                                            <?php 
                                                            if(isset($g_total_hours) && $g_total_hours != ''){ ?>

                                                             Total Hours :- <?php echo $g_total_hours; ?>
                                                            <?php }
                                                            ?>
                                                        </h3>
                                                    </div>
                                                <?php }
                                                else{?>
                                                    <tr><td colspan="8">No Record found.</td></tr><?php 
                                                }?>
                                            </tbody>
                                        </table>
                                        <input type="hidden" name="id" value="<?php echo @$_REQUEST['id'];?>" />
                                    </div>
                                </form>
                            </div> <?php 
                        
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
