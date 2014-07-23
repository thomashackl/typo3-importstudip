<?php

namespace \UniPassau\ImportStudip;

use \TYPO3\CMS\Extbase\UtilityTx_Extbase_Utility_Localization;

class StudipModel {

    public function getExternConfigTypes($PA, $fobj) {
        $result = array();
        $rest = new StudipRESTHelper();
        $data = $rest->call('/typo3/externalpagetypes');
        /*
         * Check for available config types and enable corresponding
         * "abstract" type.
         */
        foreach ($data as $entry) {
            switch ($entry['type']) {
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
                Tx_Extbase_Utility_Localization::translate('tx_importstudip.backend.externtype.courses'),
                'courses'
            );
        }
        if ($coursedetails) {
            $result[] = array(
                Tx_Extbase_Utility_Localization::translate('tx_importstudip.backend.externtype.coursedetails'),
                'coursedetails'
            );
        }
        if ($persons) {
            $result[] = array(
                Tx_Extbase_Utility_Localization::translate('tx_importstudip.backend.externtype.persons'),
                'persons'
            );
        }
        if ($persondetails) {
            $result[] = array(
                Tx_Extbase_Utility_Localization::translate('tx_importstudip.backend.externtype.persondetails'),
                'persondetails'
            );
        }
        if ($news) {
            $result[] = array(
                Tx_Extbase_Utility_Localization::translate('tx_importstudip.backend.externtype.news'),
                'news'
            );
        }
        if ($download) {
            $result[] = array(
                Tx_Extbase_Utility_Localization::translate('tx_importstudip.backend.externtype.download'),
                'download'
            );
        }
        return $result;
    }

}
