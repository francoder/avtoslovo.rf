<?php
return new \Phalcon\Config(array(
	array(
		"link" => "",
		"title" => "Главная",
		"controllers" => array(
			array("c" => "index", "a" => "index"),
		),
	),
	array(
		"link" => "actions",
		"title" => "Акции",
		"controllers" => array(
			array("c" => "actions", "a" => "list"),
		),
	),
	array(
		"link" => "delivery",
		"title" => "Доставка",
		"controllers" => array(
			array("c" => "pages", "a" => "delivery"),
		),
	),
	array(
		"link" => "howtobuy",
		"title" => "Как купить",
		"controllers" => array(
			array("c" => "pages", "a" => "howtobuy"),
		),
	),
	array(
		"link" => "contacts",
		"title" => "Контакты",
		"controllers" => array(
			array("c" => "pages", "a" => "contacts"),
		),
	),
));