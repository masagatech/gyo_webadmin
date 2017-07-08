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
					_self.getDefaultFields( status, data, cb );
				}
				else{
					cb( status, data );
				}
			});
		},
		
		getWh : function( _wh, cb ){
			var _self = this;
			dclass._select( '*', table, _wh, function( status, data ){
				if( status && data.length ){
					_self.getDefaultFields( status, data, cb );
				}
				else{
					cb( status, data );
				}
			});
		},
		
		getWhSelect : function( _select, _wh, cb ){
			var _self = this;
			dclass._select( _select, table, _wh, function( status, data ){
				if( status && data.length ){
					_self.getDefaultFields( status, data, cb );
				}
				else{
					cb( status, data );
				}
			});
		},
		
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
			
			var _q = "";
			_q += " SELECT ";
			_q += " ( SELECT COALESCE( SUM( ( l_data->>'distance' )::numeric ), 0 ) FROM tbl_track_vehicle_location WHERE l_data->>'run_type' = 'ride' AND l_data->>'i_ride_id' = '"+ride_id+"' ) AS actual_distance ";
			_q += " , ( SELECT COALESCE( SUM( ( l_data->>'distance' )::numeric ), 0 ) FROM tbl_track_vehicle_location WHERE l_data->>'run_type' = 'dry_run' AND l_data->>'i_ride_id' = '"+ride_id+"' ) AS actual_dry_run ";
			
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
		
		getFinalTotal : function( ride_id, cb ){
			var _self = this;
			var _q = " SELECT COALESCE( SUM( f_amount ), 0 ) AS final_amount FROM tbl_ride_charges WHERE i_ride_id = '"+ride_id+"' ";
			dclass._query( _q, function( status, data ){
				if( !status ){
					cb( 0 );
				}
				else{
					cb( gnrl._round( data.length ? data[0].final_amount : 0 ) );
				}
			});
		},
		
		getFinalTotalWithoutDiscount : function( ride_id, cb ){
			var _self = this;
			var _q = " SELECT COALESCE( SUM( f_amount ), 0 ) AS final_amount FROM tbl_ride_charges WHERE v_charge_type NOT IN ('disount') AND i_ride_id = '"+ride_id+"' ";
			dclass._query( _q, function( status, data ){
				if( !status ){
					cb( 0 );
				}
				else{
					cb( gnrl._round( data.length ? data[0].final_amount : 0 ) );
				}
			});
		},
		
		overWriteChargeVehicleWise : function( ride_id, cb ){
			
			var _self = this;
			
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
					dclass._select( 'id, i_vehicle_id, l_data', table, " AND id = '"+ride_id+"' ", function( status, data ){
						if( status && data.length ){
							_ride = data[0];
							callback( null );
						}
						else{
							return cb();
						}
					});
				},
				
				// Get Vehicle Wise Charge
				function( callback ){
					var _q = " SELECT id,l_data FROM tbl_vehicle_fairs WHERE i_delete = '0' AND v_type = 'vehicle_wise' AND e_status = 'active' AND i_vehicle_id = '"+_ride.i_vehicle_id+"' ";
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
					
					var charges = _ride.l_data.charges;
					charges.vehicle_wise_id = _vehicle_wise.id ? _vehicle_wise.id : 0;
					
					var _charge2 = _vehicle_wise.l_data.charges;
					if( parseFloat( _charge2.max_dry_run_km ) > 0 ){ charges.max_dry_run_km = _charge2.max_dry_run_km; }
					if( parseFloat( _charge2.max_dry_run_charge ) > 0 ){ charges.max_dry_run_charge = _charge2.max_dry_run_charge; }
					
					var _ins = [
						"l_data = l_data || '"+( gnrl._json_encode({
							'charges' : charges
						}) )+"'",
					];
					dclass._updateJsonb( table, _ins, " AND id = '"+i_ride_id+"' ", function( status, data ){ 
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
				'ride_time_pick_charge' : "Ride Time Pick Charge",
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
