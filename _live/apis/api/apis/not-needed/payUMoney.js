var express   = require('express');
var validator = require('validator');
var md5 	  = require('md5');
var PayU      = require('payu');

var currentApi = function( req, res, next ){
	
	var dclass 	= req.app.get('Database');
	var gnrl 	= req.app.get('gnrl');
	var _p 		= req.app.get('_p');
	
	var params 	= gnrl._frm_data( req );
	var _lang 	= gnrl._getLang( params, req.app.get('_lang') );
	
	var _status   = 1;
	var _message  = '';
	var _response = {};
	
	if( _status ){
				
		var merchant_id = "";
		var salt        = "";
		var payu_url    = "";
		var payu = new PayU(merchant_id, salt, payu_url);
		
	}
	else{
		gnrl._api_response( res, 0, _message, {}, 0 );
	}
};

module.exports = currentApi;
