<div id="head-nav" class="navbar navbar-default navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse"> 
            	<span class="fa fa-gear"></span> 
            </button>
            <a style="width:auto;" class="navbar-brand" href="#"><span><?php echo SITE_NAME;?></span></a> 
            <ul class="nav navbar-nav navbar-right user-nav" style="margin:8px 0px 0 0;" >
            	<li><img src="<?php echo ADMIN_IMG;?>firefox-browser.png" /></li>
            </ul>
        </div>
        <div class="navbar-collapse collapse">
        	<ul class="nav navbar-nav navbar-right user-nav">
            	<li class="dropdown profile_menu"> 
                	<a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    	<span><?php echo ucwords( AUNAME );?></span>
                        <b class="caret"></b>
                	</a>
                    <ul class="dropdown-menu">
                        <li><a href="#" onClick="doLogout();">Sign Out</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
	<style>
		.edit_img{ margin:5px 0 0 0; clear:both; background:url(images/transparant_img.png) repeat; max-width:300px; }
		.red_astric_span{ color:#da4932; }
		.block-flat .header h3{ font-family:Helvetica,sans-serif; font-weight:100; }
		.block .header h2, .widget-block .header h2, .block-flat .header h2{ font-family:Helvetica,sans-serif; font-weight:100; }
	</style>
	<?php $red_astric_span = '<span class="red_astric_span">*</span>'; ?>

	<?php 
	$language_query = array();
	if( isset( $_REQUEST['keyword'] ) && $_REQUEST['keyword'] != '' ){
		$keyword =  trim( $_REQUEST['keyword'] );
		$language_related_keys = array( "v_title", "l_description", "l_short_description" );
		foreach( $global_language_arr as $kLang => $vLang ){
			foreach( $language_related_keys as $lang_key ){
				$language_query[ $lang_key ][] = " ( ".$lang_key."_".$kLang." LIKE '%".$keyword."%' ) ";
			}
		}
	} ?>
    
    <script type="text/javascript" >
		function submit_frm(){
			jQuery('#frm').submit();
		}
    </script>
    
    
    <style>
		table thead th, table tbody td{ font-family:Arial, Helvetica, sans-serif; font-size:14px; color:#333; }
		.form-control{ font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#333; }
    </style>
    