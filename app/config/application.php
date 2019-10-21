<?php
return new \Phalcon\Config(array(
	"title" => "АвтоСлово | Интернет магазин авто запчастей",
	"domain" => "автослово.рф",
	"system" => array(
		"debug" => true,
		# Dirs
		"libraryDir" => ROOT_DIR . "/app/library/",
		"modelsDir" => ROOT_DIR . "/app/models/",
		"phalconDir" => ROOT_DIR . "/app/system/",
	),
	"mail" => array(
		"from" => "noreply@xn--80aea9aobbuh.xn--p1ai",
		"name" => "АвтоСлово.рф",
		"viewsDir" => ROOT_DIR . "/app/mail/",
	),
	"users" => array(
		"defaultGroup" => 2,
		"emailRequired" => false,
	),
	"placesapi" => "AIzaSyAkg-clZT_7XlJdiAjOEW5AIVo3xgPAv-o",
));