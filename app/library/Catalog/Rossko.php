<?php
namespace Molly\Catalog;

use Phalcon\Mvc\User\Component;

class Rossko extends Component
{
	const API_URL = "http://api.rossko.ru/service/";
	
	private $methodClients = array();
	
	private $key1;
	private $key2;
	
	private $type;
	
	function __construct( $config )
	{
		$this->key1 = $config->key1;
		$this->key2 = $config->key2;
		
		$this->type = $config->type;
	}
	
	public function getStorages()
	{
		$storages = array(
			array(
				"id" => "R0",
				"name" => "Склад по умолчанию",
			)
		);
		
		return json_decode(json_encode($storages));
	}
	
	private function getClientFor( $method )
	{
		if( isset($methodClients[$method]) )
			return $methodClients[$method];
		
		$methodClients[$method] = new \SoapClient(self::API_URL . $method, array(
			"connection_timeout" => 30,
		));
		
		return $methodClients[$method];
	}
	
	private function executeMethod( $method, $params = array() )
	{
		$params["KEY1"] = $this->key1;
		$params["KEY2"] = $this->key2;
		
		$client = $this->getClientFor($method);
		
		try {
			$result = $client->{$method}($params); 
		} catch (Exception $e) {
			$result = false;
		}
		
		return $result;
	}
	
	private function prepareItems( $items )
	{
		$itemsProcessed = array();
		
		if( empty($items) )
			return $itemsProcessed;
		
		foreach( $items AS $item )
		{
			if( !isset($items->stocks) AND !isset($items->crosses) )
				continue;
			
			if( isset($items->stocks) AND !empty($items->stocks->stock) )
			{
				$itemProcessed = array(
					"code" => $this->type."::" . $items->brand . "@" . $items->partnumber,
					"article" => $items->partnumber,
					"title" => $items->name,
					"photo" => "",
					"brand" => $items->brand,
					"price" => 0,
					"count" => 0,
					"stocks" => array(),
				);

				foreach( $items->stocks->stock AS $stockItem )
				{
					if( !isset($stockItem->price) )
						continue;
					
					if( $itemProcessed["price"] == 0 )
					{
						$itemProcessed["price"] = $stockItem->price;
					}
					
					$itemProcessed["count"] += $stockItem->count;
					$itemProcessed["stocks"][] = array(
						"id" => "R0",
						"stockItemId" => $stockItem->id,
						"code" => $this->type."::" . $items->brand . "@" . $items->partnumber . "::" . $stockItem->id,
						"count" => $stockItem->count,
						"name" => "stock",
						"price" => $stockItem->price,
						"delivery" => $stockItem->delivery,
					);
				}
				
				if( !empty($itemProcessed["stocks"]) )
					$itemsProcessed[] = $itemProcessed;
			}
			
			if( isset($items->crosses) AND !empty($items->crosses->Part) )
			{
				$subItems = $this->prepareItems($item->crosses->Part);
				$itemsProcessed = array_merge($itemsProcessed, $subItems);
			}
		}
		
		return $itemsProcessed;
	}
	
	public function find( $query, $brand = "" )
	{
		if( $brand )
			$text = $brand . " " . $query;
		else
			$text = $query;
		
		$result = $this->executeMethod("GetSearch", array(
			"TEXT" => $query,
		));
		
		if( !$result OR !isset($result->SearchResult->PartsList->Part) )
			return array();
		
		$items = $this->prepareItems($result->SearchResult->PartsList->Part);
		
		return $items;
	}
	
	public function cart( $items )
	{
		$resultItems = array();
		
		foreach( $items AS $key => $positions )
		{
			list($brand, $article) = explode("@", $key);
			
			$result = $this->executeMethod("GetSearch", array(
				"TEXT" => $article,
			));
			
			if( !$result OR !isset($result->SearchResult->PartsList->Part) )
				continue;
			
			$items = $this->prepareItems($result->SearchResult->PartsList->Part);
			
			foreach( $items AS $item )
			{
				if( $key != $item["brand"] . "@" . $item["article"] )
					continue;
				
				$item["name"] = $item["title"];
				unset($item["title"]);
				unset($item["photo"]);
				
				$resultItems[$item["brand"] . "@" . $item["article"]] = $item;
			}
		}
		
		return $resultItems;
	}
}