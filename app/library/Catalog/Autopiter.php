<?php
namespace Molly\Catalog;

use Phalcon\Mvc\User\Component;

class Autopiter extends Component
{
	CONST API_URL = "http://service.autopiter.ru/price.asmx?WSDL";
	private $login;
	private $password;
	
	private $type;
	
	private $client;
	
	public function __construct( $config )
	{
		$this->login = $config->login;
		$this->password = $config->password;
		
		$this->type = $config->type;
	}
	
	public function getStorages()
	{
		$storages = array(
			array(
				"id" => "AP0",
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
		
		$this->client = new \SoapClient(self::API_URL, array(
			"login" => $this->login,
			"password" => $this->password,
			"connection_timeout" => 3,
		));
		
		$result = $this->client->Authorization($this->login, $this->password, false);
		
		print_r($result);
		exit;
		
		return $this->client;
	}
	
	public function find( $query, $brand = "" )
	{
		$results = array();
		
		if( MODULE == "backend" )
		{
			// try {
			// 	$isAuth = $this->getClient()->IsAuthorization();
			// 	$queryCatalog = $this->getClient()->FindCatalog($query);
			// 	print_r($isAuth);
			// 	print_r($queryCatalog);
			// 
			// } catch (\Exception $e) {
			// 	print_r($e);
			// }
			// 
			// exit;
		}
		
		return $results;
	}
	
	public function cart( $items )
	{
		$resultItems = array();
		
		return $resultItems;
	}
}