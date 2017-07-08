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
	
	var login_id = gnrl._is_undf( params.login_id );
	var i_ride_id = gnrl._is_undf( params.i_ride_id );
	
	if( !i_ride_id ){ _status = 0; _message = 'err_req_ride_id'; }
	
	if( !_status ){
		gnrl._api_response( res, 0, _message );
	}
	else{
		Ride.getCharges( i_ride_id, function( status, data ){ 
			if( !status ){
				gnrl._api_response( res, 0, 'error', {} );
			}
			else{
				gnrl._api_response( res, 1, '', data );
			}
		});
	}
};

module.exports = currentApi;
