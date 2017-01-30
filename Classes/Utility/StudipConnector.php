<?php

/**
 * Handles the connection and data fetching from Stud.IP
 *
 * This software is published under the GNU General Public License version 2.
 * The license text can be found at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @category   Extension
 * @package    ImportStudip
 * @subpackage Utility
 * @author     Thomas Hackl <thomas.hackl@uni-passau.de>
 */

namespace UniPassau\Importstudip\Utility;

class StudipConnector {

    /**
     * Fetches all available tyoes for external page configurations
     * (like person or course lists) and categorizes them.
     *
     * @return Array External page types available in this Stud.IP.
     */
    public static function getExternConfigTypes()
    {
        $data = json_decode(self::getData('extern/externalpagetypes'));

        /*
         * Check for available config types and enable corresponding
         * "abstract" type.
         */
        foreach ($data as $entry) {
            switch ($entry) {
                // Course lists.
                case 3:
                case 8:
                case 12:
                case 15:
                    $courses = true;
                    break;

                // Course details
                case 4:
                case 13:
                    $coursedetails = true;
                    break;

                // Person lists
                case 1:
                case 9:
                case 16:
                    $persons = true;
                    break;

                // Person details
                case 2:
                case 14:
                    $persondetails = true;
                    break;

                // News
                case 5:
                case 7:
                case 11:
                    $news = true;
                    break;

                // Download
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
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('backend.externtype.courses', 'importstudip'),
                'courses'
            );
        }
        if ($coursedetails) {
            $types[] = array(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('backend.externtype.coursedetails', 'importstudip'),
                'coursedetails'
            );
        }
        if ($persons) {
            $types[] = array(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('backend.externtype.persons', 'importstudip'),
                'persons'
            );
        }
        if ($persondetails) {
            $types[] = array(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('backend.externtype.persondetails', 'importstudip'),
                'persondetails'
            );
        }
        if ($news) {
            $types[] = array(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('backend.externtype.news', 'importstudip'),
                'news'
            );
        }
        if ($download) {
            $types[] = array(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('backend.externtype.download', 'importstudip'),
                'download'
            );
        }
        return $types;
    }

    /**
     * Gets the Stud.IP institute hierarchy (according to given hierarchy type)
     * filtered by external page type.
     *
     * @param String $treetype Which hierarchy to use? extended range tree or
     *                         just real institutes?
     * @param $externtype Get only institutes which provide the given external
     *                    page type.
     * @return Array Stud.IP institutes sorted by their hierarchy.
     */
    public static function getInstitutes($treetype, $externtype='')
    {
        $result = array();
        if ($treetype) {
            $mapping = self::getTypeMapping();
            if ($treetype == 'rangetree') {
                $route = 'extern/rangetree' . ($externtype ? '/' . implode(',', $mapping[$externtype]) : '');
            } else {
                $route = 'extern/institutes' . ($externtype ? '/' . implode(',', $mapping[$externtype]) : '');
            }
            $result = self::getData($route);
        }
        return $result;
    }

    public static function getExternConfigurations($institute, $type)
    {
        $result = array();
        if ($institute) {
            $mapping = self::getTypeMapping();
            $route = 'extern/externconfigs/' . $institute;
            if ($mapping[$type]) {
                $route .= '/' . implode(',', $mapping[$type]);
            }
            $result = self::getData($route);
        }
        return $result;
    }

    public static function getExternConfigData($configid)
    {
        $result = array();
        if ($configid) {
            $result = self::getData('extern/externconfig/' . $configid);
        }
        return $result;
    }

    public static function getUser($user_id) {
        $result = array();
        if ($user_id) {
            $data = json_decode(self::getData('extern/user/' . $user_id), true);
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
        }
        return $result;
    }

    public static function searchUser($searchterm)
    {
        $result = array();
        if ($searchterm) {
            $result = self::getData('extern/usersearch/' . rawurlencode($searchterm), false);
        }
        return $result;
    }

    public static function getUserInstitutes($user_id)
    {
        $result = array();
        if ($user_id) {
            $result = self::getData('extern/user_institutes/' . $user_id);
        }
        return $result;
    }

    public static function searchCourse($searchterm, $semester_id='')
    {
        $result = array();
        if ($searchterm) {
            $call = 'extern/coursesearch/' . rawurlencode($searchterm);
            if ($semester_id) {
                $call .= '/' . $semester_id;
            }
            $result = self::getData($call, false);
        }
        return $result;
    }

    public static function frontendSearchCourse($searchterm, $semester_id='', $institute_id='', $coursetype='')
    {
        $result = array();
        if ($searchterm) {
            $call = 'extern/extendedcoursesearch/' . rawurlencode($searchterm);
            if ($semester_id || $institute_id || $coursetype) {
                $call .= '/' . ($semester_id ?: 0) . '/' . ($institute_id ?: 0) . '/' . ($coursetype ?: 0);
            }
            $result = self::getData($call, false);
        }
        return $result;
    }

    public static function getCourse($course_id)
    {
        $result = array();
        if ($course_id) {
            $result = self::getData('extern/course/' . $course_id);
        }
        return $result;
    }

    public static function getInstitute($institute_id)
    {
        $result = array();
        if ($institute_id) {
            $result = self::getData('extern/institute/' . $institute_id);
        }
        return $result;
    }

    public static function getAllSemesters() {
        return self::getData('extern/allsemesters');
    }

    public static function getCourseTypes($institute='')
    {
        return self::getData('extern/coursetypes'.($institute ? '/'.$institute : ''));
    }

    public static function getSubjects($parent_id, $depth)
    {
        return self::getData('extern/semtree/'.$parent_id.'/'.$depth);
    }

    public static function getStatusgroupNames($institute)
    {
        return self::getData('extern/statusgroupnames/'.$institute);
    }

    private static function getTypeMapping()
    {
        return array(
            'courses' => array(3, 8, 12, 15),
            'coursedetails' => array(4, 13),
            'persons' => array(1, 9, 16),
            'persondetails' => array(2, 14),
            'news' => array(5, 7, 11),
            'download' => array(6, 10)
        );
    }

    private static function getModuleMapping()
    {
        return array(
            1 => 'Persons',
            2 => 'Persondetails',
            3 => 'Lectures',
            4 => 'Lecturedetails',
            5 => 'News',
            6 => 'Download',
            7 => 'Newsticker',
            8 => 'Lecturestable',
            9 => 'TemplatePersons',
            10 => 'TemplateDownload',
            11 => 'TemplateNews',
            12 => 'TemplateLectures',
            13 => 'TemplateLecturedetails',
            14 => 'TemplatePersondetails',
            15 => 'TemplateSemBrowse',
            16 => 'TemplatePersBrowse'
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
     * @param bool $caching cache the result to database?
     * @return mixed
     */
    private static function getData($route, $caching=true)
    {
        $config = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['importstudip']);
        $validfor = intval($config['config_cache_lifetime']) * 60 * 1000;
        if ($caching) {
            $cached = $GLOBALS['TYPO3_DB']->exec_SELECTquery('data, chdate',
                'tx_importstudip_config', "route='" . $route . "'");
            $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($cached);
        }
        if ($row && $row['chdate'] >= time() - $validfor) {
            $data = $row['data'];
            $GLOBALS['TYPO3_DB']->sql_free_result($cached);
        } else {
            $rest = new StudipRESTHelper();
            $data = $rest->call($route);
            if ($caching) {
                if ($row) {
                    $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
                        'tx_importstudip_config',
                        'route=' . $route,
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
        }
        return $data;
    }

}
