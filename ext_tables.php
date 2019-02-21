<?php

// Register plugin for usage.
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $_EXTKEY,
    'Pi1',
    'LLL:EXT:'.$_EXTKEY.'/Resources/Private/Language/locallang.xlf:plugintitle',
    'EXT:importstudip/Resources/Public/Icons/Extension.svg'
);

// Register AJAX handler.
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler (
    'ImportStudip::AjaxHandler',
    'UniPassau\Importstudip\Utility\AjaxHandler->handleAjax'
);

// Use new Icon API in TYPO3 7.5 and up
if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 7005000) {
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon('studip', \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:importstudip/Resources/Public/Icons/Extension.svg']);
}

// Include Flexform
$extensionName = strtolower(\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($_EXTKEY));
$pluginSignature = strtolower($extensionName) . '_pi1';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature,
    'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform.xml');

// Wizicon
if (TYPO3_MODE == 'BE') {
    $GLOBALS['TBE_MODULES_EXT']['xMOD_db_new_content_el']['addElClasses']['UniPassau\\Importstudip\\Wizicon'] =
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Classes/Wizicon.php';
}

