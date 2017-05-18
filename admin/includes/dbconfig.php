<?php 
	if( _LIVE ){
		$glob['dbdatabase'] = 'goyo_app';
		$glob['dbhost'] = 'localhost';
		$glob['dbusername'] = 'postgres';
		$glob['dbpassword'] = 'sa@123';
		$glob['dbport'] = 5432;	
	}
	else{
		$glob['dbdatabase'] = 'db_goyo';
		$glob['dbhost'] = 'localhost';
		$glob['dbusername'] = 'postgres';
		$glob['dbpassword'] = 'admin';
		$glob['dbport'] = 5432;	
	}
	
?>