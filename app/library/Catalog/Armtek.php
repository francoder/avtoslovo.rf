<?php
namespace Molly\Catalog;

use Phalcon\Mvc\User\Component;

class Armtek extends Component
{
	CONST API_URL = "http://ws.armtek.ru/api/";
	
	private $login;
	private $password;
	
	private $type;
	
	function __construct( $config )
	{
		$this->login = $config->login;
		$this->password = $config->password;
		
		$this->type = $config->type;
	}
	
	private function executeMethod( $method, $params = array() )
	{
		$queryParams = array(
			"format" => "json",
		);
		
		$queryUrl = self::API_URL . $method . "?" . http_build_query($queryParams);
		
		$postdata = http_build_query($params);
		
		$auth = base64_encode($this->login.":".$this->password);
		
		$opts = array(
			"http" => array(
				"method" => "POST",
				"header" => "Content-type: application/x-www-form-urlencoded" . PHP_EOL . 
							"Authorization: Basic " . $auth,
				"content" => $postdata
			)
		);
		
		$context = stream_context_create($opts);
		
		$result = @file_get_contents($queryUrl, false, $context);
		
		return json_decode($result);
	}
	
	public function getStorages()
	{
		$storages = array(
			array(
				"id" => "AR0",
				"name" => "Склад по умолчанию",
			)
		);
		
		return json_decode(json_encode($storages));
	}
	
	public function find( $query, $brand = "" )
	{
		$params = array(
			"VKORG" => "4150",
			"KUNNR_RG" => "43105668",
			"PIN" => $query,
			"QUERY_TYPE" => "2",
		);
		
		if( $brand != "" )
			$params["BRAND"] = $brand;
		
		$result = $this->executeMethod("ws_search/search", $params);
		
		$results = array();
		
		if( !isset($result->RESP) OR !is_array($result->RESP) )
			return $results;
		
		$grouppedByArticle = array();
		$currentTime = new \DateTime();
		
		foreach( $result->RESP AS $item )
		{
			if( !isset($item->PIN) )
				continue;
			
			if( !isset($grouppedByArticle[$item->BRAND . "@" . $item->PIN]) )
			{
				$grouppedByArticle[$item->BRAND . "@" . $item->PIN] = array(
					"code" => $this->type . "::" . $item->BRAND . "@" . $item->PIN,
					"article" => $item->PIN,
					"title" => $item->NAME,
					"photo" => "",
					"brand" => $item->BRAND,
					"price" => $item->PRICE,
					"count" => 0,
					"stocks" => array(),
				);
			}
			
			$deliveryd = \DateTime::createFromFormat("YmdHis", $item->DLVDT);
			$interval = $deliveryd->diff($currentTime);
			
			$grouppedByArticle[$item->BRAND . "@" . $item->PIN]["count"] += $item->RVALUE;
			$grouppedByArticle[$item->BRAND . "@" . $item->PIN]["stocks"][] = array(
				"id" => "AR0",
				"stockItemId" => $item->KEYZAK,
				"code" => $this->type . "::" . $item->BRAND . "@" . $item->PIN . "::" . $item->KEYZAK,
				"count" => $item->RVALUE,
				"name" => "stock",
				"price" => $item->PRICE,
				"delivery" => $interval->format("%a"),
			);
		}
		
		foreach( $grouppedByArticle AS $item )
			$results[] = $item;
		
		return $results;
	}
	
	public function cart( $items )
	{
		$resultItems = array();
		$currentTime = new \DateTime();
		
		foreach( $items AS $key => $positions )
		{
			list($brand, $article) = explode("@", $key);
			
			$result = $this->executeMethod("ws_search/search", array(
				"VKORG" => "4150",
				"KUNNR_RG" => "43105668",
				"PIN" => $article,
			));
			
			if( !isset($result->RESP) OR !is_array($result->RESP) )
				continue;
			
			foreach( $result->RESP AS $item )
			{
				if( !isset($positions[$item->KEYZAK]) )
					continue;
				
				if( !isset($resultItems[$item->BRAND . "@" . $item->PIN]) )
				{
					$resultItems[$item->BRAND . "@" . $item->PIN] = array(
						"code" => $this->type . "::" . $item->BRAND . "@" . $item->PIN,
						"article" => $item->PIN,
						"name" => $item->NAME,
						"brand" => $item->BRAND,
						"price" => $item->PRICE,
						"count" => 0,
						"stocks" => array(),
					);
				}
				
				$deliveryd = \DateTime::createFromFormat("YmdHis", $item->DLVDT);
				$interval = $deliveryd->diff($currentTime);
				
				$resultItems[$item->BRAND . "@" . $item->PIN]["count"] += $item->RVALUE;
				$resultItems[$item->BRAND . "@" . $item->PIN]["stocks"][] = array(
					"id" => "AR0",
					"stockItemId" => $item->KEYZAK,
					"code" => $this->type . "::" . $item->BRAND . "@" . $item->PIN . "::" . $item->KEYZAK,
					"count" => $item->RVALUE,
					"name" => "stock",
					"price" => $item->PRICE,
					"delivery" => $interval->format("%a"),
				);
			}
		}
		
		return $resultItems;
	}
}