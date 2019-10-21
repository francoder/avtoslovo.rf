<?php
use Phalcon\Loader,
	Phalcon\Mvc\Application,
	Phalcon\DI\FactoryDefault,
	Phalcon\Mailer\Manager AS MailerManager,
	Phalcon\Db\Adapter\Pdo\Mysql AS DbAdapter;

$di = new FactoryDefault();
$di->set("application", function(){
	return require ROOT_DIR."/app/config/application.php";
});
$di->set("module", function(){
	return require MODULE_ROOT_DIR."/config/module.php";
});
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

$loader = new Loader();
$loader->registerNamespaces(array(
	"Phalcon" => $di->getShared("application")->system->phalconDir,
));
$loader->register();

require ROOT_DIR . "/vendor/autoload.php";
require ROOT_DIR . "/app/swiftmailer/lib/swift_required.php";

$di->set("mail", function() use ($di){
	$mailerManager = new MailerManager(array(
		"driver" => "mail",
		"from" => array(
			"email" => $di->getShared("application")->mail->from,
			"name" => $di->getShared("application")->mail->name,
		),
		"viewsDir" => $di->getShared("application")->mail->viewsDir,
	));
	
	return $mailerManager;
});

if( file_exists(ROOT_DIR."/app/modules/".MODULE."/config/services.php") )
	require_once ROOT_DIR."/app/modules/".MODULE."/config/services.php";

try {
	$application = new Application($di);
	$application->registerModules(array(
		MODULE => array(
			"className" => "Molly\\".ucwords(MODULE)."\Module",
			"path" => ROOT_DIR."/app/modules/".MODULE."/Module.php",
		)
	));
	$response = $application->handle();
	$response->send();
} catch (Exception $e) {
	if( $di->getShared("application")->system->debug )
		echo "Exception: ", $e;
	else
		echo "Exception: ", $e->getMessage();
}