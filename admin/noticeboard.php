<?php 
include('includes/configuration.php');
$gnrl->check_login();

	$page_title = "Noticeboard Settings";
	$page = "noticeboard";
	$table = 'tbl_sitesetting';
	extract( $_POST );
	$site_setting_key = 'NOTICE_BOARD';
	
	if( isset( $_REQUEST['submit_btn'] ) && $_REQUEST['submit_btn'] == 'Update' ){
		
		$arr['l_value'] = addslashes( stripcslashes( $$site_setting_key ) );
		$gnrl->save_site_setting( $site_setting_key, $arr['l_value'] );
		$gnrl->redirectTo( $page.'.php?succ=1&msg=edit');
	}
	else {
		## Retrieve All Styles
		$NOTICE_BOARD = $gnrl->getSettings( $site_setting_key );
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
<link href='http://fonts.googleapis.com/css?family=Raleway:100' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=Open+Sans+Condensed:300,700' rel='stylesheet' type='text/css'>

<!-- Bootstrap core CSS -->
<link href="js/bootstrap/dist/css/bootstrap.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="js/bootstrap.switch/bootstrap-switch.css" />
<link rel="stylesheet" type="text/css" href="js/bootstrap.datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="js/jquery.select2/select2.css" />
<link rel="stylesheet" type="text/css" href="js/bootstrap.slider/css/slider.css" />
<link rel="stylesheet" type="text/css" href="js/bootstrap.wysihtml5/src/bootstrap-wysihtml5.css"></link>
<link rel="stylesheet" type="text/css" href="js/bootstrap.summernote/dist/summernote.css" />
<link rel="stylesheet" href="fonts/font-awesome-4/css/font-awesome.min.css">
<link rel="stylesheet" href="css/pygments.css">
<link rel="stylesheet" type="text/css" href="js/jquery.nanoscroller/nanoscroller.css" />

<link rel="stylesheet" type="text/css" href="js/jquery.gritter/css/jquery.gritter.css" />


<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <![endif]-->

<link rel="stylesheet" type="text/css" href="js/jquery.easypiechart/jquery.easy-pie-chart.css" />
<link rel="stylesheet" type="text/css" href="js/jquery.datatables/bootstrap-adapter/css/datatables.css" />
<link href="js/jquery.icheck/skins/square/blue.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="js/dropzone/css/dropzone.css" />

<link href="css/style.css" rel="stylesheet" />
<link href="css/common.css" rel="stylesheet" />
</head>

<body>

<!-- Fixed navbar -->
<?php include('inc/header.php');?>
<div id="cl-wrapper" class="fixed-menu">
	<?php include('inc/sidebar.php');?>
	<div class="container-fluid" id="pcont">
    	<?php include('all_page_head.php'); ?>
		
        <form role="form" action="#" method="post" parsley-validate novalidate enctype="multipart/form-data" >
            <div class="cl-mcont">
            	<?php include('all_alert_msg.php'); ?>
                
                <div class="row">
                	<div class="col-sm-12 col-md-12">
                    	<div class="block-flat">
                        	<div class="header"><h3><?php echo $page_title;?></h3></div>
                            <div class="content">
                                <div class="form-group">
									<?php
									$key = $site_setting_key;
									?>
                                    <label>Noticeboard</label>
                                    <textarea class="form-control" id="<?php echo $key;?>"  name="<?php echo $key;?>" style="height:100px !important;" required ><?php echo stripslashes($NOTICE_BOARD);?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <button class="btn btn-primary" type="submit" name="submit_btn" value="Update">Update</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                
            </div>
            
        </form>
		
	</div>
</div>

<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery.select2/select2.min.js" ></script>
<script type="text/javascript" src="js/jquery.parsley/parsley.js" ></script>
<script type="text/javascript" src="js/bootstrap.slider/js/bootstrap-slider.js" ></script>
<script type="text/javascript" src="js/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="js/ckeditor/adapters/jquery.js"></script>
<script type="text/javascript" src="js/bootstrap.summernote/dist/summernote.min.js"></script>
<script type="text/javascript" src="js/bootstrap.wysihtml5/lib/js/wysihtml5-0.3.0.js"></script>
<script type="text/javascript" src="js/bootstrap.wysihtml5/src/bootstrap-wysihtml5.js"></script>
<script type="text/javascript" src="js/jquery.nanoscroller/jquery.nanoscroller.js"></script>
<script type="text/javascript" src="js/jquery.nestable/jquery.nestable.js"></script>
<script type="text/javascript" src="js/behaviour/general.js"></script>
<script type="text/javascript" src="js/jquery.ui/jquery-ui.js" ></script>
<script type="text/javascript" src="js/bootstrap.switch/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="js/bootstrap.datetimepicker/js/bootstrap-datetimepicker.min.js"></script>

<script type="text/javascript" src="js/jquery.sparkline/jquery.sparkline.min.js"></script> 
<script type="text/javascript" src="js/jquery.easypiechart/jquery.easy-pie-chart.js"></script> 
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp&amp;sensor=false"></script> 
<script type="text/javascript" src="js/jquery.gritter/js/jquery.gritter.js"></script> 
<script type="text/javascript" src="js/jquery.datatables/jquery.datatables.min.js"></script> 
<script type="text/javascript" src="js/jquery.datatables/bootstrap-adapter/js/datatables.js"></script> 
<script type="text/javascript" src="js/jquery.icheck/icheck.min.js"></script>
<script type="text/javascript" src="js/dropzone/dropzone.js"></script>



<script type="text/javascript">
	$(document).ready(function(){
		//initialize the javascript
		App.init();
		App.textEditor();
		
		//CKEDITOR.replace( 'l_description',{ height: '500px', });
	
		$('#some-textarea').wysihtml5();
		$('#summernote').summernote();
	});
</script>
<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script type="text/javascript" src="js/behaviour/voice-commands.js"></script>
<script type="text/javascript" src="js/bootstrap/dist/js/bootstrap.min.js"></script>

<!--

<script type="text/javascript" src="js/bootstrap/dist/js/bootstrap.min.js"></script> 
<script type="text/javascript" src="js/jquery.flot/jquery.flot.js"></script> 
<script type="text/javascript" src="js/jquery.flot/jquery.flot.pie.js"></script> 
<script type="text/javascript" src="js/jquery.flot/jquery.flot.resize.js"></script> 
<script type="text/javascript" src="js/jquery.flot/jquery.flot.labels.js"></script>
-->
<?php include('jsfunctions/jsfunctions.php');?>
</body>
</html>
