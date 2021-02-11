<?php

/**
 * ImportStudip Controller
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
 * @subpackage Controller
 * @author     Thomas Hackl <thomas.hackl@uni-passau.de>
 */

namespace UniPassau\Importstudip\Controller;

use UniPassau\Importstudip\Utility\StudipConnector;
use UniPassau\Importstudip\Utility\StudipExternalPage;

class ImportStudipController extends \TYPO3\CMS\Extbase\MVC\Controller\ActionController {

    private $limit = 50;

    public function InitializeAction()
    {
        // Check for site charset and set local variable accordingly
        if (stripos($GLOBALS['TSFE']->metaCharset, 'utf') !== false) {
            $this->utf = true;
        }
    }

    /**
     * Standard action for showing content when the extension is called.
     */
    public function indexAction()
    {
        $this->view->assign('showsearch', $this->settings['pagetype'] == 'searchpage');
        $this->view->assign('phonebook', $this->settings['pagetype'] == 'phonebook');

        session_start();

        // Generate form for course search.
        if ($this->settings['pagetype'] == 'searchpage') {

            $semesters = $this->getSemesters();
            $this->view->assign('semesters', array_reverse($semesters['semesters']));
            // Current semester is pre-selected
            $this->view->assign('semester', $semesters['current']);

            // Get all institutes and build a value => name array.
            $this->view->assign('institutes', $this->getInstitutes());
            // If a pre-selected institute is set in backend, set it.
            $this->view->assign('institute', $this->settings['preselectinst']);

            // Get all coursetypes.
            $coursetypes = $this->getCourseTypes();

            $this->view->assign('coursetypes', $coursetypes);

            /*
             * Provide current UID so that we know which content element should
             * handle the search result in case the are several on this page.
             */
            $this->view->assign('target', intval($this->configurationManager->getContentObject()->data['uid']));

        // Generate form for phonebook and corresponding search.
        } else if ($this->settings['pagetype'] == 'phonebook') {

            /*
             * Provide current UID so that we know which content element should
             * handle the search result in case the are several on this page.
             */
            $this->view->assign('target', intval($this->configurationManager->getContentObject()->data['uid']));

            $this->view->assign('searchterm', '');

            // Assign fields to search in
            $this->view->assign('phone_number', true);
            $this->view->assign('person_name', true);
            $this->view->assign('institute_name', true);
            $this->view->assign('institute_holder', false);

        } else {

            // Get current UID.
            $uid = intval($this->configurationManager->getContentObject()->data['uid']);

            if ($this->settings['makelink'] && !StudipExternalPage::urlParameters($uid)) {

                $this->view->assign('makelink', true);
                // Create a link that will fetch the configured Stud.IP external page.
                $this->view->assign('link', $this->makeLink());
                $this->view->assign('linktext', $this->settings['linktext']);
                $this->view->assign('linkformat', $this->settings['linkformat']);

            } else {

                // No GET Parameters set -> we come from extension configuration.
                // Set session variable so that detail pages may be called safely.
                if (!StudipExternalPage::urlParameters($uid)) {

                    $GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_importstudip_tracker', true);
                    $GLOBALS['TSFE']->fe_user->storeSessionData();

                    $_SESSION['tx_importstudip_tracker'] = true;

                    // Fetch Stud.IP external page.
                    $content = StudipExternalPage::get(intval($GLOBALS['TSFE']->id),
                        $uid, $this->settings, $this->controllerContext->getUriBuilder());

                // We have some GET parameters, so we need to check if we have a valid session
                // and not some direct Google call to a detail page.
                } else if ($_SESSION['tx_importstudip_tracker']) {

                    // Fetch Stud.IP external page.
                    $content = StudipExternalPage::get(intval($GLOBALS['TSFE']->id),
                        intval($this->configurationManager->getContentObject()->data['uid']),
                        $this->settings, $this->controllerContext->getUriBuilder());

                // GET parameters set but no session -> deliver no content but an error.
                } else {

                    $content = '';
                    $this->response->setStatus(404);

                }

            }

            // UTF8-encode the content if necessary.
            if (!$this->utf) {
                $content = utf8_decode($content);
            }

            // Assign Stud.IP output to view.
            $this->view->assign('studipcontent', $content);

        }
    }

    public function searchcourseAction()
    {
        // Get current UID.
        $uid = intval($this->configurationManager->getContentObject()->data['uid']);

        // Which content object should handle displaying search results?
        $target = intval(\TYPO3\CMS\Core\Utility\GeneralUtility::_POST('target'));

        if ($uid == $target) {

            /*
             * Provide current UID so that we know which content element should
             * handle the search result in case the are several on this page.
             */
            $this->view->assign('target', intval($this->configurationManager->getContentObject()->data['uid']));

            // Get request values.
            if ($this->request->hasArgument('searchterm')) {
                $searchterm = trim($this->request->getArgument('searchterm'));
                $this->view->assign('searchterm', $searchterm);
            }

            // Fill values for search form.
            $semesters = $this->getSemesters();
            $this->view->assign('semesters', array_reverse($semesters['semesters']));
            // Current semester is pre-selected
            $this->view->assign('semester', $semesters['current']);

            // Get all institutes and build a value => name array.
            $this->view->assign('institutes', $this->getInstitutes());
            // If a pre-selected institute is set in backend, set it.
            $this->view->assign('institute', $this->settings['preselectinst']);

            // Get all coursetypes and build a value => name array.
            $coursetypes = $this->getCourseTypes();
            $this->view->assign('coursetypes', $coursetypes);

            if ($this->request->hasArgument('semester')) {
                $semester = $this->request->getArgument('semester');
                $this->view->assign('semester', $semester);
            }
            if ($this->request->hasArgument('institute')) {
                $institute = $this->request->getArgument('institute');
                $this->view->assign('institute', $institute);
            }
            if ($this->request->hasArgument('coursetype')) {
                $coursetype = $this->request->getArgument('coursetype');
                $this->view->assign('coursetype', $coursetype);
            }

            if (trim($searchterm)) {
                // Get search results.
                $results = json_decode(
                    StudipConnector::frontendSearchCourse($searchterm, $semester, $institute, $coursetype), true);
                $this->view->assign('searchresults', $results);
                $this->view->assign('numresults', count($results));

                $configurationUtility = $this->objectManager->get(
                    'TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility');
                $config = $configurationUtility->getCurrentConfiguration('importstudip');

                $studip_url = $config['studip_url']['value'];
                if (substr($studip_url, 0, 1) == '/') {
                    $studip_url = substr($studip_url, 1);
                } else {
                    $studip_url = $studip_url;
                }
                $this->view->assign('studip_url', $studip_url);
            } else {
                $this->view->assign('nosearchterm', 1);
            }

        } else {

            $this->forward('index');

        }
    }

    public function phonebookAction()
    {
        // Get current UID.
        $uid = intval($this->configurationManager->getContentObject()->data['uid']);

        // Which content object should handle displaying search results?
        $target = intval($this->request->getArgument('target'));

        if ($uid == $target) {

            $this->view->assign('target', $target);

            /*
             * Provide current UID so that we know which content element should
             * handle the search result in case the are several on this page.
             */
            $this->view->assign('target', intval($this->configurationManager->getContentObject()->data['uid']));

            // Get request values.
            if ($this->request->hasArgument('searchterm')) {
                $searchterm = trim($this->request->getArgument('searchterm'));
                $this->view->assign('searchterm', $searchterm);
            }

            // Set fields to search in.
            $in = $this->request->getArgument('in');
            $this->view->assign('in', $in);
            foreach ($in as $one) {
                $this->view->assign($one, true);
            }

            if (trim($searchterm)) {
                // Get search results.
                $params = [
                    'in' => implode(',', $in),
                    'offset' => $this->request->hasArgument('page') ?
                        ($this->request->getArgument('page') - 1) * $this->limit : 0,
                    'limit' => $this->limit
                ];

                $results = json_decode(StudipConnector::frontendSearchPhonebook($searchterm, $params), true);

                $this->view->assign('searchResults', $results['collection']);
                $this->view->assign('currentResults', count($results['collection']));
                $this->view->assign('numResults', $results['pagination']['total']);

                $pageNumber = ceil($results['pagination']['total'] / $this->limit);
                $this->view->assign('numPages', $pageNumber);

                $currentPage = $this->request->hasArgument('page') ?
                    $this->request->getArgument('page') : 1;
                $this->view->assign('currentPage', $currentPage);
                $this->view->assign('previousPage', $currentPage - 1);
                $this->view->assign('nextPage', $currentPage + 1);

                /*
                 * Don't always show all pages in pagination, if there are too
                 * many, the layout would "explode". So just show the first,
                 * the last and the current pages with 2 pages around.
                 */
                $hasMorePages = $pageNumber > 5;
                $this->view->assign('hasMorePages', $hasMorePages);

                $lowerPageLimit = $hasMorePages ? max($currentPage - 2, 2) : 2;
                $this->view->assign('lowerPageLimit', $lowerPageLimit);
                $upperPageLimit = $hasMorePages ? min($currentPage + 2, $pageNumber - 1) : $pageNumber - 1;
                $this->view->assign('upperPageLimit', $upperPageLimit);
                // Stop counting upwards one entry before page count.
                $this->view->assign('stopCounting', $pageNumber - 1);

                // PageNumbers to show in pagination
                $this->view->assign('pages', $lowerPageLimit < $upperPageLimit ?
                    range($lowerPageLimit, $upperPageLimit) : []);

            } else {
                $this->view->assign('nosearchterm', 1);
            }

        } else {

            $this->forward('index');

        }
    }

    /**
     * AJAX endpoint for calls from extension Javascript.

     * @param array $params Array of parameters from the AJAX interface, currently unused
     * @param \TYPO3\CMS\Core\Http\AjaxRequestHandler|NULL $ajaxObj Object of type AjaxRequestHandler
     * @return void
     */
    public function handleAjax($params = array(), \TYPO3\CMS\Core\Http\AjaxRequestHandler &$ajaxObj = NULL)
    {
        $action = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('action');
        if (method_exists('UniPassau\\ImportStudip\\AjaxAction', $action)) {
            UniPassau\Importstudip\AjaxController::$action();
        } else {
            $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('message.rest_access_error', 'importstudip').
                    ' '.$response->response,
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('message.error', 'importstudip'),
                \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
            );
            return $message->render();
        }
    }

    /**
     * @param \TYPO3\CMS\Extbase\Mvc\RequestInterface $request
     * @param \TYPO3\CMS\Extbase\Mvc\ResponseInterface $response
     * @throws \Exception|\TYPO3\CMS\Extbase\Property\Exception
     */
    public function processRequest(\TYPO3\CMS\Extbase\Mvc\RequestInterface $request, \TYPO3\CMS\Extbase\Mvc\ResponseInterface $response) {
        try {
            parent::processRequest($request, $response);
        }
        catch(\TYPO3\CMS\Extbase\Property\Exception $e) {
            if ($e->getPrevious() instanceof \TYPO3\CMS\Extbase\Property\Exception\TargetNotFoundException) {
                $GLOBALS['TSFE']->pageNotFoundAndExit($e->getMessage());
            } else {
                throw $e;
            }
        }
    }

    private function getSemesters()
    {
        $semesters = array();
        $current = '';
        // Get all semesters and build a value => name array.
        foreach (json_decode(StudipConnector::getAllSemesters(), true) as $semester) {
            $semesters[$semester['semester_id']] = $semester['description'];
            if (!$current && !$semester['past']) {
                $current = $semester['semester_id'];
            }
        }
        return array('semesters' => $semesters, 'current' => $current);
    }

    private function getInstitutes()
    {
        return json_decode(StudipConnector::getInstitutes('institute'), true);
    }

    private function getCourseTypes()
    {
        $types = json_decode(StudipConnector::getCourseTypes(), true);

        $data = [];
        foreach ($types as $type) {
            if (!is_array($data[$type['typeclass']])) {
                $data[$type['typeclass']] = [
                    'id' => $type['typeclass'],
                    'name' => $type['classname'],
                    'types' => []
                ];
            }

            $data[$type['typeclass']]['types'][] = [
                'id' => $type['id'],
                'name' => $type['type']
            ];
        }

        return $data;
    }

    private function makeLink()
    {
        if ($this->settings['linktarget']) {
            $targetPage = StudipExternalPage::getTargetPage($this->settings['linktarget']);
            $targetElement = $this->settings['linktarget'];
        } else {
            $targetPage = $GLOBALS['TSFE']->id;
            $targetElement = intval($this->configurationManager->getContentObject()->data['uid']);
        }
        // Build base path to current page.
        $link = $this->controllerContext->getUriBuilder()
            ->reset()
            ->setTargetPageUid($targetPage)
            ->setCreateAbsoluteUri(true)
            ->buildFrontendUri();
        if (strpos($link, '?') !== false) {
            $link .= '&';
        } else {
            $link .= '?';
        }
        // Add current content element as target parameter.
        $link .= 'target='.$targetElement;

        // Add necessary parameters for Stud.IP call.
        // The basic parameters...
        $link .= '&module=' . $this->settings['module'];
        $link .= '&config_id=' . $this->settings['externconfig'];
        $link .= '&range_id=' . $this->settings['institute'];

        // ... everything else depends on set page type.
        switch ($this->settings['pagetype']) {

            // Show a single course.
            case 'coursedetails':
                $link .= '&seminar_id=' . $this->settings['coursesearch'];
                break;
            // Show a single person.
            case 'persondetails':
                $link .= '&username=' . $this->settings['personsearch'];
                break;

        }

        return $link;

    }

}
