<?php
namespace Molly\Catalog;

use Phalcon\Mvc\User\Component;

class Tmparts extends Component
{
	CONST API_URL = "http://api.tmparts.ru/api/";
	private $key;
	private $type;
	
	function __construct( $config )
	{
		$this->key = $config->key;
		$this->type = $config->type;
	}
	
	private function executeMethod( $method, $params = array() )
	{
		$opts = array(
			"http" => array(
				"method" => "GET",
				"header" => "Authorization: Bearer " . $this->key . PHP_EOL .
					"Content-Type: application/json",
			),
		);
		
		$context = stream_context_create($opts);
		
		$queryUrl = self::API_URL . $method;
		
		if( !empty($params) )
		{
			$paramsToAdd = array(
				"JSONparameter" => json_encode($params),
			);
			
			$queryUrl .= "?".http_build_query($paramsToAdd);
		}
		
		$result = @file_get_contents($queryUrl, false, $context);
		
		return @json_decode($result);
	}
	
	public function getStorages()
	{
		$storages = array(
			array(
				"id" => "TM0",
				"name" => "Склад по умолчанию",
			)
		);
		
		return json_decode(json_encode($storages));
	}
	
	public function find( $query, $brand = "" )
	{
		$results = $this->executeMethod("StockByArticleList", array(
			"is_main_warehouse" => 0,
			"Brand_Article_List" => array(
				array(
					"Brand" => $brand,
					"Article" => $query,
				),
			),
		));
		
		$returnResults = array();
		
		if( !$results )
			return $returnResults;
		
		foreach( $results AS $result )
		{
			$item = array(
				"code" => $this->type."::" . $result->brand . "@" . $result->article,
				"article" => $result->article,
				"title" => $result->article_name,
				"photo" => "",
				"brand" => $result->brand,
				"price" => $result->min_price,
				"count" => 0,
				"stocks" => array(),
			);
			
			foreach( $result->warehouse_offers AS $stock )
			{
				$item["count"] += $stock->quantity;
				
				$item["stocks"][] = array(
					"id" => "TM0",
					"stockItemId" => $stock->id,
					"code" => $this->type."::" . $result->brand . "@" . $result->article . "::" . $stock->id,
					"name" => $stock->warehouse_name,
					"count" => $stock->quantity,
					"price" => $stock->price,
					"delivery" => $stock->delivery_period,
				);
			}
			
			$returnResults[] = $item;
		}
		
		return $returnResults;
	}
	
	public function cart( $items )
	{
		$query = array();
		
		foreach( array_keys($items) AS $key )
		{
			list($brand, $article) = explode("@", $key);
			$query[] = array(
				"Brand" => $brand,
				"Article" => $article,
			);
		}
		
		$results = $this->executeMethod("StockByArticleList", array(
			"is_main_warehouse" => 0,
			"Brand_Article_List" => $query,
		));
		
		$items = array();
		
		foreach( $results AS $result )
		{
			$items[$result->brand . "@" . $result->article] = array(
				"code" => $this->type."::" . $result->brand . "@" . $result->article,
				"article" => $result->article,
				"name" => $result->article_name,
				"brand" => $result->brand,
				"price" => $result->min_price,
				"count" => 0,
				"stocks" => array(),
			);
			
			foreach( $result->warehouse_offers AS $stock )
			{
				$items[$result->brand . "@" . $result->article]["count"] += $stock->quantity;
				
				$items[$result->brand . "@" . $result->article]["stocks"][] = array(
					"id" => "TM0",
					"stockItemId" => $stock->id,
					"code" => $this->type."::" . $result->brand . "@" . $result->article . "::" . $stock->id,
					"name" => $stock->warehouse_name,
					"count" => $stock->quantity,
					"price" => $stock->price,
					"delivery" => $stock->delivery_period,
				);
			}
		}
		
		return $items;
	}
}