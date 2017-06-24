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
			dclass._select( '*', table, " AND ( LOWER( v_email ) = '"+param.toLowerCase()+"' OR v_phone = '"+param+"' ) ", function( status, data ){
				data = _self.set_lang( status, data );
				cb( status, data );
			});
		},
		
		isVerified : function( data ){
			if( !data.l_data ){ 
				return 0;
			}
			else if( !data.l_data.is_otp_verified ){ 
				return 0;
			}
			else if( data.l_data.is_otp_verified != 1 ){ 
				return 0;
			}
			else{
				return 1;
			}
		},
		
		isUser : function( data ){
			return data.v_role == 'user' ? 1 : 0;
		},
		
		isDriver : function( data ){
			return data.v_role == 'driver' ? 1 : 0;
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
		
		startLog : function( user_id, role, type, cb ){
			var _self = this;
			var _ins = {
				'i_user_id' : user_id,
				'v_role' : role,
				'v_type' : type,
				'd_loged_in' : gnrl._db_datetime(),
			};
			dclass._insert( 'tbl_user_log', _ins, function( status, data ){
				cb( status, data );
			});
		},
		
		finishLog : function( user_id, role, type, cb ){
			var _self = this;
			var _q = " UPDATE tbl_user_log ";
			_q += " SET d_loged_out = '"+gnrl._db_datetime()+"' ";
			_q += " WHERE id = ( ";
			_q += " SELECT id FROM tbl_user_log WHERE v_role = '"+role+"' AND v_type = '"+type+"' AND i_user_id = '"+user_id+"' ORDER BY id DESC LIMIT 1 ";
			_q += " ) AND d_loged_out IS NULL ";
			dclass._query( _q, function( status, data ){
				cb( status, data );
			});
		},
		
		getNewReferralCode : function( user_id, cb ){
			var _self = this;
			/*
			_self.get( user_id, function( status, data ){
				cb( data[0].v_phone );
			});*/
			var v_referral_code = gnrl._get_random_key( 8 );
			dclass._select( '*', 'tbl_referral_codes', " AND v_referral_code = '"+v_referral_code+"' ", function( status, data ){
				if( !status ){
					cb( '' );
				}
				else if( !data.length ){
					cb( v_referral_code );
				}
				else{
					_self.getNewReferralCode( cb );
				}
			});
		},
		
		getMyReferralCode : function( user_id, amount, cb ){
			var _self = this;
			var today = gnrl._db_period_time('today');
			dclass._select( '*', 'tbl_referral_codes', " AND i_user_id = '"+user_id+"' AND d_date >= '"+today.start+"' AND d_date <= '"+today.end+"' ", function( status, data ){
				if( !status ){
					cb( '' );
				}
				else if( data.length ){
					cb( data[0].v_referral_code );
				}
				else{
					_self.getNewReferralCode( user_id, function( code ){
						if( code ){
							var _ins = {
								'i_user_id' : user_id,
								'v_referral_code' : code,
								'f_amount' : amount,
								'd_date' : gnrl._db_ymd(),
							};
							dclass._insert( 'tbl_referral_codes', _ins, function( status, data ){
								cb( status ? code : '' );
							});
						}
						else{
							cb( code );
						}
					});
				}
			});
		},
		
		
	}
};

module.exports = currClass;
