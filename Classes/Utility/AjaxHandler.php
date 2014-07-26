<?php

namespace UniPassau\ImportStudip\Utility;

class AjaxHandler {

    public function handleAjax($params = array(), \TYPO3\CMS\Core\Http\AjaxRequestHandler &$ajaxObj = NULL) {
        $action = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('action');
        $ajaxObj->setContentFormat('json');
        $ajaxObj->addContent('tx_importstudip', self::$action());
    }

    public function institutes() {
        return StudipConnector::getInstitutes();
    }

    public function externconfigurations() {
        $institute = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('institute');
        $configtype = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('institute');
        
        return StudipConnector::getExternConfigurations($institute, $types);
    }

}
