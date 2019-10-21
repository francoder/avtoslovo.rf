<?php
use Phalcon\Cli\Task;

class ImportTask extends Task
{
	public function mainAction()
	{
		echo "Start importing..." . PHP_EOL;
		
		$suppliers = $this->catalog->getSuppliers();
		
		if( count($suppliers) )
		{
			foreach( $suppliers AS $supplierKey => $supplierTitle )
			{
				$import = $this->catalog->getImport($supplierKey);
				
				echo "Start importing \"" . ucfirst($supplierKey) . "\"" . PHP_EOL;
				
				if( $import )
					$import->start();
				else
					echo "Import \"" . ucfirst($supplierKey) . "\" unaviable." . PHP_EOL;
				
				echo PHP_EOL;
			}
			
			echo "Import Complete" . PHP_EOL;
		}
		else
		{
			echo "\rNothing to import".PHP_EOL;
		}
		// for( $percent = 0; $percent <= 100; $percent++ )
		// {
		// 	echo $percent;
		// 	sleep(1);
		// 	// Print one or more backspaces, erasing current character(s)
		// 	echo str_repeat("\x08", strlen($percent));
		// }
	}
}