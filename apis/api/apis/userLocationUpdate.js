var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');
var async = require('async');


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
	var l_latitude = gnrl._is_undf( params.l_latitude, 0 );
	var l_longitude = gnrl._is_undf( params.l_longitude, 0 );
	
	if( _status && !l_latitude ){ _status = 0; _message = 'err_req_latitude'; }
	if( _status && !l_longitude ){ _status = 0; _message = 'err_req_longitude'; } 
	
	if( !_status ){
		gnrl._api_response( res, 0, _message, {} );
	}
	else{
	
		async.series([
			
			function( callback ){
				var _ins = [
					" l_latitude = '"+l_latitude+"' ",
					" l_longitude = '"+l_longitude+"' ",
					" l_data = l_data || '"+( gnrl._json_encode({
						'last_location_update' : gnrl._db_datetime(),
					}) )+"' ",
				];
				dclass._updateJsonb( 'tbl_user', _ins, " AND id = '"+login_id+"' ", function( status, data ){
					callback( null );	
				});
			},
			
		], function( error, results ){
			
			gnrl._api_response( res, 1, 'succ_location_updated', {} );
			
		});
		
	}
};

module.exports = currentApi;