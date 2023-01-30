<?php

use Hcode\Page\PageAdmin;
use Hcode\Model\Order;
use Hcode\Model\User;
use Hcode\Model\Cart;
use Hcode\Model\OrderStatus;

$app->get('/admin/orders/:idorder/delete', function($idorder) {

    User::verifyLogin();
    
    $pageAdmin = new PageAdmin();
    
    $order = new Order();
    
    $order->getById((int)$idorder);
    
    $order->delete();
    
    header("Location: /admin/orders");
    
    exit;
    
});

$app->get('/admin/orders/:idorder/status', function($idorder) {

    User::verifyLogin();
    
    $pageAdmin = new PageAdmin();
    
    $order = new Order();
    
    $order->getById((int)$idorder);
    
    $pageAdmin->setTpl("order-status",
    [
        "order" => $order->getValues(),
        "status" => OrderStatus::listAll(),
        "msgError" => Order::getMsgError(),
        "msgSuccess" => Order::getMsgSucess()
    ]);
    
});

$app->post('/admin/orders/:idorder/status', function($idorder) {

    User::verifyLogin();
    
    $order = new Order();

    $order->getById((int)$idorder);

    if(!isset($_POST["idstatus"]) || $_POST["idstatus"] === ""){
        
        Order::setMsgError("Falha ao tentar alterar o status do pedido " . $idorder . "!");

        header("Location: /admin/orders/" . $idorder . "/status");

        exit;
    }

    $order->setidstatus((int)$_POST["idstatus"]);

    $order->save();

    Order::setMsgSucess("Status alterado com sucesso!");
    
    header("Location: /admin/orders/" . $idorder . "/status");

    exit;
    
});

$app->get('/admin/orders/:idorder', function($idorder) {

    User::verifyLogin();
    
    $pageAdmin = new PageAdmin();
    
    $order = new Order();
    
    $order->getById((int)$idorder);

    $cart = new Cart();

    $cart->findById((int)$order->getidcart());
    
    $pageAdmin->setTpl("order",
    [
        "order" => $order->getValues(),
        "cart" => $cart->getValues(),
        "products" => $cart->getProducts()
    ]);
    
});

$app->get('/admin/orders', function() {

    User::verifyLogin();

    $search = (isset($_GET["search"])) ? $_GET["search"] : "";

	$page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;

	if($search === ""){
		$pagination = Order::getOrderPage($page);
	}else{
		$pagination = Order::getOrderPageSearch($search, $page);
	}

	$pages = [];

	for($i = 0; $i < $pagination["pages"]; $i++){
		array_push($pages, 
		[
			"href" => "/admin/orders?" . http_build_query([
				"page" => $i + 1,
				"search" => $search
			]),
			"text" => $i + 1
		]);
	}

	$pageAdmin = new PageAdmin();

    $pageAdmin->setTpl("orders",
    [
        "orders" => $pagination["data"],
        "search" => $search,
        "pages" =>  $pages
    ]);

});

?>