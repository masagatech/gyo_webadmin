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
	
	if( _lables[_lang] ){
		gnrl._api_response( res, 1, _message, _lables[_lang] );
	}
	else{
		gnrl._api_response( res, 0, 'err_invalid_lang', _lables );
	}
	
};

module.exports = currentApi;
