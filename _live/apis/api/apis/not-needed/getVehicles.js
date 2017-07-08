var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');


var currentApi = function( req, res, next ){
	
	var dclass 	= req.app.get('Database');
	var gnrl 	= req.app.get('gnrl');
	var _p 		= req.app.get('_p');
	
	
	var params 	= gnrl._frm_data( req );
	var _lang 	= gnrl._getLang( params, req.app.get('_lang') );
	
	var _status   = 1;
	var _message  = '';
	var _response = {};
	
	var address = gnrl._is_undf( params.address ).trim();
	
	if( !address ){ _status = 0; _message = 'Address is required'; }
	
	var vehicleList = [{ 
		'id' : 'V001', 
		'v_name' : 'V1', 
		'v_image' : '', 
		'time' : '10 Mins', 
	},{ 
		'id' : 'V002', 
		'v_name' : 'V2', 
		'v_image' : '', 
		'time' : '15 Mins', 
	}];
	
	if( _status ){
		gnrl._api_response( res, 1, _message, vehicleList, 0 );
		
	}
	else{
		gnrl._api_response( res, 0, _message, {}, 0 );
	}
};

module.exports = currentApi;
