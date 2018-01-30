var express = require('express');

// IS LIVE

if (0) {
    var _cnf = {
        _live: 1,
        _lang: 'en',
        _cloudinary_image_url: 'https://res.cloudinary.com/goyo/image/upload/',
        _site_url: 'http://35.154.230.244:8081/',
        _port: 8081,

        _db_conn: {
            host: 'localhost',
            database: 'goyo_app',
            user: 'postgres',
            password: 'sa@123',
            port: 5432,
        },

        _db_conn_str: 'postgres://postgres:sa@123@localhost:5432/goyo_app',
    };
}

// Local
else {
    var _cnf = {
        _live: 0,
        _lang: 'en',
        _cloudinary_image_url: 'https://res.cloudinary.com/goyo/image/upload/',
        _site_url: 'http://192.168.0.222:3000/',
        _port: 3000,

        _db_conn: {
            host: 'localhost',
            database: 'db_goyo',
            user: 'postgres',
            password: 'admin',
            port: 5432,
        },

        _db_conn_str: 'postgres://postgres:admin@localhost:5432/db_goyo',
    };
}

// Live Mode

if (0) {
    var _cnf = {
        _live: 1,
        _lang: 'en',
        _cloudinary_image_url: 'https://res.cloudinary.com/goyo/image/upload/',
        _site_url: 'http://35.154.230.244:8081/',
        _port: 8081,

        _db_conn: {
            host: '35.154.230.244',
            database: 'goyo_app',
            user: 'postgres',
            password: 'sa@123',
            port: 5432,
        },

        _db_conn_str: 'postgres://postgres:sa@123@35.154.230.244:5432/goyo_app',
    };
}

module.exports = _cnf;