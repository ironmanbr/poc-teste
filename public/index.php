<?php

require_once __DIR__ . '/../plugins/Template.php';

$app = @$_REQUEST['app'] ?: '';
$act = @$_REQUEST['act'] ?: '';


switch ($app) {
    case 'order':
    case 'products':
    case 'customers':
    default:
        require_once __DIR__ . '/../controllers/CustomersController.php';
        $app = new CustomersController($_REQUEST);
        break;
}



$mainTpl = new Template('main.html');
$mainTpl->content = $app->run($act)->view();
echo $mainTpl->render();
