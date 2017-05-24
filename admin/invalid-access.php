<?php 
include('includes/configuration.php');
$gnrl->check_login();

	extract( $_POST );
	$page_title = "Invalid Access";
	$page = "invalid-access";
	
	

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
						<h2 style="text-align:center" class="text-danger">You have no permissions to perform this action.</h2>
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
