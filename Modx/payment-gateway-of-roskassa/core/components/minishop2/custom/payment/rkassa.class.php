<?php

/*

Plugin Name: Payment Gateway for Integration of RosKassa with MiniShop2

Plugin URI: https://Syntlex.Biz/

Description: Payment Gateway for Integration of RosKassa with MiniShop2 - the best Payment Processing 

Version: 1.1

Author: Syntlex Biz

Author URI: https://syntlex.biz 

Copyright: © 2021 Syntlex Biz.

License: GNU General Public License v3.0

License URI: http://www.gnu.org/licenses/gpl-3.0.html

 */

define('MERCHANT_ID', 'Укажите id мерчанта');

define('SECRET_KEY', 'Укажите секретный ключ');


if (!class_exists('msPaymentInterface')) {

	require_once dirname(dirname(dirname(__FILE__))) . '/model/minishop2/mspaymenthandler.class.php';

}



class Rkassa extends msPaymentHandler implements msPaymentInterface {

	public $config;

	public $modx;



	function __construct(xPDOObject $object, $config = array()) {

		$this->modx = & $object->xpdo;

    

		$siteUrl = $this->modx->getOption('site_url');

		$assetsUrl = $this->modx->getOption('minishop2.assets_url', $config, $this->modx->getOption('assets_url').'components/minishop2/');

    

		$this->config = array_merge(array(

			'merchantId' => MERCHANT_ID,

			'SecretId' => SECRET_KEY

		), $config);

	}



	public function send(msOrder $order) {

		$link = $this->getPaymentLink($order);



		return $this->success('', array('redirect' => $link));

	}



	public function getPaymentLink(msOrder $order) {

		$id = $order->get('id');

		$sum = number_format($order->get('cost'), 2, '.', '');



		$sign = array(

			'amount' => $sum,

      'order_id' => $id,

      'shop_id' => $this->config['merchantId'],

      'currency' => 'RUB',

      'test' => 1,

		);

		ksort($sign);

		$str = http_build_query($sign);

    

    $request = array(

      'amount' => $sum,

      'order_id' => $id,

      'shop_id' => $this->config['merchantId'],

      'currency' => 'RUB',

      'test' => 1,

      'sign' => md5($str . $this->config['SecretId'])

    );

    

		$link = 'https://pay.roskassa.net/?'. http_build_query($request);

    

		return $link;

	}



	public function receive(msOrder $order) {

		$data = $_POST;

		unset($data['sign']);

		ksort($data);

		$str = http_build_query($data);

		$sign = md5($str . SECRET_KEY);

    echo 'rkassa';

    if($_POST["sign"] == $sign) {

      $miniShop2 = $this->modx->getService('miniShop2');

			@$this->modx->context->key = 'mgr';

			$miniShop2->changeOrderStatus($order->get('id'), 2);

    } else {

      $this->paymentError('Err: wrong signature.', $sign);

    }

	}



	public function paymentError($text, $request = array()) {

		$this->modx->log(modX::LOG_LEVEL_ERROR,'[miniShop2:Roskassa] ' . $text . ', request: '.print_r($request,1));

		header("HTTP/1.0 400 Bad Request");



		die('ERR: ' . $text);

	}

}