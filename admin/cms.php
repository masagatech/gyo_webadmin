<?php 
include('includes/configuration.php');
$gnrl->check_login();
// _P(DEFAULT_LANGUAGE);
// exit;
	extract( $_POST );
	$page_title = "Manage CMS";
	$page = "cms";
	$table = 'tbl_cms';
	$title2 = 'CMS';
	// $folder = 'brand';
	// $upload_path = UPLOAD_PATH.$folder.'/';
	$lanuages = $dclass->select('*','tbl_language');
    // _P($lanuages);
    // exit;
	$script = ( isset( $_REQUEST['script'] ) && ( $_REQUEST['script'] == 'add' || $_REQUEST['script'] == 'edit' ) ) ? $_REQUEST['script'] : "";
	
	## Insert Record in database starts
	if(isset($_REQUEST['submit_btn']) && $_REQUEST['submit_btn']=='Submit'){

		$j_title = json_encode( $j_title );
		$j_content = json_encode( $j_content );
        
		$ins = array(
			'v_key' => $v_key,
			'j_title' => $j_title,
			'j_content' => $j_content,
        );
        $key_exit = $dclass->select('*',$table," AND v_key = '".$v_key."'");
        if(!empty($key_exit)){
         $gnrl->redirectTo($page.'.php?succ=0&msg=key_exit&a=2&script=add');
        }

	    $id = $dclass->insert( $table, $ins );
        // _P($id);
        // exit;
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
				$j_content = json_encode( $j_content );
				
				$ins = array(
					'v_key' => $v_key,
					'j_title' => $j_title,
					'j_content' => $j_content,
				);
				$dclass->update( $table, $ins, " id = '".$id."' ");
				$gnrl->redirectTo($page.'.php?succ=1&msg=edit&a=2&script=edit&id='.$_REQUEST['id']);
			}
			else {
				$row = $dclass->select('*',$table," AND id = '".$id."'");
				$row = $row[0];
                extract( $row );
                $l_data=json_decode($l_data,true);
			}
		}
	}

	

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">
<link rel="shortcut icon" href="images/favicon.png">
<title><?php echo SITE_NAME;?>-<?php echo $page_title;?></title>

<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,400italic,700,800' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=Raleway:300,200,100' rel='stylesheet' type='text/css'>

<!-- Bootstrap core CSS -->
<link href="js/bootstrap/dist/css/bootstrap.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="js/jquery.gritter/css/jquery.gritter.css"/>
<link rel="stylesheet" href="fonts/font-awesome-4/css/font-awesome.min.css">
<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
   <![endif]-->
<link rel="stylesheet" type="text/css" href="js/jquery.nanoscroller/nanoscroller.css" />
<link rel="stylesheet" type="text/css" href="js/jquery.easypiechart/jquery.easy-pie-chart.css" />
<link rel="stylesheet" type="text/css" href="js/bootstrap.switch/bootstrap-switch.css" />
<link rel="stylesheet" type="text/css" href="js/bootstrap.datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="js/jquery.select2/select2.css" />
<link rel="stylesheet" type="text/css" href="js/bootstrap.slider/css/slider.css" />
<link rel="stylesheet" type="text/css" href="js/jquery.datatables/bootstrap-adapter/css/datatables.css" />
<link href="js/jquery.icheck/skins/square/blue.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="js/dropzone/css/dropzone.css" />
<link rel="stylesheet" type="text/css" href="js/jquery.niftymodals/css/component.css" />
<link href="css/style.css" rel="stylesheet" />
<link href="css/common.css" rel="stylesheet" />
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
                                <?php 
                                    if( !$script ){
                                        if( !$script && 1){?>
                                            <a href="<?php echo $page?>.php?script=add" class="fright">
                                                <button class="btn btn-primary" type="button">Add</button>
                                            </a>
                                    
								         <?php
                                        }
                                    } 
                                ?>
                            </h3>
                        </div>
                        <?php 
                        if( ($script == 'add' || $script == 'edit') && 1 ){?>
                        	<form role="form" action="#" method="post" parsley-validate novalidate enctype="multipart/form-data" >
                                <div class="row">
                                    
                                        
                                    <div class="col-md-12">
                                        
                                        <div class="content">
                                            
										   	
                                            <div class="form-group">
                                                <label>Key</label>
                                                <input type="text" class="form-control" id="v_key" name="v_key" value="<?php echo $v_key; ?>" required />
                                            </div>
											
											<div class="row" >
												
												
												<?php
												$valArr = json_decode( $j_title, true );
												foreach( $globLangArr as $_langK => $_langV ){ 
													$key = 'j_title';
													?>
													<div class="col-md-12">
														<div class="form-group"> 
															<label>Content Title (<?php echo $_langV?>)</label>
															<input type="text" name="<?php echo $key;?>[<?php echo $_langK?>]" class="form-control" value="<?php echo $valArr[$_langK];?>" >
														</div>
													</div> <?php
												} ?>
												
												<?php
												$valArr = json_decode( $j_content, true );
												foreach( $globLangArr as $_langK => $_langV ){ 
													$key = 'j_content';
													?>
													<div class="col-md-12">
														<div class="form-group"> 
															<label>Content Body (<?php echo $_langV?>)</label>
															<textarea name="<?php echo $key;?>[<?php echo $_langK?>]" class="form-control ckeditor" style="min-height:200px" >
                                                            <?php echo $valArr[$_langK];?></textarea>
														</div>
													</div> <?php
												} ?>


												
											
											</div>
												
                                            <div class="form-group">
                                                <button class="btn btn-primary" type="submit" name="submit_btn" value="<?php echo ( $script == 'edit' ) ? 'Update' : 'Submit'; ?>"><?php echo ( $script == 'edit' ) ? 'Update' : 'Submit'; ?></button>
                                                <a href="<?php echo $page?>.php"><button class="btn fright" type="button" name="submit_btn">Cancel</button></a> 
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
							</form>
							
							<div class="md-modal colored-header custom-width md-effect-9" id="form-primary" style="width:80% !important;" >
                                <div class="md-content">
                                    <div class="modal-header">
                                    	<h3>Keywords for Email Templates</h3>
                                    	<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    </div>
                                    <div class="modal-body form">
                                        <div class="row" style="margin-top:0;" >
                                            <div class="form-group col-md-12 no-margin">
                                            	<div class="content">
                                                	<div class="row" style="margin-top:0;" >
                                                    	<?php
														if( count( $email_keywords ) ){
															foreach( $email_keywords as $kKw => $vKw ){
																?>
                                                                <div class="form-group col-md-3 no-margin" style="margin-bottom:15px !important;" >
                                                                    <label><?php echo $vKw; ?></label>
                                                                    <input type="name" class="form-control" value="<?php echo $kKw; ?>" readonly >
                                                                </div>
                                                                <?php
															}
														} ?>
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
                                           LOWER(v_key) like LOWER('%".$keyword."%') 
                                    )";
                                }
                                $checked="";
                                if( isset( $_REQUEST['deleted'] ) ){
                                    $wh .= " AND i_delete='1'";
                                    $checked="checked";
                                }else{
                                    $wh .= " AND i_delete='0'";
                                }
                                $ssql = "SELECT * FROM ".$table." WHERE true ".$wh;

                                $sortby = $_REQUEST['sb'] = ( $_REQUEST['st'] ? $_REQUEST['sb'] : 'v_key' );
                                $sorttype = $_REQUEST['st'] = ( $_REQUEST['st'] ? $_REQUEST['st'] : 'DESC' );

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
                                                    'j_title' => array( 'order' => 1, 'title' => 'Title' ),
                                                    'v_key' => array( 'order' => 1, 'title' => 'Key' ),
                                                    'action' => array( 'order' => 0, 'title' => 'Action' ),
                                                ));
                                                ?>
                                                <tbody>
                                                    <?php 
                                                    if($nototal > 0){
                                                        foreach($row_Data as $row){
                                                            $j_title=json_decode($row['j_title'],true);
                                                            ?>
                                                            <tr>
                                                                <td>
                                                                    <?php echo $j_title[DEFAULT_LANGUAGE]; ?>
                                                                </td>
                                                                <td>
                                                                    <?php echo $row['v_key']; ?>
                                                                </td>
                                                                <td>
                                                                    <?php if(1){?> 
                                                                        <div class="btn-group">
                                                                        <button class="btn btn-default btn-xs" type="button">Actions</button>
                                                                        <button data-toggle="dropdown" class="btn btn-xs btn-primary dropdown-toggle" type="button">
                                                                            <span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                                                                        </button>
                                                                        <ul role="menu" class="dropdown-menu pull-right">
                                                                            <li><a href="<?php echo $page?>.php?a=2&script=edit&id=<?php echo $row['id'];?>">Edit</a></li>
                                                                            <li><a href="javascript:;" onclick="confirm_delete('<?php echo $page;?>','<?php echo $row['id'];?>');">Delete</a></li>
                                                                        </ul>
                                                                    </div>
                                                                    <?php } ?>
                                                                    
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
                            }else{ ?>
                                    
                            <?php 
                            }
                        }?>
                    </div>
                </div>
            </div>
        </div>
	</div>
</div>

		


<script src="js/jquery.js"></script> 
<script src="js/jquery.select2/select2.min.js" type="text/javascript"></script> 
<script src="js/jquery.parsley/parsley.js" type="text/javascript"></script> 
<script type="text/javascript" src="js/jquery.nanoscroller/jquery.nanoscroller.js"></script> 
<script type="text/javascript" src="js/jquery.sparkline/jquery.sparkline.min.js"></script> 
<script type="text/javascript" src="js/jquery.easypiechart/jquery.easy-pie-chart.js"></script> 
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&amp;sensor=false"></script> 
<script type="text/javascript" src="js/behaviour/general.js"></script> 
<script src="js/jquery.ui/jquery-ui.js" type="text/javascript"></script> 
<script type="text/javascript" src="js/jquery.nestable/jquery.nestable.js"></script> 
<script type="text/javascript" src="js/bootstrap.switch/bootstrap-switch.min.js"></script> 
<script type="text/javascript" src="js/bootstrap.datetimepicker/js/bootstrap-datetimepicker.min.js"></script> 
<script src="js/jquery.select2/select2.min.js" type="text/javascript"></script> 
<script src="js/bootstrap.slider/js/bootstrap-slider.js" type="text/javascript"></script> 
<script type="text/javascript" src="js/jquery.gritter/js/jquery.gritter.js"></script> 
<script type="text/javascript" src="js/jquery.niftymodals/js/jquery.modalEffects.js"></script>   
<script type="text/javascript" src="js/jquery.datatables/jquery.datatables.min.js"></script> 
<script type="text/javascript" src="js/jquery.datatables/bootstrap-adapter/js/datatables.js"></script> 
<script type="text/javascript" src="js/jquery.icheck/icheck.min.js"></script>
<script type="text/javascript" src="js/dropzone/dropzone.js"></script>
<script type="text/javascript" src="js/ckeditor/ckeditor.js"></script> 

<script type="text/javascript">
	$(document).ready(function(){
		//initialize the javascript
		App.init();
		App.textEditor();
		//$('#summernote').summernote();
		$('.md-trigger').modalEffects();
	});
</script> 
<style>
	.cke_contents{ height:300px !important; }
	.banner .cke_contents{ height:100px !important; }
</style>
<!-- Bootstrap core JavaScript
================================================== --> 
<!-- Placed at the end of the document so the pages load faster --> 
<script src="js/behaviour/voice-commands.js"></script> 
<script src="js/bootstrap/dist/js/bootstrap.min.js"></script> 
<script type="text/javascript" src="js/jquery.flot/jquery.flot.js"></script> 
<script type="text/javascript" src="js/jquery.flot/jquery.flot.pie.js"></script> 
<script type="text/javascript" src="js/jquery.flot/jquery.flot.resize.js"></script> 
<script type="text/javascript" src="js/jquery.flot/jquery.flot.labels.js"></script>
<?php include('jsfunctions/jsfunctions.php');?>
</body>
</html>
