var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');
var async       = require('async');



var currentApi = function( req, res, next ){
	
	var classes = req.app.get('classes');
	classes.gnrl._extract( classes, this ); // Extract Classes
	var _p = gnrl._p;
	
	var params = gnrl._frm_data( req );
	var _lang = gnrl._getLang( params );
	
	var _status = 1;
	var _message = '';
	var _response = {};
	
	var login_id = gnrl._is_undf( params.login_id ).trim();
	var i_ride_id = gnrl._is_undf( params.i_ride_id ).trim();
	
	if( _status && !i_ride_id ){ _status = 0; _message = 'err_req_ride_id'; }
	
	/*
	STEPS
		>> Get Ride
		>> Remove Code & Update
	*/
	
	if( !_status ){
		
		gnrl._api_response( res, 0, _message );
		
	}
	else{

		var _data = {
			ride : {}
		};
		
		async.series([
			
			// Get Ride
			function( callback ){
				Ride.get( i_ride_id, function( status, data ){
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else if( !data.length ){
						gnrl._api_response( res, 0, 'err_no_ride', {} );
					}
					else if( data[0].i_user_id != login_id ){
						gnrl._api_response( res, 0, 'err_no_ride', {} );
					}
					else{
						_data.ride = data[0];
						callback( null );
					}
				});
			},
			
			// Remove Code & Update
			function( callback ){
				
				_data.ride.l_data.charges.promocode_id = 0;
				_data.ride.l_data.charges.promocode_code = '';
				_data.ride.l_data.charges.promocode_code_discount = 0;
				_data.ride.l_data.charges.promocode_code_discount_upto = 0;
				_data.ride.l_data.charges.promocode_code_discount_amount = 0;
				
				var _ins = [
					" l_data = l_data || '"+gnrl._json_encode( 
						_data.ride.l_data
					)+"' ",
				];
				dclass._updateJsonb( 'tbl_ride', _ins, " AND id = '"+i_ride_id+"' ", function( status, data ){
					if( !status ){
						gnrl._api_response( res, 0, 'error', { status : status, data : data } );
					}
					else{
						callback( null );
					}
				});
				
			},
			
		], 
		
		function( error, results ){
			gnrl._api_response( res, 1, 'succ_promotion_code_removed', {} );
		});
		
	}
};

module.exports = currentApi;
