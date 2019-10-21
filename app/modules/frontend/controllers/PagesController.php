<?php
namespace Molly\Frontend\Controllers;

use Molly\Frontend\CatalogModels;
use Molly\Models\ContentBanner;

class PagesController extends ControllerBase
{
    use CatalogModels;
	public function deliveryAction()
	{
		$this->setTitle("Доставка и оплата");

        $this->getModelList();
        $banners = ContentBanner::find();
        $this->view->banners = $banners;
	}
	
	public function howtobuyAction()
	{
		$this->setTitle("Как купить");
        $banners = ContentBanner::find();
        $this->view->banners = $banners;
	}
	
	public function contactsAction()
	{
		$this->setTitle("Контакты");
	}
}