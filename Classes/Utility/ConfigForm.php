<?php

namespace UniPassau\ImportStudip\Utility;

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'Classes/Utility/StudipConnector.php');

class ConfigForm {

    public function getExternConfigTypes($parameters, $config) {
        $result = '<div id="pagetypes">';
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
        $result = '<div id="institutes" data-input-name="'.
            $parameters['itemFormElName'].'" data-input-value="'.
            $parameters['itemFormElValue'].'">';
        if ($parameters['itemFormElValue']) {
            $result .= self::getInstituteForm(json_decode(StudipConnector::getInstitutes()), $parameters['itemFormElName'], $parameters['itemFormElValue']);
        }
        $result .= '</div>';
        if (!$parameters['itemFormElValue']) {
            $result .= '<script type="text/javascript">
            //<!--
            TYPO3.jQuery("#institutes").closest(".t3-form-field-container").hide();
            //-->
            </script>';
        }
        return $result;
    }

    public function getInstituteForm($data, $inputname, $selected, $parameters=array()) {
        $html = '<ul>';
        foreach ($data as $entry) {
            $html .= '<li class="'.
                ($entry->children ? 'tx_importstudip_treebranch' : 'tx_importstudip_treeleaf').
                '"><input type="radio" class="tx_importstudip_selector" name="'.
                $inputname.'" value="'.$entry->id.
                '" onclick="Tx_ImportStudip.getExternConfigurations()"'.
                ($entry->id == $selected ? ' checked="checked"' : '').
                '/><label for="'.$entry->id.'">'.$entry->name.'</label>'.
                '<input type="checkbox" class="tx_importstudip_treeinput" id="'.
                $entry->id.'"/>';
            if ($entry->children != null) {
                $html .= self::getInstituteForm($entry->children, $inputname, $selected);
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    public function getExternConfigurations($parameters, $config) {
        $result = '<div id="externconfigs" data-input-name="'.$parameters['itemFormElName'].'">';
        if ($parameters['itemFormElValue']) {
            // Extract already configured flexform values.
            $xml = simplexml_load_string($parameters['row']['pi_flexform']);
            $json = json_encode($xml);
            $fullConfig = json_decode($json, true);
            $fullConfig = $fullConfig['data']['sheet']['language']['field'];
            foreach ($fullConfig as $c) {
                if ($c['@attributes']['index'] == 'settings.pagetype') {
                    $pagetype = $c['value'];
                } else if ($c['@attributes']['index'] == 'settings.institute') {
                    $institute = $c['value'];
                }
            }
            $result .= self::getExternConfigurationsForm(
                json_decode(StudipConnector::getExternConfigurations($institute, $pagetype)), 
                $parameters['itemFormElName'], $parameters['itemFormElValue'],
                $parameters);
        }
        $result .= '</div>';
        if (!$parameters['itemFormElValue']) {
            $result .= '<script type="text/javascript">
            //<!--
            TYPO3.jQuery("#externconfigs").closest(".t3-form-field-container").hide();
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

}
