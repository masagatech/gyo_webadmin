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
	var i_vehicle_type_id = gnrl._is_undf( params.i_vehicle_type_id );
	
	if( !i_city_id ){ _status = 0; _message = 'err_req_city'; }
	if( _status && !i_vehicle_type_id ){ _status = 0; _message = 'err_req_vehicle_type'; }
	
	var _data = {};
	
	if( !_status ){
		gnrl._api_response( res, 0, _message, {} );
	}
	else{
	
		async.series([
			
			// Get Charges
			function( callback ){
				
				var _q = "SELECT ";
				
				_q += " vt.l_data ";
				_q += " , COALESCE( ( city_wise.l_data->>'charges' )::jsonb, '{}' ) AS city_wise_charges ";
				
				_q += " FROM tbl_vehicle_type vt ";
				
				_q += " LEFT JOIN tbl_vehicle_fairs city_wise ON ( ";
					_q += " city_wise.v_type = 'city_wise' ";
					_q += " AND city_wise.i_city_id = '"+i_city_id+"' ";
					_q += " AND city_wise.i_vehicle_type_id = vt.id ";
				_q += " ) ";
				
				_q += " WHERE true ";
				
				_q += " AND vt.i_delete = '0' ";
				_q += " AND vt.e_status = 'active' ";
				_q += " AND vt.id = '"+i_vehicle_type_id+"'; ";
				
				dclass._query( _q, function( status, data ){ 
					if( !status ){
						gnrl._api_response( res, 0, '', {} );
					}
					else if( !data.length ){
						gnrl._api_response( res, 0, 'err_no_records', {} );
					}
					else{
						
						_data = data[0];
						
						_data.l_data.list_icon = _data.l_data.list_icon ? gnrl._uploads( 'vehicle_type/'+_data.l_data.list_icon ) : '';
						_data.l_data.plotting_icon = _data.l_data.plotting_icon ? gnrl._uploads( 'vehicle_type/'+_data.l_data.plotting_icon ) : '';
						
						var temp = _data.city_wise_charges;
						for( var k in temp ){
							if( temp[k] ){
								_data.l_data.charges[k] = temp[k];
							}
						}
						if( _data.city_wise_charges ){ delete _data.city_wise_charges; }
						if( _data.l_data.driver_charges ){ delete _data.l_data.driver_charges; }
						if( _data.l_data.other ){ delete _data.l_data.other; }
						if( _data.l_data.plotting_icon ){ delete _data.l_data.plotting_icon; }
						if( _data.l_data.active_icon ){ delete _data.l_data.active_icon; }
						
						callback( null );
					}
				});
			},
			
		], function( error, results ){
			_data.l_data.charges.surcharge = _data.l_data.charges.surcharge+'%';
			_data.l_data.charges.service_tax = _data.l_data.charges.service_tax+'%';
			gnrl._api_response( res, 1, '', _data );
			
		});
	}
};

module.exports = currentApi;
