<?php


namespace Molly\Backend\Controllers;

use Molly\Models\ContentBanner,
    Molly\Models\ContentSlider;

class ContentController extends ControllerBase
{

    public function bannerAction()
    {
        if($this->request->isPost())
        {

            switch( $this->request->getPost("bulkAction") )
            {
                case "0":
                    # Nothing
                    break;
                case "1":
                    # Deleting
                    $bulkList = $this->request->getPost("bulk");

                    if( empty($bulkList) )
                        break;

                    $banner_list = $this->modelsManager->createBuilder()
                        ->from("Molly\Models\ContentBanner")
                        ->inWhere("id", $bulkList)
                        ->getQuery()->execute();

                    if( empty($banner_list) )
                        break;

                    foreach( $banner_list AS $banner )
                        $banner->delete();

                    return $this->response->redirect($_SERVER["HTTP_REFERER"]);

                    break;
            }

            $dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/banners/';

            $banner_image = time() . $_FILES['banner_image']['name'];
            $banner_image_name = $this->request->getPost('banner_image_name');
            $banner_image_title = $this->request->getPost('banner_image_title');
            $uploadfile = $dir . basename($banner_image);
            $allow_ext = array("jpg", "jpeg", "png");
            $banner_image_ext = pathinfo($uploadfile);
            if (!in_array($banner_image_ext['extension'], $allow_ext)) {
                $this->setTitle("Неподдерживаемый тип файла");
                return $this->response->setStatusCode(415, "Неподдерживаемый тип файла");
            };
            if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $uploadfile)) {
                $banners_table = new ContentBanner();
                $banners_table->createdAt = (new \DateTime())->format("Y-m-d H:i:s");
                $banners_table->userId = $this->auth->getCurrentId();
                $banners_table->name = htmlentities($banner_image_name, ENT_COMPAT | ENT_HTML401, 'UTF-8');
                $banners_table->title = htmlspecialchars($banner_image_title);
                $banners_table->path = '/assets/img/banners/' . $banner_image;
                $banners_table->save();
            }
        }


        $this->setTitle("Баннеры");

        $banners = ContentBanner::find();

        $this->view->banners = $banners;
    }

    public function sliderAction()
    {

        if($this->request->isPost())
        {

            switch( $this->request->getPost("bulkAction") )
            {
                case "0":
                    # Nothing
                    break;
                case "1":
                    # Deleting
                    $bulkList = $this->request->getPost("bulk");

                    if( empty($bulkList) )
                        break;

                    $slider_list = $this->modelsManager->createBuilder()
                        ->from("Molly\Models\ContentSlider")
                        ->inWhere("id", $bulkList)
                        ->getQuery()->execute();

                    if( empty($slider_list) )
                        break;

                    foreach( $slider_list AS $slide )
                        $slide->delete();

                    return $this->response->redirect($_SERVER["HTTP_REFERER"]);

                    break;
            }

            $dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/slides/';

            $slider_image = time() . $_FILES['slider_image']['name'];
            $slider_image_name = $this->request->getPost('slider_image_name');
            $slider_image_title = $this->request->getPost('slider_image_title');
            $uploadfile = $dir . basename($slider_image);
            $allow_ext = array("jpg", "jpeg", "png");
            $slider_image_ext = pathinfo($uploadfile);
            if(!in_array($slider_image_ext['extension'], $allow_ext)) {
                $this->setTitle("Неподдерживаемый тип файла");
                return $this->response->setStatusCode(415, "Неподдерживаемый тип файла");
            };
            if(move_uploaded_file($_FILES['slider_image']['tmp_name'], $uploadfile)) {
                $slider_table = new ContentSlider();
                $slider_table->createdAt = (new \DateTime())->format("Y-m-d H:i:s");
                $slider_table->userId    = $this->auth->getCurrentId();
                $slider_table->name      = htmlentities($slider_image_name, ENT_COMPAT | ENT_HTML401, 'UTF-8');
                $slider_table->title     = htmlspecialchars($slider_image_title);
                $slider_table->path      = '/assets/img/slides/'.$slider_image;
                $slider_table->save();
            }
        }

        $this->setTitle("Слайдер");

        $slides = ContentSlider::find();

        $this->view->slides = $slides;
    }
}