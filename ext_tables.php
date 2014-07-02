<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

include_once(t3lib_extMgm::extPath($_EXTKEY).'res/class.tx_importstudip_soap.php');
include_once(t3lib_extMgm::extPath($_EXTKEY).'res/class.tx_importstudip_subjectarealist.php');
include_once(t3lib_extMgm::extPath($_EXTKEY).'res/class.tx_importstudip_semtreeid.php');

$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
t3lib_div::loadTCA('tt_content');

t3lib_extMgm::addPlugin(array("LLL:EXT:importstudip/locallang.xml:pi1_title", $_EXTKEY."_pi1"),"list_type");
t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Stud.IP data");
t3lib_extMgm::addPiFlexFormValue($_EXTKEY."_pi1", "FILE:EXT:".$_EXTKEY."/pi1/flexform_ds_pi1.xml");			

if (TYPO3_MODE=="BE") {
	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_importstudip_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_importstudip_pi1_wizicon.php';
}
?>