<?php 
include('includes/configuration.php');
$gnrl->check_login();
extract( $_POST );
$page_title = "Referred";
$page = "referred";
$table = 'tbl_user';
$title2 = 'Referred';
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
	                                   LOWER(t1.v_name) like LOWER('%".$keyword."%') OR
	                                   LOWER(t3.v_name) like LOWER('%".$keyword."%') OR
	                                   LOWER(t1.v_id) like LOWER('%".$keyword."%') OR
	                                   LOWER(t3.v_id) like LOWER('%".$keyword."%') OR
	                                   LOWER(t3.l_data->>'referral_wallet_apply') like LOWER('%".$keyword."%')
	                                   
	                                )";
	                            }	

                             	if( isset( $_REQUEST['fr'] ) && $_REQUEST['fr'] != ''){
                             		if($_REQUEST['fr'] == 'signup'){
                                 		$wh .= " AND t1.l_data->>'referral_wallet_apply' = 'signup' ";
                             		}
                             		if($_REQUEST['fr'] == 'first_ride'){
                             			$wh .= " AND t1.l_data->>'referral_wallet_apply' = 'first_ride' ";
                             		}
                             	}else{
                             		$wh .= "";
                             	} 
	                            

								
										// (SELECT t2.v_id FROM tbl_user t2 where t2.id = COALESCE( t1.l_data->>'referral_user_id', '0' )::bigint ) AS referral_user_id,
										// (SELECT t3.v_name FROM tbl_user t3 where t3.id = COALESCE( t1.l_data->>'referral_user_id', '0' )::bigint ) AS referral_user_name
								$ssql="SELECT t1.* ,
										t3.v_id as referral_user_id,
										t3.v_name as referral_user_name,
										SUM( CASE  WHEN ( t1.l_data->>'referral_wallet_apply' = 'first_ride' ) THEN 1   ELSE 0   END  ) AS first_ride
										FROM 
										tbl_user  t1
										LEFT JOIN tbl_user t3 on t3.id = COALESCE( t1.l_data->>'referral_user_id', '0' )::bigint

										WHERE true AND (t1.l_data->>'referral_user_id')::INTEGER > '0' ".$wh." GROUP BY t1.id,t3.id ";
									
                                $sortby = $_REQUEST['sb'] = ( $_REQUEST['st'] ? $_REQUEST['sb'] : 't1.id' );
                           		$sorttype = $_REQUEST['st'] = ( $_REQUEST['st'] ? $_REQUEST['st'] : 'DESC' );
	                            
	                            $nototal = $dclass->numRows( $ssql );
	                            $pagen = new vmPageNav( $nototal, $limitstart, $limit, $form ,"black" );
	                            
	                           	$sqltepm = $ssql." ORDER BY ".$sortby." ".$sorttype." OFFSET ".$limitstart." LIMIT ".$limit;

	                           	#STORE QUERY IN SESSION FOR EXCEL REPORT
	                           	$_SESSION['report_query'][$page] = $ssql." ORDER BY ".$sortby." ".$sorttype;
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
	                                                <?php 
	                                                	$drop_arr = array(
	                                                		'signup' => 'Sign Up',
	                                                		'first_ride' => '1st Ride',
	                                                	);
	                                                ?>
                                                   <label style="margin-left:5px">Applied Wallet
													 <div class="clearfix"></div>
														<div class="pull-left" style="">
														<div>
														 	<select class="select2" name="fr" id="fr" onChange="document.frm.submit();">
																<option value="">--Select--</option>
																<?php echo $gnrl->get_keyval_drop($drop_arr,$_GET['fr']); ?>
															</select>
														</div>
													</div>
												</label>
	                                                <div class="clearfix"></div>
	                                            </div>
	                                        </div>
	                                        
	                                        <table class="table table-bordered" id="datatable" style="width:100%;" >
												
	                                            <?php 
	                                            echo $gnrl->renderTableHeader(array(
	                                            	'v_id' => array( 'order' => 1, 'title' => 'Customer/Driver ID' ),
	                                                'v_name' => array( 'order' => 1, 'title' => 'Customer/Driver Name' ),
	                                                'referred_by_id' => array( 'order' => 1, 'title' => 'Referred By ID' ),
	                                                'Referred_by_name' => array( 'order' => 1, 'title' => 'Referred By Name' ),
	                                                'referral_amount' => array( 'order' => 1, 'title' => 'Referral Amount'),
	                                                'type' => array( 'order' => 1, 'title' => ' Type'),
	                                            ));
	                                            ?>
	                                            <tbody>
	                                                <?php 
	                                                if( $nototal > 0 ){
														$i = 0;
														foreach( $row_Data as $row ){
															$l_data = json_decode($row['l_data'],true);
	                                                    	$i++;
	                                                    	?>
	                                                        <tr>
																<td><?php echo $row['v_id'];?></td>
																<td><?php echo $row['v_name'];?></td>
																<td><?php echo $row['referral_user_id'];?></td>
																<td><?php echo $row['referral_user_name'];?></td>
																<td><?php echo $l_data['referral_amount'];?></td>
																<td><?php echo $l_data['referral_wallet_apply'];?></td>
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
