<?php
namespace Molly\Catalog;

use Phalcon\Mvc\User\Component;

class Abcp extends Component
{
	CONST API_URL = "http://avtolev.ru.public.api.abcp.ru/";
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
			"userlogin" => $this->login,
			"userpsw" => md5($this->password),
		);
		
		if( !empty($params) )
		{
			$queryParams = array_merge($queryParams, $params);
		}
		
		$queryUrl = self::API_URL . $method . "?" . http_build_query($queryParams);
		
		$result = @file_get_contents($queryUrl);
		
		return @json_decode($result);
	}
	
	public function getStorages()
	{
		$storages = array(
			array(
				"id" => "AB0",
				"name" => "Склад по умолчанию",
			)
		);
		
		return json_decode(json_encode($storages));
	}
	
	public function find( $query, $brand = "" )
	{
		$brands = $this->executeMethod("search/brands/", array(
			"number" => $query,
		));
		
		$returnResults = array();
		
		if( !$brands )
			return $returnResults;
		
		foreach( $brands AS $brandData )
		{
			if( $brand != "" AND strtoupper($brandData->brand) != strtoupper($brand) )
				continue;
			
			$articles = $this->executeMethod("search/articles/", array(
				"number" => $query,
				"brand" => $brandData->brand,
			));
			
			if( empty($articles) )
				continue;
			
			foreach( $articles AS $article )
			{
				$item = array(
					"code" => $this->type . "::" . $article->brand . "@" . $article->number,
					"article" => $article->number,
					"title" => $article->description,
					"photo" => "",
					"brand" => $article->brand,
					"price" => $article->price,
					"count" => $article->availability,
					"stocks" => array(
						array(
							"id" => "AB0",
							"stockItemId" => urldecode($article->itemKey),
							"code" => $this->type . "::" . $article->brand . "@" . $article->number . "::" . urldecode($article->itemKey),
							"name" => "Склад по умолчанию",
							"count" => $article->availability,
							"price" => $article->price,
							"delivery" => $article->deliveryPeriod,
						),
					),
				);
				
				$returnResults[] = $item;
			}
		}
		
		return $returnResults;
	}
	
	public function cart( $items )
	{
		$resultItems = array();
		
		foreach( $items AS $key => $positions )
		{
			list($brand, $article) = explode("@", $key);
			
			$articles = $this->executeMethod("search/articles/", array(
				"number" => $article,
				"brand" => $brand,
			));
			
			if( empty($articles) )
				continue;
			
			foreach( $articles AS $article )
			{
				if( !isset($positions[urldecode($article->itemKey)]) )
					continue;
				
				$resultItems[$article->brand . "@" . $article->number] = array(
					"code" => $this->type . "::" . $article->brand . "@" . $article->number,
					"article" => $article->number,
					"name" => $article->description,
					"brand" => $article->brand,
					"price" => $article->price,
					"count" => $article->availability,
					"stocks" => array(
						array(
							"id" => "AB0",
							"stockItemId" => urldecode($article->itemKey),
							"code" => $this->type . "::" . $article->brand . "@" . $article->number . "::" . urldecode($article->itemKey),
							"name" => "Склад по умолчанию",
							"count" => $article->availability,
							"price" => $article->price,
							"delivery" => $article->deliveryPeriod,
						),
					),
				);
			}
		}
		
		return $resultItems;
	}
}