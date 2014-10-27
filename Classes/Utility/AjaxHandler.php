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

    public function personsearch() {
        $inputname = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('inputname');
        $selected = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('selected');
        return ConfigForm::getPersonSearch();
    }

    public function personsearchform() {
        $searchterm = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('searchterm');
        $inputname = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('inputname');
        $selected = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('selected');
        $data = json_decode(StudipConnector::searchUser($searchterm));
        return ConfigForm::getPersonSearchForm($data, $inputname, $selected);
    }

    public function personsearchresults() {
        $number = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('number');
        $html = '<div class="tx-importstudip-searchresult-found">';
        if ($number == 1) {
            $html .= \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.backend.text.searchresult_one', 'importstudip');
        } else {
            $html .= $number.' '.\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.backend.text.searchresult_more', 'importstudip');
        }
        $html .= '</div>';
        return $html;
    }

    public function chooseuserinstitute() {
        $user_id = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('userid');
        $selected = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('selected');
        $inputname = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('inputname');
        $institutes = (array) StudipConnector::getUserInstitutes($user_id);
        return ConfigForm::chooseUserInstituteForm($institutes, $inputname, $selected);
    }

    public function coursesearch() {
        $inputname = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('inputname');
        $selected = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('selected');
        return ConfigForm::getCourseSearch();
    }

    public function coursesearchform() {
        $searchterm = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('searchterm');
        $semester = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('semester');
        $inputname = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('inputname');
        $selected = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('selected');
        $data = json_decode(StudipConnector::searchUser($searchterm));
        return ConfigForm::getPersonSearchForm($data, $inputname, $selected);
    }

    public function additionaloptions() {
        return ConfigForm::getadditionalOptions();
    }

    public function aggregationform() {
        $inputname = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('inputname');
        $value = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('value');
        return ConfigForm::getAggregationForm($inputname, $value);
    }

    public function coursetypeform() {
        $inputname = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('inputname');
        $value = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('value');
        $institute = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('institute');
        return ConfigForm::getCourseTypeForm(json_decode(StudipConnector::getCourseTypes($institute)), $inputname, $value);
    }

    public function subjectsform() {
        $parent_id = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('parent');
        $inputname = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('inputname');
        $selected = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('selected');
        //return StudipConnector::getSubjects($parent_id, 2);
        return ConfigForm::getSubjectForm(json_decode(StudipConnector::getSubjects($parent_id, 2)), $inputname, $selected);
    }

    public function statusgroupform() {
        $inputname = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('inputname');
        $value = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('value');
        $institute = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('institute');
        return ConfigForm::getStatusgroupForm(json_decode(StudipConnector::getStatusgroupNames($institute)), $inputname, $value);
    }

}
