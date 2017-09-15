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
	
	var _status = 1;
	var _message = '';
	var _response = {};
	
	var txnid = gnrl._is_undf( params.txnid );
	var amount = gnrl._is_undf( params.amount );
	var productinfo = gnrl._is_undf( params.productinfo );
	var firstname = gnrl._is_undf( params.firstname );
	var email = gnrl._is_undf( params.email );
	var user_credentials = gnrl._is_undf( params.user_credentials );
	
	if( !txnid.trim() ){ _status = 0; _message = 'err_req_transaction_id'; }
	else if( !amount.trim() ){ _status = 0; _message = 'err_req_amount'; }
	else if( !productinfo.trim() ){ _status = 0; _message = 'err_req_productinfo'; }
	else if( !firstname.trim() ){ _status = 0; _message = 'err_req_name'; }
	else if( !email.trim() ){ _status = 0; _message = 'err_req_email'; }
	else if( !user_credentials.trim() ){ _status = 0; _message = 'err_req_user_credentials'; }
	
	if( !_status ){
		gnrl._api_response( res, 0, _message, {} );
	}
	else{
		async.series([
			function( callback ){
				PayuBiz.getHashes({
					txnid : txnid,
					amount : amount,
					productinfo : productinfo,
					firstname : firstname,
					email : email,
					user_credentials : user_credentials,
					udf1 : '',
					udf2 : '',
					udf3 : '',
					udf4 : '',
					udf5 : '',
					offerKey : '',
					cardBin : '',
				} , function( data ){
					_response = data;
					callback( null );
				})
			},
		],
		function( error, results ){
			gnrl._api_response( res, 1, '', _response );
		});
	}
	
	
	
	
	
};

module.exports = currentApi;
