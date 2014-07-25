<?php

namespace UniPassau\ImportStudip\Utility;

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'Classes/Utility/StudipConnector.php');

class ConfigForm {

    public function getExternConfigTypes($parameters, $config) {
        $result = '<script type="text/javascript" src="'.\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('importstudip').'Resources/Public/JavaScript/importstudip.js"></script>';
        $types = StudipConnector::getExternConfigTypes();
        foreach ($types as $type) {
            $result .= '<input type="radio" onclick="Tx_ImportStudip.getInstitutes()" name="'.$parameters['itemFormElName'].'" value="'.$type[1].'"/>'.$type[0].'<br/>';
        }
        return $result;
    }

    public function getInstitutes($parameters, $config) {
        return '<div id="institutes"></div>';
    }

}
