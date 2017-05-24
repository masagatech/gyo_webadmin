<?php 
include('includes/configuration.php');
extract($_POST);
$data = array();
$table = 'tbl_admin';

 
	switch ( $action ) {
		
		case 'doLogin':
			$data['success'] = 0;
			$data['msg'] = $mess['invalid'];

			if( $v_admin_username != '' && $v_admin_username != '' ){
				$v_admin_password = md5( $v_admin_password );
				$check = $dclass->select( "*", $table, " AND ( v_email='".$v_admin_username."' ) AND v_password='".$v_admin_password."'  AND e_status='active' ");
				if( count( $check ) ){
					// $temp_data=$check[0]['l_data'];
					// $temp_data=json_decode($temp_data,true);
					// _P($temp_data);
					// $page_arr=array();
					// foreach ( $temp_data as $c_key => $c_value) {
					// 	$page_arr[]=$c_value;
					// }
					// _P($page_arr);
					// exit();
					$_SESSION['adminid'] = $check[0]['id'];
					$_SESSION['adminemail'] = $check[0]['v_email'];
					$_SESSION['adminname'] = $check[0]['v_name'];
					$_SESSION['lastlog'] = $check[0]['d_last_login'];
					$_SESSION['page_access'] =json_decode( $check[0]['l_data'], true );
					$dclass->update( $table, array( 'd_last_login' => date( 'Y-m-d H:i:s' ) )," id = '".$_SESSION['adminid']."'");
					$data['success'] = 1;
					// _P($_SESSION);
					// exit;
					$data['level'] = $_SESSION['adminlevel'];
					$data['redirect_link'] = '';
					$_SESSION['redirect_link'] = 'firstpage';
				}
			}
			echo json_encode( $data );
		break;
		
		case 'doLogout':
			session_unset();
			session_unset('adminlevel');
			session_unset('adminname');
			session_unset('lastlog');
			session_unset('lastlog_id');
			$data['success'] = 1;
			$data['msg'] = $mess['logout'];
			echo json_encode( $data );
		break;
		
		
		default:
		  echo "";
	}

?>