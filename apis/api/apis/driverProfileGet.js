var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');



var currentApi = function( req, res, next ){
	
	var classes = req.app.get('classes');
	classes.gnrl._extract( classes, this ); // Extract Classes
	var _p = gnrl._p;
	
	var params = gnrl._frm_data( req );
	var _lang = gnrl._getLang( params );
	
	var _status   = 1;
	var _message  = '';
	var _response = {};
	
	var id = gnrl._is_undf( params.id ).trim();
	var v_token = gnrl._is_undf( params.v_token ).trim();
	
	if( !id ){ _status = 0; _message = 'err_req_user_id'; }
	if( _status && !v_token ){ _status = 0; _message = 'err_req_auth_token'; }
	
	if( _status ){
		dclass._query( "SELECT a.v_image,a.v_name,a.v_email,b.v_vehicle_number,b.v_image_rc_book,b.v_image_puc,b.v_image_insurance,a.v_phone,a.v_password,a.v_token FROM tbl_user AS a LEFT JOIN tbl_vehicle AS b ON a.id = b.i_driver_id WHERE a.id = '"+id+"' AND a.v_role = 'driver' ", function( status, data ){
			if( status && !data.length ){
				gnrl._api_response( res, 0, 'err_msg_no_account', {} );
			}
			else{
				if( v_token != data[0].v_token ){
					gnrl._api_response( res, 0, 'err_invalid_auth_token', {} );
				}
				else{
					if( data[0].v_image ){
						data[0].v_image = gnrl._uploads( 'users/'+data[0].v_image )
					}
					gnrl._api_response( res, 1, '', data[0] );
				}
			}
		});
	}
	else{
		gnrl._api_response( res, 0, _message );
	}
};

module.exports = currentApi;
