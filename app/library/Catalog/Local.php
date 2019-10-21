<?php
namespace Molly\Catalog;

use Phalcon\Mvc\User\Component;

class Local extends Component
{
	public function find( $query, $brand = "" )
	{
		$result = array();
		
		$dateCurrent = new \DateTime();
		
		$itemsQuery = $this->modelsManager->createBuilder()
			->from("Molly\Models\CatalogLocalItem")
			->where("externalId LIKE '%".$query.",%' OR oem LIKE '%".$query.",%'");
		
		if( $brand != "" )
			$itemsQuery->andWhere("brand = :brand:", array(
				"brand" => $brand,
			));
		
		$items = $itemsQuery->getQuery()->execute();

        if(count($items) <= 0) {

            $itemsQuery = $this->modelsManager->createBuilder()
                ->from("Molly\Models\CatalogLocalItem")
                ->where("externalId LIKE '". $query ."' OR oem LIKE '". $query ."'");

            if( $brand != "" )
                $itemsQuery->andWhere("brand = :brand:", array(
                    "brand" => $brand,
                ));

            $items = $itemsQuery->getQuery()->execute();
        }

        if(count($items) <= 0) {

            $itemsQuery = $this->modelsManager->createBuilder()
                ->from("Molly\Models\CatalogLocalItem")
                ->where("externalId LIKE '%".$query.";%' OR oem LIKE '%".$query.";%'");

            if( $brand != "" )
                $itemsQuery->andWhere("brand = :brand:", array(
                    "brand" => $brand,
                ));

            $items = $itemsQuery->getQuery()->execute();
        }

		if(count($items) <= 0) {

            $itemsQuery = $this->modelsManager->createBuilder()
                ->from("Molly\Models\CatalogLocalItem")
                ->where("externalId LIKE '%".str_replace('-', '', $query).",%' OR oem LIKE '%".str_replace('-', '', $query).",%'");

            if( $brand != "" )
                $itemsQuery->andWhere("brand = :brand:", array(
                    "brand" => $brand,
                ));

            $items = $itemsQuery->getQuery()->execute();
        }
		
		foreach( $items AS $item )
		{
			if( !$item->supplierConfig->isActive() )
				continue;
			
			$resultItem = array(
				"code" => "local-" . $item->supplier . "::" . $item->brand . "@" . $item->externalId,
				"article" => $item->oem,
				"brand" => $item->brand,
				"title" => $item->title,
				"photo" => "",
				"price" => $item->price,
				"count" => $item->count,
				"markups" => $item->supplierConfig->getMarkups(),
				"stocks" => array(
					array(
						"id" => $item->supplier."0",
						"code" => "local-" . $item->supplier . "::" . $item->brand . "@" . $item->externalId . "::0",
						"name" => $item->getStockName(),
						"count" => $item->count,
						"price" => $item->price,
						'delivery' => $item->supplierConfig->delivery,
						"rate" => $item->getDeliveryRate(),
						"stat" => json_decode(json_encode($item->getDeliveryStat()), FALSE),
					),
				),
			);
			
			$result[] = $resultItem;
		}
		
		return $result;
	}
	
	public function cart( $items )
	{
		$result = array();
		
		$dateCurrent = new \DateTime();
		
		foreach( $items AS $supplier => $positions )
		{
			$keys = array();
			
			foreach( array_keys($positions) AS $position )
			{
				list($brand, $article) = explode("@", $position);
				
				$keys[] = $article;
			}
			
			$items = $this->modelsManager->createBuilder()
				->from("Molly\Models\CatalogLocalItem")
				->where("supplier = :supplier:", array("supplier" => $supplier))
				->inWhere("externalId", $keys)
				->getQuery()->execute();
			
			foreach( $items AS $item )
			{
				if( !$item->supplierConfig->isActive() )
					continue;
				
				$resultItem = array(
					"code" => "local-" . $item->supplier . "::" . $item->brand . "@" . $item->externalId,
					"article" => $item->oem,
					"brand" => $item->brand,
					"name" => $item->title,
					"photo" => "",
					"price" => $item->price,
					"count" => $item->count,
					"markups" => $item->supplierConfig->getMarkups(),
					"stocks" => array(
						array(
							"id" => 0,
							"code" => "local-" . $item->supplier . "::" . $item->brand . "@" . $item->externalId . "::0",
							"name" => $item->getStockName(),
							"count" => $item->count,
							"price" => $item->price,
							'delivery' => $item->supplierConfig->delivery,
							"rate" => $item->getDeliveryRate(),
							"stat" => json_decode(json_encode($item->getDeliveryStat()), FALSE),
						),
					),
				);
				
				$result[] = $resultItem;
			}
		}
		
		return $result;
	}
}