<?php

use Hcode\Model\User;
use Hcode\Model\Cart;
use Hcode\Model\Order;
use Hcode\Model\Address;
use Hcode\Page\Page;
use Hcode\Model\OrderStatus;
use Hcode\Page\PageAdmin;

$app->get("/checkout", function(){

	User::verifyLogin(false);

	$cart = Cart::getFromSession();

	$address = new Address();

	if(!isset($_GET["zipcode"])){
		$_GET["zipcode"] = $cart->getdeszipcode();
	}

	if(isset($_GET["zipcode"])){
		$address->loadFromCEP($_GET["zipcode"]);
		
		$cart->setdeszipcode($address->getdeszipcode());

		$cart->save();

		$cart->getCalculateTotal();
	}

	if(!$address->getdesaddress())
		$address->setdesaddress("");
	if(!$address->getdesnumber())
		$address->setdesnumber("");
	if(!$address->getdescomplement())
		$address->setdescomplement("");
	if(!$address->getdesdistrict())
		$address->setdesdistrict("");
	if(!$address->getdescity())
		$address->setdescity("");
	if(!$address->getdesstate())
		$address->setdesstate("");
	if(!$address->getdescountry())
		$address->setdescountry("");
	if(!$address->getdeszipcode())
		$address->setdeszipcode("");
	
	$page = new Page();

	$page->setTpl("checkout", [
		"cart" => $cart->getValues(),
		"address" => $address->getValues(),
		"products" => $cart->getProducts(),
		"error" => Address::getMsgError()
	]);
});

$app->post("/checkout", function(){

	User::verifyLogin(false);

	$error = [];

	if($_POST["desaddress"] === '' || !isset($_POST["desaddress"])){
		$msg = "Por favor, informe o endereço!";
		
		array_push($error, $msg);
	}

	if($_POST["descity"] === '' || !isset($_POST["descity"])){
		$msg = "Por favor, informe a cidade!";

		array_push($error, $msg);
	}
	if($_POST["descountry"] === '' || !isset($_POST["descountry"])){
		$msg = "Por favor, informe o país!";

		array_push($error, $msg);
	}
	if($_POST["zipcode"] === '' || !isset($_POST["zipcode"])){
		$msg = "Por favor, informe o CEP!";

		array_push($error, $msg);
	}
	if($_POST["desdistrict"] === '' || !isset($_POST["desdistrict"])){
		$msg = "Por favor, informe o bairro!";

		array_push($error, $msg);
	}

	if(count($error) > 0){

        Address::setMsgError($error);

        header("Location: /checkout");

        exit;
    }

	$user = User::userInSession();

	$address = new Address();

	$_POST["deszipcode"] = $_POST["zipcode"]; 
	$_POST["idperson"] = $user->getidperson();

	$address->setData($_POST);

	$address->save();

	$order = new Order();

	$cart = Cart::getFromSession();

	$cart->getCalculateTotal();

	$order->setData([
		"iduser" => $user->getiduser(),
		"idcart" => $cart->getidcart(),
		"idstatus" => OrderStatus::EM_ABERTO,
		"idaddress" => $address->getidaddress(),
		"vltotal" => $cart->getvltotal()
	]);

	$order->save();


	switch((int)$_POST["payment-method"]){
		case 1:
			header("Location: /order/" . $order->getidorder() . "/pagseguro");
			break;
		
		case 2:
			header("Location: /order/" . $order->getidorder() . "/paypal");
			break;
			
	}

	exit;
});

$app->get("/order/:idorder/pagseguro", function($idorder){

	User::verifyLogin(false);

	$order = new Order();

	$order->getById((int)$idorder);

	$cart = new Cart();

	$cart->findById((int)$order->getidcart());

	$page =  new Page([
		"header" => false,
		"footer" =>false
	]);

	$page->setTpl("payment-pagseguro",[
		"order" => $order->getValues(),
		"cart" => $cart->getValues(),
		"products" => $cart->getProducts(),
		"phone" => [
			"areaCode" => substr($order->getnrphone(), 0, 2),
			"number" => substr($order->getnrphone(), 2, strlen($order->getnrphone()))
		]
	]);
});

$app->get("/order/:idorder/paypal", function($idorder){

	User::verifyLogin(false);

	$order = new Order();

	$order->getById((int)$idorder);

	$cart = new Cart();

	$cart->findById((int)$order->getidcart());

	$page =  new Page([
		"header" => false,
		"footer" =>false
	]);

	$page->setTpl("payment-paypal",[
		"order" => $order->getValues(),
		"cart" => $cart->getValues(),
		"products" => $cart->getProducts(),
	]);
});
?>