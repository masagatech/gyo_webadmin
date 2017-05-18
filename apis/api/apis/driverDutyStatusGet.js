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
		var _q = "SELECT a.v_token, b.e_status FROM tbl_user AS a LEFT JOIN tbl_vehicle AS b ON a.id = b.i_driver_id WHERE a.id = '"+login_id+"' ";
		dclass._query( _q, function( status, data ){
			if( !status ){
				gnrl._api_response( res, 0, '', {} );
			}
			else{
				gnrl._api_response( res, 1, '', { e_status : data[0].e_status });
			}
		});
	}
	else{
		gnrl._api_response( res, 0, _message );
	}
};

module.exports = currentApi;
