<?php

use Hcode\Page\PageAdmin;
use Hcode\Model\User;

if(session_status() !== PHP_SESSION_ACTIVE)
{
    session_start();
}

$app->get('/admin', function() {

User::verifyLogin();

$pageAdmin = new PageAdmin();

$pageAdmin->setTpl("index");

});
?>