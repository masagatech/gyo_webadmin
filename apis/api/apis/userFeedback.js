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
	
	var login_id   = gnrl._is_undf( params.login_id ).trim();
	var i_rate   = gnrl._is_undf( params.i_rate ).trim();
	var l_comment   = gnrl._is_undf( params.l_comment ).trim();
	
	if( !i_rate ){ _status = 0; _message = 'err_req_rate'; }
	if( _status && !l_comment ){ _status = 0; _message = 'err_req_comment'; }
	
	if( !_status ){
		gnrl._api_response( res, 0, _message );
	}
	else{

		var _ins = {
			'i_rate'     : i_rate,
			'l_comment'  : l_comment,
			'i_user_id'  : login_id,
			'd_added'    : gnrl._db_datetime(),
			'l_data'     : {}
		};
		dclass._insert( 'tbl_feedback', _ins, function( status, data ){ 
			if( status ){
				gnrl._api_response( res, 1, 'succ_feedback_successfully', {});
			}
			else{
				gnrl._api_response( res, 0, _message );
			}
		});
			
	}
};

module.exports = currentApi;
