<?php
return new \Phalcon\Config(array(
	array(
		"type" => "item",
		"link" => "",
		"title" => "Обзор",
		"icon" => "i-statistics",
		"controllers" => array(
			array("c" => "index", "a" => "index"),
		),
	),
	array(
		"type" => "group",
		"title" => "Каталог",
		"icon" => "i-docs",
		"menu" => array(
			array(
				"type" => "item",
				"link" => "catalog",
				"title" => "Каталог",
				"controllers" => array(
					array("c" => "catalog", "a" => "index"),
				),
			),
			array(
				"type" => "item",
				"link" => "catalog/suppliers",
				"title" => "Поставщики",
				"controllers" => array(
					array("c" => "catalog", "a" => "suppliers"),
				),
			),
			array(
				"type" => "item",
				"link" => "catalog/parts",
				"title" => "Поиск запчастей",
				"controllers" => array(
					array("c" => "catalog", "a" => "parts"),
				),
			),
			array(
				"type" => "item",
				"link" => "catalog/local",
				"title" => "Прайс лист",
				"controllers" => array(
					array("c" => "catalog", "a" => "local"),
				),
			),
		),
	),
	array(
		"type" => "item",
		"link" => "orders",
		"title" => "Заказы",
		"icon" => "i-list",
		"controllers" => array(
			array("c" => "orders", "a" => "list"),
			array("c" => "orders", "a" => "show"),
		),
	),
	array(
	  "type" => "group",
      "title" => "Контент",
      "icon" => "i-book",
      "menu" => array(
          array(
              "type" => "item",
              "link" => "content/banners",
              "title" => "Баннеры",
              "controllers" => array(
                  array("c" => "content", "a" => "banner"),
              ),
          ),
          array(
               "type" => "item",
               "link" => "content/slider",
               "title" => "Слайдер",
               "controllers" => array(
                   array("c" => "content", "a" => "slider"),
               ),
          ),
      ),
    ),
	array(
		"type" => "item",
		"link" => "feedback",
		"title" => "Обратная связь",
		"icon" => "i-mail",
		"controllers" => array(
			array("c" => "feedback", "a" => "list"),
		),
	),
	array(
		"type" => "group",
		"title" => "Настройки",
		"icon" => "i-stack",
		"menu" => array(
			array(
				"type" => "item",
				"link" => "settings/users",
				"title" => "Пользователи",
				"controllers" => array(
					array("c" => "users", "a" => "list"),
					array("c" => "users", "a" => "show"),
				),
			),
			array(
				"type" => "item",
				"link" => "settings/usersgroups",
				"title" => "Группы пользователей",
				"controllers" => array(
					array("c" => "usersgroups", "a" => "list"),
				),
			),
		),
	),
));