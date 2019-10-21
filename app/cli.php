<?php
define("ROOT_DIR", dirname(dirname(__FILE__)));

use Molly\Catalog,
	Phalcon\Loader,
	Phalcon\CLI\Console as ConsoleApp,
	Phalcon\DI\FactoryDefault\CLI as CliDI,
	Phalcon\Db\Adapter\Pdo\Mysql AS DbAdapter;

$di = new CliDI();

$loader = new Loader();
$loader->registerDirs(array(
	ROOT_DIR . "/app/modules/cli/tasks",
));
$loader->registerNamespaces(array(
	"Molly" => ROOT_DIR . "/app/library/",
	"Molly\Models" => ROOT_DIR . "/app/models/",
));
$loader->registerClasses(array(
    "PHPExcel_IOFactory" => ROOT_DIR . "/vendor/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php",
));
$loader->register();

$di->set("db", function(){
	$databaseConfig = require ROOT_DIR."/app/config/database.php";
	$connection = new DbAdapter(array(
		"host" => $databaseConfig->hostname,
		"username" => $databaseConfig->username,
		"password" => $databaseConfig->password,
		"dbname" => $databaseConfig->database,
		"charset" => "utf8",
		"options" => array(
			\PDO::MYSQL_ATTR_INIT_COMMAND => "SET LOCAL time_zone='".date("P")."'",
		),
	));
	
	return $connection;
});

$di->set("catalog", function(){
	return new Catalog();
});

$di->get('ImportGmailTask');

$console = new ConsoleApp();
$console->setDI($di);

$arguments = array();

foreach( $argv as $k => $arg )
{
	if( $k == 1 )
		$arguments["task"] = $arg;
	elseif( $k == 2 )
		$arguments["action"] = $arg;
	elseif( $k >= 3 )
		$arguments["params"][] = $arg;
}

define("CURRENT_TASK",   (isset($argv[1]) ? $argv[1] : null));
define("CURRENT_ACTION", (isset($argv[2]) ? $argv[2] : null));

try {
	$console->handle($arguments);
} catch( \Phalcon\Exception $e ) {
	echo $e->getMessage();
	exit(255);
}