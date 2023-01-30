<?php

use Hcode\Model\Category;
use Hcode\Page\Page;

$app->get('/categories/:idcategory', function($idcategory) {

	$nPage = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;

    $category = new Category();

    $category->findById((int)$idcategory);
	
	$pagination = $category->getProductsPage($nPage);

	$page = new Page();

	$pages = [];

	for($i = 1; $i <= $pagination["pages"]; $i++){
		array_push($pages, [
			"link" => "/categories/" . $category->getidcategory() . "?page=" . $i,
			"page" => $i
		]);
	}

	$page->setTpl("category", array(
		"category" => $category->getValues(),
        "products" => $pagination["data"],
		"pages" => $pages
	));

});
?>