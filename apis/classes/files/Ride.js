var express = require('express');
var async = require('async');


var currClass = function( params ){
	
	// Extract Variables
	params.gnrl._extract( params, this );
	
	var table = 'tbl_ride';
	
	return {
		
		get : function( ride_id, cb ){
			
			var _self = this;
			
			dclass._select( '*', table, " AND id = '"+ride_id+"' ", function( status, data ){
				if( status && data.length ){
					var _subkeys = {
						actual_distance : 0,
						final_amount : 0,
						actual_dry_run : 0,
						apply_dry_run : 0,
						apply_dry_run_amount : 0,
						ride_paid_by_cash : 0,
						ride_paid_by_wallet : 0,
						trip_time : {
							days : 0,
							hours : 0,
							minutes : 0,
							seconds : 0,
						},
						trip_time_in_min : 0,
					};
					for( var k in _subkeys ){
						if( !data[0].l_data[k] ){
							data[0].l_data[k] = _subkeys[k];
						}
					}
				}
				cb( status, data );
			});
		},
		
		getChargesData : function( data, cb ){
			
			var _self = this;
			
			var tempArr = {
				'min_charge' : 0,
				'base_fare' : 0,
				'service_tax' : 0,
				'surcharge' : 0,
				'ride_time_charge' : 0,
				'max_dry_run_km' : 0,
				'max_dry_run_charge' : 0,
				'upto_km' : 0,
				'upto_km_charge' : 0,
				'after_km_charge' : 0,
			};
			for( var k in tempArr ){
				tempArr[k] = gnrl._round( parseFloat( data.l_data.charges[k] ? data.l_data.charges[k] : 0 ) );
			}
			cb( tempArr );
		},
		
		calculateDistances : function( ride_id, cb ){
			
			var _self = this;
			
			var _result = {
				actual_dry_run : 0,
				actual_distance : 0,
			};
			dclass._select( '*', 'tbl_track_vehicle_location', " AND l_data->>'i_ride_id' = '"+ride_id+"'", function( status, data ){
				if( !status ){
					cb( status, _result );
				}
				else{
					for( var k in data ){
						var temp = parseFloat( data[k].l_data.distance ? data[k].l_data.distance : 0 );
						if( data[k].l_data.run_type && data[k].l_data.run_type == 'ride' ){
							_result.actual_distance += temp;
						}
						else{
							_result.actual_dry_run += temp;
						}
					}
					cb( status, _result );
				}
			});
		},
		
		getFinalTotal : function( ride_id, cb ){
			var _self = this;
			var _q = " SELECT COALESCE( SUM( f_amount ), 0 ) AS final_amount FROM tbl_ride_charges WHERE i_ride_id = '"+ride_id+"' ";
			dclass._query( _q, function( status, data ){
				if( !status ){
					cb( 0 );
				}
				else{
					cb( gnrl._round( data[0].final_amount ) );
				}
			});
		},
		
		overWriteChargeVehicleWise : function( params, cb ){
			
			var _self = this;
			
			var i_ride_id = params.i_ride_id ? params.i_ride_id : 0;
			if( !i_ride_id ){
				return cb( 0, '' );
			}
			
			var _ride = {};
			var _vehicle_wise = {};
			
			/*
				>> Get Ride
				>> Get Vehicle Wise Charge
				>> Over Write Charges & Update Ride
			*/
			
			async.series([
				
				// Get Ride
				function( callback ){
					_self.get( i_ride_id, function( status, ride ){
						if( !status ){
							return cb( 0, '' );
						}
						else if( !ride.length ){
							return cb( 0, '' );
						}
						else{
							_ride = ride[0];
							callback( null );
						}
					});
				},
				
				// Get Vehicle Wise Charge
				function( callback ){
					
					var _q = " SELECT * FROM tbl_vehicle_fairs ";
					_q += " WHERE true ";
					_q += " AND v_type = 'vehicle_wise' ";
					_q += " AND e_status = 'active' ";
					_q += " AND i_vehicle_id = '"+_ride.i_vehicle_id+"' ";
					
					dclass._query( _q, function( status, vehicle_charge ){
						if( !status ){
							return cb( 0, '' );
						}
						else if( !vehicle_charge.length ){
							return cb( 0, '' );
						}
						else{
							_vehicle_wise = vehicle_charge[0];
							callback( null );
						}
					});
				},
				
				// Over Write Charges & Update Ride
				function( callback ){
					
					var _charge2 = _vehicle_wise.l_data.charges;
					var charges = _ride.l_data.charges;
					
					charges.vehicle_wise_id = _vehicle_wise.id ? _vehicle_wise.id : 0;
					
					if( parseFloat( _charge2.max_dry_run_km ) > 0 ){ charges.max_dry_run_km = _charge2.max_dry_run_km; }
					if( parseFloat( _charge2.max_dry_run_charge ) > 0 ){ charges.max_dry_run_charge = _charge2.max_dry_run_charge; }
					
					var _ins = [
						"l_data = l_data || '"+( gnrl._json_encode({
							'charges' : charges
						}) )+"'",
					];
					dclass._updateJsonb( table, _ins, " AND id = '"+i_ride_id+"' ", function( status, data ){ 
						return cb( 1, '' );
					});
				}
				
				
			],  function( error, results ){
				return cb( 0, '' );
			});
		},
		
		getAllChargeTypes : function( type = null ){
		
			/*
			'upto_km' => 'Upto X Km',
			'upto_km_charge' => 'Upto X Km Charge (Per Kilometer)',
			'after_km_charge' => 'After X Km Charges',
			'cancel_charge_driver' => 'Ride Cancellation Charge (Driver)',
			'cancel_charge_user' => 'Ride Cancellation Charge (Customer)',
			'max_dry_run_km' => 'Max Dry Run (In Km)',
			'max_dry_run_charge' => 'Max Dry Run Price (Per Km)',
			*/
			
			var types = {
				'base_fare' : "Base Fare",
				'min_charge' : "Minimum Charge",
				'ride_time_pick_charge' : "Ride Time Pick Charge",
				'ride_time_charge' : "Ride Time Charge",
				'toll_charge' : "Toll Charge",
				'parking_charge' : "Parking Charge",
				'other_charge' : "Other Charge",
				'service_tax' : 'Service Tax',
				'surcharge' : 'Surcharge',
				'total_fare' : 'Total Fare',
			};
			if( type != null ){
				return types[type];
			}
			return types;
		},
		
		getExtraChargeTypes : function( type = null ){
			var types = {
				'toll_charge' : "Toll Charge",
				'parking_charge' : "Parking Charge",
				'other_charge' : "Other Charge",
			};
			if( type != null ){
				return types[type];
			}
			return types;
		},
		
	}
};

module.exports = currClass;
