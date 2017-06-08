<?php

/**
 * Stud.IP external page handling.
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

class StudipExternalPage
{

    /**
     * Fetch an external page from Stud.IP as specified by given settings.
     *
     * @param int $pageid the page we are currently on (is needed for link rewriting
     * @param int $elementid the element we are currently in (is needed for link rewriting)
     * @param Array $settings extension settings as configured by user for this element.
     * @return string The HTML snippet as generated by Stud.IP.
     */
    public static function get($pageid, $elementid, $settings, $uribuilder)
    {

        // Load global extension config as we need the URL for Stud.IP extern.php.
        $extconfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['importstudip']);

        // Create the correct URL from given settings.
        $url = self::buildStudipURL($settings, $extconfig['studip_externphp_path']);

        $error = false;

        // Caching settings.
        $config = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['importstudip']);

        $validfor = intval($config['config_cache_lifetime']) * 60;

        // Fetch cached content if available.
        $cached = $GLOBALS['TYPO3_DB']->exec_SELECTquery('content, chdate',
            'tx_importstudip_externalpages', "url='" . $url . "'");
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($cached);

        // Cached content exists and is not expired, use it.
        if ($row && $row['chdate'] >= time() - $validfor) {

            $html = utf8_decode($row['content']);
            $GLOBALS['TYPO3_DB']->sql_free_result($cached);

        // No cached content or expired -> fetch page from Stud.IP.
        } else {

            // Set timeout for HTTP call.
            $timeout = 10;

            /* Retrieve the external page content from Stud.IP. Just a HTTP(S) call. */

            // Use CURL if available as it is faster than file_get_contents.
            if (in_array('curl', get_loaded_extensions())) {

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
                $html = trim(curl_exec($curl));

                // No result because of code 404 "Not found".
                if (curl_getinfo($curl, CURLINFO_HTTP_CODE) == 404) {
                    $error = true;
                    /*throw new \TYPO3\CMS\Extbase\Property\Exception\TargetNotFoundException(
                        trim(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                            'frontend.text.error_studip_404',
                            'importstudip')));*/
                    $html = trim(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'frontend.text.error_studip_404',
                        'importstudip'));
                }
                curl_close($curl);

            // No CURL, try file_get_contents instead.
            } else {

                // We need allow_url_fopen for this functionality to work.
                if (ini_get('allow_url_fopen')) {

                    // Create a HTTP context (for easy timeout setting)
                    $ctx = stream_context_create(array(
                        'http' => array(
                            'ignore_errors' => true,
                            'timeout' => $timeout,
                            'http_protocol' => '1.1'
                        )
                    ));

                    $html = trim(@file_get_contents($url, false, $ctx));

                    // No result because of code 404 "Not found".
                    if (!$html && strpos($http_response_header[0], '404') !== false) {
                        $error = true;
                        /*throw new \TYPO3\CMS\Extbase\Property\Exception\TargetNotFoundException(
                            trim(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                                'frontend.text.error_studip_404',
                                'importstudip')));*/
                        $html = trim(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                            'frontend.text.error_studip_404',
                            'importstudip'));
                    }

                // No allow_url_fopen, show error message and write to syslog.
                } else {

                    $error = true;

                    $html = trim(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'frontend.text.error_no_url_fopen',
                        'importstudip'));

                    $logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                        'TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
                    $logger->error('Cannot transfer Stud.IP data for '.
                        'frontend. Please enable either the CURL extension '.
                        'for PHP or allow_url_fopen in php.ini.');

                }

            }

            // No or too slow response from Stud.IP :(
            if ($html === false) {
                // Try to load cached content.
                if ($row) {
                    $html = $row['content'];
                } else {
                    $error = true;

                    $html = trim(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'frontend.text.error_studip_unavailable',
                        'importstudip'));
                }
            }

            if (!$error) {
                // Expired content available, replace it.
                if ($row) {
                    // Update existing row.
                    $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
                        'tx_importstudip_externalpages',
                        "url='" . $url . "'",
                        array('content' => utf8_encode(trim($html)), 'chdate' => time())
                    );
                // Nothing cached yet, create new entry.
                } else {
                    // Insert new row.
                    $GLOBALS['TYPO3_DB']->exec_INSERTquery(
                        'tx_importstudip_externalpages',
                        array(
                            'url' => $url,
                            'content' => utf8_encode(trim($html)),
                            'mkdate' => time(),
                            'chdate' => time()
                        )
                    );
                }

            }
        }

        // Rewrite links to Stud.IP in content.
        $html = self::rewriteStudipLinks($html, $pageid, $elementid, $extconfig, $settings, $uribuilder);

        return $html;

    }

    /**
     * Creates an URL to the external page that contains the desired data.
     *
     * @param Array $settings extension settings as configured by user for this element.
     * @param String $studippath Path to Stud.IP extern.php as given in extension config
     * @return string The external page URL.
     */
    private static function buildStudipURL($settings, $studippath)
    {
        $studip = $studippath;

        // Check and set if some parameters are already given by called TYPO3 page URL.
        $urlparams = self::urlParameters();

        $params = array();

        // Parameters provided by GET, use these.
        if ($urlparams) {

            $params['range_id'] = $urlparams['range_id'];
            $params['config_id'] = $urlparams['config_id'];
            $params['module'] = $urlparams['module'];

            // Initial for person browser.
            if ($initial = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('initial')) {
                $params['ext_templatepersbrowse[initiale]'] = $initial;
            }

            // Item_id for person browser.
            if ($item = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('ext_templatepersbrowse')) {
                $params['ext_templatepersbrowse[item_id]'] = $item['item_id'];
            }

            // Single username.
            if ($username = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('username')) {
                $params['username'] = $username;
            }

            // Single course ID.
            if ($course = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('seminar_id')) {
                $params['seminar_id'] = $course;
            }

            // Single news ID.
            if ($news = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('news_id')) {
                $params['news_id'] = $news;
            }

            // Aggregate over sub institutes?
            if ($aggregation = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('aggregation')) {
                $params['aggregation'] = true;
            }

            // Short news display.
            if (\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('shortnews')) {
                $params['short'] = 1;
            }

            // Show courses not only at home, but also at participating institutes.
            if (\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('allseminars')) {
                $params['allseminars'] = 1;
            }

        // Nothing given per GET, use extension settings.
        } else {

            // The basic, necessary parameters...
            $params['module'] = $settings['module'];
            $params['config_id'] = $settings['externconfig'];
            // These are absolutely necessary.
            switch ($settings['pagetype']) {
                case 'coursedetails':
                    $params['range_id'] = $settings['choose_course_institute'];
                    break;
                case 'persondetails':
                    $params['range_id'] = $settings['choose_user_institute'];
                    break;
                default:
                    $params['range_id'] = $settings['institute'];
            }

            // ... everything else depends on set page type.
            switch ($settings['pagetype']) {

                // Show a list of courses.
                case 'courses':
                    // Show courses from subinstitutes, too.
                    if ($settings['aggregate']) {
                        $params['aggregation'] = 1;
                    }
                    // Show courses not only at home, but also at participating institutes.
                    if ($settings['participating']) {
                        $params['allseminars'] = 1;
                    }
                    // Show only a selected course type.
                    if ($settings['coursetype']) {
                        $params['semstatuses'] = $settings['coursetype'];
                    }
                    // Show only courses assigned to selected SemTree node (and its children).
                    if ($settings['subject']) {
                        $params['sem_tree_id'] = $settings['subject'];
                    }
                    break;
                // Show a single course.
                case 'coursedetails':
                    $params['seminar_id'] = $settings['coursesearch'];
                    break;
                // Show a list of persons.
                case 'persons':
                    // Show persons from subinstitutes, too.
                    if ($settings['aggregate']) {
                        $params['aggregation'] = 1;
                    }
                    // Show only persons in selected statusgroup.
                    if ($settings['statusgroup']) {
                        $params['visible_groups'] = $settings['statusgroup'];
                    }
                    break;
                // Show a single person.
                case 'persondetails':
                    $params['username'] = $settings['personsearch'];
                    break;
                // Show news
                case 'news':
                    if ($settings['smallnews']) {
                        $params['short'] = 1;
                    }
                    break;

            }

        }

        // Now iterate over set parameters and build the URL.
        $first = true;
        foreach ($params as $name => $value) {

            // First parameter needs a '?' instead of '&'.
            if ($first && strpos($studip, '?') === false) {
                $studip .= '?';
            } else {
                $studip .= '&';
            }

            $studip .= $name . '=' . $value;
            $first = false;
        }

        return $studip;
    }

    /**
     * Check if extension parameters are given via GET and set them accordingly
     *
     * @return Array The parameters set by GET values.
     */
    public static function urlParameters()
    {

        $set = array();

        $module = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('module');
        $config = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('config_id');
        $range = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('range_id');

        if ($module && $config && $range) {
            $set = array(
                'module' => $module,
                'config_id' => $config,
                'range_id' => $range,
            );
        }

        return $set;
    }

    /**
     * Rewrites links pointing to Stud.IP extern.php so that they stay
     * in TYPO3 context.
     *
     * @param string $html The HTML snippet generated by Stud.IP external page mechanism
     * @param int $pageid ID of the current page
     * @param int $elementid ID of the current content element
     * @param array $extconfig
     * @param array $params
     * @param TYPO3\Flow\Mvc\Routing\UriBuilder $uribuilder funcionality for building TYPO3 URLs
     * @return string The HTML with rewritten links.
     */
    private static function rewriteStudipLinks($html, $pageid, $elementid, $extconfig, $params, $uribuilder)
    {
        $oldpath = $extconfig['studip_externphp_path'].'?';

        $find = array();
        $replace = array();

        // Rewrite links to person details.
        if (in_array($params['pagetype'], array('', 'courses', 'coursedetails', 'persons', 'news'))) {
            $find[] = $oldpath.'module=Persondetails';
            $find[] = $oldpath.'module=TemplatePersondetails';
            $target = $params['persondetailtarget'] ?
                self::getTargetPage($params['persondetailtarget']) :
                $pageid;
            $element = $params['persondetailtarget'] ?: $elementid;
            $newpath = self::buildTargetLink($target, $element, $uribuilder);
            $replace[] = $newpath.'module=Persondetails';
            $replace[] = $newpath.'module=TemplatePersondetails';
        }

        // Rewrite links to course details.
        if (in_array($params['pagetype'], array('', 'courses', 'persondetails'))) {
            $find[] = $oldpath.'module=Lecturedetails';
            $find[] = $oldpath.'module=TemplateLecturedetails';
            $target = $params['coursedetailtarget'] ?
                self::getTargetPage($params['coursedetailtarget']) :
                $pageid;
            $element = $params['coursedetailtarget'] ?: $elementid;
            $newpath = self::buildTargetLink($target, $element, $uribuilder);
            $replace[] = $newpath.'module=Lecturedetails';
            $replace[] = $newpath.'module=TemplateLecturedetails';
        }

        // Rewrite links to news details.
        if (in_array($params['pagetype'], array('', 'news'))) {
            $find[] = $oldpath.'module=News';
            $find[] = $oldpath.'module=TemplateNews';
            $target = $params['newsdetailtarget'] ?
                self::getTargetPage($params['newsdetailtarget']) :
                $pageid;
            $element = $params['newsdetailtarget'] ?: $elementid;
            $newpath = self::buildTargetLink($target, $element, $uribuilder);
            $replace[] = $newpath.'module=News';
            $replace[] = $newpath.'module=TemplateNews';
        }

        // Rewrite browsing links.
        if (in_array($params['pagetype'], array('', 'persons'))) {
            $find[] = $oldpath.'module=TemplatePersBrowse';
            $target = $params['browsingtarget'] ?
                self::getTargetPage($params['browsingtarget']) :
                $pageid;
            $element = $params['browsingtarget'] ?: $elementid;
            $newpath = self::buildTargetLink($target, $element, $uribuilder);
            $replace[] = $newpath.'module=TemplatePersBrowse';
            $find[] = 'ext_templatepersbrowse[initiale]=';
            $replace[] = 'initial=';
        }
        if (in_array($params['pagetype'], array('', 'courses'))) {
            $find[] = $oldpath.'module=TemplateSemBrowse';
            $target = $params['browsingtarget'] ?
                self::getTargetPage($params['browsingtarget']) :
                $pageid;
            $element = $params['browsingtarget'] ?: $elementid;
            $newpath = self::buildTargetLink($target, $element, $uribuilder);
            $replace[] = $newpath.'module=TemplateSemBrowse';
            $find[] = 'ext_templatepersbrowse[item_id]=';
            $replace[] = 'item_id=';
        }

        $html = str_replace($find, $replace, $html);

        return $html;
    }

    /**
     * Creates a link to the given TYPO3 page.
     *
     * @param int $pid the target page
     * @param int $uid the target content element (passed as GET parameter "target")
     * @param TYPO3\Flow\Mvc\Routing\UriBuilder $uribuilder
     * @return string A link to the given TYPO3 page with given element as target.
     */
    private static function buildTargetLink($pid, $uid, $uribuilder)
    {
        $newpath = $uribuilder
            ->reset()
            ->setTargetPageUid($pid)
            ->setCreateAbsoluteUri(true)
            ->buildFrontendUri();
        if (strpos($newpath, '?') !== false) {
            $newpath .= '&';
        } else {
            $newpath .= '?';
        }
        $newpath .= 'target='.$uid.'&';
        return $newpath;
    }

    /**
     * Fetches the page the given content element belongs to.
     *
     * @param int $uid content element to get page for
     * @return int ID of the TYPO3 page containing the given content element.
     */
    public static function getTargetPage($uid)
    {
        $dbData = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('pid', 'tt_content', 'uid='.$uid);
        return $dbData['pid'];
    }

}
