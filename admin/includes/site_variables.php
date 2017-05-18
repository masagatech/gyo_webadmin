<?php
	$temp_settings = $dclass->select( "*", 'tbl_sitesetting' );
	if( count( $temp_settings ) ){
		foreach( $temp_settings as $ss_row ){
			$global_site_settings[ $ss_row["v_key"] ] = $ss_row["l_value"];
			define( $ss_row["v_key"], $ss_row["l_value"] );
		}
	}
?>