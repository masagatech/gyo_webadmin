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
	
	var id = gnrl._is_undf( params.id );
	var v_token = gnrl._is_undf( params.v_token );
	
	if( !id ){ _status = 0; _message = 'err_req_user_id'; }
	if( _status && !v_token ){ _status = 0; _message = 'err_req_auth_token'; }
	
	var folder = 'drivers';
	
	if( !_status ){
		gnrl._api_response( res, 0, _message );
	}
	else{
		
		var _q = " SELECT ";
		_q += " a.v_image, a.v_name, a.v_email, a.v_phone, a.v_password, a.v_gender, a.v_token, a.i_city_id ";
		_q += " , b.v_type as v_vehicle_type ";
		_q += " , b.v_vehicle_number ";
		_q += " , b.v_image_rc_book ";
		_q += " , b.v_image_puc ";
		_q += " , b.v_image_insurance ";
		
		_q += " , b.v_image_license ";
		_q += " , b.v_image_adhar_card ";
		_q += " , b.v_image_permit_copy ";
		_q += " , b.v_image_police_copy ";

		_q += " , b.v_image_rc_book_2 ";
		_q += " , b.v_image_adhar_card_2 ";
		_q += " , b.v_image_license_2 ";
		
		_q += " FROM ";
		_q += " tbl_user AS a ";
		_q += " LEFT JOIN tbl_vehicle AS b ON a.id = b.i_driver_id ";
		_q += " WHERE true ";
		_q += " AND a.v_role = 'driver' ";
		_q += " AND a.id = '"+id+"'  ";
		
		
		dclass._query( _q, function( status, data ){
			if( status && !data.length ){
				gnrl._api_response( res, 0, 'err_msg_no_account', {} );
			}
			else{
				if( v_token != data[0].v_token ){
					gnrl._api_response( res, 0, 'err_invalid_auth_token', {} );
				}
				else{
					var fileArr = {
						'v_image' 				: folder,
						'v_image_rc_book' 		: folder,
						'v_image_puc' 			: folder,
						'v_image_insurance' 	: folder,
						'v_image_license' 		: folder,
						'v_image_adhar_card'	: folder,
						'v_image_permit_copy'	: folder,
						'v_image_police_copy' 	: folder,
						
						'v_image_rc_book_2' 	: folder,
						'v_image_adhar_card_2' 	: folder,
						'v_image_license_2' 	: folder,
						
					};
					for( var k in fileArr ){
						if( data[0][k] ){ data[0][k] = gnrl._uploads( fileArr[k]+'/'+data[0][k] ) }
					}
					gnrl._api_response( res, 1, '', data[0] );
				}
			}
		});
	}
	
};

module.exports = currentApi;
