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
<script type="text/javascript" src="js/bootstrap.datetimepicker/js/bootstrap-datetimepicker.js"></script>

<script type="text/javascript" src="js/jquery.sparkline/jquery.sparkline.min.js"></script> 
<script type="text/javascript" src="js/jquery.easypiechart/jquery.easy-pie-chart.js"></script> 
<!-- <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp&amp;sensor=false"></script> 
 --><script type="text/javascript" src="js/jquery.gritter/js/jquery.gritter.js"></script> 
<script type="text/javascript" src="js/jquery.datatables/jquery.datatables.min.js"></script> 
<script type="text/javascript" src="js/jquery.datatables/bootstrap-adapter/js/datatables.js"></script> 
<script type="text/javascript" src="js/jquery.icheck/icheck.min.js"></script>
<script type="text/javascript" src="js/dropzone/dropzone.js"></script>
<script type="text/javascript" src="js/bootstrap.colorpicker/dist/js/bootstrap-colorpicker.js"></script>
<script type="text/javascript" src="js/jquery.niftymodals/js/jquery.modalEffects.js"></script> 
<script type="text/javascript" src="js/imageviewer.js"></script> 

<script>
    $(function () {
        // add multiple select / deselect functionality
        $("#selectall").click(function () {
                
            $('.case').attr('checked', this.checked);
        });
 
        // if all checkbox are selected, then check the select all checkbox
        // and viceversa
        $(".case").click(function () {
            
            if ($(".case").length == $(".case:checked").length) {
                $("#selectall").attr("checked", "checked");
            } else {
                $("#selectall").removeAttr("checked");
            }
 
        });
    });
</script>

<script type="text/javascript">
	$(document).ready(function(){
		//initialize the javascript
		App.init();
		App.textEditor();
		
		$('#some-textarea').wysihtml5();
		$('#summernote').summernote();
		$('.apply_colorpicker').colorpicker();
		$('.md-trigger').modalEffects();
	});
</script>
<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script type="text/javascript" src="js/behaviour/voice-commands.js"></script>
<script type="text/javascript" src="js/bootstrap/dist/js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/jquery.flot/jquery.flot.js"></script> 
<script type="text/javascript" src="js/jquery.flot/jquery.flot.pie.js"></script> 
<script type="text/javascript" src="js/jquery.flot/jquery.flot.resize.js"></script> 
<script type="text/javascript" src="js/jquery.flot/jquery.flot.labels.js"></script>

<style>
	.cke_contents{ height:300px !important; }
	.banner .cke_contents{ height:100px !important; }
</style>



