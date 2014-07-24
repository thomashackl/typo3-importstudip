<?php

use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Utility\ExtensionUtility;

require_once(ExtensionManagementUtility::extPath($_EXTKEY).'Classes/StudipConnector.php');

// Register plugin for usage.
ExtensionUtility::registerPlugin(
    $_EXTKEY,
    'Pi1',
    'LLL:EXT:'.$_EXTKEY.'/Resources/Private/Language/locallang.xlf:tx_importstudip.plugintitle'
);

// Include Flexform
$extensionName = strtolower(GeneralUtility::underscoredToUpperCamelCase($_EXTKEY));
$pluginSignature = strtolower($extensionName) . '_pi1';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:'.$_EXTKEY . '/Configuration/FlexForms/flexform.xml');

// Wizicon
if (TYPO3_MODE == 'BE') {
    $GLOBALS['TBE_MODULES_EXT']['xMOD_db_new_content_el']['addElClasses']['UniPassau\\ImportStudip\\Wizicon'] = ExtensionManagementUtility::extPath($_EXTKEY) . 'Classes/Wizicon.php';
}
