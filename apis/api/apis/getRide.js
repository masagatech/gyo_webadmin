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
	var i_ride_id = gnrl._is_undf( params.i_ride_id );
	
	if( !i_ride_id ){ _status = 0; _message = 'err_req_ride_id'; }
	
	if( _status ){
		
		var _row = {
		};
		
		/*
		>> Get Ride
		>> Get Vehicle Icon
		>> Get Driver
		>> Get User
		>> Get Rate & Comment
		>> Get Estimated Prices
		*/
		
		async.series([
			
			
			// Get Ride
			function( callback ){
				
				var _q = " SELECT ";
				_q += " rd.* ";
				
				_q += " , COALESCE( vt.l_data->>'list_icon', '' ) AS list_icon ";
				_q += " , COALESCE( vt.l_data->>'plotting_icon', '' ) AS plotting_icon ";
				
				_q += " , COALESCE( ur.id, 0 ) AS user_id ";
				_q += " , COALESCE( ur.v_name, '' ) AS user_v_name ";
				_q += " , COALESCE( ur.v_email, '' ) AS user_v_email ";
				_q += " , COALESCE( ur.v_phone, '' ) AS user_v_phone ";
				_q += " , COALESCE( ur.v_image, '' ) AS user_v_image ";
				_q += " , COALESCE( ur.v_id, '' ) AS user_v_id ";
				
				_q += " , COALESCE( dr.id, 0 ) AS driver_id";
				_q += " , COALESCE( dr.v_image, '' ) AS driver_v_image";
				_q += " , COALESCE( dr.v_name, '' ) AS driver_v_name";
				_q += " , COALESCE( dr.v_email, '' ) AS driver_v_email";
				_q += " , COALESCE( dr.v_phone, '' ) AS driver_v_phone";
				_q += " , COALESCE( dr.v_id, '' ) AS driver_v_id";
				
				_q += " , COALESCE( vh.id, 0 ) AS vehicle_id ";
				_q += " , COALESCE( vh.v_image_rc_book, '' ) AS v_image_rc_book ";
				_q += " , COALESCE( vh.v_image_puc, '' ) AS v_image_puc ";
				_q += " , COALESCE( vh.v_image_insurance, '' ) AS v_image_insurance ";
				_q += " , COALESCE( vh.v_image_license, '' ) AS v_image_license ";
				_q += " , COALESCE( vh.v_image_adhar_card, '' ) AS v_image_adhar_card ";
				_q += " , COALESCE( vh.v_image_permit_copy, '' ) AS v_image_permit_copy ";
				_q += " , COALESCE( vh.v_image_police_copy, '' ) AS v_image_police_copy ";
				
				_q += " , COALESCE( rr.i_rate, 0 ) AS i_rate";
				_q += " , COALESCE( rr.l_comment, '' ) AS rate_cmment";
				
				_q += " , COALESCE( drr.i_rate, 0 ) AS driver_i_rate";
				_q += " , COALESCE( drr.l_comment, '' ) AS driver_rate_cmment";
				
				
				_q += " FROM tbl_ride rd ";
				
				_q += " LEFT JOIN tbl_vehicle_type vt ON vt.v_type = rd.l_data->>'vehicle_type' ";
				
				_q += " LEFT JOIN tbl_user ur ON ur.id = rd.i_user_id ";
				
				_q += " LEFT JOIN tbl_user dr ON dr.id = rd.i_driver_id ";
				
				_q += " LEFT JOIN tbl_vehicle vh ON vh.i_driver_id = dr.id ";
				
				_q += " LEFT JOIN tbl_ride_rate rr ON rr.i_ride_id = rd.id AND rr.i_target_user_id = dr.id ";
				
				_q += " LEFT JOIN tbl_ride_rate drr ON drr.i_ride_id = rd.id AND drr.i_target_user_id = ur.id ";
				
				_q += " WHERE rd.id = '"+i_ride_id+"' ";
				
				_q += " AND ( rd.i_user_id = '"+login_id+"' OR rd.i_driver_id = '"+login_id+"' ) ";
				
				dclass._query( _q, function( status, data ){
					
					if( !status ){
						gnrl._api_response( res, 0, 'error', _row );
					}
					else if( !data.length ){
						gnrl._api_response( res, 0, 'err_no_ride', _row );
					}
					else{
						
						_row 			= data[0];
						
						_row.d_time  	= gnrl._timestamp( _row.d_time );
						_row.d_start 	= _row.d_start ? gnrl._timestamp( _row.d_start ) : '';
						_row.d_end 	 	= _row.d_end ? gnrl._timestamp( _row.d_end ) : '';
						
						_row.v_pin		= _row.v_pin.toString();
						_row.v_pin 		= _row.v_pin.substring(0,4)+"-"+_row.v_pin.substring(4,8);
						
						// Vehicle Type Icons
						_row.vehicle_type_data = {
							list_icon 		: gnrl._uploads( 'vehicle_type/'+_row.list_icon ),
							plotting_icon 	: gnrl._uploads( 'vehicle_type/'+_row.plotting_icon ),
						};
						
						// User Data
						_row.user_data = {
							"id"		: _row.user_id,
							"v_name"	: _row.user_v_name,
							"v_email"	: _row.user_v_email,
							"v_phone"	: _row.user_v_phone,
							"v_image"	: _row.user_v_image ? gnrl._uploads( 'users/'+_row.user_v_image ) : '',
							"v_id"		: _row.user_v_id
						};
						
						// Driver Data
						_row.driver_data = {
							
							"id"					: _row.driver_id,
							"driver_name"			: _row.driver_v_name,
							"driver_email"			: _row.driver_v_email,
							"driver_phone"			: _row.driver_v_phone,
							"driver_image"			: _row.driver_v_image ? gnrl._uploads( 'drivers/'+_row.driver_v_image ) : '',
							"v_id"					: _row.driver_v_id,
							
							"vehicle_id"			: _row.vehicle_id,
							"v_image_rc_book"		: _row.v_image_rc_book 		? gnrl._uploads( 'drivers/'+_row.v_image_rc_book ) : '',
							"v_image_puc"			: _row.v_image_puc 			? gnrl._uploads( 'drivers/'+_row.v_image_puc ) : '',
							"v_image_insurance"		: _row.v_image_insurance 	? gnrl._uploads( 'drivers/'+_row.v_image_insurance ) : '',
							"v_image_license"		: _row.v_image_license 		? gnrl._uploads( 'drivers/'+_row.v_image_license ) : '',
							"v_image_adhar_card"	: _row.v_image_adhar_card 	? gnrl._uploads( 'drivers/'+_row.v_image_adhar_card ) : '',
							"v_image_permit_copy"	: _row.v_image_permit_copy 	? gnrl._uploads( 'drivers/'+_row.v_image_permit_copy ) : '',
							"v_image_police_copy"	: _row.v_image_police_copy 	? gnrl._uploads( 'drivers/'+_row.v_image_police_copy ) : ''
							
						};
						
						// Rate
						_row.rate = {
							"i_rate"		: _row.i_rate,
							"rate_cmment"	: _row.rate_cmment,
						};
						
						_row.user_rate = {
							"i_rate"		: _row.i_rate,
							"rate_cmment"	: _row.rate_cmment,
						};
						
						_row.driver_rate = {
							"i_rate"		: _row.driver_i_rate,
							"rate_cmment"	: _row.driver_rate_cmment,
						};
						
						
						var _deletable = [
							'list_icon',
							'plotting_icon',
							
							'user_id',
							'user_v_name',
							'user_v_email',
							'user_v_phone',
							'user_v_image',
							'user_v_id',
							
							'driver_id',
							'driver_v_name',
							'driver_v_email',
							'driver_v_phone',
							'driver_v_image',
							'driver_v_id',
							'vehicle_id',
							'v_image_rc_book',
							'v_image_puc',
							'v_image_insurance',
							'v_image_license',
							'v_image_adhar_card',
							'v_image_permit_copy',
							'v_image_police_copy',
							
							'i_rate',
							'rate_cmment',
							
							'driver_i_rate',
							'driver_rate_cmment',
							
						];
						
						for( var k in _deletable ){
							delete _row[_deletable[k]];
						}
						
						callback( null );
					}
				});
			},
			
			// Ride Set Charges
			function( callback ){
				Ride.getChargesData( _row.l_data, function( data ){
					_row.l_data = data;
					callback( null );
				});
			},
			
			// Get Estimated Prices
			function( callback ){
				
				var estimate_km 	= parseFloat( _row.l_data.estimate_km );
				var estimate_time 	= parseFloat( _row.l_data.estimate_time );
				
				var estimation = {
					estimate_km 	 : estimate_km,
					estimate_time 	 : estimate_time,
					min_charge 		 : 0,
					base_fare 		 : 0,
					total_fare 		 : 0,
					ride_time_charge : 0,
					service_tax 	 : 0,
					surcharge 		 : 0,
					final_total 	 : 0,
				};
				
				// Min Charge
				var amt = parseFloat( _row.l_data.charges.min_charge );
				if( amt > 0 ){
					estimation.min_charge = amt;
				}
				
				// Base Fare
				var amt = parseFloat( _row.l_data.charges.base_fare );
				if( amt > 0 ){
					estimation.base_fare = amt;
				}
				
				// Total Fare
				if( estimate_km > 0 ){
					
					var upto_km 		= parseFloat( _row.l_data.charges.upto_km );
					var upto_km_charge 	= parseFloat( _row.l_data.charges.upto_km_charge );
					var after_km_charge = parseFloat( _row.l_data.charges.after_km_charge );
					
					var amt = 0; 
					if( estimate_km > upto_km ){ 
						amt += ( upto_km_charge * upto_km ); 
						amt += ( after_km_charge * ( estimate_km - upto_km ) ); 
					}
					else{
						amt += ( upto_km_charge * estimate_km ); 
					}
					
					estimation.total_fare = amt;
					
				}
				
				// Ride Time Charge
				var amt = parseFloat( _row.l_data.charges.ride_time_charge );
				if( amt > 0 ){
					estimation.ride_time_charge = parseFloat( amt * estimate_time );
				}
				
				// Service Tax
				var amt = parseFloat( _row.l_data.charges.service_tax );
				if( amt > 0 ){
					var tempTotal = (
						estimation.min_charge
						+ estimation.base_fare
						+ estimation.total_fare
						+ estimation.ride_time_charge
					);
					estimation.service_tax = parseFloat( ( tempTotal * amt ) / 100 );
				}
				
				// Surcharge
				var amt = parseFloat( _row.l_data.charges.surcharge );
				if( amt > 0 ){
					var tempTotal = (
						estimation.min_charge
						+ estimation.base_fare
						+ estimation.total_fare
						+ estimation.ride_time_charge
						+ estimation.service_tax
					);
					estimation.surcharge = parseFloat( ( tempTotal * amt ) / 100 );
				}
				
				estimation.final_total = gnrl._round(
					estimation.min_charge
					+ estimation.base_fare
					+ estimation.total_fare
					+ estimation.ride_time_charge
					+ estimation.service_tax
					+ estimation.surcharge
				);
				
				_row.l_data.estimation = estimation;
				
				callback( null );
			},
			
		], 
		function( error, results ){
			
			gnrl._api_response( res, 1, '', _row );
			
		});
		
		
	}
	else{
		gnrl._api_response( res, 0, _message );
	}
};

module.exports = currentApi;
