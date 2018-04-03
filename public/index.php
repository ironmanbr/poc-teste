<?php

require_once '../data/Customer.php';
require_once '../data/Product.php';

echo '<pre>';

// $customer = new Customer();

// $customer->create([
//     'name' => 'Graciele',
//     'email' => 'xika@gmail.com'
// ]);

// var_dump($customer);

// print_r($customer->all());

$p = new Product();

// $p->create([
//     'name' => 'Caneta',
//     'amount' => '1.00',
//     'description' => 'Serve para escrever'
// ]);


$p->read(1);
$p->update([
    'name' => 'Super caneta'
]);

print_r($p);


print_r($p->read(1));