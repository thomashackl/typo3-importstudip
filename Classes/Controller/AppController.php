<?php

namespace \UniPassau\ImportStudip;

use \TYPO3\CMS\Extbase\MVC\Controller\ActionController;
use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class AppController extends ActionController {

    public function initializeAction() {
    }

    public function indexAction() {
        $this->view->assign('studipcontent', 'TEST');
    }

}
