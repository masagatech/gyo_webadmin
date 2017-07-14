var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');



var currentApi = function( req, res, next ){
	
	var classes = req.app.get('classes');
	classes.gnrl._extract( classes, this ); // Extract Classes
	var _p = gnrl._p;
	
	var params = gnrl._frm_data( req );
	var _lang = gnrl._getLang( params );
	
	var city = gnrl._is_undf( params.city );
	
	var _status = 1;
	var _message = '';
	var _response = {};
	
	// city
	var _q = " SELECT ";
	_q += " id, v_name, v_code, l_data ";
	_q += " FROM tbl_city ";
	_q += " WHERE true ";
	if( city = city.trim() ){
		_q += " AND LOWER( v_name ) = '"+city.toLowerCase()+"' ";
	}
	_q += " AND i_delete = '0' AND e_status = 'active' ORDER BY v_name ";
	
	dclass._query( _q, function( status, data ){
		if( !status ){
			gnrl._api_response( res, 0, _message );
		}
		else if( !data.length ){
			gnrl._api_response( res, 0, 'err_no_records', [] );
		}
		else{
			gnrl._api_response( res, 1, _message, data );
		}
	});
	
	
};

module.exports = currentApi;
