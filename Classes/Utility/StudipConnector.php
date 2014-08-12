<?php

namespace UniPassau\ImportStudip\Utility;

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'Classes/Utility/StudipRESTHelper.php');

class StudipConnector {

    public function getExternConfigTypes() {
        $rest = new StudipRESTHelper();
        $data = json_decode($rest->call('typo3/externalpagetypes'));
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
        $types = array();
        // Now set enabled types for GUI.
        if ($courses) {
            $types[] = array(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.backend.externtype.courses', 'importstudip'),
                'courses'
            );
        }
        if ($coursedetails) {
            $types[] = array(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.backend.externtype.coursedetails', 'importstudip'),
                'coursedetails'
            );
        }
        if ($persons) {
            $types[] = array(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.backend.externtype.persons', 'importstudip'),
                'persons'
            );
        }
        if ($persondetails) {
            $types[] = array(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.backend.externtype.persondetails', 'importstudip'),
                'persondetails'
            );
        }
        if ($news) {
            $types[] = array(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.backend.externtype.news', 'importstudip'),
                'news'
            );
        }
        if ($download) {
            $types[] = array(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.backend.externtype.download', 'importstudip'),
                'download'
            );
        }
        return $types;
    }

    public function getInstitutes($treetype, $externtype) {
        $result = array();
        $mapping = self::getTypeMapping();
        $rest = new StudipRESTHelper();
        if ($treetype == 'rangetree') {
            $route = 'typo3/rangetree/'.$mapping[$externtype];
        } else {
            $route = 'typo3/institutes/'.$mapping[$externtype];
        }
        return $rest->call($route);
    }

    public function getExternConfigurations($institute, $type) {
        $result = array();
        $mapping = self::getTypeMapping();
        $rest = new StudipRESTHelper();
        return $rest->call('typo3/externconfigs/'.$institute.'/'.implode(',',$mapping[$type]));
    }

    public function getCourseTypes($institute) {
        $rest = new StudipRESTHelper();
        return $rest->call('typo3/coursetypes/'.$institute);
    }

    public function getSubjects($parent_id, $depth) {
        $rest = new StudipRESTHelper();
        return $rest->call('typo3/semtree/'.$parent_id.'/'.$depth);
    }

    public function getStatusgroupNames($institute) {
        $rest = new StudipRESTHelper();
        return $rest->call('typo3/statusgroupnames/'.$institute);
    }

    private function getTypeMapping() {
        return array(
            'courses' => array(3, 8, 12, 15),
            'coursedetails' => array(4, 13),
            'persons' => array(1, 9, 16),
            'persondetails' => array(2, 14),
            'news' => array(5, 7, 11),
            'download' => array(6, 10)
        );
    }

}
