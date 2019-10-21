<?php
namespace Molly\Catalog;

use Phalcon\Mvc\User\Component,
	Molly\Models\ApiRequest;

class Autotrade extends Component
{
	CONST API_URL = "https://api2.autotrade.su/?json&data=";
	private $key;
	
	private $type;
	
	function __construct( $config )
	{
		$this->key = $config->key;
		
		$this->type = $config->type;
	}
	
	private function executeMethod( $method, $params = array() )
	{
		$queryUrl = self::API_URL;
		
		$queryData = array(
			"auth_key" => $this->key,
			"method" => $method,
		);
		
		if( !empty($params) ) $queryData["params"] = $params;
		
		$queryUrl .= json_encode($queryData);
		
		$request = new ApiRequest();
		$request->assign(array(
			"type" => "autotrade",
			"data" => json_encode($queryData),
		));
		$request->save();
		
		$currentRequestIsFirst = false;
		
		while( !$currentRequestIsFirst )
		{
			$requestsBefore = ApiRequest::find(array(
				"conditions" => "type = 'autotrade' AND executedAt = '0' AND id < :id:",
				"bind" => array(
					"id" => $request->id,
				),
				"order" => "id DESC",
			));
			
			if( count($requestsBefore) > 0 )
			{
				sleep(count($requestsBefore));
				continue;
			}
			
			$currentRequestIsFirst = true;
			
			$beforeRequest = ApiRequest::findFirst(array(
				"conditions" => "type = 'autotrade' AND executedAt != '0'",
				"order" => "id DESC",
			));
			
			if( $beforeRequest )
			{
				$current = microtime(true);
				
				if( $current - $beforeRequest->executedAt < 1 )
				{
					sleep(1);
				}
			}
			
			$options = stream_context_create(array(
				"http" => array(
					"timeout" => 3, //3 seconds
				),
			));
			
			$result = json_decode(file_get_contents($queryUrl, false, $options));
			
			$request->executedAt = microtime(true);
			$request->save();
		}
		
		return $result;
	}
	
	public function getCatalogs()
	{
		return $this->executeMethod("GetCatalogsList");
	}
	
	public function getCatalogSections( $catalogId )
	{
		return $this->executeMethod("GetSectionsList", array(
			"catalog_id" => $catalogId,
		));
	}
	
	public function getCatalogSubSections( $catalogId, $sectionId )
	{
		return $this->executeMethod("GetSubSectionsList", array(
			"catalog_id" => $catalogId,
			"section_id" => $sectionId,
		));
	}
	
	public function getCatalogItems( $catalogId, $sectionId, $subSectionId, $page = 1 )
	{
		return $this->executeMethod("getItemsByCatalog", array(
			"catalog_id" => $catalogId,
			"section_id" => $sectionId,
			"subsection_ids" => array($subSectionId),
			"page" => $page,
			"limit" => 100,
		));
	}
	
	public function getItemProperties( $article )
	{
		return $this->executeMethod("getProperties", array(
			"article" => $article,
		));
	}
	
	public function getStorages()
	{
		return $this->executeMethod("getStoragesList");
	}
	
	public function find( $query, $brand = "" )
	{
		$params = array(
			"q" => array($query),
			"strict" => 0,
			"cross" => 1,
			"replace" => 1,
			"with_stocks_and_prices" => 1,
			"with_delivery" => 1,
		);
		
		if( $brand != "" )
			$params["filter_brands"] = array($brand);
		
		$result = $this->executeMethod("getItemsByQuery", $params);
		
		$items = array();
		$itemsArticles = array();
		
		foreach( $result->items AS $item )
		{
			$itemTo = array(
				"code" => $this->type."::" . $item->brand_name . "@" . $item->article,
				"article" => $item->article,
				"brand" => $item->brand_name,
				"title" => $item->name,
				"photo" => $item->photo,
				"price" => $item->price,
				"count" => 0,
				"stocks" => array(),
			);
			
			foreach( $item->stocks AS $stock )
			{
				$itemTo["count"] += $stock->quantity_unpacked + $stock->quantity_packed;
				$itemTo["stocks"][] = array(
					"id" => $stock->id,
					"code" => $this->type."::" . $item->brand_name . "@" . $item->article . "::" . $stock->id,
					"name" => $stock->name,
					"count" => $stock->quantity_unpacked + $stock->quantity_packed,
					"price" => $item->price,
					"delivery" => $stock->delivery_period,
				);
			}
			
			$items[] = $itemTo;
		}
		
		return $items;
	}
	
	public function cart( $items )
	{
		$itemsToQuery = array();
		
		foreach( $items AS $article => $stocksData )
		{
			list($brand, $articleVal) = explode("@", $article);
			
			$itemsToQuery[$articleVal] = 1;
		}
		
		$result = $this->executeMethod("getStocksAndPrices", array(
			"storages" => 0,
			"items" => $itemsToQuery,
			"strict" => 1,
			"withDelivery" => 1,
		));
		
		$items = array();
		
		foreach( $result->items AS $item )
		{
			$items[$item->brand . "@" . $item->article] = array(
				"code" => $this->type."::" . $item->brand . "@" . $item->article,
				"article" => $item->article,
				"name" => $item->name,
				"brand" => $item->brand,
				"price" => $item->price,
				"count" => 0,
				"stocks" => array(),
			);
			
			foreach( $item->stocks AS $stock )
			{
				$items[$item->brand . "@" . $item->article]["count"] += $stock->quantity_unpacked + $stock->quantity_packed;
				
				$items[$item->brand . "@" . $item->article]["stocks"][] = array(
					"id" => $stock->id,
					"code" => $this->type."::" . $item->brand . "@" . $item->article . "::" . $stock->id,
					"name" => $stock->name,
					"count" => $stock->quantity_unpacked + $stock->quantity_packed,
					"price" => $item->price,
					"delivery" => $stock->delivery_period,
				);
			}
		}
		
		return $items;
	}
}