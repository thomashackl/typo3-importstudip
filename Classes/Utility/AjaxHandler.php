<?php

namespace UniPassau\ImportStudip\Utility;

class AjaxHandler {

    public function handleAjax($params = array(), \TYPO3\CMS\Core\Http\AjaxRequestHandler &$ajaxObj = NULL) {
        $action = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('action');
        $ajaxObj->setContentFormat('json');
        $ajaxObj->addContent('tx_importstudip', self::$action());
    }

    public function institutes() {
        $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['importstudip']);
        return StudipConnector::getInstitutes($extConf['studip_use_hierarchy']);
    }

    public function instituteform() {
        $institutes = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('institutes');
        $inputname = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('inputname');
        $selected = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('selected');
        return ConfigForm::getInstituteForm(json_decode($institutes), $inputname, $selected);
    }

    public function externconfigurations() {
        $institute = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('institute');
        $types = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('configtype');
        
        return StudipConnector::getExternConfigurations($institute, $types);
    }

    public function externconfigurationsform() {
        $externconfigs = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('configurations');
        $inputname = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('inputname');
        $selected = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('selected');
        return ConfigForm::getExternConfigurationsForm(json_decode($externconfigs), $inputname, $selected);
    }

    public function subjectsform() {
        $parent_id = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('parent');
        $inputname = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('inputname');
        $selected = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('selected');
        //return StudipConnector::getSubjects($parent_id, 2);
        return ConfigForm::getSubjectForm(json_decode(StudipConnector::getSubjects($parent_id, 2)), $inputname, $selected);
    }

    public function additionaloptions() {
        return ConfigForm::getadditionalOptions();
    }

}
