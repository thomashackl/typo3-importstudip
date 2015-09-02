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

namespace UniPassau\ImportStudip\Utility;

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

        // Retrieve the external page content from Stud.IP. Just a HTTP(S) call.
        $html = file_get_contents($url);

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

        // Parameters provided by GET, use these.
        if ($urlparams) {

            // These are absolutely necessary.
            $params['range_id'] = $urlparams['range_id'];
            $params['config_id'] = $urlparams['config_id'];
            $params['module'] = $urlparams['module'];

            // Initial for person browser.
            if ($urlparams['initial']) {
                $params['initial'] = $urlparams['initial'];
            }

            // Single username.
            if ($username = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('username')) {
                $params['username'] = $username;
            }

            // Single course ID.
            if ($course = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('seminar_id')) {
                $params['seminar_id'] = $course;
            }

            // Aggregate over sub institutes?
            if ($aggregation = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('aggregate')) {
                $params['aggregate'] = true;
            }

            // Item_id for course browser.
            if ($item = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('item_id')) {
                $params['item_id'] = $item;
            }

        // Nothing given per GET, use extension settings.
        } else {

            // The basic, necessary parameters...
            $params['module'] = $settings['module'];
            $params['config_id'] = $settings['externconfig'];
            $params['range_id'] = $settings['institute'];

            // ... everything else depends on set page type.
            switch ($settings['pagetype']) {

                // Show a single course.
                case 'coursedetails':
                    $params['seminar_id'] = $settings['coursesearch'];
                    break;
                // Show a single person.
                case 'persondetails':
                    $params['username'] = $settings['personsearch'];
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
    private static function urlParameters()
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

        // Rewrite links to person details.
        if (in_array($params['pagetype'], array('', 'courses', 'coursedetails', 'persons'))) {
            $target = $params['persondetailtarget'] ?
                self::getTargetPage($params['persondetailtarget']) :
                $pageid;
            $element = $params['persondetailtarget'] ?: $elementid;
            $newpath = self::buildTargetLink($target, $element, $uribuilder);
            $html = str_replace($oldpath, $newpath, $html);
        }

        // Rewrite links to course details.
        if (in_array($params['pagetype'], array('', 'courses', 'persondetails'))) {
            $target = $params['coursedetailtarget'] ?
                self::getTargetPage($params['coursedetailtarget']) :
                $pageid;
            $element = $params['coursedetailtarget'] ?: $elementid;
            $newpath = self::buildTargetLink($target, $element, $uribuilder);
            $html = str_replace($oldpath, $newpath, $html);
        }

        // Rewrite links to news details.
        if (in_array($params['pagetype'], array('', 'news'))) {
            $target = $params['newsdetailtarget'] ?
                self::getTargetPage($params['newsdetailtarget']) :
                $pageid;
            $element = $params['newsdetailtarget'] ?: $elementid;
            $newpath = self::buildTargetLink($target, $element, $uribuilder);
            $html = str_replace($oldpath, $newpath, $html);
        }

        // Rewrite browsing links.

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
    private static function getTargetPage($uid)
    {
        $dbData = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('pid', 'tt_content', 'uid='.$uid);
        return $dbData['pid'];
    }

}