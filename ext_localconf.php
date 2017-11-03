<?php
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'UniPassau.'.$_EXTKEY,
        'Pi1',
        array('ImportStudip' => 'index, searchcourse'),
        array('ImportStudip' => 'index, searchcourse')
);

// Use new Icon API in TYPO3 7.5 and up
if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 7005000) {
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon('studip', \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:importstudip/Resources/Public/Icons/Extension.svg']);
}
