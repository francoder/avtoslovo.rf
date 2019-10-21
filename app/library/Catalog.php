<?php
namespace Molly;

use Phalcon\Mvc\User\Component,
	Molly\Models\CatalogSupplier;

class Catalog extends Component
{
	private $config;
	private $apis;
	private $imports;
	private $suppliers;
	private $suppliersData;
	
	function __construct()
	{
		$this->config = require ROOT_DIR."/app/config/catalog.php";
		
		$this->registerApis($this->config);
	}
	
	private function getSupplierData( $code )
	{
		if( isset($this->suppliersData[$code]) )
			return $this->suppliersData[$code];
		
		$this->suppliersData[$code] = CatalogSupplier::findFirst(array(
			"conditions" => "code = :code:",
			"bind" => array(
				"code" => $code,
			),
		));
		
		return $this->suppliersData[$code];
	}
	
	private function registerApis( $list )
	{		
		$className = "\Molly\Catalog\Local";
		$this->apis["local"] = new $className();

		foreach( $list AS $supplier )
		{
			$supplierData = $this->getSupplierData($supplier->type);
			
			$active = false;
			$markup = 0;
			
			if( $supplierData )
			{
				$active = $supplierData->active == "Y";
				$markup = $supplierData->markup;
			}
			
			if( !$active )
				continue;
			
			$className = "\Molly\Catalog\\" . ucfirst($supplier->class);
			// $classNameImport = "\Molly\Catalog\Import\\" . ucfirst($supplier->type);
			
			$this->apis[$supplier->type] = new $className($supplier);
			// $this->imports[$supplier->type] = new $classNameImport($this->apis[$supplier->type]);
		}
	}
	
	public function getApi( $name )
	{
		if( isset($this->apis[$name]) )
			return $this->apis[$name];
		
		return false;
	}
	
	public function getImport( $name )
	{
		if( isset($this->imports[$name]) )
			return $this->imports[$name];
		
		return false;
	}
	
	public function getSuppliers( $withStorages = false )
	{
		if( isset($this->suppliers) ) return $this->suppliers;
		
		$this->suppliers = array();
		
		foreach( $this->config AS $supplier )
		{
			$supplierData = $this->getSupplierData($supplier->type);
			
			$active = false;
			$markups = array();
			$brands = array();
			$storages = array();
			
			if( $supplierData )
			{
				$active = $supplierData->active == "Y";
				$markups = $supplierData->getMarkups();
				$storages = $supplierData->getStorages();
				$brands = $supplierData->getBrandsByBrand();
			}
			
			$this->suppliers[$supplier->type] = array(
				"code" => $supplier->type,
				"title" => $supplier->title,
				"data" => $supplierData,
				"active" => $active,
				"markups" => $markups,
				"storages" => $storages,
				"brands" => $brands,
			);
			
			if( $withStorages )
			{
				$api = $this->getApi($supplier->type);
				
				if( method_exists($api, "getStorages") )
				{
					$this->suppliers[$supplier->type]["storages"] = $api->getStorages();
				}
			}
		}
		
		return $this->suppliers;
	}
	
	public function findAll( $query, $brand = "" )
	{
		$local = array(
			"active" => true,
			"code" => "local",
			);
		$suppliers = $this->getSuppliers();
		array_unshift($suppliers, $local);
				
		$result = array();
		
		foreach( $suppliers AS $supplier )
		{
			if( !$supplier["active"] )
				continue;
			
			$apiResult = $this->getApi($supplier["code"])->find($query, $brand);
			
			if( empty($apiResult) )
				continue;
			
			foreach( $apiResult AS $resultItem )
			{
				if( $resultItem["count"] == 0 )
					continue;
				
				if( isset($resultItem["markups"]) )
					$resultItem = $this->markupItem($resultItem, $resultItem["markups"]);
				else
				{
					if( isset($supplier["brands"][strtoupper($resultItem["brand"])]) )
						$resultItem = $this->markupItem($resultItem, $supplier["brands"][strtoupper($resultItem["brand"])]["markups"]);
					else
						$resultItem = $this->markupItem($resultItem, $supplier["markups"]);
				}
				
				if( isset($supplier["storages"]) )
					$resultItem = $this->deliveryItem($resultItem, $supplier["storages"]);
				else
					$resultItem = $this->deliveryItem($resultItem, array());
				
				if( $resultItem["realCount"] == 0 )
					break;
				
				$resultItem["count"] = $resultItem["realCount"];
				
				$result[] = $resultItem;
			}
		}
		
		return $result;
	}
	
	public function cartAll( $data )
	{
		$suppliers = $this->getSuppliers();
		$result = array();
		
		foreach( $suppliers AS $supplier )
		{
			if( !isset($data[$supplier["code"]]) )
				continue;
			
			$apiResult = $this->getApi($supplier["code"])->cart($data[$supplier["code"]]);
			
			foreach( $apiResult AS $resultItem )
			{
				if( isset($supplier["brands"][strtoupper($resultItem["brand"])]) )
					$resultItem = $this->markupItem($resultItem, $supplier["brands"][strtoupper($resultItem["brand"])]["markups"]);
				else
					$resultItem = $this->markupItem($resultItem, $supplier["markups"]);
				
				$resultItem = $this->deliveryItem($resultItem, $supplier["storages"]);
				
				if( $resultItem["realCount"] == 0 )
					break;
				
				foreach( $resultItem["stocks"] AS $stock )
				{
					if( $stock["count"] == 0 )
						continue;
					
					if( isset($stock["stockItemId"]) )
					{
						if( !isset($data[$supplier["code"]][$resultItem["brand"] . "@" . $resultItem["article"]][$stock["stockItemId"]]) )
							continue;
					}
					else
					{
						if( !isset($data[$supplier["code"]][$resultItem["brand"] . "@" . $resultItem["article"]][$stock["id"]]) )
							continue;
					}
					
					
					$itemToResult = $resultItem;
					$itemToResult["code"] = $stock["code"];
					$itemToResult["stock"] = $stock["id"];
					$itemToResult["count"] = $stock["count"];
					$itemToResult["minDelivery"] = $stock["delivery"];
					$itemToResult["delivery"] = $stock["delivery"];
					$itemToResult["price"] = $stock["price"];
					$itemToResult["originalPrice"] = $stock["originalPrice"];
					
					unset($itemToResult["stocks"]);
					
					if( isset($stock["stockItemId"]) )
					{
						$result[$supplier["code"]][$resultItem["brand"] . "@" . $resultItem["article"]][$stock["stockItemId"]] = $itemToResult;
					}
					else
					{
						$result[$supplier["code"]][$resultItem["brand"] . "@" . $resultItem["article"]][$stock["id"]] = $itemToResult;
					}
				}
			}
		}
		
		# Local
		$localData = array();
		
		foreach( $data AS $supplier => $positions )
		{
			if( count(explode("-", $supplier)) != 2 )
				continue;
			
			list($local, $subsupplier) = explode("-", $supplier);
			
			$localData[$subsupplier] = $positions;
		}
		
		if( !empty($localData) )
		{
			$apiResult = $this->getApi("local")->cart($localData);
			
			foreach( $apiResult AS $resultItem )
			{
				list($api, $code) = explode("::", $resultItem["code"]);
				
				$resultItem = $this->markupItem($resultItem, $resultItem["markups"]);
				$resultItem = $this->deliveryItem($resultItem, array());
				
				if( $resultItem["realCount"] == 0 )
					break;
				
				$resultItem["count"] = $resultItem["realCount"];
				
				unset($resultItem["stocks"]);
				
				$result[$api][$code][0] = $resultItem;
			}
		}
		
		return $result;
	}
	
	public function markupItem( $item, $markups )
	{
		if( isset($item["originalPrice"]) )
			return $item;
		
		$item["originalPrice"] = $item["price"];
		
		usort($markups, function($a, $b){
			if($a->value == $b->value) return 0;
			return ($a->value < $b->value) ? 1 : -1;	
		});
		
		foreach( $markups AS $markup ){
			if( $markup->value >= $item["price"] )
				continue;
			
			$item["price"] = $item["originalPrice"] + 
				($item["originalPrice"]*$markup->percent/100);
			
			$item["price"] += $markup->fixed;
			break;
		}
		
		$item["price"] = intval($item["price"]);
		
		foreach( $item["stocks"] AS $stockKey => $storageData )
		{
			$item["stocks"][$stockKey]["originalPrice"] = $storageData["price"];
			$storageData["originalPrice"] = $storageData["price"];
			
			foreach( $markups AS $markup ){
				if( $markup->value >= $storageData["price"] )
					continue;
				
				$item["stocks"][$stockKey]["price"] = $storageData["originalPrice"] + 
					($storageData["originalPrice"]*$markup->percent/100);
				
				$item["stocks"][$stockKey]["price"] += $markup->fixed;
				break;
			}
			
			$item["stocks"][$stockKey]["price"] = intval($item["stocks"][$stockKey]["price"]);
		}
		
		return $item;
	}
	
	public function deliveryItem( $item, $storages )
	{
		foreach( $item["stocks"] AS $stockKey => $storageData )
		{
			$issetStorage = false;
			
			foreach( $storages AS $storage )
			{
				if( $storageData["id"] != $storage->id )
					continue;
				
				$issetStorage = true;
				
				if( !$storage->active OR $storageData["count"] == 0 )
				{
					unset($item["stocks"][$stockKey]);
					break;
				}
				
				$item["stocks"][$stockKey]["rate"] = $storage->rate;
				$item["stocks"][$stockKey]["stat"] = $storage->stat;
				$item["stocks"][$stockKey]["delivery"] += $storage->up;
				break;
			}
			
			if( !$issetStorage )
			{
				if( !isset($item["stocks"][$stockKey]["rate"]) )
					$item["stocks"][$stockKey]["rate"] = 5;
				
				if( !isset($item["stocks"][$stockKey]["stat"]) )
					$item["stocks"][$stockKey]["stat"] = json_decode(json_encode(array(
						"all" => 100,
						"before" => 0,
						"intime" => 100,
						"fail" => 0,
					)), FALSE);
			}
		}
		
		$item["minDelivery"] = 0;
		$item["realCount"] = 0;
		
		foreach( $item["stocks"] AS $storageData )
		{
			$item["realCount"] += $storageData["count"];
			
			if( $item["minDelivery"] == 0 )
				$item["minDelivery"] = $storageData["delivery"];
			else
				$item["minDelivery"] = min($storageData["delivery"], $item["minDelivery"]);
		}
		
		return $item;
	}
}