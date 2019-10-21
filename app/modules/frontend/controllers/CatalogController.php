<?php

namespace Molly\Frontend\Controllers;

use Molly\Frontend\CatalogModels;
use Molly\Models\CatalogLocalItem;
use Molly\Models\CatalogLocalSupplier;
use Molly\Models\CatalogSupplier;
use Molly\Models\UserAuto,
    Molly\Models\CatalogUserVin,
    Molly\Models\CatalogUserFrame,
    Molly\Models\ContentBanner,
    Molly\Models\CatalogUserPart;

class CatalogController extends ControllerBase {

    use CatalogModels;
    public function indexAction() {
        $this->setTitle("Каталог");

        $this->getModelList();
        switch ($this->request->get("action")) {
            case "unit":
                $catalogCode = $this->request->get("code");
                $vid = $this->request->get("vid");
                $uid = $this->request->get("uid");
                $ssd = $this->request->get("ssd");

                $commandCatalog = $this->cataloghelper->buildCommand("GetCatalogInfo", array("Locale" => "ru_RU", "Catalog" => $catalogCode, "ssd" => $ssd));
                $commandVehicle = $this->cataloghelper->buildCommand("GetVehicleInfo", array("Locale" => "ru_RU", "Catalog" => $catalogCode, "VehicleId" => $vid, "ssd" => $ssd));
                $info = $this->cataloghelper->query(array($commandCatalog, $commandVehicle));

                $catalogInfo = $info->GetCatalogInfo->row;
                $vehicleInfo = $info->GetVehicleInfo->row;

                $commandUnit = $this->cataloghelper->buildCommand("GetUnitInfo", array(
                    "Locale" => "ru_RU",
                    "Catalog" => $catalogCode,
                    "UnitId" => $uid,
                    "ssd" => $ssd,
                    "Localized" => true,
                ));
                $commandImage = $this->cataloghelper->buildCommand("ListImageMapByUnit", array(
                    "Locale" => "ru_RU",
                    "Catalog" => $catalogCode,
                    "UnitId" => $uid,
                    "ssd" => $ssd,
                ));
                $commandDetails = $this->cataloghelper->buildCommand("ListDetailByUnit", array(
                    "Locale" => "ru_RU",
                    "Catalog" => $catalogCode,
                    "UnitId" => $uid,
                    "ssd" => $ssd,
                    "Localized" => true,
                ));

                $response = $this->cataloghelper->query(array($commandUnit, $commandImage, $commandDetails));

                if ($this->cataloghelper->getSoapError()) {
                    echo $this->cataloghelper->getSoapError();
                    print_r($response);
                    exit;
                }

                $this->view->pageType = "unit";
                $this->view->unit = $response->GetUnitInfo;
                $this->view->image = $response->ListImageMapByUnit;
                $this->view->details = $response->ListDetailsByUnit;
                $this->view->catalogCode = $catalogCode;
                $this->view->vid = $vid;
                $this->view->uid = $uid;
                $this->view->ssd = $ssd;
                $this->view->info = $catalogInfo;

                # Bread crumbs
                $breadCrumbs = array();
                $breadCrumbs[] = array(
                    "link" => $this->url->get("catalog"),
                    "title" => "Каталог",
                );
                $breadCrumbs[] = array(
                    "link" => $this->url->get("catalog", array(
                        "action" => "catalog",
                        "code" => $catalogCode,
                        "ssd" => "",
                    )),
                    "title" => $catalogInfo->attributes()["name"][0],
                );

                $breadCrumbs[] = array(
                    "link" => $this->url->get("catalog", array(
                        "action" => "units",
                        "code" => $catalogCode,
                        "vid" => $vid,
                        "ssd" => $ssd,
                    )),
                    "title" => $vehicleInfo->attributes()["name"][0],
                );
                $breadCrumbs[] = array(
                    "link" => $this->url->get("catalog", array(
                        "action" => "unit",
                        "code" => $catalogCode,
                        "vid" => $vid,
                        "uid" => $uid,
                        "ssd" => $ssd,
                    )),
                    "title" => "<b>" . $response->GetUnitInfo->row->attributes()["code"][0] . "</b>: " . $response->GetUnitInfo->row->attributes()["name"][0],
                );
                $this->view->breadCrumbs = $breadCrumbs;

                if ($this->auth->isAuthorized()) {
                    $auto = UserAuto::findFirst(array(
                                "conditions" => "userId = :userId: AND catalogId = :catalogId: AND vehicleId = :vehicleId:",
                                "bind" => array(
                                    "userId" => $this->auth->getCurrentId(),
                                    "catalogId" => $catalogCode,
                                    "vehicleId" => $vid,
                                ),
                    ));

                    if (!$auto) {
                        $auto = new UserAuto();
                        $auto->assign(array(
                            "userId" => $this->auth->getCurrentId(),
                            "catalogId" => $catalogCode,
                            "vehicleId" => $vid,
                            "ssd" => $ssd,
                        ));
                        $auto->save();
                    }
                }
                break;

            case "units":
                $catalogCode = $this->request->get("code");
                $vid = $this->request->get("vid", "int");
                $ssd = $this->request->get("ssd");
                $cid = $this->request->get("cid", "int");
                $cid = ($cid ? $cid : -1);

                $commandCatalog = $this->cataloghelper->buildCommand("GetCatalogInfo", array("Locale" => "ru_RU", "Catalog" => $catalogCode, "ssd" => $ssd));
                $commandVehicle = $this->cataloghelper->buildCommand("GetVehicleInfo", array("Locale" => "ru_RU", "Catalog" => $catalogCode, "VehicleId" => $vid, "ssd" => $ssd));
                $info = $this->cataloghelper->query(array($commandCatalog, $commandVehicle));

                $catalogInfo = $info->GetCatalogInfo->row;
                $vehicleInfo = $info->GetVehicleInfo->row;

                $commandCategories = $this->cataloghelper->buildCommand("ListCategories", array(
                    "Locale" => "ru_RU",
                    "Catalog" => $catalogCode,
                    "VehicleId" => $vid,
                    "CategoryId" => $cid,
                    "ssd" => $ssd,
                ));
                $commandUnits = $this->cataloghelper->buildCommand("ListUnits", array(
                    "Locale" => "ru_RU",
                    "Catalog" => $catalogCode,
                    "VehicleId" => $vid,
                    "CategoryId" => $cid,
                    "ssd" => $ssd,
                    "Localized" => true,
                ));

                $response = $this->cataloghelper->query(array($commandCategories, $commandUnits));


                if ($this->cataloghelper->getSoapError()) {
                    echo $this->cataloghelper->getSoapError();
                    print_r($response);
                    exit;
                }

                $this->view->pageType = "units";
                $this->view->categories = $response->ListCategories;
                $this->view->units = $response->ListUnits;
                $this->view->catalogCode = $catalogCode;
                $this->view->vid = $vid;
                $this->view->ssd = $ssd;
                $this->view->cid = $cid;
                $this->view->info = $catalogInfo;

                # Bread crumbs
                $breadCrumbs = array();
                $breadCrumbs[] = array(
                    "link" => $this->url->get("catalog"),
                    "title" => "Каталог",
                );
                $breadCrumbs[] = array(
                    "link" => $this->url->get("catalog", array(
                        "action" => "catalog",
                        "code" => $catalogCode,
                        "ssd" => "",
                    )),
                    "title" => $catalogInfo->attributes()["name"][0],
                );

                $breadCrumbs[] = array(
                    "link" => $this->url->get("catalog", array(
                        "action" => "units",
                        "code" => $catalogCode,
                        "vid" => $vid,
                        "ssd" => $ssd,
                    )),
                    "title" => $vehicleInfo->attributes()["name"][0],
                );
                $this->view->breadCrumbs = $breadCrumbs;

                if ($this->auth->isAuthorized()) {
                    $auto = UserAuto::findFirst(array(
                                "conditions" => "userId = :userId: AND catalogId = :catalogId: AND vehicleId = :vehicleId:",
                                "bind" => array(
                                    "userId" => $this->auth->getCurrentId(),
                                    "catalogId" => $catalogCode,
                                    "vehicleId" => $vid,
                                ),
                    ));

                    if (!$auto) {
                        $auto = new UserAuto();
                        $auto->assign(array(
                            "userId" => $this->auth->getCurrentId(),
                            "catalogId" => $catalogCode,
                            "vehicleId" => $vid,
                            "ssd" => $ssd,
                        ));
                        $auto->save();
                    }
                }
                break;

            case "find":
                $catalogCode = $this->request->get("code");
                $vid = $this->request->get("vid");
                $ssd = $this->request->get("ssd");

                $commandCatalog = $this->cataloghelper->buildCommand("GetCatalogInfo", array("Locale" => "ru_RU", "Catalog" => $catalogCode, "ssd" => $ssd));
                $commandVehicle = $this->cataloghelper->buildCommand("GetVehicleInfo", array("Locale" => "ru_RU", "Catalog" => $catalogCode, "VehicleId" => $vid, "ssd" => $ssd));
                $info = $this->cataloghelper->query(array($commandCatalog, $commandVehicle));

                $catalogInfo = $info->GetCatalogInfo->row;
                $vehicleInfo = $info->GetVehicleInfo->row;

                $command = $this->cataloghelper->buildCommand("ListQuickGroup", array("Locale" => "ru_RU", "Catalog" => $catalogCode, "VehicleId" => $vid, "ssd" => $ssd));
                $response = $this->cataloghelper->query($command);

                $this->view->pageType = "find";
                $this->view->response = $response;
                $this->view->list = $response->ListQuickGroups;
                $this->view->catalogCode = $catalogCode;
                $this->view->vid = $vid;
                $this->view->ssd = $ssd;

                # Bread crumbs
                $breadCrumbs = array();
                $breadCrumbs[] = array(
                    "link" => $this->url->get("catalog"),
                    "title" => "Каталог",
                );
                $breadCrumbs[] = array(
                    "link" => $this->url->get("catalog", array(
                        "action" => "catalog",
                        "code" => $catalogCode,
                        "ssd" => "",
                    )),
                    "title" => $catalogInfo->attributes()["name"][0],
                );

                $breadCrumbs[] = array(
                    "link" => $this->url->get("catalog", array(
                        "action" => "find",
                        "code" => $catalogCode,
                        "vid" => $vid,
                        "ssd" => $ssd,
                    )),
                    "title" => $vehicleInfo->attributes()["name"][0],
                );
                $this->view->breadCrumbs = $breadCrumbs;

                // print_r($this->auth->isAuthorized());
                // exit;

                if ($this->auth->isAuthorized()) {
                    $auto = UserAuto::findFirst(array(
                                "conditions" => "userId = :userId: AND catalogId = :catalogId: AND vehicleId = :vehicleId:",
                                "bind" => array(
                                    "userId" => $this->auth->getCurrentId(),
                                    "catalogId" => $catalogCode,
                                    "vehicleId" => $vid,
                                ),
                    ));
                    if (!$auto) {
                        $auto = new UserAuto();
                        $auto->assign(array(
                            "userId" => $this->auth->getCurrentId(),
                            "catalogId" => $catalogCode,
                            "vehicleId" => $vid,
                            "ssd" => $ssd,
                        ));
                        $auto->save();
                    }
                }
                break;

            case "model":
                $catalogCode = $this->request->get("code");
                $ssd = $this->request->get("ssd");

                $commandCatalog = $this->cataloghelper->buildCommand("GetCatalogInfo", array("Locale" => "ru_RU", "Catalog" => $catalogCode, "ssd" => ""));
                $info = $this->cataloghelper->query($commandCatalog);

                $catalogInfo = $info->GetCatalogInfo->row;

                if (isset($catalogInfo->attributes()["supportquickgroups"]) AND $catalogInfo->attributes()["supportquickgroups"][0])
                    $newxtaction = "find";
                else
                    $newxtaction = "units";

                $this->view->newxtaction = $newxtaction;

                $command = $this->cataloghelper->buildCommand("FindVehicleByWizard2", array("Catalog" => $catalogCode, "Locale" => "ru_RU", "ssd" => $ssd));
                $model = $this->cataloghelper->query($command);

                $this->view->pageType = "model";
                $this->view->model = $model->FindVehicleByWizard2;
                $this->view->catalogCode = $catalogCode;
                $this->view->info = $catalogInfo;

                # Bread crumbs
                $breadCrumbs = array();
                $breadCrumbs[] = array(
                    "link" => $this->url->get("catalog"),
                    "title" => "Каталог",
                );
                $breadCrumbs[] = array(
                    "link" => $this->url->get("catalog", array(
                        "action" => "catalog",
                        "code" => $catalogCode,
                        "ssd" => "",
                    )),
                    "title" => $catalogInfo->attributes()["name"][0],
                );
                $this->view->breadCrumbs = $breadCrumbs;
                break;

            case "catalog":
                $catalogCode = $this->request->get("code");
                $ssd = $this->request->get("ssd");

                if ($catalogCode == "") {
                    return $this->response->redirect("catalog");
                }

                $commandCatalog = $this->cataloghelper->buildCommand("GetCatalogInfo", array("Locale" => "ru_RU", "Catalog" => $catalogCode, "ssd" => ""));
                $commandCatalogs = $this->cataloghelper->buildCommand("ListCatalogs", array(
                    "Locale" => "ru_RU",
                    "ssd" => ""
                ));
                $info = $this->cataloghelper->query(array($commandCatalog, $commandCatalogs));

                $this->view->catalogs = $info->ListCatalogs;

                $catalogInfo = $info->GetCatalogInfo->row;

                if (isset($catalogInfo->attributes()["supportparameteridentification2"]) AND $catalogInfo->attributes()["supportparameteridentification2"][0]) {
                    $command = $this->cataloghelper->buildCommand("GetWizard2", array("Catalog" => $catalogCode, "Locale" => "ru_RU", "ssd" => $ssd));
                    $catalog = $this->cataloghelper->query($command);
                }

                $this->view->pageType = "catalog";
                $this->view->info = $catalogInfo;
                $this->view->catalog = $catalog->GetWizard2;
                $this->view->catalogCode = $catalogCode;
                $this->view->ssd = $ssd;

                $hasVinSearch = (isset($catalogInfo->attributes()["supportvinsearch"]) AND $catalogInfo->attributes()["supportvinsearch"][0] ? true : false);
                $hasFrameSearch = (isset($catalogInfo->attributes()["supportframesearch"]) AND $catalogInfo->attributes()["supportframesearch"][0] ? true : false);

                $this->view->hasVinSearch = $hasVinSearch;
                $this->view->hasFrameSearch = $hasFrameSearch;

                # Bread crumbs
                $breadCrumbs = array();
                $breadCrumbs[] = array(
                    "link" => $this->url->get("catalog"),
                    "title" => "Каталог",
                );
                $breadCrumbs[] = array(
                    "link" => $this->url->get("catalog", array(
                        "action" => "catalog",
                        "code" => $catalogCode,
                        "ssd" => "",
                    )),
                    "title" => $catalogInfo->attributes()["name"][0],
                );
                $this->view->breadCrumbs = $breadCrumbs;
                break;

            case "vin":
                $vin = $this->request->get("vin");
                $catalogCode = $this->request->get("code");

                $commandCatalogs = $this->cataloghelper->buildCommand("ListCatalogs", array(
                    "Locale" => "ru_RU",
                    "ssd" => ""
                ));
                $requestCatalogs = $this->cataloghelper->query($commandCatalogs);

                $this->view->catalogs = $requestCatalogs->ListCatalogs;

                $command = $this->cataloghelper->buildCommand("FindVehicleByVIN", array(
                    "Locale" => "ru_RU",
                    "Catalog" => $catalogCode,
                    "VIN" => $vin,
                    "ssd" => "",
                    "Localized" => true,
                ));
                $request = $this->cataloghelper->query($command);
                $results = $request->FindVehicleByVIN;

                $this->view->pageType = "vin";
                $this->view->vin = $vin;
                $this->view->results = $results;

                if ($results AND count($results->children()) > 0) {
                    $item = $results->children()[0];
                    $model = strtoupper($item->attributes()['name']);
                    $vin = strtoupper($vin);

                    $history = CatalogUserVin::findFirst(array(
                                "conditions" => "userId = :userId: AND vin = :vin: AND model = :model:",
                                "bind" => array(
                                    "userId" => $this->auth->getCurrentId(),
                                    "vin" => $vin,
                                    "model" => $model,
                                ),
                    ));

                    if (!$history) {
                        $history = new CatalogUserVin();
                        $history->userId = $this->auth->getCurrentId();
                        $history->vin = $vin;
                        $history->model = $model;
                    }
                    $history->save();
                }

                # Bread crumbs
                $breadCrumbs = array();
                $breadCrumbs[] = array(
                    "link" => $this->url->get("catalog"),
                    "title" => "Каталог",
                );
                $breadCrumbs[] = array(
                    "link" => $this->url->get("catalog", array(
                        "action" => "vin",
                        "vin" => $vin,
                    )),
                    "title" => "Поиск по VIN номеру",
                );

                $this->view->breadCrumbs = $breadCrumbs;

                break;

            case "frame":
                $frameCode = $this->request->get("frame");
                $frameNumber = $this->request->get("frame-number");
                $catalogCode = $this->request->get("code");
                $frame = $frameCode.'-'.$frameNumber;

                $commandCatalogs = $this->cataloghelper->buildCommand("ListCatalogs", array(
                    "Locale" => "ru_RU",
                    "ssd" => ""
                ));
                $request = $this->cataloghelper->query($commandCatalogs);

                $this->view->catalogs = $request->ListCatalogs;

                $command = $this->cataloghelper->buildCommand("FindVehicleByFrame", array(
                    "Locale" => "ru_RU",
                    "Catalog" => $catalogCode,
                    "Frame" => $frameCode,
                    "FrameNo" => $frameNumber,
                    "ssd" => "",
                    "Localized" => true,
                ));
                $request = $this->cataloghelper->query($command);

                $results = $request->FindVehicleByFrame;
                $this->view->pageType = "frame";
                $this->view->results = $results;
                $this->view->frame = $frameCode;
                $this->view->frameNumber = $frameNumber;

                # Bread crumbs
                $breadCrumbs = array();
                $breadCrumbs[] = array(
                    "link" => $this->url->get("catalog"),
                    "title" => "Каталог",
                );
                $breadCrumbs[] = array(
                    "link" => $this->url->get("catalog", array(
                        "action" => "frame",
                        "frame" => $frame,
                        "frameNo" => $frameNumber,
                    )),
                    "title" => "Поиск по номеру кузова",
                );


                if ($results ) {
                    $item = $results->children()[0];
                    if (is_null($item))
                    {
                        $model = "undefined model";
                    } else {
                        $model = strtoupper($item->attributes()['name']);
                    }
                    $frame = strtoupper($frame);

                    $history = CatalogUserFrame::findFirst(array(
                                "conditions" => "userId = :userId: AND frame = :frame: AND model = :model:",
                                "bind" => array(
                                    "userId" => $this->auth->getCurrentId(),
                                    "frame" => $frame,
                                    "model" => $model,
                                ),
                    ));

                    if (!$history) {
                        $history = new CatalogUserFrame();
                        $history->userId = $this->auth->getCurrentId();
                        $history->frame = $frame;
                        $history->model = $model;
                    }
                    $history->save();
                }

                $this->view->breadCrumbs = $breadCrumbs;
                break;

            case "":
                $command = $this->cataloghelper->buildCommand("ListCatalogs", array("Locale" => "ru_RU", "ssd" => ""));
                $catalogs = $this->cataloghelper->query($command);

                $this->view->pageType = "catalogs";
                $this->view->catalogs = $catalogs->ListCatalogs;

                # Bread crumbs
                $breadCrumbs = array();
                $breadCrumbs[] = array(
                    "link" => $this->url->get("catalog"),
                    "title" => "Каталог",
                );
                $this->view->breadCrumbs = $breadCrumbs;
                break;

            default:
                return $this->dispatcher->forward(array(
                            "controller" => "error",
                            "action" => "notFound",
                ));
                break;
        }
    }

    public function ajaxAction() {
        $this->view->disable();
        $this->response->setContentType("application/json", "UTF-8");

        switch ($this->request->get("action")) {
            case "loadfind":
                $quickgroupid = $this->request->get("id", "int");
                $catalogCode = $this->request->get("code");
                $vid = $this->request->get("vid");
                $ssd = $this->request->get("ssd");

                $command = $this->cataloghelper->buildCommand("ListQuickDetail", array("Locale" => "ru_RU", "Catalog" => $catalogCode, "VehicleId" => $vid, "QuickGroupId" => $quickgroupid, "All" => 0, "ssd" => $ssd));
                $response = $this->cataloghelper->query($command);

                return $this->response->setJsonContent(array(
                            "error" => false,
                            "data" => $response->ListQuickDetail,
                            "params" => array(
                                "quickgroupid" => $quickgroupid,
                                "code" => $catalogCode,
                                "vid" => $vid,
                                "ssd" => $ssd,
                            ),
                ));
                break;

            default:
                return $this->response->setJsonContent(array("error" => true, "errorMsg" => "Action not found"));
                break;
        }
    }

    public function unique_multidim_array($array, $key) {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach ($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

    public function partsAction() {
        $this->setTitle("Поиск запчастей");

        $commandCatalogs = $this->cataloghelper->buildCommand("ListCatalogs", array(
            "Locale" => "ru_RU",
            "ssd" => ""
        ));

        $banners = ContentBanner::find();

        $request = $this->cataloghelper->query($commandCatalogs);
        $this->view->catalogs = $request->ListCatalogs;

        $fullQuery = str_replace(array("-", " ", ","), "", $this->request->get("query"));
        $brand = trim($this->request->get("brand"));
        $results = array();
        $resultsByBrand = array();
        $resultsByArticle = array();


        if ($fullQuery != "") {
            $query = $fullQuery;
            if ($this->auth->isAuthorized()) {
                $history = CatalogUserPart::findFirst(array(
                            "conditions" => "userId = :userId: AND article = :article:",
                            "bind" => array(
                                "userId" => $this->auth->getCurrentId(),
                                "article" => strtoupper($query),
                            ),
                ));

                if (!$history) {
                    $history = new CatalogUserPart();
                    $history->userId = $this->auth->getCurrentId();
                    $history->article = strtoupper($query);
                }

                $history->save();
            }

            $results = $this->catalog->findAll($fullQuery, $brand);
            if ($fullQuery !== $query) {
                $resultsShort = $this->catalog->findAll($query, $brand);
                $results = array_merge($results, $resultsShort);
            }

            foreach ($results as $result) {
                if (!isset($resultsByBrand[$result["brand"]]))
                    $resultsByBrand[$result["brand"]] = array();

                $localItemSupplier = CatalogLocalItem::find(array(
                    "conditions" => "externalId = :externalId:",
                    "bind" => array(
                        "externalId" => substr($result['code'], strpos($result['code'], "@") + 1),
                    ),
                ));
                foreach($localItemSupplier AS $item) {
                    $localSupplier = CatalogLocalSupplier::find(array(
                        "conditions" => "code = :code:",
                        "bind" => array(
                            "code" => $item->supplier,
                        ),
                    ));

                }


                $articleCode = strtoupper(str_replace(array(" ", "-", "/"), "", $result["article"]));
                $code = strtoupper($result["brand"]) . "@" . $articleCode;

                if (!isset($resultsByArticle[$code]))
                    $resultsByArticle[$code] = array(
                        "article" => $result["article"],
                        "articleCode" => $articleCode,
                        "brand" => $result["brand"],
                        "title" => $result["title"],
                        "photo" => $result["photo"],
                        "items" => array($code => $result),
                    );
                else {
                    $items = $resultsByArticle[$code]['items'][$code]['stocks'];
                    $resultsByArticle[$code]['items'][$code]['stocks'] = $this->unique_multidim_array(array_merge($result['stocks'], $items), 'stockItemId');
                }
                $resultsByBrand[$result["brand"]][] = $result;
                //$resultsByArticle[$code]['items'][$code] = $result;
                $contractCode[] = $result['code'];
            }

            $queryCode = strtoupper(str_replace(array(" ", "-", "/"), "", $query));
            $this->view->queryCode = $queryCode;
            $this->view->fullQuery = $fullQuery;
            $this->view->queryBrand = $brand;
            $this->view->localSupplier = $localSupplier;

            $resultsSorted = array();

            foreach ($resultsByArticle AS $result) {
                if ($brand != "") {
                    if ($result["brand"] == $brand AND strtoupper(str_replace(array(" ", "-", "/"), "", $result["article"])) == $queryCode)
                        $resultsSorted[] = $result;
                }
                else {
                    if (strtoupper(str_replace(array(" ", "-", "/"), "", $result["article"])) == $queryCode)
                        $resultsSorted[] = $result;
                }
            }

            $this->view->variantsCount = count($resultsSorted);

            foreach ($resultsByArticle AS $result) {
                if ($brand != "") {
                    if ($result["brand"] != $brand OR strtoupper(str_replace(array(" ", "-", "/"), "", $result["article"])) != $queryCode)
                        $resultsSorted[] = $result;
                }
                else {
                    if (strtoupper(str_replace(array(" ", "-", "/"), "", $result["article"])) != $queryCode)
                        $resultsSorted[] = $result;
                }
            }

            $resultsByArticle = $resultsSorted;
        }

        $this->view->query = $fullQuery;
        $this->view->results = $results;
        $this->view->resultsByBrand = $resultsByBrand;
        $this->view->resultsByArticle = $resultsByArticle;
        $this->view->banners = $banners;
    }

}
