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
	
	async.series([
		
		function( callback ){
			
			var _selection = " id ";
			_selection += " , v_name ";
			_selection += " , v_type ";
			_selection += " , v_mode ";
			_selection += " , v_image ";
			_selection += " , l_data ";
			
			dclass._select( _selection, "tbl_payment_methods", " AND i_delete = '0' AND e_status = 'active' ORDER BY i_order ", function( status, data ){
				if( !status ){
					gnrl._api_response( res, 0, 'error' );
				}
				else if( !data.length ){
					gnrl._api_response( res, 0, 'err_no_records', [] );
				}
				else{
					for( var k in data ){
						if( data[k].v_image ){ data[k].v_image = gnrl._uploads( 'payment/'+data[k].v_image ); }
						data[k].l_data = data[k].l_data[ data[k].v_mode ];
					}
					gnrl._api_response( res, 1, _message, data );
				}		
			});
		}

	], 
	function( error, results ){
		gnrl._api_response( res, 0, 'err_no_records', [] );
	});
	
	
};

module.exports = currentApi;
