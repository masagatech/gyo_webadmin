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
	
	var types = Ride.getExtraChargeTypes();
	var _types = [];

	for ( var t in types ){
		_types.push(
			[
				t,
				types[t],
			]
		);
		//_types.push( { [t] : types[t] } );
	}

	gnrl._api_response( res, 1, '', _types.reverse() );
};

module.exports = currentApi;
