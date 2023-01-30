<?php

use Hcode\Page\PageAdmin;
use Hcode\Model\User;

$app->get('/admin/forgot', function() {

	$pageAdmin = new PageAdmin([
		"header" => false,
		"footer" => false
	]);

	$pageAdmin->setTpl("forgot");

});

$app->post('/admin/forgot', function() {

	$user = User::getForgotEmail($_POST["email"]);

	header("Location: /admin/forgot/sent");
	exit;
});

$app->get("/admin/forgot/sent", function(){

	$pageAdmin = new PageAdmin([
		"header" => false,
		"footer" => false
	]);

	$pageAdmin->setTpl(("forgot-sent"));
});

$app->get("/admin/forgot/reset", function(){

	$user = User::validForgotDecrypt($_GET["code"]);

	$pageAdmin = new PageAdmin([
		"header" => false,
		"footer" => false
	]);

	$pageAdmin->setTpl(("forgot-reset"),
	array(
		"name" => $user["desperson"],
		"code" => $_GET["code"]
	));
});

$app->post("/admin/forgot/reset", function(){

	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setFogotUsed($forgot["idrecovery"]);

	$user = new User();

	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost" => 12
	]);

	$user->findById((int)$forgot["iduser"]);

	$user->setPassword($password);

	$pageAdmin = new PageAdmin([
		"header" => false,
		"footer" => false
	]);

	$pageAdmin->setTpl(("forgot-reset-success"));
});

?>