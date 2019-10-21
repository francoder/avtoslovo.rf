<?php
namespace Molly;

use Phalcon\Mvc\User\Component;

class Geocode extends Component
{
	const GOOGLE_API_URL = "https://maps.googleapis.com/maps/api/geocode/json";
	const GOOGLE_PLACES_API_URL = "https://maps.googleapis.com/maps/api/place/autocomplete/json";
	const GOOGLE_PLACE_API_URL = "https://maps.googleapis.com/maps/api/place/details/json";
	
	private $codesCache = array();
	
	public function search( $text, $types = "" )
	{
		$queryUrl = self::GOOGLE_PLACES_API_URL;
		
		$queryParams = array(
			"key" => $this->getDI()->get("application")->placesapi,
			"input" => $text,
			"language" => "ru-RU",
		);
		
		if( $types != "" )
			$queryParams["types"] = $types;
		
		$queryUrl .= "?" . http_build_query($queryParams);
		
		return json_decode(file_get_contents($queryUrl));
	}
	
	public function details( $place )
	{
		if( isset($codesCache[$place]) )
			return $codesCache[$place];
		
		$queryUrl = self::GOOGLE_PLACE_API_URL;
		
		$queryParams = array(
			"key" => $this->getDI()->get("application")->placesapi,
			"placeid" => $place,
			"language" => "ru-RU",
		);
		
		$queryUrl .= "?" . http_build_query($queryParams);
		
		$codesCache[$place] = json_decode(file_get_contents($queryUrl));
		
		return $codesCache[$place];
	}
	
	public function geocode( $text )
	{
		$queryUrl = self::GOOGLE_API_URL;
		
		$queryUrl .= "?" . http_build_query(array(
			"address" => urlencode($text),
		));
		
		return json_decode(file_get_contents($queryUrl));
	}
	
	public function geocodeBack( $placeId )
	{
		$queryUrl = self::GOOGLE_API_URL;
		
		$queryUrl .= "?" . http_build_query(array(
			"place_id" => urlencode($placeId),
		));
		
		return json_decode(file_get_contents($queryUrl));
	}
	
	public function geocodeBackLatLng( $lat, $lng )
	{
		$queryUrl = self::GOOGLE_API_URL;
		
		$queryUrl .= "?" . http_build_query(array(
			"latlng" => urlencode($lat . ", " . $lng),
		));
		
		return json_decode(file_get_contents($queryUrl));
	}
}