<?php
define('MODX_API_MODE', true);
require dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/index.php';

$modx->getService('error','error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('FILE');

$miniShop2 = $modx->getService('minishop2');
$miniShop2->loadCustomClasses('payment');

if (!class_exists('Rkassa')) {exit('Error: could not load payment class "Rkassa".');}
$context = '';
$params = array();

$handler = new Rkassa($modx->newObject('msOrder'));

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  if ($order = $modx->getObject('msOrder', $_POST['order_id'])) {
    $handler->receive($order);
  }

}
die;