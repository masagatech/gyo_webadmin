var express = require('express');
var async = require('async');


var currClass = function( params ){
	
	// Extract Variables
	params.gnrl._extract( params, this );
	
	var table = 'tbl_user';
	
	return {
		
		get : function( param, cb ){
			var _self = this;
			dclass._select( '*', table, " AND id = '"+param+"' ", function( status, data ){
				data = _self.set_lang( status, data );
				cb( status, data );
			});
		},
		
		getByEmail : function( param, cb ){
			var _self = this;
			dclass._select( '*', table, " AND v_email = '"+param+"' ", function( status, data ){
				data = _self.set_lang( status, data );
				cb( status, data );
			});
		},
		
		getByPhone : function( param, cb ){
			var _self = this;
			dclass._select( '*', table, " AND v_phone = '"+param+"' ", function( status, data ){
				data = _self.set_lang( status, data );
				cb( status, data );
			});
		},
		
		getByUsername : function( param, cb ){
			var _self = this;
			param = param.toLowerCase( param );
			dclass._select( '*', table, " AND v_role = 'user' AND ( LOWER( v_email ) = '"+param+"' OR LOWER( v_phone ) = '"+param+"' ) ", function( status, data ){
				data = _self.set_lang( status, data );
				cb( status, data );
			});
		},
		
		
		
		lang : function( data ){
			var _self = this;
			return gnrl._getLang( data.l_data ? data.l_data : {} );
		},
		
		set_lang : function( status, data ){
			var _self = this;
			if( status && data.length ){
				for( var k in data ){
					if( data[k].l_data ){
						data[k].l_data.lang = _self.lang( data[k] );
					}
					else{
						data[k].l_data = {
							lang : _self.lang( data[k] )
						};
					}
				}
			}
			return data;
		},
		
		
	}
};

module.exports = currClass;
