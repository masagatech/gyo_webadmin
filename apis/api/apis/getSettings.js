var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');



var currentApi = function( req, res, next ){
	
	var classes = req.app.get('classes');
	classes.gnrl._extract( classes, this ); // Extract Classes
	var _p = gnrl._p;
	
	var params = gnrl._frm_data( req );
	var _lang = gnrl._getLang( params );
	
	var _status = 1;
	var _message = '';
	var _response = {};
	
	var key = gnrl._is_undf( params.key );
	if( !key.length ){ _status = 0; _message = 'err_invalid_key'; }
	
	if( !_status ){
		gnrl._api_response( res, 0, _message );
	}
	else{
		Settings.getMulti( key, function( status, data ){
			gnrl._api_response( res, 1, _message, data );
		});
	}
};

module.exports = currentApi;
