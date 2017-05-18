var express = require('express');
var FCM = require('fcm-node');
var nodemailer = require('nodemailer');
var sendmail = require('sendmail')();
var http = require('http');
var async = require('async');

var _config = require('./config.js');
var _lables = require('./lable-translation.js');
var dclass = require('./database.js');

var gnrl = {
	
	_live : _config._live,
	_site_url : _config._site_url,
	_api_base : '/api/',
	
	// Replace undefined variables
	_p : function( lable, data ){ 
		if( data == undefined ) console.log( lable ); 
		else console.log( lable, data ); 
	},
	
	// Extract Variables
	_extract : function( data, extractdata ){ 
		for( var key in data ){
			extractdata[key] = data[key];
		}
	},
	
	// DB Schema
	_db_schema : function( param ){
		return 'public.'+param;
	},
	
	// Get Minus Value
	_minus : function( amount ){
		amount = parseFloat( amount );
		if( amount < 0 ){
			return amount;
		}
		else{
			return ( amount * -1 );
		}
	},
	
	// Get Uplods File Path
	_uploads : function( url ){ 
		if( url == undefined ) { url = '' }; 
		return gnrl._site_url+'uploads/'+url;
	},
	
	// Replace undefined variables
	_is_undf : function( data, deafaultVal = null ){
		if( data != undefined ){
			return data ? data : ( deafaultVal != undefined ? deafaultVal : '' );
		}
		else{
			return ( deafaultVal != undefined ? deafaultVal : '' );
		}
	},
	
	// Generate OTPs
	_api_response : function( res, status, message, data ){
		if( !status && !message ){ message = 'error'; }
		message = gnrl._lbl( message );
		res.json({
			'status' 	: status,
			'message' 	: message,
			'data' 		: data,
		});
	},
	
	// Get Lang
	_getLang : function( params, _lang ){
		var lang = params.lang ? params.lang : _config._lang;
		global._lang = lang = _lables[lang] ? lang : _config._lang;
		return global._lang;
	},
	
	// Get Lable
	_lbl : function( key, lang ){
		var key = gnrl._is_undf( key );
		var lang = global._lang;
		if( _lables[lang][key] ){
			return _lables[lang][key];
		}
		return '';
	},
	
	
	// Is Percentage
	_isPercent : function( amount, val ){ 
		val = ( ''+val ).split('%');
		val[0] = val[0] ? val[0] : 0;
		
		amount = parseFloat( amount );
		val[0] = parseFloat( val[0] );
		if( val.length == 2 ){
			return {
				is_percent : 1,
				value : val[0],
				comm_amount : gnrl._round( ( val[0] * amount / 100 ) ),
				amount : gnrl._round( amount + ( val[0] * amount / 100 ) )
			};
		}
		else{
			return {
				is_percent : 0,
				value : val[0],
				comm_amount : gnrl._round( val[0] ),
				amount : gnrl._round( amount + val[0] )
			};
		}
	},
	
	// Calculate Commision
	_calc_commision : function( amount, val ){ 
		amount = parseFloat( amount );
		val = ( ''+val ).split('%');
		val[0] = parseFloat( val[0] ? val[0] : 0 );
		if( val.length == 2 ){
			return gnrl._round( val[0] * amount / 100 );
		}
		else{
			return gnrl._round( val[0] );
		}
	},
	
	// Check if data is NULL
	_isNull : function( data ){
		//gnrl._p( data );
		//gnrl._p( ' C :  '+ data.constructor );
		//gnrl._p( ' T :  '+ typeof( data ) );
		//gnrl._p( ' A :  '+ Array.isArray( data ) );
		if( data == undefined ){ return 1; }
		else if( data == null ){ return 1; }
		else if( typeof( data ) == 'object' ){ for( var k in data ){ return 0; } return 1; }
		else if( Array.isArray( data ) && !data.length ){ for( var k in data ){ return 0; } return 1; }
		else if( typeof( data ) == 'string' && !data.trim() ){ return 1; }
		else{ return 0; }
	},
	
	_json_encode : function( data ){
		return JSON.stringify( data );
	},
	
	// Form Data
	_frm_data : function( data ){
		return Object.assign( data.body, data.query );
		if( data.method == 'GET' || data.method == 'get' ){
			return data.query;
		}
		else{
			return data.body;
		}
	},
	
	// Remove Files
	_remove_loop_file : function( fs, file_arr ){
		for( var k in file_arr ){
			if( file_arr[k].path ){
				fs.unlink( file_arr[k].path );
			}
		}
	},
	
	// Generate OTPs
	_get_otp : function(){
		return Math.floor( 1000 + Math.random() * 9000 );
	},

	// Generate Random Key
	_get_random_key : function( _text = 6 ){
		var text = "";
	    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	    for( var i = 0; i < _text; i++ ){
	        text += possible.charAt( Math.floor( Math.random() * possible.length ) );
	    }
	    return text;
	},
	
	_db_ymd : function( format = '', DT = '' ){
		
		DT = DT ? DT : new Date();
		
		var	Y = DT.getFullYear();
		var	M = ''+( DT.getMonth() + 1 );
		var	D = ''+( DT.getDate() );
		var	H = ''+( DT.getHours() );
		var	I = ''+( DT.getMinutes() );
		var	S = ''+( DT.getSeconds() );
		if( M.length < 2) M = '0'+M;
		if( D.length < 2) D = '0'+D;
		if( H.length < 2) H = '0'+H;
		if( I.length < 2) I = '0'+I;
		if( S.length < 2) S = '0'+S;
		
		if( format == 'Y-m-d' ){ return Y+'-'+M+'-'+D; }
		else if( format == 'Y-m-d H:i' ){ return Y+'-'+M+'-'+D+' '+H+':'+I; }
		else if( format == 'Y' ){ return Y; }
		else if( format == 'm' ){ return M; }
		else if( format == 'd' ){ return D; }
		else if( format == 'H:i:s' ){ return H+':'+I+':'+S; }
		else if( format == 'H' ){ return H; }
		else if( format == 'i' ){ return I; }
		
		return Y+'-'+M+'-'+D+' '+H+':'+I+':'+S;
	},
	
	// Get Datetime, Y-m-d H:i:s
	_db_period_time : function( period = 'daily' ){
		var dates = {};
		if( period == 'daily' ){
			dates.start = gnrl._db_ymd('Y-m-d')+' 00:00:00';
			dates.end = gnrl._db_ymd('Y-m-d H:i:s');
		}
		else if( period == 'weekly' ){
			var curr = new Date; // get current date
			var first = curr.getDate() - curr.getDay() + 1; // First day is the day of the month - the day of the week
			var last = first + 6; // last day is the first day + 6
			dates.start = gnrl._db_ymd('Y-m-d', new Date(curr.setDate(first)) )+' 00:00:00';
			dates.end = gnrl._db_ymd('Y-m-d H:i:s');
			// dates.end = gnrl._db_ymd('Y-m-d H:i:s', new Date(curr.setDate(last)));
		}
		else if( period == 'monthly' ){
			var date = new Date();
			dates.start = gnrl._db_ymd('Y-m-d', new Date(date.getFullYear(), date.getMonth(), 1) )+' 00:00:00';
			dates.end = gnrl._db_ymd('Y-m-d H:i:s');
		}
		return dates;
	},
	
	// Get Datetime, Y-m-d H:i:s
	_db_datetime : function(){
		return gnrl._db_ymd();
	},

	// Get Datetime, date array
	_db_date : function(){
		
		var DT 		= new Date();
		var	Y = DT.getFullYear();
		var	M = ''+( DT.getMonth() + 1 );
		var	D = ''+( DT.getDate() );
		var	H = ''+( DT.getHours() );
		var	I = ''+( DT.getMinutes() );
		var	S = ''+( DT.getSeconds() );
		
		if( M.length < 2) M = '0'+M;
		if( D.length < 2) D = '0'+D;
		if( H.length < 2) H = '0'+H;
		if( I.length < 2) I = '0'+I;
		if( S.length < 2) S = '0'+S;

		var firstday = new Date(DT.setDate(DT.getDate() - DT.getDay()));
		var FWDT_Y = firstday.getFullYear();
		var	FWDT_M = ''+( firstday.getMonth() + 1 );
		var	FWDT_D = ''+( firstday.getDate() );
		if( FWDT_M.length < 2) FWDT_M = '0'+FWDT_M;
		if( FWDT_D.length < 2) FWDT_D = '0'+FWDT_D;

		var lastday = new Date(DT.setDate(DT.getDate() - DT.getDay()+6));
		var LWDT_Y = lastday.getFullYear();
		var	LWDT_M = ''+( lastday.getMonth() + 1 );
		var	LWDT_D = ''+( lastday.getDate() );
		if( LWDT_M.length < 2) LWDT_M = '0'+LWDT_M;
		if( LWDT_D.length < 2) LWDT_D = '0'+LWDT_D;
			
		var date = {};
		date['Y'] = Y;
		date['M'] = M;
		date['D'] = D;
		date['H'] = H;
		date['I'] = I;
		date['S'] = S;
		
		date['WEEK_FIRST_DATE'] = {};
		date['WEEK_FIRST_DATE']['Y'] = FWDT_Y;
		date['WEEK_FIRST_DATE']['M'] = FWDT_M;
		date['WEEK_FIRST_DATE']['D'] = FWDT_D;
		
		date['WEEK_LAST_DATE'] = {};
		date['WEEK_LAST_DATE']['Y'] = LWDT_Y;
		date['WEEK_LAST_DATE']['M'] = LWDT_M;
		date['WEEK_LAST_DATE']['D'] = LWDT_D;

		return date;
		
	},
	
	_getLangWiseData : function( data, lang, lang_columns ){
		if( lang_columns.length ){
			if( Array.isArray( data ) && data.length ){
				for( var i = 0; i < data.length; i++ ){
					var temp = data[i];
					for( var col = 0; col < lang_columns.length; col++ ){
						var colm = lang_columns[col];
						temp[colm] = temp[colm][lang];
					}
					data[i] = temp;
				}
			}
			else if( typeof( data ) == 'object' && data != null ){
				for( var col = 0; col < lang_columns.length; col++ ){
					var colm = lang_columns[col];
					data[colm] = data[colm][lang];
				}
			}
		}
		return data;
	},
	
	_getLangField : function( data, _lang ){
		var val = '';
		if( typeof( data ) == 'object' && data != null ){
			val = data._lang;
		}
		return val;
	},

	
	
	// Check Is Json Data
	_isJson : function( data ){
		try{ JSON.parse( data ); } catch( e ){ return false; }
		return true;
	},
	
	// Get TimeStamp
	_timestamp : function( datestr ){
		if( datestr == undefined ){ datestr = ''; }
		else if( datestr == null ){ datestr = ''; }
		datestr = datestr ? datestr.toString() : '';
		return gnrl._isNull( datestr ) ? '' : new Date( datestr ).getTime();
	},

	_dateDiff : function(_start, _end){

		var _res = {};
		var date1 = new Date(_start);
		var date2 = new Date(_end);
		var delta = Math.abs(date2 - date1) / 1000;

		// calculate (and subtract) whole days
		_res['days'] = Math.floor(delta / 86400);
		delta -= _res['days'] * 86400;

		// calculate (and subtract) whole hours
		_res['hours'] = Math.floor(delta / 3600) % 24;
		delta -= _res['hours'] * 3600;

		// calculate (and subtract) whole minutes
		_res['minutes'] = Math.floor(delta / 60) % 60;
		delta -= _res['minutes'] * 60;

		// what's left is seconds
		_res['seconds'] = delta % 60;  // in theory the modulus is not required

        return _res;

	},

	_cardValidation : function(number){

		var regex = new RegExp("^[0-9]{16}$");
	    if (!regex.test(number)){
	        return false;
	    }

	    var sum = 0;
	    for (var i = 0; i < number.length; i++) {
	        var intVal = parseInt(number.substr(i, 1));
	        if (i % 2 == 0) {
	            intVal *= 2;
	            if (intVal > 9) {
	                intVal = 1 + (intVal % 10);
	            }
	        }
	        sum += intVal;
	    }
	    return (sum % 10) == 0;

	},
	
	_distQuery : function( lat1, long1, lat2, long2 ){
		
		// lat = "'"+lat+"':double precision", 
		
		var _q = "";
		_q += " round ( float8 ( ";
			_q += " 111.111 * DEGREES( ";
				_q += " ACOS( ";
					_q += " COS( RADIANS( "+lat1+" ) ) ";
					_q += " * COS( RADIANS( "+lat2+" ) ) ";
					_q += " * COS( RADIANS( "+long2+" - "+long1+" ) ) ";
					_q += " + SIN( RADIANS( "+lat1+" ) ) ";
					_q += " * SIN( RADIANS( "+lat2+" ) ) ";
				_q += " ) ";
			_q += " ) ";
		_q += ")::numeric, 2 )";
		return _q;
		
	},
	
	_round : function( amount ){
		amount = parseFloat( amount );
		return Math.round( amount * 100 ) / 100;
	},
	
	_getTransactionTypes : function(type = null){

		var types = {
			'cash' : "Cash",
			'wallet' : "Goyo Wallet",
			'paytm' : "Paytm Wallet",
			'mobikwik' : "Mobi Kwik Wallet",
			'credit_card' : "Credit Card",
			'debit_card' : "Debit Card",
		};

		if( type != null ){
			return types[type];
		}
		return types;

	},
	
	
};

module.exports = gnrl;