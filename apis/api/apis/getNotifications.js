var express 	= require('express');
var validator 	= require('validator');
var md5 		= require('md5');



var currentApi = function( req, res, next ){
	
	var classes = req.app.get('classes');
	classes.gnrl._extract( classes, this ); // Extract Classes
	var _p = gnrl._p;
	
	var params = gnrl._frm_data( req );
	var _lang = gnrl._getLang( params );
	
	var _status = 1;
	var _message = '';
	var _response = {};
	
	var login_id = gnrl._is_undf( params.login_id ).trim();
	var v_type = gnrl._is_undf( params.v_type, 'pending' ).trim();
	
	if( !_status ){
		gnrl._api_response( res, 0, _message );
	}
	else {
		var wh = " AND i_user_id = '"+login_id+"' ";
		if( v_type == 'pending' ){
			wh += " AND l_data->>'i_view' = '0' ";
		}
		else if( v_type == 'viewed' ){
			wh += " AND l_data->>'i_view' = '1' ";
		}
		wh += " ORDER BY d_added ";
		
		dclass._select( "*", 'tbl_track_push_notification', wh, function( status, data ){
			if( !status ){
				gnrl._api_response( res, 0, 'error', {} );
			}
			else if( !data.length ){
				gnrl._api_response( res, 0, 'err_no_records', _response );
			}
			else{
				for( var i = 0; i < data.length; i++ ){
					data[i].l_data.title = data[i].l_data.title[_lang];
					data[i].l_data.content = data[i].l_data.content[_lang];
				}
				gnrl._api_response( res, 1, '', data );
			}
		});
	}
};

module.exports = currentApi;
