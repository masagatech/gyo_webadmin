<?php 
include('includes/configuration.php');
$gnrl->check_login();

$page_title = "Admin Details";
$page = "admin_settings";
$table = 'tbl_admin';
extract( $_POST );
	
	
	
	if( isset( $_REQUEST['submit_btn'] ) && $_REQUEST['submit_btn'] == 'Update' ){
		## Update Admin Table
		$ins = array(
			'v_admin_username' 	=> $v_admin_username,
			'v_admin_email' 	=> $v_admin_email,
		);
		if( trim( $v_admin_password_new ) && trim( $v_admin_password_confirm ) && trim( $v_admin_password_new == $v_admin_password_confirm ) ){
			$ins['v_admin_password'] = md5( $v_admin_password_new );
		}
		$_SESSION['adminname'] = $v_admin_username;	
		$dclass->update( $table, $ins, " id='".$_SESSION['adminid']."' ");
		$gnrl->redirectTo( $page.'.php?succ=1&msg=edit');
	}
	else {
		$row_admin = $dclass->select( "*", $table, " AND id='".$_SESSION['adminid']."' ");
		extract( $row_admin[0] );
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
<link rel="stylesheet" type="text/css" href="js/jquery.gritter/css/jquery.gritter.css" />
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

        <div class="cl-mcont">
            <?php include('all_alert_msg.php'); ?>
            <div class="row">
                <div class="col-sm-12 col-md-12">
                    <div class="block-flat">
                        <div class="header">
							<h3><?php echo $page_title;?></h3>
						</div>
                        <div class="row">
                        	<div class="col-sm-6 col-md-6">
                            	<div class="content">
                                	<form role="form" action="#" method="post" parsley-validate novalidate >
                                        <div class="form-group">
                                            <label>Username</label>
                                            <input type="text" placeholder="Username" class="form-control"  name="v_admin_username" value="<?php echo $v_admin_username?>" required >
                                        </div>
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" placeholder="Email" class="form-control"  name="v_admin_email" value="<?php echo $v_admin_email?>" required parsley-type="email" >
                                        </div>
                                        <div class="form-group">
                                            <label>Password</label>
                                            <input type="password" placeholder="Password" class="form-control" id="v_admin_password_new" name="v_admin_password_new" value="" >
                                        </div>
                                        <div class="form-group">
                                            <label>Confirm Password</label>
                                            <input type="password" placeholder="Confirm Password" class="form-control" id="v_admin_password_confirm" name="v_admin_password_confirm" parsley-equalto="#v_admin_password_new" value="" >
                                        </div>
                                        <div class="form-group">
                                            <button class="btn btn-primary" type="submit" name="submit_btn" value="Update">Update</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
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
<!-- <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&amp;sensor=false"></script> -->
<script type="text/javascript" src="js/behaviour/general.js"></script> 
<script src="js/jquery.ui/jquery-ui.js" type="text/javascript"></script> 
<script type="text/javascript" src="js/jquery.nestable/jquery.nestable.js"></script> 
<script type="text/javascript" src="js/bootstrap.switch/bootstrap-switch.min.js"></script> 
<script type="text/javascript" src="js/bootstrap.datetimepicker/js/bootstrap-datetimepicker.min.js"></script> 
<script src="js/jquery.select2/select2.min.js" type="text/javascript"></script> 
<script src="js/bootstrap.slider/js/bootstrap-slider.js" type="text/javascript"></script> 
<script type="text/javascript" src="js/jquery.gritter/js/jquery.gritter.js"></script> 
<script type="text/javascript" src="js/jquery.datatables/jquery.datatables.min.js"></script> 
<script type="text/javascript" src="js/jquery.datatables/bootstrap-adapter/js/datatables.js"></script> 
<script type="text/javascript" src="js/jquery.icheck/icheck.min.js"></script>
<script type="text/javascript" src="js/dropzone/dropzone.js"></script>
<script type="text/javascript" src="js/ckeditor/ckeditor.js"></script> 

<script type="text/javascript">
    $(document).ready(function(){
      //initialize the javascript
      App.init();
	  CKEDITOR.replace( 'l_description',{
		height: '500px',
		
	  });	

    });
  </script> 
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
