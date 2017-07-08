var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');

var currentApi = function( req, res, next ){
	
	var dclass 	= req.app.get('Database');
	var gnrl 	= req.app.get('gnrl');
	var _p 		= req.app.get('_p');
	
	
	var params 	= gnrl._frm_data( req );
	var _lang 	= gnrl._getLang( params, req.app.get('_lang') );
	
	var _status   = 1;
	var _message  = '';
	var _response = {};
	
	var i_driver_id = gnrl._is_undf( params.i_driver_id ).trim();
		
	if( !i_driver_id ){ _status = 0; _message = gnrl._lbl( 'err_req_driver_id', _lang ); }
	
	if( _status ){	

		dclass._query( "SELECT a.*,b.l_data AS ride_l_data FROM tbl_buzz AS a LEFT JOIN tbl_ride AS b ON b.id = a.i_ride_id WHERE a.i_driver_id = '"+i_driver_id+"' AND a.is_alive = 1 AND a.i_status = 0 LIMIT 1", function( status, buzz ){ 
			if( status ){
				var buzz = buzz[0];
				var buzz_response = { 
					'i_buzz_id' : buzz.id,
					'i_ride_id' : buzz.i_ride_id,
					'pickup_address' : buzz.ride_l_data.pickup_address ? buzz.ride_l_data.pickup_address : '',
				};
				gnrl._api_response( res, 1, '', buzz_response );
			}
			else{
				gnrl._api_response( res, 0, gnrl._lbl( 'err_msg_no_account', _lang ), {}, 0 );
			}
		});
		
	}
	else{
		gnrl._api_response( res, 0, _message, {}, 0 );
	}
};

module.exports = currentApi;
