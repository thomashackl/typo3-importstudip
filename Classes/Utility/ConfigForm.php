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
                '" onclick="Tx_ImportStudip.getInstitutes()" name="'.
                $parameters['itemFormElName'].'" value="'.$type[1].'"'.
                ($type[1] == $parameters['itemFormElValue'] ? ' checked="checked"' : '').
                '/><label for="'.$type[1].'">'.$type[0].'</label><br/>';
        }
        $result .= '</div>';
        return $result;
    }

    public function getInstitutes($parameters, $config) {
        $result = '<div id="tx-importstudip-institutes" data-input-name="'.
            $parameters['itemFormElName'].'" data-input-value="'.
            $parameters['itemFormElValue'].'">';
        if ($parameters['itemFormElValue']) {
            $result .= self::getInstituteForm(json_decode(
                StudipConnector::getInstitutes()), $parameters['itemFormElName'],
                $parameters['itemFormElValue']);
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
            if ($entry->id) {
                $html .= '<input type="radio" class="tx-importstudip-selector" '.
                    'name="'.$inputname.'" value="'.$entry->id.
                    '" onclick="Tx_ImportStudip.getExternConfigurations()"'.
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
        $result = '<div id="tx-importstudip-externconfigs" data-input-name="'.$parameters['itemFormElName'].'">';
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

    public function getAdditionalOptions() {
        $html = '<div id="tx-importstudip-options">';
        $html .= '</div>';
        return $html;
    }

    public function getAggregationForm($parameters, $config) {
        $html = '';
        $config = self::getConfig($parameters);
        if ($config['settings.pagetype'] == 'courses' || $config['settings.pagetype'] == 'persons') {
            $html .= '<input type="checkbox" name="'.$parameters['itemFormElName'].'"'.($parameters['itemFormElValue'] ? ' checked="checked"' : '').'/>';
            $html .= \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.backend.label.aggregate', 'importstudip');
        }
        return $html;
    }

    public function getCourseTypes($parameters, $config) {
        $html = '<div id="tx-importstudip-coursetypes">';
        $config = self::getConfig($parameters);
        if ($config['settings.pagetype'] == 'courses') {
            $html .= self::getCourseTypeForm(
                json_decode(StudipConnector::getCourseTypes($config['settings.institute'])), 
                $parameters['itemFormElName'], $parameters['itemFormElValue'],
                $parameters);
        }
        $html .= '</div>';
        return $html;
    }

    public function getCourseTypeForm($data, $inputname, $selected, $parameters=array()) {
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
        $html = '<div id="tx-importstudip-subjects">';
        $config = self::getConfig($parameters);
        if ($config['settings.pagetype'] == 'courses') {
            $html .= self::getSubjectForm(
                json_decode(StudipConnector::getSubjects()), 
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
                ($entry->children ? 'tx-importstudip-treebranch' : 'tx-importstudip-treeleaf').'">';
            if ($entry->children) {
                $path = substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/') + 1);
                $html .= '<img class="tx-importstudip-openclose" src="'.$path.
                    'gfx/ol/plusonly.gif" data-swap-img="'.$path.
                    'gfx/ol/minusonly.gif"/>';
            }
            $html .= '<input type="radio" class="tx-importstudip-selector" '.
                'name="'.$inputname.'" value="'.$entry->id.
                '" onclick="Tx_ImportStudip.getExternConfigurations()"'.
                ($entry->id == $selected ? ' checked="checked"' : '').
                '/>';
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

    public function getStatusgroups() {
        $html = '<div id="tx-importstudip-statusgroups">';
        $html .= '</div>';
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
