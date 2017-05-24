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
	
	City.getActiveList( function( status, data ){
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
