<?php

use Hcode\Model\Cart;
use Hcode\Model\Order;
use Hcode\Page\Page;
use Hcode\Model\User;

$app->get('/order/:idorder', function($idorder) {

    User::verifyLogin(false);

	$page = new Page();

    $order = new Order;

    $order->getById((int)$idorder);

	$page->setTpl("payment", [
		"order" => $order->getValues()
	]);

	exit;
});

$app->get('/profile/orders', function() {

    User::verifyLogin(false);

	$page = new Page();

	$user = new User();

	$user = User::userInSession();

	$page->setTpl("profile-orders", [
		"orders" => $user->getOrders()
	]);

	exit;
});

$app->get('/profile/orders/:idorder', function($idorder) {

    User::verifyLogin(false);

	$page = new Page();

	$user = new User();

	$user = User::userInSession();

	$order = new Order();

	$order->getById((int)$idorder);

	$cart = new Cart();

	$cart->findById((int)$order->getidcart());



	$page->setTpl("profile-orders-detail", [
		"order" => $order->getValues(),
		"cart" => $cart->getValues(),
		"products" => $cart->getProducts()
	]);

	exit;
});
?>