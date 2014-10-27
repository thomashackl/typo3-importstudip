<?php

namespace UniPassau\ImportStudip\Utility;

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'Classes/Utility/StudipConnector.php');

class ConfigForm {

    public function getExternConfigTypes($parameters, $config) {
        $result = '<div id="tx-importstudip-pagetypes">';
        $result .= '<script type="text/javascript" src="'.
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('importstudip').
            'Resources/Public/JavaScript/tx_importstudip.js"></script>';
        $result .= '<style type="text/css">
            @import url("'.
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('importstudip').
            'Resources/Public/Css/tx_importstudip.css")
            </style>';
        $types = StudipConnector::getExternConfigTypes();
        foreach ($types as $type) {
            $result .= '<input type="radio" id="' .$type[1].
                '" onclick="Tx_ImportStudip.changeSelection(\'pagetype\')" name="'.
                $parameters['itemFormElName'].'" value="'.$type[1].'"'.
                ($type[1] == $parameters['itemFormElValue'] ? ' checked="checked"' : '').
                '/><label for="'.$type[1].'">'.$type[0].'</label><br/>';
        }
        $result .= '</div>';
        return $result;
    }

    public function getInstitutes($parameters, $config) {
        $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['importstudip']);
        $hierarchy = $extConf['studip_use_hierarchy'];
        $result = '<div id="tx-importstudip-institutes" data-input-name="'.
            $parameters['itemFormElName'].'" data-input-value="'.
            $parameters['itemFormElValue'].'" data-inst-treetype="'.$hierarchy.'" '.
            'data-loading-text="'.
            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.backend.label.loading', 'importstudip').'">';
        if ($parameters['itemFormElValue']) {
            $result .= self::getInstituteForm(json_decode(
                StudipConnector::getInstitutes($hierarchy)), $parameters['itemFormElName'],
                $parameters['itemFormElValue'], $hierarchy);
        }
        $result .= '</div>';
        if (!$parameters['itemFormElValue']) {
            $result .= '<script type="text/javascript">
            //<!--
            TYPO3.jQuery("#tx-importstudip-institutes").closest(".t3-form-field-container").hide();
            //-->
            </script>';
        }
        return $result;
    }

    public function getInstituteForm($data, $inputname, $selected, $parameters=array()) {
        $html = '<ul class="tx-importstudip-tree">';
        foreach ($data as $entry) {
            $id = 'tx-importstudip-institute-'.($entry->id ?: $entry->tree_id);
            $html .= '<li class="'.
                ($entry->children ? 'tx-importstudip-treebranch' : 'tx-importstudip-treeleaf').'">';
            if ($entry->children) {
                $path = substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/') + 1);
                $html .= '<img class="tx-importstudip-openclose" src="'.$path.
                    'gfx/ol/plusonly.gif" data-swap-img="'.$path.
                    'gfx/ol/minusonly.gif"/>';
            }
            if ($entry->selectable) {
                $html .= '<input type="radio" class="tx-importstudip-selector" '.
                    'name="'.$inputname.'" value="'.$entry->id.
                    '" onclick="Tx_ImportStudip.changeSelection(\'institute\')"'.
                    ($entry->id == $selected ? ' checked="checked"' : '').
                    '/>';
            }
            $html .= '<label for="'.$id.'">'.$entry->name.'</label>'.
                '<input type="checkbox" class="tx-importstudip-treeinput" id="'.
                $id.'"/>';
            if ($entry->children != null) {
                $html .= self::getInstituteForm($entry->children, $inputname, $selected, $parameters);
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    public function getExternConfigurations($parameters, $config) {
        $result = '<div id="tx-importstudip-externconfigs" data-input-name="'.
            $parameters['itemFormElName'].'" data-loading-text="'.
            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.backend.label.loading', 'importstudip').
            '">';
        if ($parameters['itemFormElValue']) {
            $config = self::getConfig($parameters);
            $result .= self::getExternConfigurationsForm(
                json_decode(StudipConnector::getExternConfigurations(
                $config['settings.institute'], $config['settings.pagetype'])), 
                $parameters['itemFormElName'], $parameters['itemFormElValue'],
                $parameters);
        }
        $result .= '</div>';
        if (!$parameters['itemFormElValue']) {
            $result .= '<script type="text/javascript">
            //<!--
            TYPO3.jQuery("#tx-importstudip-externconfigs").closest(".t3-form-field-container").hide();
            //-->
            </script>';
        }
        return $result;
    }

    public function getExternConfigurationsForm($data, $inputname, $selected, $parameters=array()) {
        $html = '<select name="'.$inputname.'" size="1">';
        foreach ($data as $entry) {
            $html .= '<option value="'.$entry->id.'"'.
                ($entry->id==$selected ? ' selected="selected"' : '').'>'.
                $entry->name.'</option>';
        }
        $html .= '</select>';
        return $html;
    }

    public function getPersonSearch($parameters, $config) {
        $html = '<div id="tx-importstudip-personsearch" data-input-name="'.
            $parameters['itemFormElName'].'" data-input-value="'.
            $parameters['itemFormElValue'].'">';
        $html .= '<input type="text" id="tx-importstudip-searchterm" size="40" maxlength="255" placeholder="'.
            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.backend.placeholder.personsearch', 'importstudip').
            '">';
        $html .= '<button type="button" id="tx-importstudip-execute-personsearch" onclick="Tx_ImportStudip.performPersonSearch()">'.
            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.backend.label.execute_search', 'importstudip').
            '</button>';
        $html .= '<div>'.\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.backend.text.personsearch', 'importstudip').'</div>';
        $html .= '<div id="tx-importstudip-personsearch-result">';
        if ($parameters['itemFormElValue']) {
            $html .= self::getPersonSearchForm($selected, $parameters['itemFormElName'],
                $parameters['itemFormElValue'], $parameters);
        }
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    public function getPersonSearchForm($data, $inputname, $value, $parameters=array()) {
        if ($value) {
            $selected = StudipConnector::getUser($value);
        }
        $html = '<select name="'.$inputname.'" size="1">';
        foreach ($data as $entry) {
            $html .= '<option value="'.$entry->user_id.'"'.
                ($id == $selected ? ' selected="selected"' : '').'>'.
                $entry->lastname.', '.$entry->firstname.' ('.$entry->username.')</option>';
        }
        $html .= '</select>';
        return $html;
    }

    public function chooseUserInstitute($parameters, $config) {
        $config = self::getConfig($parameters);
        $html = '<div id="tx-importstudip-choose-user-institute" data-input-name="'.
            $parameters['itemFormElName'].'" data-input-value="'.
            $parameters['itemFormElValue'].'">';
        if ($parameters['itemFormElValue']) {
            $html .= self::chooseUserInstituteForm(
                (array) StudipConnector::getUserInstitutes($config['settings.personsearch']),
                $parameters['itemFormElName'], $parameters['itemFormElValue']);
        }
        $html .= '</div>';
        return $html;
    }

    public function chooseUserInstituteForm($data, $inputname, $value, $parameters=array()) {
        if (sizeof($institutes) == 1) {
            $html .= '<input type="hidden" name="'.$parameters['itemFormElName'].
                '" value="'.$institutes[0]['institute_id'].'"/>';
            $html .= '<div class="tx-importstudip-choose-institute">'.
                $institutes[0]['name'].'</div>';
        } else {
            $html = '<select name="'.$parameters['itemFormElName'].'">';
            foreach ($institutes as $i) {
                $html .= '<option value="'.$i['institute_id'].'"'.
                    ($i['institute_id']==$parameters['itemFormElValue'] ? ' selected="selected"' : '').
                    '>'.$i['name'].'</option>';
            }
            $html .= '</select>';
        }
    }

    public function getCourseSearch($parameters, $config) {
        $html = '<div id="tx-importstudip-coursesearch" data-input-name="'.
            $parameters['itemFormElName'].'" data-input-value="'.
            $parameters['itemFormElValue'].'">';
        $html .= '<input type="text" id="tx-importstudip-searchterm" size="40" maxlength="255" placeholder="'.
            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.backend.placeholder.coursesearch', 'importstudip').
            '">';
        $html .= '<select id="tx-importstudip-semester" size="1">';
        $html .= '<option value="">'.\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.backend.label.allsemesters', 'importstudip').'</option>';
        foreach ((array) json_decode(StudipConnector::getAllSemesters()) as $semester) {
            $html .= '<option value="'.$semester->semester_id.'">'.$semester->description.'</option>';
        }
        $html .= '</select>';
        $html .= '<button type="button" id="tx-importstudip-execute-coursesearch" onclick="Tx_ImportStudip.performCourseSearch()">'.
            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.backend.label.execute_search', 'importstudip').
            '</button>';
        $html .= '<div>'.\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.backend.text.coursesearch', 'importstudip').'</div>';
        $html .= '<div id="tx-importstudip-coursesearch-result">';
        if ($parameters['itemFormElValue']) {
            $html .= self::getPersonSearchForm($selected, $parameters['itemFormElName'],
                $parameters['itemFormElValue'], $parameters);
        }
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    public function getCourseSearchForm($data, $inputname, $value, $parameters) {
        if ($value) {
            $selected = StudipConnector::getCourse($value);
        }
        $html = '<select name="'.$inputname.'" size="1">';
        foreach ($data as $entry) {
            $html .= '<option value="'.$entry->seminar_id.'"'.
                ($id == $selected ? ' selected="selected"' : '').'>'.
                $entry->name.' ('.$entry->type.', '.$entry->semester.')</option>';
        }
        $html .= '</select>';
        return $html;
    }

    public function chooseCourseInstitute($parameters, $config) {
        $config = self::getConfig($parameters);
        $html = '<div id="tx-importstudip-choose-course-institute" data-input-name="'.
            $parameters['itemFormElName'].'" data-input-value="'.
            $parameters['itemFormElValue'].'">';
        if ($parameters['itemFormElValue']) {
            $html .= self::chooseCourseInstituteForm(
                (array) StudipConnector::getCourseInstitutes($config['settings.coursesearch']),
                $parameters['itemFormElName'], $parameters['itemFormElValue']);
        }
        $html .= '</div>';
        return $html;
    }

    public function chooseCourseInstituteForm($data, $inputname, $value, $parameters) {
        if (sizeof($institutes) == 1) {
            $html .= '<input type="hidden" name="'.$parameters['itemFormElName'].
                '" value="'.$institutes[0]['institute_id'].'"/>';
            $html .= '<div class="tx-importstudip-choose-institute">'.
                $institutes[0]['name'].'</div>';
        } else {
            $html = '<select name="'.$parameters['itemFormElName'].'">';
            foreach ($institutes as $i) {
                $html .= '<option value="'.$i['institute_id'].'"'.
                    ($i['institute_id']==$parameters['itemFormElValue'] ? ' selected="selected"' : '').
                    '>'.$i['name'].'</option>';
            }
            $html .= '</select>';
        }
    }

    public function getAdditionalOptions() {
        $html = '<div id="tx-importstudip-options">';
        $html .= '</div>';
        return $html;
    }

    public function getAggregation($parameters, $config) {
        $config = self::getConfig($parameters);
        $html = '<div id="tx-importstudip-aggregate" data-input-name="'.
            $parameters['itemFormElName'].'" data-input-value="'.
            $parameters['itemFormElValue'].'">';
        if ($config['settings.pagetype'] == 'courses' || $config['settings.pagetype'] == 'persons') {
            $html .= self::getAggregationForm($parameters['itemFormElName'], $parameters['itemFormElValue']);
        }
        $html .= '</div>';
        return $html;
    }

    public function getAggregationForm($inputname, $value) {
        $html = '<input type="checkbox" name="'.$inputname.'"'.
            ($value ? ' checked="checked"' : '').'/>';
        //$html .= \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.backend.label.aggregate', 'importstudip');
        return $html;
    }

    public function getCourseTypes($parameters, $config) {
        $html = '<div id="tx-importstudip-coursetypes" data-input-name="'.
            $parameters['itemFormElName'].'" data-input-value="'.
            $parameters['itemFormElValue'].'">';
        $config = self::getConfig($parameters);
        if ($config['settings.pagetype'] == 'courses' || \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('configtype') == 'courses') {
            $html .= self::getCourseTypeForm(
                json_decode($config['settings.institute']), 
                $parameters['itemFormElName'], $parameters['itemFormElValue'],
                $parameters);
        }
        $html .= '</div>';
        return $html;
    }

    public function getCourseTypeForm($data, $inputname, $selected) {
        $html = '<select name="'.$inputname.'" size="1">';
        $html .= '<option value="">'.\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.backend.label.select', 'importstudip').'</option>';
        foreach ($data as $entry) {
            $html .= '<option value="'.$entry->id.'"'.
                ($entry->id==$selected ? ' selected="selected"' : '').'>'.
                $entry->type.' ('.$entry->classname.')</option>';
        }
        $html .= '</select>';
        return $html;
    }

    public function getSubjects($parameters, $config) {
        $html = '<div id="tx-importstudip-subjects" data-input-name="'.
            $parameters['itemFormElName'].'" data-input-value="'.
            $parameters['itemFormElValue'].'">';
        $config = self::getConfig($parameters);
        if ($config['settings.pagetype'] == 'courses' || \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('configtype') == 'courses') {
            $html .= self::getSubjectForm(
                json_decode(StudipConnector::getSubjects('root', 1, $parameters['itemFormElValue'])), 
                $parameters['itemFormElName'], $parameters['itemFormElValue'],
                $parameters);
        }
        $html .= '</div>';
        return $html;
    }

    public function getSubjectForm($data, $inputname, $selected, $parameters=array()) {
        $html = '<ul class="tx-importstudip-tree">';
        foreach ($data as $entry) {
            $id = 'tx-importstudip-subject-'.$entry->tree_id;
            $html .= '<li class="'.
                ($entry->num_children ? 'tx-importstudip-treebranch' : 'tx-importstudip-treeleaf').'">';
            if ($entry->num_children > 0) {
                $path = substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/') + 1);
                $html .= '<img class="tx-importstudip-openclose" src="'.$path.
                    'gfx/ol/plusonly.gif" data-swap-img="'.$path.
                    'gfx/ol/minusonly.gif"/>';
            }
            if ($entry->id) {
                $html .= '<input type="radio" class="tx-importstudip-selector" '.
                    'name="'.$inputname.'" value="'.$entry->id.'"'.
                    ($entry->id == $selected ? ' checked="checked"' : '').
                    '/>';
            }
            $html .= '<label for="'.$id.'">'.$entry->name.'</label>'.
                '<input type="checkbox" class="tx-importstudip-treeinput" id="'.
                $id.'"';
            if ($entry->num_children && !$entry->children) {
                $html .= 'onclick="return Tx_ImportStudip.getSubjects(\''.$entry->id.'\')"';
            }
            $html .= '/>';
            if ($entry->children) {
                $html .= self::getSubjectForm($entry->children, $inputname, $selected, $parameters);
            } else if ($entry->num_children) {
                $html .= '<ul class="tx-importstudip-tree"><li data-loading-text="'.
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.backend.label.loading', 'importstudip').
                    '">&nbsp;</li></ul>';
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    public function getStatusgroups($parameters, $config) {
        $html = '<div id="tx-importstudip-statusgroups" data-input-name="'.
            $parameters['itemFormElName'].'" data-input-value="'.
            $parameters['itemFormElValue'].'">';
        $config = self::getConfig($parameters);
        if ($config['settings.pagetype'] == 'persons' || \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('configtype') == 'persons') {
            $html .= self::getStatusgroupForm(
                json_decode(StudipConnector::getStatusgroupNames($config['settings.institute'])), 
                $parameters['itemFormElName'], $parameters['itemFormElValue']);
        }
        $html .= '</div>';
        return $html;
    }

    public function getStatusgroupForm($data, $inputname, $selected) {
        $html = '<select name="'.$inputname.'" size="1">';
        $html .= '<option value="">'.
            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.backend.label.all', 'importstudip').
            '</option>';
        foreach ($data as $entry) {
            $html .= '<option value="'.$entry.'"'.
                ($entry==$selected ? ' selected="selected"' : '').'>'.
                $entry.'</option>';
        }
        $html .= '</select>';
        return $html;
    }

    private function getConfig($data) {
        $result = array();
        // Extract already configured flexform values.
        $xml = simplexml_load_string($data['row']['pi_flexform']);
        $json = json_encode($xml);
        $fullConfig = json_decode($json, true);
        $fullConfig = $fullConfig['data']['sheet']['language']['field'];
        foreach ($fullConfig as $c) {
            $result[$c['@attributes']['index']] = $c['value'];
        }
        return $result;
    }

}
