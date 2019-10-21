<?php
namespace Molly\Catalog;

use Phalcon\Mvc\User\Component;

class Emex extends Component
{
	private $client = false;
	
	private $login;
	private $password;
	
	private $type;
	
	function __construct( $config )
	{
		$this->login = $config->login;
		$this->password = $config->password;
		
		$this->type = $config->type;
	}
	
	public function getStorages()
	{
		$storages = array(
			array(
				"id" => "EM0",
				"name" => "Склад по умолчанию",
			)
		);
		
		return json_decode(json_encode($storages));
	}
	
	private function getClient()
	{
		if( $this->client )
			return $this->client;
		
		ini_set("soap.wsdl_cache_enabled", "0");
		
		$this->client = new \SoapClient("http://ws.emex.ru/EmExService.asmx?WSDL", array(
			"login" => $this->login,
			"password" => $this->password,
			"connection_timeout" => 3,
		));
		
		return $this->client;
	}
	
	private function executeMethod( $method, $params = array() )
	{
		$client = $this->getClient();
		
		try {
			$result = $client->{$method}($params); 
		} catch (Exception $e) {
			return false;
		}
		
		return $result;
	}
	
	private function getBrandLogo( $brand )
	{
		ini_set("soap.wsdl_cache_enabled", "0");
		
		$client = new \SoapClient("http://ws.emex.ru/EmExDictionaries.asmx?WSDL", array(
			"login" => $this->login,
			"password" => $this->password,
			"connection_timeout" => 30,
		));
		
		$brands = $client->GetMakesDict(array(
			"login" => $this->login,
			"password" => $this->password,
		));
		
		foreach( $brands->GetMakesDictResult->ShortMakeInfo AS $logoItem )
		{
			if( strtoupper($logoItem->MakeName) == strtoupper($brand) )
			{
				return $logoItem->MakeLogo;
			}
		}
		
		return "null";
	}
	
	public function find( $query, $brand = "" )
	{
		$params = array(
			"login" => $this->login,
			"password" => $this->password,
			"detailNum" => $query,
			"substLevel" => "All",
			"substFilter" => "None",
			"deliveryRegionType" => "PRI",
		);
		
		if( $brand != "" )
		{
			$logo = $this->getBrandLogo($brand);
			$params["MakeLogo"] = $logo; //$brand
		}
		
		$result = $this->executeMethod("FindDetailAdv4", $params);
		
		$resultItems = array();
		
		if( !$result OR !isset($result->FindDetailAdv4Result) OR empty($result->FindDetailAdv4Result->Details->SoapDetailItem) )
			return $resultItems;
		
		$grouppedByArticle = array();
		
		foreach( $result->FindDetailAdv4Result->Details->SoapDetailItem AS $item )
		{
			if( !isset($grouppedByArticle[$item->MakeName . "@" . $item->DetailNum]) )
			{
				$grouppedByArticle[$item->MakeName . "@" . $item->DetailNum] = array(
					"code" => $this->type."::" . $item->MakeName . "@" . $item->DetailNum,
					"article" => $item->DetailNum,
					"title" => $item->DetailNameRus,
					"photo" => "",
					"brand" => $item->MakeName,
					"price" => $item->ResultPrice,
					"count" => 0,
					"stocks" => array(),
				);
			}
			
			$stockItemId = $item->DestinationLogo . "|";
			
			if( isset($item->PriceLogo) )
				$stockItemId .= $item->PriceLogo;
			
			$grouppedByArticle[$item->MakeName . "@" . $item->DetailNum]["count"] += $item->Quantity;
			$grouppedByArticle[$item->MakeName . "@" . $item->DetailNum]["stocks"][] = array(
				"id" => "EM0",
				"stockItemId" => $stockItemId,
				"code" => $this->type."::" . $item->MakeName . "@" . $item->DetailNum . "::" . $stockItemId,
				"count" => $item->Quantity,
				"name" => "stock",
				"price" => $item->ResultPrice,
				"delivery" => $item->DeliverTimeGuaranteed,
			);
		}
		
		if( empty($grouppedByArticle) )
			return $resultItems;
		
		foreach( $grouppedByArticle AS $item )
			$resultItems[] = $item;
		
		return $resultItems;
	}
	
	public function cart( $items )
	{
		$resultItems = array();
		
		foreach( $items AS $key => $positions )
		{
			list($brand, $article) = explode("@", $key);
			
			$result = $this->executeMethod("FindDetailAdv4", array(
				"login" => $this->login,
				"password" => $this->password,
				"detailNum" => $article,
				"substLevel" => "All",
				"substFilter" => "None",
				"deliveryRegionType" => "PRI",
			));
			
			if( !$result OR !isset($result->FindDetailAdv4Result) OR empty($result->FindDetailAdv4Result->Details->SoapDetailItem) )
				continue;
			
			foreach( $result->FindDetailAdv4Result->Details->SoapDetailItem AS $item )
			{
				if( $key != $item->MakeName . "@" . $item->DetailNum )
					continue;
				
				$stockItemId = $item->DestinationLogo . "|";
				
				if( isset($item->PriceLogo) )
					$stockItemId .= $item->PriceLogo;
				
				if( !isset($positions[$stockItemId]) )
					continue;
				
				if( !isset($resultItems[$item->MakeName . "@" . $item->DetailNum]) )
				{
					$resultItems[$item->MakeName . "@" . $item->DetailNum] = array(
						"code" => $this->type."::" . $item->MakeName . "@" . $item->DetailNum,
						"article" => $item->DetailNum,
						"name" => $item->DetailNameRus,
						"brand" => $item->MakeName,
						"price" => $item->ResultPrice,
						"count" => 0,
						"stocks" => array(),
					);
				}
				
				$resultItems[$item->MakeName . "@" . $item->DetailNum]["count"] += $item->Quantity;
				$resultItems[$item->MakeName . "@" . $item->DetailNum]["stocks"][] = array(
					"id" => "EM0",
					"stockItemId" => $stockItemId,
					"code" => $this->type."::" . $item->MakeName . "@" . $item->DetailNum . "::" . $stockItemId,
					"count" => $item->Quantity,
					"name" => "stock",
					"price" => $item->ResultPrice,
					"delivery" => $item->DeliverTimeGuaranteed,
				);
			}
		}
		
		return $resultItems;
	}
}
