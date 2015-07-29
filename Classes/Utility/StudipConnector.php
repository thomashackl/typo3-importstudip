<?php

namespace UniPassau\ImportStudip\Utility;

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'Classes/Utility/StudipRESTHelper.php');

class StudipConnector {

    public static function getExternConfigTypes() {
        $data = json_decode(self::getData('typo3/externalpagetypes'));
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

    public static function getInstitutes($treetype, $externtype) {
        $result = array();
        $mapping = self::getTypeMapping();
        if ($treetype == 'rangetree') {
            $route = 'typo3/rangetree/'.$mapping[$externtype];
        } else {
            $route = 'typo3/institutes/'.$mapping[$externtype];
        }
        return self::getData($route);
    }

    public static function getExternConfigurations($institute, $type) {
        $result = array();
        $mapping = self::getTypeMapping();
        return self::getData('typo3/externconfigs/'.$institute.'/'.implode(',',$mapping[$type]));
    }

    public static function getUser($user_id) {
        $result = array();
        $data = json_decode(self::getData('user/'.$user_id), true);
        if ($data) {
            $result = array(
                array(
                    'user_id' => $data['user_id'],
                    'firstname' => $data['name']['given'],
                    'lastname' => $data['name']['family'],
                    'username' => $data['username'],
                    'title_front' => $data['name']['prefix'],
                    'title_rear' => $data['name']['suffix']
                )
            );
        }
        return $result;
    }

    public static function searchUser($searchterm) {
        $result = array();
        $result = self::getData('typo3/usersearch/'.rawurlencode($searchterm));
        return $result;
    }

    public static function getUserInstitutes($user_id) {
        $result = array();
        $result = self::getData('user/'.$user_id.'/institutes');
        return $result;
    }

    public static function searchCourse($searchterm, $semester_id='') {
        $result = array();
        $call = 'typo3/coursesearch/'.rawurlencode($searchterm);
        if ($semester_id) {
            $call .= '/'.$semester_id;
        }
        $result = self::getData($call);
        return $result;
    }

    public static function getCourse($course_id) {
        $result = array();
        $result = self::getData('typo3/course/'.$course_id);
        return $result;
    }

    public static function getInstitute($institute_id) {
        $result = array();
        $result = self::getData('typo3/institute/'.$institute_id);
        return $result;
    }

    public static function getAllSemesters() {
        $result = array();
        $result = self::getData('typo3/allsemesters');
        return $result;
    }

    public static function getCourseTypes($institute) {
        return self::getData('typo3/coursetypes/'.$institute);
    }

    public static function getSubjects($parent_id, $depth) {
        return self::getData('typo3/semtree/'.$parent_id.'/'.$depth);
    }

    public static function getStatusgroupNames($institute) {
        return self::getData('typo3/statusgroupnames/'.$institute);
    }

    private static function getTypeMapping() {
        return array(
            'courses' => array(3, 8, 12, 15),
            'coursedetails' => array(4, 13),
            'persons' => array(1, 9, 16),
            'persondetails' => array(2, 14),
            'news' => array(5, 7, 11),
            'download' => array(6, 10)
        );
    }

    /**
     * Fetches data specified by the given route. If an entry for the given
     * route is found in the database and the entry is still valid according
     * to the given timestamp, it is returned. Otherwise, a REST call to
     * the Stud.IP server is created, result is written to database for
     * caching.
     *
     * @param String $route the route to get data for.
     * @param int $validfor how long is database entry valid (in minutes)?
     * @return mixed
     */
    private static function getData($route, $validfor) {
        $cached = $GLOBALS['TYPO3_DB']->exec_SELECTquery('data, chdate',
            'tx_importstudip_config', 'route='.$route, '', '', 1);
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($cached);
        if ($row && $row['chdate'] >= time() - $validfor) {
            $data = $row['data'];
            $GLOBALS['TYPO3_DB']->sql_free_result($cached);
        } else {
            $rest = new StudipRESTHelper();
            $data = $rest->call($route);
            if ($row) {
                $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
                    'tx_importstudip_config',
                    'route='.$route,
                    array('data' => $data, 'chdate' => time())
                );
            } else {
                $GLOBALS['TYPO3_DB']->exec_INSERTquery(
                    'tx_importstudip_config',
                    array(
                        'route' => $route,
                        'data' => $data,
                        'mkdate' => time(),
                        'chdate' => time()
                    )
                );
            }
        }
        return $data;
    }

}
