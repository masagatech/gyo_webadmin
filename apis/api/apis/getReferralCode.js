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
	
	var login_id = gnrl._is_undf( params.login_id ).trim();	
	
	if( _status ){	

		var data = {
			'v_referral_code' : "",
			'earn_money'      : 0,
		};

		var condition = " AND id = '"+login_id+"'";
		dclass._select( "*", 'tbl_user', condition, function( status, user ){
			if( status && !user.length ){
				gnrl._api_response( res, 0, 'err_no_records', _response );
			}
			else{

				var user = user[0];
				var v_referral_code = "";
				if( gnrl._isNull(user.l_data) ){

					var random_key = gnrl._get_random_key(4);
					data.v_referral_code = random_key+login_id;

					// Update referral code
					var _ins = [
						"l_data = l_data || '"+( gnrl._json_encode({
							'v_referral_code' : data.v_referral_code,
						}) )+"'",
					];	
					dclass._updateJsonb( "tbl_user", _ins, " AND id = '"+login_id+"'", function( status, jdata ){ 
						gnrl._api_response( res, 1, '', data );
					});

				}
				else{
					data.v_referral_code = user.l_data.v_referral_code;
					gnrl._api_response( res, 1, '', data );
				}

			}
		});
		
	}
	else{
		gnrl._api_response( res, 0, _message, _sort_by, 0 );
	}
};

module.exports = currentApi;
