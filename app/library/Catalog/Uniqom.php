<?php
namespace Molly\Catalog;

use Phalcon\Mvc\User\Component;

class Uniqom extends Component
{
	CONST API_URL = "http://uniqom.ru/api/v2.1/";
	CONST PHOTO_BASE_URL = "http://uniqom.ru";
	
	private $login;
	private $password;
	
	private $type;
	
	function __construct( $config )
	{
		$this->login = $config->login;
		$this->password = $config->password;
		
		$this->type = $config->type;
	}
	
	public function executeMethod( $method, $params = array() )
	{
		$queryParams = array(
			"login" => $this->login,
			"password" => $this->password,
		);
		
		if( !empty($params) )
		{
			$queryParams = array_merge($queryParams, $params);
		}
		
		$queryUrl = self::API_URL . $method . "?" . http_build_query($queryParams);
		
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $queryUrl);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$json = curl_exec($ch);
		
		curl_close($ch);
		
		return @json_decode($json);
	}
	
	public function getStorages()
	{
		$storages = array(
			array(
				"id" => "U0",
				"name" => "Склад по умолчанию",
			)
		);
		
		return json_decode(json_encode($storages));
	}
	
	public function find( $query, $brand = "" )
	{
		$returnResults = array();
		
		if( $brand )
			$text = $brand . " " . $query;
		else
			$text = $query;
		
		$result = $this->executeMethod("searchall.json", array(
			"term" => $text,
		));
		
		if( !isset($result->goods) OR empty($result->goods) )
			return $returnResults;
		
		foreach( $result->goods AS $item )
		{
			$article = array(
				"code" => $this->type . "::" . $item->brand . "@" . $item->article_code,
				"article" => $item->article_code,
				"title" => $item->header,
				"photo" => (isset($item->images[0])?self::PHOTO_BASE_URL . $item->images[0]->big:""),
				"brand" => $item->brand,
				"price" => $item->price,
				"count" => 0,
				"stocks" => array(),
			);
			
			foreach( $item->deposit_storages AS $stockItem )
			{
				$article["count"] += intval($stockItem->deposit);
				$article["stocks"][] = array(
					"id" => "U0",
					"stockItemId" => $stockItem->id,
					"code" => $this->type . "::" . $item->brand . "@" . $item->article_code . "::" . $stockItem->id,
					"name" => "Склад по умолчанию",
					"count" => $stockItem->deposit,
					"price" => $stockItem->price,
					"delivery" => $stockItem->days,
				);
			}
			
			$returnResults[] = $article;
		}
		
		return $returnResults;
	}
	
	public function cart( $items )
	{
		$resultItems = array();
		
		foreach( $items AS $key => $positions )
		{
			list($brand, $article) = explode("@", $key);
			
			$result = $this->executeMethod("search.json", array(
				"term" => $article,
			));
			
			if( !isset($result->goods) OR empty($result->goods) )
				continue;
			
			foreach( $result->goods AS $item )
			{
				if( $item->brand != $brand OR $item->article_code != $article )
					continue;
				
				foreach( $item->deposit_storages AS $stockItem )
				{
					if( !isset($positions[$stockItem->id]) )
						continue;
					
					if( !isset($resultItems[$item->brand . "@" . $item->article_code]) )
					{
						$resultItems[$item->brand . "@" . $item->article_code] = array(
							"code" => $this->type . "::" . $item->brand . "@" . $item->article_code,
							"article" => $item->article_code,
							"name" => $item->header,
							"brand" => $item->brand,
							"price" => $item->price,
							"count" => 0,
							"stocks" => array(),
						);
					}
					
					$resultItems[$item->brand . "@" . $item->article_code]["count"] += intval($stockItem->deposit);
					
					$resultItems[$item->brand . "@" . $item->article_code]["stocks"][] = array(
						"id" => "U0",
						"stockItemId" => $stockItem->id,
						"code" => $this->type . "::" . $item->brand . "@" . $item->article_code . "::" . $stockItem->id,
						"name" => "Склад по умолчанию",
						"count" => $stockItem->deposit,
						"price" => $stockItem->price,
						"delivery" => $stockItem->days,
					);
				}
			}
		}
		
		return $resultItems;
	}
}