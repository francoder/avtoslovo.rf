<?php
namespace Molly;

use Phalcon\Mvc\User\Component;

class CatalogHelper extends Component
{
	private $config;
	
	private $soap;
	private $soapError;
	
	function __construct()
	{
		$this->config = require ROOT_DIR."/app/config/cataloghelper.php";
		
	}
	
	public function buildCommand( $command, $params )
	{
		$item = new \stdClass();
		$item->command = $command;
		$item->params = $params;
		
		if( isset($params) && is_array($params) )
		{
			$command .= ":";
			$first = true;
			
			foreach ($params as $key=>$value)
			{
				if ($first)
					$first = false;
				else
					$command .= "|";
				
				$command .= $key."=".$value;
			}
			
			$item->commandText = $command;
		}
		else
			$item->commandText = $command;
		
		return $item;
	}
	
	public function query( $command )
	{
		if( is_array($command) )
		{
			$commands = array();
			
			foreach( $command AS $item )
				$commands[] = $item->commandText;
			
			$request = implode("\n", $commands);
		}
		else
			$request = $command->commandText;
		
		$data = $this->_query($request);
		
		if( $this->getSoapError() ) return false;
		
		return simplexml_load_string($data);
	}
	
	private function _query( $request )
	{
		try {
			$client = $this->getSoapClient();
			
			if( $this->config->authMethod == "certificate" )
				$data = $client->QueryData($request);
			else
				$data = $client->QueryDataLogin($request, $this->config->login, md5($request . $this->config->key));
			
			return $data;
		} catch (\Exception $e) {
			$this->soapError = $this->parseError($e->getMessage());
		}
	}
	
	public function getSoapError()
	{
		return $this->soapError;
	}
	
	private function parseError( $errorMessage )
	{
		if (strpos($errorMessage, "cURL ERROR: 35"))
			return "Not Connected";

		if (strpos($errorMessage, "cURL ERROR: 58"))
			return "No Certificate";

		if (strpos($errorMessage, "400 Bad Request"))
			return "Certificate expired";

		$e = explode("<br>", $errorMessage, 2);
		$errorMessage = $e[0];
		$pos = strrpos($errorMessage, "E_");
		
		if ($pos === false)
			$pos = strrpos($errorMessage, ":") + 1;
		
		return substr($errorMessage, $pos);
	}
	
	private function getSoapClient()
	{
		if( $this->soap )
			return $this->soap;
		
		$options = array(
			"compression" => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
		);
		
		$options["uri"] = "http://WebCatalog.Kito.ec";
		$options["location"] = ($this->config->authMethod == "certificate" ? "https" : "http")
			. "://ws.avtosoft.net/ec.Kito.WebCatalog/services/Catalog.CatalogHttpSoap11Endpoint/";
		
		if( $this->config->authMethod == "certificate" )
		{
			$options["sslCertPath"] = $this->config->certificateFileName;
			$options["sslKeyPath"] = $this->config->certificateKeyFileName;
			$options["passphrase"] = $this->config->certificatePassword;
			$options["sslcertpasswd"] = $this->config->certificatePassword;
			$options["verifypeer"] = 0;
			$options["verifyhost"] = 0;
		}
		
		$this->soap = new SSLSoapClient(null, $options);
		
		return $this->soap;
	}
}