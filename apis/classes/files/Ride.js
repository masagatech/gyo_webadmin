var express = require('express');
var async = require('async');


var currClass = function( params ){
	
	// Extract Variables
	params.gnrl._extract( params, this );
	
	var table = 'tbl_ride';
	
	return {
		
		getDefaultFields : function( status, data, cb ){
			if( status && data.length ){
				
				var _subkeys = {
					
					actual_distance 		: 0,
					final_amount 			: 0,
					actual_dry_run 			: 0,
					apply_dry_run 			: 0,
					apply_dry_run_amount 	: 0,
					ride_paid_by_cash 		: 0,
					ride_paid_by_wallet 	: 0,
					trip_time 				: {
						days 	: 0,
						hours 	: 0,
						minutes : 0,
						seconds : 0,
					},
					trip_time_in_min 		: 0,
					
					cancel_by 				: 0,
					cancel_by_role 			: '',
					cancel_reason_id 		: 0,
					cancel_reason_id_text 	: '',
					cancel_reason_text 		: '',
					cancel_reason_final		: '',
					
					promocode_id					: 0,
					promocode_code					: '',
					promocode_code_discount			: 0,
					promocode_code_discount_upto	: 0,
					promocode_code_discount_amount	: 0,
					
				};
				
				for( var k in _subkeys ){
					for( var k1 in data ){
						if( !data[k1].l_data[k] ){
							data[k1].l_data[k] = _subkeys[k];
						}
						data[k1].l_data['cancel_reason_final'] = data[k1].l_data['cancel_reason_id_text'] ? data[k1].l_data['cancel_reason_id_text'] : data[k1].l_data['cancel_reason_text'];
					}
				}
				
				
				
				cb( status, data );
			}
			else{
				cb( status, data );
			}
		},
		
		getChargesData : function( l_data, cb ){
			
			var _self = this;
			
			l_data.actual_distance = l_data.actual_distance ? parseFloat( l_data.actual_distance ) : 0;
			
			l_data.actual_dry_run = l_data.actual_dry_run ? parseFloat( l_data.actual_dry_run ) : 0;
			l_data.apply_dry_run = l_data.apply_dry_run ? parseFloat( l_data.apply_dry_run ) : 0;
			l_data.apply_dry_run_amount = l_data.apply_dry_run_amount ? parseFloat( l_data.apply_dry_run_amount ) : 0;
			
			l_data.trip_time_in_min = l_data.trip_time_in_min ? parseFloat( l_data.trip_time_in_min ) : 0;
			l_data.trip_time = l_data.trip_time ? l_data.trip_time : { days : 0, hours : 0, minutes : 0, seconds : 0, };
			l_data.trip_time.days = l_data.trip_time.days ? parseFloat( l_data.trip_time.days ) : 0;
			l_data.trip_time.hours = l_data.trip_time.hours ? parseFloat( l_data.trip_time.hours ) : 0;
			l_data.trip_time.minutes = l_data.trip_time.minutes ? parseFloat( l_data.trip_time.minutes ) : 0;
			l_data.trip_time.seconds = l_data.trip_time.seconds ? parseFloat( l_data.trip_time.seconds ) : 0;
			
			l_data.ride_paid_by_cash = l_data.ride_paid_by_cash ? parseFloat( l_data.ride_paid_by_cash ) : 0;
			l_data.ride_paid_by_wallet = l_data.ride_paid_by_wallet ? parseFloat( l_data.ride_paid_by_wallet ) : 0;
			
			l_data.ride_driver_receivable = l_data.ride_driver_receivable ? parseFloat( l_data.ride_driver_receivable ) : 0;
			l_data.ride_driver_payable = l_data.ride_driver_payable ? parseFloat( l_data.ride_driver_payable ) : 0;
			l_data.ride_driver_received = l_data.ride_driver_received ? parseFloat( l_data.ride_driver_received ) : 0;
			
			l_data.company_commision_amount = l_data.company_commision_amount ? parseFloat( l_data.company_commision_amount ) : 0;
			
			l_data.final_amount = l_data.final_amount ? parseFloat( l_data.final_amount ) : 0;
			
			l_data.charges.min_charge = l_data.charges.min_charge ? parseFloat( l_data.charges.min_charge ) : 0;
			l_data.charges.base_fare = l_data.charges.base_fare ? parseFloat( l_data.charges.base_fare ) : 0;
			l_data.charges.ride_time_charge = l_data.charges.ride_time_charge ? parseFloat( l_data.charges.ride_time_charge ) : 0;
			l_data.charges.upto_km = l_data.charges.upto_km ? parseFloat( l_data.charges.upto_km ) : 0;
			l_data.charges.upto_km_charge = l_data.charges.upto_km_charge ? parseFloat( l_data.charges.upto_km_charge ) : 0;
			l_data.charges.after_km_charge = l_data.charges.after_km_charge ? parseFloat( l_data.charges.after_km_charge ) : 0;
			l_data.charges.other_charge = l_data.charges.other_charge ? parseFloat( l_data.charges.other_charge ) : 0;
			l_data.charges.service_tax = l_data.charges.service_tax ? parseFloat( l_data.charges.service_tax ) : 0;
			l_data.charges.surcharge = l_data.charges.surcharge ? parseFloat( l_data.charges.surcharge ) : 0;
			
			l_data.charges.max_dry_run_km = l_data.charges.max_dry_run_km ? parseFloat( l_data.charges.max_dry_run_km ) : 0;
			l_data.charges.max_dry_run_charge = l_data.charges.max_dry_run_charge ? parseFloat( l_data.charges.max_dry_run_charge ) : 0;
			l_data.charges.cancel_charge_user = l_data.charges.cancel_charge_user ? parseFloat( l_data.charges.cancel_charge_user ) : 0;
			l_data.charges.cancel_charge_driver = l_data.charges.cancel_charge_driver ? parseFloat( l_data.charges.cancel_charge_driver ) : 0;
			
			l_data.charges.company_commission = l_data.charges.company_commission ? l_data.charges.company_commission : 0;
			
			l_data.charges.promocode_id = l_data.charges.promocode_id ? parseFloat( l_data.charges.promocode_id ) : 0;
			l_data.charges.promocode_code = l_data.charges.promocode_code ? l_data.charges.promocode_code : 0;
			l_data.charges.promocode_code_discount = l_data.charges.promocode_code_discount ? parseFloat( l_data.charges.promocode_code_discount ) : 0;
			l_data.charges.promocode_code_discount_upto = l_data.charges.promocode_code_discount_upto ? parseFloat( l_data.charges.promocode_code_discount_upto ) : 0;
			l_data.charges.promocode_code_discount_amount = l_data.charges.promocode_code_discount_amount ? parseFloat( l_data.charges.promocode_code_discount_amount ) : 0;
			
			cb( l_data );
			
		},
		
		calculateDistances : function( ride_id, cb ){
			
			var _self = this;
			
			var _result = {
				actual_dry_run : 0,
				actual_distance : 0,
			};
			
			var _q = " SELECT ";
			_q += " COALESCE( SUM( CASE WHEN l_data->>'run_type' = 'ride' THEN ( l_data->>'distance' )::numeric ELSE 0 END ), 0 ) AS actual_distance, ";
			_q += " COALESCE( SUM( CASE WHEN l_data->>'run_type' = 'dry_run' THEN ( l_data->>'distance' )::numeric ELSE 0 END ), 0 ) AS actual_dry_run ";
			_q += " FROM tbl_track_vehicle_location WHERE l_data->>'i_ride_id' = '"+ride_id+"' ";
			_q += " GROUP BY l_data->>'i_ride_id' ";
			
			dclass._query( _q, function( status, data ){
				if( status && data.length ){
					_result.actual_dry_run = parseFloat( data[0].actual_dry_run );
					_result.actual_distance = parseFloat( data[0].actual_distance );
					cb( status, _result );
				}
				else{
					cb( status, _result );
				}
			});
		},
		
		overWriteChargeVehicleWise : function( ride_id, vehicle_id, l_data, cb ){
			
			var _self = this;
			
			var _vehicle_wise = {};
			
			// Get Vehicle Wise Charge
			// Over Write Charges & Update Ride
			
			async.series([
				
				// Get Vehicle Wise Charge
				function( callback ){
					var _q = " SELECT id, l_data FROM tbl_vehicle_fairs WHERE i_delete = '0' AND v_type = 'vehicle_wise' AND e_status = 'active' AND i_vehicle_id = '"+vehicle_id+"' ";
					dclass._query( _q, function( status, vehicle_charge ){
						if( !status ){
							return cb();
						}
						else if( !vehicle_charge.length ){
							return cb();
						}
						else{
							_vehicle_wise = vehicle_charge[0];
							callback( null );
						}
					});
				},
				
				// Over Write Charges & Update Ride
				function( callback ){
					
					l_data.charges.vehicle_wise_id = _vehicle_wise.id ? _vehicle_wise.id : 0;
					
					var chrg = parseFloat( _vehicle_wise.l_data.charges.max_dry_run_km );
					if( chrg > 0 ){ l_data.charges.max_dry_run_km = chrg; }
					
					var chrg = parseFloat( _vehicle_wise.l_data.charges.max_dry_run_charge );
					if( chrg > 0 ){ l_data.charges.max_dry_run_charge = chrg; }
					
					var _ins = [
						"l_data = l_data || '"+( gnrl._json_encode( l_data ) )+"'",
					];
					dclass._updateJsonb( 'tbl_ride', _ins, " AND id = '"+ride_id+"' ", function( status, data ){ 
						return cb();
					});
				}
				
			], function( error, results ){
				return cb();
			});
		},
		
		getAllChargeTypes : function( type ){
			type == undefined ? null : type;
			var types = {
				'base_fare' : "Base Fare",
				'min_charge' : "Minimum Charge",
				'ride_time_charge' : "Ride Time Charge",
				'toll_charge' : "Toll Charge",
				'parking_charge' : "Parking Charge",
				'other_charge' : "Other Charge",
				'service_tax' : 'Service Tax',
				'surcharge' : 'Surcharge',
				'total_fare' : 'Total Fare',
				'discount' : 'Discount',
			};
			if( type != null ){
				return types[type];
			}
			return types;
		},
		
		getExtraChargeTypes : function( type ){
			type == undefined ? null : type;
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
		
		getPin : function(){
			return Math.floor( 10000000 + Math.random() * 90000000 );
		},
		
		getCharges : function( ride_id, cb ){
			var _self = this;
			dclass._select( '*', 'tbl_ride_charges', " AND i_ride_id = '"+ride_id+"' ORDER BY id ASC", function( status, data ){ 
				if( status && data.length ){
					for( var k in data ){
						data[k].display_charge_type = _self.getAllChargeTypes( data[k]['v_charge_type'] );
					}
				}
				cb( status, data );
			});
		},
		
		getChargesTableStr : function( ride_id, cb ){
			
			var _self = this;
			
			dclass._select( 'f_amount, v_charge_type', 'tbl_ride_charges', " AND i_ride_id = '"+ride_id+"' ORDER BY id ASC", function( status, data ){ 
				var str = [];
				
				if( status && data.length ){
					
					str.push('<table border="0" width="100%" cellpadding="5" cellspacing="0" >');
						
						str.push('<tr>');
							str.push('<td colspan="2" align="center" ><strong>Ride Bill</strong></td>');
						str.push('</tr>');
						
						var total = 0;
						for( var k in data ){
							
							data[k].display_charge_type = _self.getAllChargeTypes( data[k]['v_charge_type'] );
							
							str.push('<tr>');
								str.push('<td align="left" >'+data[k].display_charge_type+'</td>');
								str.push('<td align="right" > ₹'+data[k].f_amount+'</td>');
							str.push('</tr>');
							total += data[k].f_amount;
						}
						
						str.push('<tr>');
							str.push('<td style="border-top:1px solid #CCC;" align="left" ><strong>Total Fare</strong></td>');
							str.push('<td style="border-top:1px solid #CCC;" align="right" ><strong>₹'+total+'</strong></td>');
						str.push('</tr>');
						
					str.push('</table>');
				}
				
				str = str.join( '' );
				
				return cb( str );
				
			});
			
		},
		
	}
};

module.exports = currClass;
