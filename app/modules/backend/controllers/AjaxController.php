<?php
namespace Molly\Backend\Controllers;

class AjaxController extends ControllerBase
{
	public function ajaxAction()
	{
		$this->view->disable();
		$this->response->setContentType("application/json", "UTF-8");
		
		switch( $this->request->get("action") )
		{
			case "citySearch":
				$result = $this->geocode->search($this->request->get("city"), "(cities)");
				
				return $this->response->setJsonContent(array("error" => false, "data" => array(
					"words" => $this->request->get("city"),
					"result" => $result,
				)));
				break;
			
			case "place":
				$result = $this->geocode->details($this->request->get("placeId"));
				
				return $this->response->setJsonContent(array("error" => false, "data" => array(
					"result" => $result->result,
				)));
				break;
			
			default:
				return $this->response->setJsonContent(array("error" => true, "errorMsg" => "Action not found"));
				break;
		}
	}
}