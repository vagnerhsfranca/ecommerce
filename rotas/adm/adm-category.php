<?php

use Hcode\Page\PageAdmin;
use Hcode\Model\Category;
use Hcode\Model\Product;
use Hcode\Model\User;

$app->get("/admin/categories/create", function(){

	User::verifyLogin();

	$pageAdmin = new PageAdmin();

	$categories = Category::ListAll();

	$pageAdmin->setTpl("categories-create",[
		"categories" => $categories
	]);
});

$app->post("/admin/categories/create", function(){

	User::verifyLogin();

	$categories = new Category();

	$categories->setData($_POST);

	$categories->save();

	header("Location: /admin/categories");
	exit;
});

$app->get("/admin/categories/:idcategory/delete", function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->findById((int)$idcategory);

	$category->delete();

	header("Location: /admin/categories");
	exit;
});

$app->get("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->findById((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl("categories-update",
	array(
		"category" => $category->getValues()
	));

});
$app->post("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->findById((int)$idcategory);

	$category->setData($_POST);

	$category->save();

	header("Location: /admin/categories");

	exit;
});

$app->get('/admin/categories/:idcategory/products', function($idcategory) {

	User::verifyLogin();

    $category = new Category();

    $category->findById((int)$idcategory);
	
	$pageAdmin = new PageAdmin();

	$pageAdmin->setTpl("categories-products", array(
		"category" => $category->getValues(),
        "productsRelated" => $category->getProducts(),
		"productsNotRelated" => $category->getProducts(false)
	));

});

$app->get('/admin/categories/:idcategory/products/:idproduct/add', function($idcategory, $idproduct) {

	User::verifyLogin();

    $category = new Category();

	$product = new Product();

    $category->findById((int)$idcategory);

	$product->findById((int)$idproduct);

	$category->addProduct($product);
	
	header("Location: /admin/categories/". $idcategory . "/products");
	exit;
});

$app->get('/admin/categories/:idcategory/products/:idproduct/remove', function($idcategory, $idproduct) {

	User::verifyLogin();

    $category = new Category();

	$product = new Product();

    $category->findById((int)$idcategory);

	$product->findById((int)$idproduct);

	$category->removeProduct($product);
	
	header("Location: /admin/categories/". $idcategory . "/products");
	exit;

});

$app->get("/admin/categories", function(){

	User::verifyLogin();

	$search = (isset($_GET["search"])) ? $_GET["search"] : "";

	$page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;

	if($search === ""){
		$pagination = Category::getCategoryPage($page);
	}else{
		$pagination = Category::getCategoryPageSearch($search, $page);
	}

	$pages = [];

	for($i = 0; $i < $pagination["pages"]; $i++){
		array_push($pages, 
		[
			"href" => "/admin/categories?" . http_build_query([
				"page" => $i + 1,
				"search" => $search
			]),
			"text" => $i + 1
		]);
	}

	$pageAdmin = new PageAdmin();

	$pageAdmin->setTpl("categories",
	[
		"categories" => $pagination["data"],		
		"search" => $search,
		"pages" => $pages
	]);
});
?>