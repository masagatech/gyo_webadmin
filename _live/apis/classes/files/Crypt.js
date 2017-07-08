var express = require('express');
var nodemailer = require('nodemailer');
var async = require('async');
var CryptoJS = require('crypto-js');

var currClass = function( params ){
	
	// Extract Variables
	params.gnrl._extract( params, this );
	
	return {
		
		secret : 'goyo-pyhoa5oJHc',
		
		encrypt : function( str ){
			var _self = this;
			var ciphertext = CryptoJS.AES.encrypt( str, _self.secret );
			return ciphertext.toString();
		},
		
		decrypt : function( str ){
			var _self = this;
			var bytes = CryptoJS.AES.decrypt( str, _self.secret ).toString( CryptoJS.enc.Utf8 );
			return bytes.toString( CryptoJS.enc.Utf8 );
		},
		
	}
};

module.exports = currClass;
