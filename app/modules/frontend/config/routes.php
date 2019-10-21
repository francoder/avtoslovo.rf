<?php
use Phalcon\Mvc\Router;

$router = new Router(false);

$router->setDefaultModule(MODULE);

$router->setDefaultController("index");
$router->setDefaultAction("index");

$router->removeExtraSlashes(false);

# Index
$router->add("/", array(
	"controller" => "index",
	"action" => "index",
));
$router->add("/ajax", array(
	"controller" => "ajax",
	"action" => "ajax",
));
$router->add("/cart", array(
	"controller" => "index",
	"action" => "cart",
));
$router->add("/cart/order", array(
	"controller" => "index",
	"action" => "cartOrder",
));
$router->add("/cart/createOrder", array(
    "controller" => "index",
    "action" => "createOrder",
));

#Payment status

$router->add("/payment-success", array(
    "controller" => "index",
    "action" => "paymentSuccess",
));

$router->add("/payment-failure", array(
    "controller" => "index",
    "action" => "paymentFailure",
));

# Catalog
$router->add("/catalog", array(
	"controller"	=> "catalog",
	"action"		=> "index",
));
$router->add("/catalog/ajax", array(
	"controller"	=> "catalog",
	"action"		=> "ajax",
));
$router->add("/catalog/parts", array(
	"controller"	=> "catalog",
	"action"		=> "parts",
));

# Actions
$router->add("/actions", array(
	"controller" => "actions",
	"action" => "list",
));

# Pages
$router->add("/delivery", array(
	"controller" => "pages",
	"action" => "delivery",
));
$router->add("/howtobuy", array(
	"controller" => "pages",
	"action" => "howtobuy",
));
$router->add("/contacts", array(
	"controller" => "pages",
	"action" => "contacts",
));

# Users
$router->add("/register", array(
	"controller" => "user",
	"action" => "register",
));
$router->add("/forgot", array(
	"controller" => "user",
	"action" => "forgot",
));
$router->add("/confirmemail/{code}", array(
	"controller" => "user",
	"action" => "confirmEmail"
));
$router->add("/resetpassword/{code}", array(
	"controller" => "user",
	"action" => "resetpassword"
));
$router->add("/profile", array(
	"controller" => "user",
	"action" => "profile"
));
$router->add("/profile/balance", array(
	"controller" => "user",
	"action" => "balance"
));
$router->add("/profile/auto", array(
	"controller" => "user",
	"action" => "auto"
));
$router->add("/profile/orders", array(
	"controller" => "user",
	"action" => "orders"
));
$router->add("/profile/orders/{id}", array(
	"controller" => "user",
	"action" => "order"
))->setName("order");
$router->add("/profile/settings", array(
	"controller" => "user",
	"action" => "settings"
));

# Session
$router->add("/login", array(
	"controller" => "session",
	"action" => "login",
));
$router->add("/logout", array(
	"controller" => "session",
	"action" => "logout",
));

$router->notFound(array(
	"controller" => "error",
	"action" => "notFound"
));

return $router;