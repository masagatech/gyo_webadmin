var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');
var async 		= require('async');


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
	var v_charge_type = gnrl._is_undf( params.v_charge_type );
	var v_charge_info = gnrl._is_undf( params.v_charge_info );
	var f_amount = gnrl._is_undf( params.f_amount );
	
	if( !i_ride_id ){ _status = 0; _message = 'err_req_ride_id'; }
	if( _status && !v_charge_type ){ _status = 0; _message = 'err_req_charge_type'; }
	if( _status && !f_amount ){ _status = 0; _message = 'err_req_amount'; }
	
	var _types = Ride.getExtraChargeTypes();
	if( gnrl._isNull( _types[v_charge_type] ) ){
		_status = 0; _message = 'err_invalid_charge_type';
	}
	
	if( !v_charge_info && ( v_charge_type == 'other_charge' || v_charge_type == 'ride_time_pick_charge' ) ){
		// _status = 0; _message = 'err_req_charge_info';
	}
	if( isNaN( f_amount ) ){
		_status = 0; _message = 'err_invalid_amount';
	}
	
	if( !_status ){
		gnrl._api_response( res, 0, _message );
	}
	else{
		
		f_amount = parseFloat( f_amount );
		
		var l_data = {};
		
		async.series([
			
			// Add Charges
			function( callback ){
				var _ins = {
					'i_ride_id' : i_ride_id,
					'v_charge_type' : v_charge_type,
					'f_amount' : f_amount,
					'd_added' : gnrl._db_datetime(),
					'l_data' : gnrl._json_encode({
						'i_added_by' : login_id,
						'v_charge_info' : v_charge_info,
					}),
				};
				
				dclass._insert( 'tbl_ride_charges', _ins, function( status, data ){ 
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else{
						callback( null );
					}
				});
				
			},
			
			// Calculate Other Charge
			function( callback ){
				dclass._select( 'l_data', 'tbl_ride', " AND id = '"+i_ride_id+"' ", function( status, data ){
					l_data = data[0].l_data;
					callback( null );
				});
			},
			
			// Update Chrage
			function( callback ){
				
				l_data.charges.other_charge = parseFloat( l_data.charges.other_charge ? l_data.charges.other_charge : 0 );
				l_data.charges.other_charge += f_amount;
				
				var _ins = [
					" l_data = l_data || '"+gnrl._json_encode( l_data )+"' "
				];
				
				dclass._updateJsonb( 'tbl_ride', _ins, " AND id = '"+i_ride_id+"' ", function( status, data ){ 
					callback( null );
				});
				
			}
			
			
		], function( error, results ){
			
			gnrl._api_response( res, 1, 'succ_ride_charge_added', {} );
			
		});
		
		
		
		/*
		
		*/
		
	}
};

module.exports = currentApi;
