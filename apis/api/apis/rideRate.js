var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');



var currentApi = function( req, res, next ){
	
	var classes = req.app.get('classes');
	classes.gnrl._extract( classes, this ); // Extract Classes
	var _p = gnrl._p;

	var params = gnrl._frm_data( req );
	var _lang = gnrl._getLang( params );
	
	var _status   = 1;
	var _message  = '';
	var _response = {};
	
	var login_id    = gnrl._is_undf( params.login_id ).trim();
	var i_ride_id   = gnrl._is_undf( params.i_ride_id ).trim();
	var i_rate      = gnrl._is_undf( params.i_rate ).trim();
	var l_comment   = gnrl._is_undf( params.l_comment ).trim();
	var v_type      = gnrl._is_undf( params.v_type ).trim();
	
	if( !i_ride_id ){ _status = 0; _message = 'err_req_id'; }
	if( _status && !i_rate ){ _status = 0; _message = 'err_req_rate'; }
	if( _status && !l_comment ){ _status = 0; _message = 'err_req_comment'; }
	if( _status && !v_type ){ _status = 0; _message = 'err_req_type'; }
	
	if( !_status ){
		gnrl._api_response( res, 0, _message );
	}
	else{

		// Add Ride Rate
		var _ins = {
			'i_ride_id'  : i_ride_id,
			'i_rate'     : parseInt(i_rate),
			'l_comment'  : l_comment,
			'i_added_by' : login_id,
			'd_added'    : gnrl._db_datetime(),
			'l_data'     : { 'v_type' : v_type }
		};

		dclass._insert( 'tbl_ride_rate', _ins, function( status, data ){ 
			if( !status ){
				gnrl._api_response( res, 0, 'error' );
			}
			else {
				if( v_type == 'user' ){
					dclass._select( "*", 'tbl_ride', " AND id = '"+i_ride_id+"'", function( status, ride ){
			
						var ride = ride[0];
						var i_driver_id = ride.i_driver_id;

						dclass._query( "SELECT SUM(tbl_ride_rate.i_rate) AS sum_rate, COUNT(tbl_ride_rate.id) AS count_rate FROM tbl_ride AS tbl_ride LEFT JOIN tbl_ride_rate AS tbl_ride_rate ON tbl_ride_rate.i_ride_id = tbl_ride.id WHERE true AND tbl_ride.i_driver_id = '"+i_driver_id+"'", function( status, ride_rate ){	

							if( status ){

								var ride_rate = ride_rate[0];
								var rate = ride_rate.sum_rate / ride_rate.count_rate; 
								var _ins = [
									"l_data = l_data || '"+( gnrl._json_encode({
										'rate' : rate,
										'rate_total' : ride_rate.count_rate,
									}) )+"'",
								];
								dclass._updateJsonb( "tbl_user", _ins, " AND id = '"+i_driver_id+"' ", function( status, ride_data ){ 
									gnrl._api_response( res, 1, 'succ_ride_rate_successfully', {});
								});

							}
							else{

								var _ins = [
									"l_data = l_data || '"+( gnrl._json_encode({
										'rate' : i_rate,
										'rate_total' : 1,
									}) )+"'",
								];
								dclass._updateJsonb( "tbl_user", _ins, " AND id = '"+i_driver_id+"' ", function( status, ride_data ){ 
									gnrl._api_response( res, 1, 'succ_ride_rate_successfully', {});
								});

							}

						});

					});
				}
				else{
					gnrl._api_response( res, 1, 'succ_ride_rate_successfully', {});
				}
			}
		});

	}
};

module.exports = currentApi;
