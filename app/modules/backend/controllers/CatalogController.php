<?php

namespace Molly\Backend\Controllers;

use Molly\Models\CatalogSupplier,
    Molly\Models\CatalogLocalItem,
    Molly\Models\CatalogLocalSupplier,
    Phalcon\Http\Request\File,
    Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
;

class CatalogController extends ControllerBase {

    public function indexAction() {
        $this->setTitle("Каталог");

        switch ($this->request->get("action")) {
            // AutoTrade
            // case "subsection":
            // 	$this->view->pageType = "subsection";
            // 	$this->view->subSectionId = $this->request->get("id");
            // 	$this->view->sectionId = $this->request->get("sectionId");
            // 	$this->view->catalogId = $this->request->get("catalogId");
            // 	$this->view->items = $this->catalog->getApi("autotrade")->getCatalogItems($this->view->catalogId, $this->view->sectionId, $this->view->subSectionId);
            // 	break;
            // 
            // case "section":
            // 	$this->view->pageType = "section";
            // 	$this->view->sectionId = $this->request->get("id");
            // 	$this->view->catalogId = $this->request->get("catalogId");
            // 	$this->view->subsections = $this->catalog->getApi("autotrade")->getCatalogSubSections($this->view->catalogId, $this->view->sectionId);
            // 	break;
            // 
            // case "catalog":
            // 	$this->view->pageType = "catalog";
            // 	$this->view->catalogId = $this->request->get("id");
            // 	$this->view->sections = $this->catalog->getApi("autotrade")->getCatalogSections($this->view->catalogId);
            // 	break;
            // 
            // default:
            // 	$this->view->pageType = "catalogs";
            // 	$this->view->catalogs = $this->catalog->getApi("autotrade")->getCatalogs();
            // 	break;

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
                        "ssd" => $ssd,
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
                        "uid" => $vid,
                        "ssd" => $ssd,
                    )),
                    "title" => "<b>" . $response->GetUnitInfo->row->attributes()["code"][0] . "</b>: " . $response->GetUnitInfo->row->attributes()["name"][0],
                );
                $this->view->breadCrumbs = $breadCrumbs;
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
                        "ssd" => $ssd,
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
                        "ssd" => $ssd,
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
                        "ssd" => $ssd,
                    )),
                    "title" => $catalogInfo->attributes()["name"][0],
                );
                $this->view->breadCrumbs = $breadCrumbs;
                break;

            case "catalog":
                $catalogCode = $this->request->get("code");
                $ssd = $this->request->get("ssd");

                $commandCatalog = $this->cataloghelper->buildCommand("GetCatalogInfo", array("Locale" => "ru_RU", "Catalog" => $catalogCode, "ssd" => ""));
                $info = $this->cataloghelper->query($commandCatalog);

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
                        "ssd" => $ssd,
                    )),
                    "title" => $catalogInfo->attributes()["name"][0],
                );
                $this->view->breadCrumbs = $breadCrumbs;
                break;

            case "vin":
                $vin = $this->request->get("vin");
                $catalogCode = $this->request->get("code");

                $command = $this->cataloghelper->buildCommand("FindVehicleByVIN", array(
                    "Locale" => "ru_RU",
                    "Catalog" => $catalogCode,
                    "VIN" => $vin,
                    "ssd" => "",
                    "Localized" => true,
                ));
                $request = $this->cataloghelper->query($command);

                $this->view->pageType = "vin";
                $this->view->vin = $vin;
                $this->view->results = $request->FindVehicleByVIN;

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
                $frame = $this->request->get("frame");
                $frameNo = $this->request->get("frameNo");

                $command = $this->cataloghelper->buildCommand("FindVehicleByFrame", array(
                    "Locale" => "ru_RU",
                    "Catalog" => $catalogCode,
                    "Frame" => $frame,
                    "FrameNo" => $frameNo,
                    "ssd" => "",
                    "Localized" => true,
                ));
                $request = $this->cataloghelper->query($command);

                $this->view->pageType = "frame";
                $this->view->results = $request->FindVehicleByFrame;
                $this->view->frame = $frame;
                $this->view->frameNo = $frameNo;

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
                        "frameNo" => $frameNo,
                    )),
                    "title" => "Поиск по номеру кузова",
                );
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

    public function suppliersAction() {
        $this->setTitle("Поставщики");

        $suppliers = $this->catalog->getSuppliers(true);

        if ($this->request->isPost() AND $this->request->getPost("form") == "api") {
            $actives = $this->request->getPost("active");
            $markups = $this->request->getPost("markup");
            $storagesActives = $this->request->getPost("storagesActives");
            $storagesRates = $this->request->getPost("storagesRate");
            $storagesStat = $this->request->getPost("storagesStat");
            $storages = $this->request->getPost("storages");
            $brands = $this->request->getPost("brands");

            foreach ($suppliers AS $supplier) {
                if (!$supplier["data"]) {
                    $supplier["data"] = new CatalogSupplier();
                    $supplier["data"]->assign(array(
                        "code" => $supplier["code"],
                    ));
                }

                $supplier["data"]->active = (isset($actives[$supplier["code"]]) && $actives[$supplier["code"]] == "Y" ? "Y" : "N");

                $supplierMarkups = array();

                if (!empty($markups[$supplier["code"]])) {
                    foreach ($markups[$supplier["code"]] AS $markup) {
                        if (floatval($markup["value"]) <= 0)
                            continue;

                        if (floatval($markup["percent"]) <= 0 AND floatval($markup["fixed"]) <= 0)
                            continue;

                        $supplierMarkups[] = array(
                            "value" => floatval($markup["value"]),
                            "percent" => floatval($markup["percent"]),
                            "fixed" => floatval($markup["fixed"]),
                        );
                    }
                }

                usort($supplierMarkups, function($a, $b) {
                    if ($a["value"] == $b["value"])
                        return 0;
                    return ($a["value"] < $b["value"]) ? -1 : 1;
                });

                $supplier["data"]->markup = json_encode($supplierMarkups);

                $supplierStorages = array();

                if (!empty($storages[$supplier["code"]])) {
                    foreach ($storages[$supplier["code"]] AS $storageId => $up) {
                        $active = (isset($storagesActives[$supplier["code"]][$storageId]) && $storagesActives[$supplier["code"]][$storageId] ? true : false);
                        $rate = (isset($storagesRates[$supplier["code"]][$storageId]) ? intval($storagesRates[$supplier["code"]][$storageId]) : 1);
                        $stat = (isset($storagesStat[$supplier["code"]][$storageId]) ? $storagesStat[$supplier["code"]][$storageId] : array());

                        $finalStat = array(
                            "all" => (isset($stat["all"]) ? $stat["all"] : 0),
                            "before" => (isset($stat["before"]) ? $stat["before"] : 0),
                            "intime" => (isset($stat["intime"]) ? $stat["intime"] : 0),
                            "fail" => (isset($stat["fail"]) ? $stat["fail"] : 0),
                        );

                        $supplierStorages[] = array(
                            "id" => intval($storageId),
                            "active" => $active,
                            "up" => intval($up),
                            "rate" => $rate,
                            "stat" => $finalStat,
                        );
                    }
                }

                $supplier["data"]->storages = json_encode($supplierStorages);

                $supplierBrands = array();

                if (!empty($brands[$supplier["code"]])) {
                    foreach ($brands[$supplier["code"]] AS $brand) {
                        if (trim($brand["code"]) == "")
                            continue;

                        $brandItem = array(
                            "code" => $brand["code"],
                            "markups" => array(),
                        );

                        if (!empty($brand["markups"])) {
                            foreach ($brand["markups"] AS $markup) {
                                if (floatval($markup["value"]) <= 0)
                                    continue;

                                if (floatval($markup["percent"]) <= 0 AND floatval($markup["fixed"]) <= 0)
                                    continue;

                                $brandItem["markups"][] = array(
                                    "value" => floatval($markup["value"]),
                                    "percent" => floatval($markup["percent"]),
                                    "fixed" => floatval($markup["fixed"]),
                                );
                            }

                            usort($brandItem["markups"], function($a, $b) {
                                if ($a["value"] == $b["value"])
                                    return 0;
                                return ($a["value"] < $b["value"]) ? -1 : 1;
                            });
                        }

                        $supplierBrands[] = $brandItem;
                    }
                }

                $supplier["data"]->brands = json_encode($supplierBrands);

                $supplier["data"]->save();
            }

            return $this->response->redirect("catalog/suppliers");
        }

        if ($this->request->isPost() AND $this->request->getPost("form") == "local") {
            $data = $this->request->getPost("local");
            if (!is_array($data))
                $data = array();

            $ids = array();

            foreach ($data AS $localSupplier) {
                $db = false;

                if (isset($localSupplier["id"])) {
                    $db = CatalogLocalSupplier::findFirst(array(
                                "conditions" => "id = :id:",
                                "bind" => array(
                                    "id" => $localSupplier["id"],
                                ),
                    ));
                }

                if (!$db) {
                    $db = new CatalogLocalSupplier();
                }

                $supplierMarkups = array();

                if (!empty($localSupplier["markups"])) {
                    foreach ($localSupplier["markups"] AS $markup) {
                        if (floatval($markup["value"]) < 0)
                            continue;

                        if (floatval($markup["percent"]) <= 0 AND floatval($markup["fixed"]) <= 0)
                            continue;

                        $supplierMarkups[] = array(
                            "value" => floatval($markup["value"]),
                            "percent" => floatval($markup["percent"]),
                            "fixed" => floatval($markup["fixed"]),
                        );
                    }
                }

                usort($supplierMarkups, function($a, $b) {
                    if ($a["value"] == $b["value"])
                        return 0;
                    return ($a["value"] < $b["value"]) ? -1 : 1;
                });

                $db->markups = json_encode($supplierMarkups);
                $db->active = (isset($localSupplier["active"]) && $localSupplier["active"] == "Y" ? "Y" : "N");
                $db->contract = (isset($localSupplier["contract"]) && $localSupplier["contract"] == "Y" ? "Y" : "N");

                $dateActiveTo = \DateTime::createFromFormat("d.m.Y H:i", $localSupplier["activeTo"]);

                if ($dateActiveTo)
                    $db->activeTo = $dateActiveTo->format("Y-m-d H:i:s");
                else
                    $db->activeTo = "";

                $stat = $localSupplier["deliveryStat"];

                $finalStat = array(
                    "all" => (isset($stat["all"]) ? $stat["all"] : 0),
                    "before" => (isset($stat["before"]) ? $stat["before"] : 0),
                    "intime" => (isset($stat["intime"]) ? $stat["intime"] : 0),
                    "fail" => (isset($stat["fail"]) ? $stat["fail"] : 0),
                );

                $db->assign(array(
                    "code" => $localSupplier["code"],
                    "title" => $localSupplier["title"],
                    "stockName" => $localSupplier["stockName"],
                    "delivery" => $localSupplier["delivery"],
                    "deliveryRate" => $localSupplier["deliveryRate"],
                    "deliveryStat" => json_encode($finalStat),
                ));

                if ($db->save())
                    $ids[] = $db->id;
            }

            $builder = $this->modelsManager->createBuilder()
                    ->from("Molly\Models\CatalogLocalSupplier");

            if (!empty($ids))
                $builder->notInWhere("id", $ids);

            $items = $builder->getQuery()->execute();

            foreach ($items AS $item)
                $item->delete();

            return $this->response->redirect("catalog/suppliers");
        }

        $this->view->suppliersLocal = CatalogLocalSupplier::find();
        $this->view->suppliers = $suppliers;
    }

    public function partsAction() {
        $this->setTitle("Поиск запчастей");

        $query = $this->request->get("query");
        $resultsByArticle = array();

        if ($query != "") {
            $results = $this->catalog->findAll($query);

            foreach ($results AS $result) {
                $articleCode = strtoupper(str_replace(array(" ", "-", "/"), "", $result["article"]));

                if (!isset($resultsByArticle[strtoupper($result["brand"]) . "@" . $articleCode]))
                    $resultsByArticle[strtoupper($result["brand"]) . "@" . $articleCode] = array(
                        "article" => $result["article"],
                        "articleCode" => $articleCode,
                        "brand" => $result["brand"],
                        "title" => $result["title"],
                        "photo" => $result["photo"],
                        "items" => array(),
                    );

                $resultsByArticle[strtoupper($result["brand"]) . "@" . $articleCode]["items"][] = $result;
            }

            $queryCode = strtoupper(str_replace(array(" ", "-", "/"), "", $query));
            $this->view->queryCode = $queryCode;

            $resultsSorted = array();

            foreach ($resultsByArticle AS $result) {
                if (strtoupper(str_replace(array(" ", "-", "/"), "", $result["article"])) == $queryCode)
                    $resultsSorted[] = $result;
            }

            foreach ($resultsByArticle AS $result) {
                if (strtoupper(str_replace(array(" ", "-", "/"), "", $result["article"])) != $queryCode)
                    $resultsSorted[] = $result;
            }

            $resultsByArticle = $resultsSorted;
        }

        $this->view->query = $query;
        $this->view->results = $results;
        $this->view->resultsByArticle = $resultsByArticle;
    }

    public function localAction() {
        $this->setTitle("Прайс лист");

        if ($this->request->isPost()) {
            switch ($this->request->getPost("bulkAction")) {
                case "0":
                    # Nothing
                    break;
                case "1":
                    # Deleting
                    $bulkList = $this->request->getPost("bulk");

                    if (empty($bulkList))
                        break;

                    $items = $this->modelsManager->createBuilder()
                                    ->from("Molly\Models\CatalogLocalItem")
                                    ->inWhere("id", $bulkList)
                                    ->getQuery()->execute();

                    if (empty($items))
                        break;

                    foreach ($items AS $item)
                        $item->delete();
                    break;
            }

            return $this->response->redirect($_SERVER["HTTP_REFERER"]);
        }

        $items = $this->modelsManager->createBuilder()
                ->from("Molly\Models\CatalogLocalItem")
                ->where("1 = 1")
                ->orderBy("id ASC");

        $currentSupplier = $this->request->get("supplier");

        if ($currentSupplier != "") {
            $items->andWhere("supplier = :supplier:", array(
                "supplier" => $currentSupplier,
            ));
        }

        $searchQuery = trim($this->request->get("query"));

        if ($searchQuery != "") {
            $items->andWhere("oem LIKE '%" . $searchQuery . "%' OR title LIKE '%" . $searchQuery . "%'");
        }

        $this->view->suppliers = CatalogLocalSupplier::find();
        $this->view->currentSupplier = $currentSupplier;
        $this->view->searchQuery = $searchQuery;

        $paginator = new PaginatorQueryBuilder(array(
            "builder" => $items,
            "limit" => 25,
            "page" => $this->request->getQuery("page", "int"),
        ));

        $this->view->itemsPage = $paginator->getPaginate();
    }

    public function ajaxAction() {
        $this->view->disable();
        $this->response->setContentType("application/json", "UTF-8");

        switch ($this->request->get("action")) {
            case "import":
                switch ($this->request->get("step")) {
                    case "first":
                        $supplier = CatalogLocalSupplier::findFirst(array(
                                    "conditions" => "code = :code:",
                                    "bind" => array(
                                        "code" => $this->request->get("supplier"),
                                    ),
                        ));

                        if (!$supplier)
                            return $this->response->setJsonContent(array("error" => true, "errorMsg" => "Ошибка, поставщик не выбран"));

                        $file = new File($_FILES["file"]);

                        if (!$file->isUploadedFile())
                            return $this->response->setJsonContent(array("error" => true, "errorMsg" => "Ошибка, файл не загружен"));

                        if (!in_array(strtolower($file->getExtension()), array("xls", "xlsx")))
                            return $this->response->setJsonContent(array("error" => true, "errorMsg" => "Ошибка, файл не excel", "t" => $file->getExtension(), "rt" => $file->getRealType()));

                        $objPHPExcel = \PHPExcel_IOFactory::load($file->getTempName());

                        $rowsData = array();

                        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
                            foreach ($worksheet->getRowIterator() as $rkey => $row) {
                                if ($rkey > 10)
                                    break;

                                $rowsData[$rkey] = array();

                                $cellIterator = $row->getCellIterator();
                                $cellIterator->setIterateOnlyExistingCells(false);

                                foreach ($cellIterator as $ckey => $cell) {
                                    if (is_null($cell))
                                        continue;

                                    $rowsData[$rkey][] = array(
                                        "key" => $ckey,
                                        "value" => $cell->getCalculatedValue(),
                                    );
                                }
                            }

                            break;
                        }

                        return $this->response->setJsonContent(array(
                                    "error" => false,
                                    "data" => array(
                                        "rows" => $rowsData,
                                    ),
                        ));
                        break;

                    case "second":
                        $supplier = CatalogLocalSupplier::findFirst(array(
                                    "conditions" => "code = :code:",
                                    "bind" => array(
                                        "code" => $this->request->get("supplier"),
                                    ),
                        ));

                        if (!$supplier)
                            return $this->response->setJsonContent(array("error" => true, "errorMsg" => "Ошибка, поставщик не выбран"));

                        $file = new File($_FILES["file"]);

                        if (!$file->isUploadedFile())
                            return $this->response->setJsonContent(array("error" => true, "errorMsg" => "Ошибка, файл не загружен"));

                        if (!in_array($file->getExtension(), array("xls", "xlsx")))
                            return $this->response->setJsonContent(array("error" => true, "errorMsg" => "Ошибка, файл не excel", "t" => $file->getExtension(), "rt" => $file->getRealType()));

                        $objPHPExcel = \PHPExcel_IOFactory::load($file->getTempName());

                        $config = $this->request->getPost("config");

                        foreach ($config AS $valcheck) {
                            if (trim($valcheck) == "") {
                                return $this->response->setJsonContent(array(
                                            "error" => true,
                                            "errorMsg" => "Заполните все поля параметров таблицы",
                                ));
                            }
                        }

                        $result = array(
                            "countAll" => 0,
                            "countSuccess" => 0,
                            "countFailed" => 0,
                            "countRemoved" => 0,
                        );

                        $error = array();
                        
                        $temporaryHash = md5($file->getTempName());

                        $fields = array(
                            "supplier" => array(
                                "value" => $supplier->code,
                            ),
                            "externalId" => array(
                                "cell" => $config["externalId"],
                            ),
                            "hash" => array(
                                "value" => $temporaryHash,
                            ),
                            "oem" => array(
                                "cell" => $config["oem"],
                            ),
                            "brand" => array(
                                "cell" => $config["brand"],
                                "default" => "N/A",
                            ),
                            "title" => array(
                                "cell" => $config["title"],
                            ),
                            "count" => array(
                                "cell" => $config["count"],
                            ),
                            "price" => array(
                                "cell" => $config["price"],
                            ),
                            "date" => array(
                                "value" => date("d.m.Y")
                            ),
                        );
                        set_time_limit(0);

                        $catalogLocalItem = CatalogLocalItem::find(array(
                            "conditions" => "supplier = :supplier:",
                            "bind" => array(
                                "supplier" => $supplier->code,
                            ),
                        ));
                        $catalogLocalItem->delete();

                        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
                            foreach ($worksheet->getRowIterator() as $rkey => $row) {
                                if ($rkey <= $config["start"])
                                    continue;

                                $result["countAll"] ++;

                                $cellIterator = $row->getCellIterator();
                                $cellIterator->setIterateOnlyExistingCells(false);

                                $rowData = array();
                                $cells = array();

                                foreach ($cellIterator as $ckey => $cell) {
                                    $cells[$ckey] = $cell->getCalculatedValue();
                                }

                                foreach ($fields AS $code => $field) {
                                    if (isset($field["value"]))
                                        $rowData[$code] = $field["value"];
                                    else {
                                        if(trim($code) == "count" && trim($cells['H']) == "Есть") {
                                            $rowData[$code] = 2;
                                        } else if(trim($code) == "count" && strpos($cells['H'], 'пути') !== false) {
                                            $rowData[$code] = 0;
                                        } else if(trim($code) == "count" && strpos($cells['F'], '>20') !== false) {
                                            $rowData[$code] = 20;
                                        } else if(trim($code) == "oem" || trim($code) == "externalId") {
                                            $rowData[$code] = str_replace(array('-', ' '),'', str_replace(array(';', '/'), ',', trim($cells[$field["cell"]])));
                                        } else {
                                            $rowData[$code] = trim($cells[$field["cell"]]);
                                        }
                                        if (isset($field["default"]) AND !$rowData[$code]) {
                                            $rowData[$code] = $field["default"];
                                        }
                                    }
                                }

//                                # Update if exists
//                                $catalogItem = CatalogLocalItem::findFirst(array(
//                                            "conditions" => "supplier = :supplier: AND externalId = :externalId:",
//                                            "bind" => array(
//                                                "supplier" => $rowData["supplier"],
//                                                "externalId" => $rowData["externalId"],
//                                            ),
//                                ));

                                # Create if no exists
//                                if (!$catalogItem) {
                                    $catalogItem = new CatalogLocalItem();
//                                }

                                $catalogItem->assign($rowData);

                                if ($catalogItem->save())
                                    $result["countSuccess"] ++;
                                else {
                                    $messages = $catalogItem->getMessages();
                                    foreach ($messages as $message) {
                                        $error[] = "error: {$message}";
                                    }
                                    $result["countFailed"] ++;
                                }
                            }

                            break;
                        }

                        # Removing where not in this excel
                        $catalogItem = CatalogLocalItem::find(array(
                                    "conditions" => "supplier = :supplier: AND hash != :hash:",
                                    "bind" => array(
                                        "supplier" => $supplier->code,
                                        "hash" => $temporaryHash,
                                    ),
                        ));

                        foreach ($catalogItem AS $item) {
                            $result["countRemoved"] ++;
                            $item->delete();
                        }

                        return $this->response->setJsonContent(array(
                                    "error" => false,
                                    "errors" => $error,
                                    "data" => $result,
                        ));
                        break;

                    default:
                        return $this->response->setJsonContent(array("error" => true, "errorMsg" => "Некорректный шаг"));
                        break;
                }
                break;

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

}
