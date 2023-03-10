<?php

use Hcode\Model\User;
use Hcode\Model\Cart;

function formatPrice($vlprice)
{
    if(!$vlprice > 0){
        $vlprice = 0;
    }

    return number_format($vlprice, 2, ",", ".");
}

function checkLogin($inadmin = true)
{
    return User::checkLogin($inadmin);
}

function getUsername()
{

    $user = User::userInSession();

    return $user->getdesperson();
}

function getCartNrQtd()
{
    $cart = new Cart();

    $cart = Cart::getFromSession();

    $totals = $cart->getProductsTotal();

    return $totals["nrqtd"];
}

function getCartVlSubTotal()
{
    $cart = new Cart();

    $cart = Cart::getFromSession();

    $totals = $cart->getProductsTotal();

    return formatPrice($totals["vlprice"]);
}

function formatDate($date)
{
    $date = strtotime($date);

    $date = date('d/m/Y', $date);

    return $date;
}

?>