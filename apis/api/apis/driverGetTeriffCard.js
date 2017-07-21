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
	
	var _status = 1;
	var _message = '';
	var _response = {};
	
	var login_id = gnrl._is_undf( params.login_id );
	var i_city_id = gnrl._is_undf( params.i_city_id );
	
	if( !i_city_id ){ _status = 0; _message = 'err_req_city'; }
	
	var _data = {};
	
	if( !_status ){
		gnrl._api_response( res, 0, _message, {} );
	}
	else{
	
		async.series([
			
			// Get Charges
			function( callback ){
				
				var _q = "SELECT ";
				
				_q += " COALESCE( vt.l_data->>'list_icon', '' ) AS list_icon ";
				_q += " , COALESCE( ( vt.l_data->>'driver_charges' )::jsonb, '{}' ) AS charges ";
				_q += " , COALESCE( ( cw.l_data->>'driver_charges' )::jsonb, '{}' ) AS city_wise_charges ";
				
				_q += " FROM tbl_vehicle_type vt ";
				
				_q += " LEFT JOIN tbl_vehicle_fairs cw ON ( ";
					_q += " cw.v_type = 'city_wise' ";
					_q += " AND cw.i_city_id = '"+i_city_id+"' ";
					_q += " AND cw.i_vehicle_type_id = vt.id ";
				_q += " ) ";
				
				_q += " WHERE true ";
				
				_q += " AND vt.i_delete = '0' ";
				_q += " AND vt.e_status = 'active' ";
				_q += " AND vt.v_type = ( SELECT v_type FROM tbl_vehicle WHERE i_driver_id = '"+login_id+"' ); ";
				
				dclass._query( _q, function( status, data ){ 
					if( !status ){
						gnrl._api_response( res, 0, '', {} );
					}
					else if( !data.length ){
						gnrl._api_response( res, 0, 'err_no_records', {} );
					}
					else{
						_data = data[0];
						_data.list_icon = _data.list_icon ? gnrl._uploads( 'vehicle_type/'+_data.list_icon ) : '';
						
						var temp = _data.city_wise_charges;
						for( var k in temp ){
							if( temp[k] ){
								_data.charges[k] = temp[k];
							}
						}
						
						delete _data.city_wise_charges;
						//_data._q = _q;
						callback( null );
					}
				});
			},
			
		], function( error, results ){
			if( _data.charges.surcharge ){
				_data.charges.surcharge = _data.charges.surcharge+'%';
			}
			if( _data.charges.surcharge ){
				_data.charges.service_tax = _data.charges.service_tax+'%';
			}
			
			gnrl._api_response( res, 1, '', _data );
			
		});
	}
};

module.exports = currentApi;
