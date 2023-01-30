<?php

require_once("rotas/functions.php");

$rotaAdm = "rotas/adm/";

require_once($rotaAdm . "adm.php");
require_once($rotaAdm . "adm-user.php");
require_once($rotaAdm . "adm-forgot.php");
require_once($rotaAdm . "adm-login.php");
require_once($rotaAdm . "adm-category.php");
require_once($rotaAdm . "adm-product.php");
require_once($rotaAdm . "adm-order.php");

$rotaSite = "rotas/site/";

require_once($rotaSite . "site.php");
require_once($rotaSite  . "site-cart.php");
require_once($rotaSite  . "site-category.php");
require_once($rotaSite  . "site-login.php");
require_once($rotaSite  . "site-product.php");
require_once($rotaSite  . "site-forgot.php");
require_once($rotaSite  . "site-profile.php");
require_once($rotaSite  . "site-checkout.php");
require_once($rotaSite  . "site-order.php");
require_once($rotaSite  . "site-payment.php");

?>