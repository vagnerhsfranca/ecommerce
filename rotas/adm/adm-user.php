<?php

use Hcode\Model\User;
use Hcode\Page\PageAdmin;

$app->get('/admin/users/:iduser/delete', function($iduser) {

    User::verifyLogin();

	$user = new User();

	$user->findById($iduser);

	$user->delete();

	header("Location: /admin/users");
	exit;
});

$app->get('/admin/users/:iduser/password', function($iduser) {

    User::verifyLogin();

	$user = new User();

	$user->findById($iduser);

	$pageAdmin = new PageAdmin();

	$pageAdmin->setTpl("users-password",[
		"user" => $user->getValues(),
		"msgError" => User::getMsgErro(),
		"msgSuccess" => User::getMsgSucess()
	]);

	exit;
});

$app->post('/admin/users/:iduser/password', function($iduser) {

    User::verifyLogin();
	
	if(!isset($_POST["despassword"]) || $_POST["despassword"] === ""
		|| !isset($_POST["despassword-confirm"]) || $_POST["despassword-confirm"] === ""){
			User::setMsgErro("A nova senha e confirmação não podem ficar em branco!");

			header("Location: /admin/users/" . $iduser . "/password");

			exit;
	}

	if($_POST["despassword"] !== $_POST["despassword-confirm"]){
		User::setMsgErro("A nova senha e confirmação não coincidem!");

		header("Location: /admin/users/" . $iduser . "/password");

		exit;
	}

	$user = new User();

	$user->findById($iduser);

	$user->setPassword(User::getPasswordHash($_POST["despassword"]));

	User::setMsgSucess("Senha do usuário modificada com sucesso!");

	header("Location: /admin/users/" . $iduser . "/password");

	exit;
});

$app->get("/admin/users/create", function () 
{
	User::verifyLogin();

	$pageAdmin = new PageAdmin();

	$pageAdmin->setTpl("users-create");
});

$app->get("/admin/users/:iduser", function ($iduser) 
{
	User::verifyLogin();
	
	$user = new User();

	$user->findById((int)$iduser);

	$pageAdmin = new PageAdmin();

	$pageAdmin->setTpl("users-update", array(
		"user" => $user->getValues()
	));
});

$app->post("/admin/users/create", function () 
{
	User::verifyLogin();

   	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

	$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [

		"cost"=>12
	]);

	$user->setData($_POST);

   	$user->save();

   	header("Location: /admin/users");
	exit;

});

$app->post("/admin/users/:iduser", function ($iduser) 
{
	User::verifyLogin();

   	$user = new User();

	$user->findById((int)$iduser);

	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

	$user->setData($_POST);

   	$user->update();

   	header("Location: /admin/users");
	exit;

});

$app->get('/admin/users', function() {

    User::verifyLogin();

	$search = (isset($_GET["search"])) ? $_GET["search"] : "";

	$page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;

	if($search === ""){
		$pagination = User::getUsersPage($page);
	}else{
		$pagination = User::getUsersPageSearch($search, $page);
	}

	$pages = [];

	for($i = 0; $i < $pagination["pages"]; $i++){
		array_push($pages, 
		[
			"href" => "/admin/users?" . http_build_query([
				"page" => $i + 1,
				"search" => $search
			]),
			"text" => $i + 1
		]);
	}

	$pageAdmin = new PageAdmin();

	$pageAdmin->setTpl("users", array(
		"users" => $pagination["data"],
		"search" => $search,
		"pages" => $pages
	));

});

?>