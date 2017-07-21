
	<div class="page-head">
    	<button id="header-notice-board-btn" type="button" class="btn btn-github bg fright"><i class="fa fa-info-circle"></i> Notice Board</button>
    	<!-- <button id="header-notice-board-btn" class="btn btn-danger fright ">Notice Board</button> -->
        <div id="header-notice-board-content" style="display:none;" >
        	<?php echo nl2br( $gnrl->getSettings('NOTICE_BOARD') ); ?>
        </div>
		
		<h2 style="max-width:80%;" >
			<?php echo $page_title;?>
		</h2>
		<!--
        <ol class="breadcrumb">
            <li><a href="#">Home</a></li>
            <li><a href="#">Tables</a></li>
            <li class="active">DataTables</li>
        </ol>
        -->
    </div> 
    
    <script type="text/javascript" >
		setTimeout(function(){
			$('#header-notice-board-btn').click(function(){
				$.gritter.add({
					title: 'Notice Board',
					text: jQuery('#header-notice-board-content').html(),
					//class_name: 'danger'
				});
			});
		},500);
    </script>
	