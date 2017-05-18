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
	
	var login_id   = gnrl._is_undf( params.login_id ).trim();
	var city = gnrl._is_undf( params.city ).trim();
	var v_code = gnrl._is_undf( params.v_code ).trim();

	if( !city ){ _status = 0; _message = 'err_req_city'; }
	if( _status && !v_code ){ _status = 0; _message = 'err_req_promo_code'; }
	
	if( _status ){	

		var _promocodes = {};
		async.series([
			function( callback ){
				
				var _q = " SELECT a.id, a.v_title, a.v_code, a.d_start_date, a.d_end_date, a.l_description ";
				_q += " ,(SELECT b.id FROM tbl_city AS b WHERE b.v_name = '"+city+"') AS city_id ";
				_q += " ,(now() BETWEEN a.d_start_date AND a.d_end_date) AS available_on_date ";
				_q += " ,string_to_array(a.i_city_ids,',') AS city_ids_array";
				_q += " FROM tbl_coupon_code AS a ";
				_q += " WHERE true ";
				_q += " AND a.v_code = '"+v_code+"' ";

				dclass._query( _q, function( status, codes ){
					
					if( !status ){
						gnrl._api_response( res, 0, 'error', {} );
					}
					else if( !codes.length ){
						gnrl._api_response( res, 0, 'err_invalid_promotion_code', {} );
					}
					else{

						var code = codes[0];
						if( code.e_status == 'inactive' ){
							gnrl._api_response( res, 0, 'err_promotion_code_closed', {} );
						}
						else if( code.city_ids_array.indexOf(code.city_id) == -1 ){
							gnrl._api_response( res, 0, 'err_promotion_code_not_in_city', {} );
						}
						else if( code.available_on_date == false ){
							gnrl._api_response( res, 0, 'err_promotion_code_expired', {} );
						}
						else{
							
							code['d_start_date'] = gnrl._timestamp( code['d_start_date'] );
							code['d_end_date'] = gnrl._timestamp( code['d_end_date'] );
							
							delete code['available_on_date'];
							delete code['city_ids_array'];

							_promocodes = code;
							callback( null );

						}

					}

				});
			},
		], 
		function( error, results ){
			gnrl._api_response( res, 1, 'succ_promotion_code_avail', _promocodes );
		});
		
	}
	else{
		gnrl._api_response( res, 0, '_message' );
	}
};

module.exports = currentApi;
