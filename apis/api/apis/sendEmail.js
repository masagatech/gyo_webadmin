var express     = require('express');
var validator   = require('validator');
var nodemailer = require('nodemailer');

var currentApi = function( req, res, next ){
    
    var classes = req.app.get('classes');
	classes.gnrl._extract( classes, this ); // Extract Classes
	var _p = gnrl._p;
	
    var params = gnrl._frm_data( req );
	var _lang = gnrl._getLang( params );
	
    var _status = 1;
    var _message = '';
    var _response = {};
    
	var to = 'deven.crestinfotech@gmail.com';
	
	Email.send({
		_to : to,
		_key : 'user_registration',
		_lang : _lang,
		_keywords : {},
	}, function( succ, info ){
		gnrl._api_response( res, 1, 'succ_record_found', {
			succ : succ,
			info : info, 
		});
	});
	
};

module.exports = currentApi;


