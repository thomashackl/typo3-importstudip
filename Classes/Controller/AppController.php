<?php

namespace UniPassau\ImportStudip;

class Tx_ImportStudip_Controller_AppController extends Tx_Extbase_MVC_Controller_ActionController {

    public function initializeAction() {
    }

    public function indexAction() {
        $this->view->assign('studipcontent', 'TEST');
    }

}
