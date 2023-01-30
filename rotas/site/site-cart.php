<?php 

use Hcode\Page\Page;
use Hcode\Model\Cart;
use Hcode\Model\Product;

$app->get('/cart', function() {
	
	$cart = Cart::getFromSession();

	$page = new Page();

	$page->setTpl("cart", 
	[
		"cart" => $cart->getValues(),
		"products" => $cart->getProducts(),
		"error" =>Cart::getMsgErro()
	]);

});

$app->get('/cart/:idproduct/add', function($idproduct) {
	
	$product = new Product();

	$product->findById((int)$idproduct);
	
	$cart = Cart::getFromSession();

	$quantidade = (isset($_GET['quantidade'])) ? (int)$_GET['quantidade'] : 1;

	for($i = 0; $i < $quantidade; $i++){		
		$cart->addProduct($product);
	}


	header("Location: /cart");
	exit;
});

$app->get('/cart/:idproduct/remove', function($idproduct) {
	
	$product = new Product();

	$product->findById((int)$idproduct);
	
	$cart = Cart::getFromSession();

	$cart->removeProduct($product);

	header("Location: /cart");
	exit;
});

$app->get('/cart/:idproduct/remove/all', function($idproduct) {
	
	$product = new Product();

	$product->findById((int)$idproduct);
	
	$cart = Cart::getFromSession();

	$cart->removeProduct($product, true);

	header("Location: /cart");
	exit;
});

$app->post("/cart/freight", function(){

	$cart = Cart::getFromSession();
	
	$cart->setFreight($_POST["zipcode"]);

	header("Location: /cart");

	exit;
});

?>