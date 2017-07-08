<?php 
include('includes/configuration.php');
$gnrl->check_login();
extract( $_POST );
$page_title = "Driver Performance";
$page = "top_drivers";
$page2 = "driver_trips";
$page3 = "driver";
$table = 'tbl_ride';

$title2 = 'Rides';
$folder = 'vehicle_type';

$script = ( isset( $_REQUEST['script'] ) && ( $_REQUEST['script'] == 'add' || $_REQUEST['script'] == 'edit' || $_REQUEST['script'] == 'citywise' ) ) ? $_REQUEST['script'] : "";

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
                                View <?php echo $title2;?>
                             		<a href="reports.php?page=<?php echo $page; ?>&page_title=<?php echo $page_title; ?> " class="fright btn_reports">
										<button class="btn btn-primary" type="button">Export Excel </button>
									</a>
									<a href="table.php?page=<?php echo $page; ?>&page_title=<?php echo $page_title; ?> " class="fright btn_reports">
										<button class="btn btn-primary" type="button">Export PDF </button>
									</a>
                             	
                				
                        		
                            </h3>
                        </div>
                        <?php 
                        
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
	                                   LOWER(v_name) like LOWER('%".$keyword."%')
	                                   
	                                )";
	                            }	

                             	if( !isset( $_REQUEST['d_start_date'] ) && $_REQUEST['d_start_date'] == ''){
                                 	$start_date = date( 'Y-m-d' ); 
                             	}else{
                             		$start_date = $_REQUEST['d_start_date'];
                             	} 
                             	if( !isset( $_REQUEST['d_end_date'] ) && $_REQUEST['d_end_date'] == ''){
                             		$end_date = date( 'Y-m-d' ); 
                             	}else{
                             		$end_date = $_REQUEST['d_end_date'];
                             	}
                             	if( $start_date ){  
                             		$wh2 .= " AND c.d_time >= Date('".date( 'Y-m-d' , strtotime( $start_date ) )."')"; } if( $end_date ){  $wh2 .= " AND c.d_time <= Date('".date( 'Y-m-d' , strtotime( $end_date ) )."')"; 
                             	}
	                            

								$ssql="SELECT a.* 
										FROM
										(  SELECT  b.*, ( SELECT COUNT(*) AS ride_count FROM tbl_ride c WHERE c.i_driver_id = b.id AND e_status = 'complete' ".$wh2." ) ,
										( SELECT COALESCE( SUM( ( l_data->>'ride_driver_receivable')::numeric ), 0 ) AS driver_earning FROM tbl_ride c WHERE l_data->>'ride_driver_receivable' IS NOT NULL AND c.i_driver_id = b.id AND e_status = 'complete'  ".$wh2."  )   
											FROM  tbl_user b) a WHERE true AND ride_count > 0 ".$wh;
								 	
									
                                $sortby = $_REQUEST['sb'] = ( $_REQUEST['st'] ? $_REQUEST['sb'] : 'ride_count' );
                           		$sorttype = $_REQUEST['st'] = ( $_REQUEST['st'] ? $_REQUEST['st'] : 'DESC' );
	                            
	                            $nototal = $dclass->numRows( $ssql );
	                            $pagen = new vmPageNav( $nototal, $limitstart, $limit, $form ,"black" );
	                            
	                           	$sqltepm = $ssql." ORDER BY ".$sortby." ".$sorttype." OFFSET ".$limitstart." LIMIT ".$limit;

	                           	#STORE QUERY IN SESSION FOR EXCEL REPORT
	                           	$_SESSION['report_query'][$page] = $ssql." ORDER BY ".$sortby." ".$sorttype;
	                            $restepm = $dclass->query($sqltepm);
	                            $row_Data = $dclass->fetchResults($restepm);
	                            // echo "<script>  </script>";

								?>
								
	                            <div class="content">
	                                <form name="frm" action="" method="get" >
	                                    <div class="table-responsive">
	                                    
	                                        <div class="row">
	                                            <div class="col-sm-12">

	                                                <div class="pull-right">
	                                                    <div class="dataTables_filter" id="datatable_filter">
	                                                        <label style="margin-top: 20px;">

	                                                            <input type="text" aria-controls="datatable" class="form-control fleft" placeholder="Search" name="keyword" value="<?php echo isset( $_REQUEST['keyword'] ) ? $_REQUEST['keyword'] : ""?>" style="width:auto;"/>
	                                                            <button type="submit" class="btn btn-primary fleft" style="margin-left:0px;"><span class="fa fa-search"></span></button>
	                                                            

	                                                        </label>

	                                                    </div>
	                                                    
	                                                    <?php if(isset($_REQUEST['keyword']) && $_REQUEST['keyword'] != '' || isset($_REQUEST['srch_driver']) && $_REQUEST['srch_driver'] != '' || isset($_REQUEST['srch_filter_status']) && $_REQUEST['srch_filter_status'] != ''
	                                                       || isset($_REQUEST['srch_filter_city']) && $_REQUEST['srch_filter_city'] != '' || isset($_REQUEST['srch_filter_type']) && $_REQUEST['srch_filter_type'] != '' || isset($_REQUEST['d_start_date']) && $_REQUEST['d_start_date'] != ''  ){ ?>
	                                                                <a href="<?php echo $page ?>.php" class="fright" style="margin: -10px 15px 20px 0px ;" >
	                                                                <h4> Clear Search </h4></a>
	                                                        <?php } ?>

	                                                        
	                                                </div>
													
	                                                <div class="pull-left">
	                                                    <div id="" class="dataTables_length">
	                                                        <label><?php $pagen->writeLimitBox(); ?></label>
	                                                    </div>
	                                                </div>
                                                    <label style="margin-left:15px">
														Start Date
														<div class="clearfix"></div> 
														<div class="pull-left" style="">
															<div class="input-group date datetime" data-min-view="2" data-date-format="yyyy-mm-dd">
															    <input class="form-control" type="date" id="d_start_date" name="d_start_date" value="<?php echo ($_REQUEST['d_start_date'])?$_REQUEST['d_start_date']:date('Y-m-d'); ?>" data-date-format="yyyy-mm-dd" readonly="" onChange="document.frm.submit();" placeholder="select">
															    <span class="input-group-addon btn btn-primary"><span class="glyphicon glyphicon-th"></span></span>
															</div>
                                                        </div>
                                                    </label>
                                                   
                                                    <label style="margin-left:15px">
														End Date
														<div class="clearfix"></div> 
														<div class="pull-left" style="">
															<div class="input-group date datetime" data-min-view="2" data-date-format="yyyy-mm-dd">
															    <input class="form-control" type="date" id="d_end_date" name="d_end_date"  value="<?php echo ($_REQUEST['d_end_date'])?$_REQUEST['d_end_date']:date('Y-m-d'); ?>" data-date-format="yyyy-mm-dd" readonly="" onclick="datetimepicker()" onChange="document.frm.submit();" placeholder="select">
															    <span class="input-group-addon btn btn-primary"><span class="glyphicon glyphicon-th"></span></span>
															</div>
                                                        </div>
                                                    </label>
	                                                <div class="clearfix"></div>
	                                            </div>
	                                        </div>
	                                        
	                                        <table class="table table-bordered" id="datatable" style="width:100%;" >
												
	                                            <?php 
	                                            echo $gnrl->renderTableHeader(array(
	                                                'v_name' => array( 'order' => 1, 'title' => 'Driver' ),
	                                                'ride_count' => array( 'order' => 1, 'title' => 'Total Trip' ),
	                                                'driver_earning' => array( 'order' => 1, 'title' => 'Amount'),
	                                            ));
	                                            ?>
	                                            <tbody>
	                                                <?php 
	                                                if( $nototal > 0 ){
														$i = 0;
														foreach( $row_Data as $row ){
	                                                    	$i++;
	                                                    	?>
	                                                        <tr>
																<td><?php echo $row['v_name'];?></td>
																<td><?php echo $row['ride_count'];?></td>
																<td>
																	<?php echo $row['driver_earning'];?>
																	
																</td>
																
	                                                        </tr><?php 
	                                                    }
	                                                }
	                                                else{?>
	                                                    <tr><td colspan="8" id="no_record">No Record found.</td></tr><?php 
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
                        ?>
                    </div>
                </div>
            </div>
        </div>
	</div>
</div>

<?php include('_scripts.php');?>
<?php include('jsfunctions/jsfunctions.php');?>
<script type="text/javascript">
	export_reports(<?php echo $nototal; ?>);
</script>


</body>
</html>
