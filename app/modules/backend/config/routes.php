<?php
use Phalcon\Mvc\Router;

$router = new Router(false);

$router->setDefaultModule(MODULE);

$router->setDefaultController("index");
$router->setDefaultAction("index");

$router->removeExtraSlashes(false);

$router->add("/", array(
	"controller"	=> "index",
	"action"		=> "index",
));

$router->add("/ajax", array(
	"controller"	=> "ajax",
	"action"		=> "ajax",
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
$router->add("/catalog/suppliers", array(
	"controller"	=> "catalog",
	"action"		=> "suppliers",
));
$router->add("/catalog/parts", array(
	"controller"	=> "catalog",
	"action"		=> "parts",
));
$router->add("/catalog/local", array(
	"controller"	=> "catalog",
	"action"		=> "local",
));

# Orders
$router->add("/orders", array(
	"controller" => "orders",
	"action" => "list",
));
$router->add("/orders/{id}", array(
	"controller" => "orders",
	"action" => "show",
))->setName("order");
# Content
$router->add("/content/banners", array(
    "controller" => "content",
    "action" => "banner",
));
$router->add("/content/slider", array(
    "controller" => "content",
    "action" => "slider",
));

# Feedback
$router->add("/feedback", array(
	"controller" => "feedback",
	"action" => "list",
));

# Settings
$router->add("/settings/users", array(
	"controller"	=> "users",
	"action"		=> "list",
));
$router->add("/settings/users/{id:[0-9]+}", array(
	"controller"	=> "users",
	"action"		=> "show",
))->setName("user");

$router->add("/settings/usersgroups", array(
	"controller"	=> "usersgroups",
	"action"		=> "list",
));

$router->add("/logout", array(
	"controller"	=> "session",
	"action"		=> "logout",
));

$router->notFound(array(
	"controller" => "error",
	"action" => "notFound"
));

return $router;