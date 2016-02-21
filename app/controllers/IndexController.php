<?php
use Phalcon\Mvc\Controller;

class IndexController extends Controller
{

    public function indexAction()
    {

    }

    public function restrictedAction()
    {
        $this->view->setParamToView("auth_name", $this->session->get("auth")['name']);
    }
}