<?php
require("PHPMailer/class.phpmailer.php");
#defaults to using php "mail()"
$mail = new PHPMailer(); 

	class general{
	
		function redirectTo($redirect_url){
			@header("location: {$redirect_url}");
			echo "<script type=\"text/javascript\">location.href = \"{$redirect_url}\"</script>";
			die();
		}
		
		function getSettings( $field ){
			global $dclass; 
			$row = $dclass->select( "*", "tbl_sitesetting", " AND v_key = '".$field."' " );
			return $row[0]['l_value'];
		}
		
		function removeChars($string){
			$arra = array(" ","@","#","?","&","&amp;");
			for( $i = 0; $i < count( $arra ); $i++ ){
				if( strstr( $string, $arra[$i] ) ){
					$string = str_replace( $arra[$i] ,"", $string );
				}
			}
			return $string;
		}
		
		function back(){
			return "<a href='javascript:history.go(-1);'>Back</a>";
		}
		
		function checkLogin(){
			if( isset( $_SESSION['adminid'] ) ){
				return 1;
			}
		}
		
		function getLangLat($address){
			$google_address = urlencode($address);
			$geocode1 = "http://maps.google.com/maps/geo?q=$google_address&output=csv&key=".GOOGLE_API_KEY;
			$handle = @fopen($geocode1, "r");
			$contents = '';			
			if ( $handle != "" ){
				while (!feof($handle) ) {
				  $contents .= fread($handle, 8192);
			
				}
				fclose($handle);
			}	
			$coord_array = explode(",",$contents);		
			$latlog[0] = $coord_array[2];
			$latlog[1] = $coord_array[3];
			return $latlog;
		}
		
		function distance($lat1, $lon1, $lat2, $lon2, $unit) {
			$theta = $lon1 - $lon2;
			$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
			$dist = acos($dist);
			$dist = rad2deg($dist);
			$miles = $dist * 60 * 1.1515;
			$unit = strtoupper($unit);
			
			if ($unit == "K") {
				return ($miles * 1.609344);
			} else if ($unit == "N") {
				return ($miles * 0.8684);
			} else {
				return $miles;
			}
		}
		
		// For dropdown
		function getDropdownList($droparray,$selval){
			// print_r($droparray);
			// print_r($selval);
			// exit;
			$str = '';
			foreach($droparray as $key => $val){
				if($selval == $val) { $sel = 'selected="selected"'; }
				else { $sel = ''; }
				$str .= '<option value="'.$val.'" '.$sel.' />'.ucwords($val).'</option>';
			}
			echo $str;
		}
		
		
		function seoText($str) {
			$str=trim(strtolower($str));
			$special_array=array('#','$','\'','"','?','&',':','!','%','&reg;','&trade;','(',')','/',',');
			$str=str_replace(' ','-',$str);
			foreach( $special_array as $item ){
				$str=str_replace($item,'-',$str);
			}
			$str=str_replace('--','-',$str);
			$str=str_replace('---','-',$str);
			$str=str_replace('--','-',$str);
			$str=str_replace('--','-',$str);
			$str=str_replace('--','-',$str);
			return trim($str,'-');
		}
		
		function getRealIpAddr(){
			
			if( !empty( $_SERVER['HTTP_CLIENT_IP'] ) ){
				## check ip from share internet
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			}
			else if ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ){
				## to check ip is pass from proxy
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
			else{
				$ip = $_SERVER['REMOTE_ADDR'];
			}
			return $ip;
		}
		
		function getSuccessMsg($str){
			return '<div class="alert alert-success">
						<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
						<i class="fa fa-check sign"></i><strong>Success!</strong> '. $str.
					'</div>';
		}
		function getErrorMsg($str){
			return '<div class="alert alert-danger">
						<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
						<i class="fa fa-times-circle sign"></i><strong>Error!</strong> '. $str.
					'</div>';
		}
		
		function save_site_setting( $k, $v = '' ){
			global $dclass;
			$table = 'tbl_sitesetting';
			$isData = $dclass->select( "*", $table, " AND v_key = '".$k."' ");
			// addslashes( stripslashes( $v ) )
			
			$v = str_replace( '\r', '', str_replace( '\n', '', $v ) );
			
			if( is_array( $isData ) && count( $isData ) > 0 ){
				$arr = array(
					"l_value" => addslashes( stripslashes( $v ) )
				);
				$dclass->update( $table, $arr, " v_key = '".$k."' ");
			}
			else{
				$arr = array(
					"v_key"  => $k,
					"l_value" => addslashes( stripslashes( $v ) )
				);
				$dclass->insert( $table, $arr );
			}
		}
		
		function curr_url() {
			$pageURL = 'http';
			if( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" ){
				$pageURL .= "s";
			}
			$pageURL .= "://";
			if( $_SERVER["SERVER_PORT"] != "80" ) {
				$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
			} 
			else {
				$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			}
			return $pageURL;
		}
		
		function check_login(){
			if( !AID ){ $this->redirectTo('index.php'); }
		}
		function user_login(){
			if( !U_ID ){ $this->redirectTo( $this->put_slug( 'login' ) ); }
		}
		function is_login(){
			if( U_ID ){ $this->redirectTo( SITE_URL ); }
		}
		
		function create_zip( $files = array(), $destination = '', $overwrite = false ){
			if( file_exists( $destination ) && !$overwrite ){ 
				return false; 
			}
			$valid_files = array();
			if( is_array( $files ) ) {
				foreach( $files as $file ) {
					if( file_exists( $file ) ) {
						$valid_files[] = $file;
					}
				}
			}
			
			if( count( $valid_files ) ) {
				$zip = new ZipArchive();
				if( $zip->open( $destination , $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE ) !== true ){
					return false;
				}
				foreach( $valid_files as $file ) {
					$zip->addFile( $file, basename( $file ) );
				}
				$zip->close();
				return file_exists( $destination );
			}
			else{
				return false;
			}
		}
		function unzip_file( $zipFile = "", $dir = "" ){
			if( file_exists( $zipFile ) ){
				$zip = new ZipArchive;
				$res = $zip->open( $zipFile );
				if ( $res === TRUE ){
					$zip->extractTo( $dir );
					$zip->close();
					return 1;
				} 
			}
			return 0;
		}
		
		function email( $email_to, $email_from = "", $reply_to = "", $email_cc = "", $email_bcc = "", $email_subject, $email_message, $email_format = "", $attachments = array() ){
			
			
			$email_format = 'html';
			if( $email_format == 'html' ){
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				
				if( count( $attachments ) ){
					$file_name	= $attachments[0][0];
					$file_path 	= $attachments[0][1];
					
					$file = fopen( $file_path, "rb" );
					$data = fread( $file, filesize( $file_path ) );
					fclose( $file );
					$data = chunk_split( base64_encode( $data ) );
					
					$boundary = md5(time()); 
					$headers = "MIME-Version: 1.0\n"."Content-Type: multipart/mixed; boundary=".$boundary."; ";
					
					$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
					
					$mail_attached .= ( "--" . $boundary . "\n"
					. "Content-Type: binary/octet-stream; name=".$file_name." \n"
					. "Content-Transfer-Encoding: base64 \n" . $data."\n"
					. "Content-Disposition: attachment; filename=".$file_name." \n"."--".$boundary."\n" ); 
					
					$email_message = ( "--".$boundary."\n".
					"Content-Type: text/html; charset='UTF-8'\n".
					"Content-Transfer-Encoding: 8bit \n\n".
					$email_message." \n\n".$mail_attached );
				}
				
			}
			
			$headers .= ( $email_from ? 'From: '.$email_from. "\r\n" : "" );
			$headers .= ( $reply_To ? 'Reply-To: '.$reply_To. "\r\n" : "" );
			$headers .= ( $email_cc ? 'Cc: '.$email_cc. "\r\n" : "" );
			$headers .= ( $email_bcc ? 'Bcc: '.$email_bcc. "\r\n" : "" );
			
			return mail( $email_to, $email_subject, $email_message, $headers );
		}
		function custom_email( $email_to, $email_from = "", $reply_to = "", $email_cc = "", $email_bcc = "", $email_subject, $email_message, $email_format = "", $attachments = array() ){
			
			$SMTP_FROM_NAME  		= $this->getSettings('SMTP_FROM_NAME');
			$SMTP_FROM_EMAIL 		= $this->getSettings('SMTP_FROM_EMAIL');
			$SMTP_REPLY_TO_EMAIL	= $this->getSettings('SMTP_REPLY_TO_EMAIL');
			
			
			$email_message = str_replace( "\n", "", $email_message );
			$email_message = str_replace( "\r", "", $email_message );
			$email_message = stripslashes( $email_message );
			
			$IS_SMTP = $this->getSettings('IS_SMTP');
			
			$email_subject = "=?UTF-8?B?".base64_encode( html_entity_decode( $email_subject, ENT_COMPAT, 'UTF-8' ) )."?=";
			
			
			## SMTP
			if( $IS_SMTP ){
				
				$mail = new PHPMailer();
				
				## < $email_from > is not use for SMTP.
				$SMTP_HOST 		 	= $this->getSettings('SMTP_HOST');
				$SMTP_PORT 		 	= $this->getSettings('SMTP_PORT');
				$SMTP_ENCRYPTION 	= $this->getSettings('SMTP_ENCRYPTION');
				$SMTP_AUTH 		 	= $this->getSettings('SMTP_AUTH');
				$SMTP_USERNAME   	= $this->getSettings('SMTP_USERNAME');
				$SMTP_PASSWORD 	 	= $this->getSettings('SMTP_PASSWORD');
				
				
				## Email To
				$email_to = str_replace( '>', ' ', trim( $email_to ) );
				$email_to = explode( '<', trim( $email_to ) );
				if( is_array( $email_to ) && count( $email_to ) > 1 ){
					$email_to = array( trim( $email_to[1] ), trim( $email_to[0] ) );
				} 
				else if( is_array( $email_to ) && count( $email_to ) ){
					$email_to = trim( $email_to[0] );
				}
				else{
					$email_to = trim( $email_to );
				}
				
				## Reply To Email
				$reply_to = $reply_to ? $reply_to : $SMTP_REPLY_TO_EMAIL;
				$reply_to = str_replace( '>', ' ', trim( $reply_to ) );
				$reply_to = explode( '<', trim( $reply_to ) );
				if( is_array( $reply_to ) && count( $reply_to ) > 1 ) {
					$reply_to =	array( trim( $reply_to[1] ), trim( $reply_to[0] ) );
				}
				else if( is_array( $reply_to ) && count( $reply_to ) ) {
					$reply_to =	trim( $reply_to[0] );
				}
				else{
					$reply_to =	trim( $reply_to );
				}
				
				if( $_REQUEST['D'] ){
					//$SMTP_AUTH = '';
				}
				
				$mail->IsSMTP();
				$mail->Host 		= $SMTP_HOST;
				$mail->SMTPAuth 	= $SMTP_AUTH;
				$mail->Username 	= $SMTP_USERNAME;
				$mail->Password 	= $SMTP_PASSWORD;
				$mail->SMTPSecure	= $SMTP_ENCRYPTION;
				$mail->Port			= $SMTP_PORT;
				
				$mail->FromName 	= $SMTP_FROM_NAME;
				$mail->From 		= $SMTP_FROM_EMAIL;
				
				
				$mail->AddAddress( $email_to );
				//$mail->AddAddress( "email.address@gmail.com", "name" );
				
				$mail->AddReplyTo( $reply_to[0], $reply_to[1] );
				//$mail->AddReplyTo( $reply_to );
				
				
				## Add Attachements 
				if( is_array( $attachments ) && count( $attachments ) ){
					foreach( $attachments as $row ){
						$file_name = $row[0];
						$file_dest = $row[1];
						$mail->AddAttachment( $file_dest, $file_name );
					}
				}
				
				if( $email_bcc ){
					$mail->AddBCC( trim( $email_bcc ) ); 
				}
				if( trim( $email_cc ) ){
					$mail->AddCC( trim( $email_cc ) );
				}
				
				$mail->IsHTML( true );
				
				//$email_subject = "=?UTF-8?B?".base64_encode( html_entity_decode( $email_subject, ENT_COMPAT, 'UTF-8' ) )."?=";
				
				$mail->Subject = $email_subject;
				
				
				$mail->Body    = $email_message;
				
				//_p( $mail ); _p( $reply_to ); exit;
				//_p( $mail ); exit;
				
				if( !$mail->Send() ){
				   echo "Message could not be sent. <p>";
				   echo "Mailer Error: " . $mail->ErrorInfo;
				   exit;
				} 
				else {
					return 1;
				}
			}
			else{
				
				$email_from = $SMTP_FROM_NAME.' < '.$SMTP_FROM_EMAIL.' >';
				$reply_to = $SMTP_REPLY_TO_EMAIL;
				//$attachments
				return $this->email( $email_to, $email_from, $reply_to, $email_cc, $email_bcc, $email_subject, $email_message, $email_format = "", $attachments );	
			}
		}
		
		
		
		
		## Added By D. 08-09-2014
		function getRGB( $hex ){
			$hex = str_replace( "#" , "" , $hex );
			if( strlen( $hex ) == 3 ) {
				$r = hexdec( $hex[0].$hex[0] );
				$g = hexdec( $hex[1].$hex[1] );
				$b = hexdec( $hex[2].$hex[2] );
			} 
			else {
				$r = hexdec( $hex[0].$hex[1] );
				$g = hexdec( $hex[2].$hex[3] );
				$b = hexdec( $hex[4].$hex[5] );
			}
			$rgb = array( $r , $g , $b );
			return $rgb;
		}
		
		
		function get_keyval_drop( $data = array(), $sel = '' ){
			
			$str = '';
			foreach( $data as $k => $v ){
				$str .= '<option value="'.$k.'" '.( $k == $sel ? "selected" : "" ).' >'.$v.'</option>';
			} 
			return $str;
		}
		function price_format( $price ){
			if( $price > 0 ){
				return number_format( $price, '0', '.', ',' );
			}
			return 0;
		}
		
		function make_field_to_key( $data, $key = "id" ){
			$temp_array = array();
			if( count( $data ) ){
				foreach( $data as $temp_row ){
					$temp_arr[ $temp_row[ $key ] ] = $temp_row;
				}
				$data = $temp_arr;
			}
			return $data;
		}
		
		
		function getLocationInfoByIp( $ip ){
			$ip_data = @json_decode( file_get_contents( "http://www.geoplugin.net/json.gp?ip=".$ip ), true );
			return $ip_data ? $ip_data : array();
			/*
			if( $ip_data && $ip_data->geoplugin_countryName != null ){
				$result['country'] = $ip_data->geoplugin_countryName;
			}
			return $result;*/
		}
		
		function set_date( $date, $type = "" ){
			if( $date == "0000-00-00" ) return "";
			if( $type == "db" ){
				return date( "Y-m-d", strtotime( $date ) );
			}
			else {
				return date( "m/d/Y", strtotime( $date ) );
			}
		}
		

		function email_valid( $email ){
			return filter_var( $email, FILTER_VALIDATE_EMAIL ); ## Valid = Return Email, ## Invalid = Return Blank
		}
		
		function get_email_data( $id = "" ){
			global $dclass;
			$email = $dclass->select( "*", 'tbl_email', " AND id = '".$id."' " );
			if( count( $email ) ){
				$email = $email[0];
				$email_data = array();
				$email_data["from"] = $email["v_sent_from"];
				$email_data["from_email"] = $email["v_sent_from_email"];
				$email_data["reply"] = $email["v_reply_to"];
				$email_data["reply_email"] = $email["v_reply_to_email"];
				$email_data["title"] = lang( $email, "v_title" );
				$email_data["subject"] = lang( $email, "v_subject" );
				$email_data["email_body"] = '<meta http-equiv="Content-Type"content="text/html; charset=UTF-8"/><table cellpadding="5" width="700" border="0"><tr><td>'.lang( $email, "l_description" ).'</td></tr></table>';
				return $email_data;
			}
			return array();
		}
		function email_keywords(){
			$array = array(
				'[email_body]' 			=> 'Email Body',
				'[user_name]' 			=> 'User Name',
				'[user_email]' 			=> 'User Email',
				'[user_phone]' 			=> 'User Phone',
				'[otp]' 				=> 'OTP',
				'[ride_pin]' 			=> 'Ride PIN',
				'[amount]'              => 'Amount',
				'[i_ride_id]'           => 'Ride ID',
				'[user_name_id]'        => 'User Name & ID',
				'[driver_name_id]'      => 'Driver Name & ID',
				'[from]'                => 'From',
				'[address]'             => 'Address',
				'[pickup_address]'      => 'Pickup Address',
				'[destination_address]' => 'Destination Address',
			);
			return $array;
		}
		
		function email_replace_keyword( $str = "" , $data_arr = array() ){
			$keywords = $this->email_keywords();
			foreach( $keywords as $k => $v ){
				$str = str_replace( $k, $data_arr[$v], $str );
			}
			return $str;
		}
		function prepare_and_send_email( $email_data, $replacer_arr ){
			
			if( count( $email_data ) && $email_data["email_to"] != "" ){

				$email_to = $email_data["email_to"];
				$email_data["subject"] = $this->email_replace_keyword( $email_data["subject"], $replacer_arr );
				$email_data["subject"] = strip_tags( $email_data["subject"] );
				
				if( isset($replacer_arr['free_text']) && $replacer_arr['free_text'] !=""  ){

					$replacer_arr['free_text'] = $this->email_replace_keyword( $replacer_arr['free_text'], $replacer_arr );
				}
				 
				$email_data["email_body"] = $this->email_replace_keyword( $email_data["email_body"], $replacer_arr );
				$email_data["reply"] = $this->email_replace_keyword( $email_data["reply"], $replacer_arr );
				$email_data["reply_email"] = $this->email_replace_keyword( $email_data["reply_email"], $replacer_arr );
				
				$email_from = $email_data["from"]." < ".$email_data["from_email"]." > ";
				$reply_to 	= $email_data["reply"]." < ".$email_data["reply_email"]." > ";
				
				$email_cc 	= $email_data["email_cc"];
				$email_bcc 	= $email_data["email_bcc"];
				return $this->custom_email( $email_to, $email_from, $reply_to, $email_cc, $email_bcc, $email_data["subject"], $email_data["email_body"], $email_format = "" );
			}
			return 0;
		}
		
		function get_status( $type = "" ){ 
			if( $type == "payment" ){
				return array( "Pending", "Paid", "Refunded", "Partial Refunded" );
			}
			else if( $type == "order" ){
				return array( "Pending", "Processing", "Shipping", "Delivered", "Cancel" );
			}
			return array();
		}
		
		function short_display( $str, $len = 400 ){
			return join("", array_slice(preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY), 0, $len)).'...';
		}
		
		function check_email( $email = "" ){
			if( preg_match( "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/i", $email ) ) {
				return $email;
			}
			return "";
		}
		
		function download_page( $path ){ 
			$ch = curl_init(); 
			curl_setopt($ch, CURLOPT_URL,$path); 
			curl_setopt($ch, CURLOPT_FAILONERROR,1); 
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 
			curl_setopt($ch, CURLOPT_TIMEOUT, 15); 
			$retValue = curl_exec($ch); 
			curl_close($ch); 
			return $retValue; 
		}
		
		function xml_to_array( $xml ){
			$xml_result = simplexml_load_string( $xml );
			$arr = json_encode( $xml_result );
			$arr = json_decode( $arr, true );
			$arr['XML_RESULT'] = $xml_result;
			return $arr;
		}
		
		function field_val( $val ){
			$val = htmlspecialchars( $val );
			return $val;
		}
		
		function getLanguageDropdownList($selval=""){
			global $dclass; 	 
			$row = $dclass->select('*','tbl_language'," ORDER BY v_name ");
			$str = '';
			foreach($row as $key => $val){
				if($selval == $val['v_key']) { $sel = 'selected="selected"'; }
				else { $sel = ''; }
				$str .= '<option value="'.$val['v_key'].'" '.$sel.'>'.ucwords($val['v_name']).'</option>';
			}
			echo $str;
		}
		
		function getCityDropdownList($selval=""){
			$orderBy="ORDER BY v_name ";
			global $dclass; 	 
			$row = $dclass->select('*','tbl_city'," ORDER BY v_name ");
			$str = '';
			foreach($row as $key => $val){
				if($selval == $val['id']) { $sel = 'selected="selected"'; }
				else { $sel = ''; }
				$str .= '<option value="'.$val['id'].'" '.$sel.'>'.ucwords($val['v_name']).'</option>';
			}
			echo $str;
		}
		
		function getVehicleTypeDropdownList($selval=""){
			global $dclass; 	 
			$row = $dclass->select('*','tbl_vehicle_type', " ORDER BY v_name ");
			$str = '';
			foreach($row as $key => $val){
				if($selval == $val['id']) { $sel = 'selected="selected"'; }
				else { $sel = ''; }
				$str .= '<option value="'.$val['id'].'" '.$sel.'>'.ucwords($val['v_name']).'</option>';
			}
			echo $str;
			
		}

		function removeTimezone($d_added=""){
			 $d_added = substr($d_added, 0, strpos($d_added, "+"));
			 return $d_added;
		}

		// function getAdminPages(){
		// 	global $dclass; 	 
		//  	$get_Page_Access = $_SESSION['page_access']['pages'];
		//  	// _P($get_Page_Access);

		// 	foreach ($get_Page_Access as $p_key => $p_value) {
		// 		$temp_arr= array();
		// 		$temp_arr= $dclass->select('v_key,v_title,v_name','tbl_sections'," AND v_key = '".$p_key."'",'ORDER BY i_order');
		// 		if( count($temp_arr) ){
		// 			$admin_pages[] = $temp_arr[0];
		// 		}

		// 	}
		// 	return $admin_pages;
		// }

		// function getAdminPagesArray(){

		// 	$admin_page = $this->getAdminPages();
		// 	// _P($admin_page);
		// 	// exit;
		// 	$admin_page_key=array();
		// 	foreach ($admin_page as $m_key => $m_value) {
		// 		$admin_page_key[]=$m_value['v_key'];
		// 	}
		// 	return $admin_page_key; 
		// }
		// function isPageAccess($file=""){
		// 	$page_access = $this->getAdminPages();
		// 	foreach ($page_access as $p_key => $p_value) {
		// 	    if($file == $p_value['v_name']){
		// 	        $is_access=1;
		// 	        break;
		// 	    }else{
		// 	        $is_access=0;
		// 	        continue;
		// 	    }
		// 	}
		// 	if($is_access == '0'){
		// 		$this->redirectTo($page_access[0]['v_name']);
		// 	}
			
		// }

		## FOR TESTING PURPOSE ( TEMPORARY FUNCTION )
		function getAdminPages(){
			global $dclass; 	 
		 	$get_Page_Access = $_SESSION['page_access']['pages'];
			foreach ($get_Page_Access as $p_key => $p_value) {
				$temp_arr= array();
				$temp_arr= $dclass->select('v_key,v_title,v_name','tbl_sections'," AND v_key = '".$p_key."'",'ORDER BY i_order');
				
				if( count($temp_arr) ){
					$temp_arr =$temp_arr[0];
					$temp_arr['allow_operation']=$p_value;
				}
				$admin_pages[] = $temp_arr;
			}
			return $admin_pages;
		}

		function getAdminPagesArray(){
			$admin_page = $this->getAdminPages();
			$admin_page_key=array();
			foreach ($admin_page as $m_key => $m_value) {
				$admin_page_key[]=$m_value['v_key'];
			}
			return $admin_page_key; 
		}
		function isPageAccess($file=""){
			$page_access = $this->getAdminPages();
			foreach ($page_access as $p_key => $p_value) {
			    if($file == $p_value['v_name']){
			    	$_SESSION['allow_operation']= $p_value['allow_operation'];
			        $is_access=1;
			        break;
			    }else{
			        $is_access=0;
			        continue;
			    }
			}
			if($is_access == '0'){
				$this->redirectTo($page_access[0]['v_name']);
			}
		}

		function checkAction($action=""){
			if($action==''){
				$action='list';
			}
			if($action){
				if($action == 'edit'){
					$action='update';
				}
				$page=$_SESSION['allow_operation'];
				foreach ($page as $p_key => $p_value) {
					if($action==$p_value){
						$is_access=1;
						break;
					}else{
						$is_access=0;
					}
				}
				return $is_access;
			}
		}

		function sendNotificationAndroid($deviceToken = NULL, $messageArr = array(),$authentication_key) {
            

            $url = 'https://fcm.googleapis.com/fcm/send';
            $registatoin_ids = array($deviceToken);
            $fields = array(
                'registration_ids' => $registatoin_ids,
                'type' => $messageArr,
            );
            $headers = array(
                'Authorization: key='.$authentication_key.'',
                'Content-Type: application/json'
            );
         
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
            if ($result === FALSE) {
                die('Curl failed: ' . curl_error($ch));
            }
            curl_close($ch);
            return $result;
        }

        function sendSMS( $url, $fields = array(), $method = 'GET' ){ 
            if( $method == 'GET' ){
                
                if( count( $fields ) ){
                    $url = $url.'?'.http_build_query( $fields );
                }
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url ); 
                curl_setopt($ch, CURLOPT_ENCODING, '');
                curl_setopt($ch, CURLOPT_POST, 0 ); 
                curl_setopt($ch, CURLOPT_FAILONERROR, 1 ); 
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 ); 
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false );
                curl_setopt($ch, CURLOPT_TIMEOUT, 10000 ); 
                $retValue = curl_exec($ch);
                
                curl_close($ch);
            }
            else{
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url ); 
                curl_setopt($ch, CURLOPT_POST, 1 );
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $fields ) );
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false ); 
                curl_setopt($ch, CURLOPT_TIMEOUT, 10000 ); 
                $retValue = curl_exec($ch);
                curl_close($ch);
            }
            return $retValue; 
        }
	}
?>
