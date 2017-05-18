var express 		= require('express');
var path 			= require('path');
var favicon 		= require('serve-favicon');
var logger 			= require('morgan');
var cookieParser 	= require('cookie-parser');
var bodyParser 		= require('body-parser');
var validator		= require('validator');
var app 			= express();

/*
var server = require("http").Server(app);
var io = require("socket.io")(server);
var handleClient = function (socket) {
    // we've got a client connection
    socket.emit("tweet", {user: "nodesource", text: "Hello, world!"});
};
io.on("connection", handleClient);
*/


app.all('/api/*', function( req, res, next ){
	//console.log( req.query );
    res.header("Access-Control-Allow-Origin", "*"); // restrict it to the required domain
	res.header('Access-Control-Allow-Methods', 'GET,PUT,POST,DELETE,OPTIONS');
    res.header('Access-Control-Allow-Headers', 'Content-type,Accept,App-Id,Password');
    // res.header("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept");
	next();
});


// view engine setup
//app.set('views', path.join(__dirname, 'views'));
//app.set('view engine', 'jade');
//app.set('view engine', 'html');

// uncomment after placing your favicon in /public
// app.use( favicon( path.join(__dirname, 'public', 'favicon.ico') ) );

app.use(logger('dev'));
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: false }));
app.use(cookieParser());
app.use(express.static(path.join(__dirname, 'public')));

// Set Up All Classes
var classes = require('./classes/index.js');
app.set( 'classes', classes );


var api = require('./api/index.js')( app ); // API Route

// catch 404 and forward to error handler
app.use( function( req, res, next ){
	var err = new Error('Not Found');
	err.status = 404;
	next( err );
	
});

// error handler
app.use( function( err, req, res, next ){
	// set locals, only providing error in development
	res.locals.message = err.message;
	res.locals.error = req.app.get('env') === 'development' ? err : {};
	// render the error page
	res.status(err.status || 500);
	res.render('error');
});

module.exports = app;
