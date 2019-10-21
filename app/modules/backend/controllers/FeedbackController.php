<?php

namespace Molly\Backend\Controllers;

use Molly\Models\FeedBack,
    Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class FeedbackController extends ControllerBase {

    public function listAction() {
        $this->setTitle("Обратная связь");

        if ($this->request->isPost()) {
            switch ($this->request->get("action")) {
                case "note":
                    $feedback = FeedBack::findFirst($this->request->getPost("id", "int"));

                    if ($feedback) {
                        $feedback->note = $this->request->getPost("note");
                        $feedback->save();
                    }

                    $page = $this->request->getQuery("page", "int");
                    if ($page < 1)
                        $page = 1;

                    return $this->response->redirect("feedback?page=" . $page);
                    break;

                default:
                    $page = $this->request->getQuery("page", "int");
                    if ($page < 1)
                        $page = 1;

                    return $this->response->redirect("feedback?page=" . $page);
                    break;
            }
        }

        $feedback = $this->modelsManager->createBuilder()
                ->from("Molly\Models\FeedBack")
                ->orderBy("id DESC");

        $paginator = new PaginatorQueryBuilder(array(
            "builder" => $feedback,
            "limit" => 25,
            "page" => $this->request->getQuery("page", "int"),
        ));

        $this->view->feedbackPage = $paginator->getPaginate();
    }

}
