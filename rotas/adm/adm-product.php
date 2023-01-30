<?php

use Hcode\Model\Product;
use Hcode\Page\PageAdmin;
use Hcode\Model\User;

$app->get("/admin/products", function(){

    User::verifyLogin();

    $search = (isset($_GET["search"])) ? $_GET["search"] : "";

	$page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;

	if($search === ""){
		$pagination = Product::getProductPage($page);
	}else{
		$pagination = Product::getProductPageSearch($search, $page);
	}

	$pages = [];

	for($i = 0; $i < $pagination["pages"]; $i++){
		array_push($pages, 
		[
			"href" => "/admin/products?" . http_build_query([
				"page" => $i + 1,
				"search" => $search
			]),
			"text" => $i + 1
		]);
	}

	$pageAdmin = new PageAdmin();

	$pageAdmin->setTpl("products",
	[
		"products" => $pagination["data"],		
		"search" => $search,
		"pages" => $pages
	]);

});

$app->get("/admin/products/create", function(){

    User::verifyLogin();

    $pageAdmin = new PageAdmin();

    $pageAdmin->setTpl("products-create");
});

$app->post("/admin/products/create", function(){

    User::verifyLogin();

    $product = new Product();

    $product->setData($_POST);

    $product->save();

    header(("Location: /admin/products"));
    exit;
});

$app->get("/admin/products/:idproduct", function($idproduct){

    User::verifyLogin();

    $product = new Product();

    $product->findById((int)$idproduct);

    $pageAdmin = new PageAdmin();

    $pageAdmin->setTpl("products-update",[
        "product" => $product->getValues()
    ]);
});

$app->post("/admin/products/:idproduct", function($idproduct){

    User::verifyLogin();

    $product = new Product();

    $product->findById((int)$idproduct);

    $product->setData($_POST);

    $product->save();

    $product->setPhoto($_FILES["file"]);

    header("Location: /admin/products");
    exit;
});

$app->get("/admin/products/:idproduct/delete", function($idproduct){

    User::verifyLogin();

    $product = new Product();

    $product->findById((int)$idproduct);

    $product->delete();

    header(("Location: /admin/products"));
    exit();
});

?>