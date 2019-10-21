<?php
namespace Molly\Catalog\Import;

use Phalcon\Mvc\User\Component,
	Molly\Models\CatalogItem,
	Molly\Models\CatalogItemProperty;

class Autotrade extends Component
{
	private $api;
	
	function __construct( $api )
	{
		$this->api = $api;
	}
	
	public function start()
	{
		echo "Starting at ".date("d.m.Y H:i:s")."...".PHP_EOL;
		echo "Reading catalogs...".PHP_EOL;
		$catalogs = $this->api->getCatalogs();
		sleep(0.7);
		
		foreach( $catalogs AS $key => $catalog )
		{
			echo "Process ".($key+1)." catalog of ".count($catalogs)."...".PHP_EOL;
			echo "Reading catalog ".($key+1)." sections...".PHP_EOL;
			$sections = $this->api->getCatalogSections($catalog->id);
			sleep(0.7);
			
			foreach( $sections AS $key => $section )
			{
				echo "Process section ".($key+1)." of ".count($sections)."...".PHP_EOL;
				$subsections = $this->api->getCatalogSubSections($catalog->id, $section->id);
				sleep(0.7);
				
				foreach( $subsections AS $key => $subsection )
				{
					echo "Process items from subsection ".($key+1)." of ".count($subsections)."...".PHP_EOL;
					$items = $this->api->getCatalogItems($catalog->id, $section->id, $subsection->id);
					sleep(0.7);
					
					foreach( $items->items AS $item )
					{
						$catalogItem = CatalogItem::findFirst(array(
							"conditions" => "supplier = 'autotrade' AND externalId = :externalId:",
							"bind" => array(
								"externalId" => $item->article,
							),
						));
						
						if( !$catalogItem )
						{
							$catalogItem = new CatalogItem();
							$catalogItem->assign(array(
								"supplier" => "autotrade",
								"title" => $item->name,
								"externalId" => $item->article,
							));
							$catalogItem->save();
							
							$properties = $this->api->getItemProperties($item->article);
							sleep(0.7);
							
							foreach( $properties->items AS $propertyItem )
							{
								$property = new CatalogItemProperty();
								$property->assign(array(
									"itemId" => $catalogItem->id,
									"key" => $propertyItem->key,
									"value" => $propertyItem->value,
								));
								if( !$property->save() )
									foreach( $property->getMessages() AS $message )
										echo $message->getMessage().PHP_EOL;
							}
						}
					}
					
					echo "Processed ".count($items->items)." items from subsection ".($key+1)." of ".count($subsections)."...".PHP_EOL;
				}
			}
		}
		
		echo "Ended at ".date("d.m.Y H:i:s")."...".PHP_EOL;
	}
}