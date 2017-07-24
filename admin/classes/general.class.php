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

		function getKey($string){

			$string= strtolower(str_replace(' ','_',$string));
			return $string;
		}
		function getAstric(){
		
			return "<span class='text-danger'>*</span>";
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

			$IS_SMTP 				= MAIL_VIA;
			$SMTP_FROM_NAME  		= MAIL_FROM_NAME;
			$SMTP_FROM_EMAIL 		= MAIL_FROM_EMAIL;
			$SMTP_REPLY_TO_NAME		= MAIL_REPLY_NAME;
			$SMTP_REPLY_TO_EMAIL	= MAIL_REPLY_EMAIL;
			
			$email_from 			= $email_from ? $email_from : ( $SMTP_FROM_NAME.' < '.$SMTP_FROM_EMAIL.' >' );
			$reply_to 				= $reply_to ? $reply_to : $SMTP_REPLY_TO_EMAIL;
			
			$email_subject 	= "=?UTF-8?B?".base64_encode( html_entity_decode( $email_subject, ENT_COMPAT, 'UTF-8' ) )."?=";
			
			$email_message 	= str_replace( "\n", "", $email_message );
			$email_message 	= str_replace( "\r", "", $email_message );
			$email_message 	= stripslashes( $email_message );
			
			
			## SMTP
			if( $IS_SMTP == 'smtp' ){
				
				$mail = new PHPMailer();
				
				## < $email_from > is not use for SMTP.
				$SMTP_HOST 		 	= MAIL_SMTP_HOST;
				$SMTP_PORT 		 	= MAIL_SMTP_PORT;
				$SMTP_ENCRYPTION 	= MAIL_SMTP_ENCRYPTION;
				$SMTP_AUTH 		 	= true;
				$SMTP_USERNAME   	= MAIL_SMTP_USERNAME;
				$SMTP_PASSWORD 	 	= MAIL_SMTP_PASSWORD;
				
				$mail->IsSMTP();
				$mail->Host 		= $SMTP_HOST;
				$mail->SMTPAuth 	= $SMTP_AUTH;
				$mail->Username 	= $SMTP_USERNAME;
				$mail->Password 	= $SMTP_PASSWORD;
				$mail->SMTPSecure	= $SMTP_ENCRYPTION;
				$mail->Port			= $SMTP_PORT;
				$mail->SMTPDebug 	= 1; // OR 2
				$mail->FromName 	= $SMTP_FROM_NAME;
				$mail->From 		= $SMTP_FROM_EMAIL;
				
				$mail->AddAddress( $email_to );
				// $mail->AddAddress( "aa@aa.com", "name" );
				
				//$mail->AddReplyTo( $reply_to[0], $reply_to[1] );
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
				$mail->Subject = $email_subject;
				$mail->Body    = $email_message;
				
				if( !$mail->Send() ){
					echo "Message could not be sent. <p>";
					echo "Mailer Error: " . $mail->ErrorInfo;
					exit;
				} 
				else{
					return 1;
				}
			}
			else {
				
				return $this->email( $email_to, $email_from, $reply_to, $email_cc, $email_bcc, $email_subject, $email_message, $email_format = "", $attachments );	
				
			}
		}
		
		
		function renderTableHeader( $arr = array() ){
			
			if( !$arr ){
				return '';
			}
			
			$ascArr = array(
				'ASC' => 'DESC',
				'DESC' => 'ASC',
			);
			
			$link = array();
			$key_array = array(
				'keyword',
				'script',
				'id',
				'a',
				'pageno',
				'limitstart',
				'np',
				);
			foreach( $_GET as $k => $v ){

				if( $k[0].$k[1].$k[2].$k[3].$k[4] == 'srch_' ){
					$link[] = $k.'='.$v;
				}
				else if( in_array( $k, $key_array ) ){
					$link[] = $k.'='.$v;
				}
			}
			
			$str = '<thead><tr>';
				foreach( $arr as $k => $v ){
					$str .= '<th>';
						if( $v['order'] ){
							
							$url = implode( '&', $link );
							$url = ( $url ? ( '?'.$url.'&' ) : '?' ).'sb='.$k.'&st='.( $_REQUEST['sb'] == $k ? $ascArr[$_REQUEST['st']] : 'ASC' );
							
							$str .= '<a class="'.( $_REQUEST['sb'] == $k ? 'active' : '' ).'" href="'.$url.'" >';
						}
						$str .= $v['title'];
						
						if( $v['order'] ){
							if( $_REQUEST['sb'] == $k ){
								if( $_REQUEST['st'] == 'ASC' ){
									$str .= '&nbsp; <i class="fa fa-long-arrow-up"></i>';
								}
								else{
									$str .= '&nbsp; <i class="fa fa-long-arrow-down"></i>';
								}
							}
							$str .= '</a>';
						}
						
						$str .= $v['title2'];
						
					$str .= '</th>';
				}
			$str .= '</tr></thead>';
			
			return $str;
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
				$selected = "";
				if( $sel != "" && $k == $sel ){
					$selected= "selected";
				}
				$str .= '<option value="'.$k.'" '.$selected.' >'.$v.'</option>';
			} 
			// _P($str);
			// exit;
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
		
		function get_email_data( $v_key = "" ){
			global $dclass;
			$email = $dclass->select( "*", 'tbl_email', " AND v_key = '".$v_key."' " );
			if( count( $email ) ){
				$email = $email[0];
				// _P($email["j_title"]);
				$email_data = array();
				$email_data["from"] = MAIL_FROM_NAME;
				$email_data["from_email"] = MAIL_FROM_EMAIL;
				$email_data["reply"] = MAIL_REPLY_NAME;
				$email_data["reply_email"] = MAIL_REPLY_EMAIL;
				$email_data["title"] = lang( json_decode( $email["j_title"], true ), "en" );
				$email_data["email_body"] = '<meta http-equiv="Content-Type"content="text/html; charset=UTF-8"/><table cellpadding="5" width="700" border="0"><tr><td>'.lang( json_decode( $email["j_content"], true ), "en" ).'</td></tr></table>';
				return $email_data;
			}
			return array();
		}
		function email_keywords(){
			$array = array(
				'[email_body]' 			=> 'Email Body',
				
				'[user_id]' 			=> 'User ID',
				'[user_name]' 			=> 'User Name',
				'[user_email]' 			=> 'User Email',
				'[user_phone]' 			=> 'User Phone',
				
				'[driver_id]' 			=> 'Driver ID',
				'[driver_name]' 		=> 'Driver Name',
				'[driver_email]' 		=> 'Driver Email',
				'[driver_phone]' 		=> 'Driver Phone',
				
				'[i_ride_id]'           => 'Ride ID',
				'[ride_pin]' 			=> 'Ride PIN',
				'[ride_code]'           => 'Ride Code',
				'[ride_total]'          => 'Ride Total',
				'[ride_discount]'       => 'Ride Total',
				'[ride_start_time]'     => 'Ride Start Time',
				'[ride_end_time]'     	=> 'Ride End Time',
				'[ride_start_address]'  => 'Ride Start Address',
				'[ride_end_address]'    => 'Ride End Address',
				'[ride_distance]'    	=> 'Ride Distance',
				'[ride_promocode_code]' => 'Ride Promocode Code',
				'[ride_payment_method]' => 'Ride Payment Methods',
				'[ride_bill_table]' 	=> 'Ride Bill Table',
				
				'[ride_paid_by_wallet]' => 'Ride Paid By Wallet',
				'[ride_paid_by_cash]' 	=> 'Ride Paid By Cash',
				
				'[city]' 				=> 'City',
				
				'[otp]' 				=> 'OTP',
				'[amount]'              => 'Amount',
				'[user_name_id]'        => 'User Name & ID',
				'[driver_name_id]'      => 'Driver Name & ID',
				'[from]'                => 'From',
				'[address]'             => 'Address',
				'[pickup_address]'      => 'Pickup Address',
				'[destination_address]' => 'Destination Address',
				'[free_text]' => 'Free Text',
				'[support_inq_id]' => 'Support Inquiry Id',
				'[support_inq_text]' => 'Support Inquiry Text',
				
				
			);
			return $array;
		}
		
		function email_replace_keyword( $str = "" , $data_arr = array() ){
			$keywords = $this->email_keywords();
			foreach( $keywords as $k => $v ){
				$str = str_replace( $k, $data_arr[$k], $str );
			}
			return $str;
		}
		function prepare_and_send_email( $email_data, $replacer_arr ){
						
			if( count( $email_data ) && $email_data["email_to"] != "" ){

				$email_to = $email_data["email_to"];
				$email_data["subject"] = $this->email_replace_keyword( $email_data["subject"], $replacer_arr );
				$email_data["subject"] = strip_tags( $email_data["subject"] );
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
		
		function _EMAIL( $options = array() ){ 
		
			global $dclass;
			
			/*
			PARAMS
				_key 				=> SMS Template Key
				_subject 			=> Direct Subject
				_body 				=> Direct Content
				_user_id 			=> User ID
				_user_lang 			=> User Lang
				_to 				=> User Email
				_replace_arr		=> Replace Arr
			*/
			
			$_subject = $options["_subject"];
			$_body = $options["_body"];
			
			$_user = $dclass->select( 'id, v_phone, l_data', 'tbl_user', " AND id = '".$options["_user_id"]."' " );
			
			if( $options['_key'] ){
				
				$_template = $dclass->select( '*', 'tbl_email', " AND i_delete = '0' AND e_status = 'active' AND v_key = '".$options['_key']."' " );
				$_template = $_template[0];
				
				$_template['j_title'] = json_decode( $_template['j_title'], true );
				$_template['j_content'] = json_decode( $_template['j_content'], true );
				
				$_subject = lang( $_template['j_title'], $options['_user_lang'] );
				$_body = lang( $_template['j_content'], $options['_user_lang'] );
				
			}
			
			$_subject = $this->email_replace_keyword( $_subject, $options['_replace_arr'] );
			$_body = $this->email_replace_keyword( $_body, $options['_replace_arr'] );
			
			$options['_user_lang'] = $options['_user_lang'] ? $options['_user_lang'] : DEFAULT_LANGUAGE;
			
			$emailTemplate = str_replace( '[email_body]', $_body, constant('EMAIL_TEMPLATE_'.$options['_user_lang']) );
			
			$_body = '<meta http-equiv="Content-Type"content="text/html; charset=UTF-8"/>';
			$_body .= '<table cellpadding="0" width="100%" border="0" >';
			$_body .= '<tr><td align="center" >'.$emailTemplate.'</td></tr>';
			$_body .= '</table>';
			
			return $this->custom_email( $email_to = $options['_to'], $email_from = "", $reply_to = "", $email_cc = "", $email_bcc = "", $_subject, $_body, $email_format = "", $attachments = array() );
			
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
			$row = $dclass->select('*','tbl_city'," AND i_delete=0 ORDER BY v_name ");
			$str = '';
			foreach($row as $key => $val){
				if($selval == $val['id']) { $sel = 'selected="selected"'; }
				else { $sel = ''; }
				$str .= '<option value="'.$val['id'].'" '.$sel.'>'.ucwords($val['v_name']).'</option>';
			}
			echo $str;
		}
		
		function getVehicleTypeDropdownList($selval="", $value = 'id'){
			global $dclass; 	 
			$row = $dclass->select('*','tbl_vehicle_type', " AND i_delete=0 ORDER BY v_name ");
			$str = '';
			foreach($row as $key => $val){
				if($selval == $val[ $value ] ) { $sel = 'selected="selected"'; }
				else { $sel = ''; }
				$str .= '<option value="'.$val[ $value ].'" '.$sel.'>'.ucwords($val['v_name']).'</option>';
			}
			echo $str;
			
		}

		function displaySiteDate( $d_added = "" ){
			if( !$d_added ) return '-';
			$d_added = date('d/m/Y h:i A', strtotime($d_added));
			return $d_added;
		}

		function getSections($checked){
			if($checked=='checked'){
				$checked='1';
			}else{
				$checked='0';
			}
			global $dclass;
			$return_data = array();
			$data = $dclass->select( '*', 'tbl_sections',"AND i_delete='".$checked."' ", " ORDER BY i_parent_id ASC, i_order ASC " );
			if( count( $data ) ){
				foreach( $data as $row ){
					if( $row['i_parent_id'] ){
						$return_data[$row['i_parent_id']]['childs'][$row['id']] = $row;
					}
					else{
						$return_data[$row['id']] = $row;
					}
				}
			}
			
			return $return_data;
		}

		function date_Difference($date1='',$date2=''){
			if($date1 != '' && $date2 != ''){
				$t_hours = round((strtotime($date1) - strtotime($date2))/3600, 1);
			}
			return $t_hours;
		}
		function isAllow( $action = '' ){
			
			$pageAccess = $_SESSION['page_access']['pages'];
			
			global $dclass;
			$page = $dclass->select( '*', 'tbl_sections', " AND v_name = '".BASE_FILE."' " );
			
			if( count( $page ) ){
				
				$v_key = $page[0]['v_key'];
				
				if( $action == 'add' && in_array( 'add', $pageAccess[$v_key] ) ){
					return 1;
				}
				else if( $action == 'edit' && in_array( 'update', $pageAccess[$v_key] ) ){
					return 1;
				}
				else if( $action == 'view' && in_array( 'view', $pageAccess[$v_key] ) ){
					return 1;
				}
				else if( $action == 'delete' && in_array( 'delete', $pageAccess[$v_key] ) ){
					return 1;
				}
				else if( $action == '' && in_array( 'list', $pageAccess[$v_key] ) ){
					return 1;
				}
			}
			
			return 0;
		}
		
		function isAccess( $file = "" ){
			
			$pageAccess = $_SESSION['page_access']['pages'];
			if( in_array( BASE_FILE, array(
				'invalid-access.php',
				'index.php',
				'adminActions.php',
				'ajax_operations.php',
				'warroom.php',
				'log.php',
				'top_drivers.php',
				) ) ){
				return '';
			}
			
			
			global $dclass;
			$page = $dclass->select( '*', 'tbl_sections', " AND v_name = '".BASE_FILE."' " );
			if( count( $page ) ){
				$v_key = $page[0]['v_key'];
				if( !isset( $pageAccess[$v_key] ) ){
					$this->redirectTo( 'invalid-access.php' );
				}
				else if( $_REQUEST['script'] == 'add' && !in_array( 'add', $pageAccess[$v_key] ) ){
					$this->redirectTo( 'invalid-access.php' );
				}
				else if( (
					$_REQUEST['script'] == 'edit'
					|| $_REQUEST['chkaction'] == 'active'
					|| $_REQUEST['chkaction'] == 'inactive'
					) && !in_array( 'update', $pageAccess[$v_key] ) ){
					$this->redirectTo( 'invalid-access.php' );
				}
				else if( $_REQUEST['script'] == 'view' && !in_array( 'view', $pageAccess[$v_key] ) ){
					$this->redirectTo( 'invalid-access.php' );
				}
				else if( $_REQUEST['chkaction'] == 'delete' && !in_array( 'delete', $pageAccess[$v_key] ) ){
					$this->redirectTo( 'invalid-access.php' );
				}
				else if( $_REQUEST['script'] == '' && !in_array( 'list', $pageAccess[$v_key] ) ){
					$this->redirectTo( 'invalid-access.php' );
				}
			}
		}
		
		
		
		function _SMS( $options = array() ){ 
		
			global $dclass;
			
			/*
			PARAMS
				_key 				=> SMS Template Key
				_body 				=> Direct Content
				_user_id 			=> User ID
				_user_lang 			=> User Lang
				_to 				=> User Phone
				_replace_arr		=> Replace Arr
			*/
			
			$_body = $options["_body"];
			
			$_user = $dclass->select( 'id, v_phone, l_data', 'tbl_user', " AND id = '".$options["_user_id"]."' " );
			
			if( $options['_key'] ){
				$_template = $dclass->select( '*', 'tbl_sms', " AND i_delete = '0' AND e_status = 'active' AND v_key = '".$options['_key']."' " );
				$_template = $_template[0];
				$_template['j_sms'] = json_decode( $_template['j_sms'], true );
				$_body = lang( $_template['j_sms'], $options['_user_lang'] );
			}
			
			
			$_body = $this->email_replace_keyword( $_body, $options['_replace_arr'] );
			
			$url = 'http://sms.cell24x7.com:1111/mspProducerM/sendSMS';
			$url .= '?user='.SMS_USERNAME;
			$url .= '&pwd='.SMS_PASSWORD;
			$url .= '&sender='.SMS_SENDERNAME;
			$url .= '&mt=2';
            $url .= '&mobile='.$options['_to'];
            $url .= '&msg='.urlencode( $_body );
			
			$returnArr = array(
				'succ' => 0,
				'data' => '',
			);
			
			try{
				
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
				
				if( ( substr( $retValue, 0, 3 ) == 'MSP' ) ){
					$returnArr['succ'] = 1;
					$returnArr['data'] = $retValue;
                }
				else{
                    $returnArr['succ'] = 0;
					$returnArr['data'] = $retValue;
                }
			}
			
			catch( Exception $e ){
				$returnArr['succ'] = 0;
				$returnArr['data'] = $e;
			}
			
			return $returnArr;
			
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
               
               try{
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
				catch( Exception $e ){
					_p($e);
				}
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

        function sendNotificationManual($deviceToken = NULL, $messageArr = array(),$authentication_key) {
            

            $url = 'https://fcm.googleapis.com/fcm/send';
            $registatoin_ids = array($deviceToken);
            $fields = array(
                'registration_ids' => $registatoin_ids,
                'notification' => $messageArr,
                'data' => $messageArr,
                
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
		
		
		
		
		function _curl( $url, $fields = array(), $method = 'GET' ){ 
        	
            if( $method == 'GET' ){
               
               try{
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
				catch( Exception $e ){
					_p($e);
				}
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
