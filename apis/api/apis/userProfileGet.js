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
	
	var login_id = gnrl._is_undf( params.login_id ).trim();
	
	if( _status ){
		dclass._select( '*', 'tbl_user', " AND v_role = 'user' AND ( id = '"+login_id+"' )", function( status, data ){ 
			if( !status ){
				gnrl._api_response( res, 0, '', {} );
			}
			else if( !data.length ){
				gnrl._api_response( res, 0, 'err_msg_no_account', {} );
			}
			else{
				if( data[0].v_image ){
					data[0].v_image = gnrl._uploads( 'users/'+data[0].v_image )
				}
				gnrl._api_response( res, 1, '', data[0] );
			}
		});
	}
	else{
		gnrl._api_response( res, 0, _message );
	}
};

module.exports = currentApi;
