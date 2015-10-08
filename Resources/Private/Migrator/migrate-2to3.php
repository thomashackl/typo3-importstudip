<?php

require_once(realpath(__DIR__.'/../../../../../../typo3_src/typo3/sysext/adodb/adodb/adodb.inc.php'));

function getTYPO3Configuration() {
    return require realpath(__DIR__.'/../../../../../LocalConfiguration.php');
}

###################################################
# Load TYPO3 settings for connecting to database. #
###################################################

$config = array();
echo 'Loading TYPO3 configuration for database access... ';
$config = getTYPO3Configuration();
if ($config['DB']) {
    echo "success.\n";
} else {
    echo "failed.\n";
    die;
}

###########################################################################
# Establish database connection and load "importstudip" content elements. #
###########################################################################

echo 'Connecting to TYPO3 database... ';
$db = ADONewConnection('mysql');
$db->Connect(
    $config['DB']['host'],
    $config['DB']['username'],
    $config['DB']['password'],
    $config['DB']['database']
);
if ($db->isConnected()) {
    echo "success.\n";
} else {
    echo "failed.\n";
    die;
}

echo 'Searching for existing and active Stud.IP extension content elements... ';
$entries = $db->GetAll("SELECT `uid`, `pi_flexform`
    FROM `tt_content`
    WHERE `list_type` = 'importstudip_pi1'
        AND `deleted` = 0
    ORDER BY `crdate`"
);

$count = count($entries);

if ($count < 1) {
    echo "No entries found.\n";
    die;
} else {
    echo $count." entries found.\n";
}

##########################################################################
# Transform Flexform entries from importstudip 2.x to new format for 3.0 #
##########################################################################

echo "Loading XSL stylesheet for transformation... ";
$xsldoc = new DOMDocument();
$loaded = $xsldoc->load(realpath(__DIR__.'/2to3.xsl'));
if ($loaded) {
    echo "success.\n";
} else {
    echo "failed.\n";
    die;
}

$i = 0;
$xmldoc = new DOMDocument();
$xsl = new XSLTProcessor();
$xsl->importStylesheet($xsldoc);
// Process database entries.
echo "Processing database entries...\n";
$i = 1;
$percentage = 0.1;
foreach ($entries as $entry) {
    if (round($count * $percentage) == $i) {
        echo ($percentage*100)."% done.\n";
        $percentage += 0.1;
    }
    $xmldoc->loadXML($entry['pi_flexform']);
    // Apply XSL transformation.
    $transformed = $xsl->transformToXml($xmldoc);
    if (!$transformed) {
        echo "Error on transforming record with UID ".$entry['uid'].".\n";
    } else {
        if (!$db->Execute("UPDATE `tt_content` SET `pi_flexform`=? WHERE `uid`=?",
                array($transformed, $entry['uid']))) {
            echo "Error on writing new XML to database for UID ".$entry['uid'].".\n";
        }
    }
    $i++;
}
