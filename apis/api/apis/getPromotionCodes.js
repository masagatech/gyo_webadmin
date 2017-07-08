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
	
	var _status   = 1;
	var _message  = '';
	var _response = {};
	
	var login_id = gnrl._is_undf( params.login_id );
	var city = gnrl._is_undf( params.city );

	if( !city ){ _status = 0; _message = 'err_req_city'; }
	
	if( _status ){	

		var _promocodes = [];
		async.series([
			function( callback ){
				var _q = " SELECT a.id, a.v_title, a.v_code, a.d_start_date, a.d_end_date, a.l_description ";
				_q += " FROM tbl_coupon_code AS a ";
				_q += " WHERE true ";
				_q += " AND a.i_delete = '0' AND a.e_status = 'active' ";
				_q += " AND now() BETWEEN a.d_start_date AND a.d_end_date ";
				_q += " AND ( SELECT CAST(b.id AS TEXT) from tbl_city as b where b.i_delete = '0' AND lower(b.v_name) = lower('"+city+"') ) = ANY (string_to_array(a.i_city_ids,',')) ";
				
				dclass._query( _q, function( status, codes ){
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else{
						for (var i = 0; i < codes.length; i++) {
							codes[i]['d_start_date'] = gnrl._timestamp( codes[i]['d_start_date'] );
							codes[i]['d_end_date'] = gnrl._timestamp( codes[i]['d_end_date'] );
							if( gnrl._isNull( codes[i]['l_description'] ) ){
								codes[i]['l_description'] = "";
							}
						}
						_promocodes = codes;
						callback( null );
					}
				});
			},
		], 
		function( error, results ){
			gnrl._api_response( res, 1, '', _promocodes );
		});
		
	}
	else{
		gnrl._api_response( res, 0, _message, {} );
	}
};

module.exports = currentApi;
