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
	
	if( _status ){
		
		var params = {
			_key : 'driver_ride_buzz',
			_role : 'driver',
			_tokens : [
				{ id : 1, lang : 'en', token : 'cnReN2roosE:APA91bFTQZXon_q_RDFfc83ROATkxv_UR5J6yXctH86jslU2x28duQQFINQOQbzsAfp8SgIzwDClUZEAeh36lgbSt-L6xM-Nc7GGI0sXafVBgEj7Fl83yYYu8b0zC-HLlIHKtiQ-Vzjx' },
			],
			_keywords : {
				'[address]' : 'Platinum Plaza'
			},
			_custom_params : {},
			_need_log : 1,
		};
		
		Notification.send( params, function( err, response ){
			gnrl._api_response( res, 1, 'Done', {
				err : err,
				response : response,
			});
		});
		
	}
	else{
		gnrl._api_response( res, 0, _message );
	}
};

module.exports = currentApi;
