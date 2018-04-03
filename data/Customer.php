<?php

require_once 'CrudBaseAbstract.php';

class Customer extends CrudBaseAbstract
{
    protected $table = 'customers';

    protected $fillable = [
        'name', 'email', 'payment_type'
    ];
}