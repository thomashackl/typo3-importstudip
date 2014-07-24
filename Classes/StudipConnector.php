<?php

namespace UniPassau\ImportStudip;

use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use \UniPassau\ImportStudip\StudipRESTHelper;

require_once(ExtensionManagementUtility::extPath($_EXTKEY).'Classes/StudipRESTHelper.php');

class StudipConnector {

    public function getExternConfigTypes($parameters, $config) {
        $result = array();
        $rest = new StudipRESTHelper();
        $data = $rest->call('typo3/externalpagetypes');
        /*
         * Check for available config types and enable corresponding
         * "abstract" type.
         */
        foreach ($data as $entry) {
            switch ($entry) {
                case 3:
                case 8:
                case 12:
                case 15:
                    $courses = true;
                    break;
                case 4:
                case 13:
                    $coursedetails = true;
                    break;
                case 1:
                case 9:
                case 16:
                    $persons = true;
                    break;
                case 2:
                case 14:
                    $persondetails = true;
                    break;
                case 5:
                case 7:
                case 11:
                    $news = true;
                    break;
                case 6:
                case 10:
                    $download = true;
                    break;
            }
        }
        // Now set enabled types for GUI.
        if ($courses) {
            $result[] = array(
                LocalizationUtility::translate('tx_importstudip.backend.externtype.courses', 'importstudip'),
                'courses'
            );
        }
        if ($coursedetails) {
            $result[] = array(
                LocalizationUtility::translate('tx_importstudip.backend.externtype.coursedetails', 'importstudip'),
                'coursedetails'
            );
        }
        if ($persons) {
            $result[] = array(
                LocalizationUtility::translate('tx_importstudip.backend.externtype.persons', 'importstudip'),
                'persons'
            );
        }
        if ($persondetails) {
            $result[] = array(
                LocalizationUtility::translate('tx_importstudip.backend.externtype.persondetails', 'importstudip'),
                'persondetails'
            );
        }
        if ($news) {
            $result[] = array(
                LocalizationUtility::translate('tx_importstudip.backend.externtype.news', 'importstudip'),
                'news'
            );
        }
        if ($download) {
            $result[] = array(
                LocalizationUtility::translate('tx_importstudip.backend.externtype.download', 'importstudip'),
                'download'
            );
        }
        $parameters['items'] = array_merge($parameters['items'], $result);
        return $config;
    }

}
