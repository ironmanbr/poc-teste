<?php

require_once 'CrudBaseAbstract.php';

class Product extends CrudBaseAbstract
{
    protected $table = 'products';

    protected $fillable = [
        'name', 'amount', 'description', 'image'
    ];
}