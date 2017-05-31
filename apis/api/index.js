var express = require('express');
var path 	= require('path');
var router 	= express.Router();

var multer  	= require('multer');
var storage 	= multer.diskStorage({
	destination	: function( req, file, callback ){
		callback( null, __dirname + '/../public/uploads/temp/' );
	},
	filename	: function( req, file, callback ){
		callback( null, Date.now()+'-'+file.originalname );
	}
});
var upload = multer({ storage : storage });

var apiRouter = function( app ){
	
	var classes = app.get('classes');
	classes.gnrl._extract( classes, this ); // Extract Classes
	var _p = gnrl._p;
	
	var _is_login = function( req, res, next ){
		
		var params = gnrl._frm_data( req );
		var _lang = gnrl._getLang( params );
		
		var _status  = 1;
		var _message = '';
		
		var login_id = gnrl._is_undf( params.login_id ).trim();
		var v_token = gnrl._is_undf( params.v_token ).trim();
		
		if( !login_id ){ _status = 0; _message = 'err_req_login_id'; }
		if( _status && !v_token ){ _status = 0; _message = 'err_req_auth_token'; }
		
		if( !_status ){
			gnrl._api_response( res, 0, _message, {} );
		}
		else{
			dclass._select( '*', 'tbl_user', " AND id = '"+login_id+"' ", function( status, data ){
				
				if( !status ){
					gnrl._api_response( res, 0, _message, {} );
				}
				else if( !data.length ){
					gnrl._api_response( res, 0, 'err_msg_no_account', {} );
				}
				else if( gnrl._isNull( data[0].v_token ) ){
					gnrl._api_response( res, 0, 'err_msg_not_logged_in', {} );
				}
				else if( v_token != data[0].v_token ){
					gnrl._api_response( res, 0, 'err_invalid_auth_token', {} );
				}
				else{
					next();
				}
			});
		}
	};
	
	
	
	// Common APIs
	app.all(gnrl._api_base+'getLables', require('./apis/getLables') );
	app.all(gnrl._api_base+'getRideCancelReasons', require('./apis/getRideCancelReasons') );
	app.all(gnrl._api_base+'getAppLanguages', require('./apis/getAppLanguages') );
	app.all(gnrl._api_base+'getBestSarathi', require('./apis/getBestSarathi') );
	app.all(gnrl._api_base+'getCities', require('./apis/getCities') );
	app.all(gnrl._api_base+'getCms', require('./apis/getCms') );
	
	// Driver - User Common APIs
	app.all(gnrl._api_base+'logout', _is_login, require('./apis/logout') );
	app.all(gnrl._api_base+'resendOtp', require('./apis/resendOtp') );
	app.all(gnrl._api_base+'cancelRide', _is_login, require('./apis/cancelRide') );
	app.all(gnrl._api_base+'getNotifications', _is_login, require('./apis/getNotifications') );
	app.all(gnrl._api_base+'getNotificationInfo', _is_login, require('./apis/getNotificationInfo') );
	app.all(gnrl._api_base+'getSettings', require('./apis/getSettings') );
	
	
	// Driver APIs
	app.all(gnrl._api_base+'driverSignUp', require('./apis/driverSignUp') ); // upload.any(), 
	app.all(gnrl._api_base+'driverLogin', require('./apis/driverLogin') );
	app.all(gnrl._api_base+'driverForgotPassword', require('./apis/driverForgotPassword') );
	app.all(gnrl._api_base+'driverResetPassword', require('./apis/driverResetPassword') );
	app.all(gnrl._api_base+'driverDutyStatusGet', _is_login, require('./apis/driverDutyStatusGet') );
	app.all(gnrl._api_base+'driverDutyStatusUpdate', _is_login, require('./apis/driverDutyStatusUpdate') );
	app.all(gnrl._api_base+'driverGetDashboard', _is_login, require('./apis/driverGetDashboard') );
	app.all(gnrl._api_base+'driverProfileGet', require('./apis/driverProfileGet') );
	app.all(gnrl._api_base+'driverProfileUpdate', require('./apis/driverProfileUpdate') );
	app.all(gnrl._api_base+'driverPasswordUpdate', require('./apis/driverPasswordUpdate') );
	app.all(gnrl._api_base+'getDriverRides', _is_login, require('./apis/getDriverRides') );
	app.all(gnrl._api_base+'getDriverWallet', _is_login, require('./apis/getDriverWallet') );
	app.all(gnrl._api_base+'driverLocationUpdate', _is_login, require('./apis/driverLocationUpdate') );
	app.all(gnrl._api_base+'getDriverEarning', _is_login, require('./apis/getDriverEarning') );
	app.all(gnrl._api_base+'ridePayment', _is_login, require('./apis/ridePayment') );
	app.all(gnrl._api_base+'verifyAccount', require('./apis/verifyAccount') );
	
	
	// User APIs
	app.all(gnrl._api_base+'userSignUp', require('./apis/userSignUp') );
	app.all(gnrl._api_base+'userLogin', require('./apis/userLogin') );
	app.all(gnrl._api_base+'userForgotPassword', require('./apis/userForgotPassword') );
	app.all(gnrl._api_base+'userResetPassword', require('./apis/userResetPassword') );
	app.all(gnrl._api_base+'userProfileGet', _is_login, require('./apis/userProfileGet') );
	app.all(gnrl._api_base+'userProfileUpdate', require('./apis/userProfileUpdate') );
	app.all(gnrl._api_base+'userResetPassword', require('./apis/userResetPassword') );
	app.all(gnrl._api_base+'userPasswordUpdate', require('./apis/userPasswordUpdate') );
	app.all(gnrl._api_base+'getUserRides', _is_login, require('./apis/getUserRides') );

	app.all(gnrl._api_base+'rideRate', _is_login, require('./apis/rideRate') );
	app.all(gnrl._api_base+'userFeedback', _is_login, require('./apis/userFeedback') );
	app.all(gnrl._api_base+'getUserWallet', _is_login, require('./apis/getUserWallet') );
	app.all(gnrl._api_base+'getTeriffCard', _is_login, require('./apis/getTeriffCard') );
	app.all(gnrl._api_base+'getReferralCode', _is_login, require('./apis/getReferralCode') );
	app.all(gnrl._api_base+'getDriverLocation', require('./apis/getDriverLocation') );
	app.all(gnrl._api_base+'getPromotionCodes', _is_login, require('./apis/getPromotionCodes') );
	
	app.all(gnrl._api_base+'addMoney', _is_login, require('./apis/addMoney') );
	app.all(gnrl._api_base+'rideSOS', _is_login, require('./apis/rideSOS') );
	
	app.all(gnrl._api_base+'rideSendTrackLink', _is_login, require('./apis/rideSendTrackLink') );
	app.all(gnrl._api_base+'rideTrack', require('./apis/rideTrack') );

	// Other APIs
	app.all(gnrl._api_base+'getVehicleTypeCharge', require('./apis/getVehicleTypeCharge') );
	app.all(gnrl._api_base+'getVehicleTypes', require('./apis/getVehicleTypes') );
	app.all(gnrl._api_base+'getVehiclesList', require('./apis/getVehiclesList') );
	
	app.all(gnrl._api_base+'sendNotification', require('./apis/sendNotification') );
	app.all(gnrl._api_base+'sendEmail', require('./apis/sendEmail') );

	// Ride APIs Customer Side
	app.all(gnrl._api_base+'saveRide', _is_login, require('./apis/saveRide') );
	app.all(gnrl._api_base+'getRide', _is_login, require('./apis/getRide') );
	app.all(gnrl._api_base+'confirmRide', _is_login, require('./apis/confirmRide') );
	app.all(gnrl._api_base+'rideApplyPromotionCode', _is_login, require('./apis/rideApplyPromotionCode') );
	app.all(gnrl._api_base+'rideRemovePromotionCode', _is_login, require('./apis/rideRemovePromotionCode') );
	
	//Ride APIs Driver Side
	//app.all(gnrl._api_base+'receiveBuzz', require('./apis/receiveBuzz') );
	app.all(gnrl._api_base+'buzzAction', _is_login, require('./apis/buzzAction') );
	app.all(gnrl._api_base+'startRide', _is_login, require('./apis/startRide') );
	app.all(gnrl._api_base+'rideChargeTypes', require('./apis/rideChargeTypes') );
	app.all(gnrl._api_base+'rideAddCharge', _is_login, require('./apis/rideAddCharge') );
	app.all(gnrl._api_base+'rideGetCharges', _is_login, require('./apis/rideGetCharges') );
	app.all(gnrl._api_base+'rideComplete', _is_login, require('./apis/rideComplete') );
	app.all(gnrl._api_base+'rideConfirmPayment', _is_login, require('./apis/rideConfirmPayment') );
	
	app.all(gnrl._api_base+'testAPIs', require('./apis/testAPIs') );
	app.all(gnrl._api_base+'userList', require('./apis/userList') );
	
	app.get(gnrl._api_base+'api-list', function( req, res ){
		res.sendFile( path.join( __dirname+'/apis/apiList.html' ) );
	});
	
	app.all( "*", function( req, res ) {
		var resData = {
			'status'  	: 0,
			'message' 	: '404 - Page not found !!!',
			'data'		: {}
		};
		res.json( resData );
	});
}

module.exports = apiRouter;