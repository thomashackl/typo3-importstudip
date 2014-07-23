<?php
Tx_Extbase_Utility_Extension::configurePlugin(
        $_EXTKEY,
        'Pi1',
        array(
            'App' => 'index'
        )
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['importstudip_piimportstudip'][] = 'UniPassau\\ImportStudip\\Hooks\\CmsLayout->getExtensionSummary';

