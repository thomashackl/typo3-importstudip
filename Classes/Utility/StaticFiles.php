<?php

namespace UniPassau\ImportStudip\Utility;

class StaticFiles {

    public static function includeFiles(&$aParams, $oTemplate) {
        $config = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['importstudip']);
        $oTemplate->getPageRenderer()->addJsFile($GLOBALS['BACK_PATH'].\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('importstudip').'Resources/Public/JavaScript/importstudip.js');
    }

}
