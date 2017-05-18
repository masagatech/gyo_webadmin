var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');



var currentApi = function( req, res, next ){
	
	var classes = req.app.get('classes');
	classes.gnrl._extract( classes, this ); // Extract Classes
	var _p = gnrl._p;
	
	var params = gnrl._frm_data( req );
	var _lang = gnrl._getLang( params );
	
	var _status  	= 1;
	var _message 	= '';
	var _response 	= {};
	
	
	dclass._select( '*', 'tbl_user', " ORDER BY id DESC", function( status, data ){ 
		if( status && !data.length ){
			gnrl._api_response( res, 0, _message );
		}
		else{
			gnrl._api_response( res, 1, _message, data, 0 );
		}
	});
	
};

module.exports = currentApi;
