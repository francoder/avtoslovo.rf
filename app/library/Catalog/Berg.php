<?php
namespace Molly\Catalog;

use Phalcon\Mvc\User\Component;

class Berg extends Component
{
	CONST API_URL = "https://api.berg.ru/";
	
	private $key;
	
	private $type;
	
	function __construct( $config )
	{
		$this->key = $config->key;
		
		$this->type = $config->type;
	}
	
	public function executeMethod( $method, $params )
	{
		$queryParams = array(
			"key" => $this->key,
		);
		
		if( !empty($params) )
		{
			$queryParams = array_merge($queryParams, $params);
		}
		
		$queryUrl = self::API_URL . $method . "?" . http_build_query($queryParams);
		// echo $queryUrl;
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $queryUrl);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$result = curl_exec($ch);
		
		curl_close($ch);
		
		return @json_decode($result);
	}
	
	public function getStorages()
	{
		$storages = array(
			array(
				"id" => "B0",
				"name" => "Склад по умолчанию",
			)
		);
		
		return json_decode(json_encode($storages));
	}
	
	public function find( $query, $brand = "" )
	{
		$items = array();
		
		$params = array(
			"resource_article" => $query,
		);
		
		$result = $this->executeMethod("ordering/get_stock.json", array("items" => array($params), "analogs" => 1));
		
		if( !isset($result->resources) OR empty($result->resources) )
			return $items;
		
		foreach( $result->resources AS $item )
		{
			$article = array(
				"code" => $this->type . "::" . $item->brand->name . "@" . $item->article,
				"article" => $item->article,
				"title" => $item->name,
				"photo" => "",
				"brand" => $item->brand->name,
				"price" => 0,
				"count" => 0,
				"stocks" => array(),
			);
			
			foreach( $item->offers AS $offer )
			{
				$article["price"] = $offer->price;
				$article["count"] += intval($offer->quantity);
				$article["stocks"][] = array(
					"id" => "B0",
					"stockItemId" => $offer->warehouse->id.$offer->price,
					"code" => $this->type . "::" . $item->brand->name . "@" . $item->article . "::" . $offer->warehouse->id.$offer->price,
					"name" => "Склад по умолчанию",
					"count" => $offer->quantity,
					"price" => $offer->price,
					"delivery" => $offer->assured_period,
				);
			}
			
			$items[] = $article;
		}
		
		return $items;
	}
	
	public function cart( $items )
	{
		$resultItems = array();
		
		foreach( $items AS $key => $positions )
		{
			list($brand, $article) = explode("@", $key);
			
			$params = array(
				"resource_article" => $article,
			);
			
			$result = $this->executeMethod("ordering/get_stock.json", array("items" => array($params), "analogs" => 0));
			
			if( !isset($result->resources) OR empty($result->resources) )
				continue;
			
			foreach( $result->resources AS $item )
			{
				if( $item->brand->name != $brand OR $item->article != $article )
					continue;
				
				foreach( $item->offers AS $offer )
				{
					if( !isset($positions[$offer->warehouse->id.$offer->price]) )
						continue;
					
					if( !isset($resultItems[$item->brand->name . "@" . $item->article]) )
					{
						$resultItems[$item->brand->name . "@" . $item->article] = array(
							"code" => $this->type . "::" . $item->brand->name . "@" . $item->article,
							"article" => $item->article,
							"name" => $item->name,
							"brand" => $item->brand->name,
							"price" => $offer->price,
							"count" => 0,
							"stocks" => array(),
						);
					}
					
					$resultItems[$item->brand->name . "@" . $item->article]["count"] += intval($offer->quantity);
					
					$resultItems[$item->brand->name . "@" . $item->article]["stocks"][] = array(
						"id" => "B0",
						"stockItemId" => $offer->warehouse->id.$offer->price,
						"code" => $this->type . "::" . $item->brand->name . "@" . $item->article . "::" . $offer->warehouse->id.$offer->price,
						"name" => "Склад по умолчанию",
						"count" => $offer->quantity,
						"price" => $offer->price,
						"delivery" => $offer->assured_period,
					);
				}
			}
		}
		
		return $resultItems;
	}
}