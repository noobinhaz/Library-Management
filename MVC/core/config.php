<?php
namespace core;

class Config
{
    public static $db = [
        'host' => '127.0.0.1',
        'database' => 'library_db',
        'username' => 'root',
        'password' => '',
        'port' => 3306,
    ];

    public static $secretKey = '2A8C3917B6EAFE16C7F31B2F057A8A40DF88F5097A43CF08D52D1A6E4B58FD76';
    public static $issuer = 'Lab2';
}