<?php
use Phalcon\Cli\Task;

class MainTask extends Task
{
	public function mainAction()
	{
		for( $percent = 0; $percent <= 100; $percent++ )
		{
			echo $percent;
			sleep(1);
			// Print one or more backspaces, erasing current character(s)
			echo str_repeat("\x08", strlen($percent));
		}
	}
	
	public function proxyAction()
	{
		$proxys = require ROOT_DIR."/app/config/proxy.php";
		
		echo "Checking proxys".PHP_EOL;
		
		foreach( $proxys AS $key => $proxy )
		{
			$aContext = array(
				"http" => array(
					"proxy" => $proxys[$key],
					"request_fulluri" => true,
				),
			);
			$cxContext = stream_context_create($aContext);
			
			$result = @file_get_contents("https://api2.autotrade.su/?json&data={%22auth_key%22:%222ba8b03ddf75133cd9e0680092252ab9%22,%22method%22:%22getProperties%22,%20%22params%22:{%22article%22:%22AUDIA2%20FD/RH%22}}", false, $cxContext);
			
			echo $proxys[$key].PHP_EOL;
			echo $result.PHP_EOL;
		}
	}
}