<?php

use Hcode\Page\Page;
use Hcode\Model\User;

$app->get('/login', function() {

	$page = new Page();

	$page->setTpl("login", [
		"error" => User::getMsgErro(),
		"errorRegister"=> User::getRegisterError(),
		"registerValues" => isset($_SESSION["registerValues"]) ? $_SESSION["registerValues"] : 
			[
				"desperson" => "",
				"desemail" => "",
				"nrphone" => ""
			]
	]);

	exit;
});

$app->post('/login', function() {
    
	try{

		User::login($_POST["deslogin"], $_POST["despassword"]);

		header("Location: /");

	}catch (Exception $e){
		
		User::setMsgErro($e->getMessage());

		header("Location: /login");
	}

	exit;
});

$app->get('/logout', function() {
    
	User::logout();

	header("Location: /login");

	exit;
});

$app->post('/register', function() {

	$user = new User();

	$error = [];

	if($_POST["desperson"] === '' || !isset($_POST["desperson"])){
		$msg = "Por favor, informe o seu nome!";
		
		array_push($error, $msg);
	}

	if($_POST["desemail"] === '' || !isset($_POST["desemail"])){
		$msg = "Por favor, informe o seu email!";

		array_push($error, $msg);
	}

	if($_POST["despassword"] === '' || !isset($_POST["despassword"])){
		$msg = "Por favor, informe a sua senha!";

		array_push($error, $msg);
	}

	if(User::checkExistLogin($_POST["desemail"])){
		$msg = "O email informado já está em uso!";

		array_push($error, $msg);
	}

	if(count($error) > 0){

		$_SESSION["registerValues"] = $_POST;

		User::setRegisterError($error);
		
		header("Location: /login");
		
		exit;
	}
	
	$user->setData([
		"inadmin" => 0,
		"desperson" => $_POST["desperson"],
		"desemail" => $_POST["desemail"],
		"deslogin" => $_POST["desemail"],
		"despassword" => $_POST["despassword"],
		"nrphone" =>  $_POST["nrphone"] != '' ? $_POST["nrphone"] : NULL 
	]);

	$user->save();
	
	User::login($_POST["desemail"], $_POST["despassword"]);

	exit;
});

?>