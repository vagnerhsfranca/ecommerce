<?php

use Hcode\Page\Page;
use Hcode\Model\User;

$app->get('/forgot', function() {

	$page = new Page([
		"header" => false,
		"footer" => false
	]);

	$page->setTpl("forgot");

});

$app->post('/forgot', function() {

	$user = User::getForgotEmail($_POST["email"]);

	header("Location: /forgot/sent");
	exit;
});

$app->get("/forgot/sent", function(){

	$page = new Page([
		"header" => false,
		"footer" => false
	]);

	$page->setTpl(("forgot-sent"));
});

$app->get("/forgot/reset", function(){

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new Page([
		"header" => false,
		"footer" => false
	]);

	$page->setTpl(("forgot-reset"),
	array(
		"name" => $user["desperson"],
		"code" => $_GET["code"]
	));
});

$app->post("/forgot/reset", function(){

	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setFogotUsed($forgot["idrecovery"]);

	$user = new User();

	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost" => 12
	]);

	$user->findById((int)$forgot["iduser"]);

	$user->setPassword($password);

	$page = new Page([
		"header" => false,
		"footer" => false
	]);

	$page->setTpl(("forgot-reset-success"));
});

?>