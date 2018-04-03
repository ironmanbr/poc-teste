<?php

/**
** Alterar dos campos DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD por suas equivalencias
* E alterar do driver se caso não for usar mysql
*/
return [
    'database' => [
        'dsn' => 'mysql:host=DB_HOST;dbname=DB_NAME',
        'username' => 'DB_USERNAME',
        'password' => 'DB_PASSWORD'
    ]
];