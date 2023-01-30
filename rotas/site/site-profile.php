<?php

use Hcode\Page\Page;
use Hcode\Model\User;

$app->get('/profile', function() {
    
    User::verifyLogin(false);

    $user = User::userInSession();
	
	$page = new Page();

	$page->setTpl("profile",[
		"profileError" => User::getMsgErro(),
        "profileMsg" => "",
        "user" => $user->getValues()
	]);

});

$app->post('/profile', function() {
    
    User::verifyLogin(false);
    
	$error = [];

    if($_POST["desperson"] === '' || !isset($_POST["desperson"])){
		$msg = "Por favor, informe o seu nome!";
		
		array_push($error, $msg);
	}

	if($_POST["desemail"] === '' || !isset($_POST["desemail"])){
		$msg = "Por favor, informe o seu email!";

		array_push($error, $msg);
	}

    $user = User::userInSession();
    
    if($_POST["desemail"] !==  $user->getdesemail()){

        if(User::checkExistLogin($_POST["desemail"])){
            $msg = "O email informado já está em uso!";
            array_push($error, $msg);
        }
	}

    if(count($error) > 0){

        User::setMsgErro($error);

        header("Location: /profile");

        exit;
    }
	
    $_POST["inadmin"] = $user->getinadmin();
    $_POST["despassword"] = $user->getdespassword();
    $_POST["deslogin"] = $user->getdeslogin ();

	$user->setData($_POST);

    $user->update();

    header("Location: /profile");

    exit;

});

$app->get('/profile/change-password', function() {

    User::verifyLogin(false);

	$page = new Page();

	$page->setTpl("profile-change-password", [
		"changePassSuccess" => User::getMsgSucess(), 
        "changePassError" => User::getMsgErro() 
	]);

	exit;
});

$app->post('/profile/change-password', function() {

    User::verifyLogin(false);

    if(!isset($_POST["current_pass"]) || $_POST["current_pass"] === "" ){
        User::setMsgErro("Por favor, informe corretamente a senha atual!");
        header("Location: /profile/change-password");
        exit;
    }

    if(!isset($_POST["new_pass"]) || $_POST["new_pass"] === "" 
    || !isset($_POST["new_pass_confirm"]) || $_POST["new_pass_confirm"] === ""
    || $_POST["new_pass"] !== $_POST["new_pass_confirm"]){
        User::setMsgErro("A nova senha informada não coincide");
        header("Location: /profile/change-password");
        exit;
    }

    if($_POST["current_pass"] === $_POST["new_pass"]){
        User::setMsgErro("Senha atual não pode ser igual a anterior!");
        header("Location: /profile/change-password");
        exit;
    }

    $user = new User();

    $user = User::userInSession();

    if(!password_verify($_POST["current_pass"], $user->getdespassword())){
        User::setMsgErro("A senha informada é inválida!");
        header("Location: /profile/change-password");
        exit;
    }

    $user->setdespassword($_POST["new_pass"]);

    $user->update();

    User::setMsgSucess("Senha alterada com sucesso!");

    User::login($user->getdeslogin(), $_POST["new_pass"]);
    
    header("Location: /profile/change-password");

    exit;
});

?>