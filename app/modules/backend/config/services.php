<?php
$di->set("router", function(){
	return require "routes.php";
});