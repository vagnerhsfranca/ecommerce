<?php 

use Hcode\Page\Page;
use Hcode\Model\Product;

$app->get('/products/:desurl', function($desurl) {
    
	$product = new Product();

	$product->findByUrl($desurl);
	
	$page = new Page();

	$page->setTpl("product-detail",[
		"product" => $product->getValues(),
		"categories" => $product->getCategories()
	]);

});

?>