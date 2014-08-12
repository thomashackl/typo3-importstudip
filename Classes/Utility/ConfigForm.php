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
        $html = '<select name="'.$inputname.'" size="1" '.
            'onchange="Tx_ImportStudip(\'content\')">';
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
        $html = '<div id="tx-importstudip-aggregate">';
        $config = self::getConfig($parameters);
        if ($config['settings.pagetype'] == 'courses' || $config['settings.pagetype'] == 'persons') {
            $html .= '<input type="checkbox" name="'.$parameters['itemFormElName'].'"'.($parameters['itemFormElValue'] ? ' checked="checked"' : '').'/>';
            $html .= \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.backend.label.aggregate', 'importstudip');
        }
        $html .= '</div>';
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
        $html = '<div id="tx-importstudip-subjects" data-input-name="'.
            $parameters['itemFormElName'].'" data-input-value="'.
            $parameters['itemFormElValue'].'">';
        $config = self::getConfig($parameters);
        if ($config['settings.pagetype'] == 'courses') {
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
        $html = '<div id="tx-importstudip-statusgroups">';
        $config = self::getConfig($parameters);
        $html .= self::getStatusgroupForm(
            json_decode(StudipConnector::getStatusgroupNames($config['settings.institute'])), 
            $parameters['itemFormElName'], $parameters['itemFormElValue'],
            $parameters);
        $html .= '</div>';
        return $html;
    }

    public function getStatusgroupForm($data, $inputname, $selected, $parameters=array()) {
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
