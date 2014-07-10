<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012 Thomas Hackl <thomas.hackl@uni-passau.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

ini_set('max_execution_time', 180);

require_once(PATH_tslib."class.tslib_pibase.php");
require_once(t3lib_extMgm::extPath("importstudip")."/res/class.tx_importstudip_studipconnector.php");
require_once(t3lib_extMgm::extPath("importstudip")."/res/class.tx_importstudip_soap.php");
require_once(t3lib_extMgm::extPath("importstudip")."/res/class.tx_importstudip_subjectarealist.php");
require_once(t3lib_extMgm::extPath("importstudip")."/res/class.tx_importstudip_semtreeid.php");
require_once(t3lib_extMgm::extPath("importstudip")."/res/functions.inc.php");

/**
 * Plugin "Stud.IP data" for the "importstudip" extension.
 *
 * @author Thomas Hackl <thomas.hackl@uni-passau.de>
 * @author Philipp Weber <philipp.weber@uni-trier.de>
 * @author Andreas Mayer <andreas.mayer@uni-passau.de>
 * @package    TYPO3
 * @subpackage tx_importstudip
 */
class tx_importstudip_pi1 extends tslib_pibase {
    // Same as class name
    var $prefixId         = "tx_importstudip_pi1";
    // Path to this script relative to the extension dir.
    var $scriptRelPath    = "pi1/class.tx_importstudip_pi1.php";
    // The extension key.
    var $extKey           = "importstudip";
    // Configuration of the Stud.IP connector.
    var $connector        = null;
    // Configuration read from Flexform values or given URL parameters.
    var $parameters = array();
    // Internal debugging outputs.
    var $debug            = false;
    // Variables from global plugin config
    var $pluginConfig = array();
    // Current query string to Stud.IP server
    var $studipQueryString = "";
    
    /**
     * The main method of the PlugIn
     *
     * @param    string        $content: The PlugIn content
     * @param    array        $conf: The PlugIn configuration
     * @return    The content that is displayed on the website
     */
    function main($content,$conf)    {
        session_start();
        $this->pluginConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['importstudip']);
        if ($this->pluginConfig['debug']) {
            $this->debug = true;
        }
        $this->conf=$conf;
        $this->pi_loadLL();
        /*
         * Configuring so caching is not expected. This value means that
         * no cHash params are ever set. We do this, because it's a USER_INT 
         * object!
         */ 
        $this->pi_USER_INT_obj=0;

        // Init $lang instance which isn't always done automatically
        $GLOBALS['LANG'] = t3lib_div::makeInstance('language');
        $GLOBALS['LANG']->init($GLOBALS['BE_USER']->uc['lang']);

        // Initialize the FlexForm.
        $this->pi_initPIflexForm();
        $this->pi_setPiVarDefaults();
        $this->connector = new tx_importstudip_studipconnector(PATH_site.$GLOBALS["TYPO3_CONF_VARS"]["BE"]["fileadminDir"]."importstudip.conf");
        $this->parameters = $this->loadParameters();
        $soap = new tx_importstudip_soap();
        if (!$this->parameters["typo3"]["contentType"] ||
                $this->parameters["typo3"]["contentType"] == "show" || 
                ($this->urlParametersSet() && 
                t3lib_div::_GET("target") == $this->cObj->data["uid"])) {
            $content = $this->getStudIPExternalPage();
        } else if ($this->parameters["typo3"]["contentType"] == "link") {
            $content = $this->makeLink();
        } else if ($this->parameters["typo3"]["contentType"] == "lecturessearch") {
            $content = $this->showLectureSearchForm();
            if (t3lib_div::_POST("tx_is_do_search")) {
                $soap = new tx_importstudip_soap();
                $data = $soap->getLectureSearchResult(
                    t3lib_div::_POST("tx_is_txt"),
                    t3lib_div::_POST("tx_is_for"),
                    t3lib_div::_POST("tx_is_sem"), 
                    t3lib_div::_POST("tx_is_type"),
                    t3lib_div::_POST("tx_is_inst"));
                if (!is_array($data))
                    $data = array(); 
                $content .= $this->showLectureSearchResult($data); 
            }
        }
        return $this->pi_WrapInBaseClass(utf8_encode($content));
    }

    /**
     * Loads runtime parameters for Stud.IP call and TYPO3 configuration.
     *
     * @return An associative array containing all necessary runtime parameters.
     */
    function loadParameters() {
        $flexform = $this->cObj->data["pi_flexform"];
        $result = array();
        $result["studip"] = $this->loadStudIPParameters($flexform);
        $result["typo3"] = $this->loadTypo3Parameters($flexform);
        $result["cache"] = $this->loadCacheParameters($flexform);
        return $result;
    }
    
    /**
     * Loads all parameters necessary for calling Stud.IP. The 
     * parameters are loaded either from the flexform data (= TYPO3 backend)
     * or from given URL parameters.
     *
     * @param string $flexform Flexform data from the TYPO3 backend
     * @return An associative array containing all parameters. 
     */
    function loadStudIPParameters($flexform) {
        // GET-Parameters
        if ($this->urlParametersSet() && 
                t3lib_div::_GET("target") == $this->cObj->data["uid"]) {
            /*
             * Check for valid signature (necessary because of Google playing
             * around with URL parameters)
             */
            /*if (!$this->validSignature()) {
                header('HTTP/1.0 404 Not found');
                die();
            }*/
            
            // Institute ID
            $rangeID = t3lib_div::_GET("range_id");
            // Display only special subject area
            if (t3lib_div::_GET("home_faculty_sem_tree_id"))
                $homeSemTreeID = t3lib_div::_GET("home_faculty_sem_tree_id");
            // Module name
            $module = t3lib_div::_GET("module");
            // External page configuration ID
            $configID = t3lib_div::_GET("config_id");
            // Username for detail page
            if (t3lib_div::_GET("username"))
                $personID = t3lib_div::_GET("username");
            // Course ID for detail page
            if (t3lib_div::_GET("seminar_id"))
                $lectureID = t3lib_div::_GET("seminar_id");
            // Aggregation level for displaying sub-hierarchy
            if (t3lib_div::_GET("aggregation_level")) {
                $aggregate = "1";
                $aggregationLevel = $_GET["aggregation_level"];
            }
            // Show news from courses
            $semiNewsParameter = t3lib_div::_GET("seminews");
            if (isset($semiNewsParameter)) {
                $seminarNews = "1";
            }
            // Smaller news display
            if (t3lib_div::_GET("onecol")) {
                $newsOneCol = "1";
            }
            // Show single news
            if (t3lib_div::_GET("showid"))
                $newsID = t3lib_div::_GET("showid");
            // Initial for Person browser (alphabetically)
            if (t3lib_div::_GET("initial")) {
                $initial = t3lib_div::_GET("initial");
            }
            // Initial for Person browser (per institute)
            if (t3lib_div::_GET("item_id")) {
                $item_id = t3lib_div::_GET("item_id");
            }
        // Read parameters from flexform
        } else {
            // Only set these values if we are not in search form generation
            if ($this->pi_getFFvalue($flexform, "contentType") == "show" ||
                    $this->pi_getFFvalue($flexform, "contentType") == "link") {
                // Institute ID
                $rangeID = $this->pi_getFFvalue($flexform,
                    "institutes");
                // Configuration details are merged by ";"...
                $configValues = explode(";", $this->pi_getFFvalue($flexform,
                    "configs"));
                // ... first is module name ...
                $module = $configValues[0];
                // ... second is config ID.
                $configID = $configValues[1];
                /*
                 * Initialize all parameters as empty values so we just
                 * set the needed ones later on.
                 */
                // Person ID for detail page
                $personID = '';
                // Course ID for detail page
                $lectureID = '';
                // Set home semTree ID -> these subject area will be displayed first
                $homeSemTreeID = '';
                // Display only special subject area
                $mainSemTreeID = '';
                $semTreeID = '';
                // Show only special group of users
                $groupID = '';
                // Show sub-hierarchy
                $aggregate = '';
                // Aggregation level for displaying sub-hierarchy
                $aggregationLevel = '';
                // Show courses at participating institutes
                $allInstitutes = '';
                // Show only given course types
                $courseTypes = '';
                // Show news from courses
                $seminarNews = '';
                // Smaller news display
                $newsOneCol = '';
                $initial = '';
                $item_id = '';
                // Additional GET-parameter string given by an admin
                $additionalURLParameters = $this->pi_getFFvalue($flexform, 
                    "additionalURLParams");
                // Check only relevant parameters, ignoring all others.
                switch ($module) {
                    case 'Lectures':
                    case 'Lecturestable':
                        // Set home semTree ID -> these subject area will be displayed first
                        $homeSemTreeID = $this->pi_getFFvalue($flexform, "homeSemTreeID");
                        // Display only special subject area
                        $mainSemTreeID = $this->pi_getFFvalue($flexform, "mainSubjectAreas");
                        $semTreeID = $this->pi_getFFvalue($flexform, "subjectAreas");
                        // Show sub-hierarchy
                        $aggregate = $this->pi_getFFvalue($flexform, "aggregate");
                        // Aggregation level for displaying sub-hierarchy
                        $aggregationLevel = $this->pi_getFFvalue($flexform, "aggregationLevel")+1;
                        // Show courses at participating institutes
                        $allInstitutes = $this->pi_getFFvalue($flexform, "allInstitutes");
                        // Show only given course types
                        $courseTypes = $this->pi_getFFvalue($flexform, "courseTypes");
                        break;
                    case 'Lecturedetails':
                        // Course ID for detail page
                        $lectureID = $this->pi_getFFvalue($flexform, "lectures");
                        // Show courses at participating institutes
                        $allInstitutes = $this->pi_getFFvalue($flexform, "allInstitutes");
                        break;
                    case 'Persons':
                        // Show only special group of users
                        $groupID = $this->pi_getFFvalue($flexform, "groups");
                        // Show sub-hierarchy
                        $aggregate = $this->pi_getFFvalue($flexform, "aggregate");
                        // Aggregation level for displaying sub-hierarchy
                        $aggregationLevel = $this->pi_getFFvalue($flexform, "aggregationLevel");
                        break;
                    case 'Persondetails':
                        // Person ID for detail page
                        $personID = $this->pi_getFFvalue($flexform, "persons");
                        break;
                    case 'TemplatePersondetails':
                        // Person ID for detail page
                        $personID = $this->pi_getFFvalue($flexform, "contacts");
                        break;
                    case 'News':
                    case 'Newsticker':
                        // Show sub-hierarchy
                        $aggregate = $this->pi_getFFvalue($flexform, "aggregate");
                        // Aggregation level for displaying sub-hierarchy
                        $aggregationLevel = $this->pi_getFFvalue($flexform, "aggregationLevel");
                        // Show news from courses
                        $seminarNews = $this->pi_getFFvalue($flexform, "seminarNews");
                        // Smaller news display
                        $newsOneCol = $this->pi_getFFvalue($flexform, "newsOneCol");
                        break;
                    case 'Download':
                        // Course ID for detail page
                        $lectureID = $this->pi_getFFvalue($flexform, "lectures");
                        break;
                }
            } else {
                // Institute ID
                $rangeID = "";
                // Configuration details are merged by ";"...
                $configValues = "";
                // ... first is module name ...
                $module = "";
                // ... second is config ID.
                $configID = "";
                // Person ID for detail page
                $personID = "";
                // Course ID for detail page
                $lectureID = "";
                // Set home semTree ID -> these subject area will be displayed first
                $homeSemTreeID = "";
                // Display only special subject area
                $mainSemTreeID = "";
                $semTreeID = "";
                // Show only special group of users
                $groupID = "";
                // Show sub-hierarchy
                $aggregate = "";
                // Aggregation level for displaying sub-hierarchy
                $aggregationLevel = "";
                // Show only given course types
                $courseTypes = "";
                // Show courses at participating institutes
                $allInstitutes = "";
                // Show news from courses
                $seminarNews = "";
                // Smaller news display
                $newsOneCol = "";
                $initial = '';
                $item_id = '';
                // Additional GET-parameter string given by an admin
                $additionalURLParameters = $this->pi_getFFvalue($flexform, 
                    "additionalURLParams");
            }
        }
        /*
         * Set parameter names used in Stud.IP
         */
        $result = array(
                "module"   => $module,
                "range_id"  => $rangeID,
                "config_id" => $configID                
            );
        if ($homeSemTreeID)
            $result["home_faculty_sem_tree_id"] = $homeSemTreeID;
        if ($personID)
            $result["username"] = $personID;
        if ($lectureID)
            $result["seminar_id"] = $lectureID;
        if ($mainSemTreeID && !$semTreeID) {
            $result["sem_tree_id"] = $mainSemTreeID;
        } else if ($semTreeID) {
            $result["sem_tree_id"] = $semTreeID;
        }
        if ($groupID)
            $result["visible_groups"] = $groupID;
        if ($aggregate)
            $result["aggregation_level"] = intval($aggregationLevel)+1;
        if ($seminarNews)
            $result["seminews"] = $seminarNews;
        if ($courseTypes)
            $result["semstatuses"] = $courseTypes;
        if ($allInstitutes)
            $result["allseminars"] = $allInstitutes;
        if ($newsOneCol)
            $result["onecol"] = $newsOneCol;
        if ($newsID)
            $result["showid"] = $newsID;
        // Language
        if ($GLOBALS["TSFE"]->config["config"]["language"]) {
            $result["sl"] .= $GLOBALS["TSFE"]->config["config"]["language"];
        }
        if ($initial) {
            $result["ext_templatepersbrowse[initiale]"] = $initial;
        }
        if ($item_id) {
            $result["ext_templatepersbrowse[item_id]"] = $item_id;
        }
        // Explode additional parameters
        if ($additionalURLParameters)
            $result = array_merge($result, 
                $this->parseAdditionalParametersString(
                    $additionalURLParameters));
        return $result;
    }
    
    /**
     * Sets parameters relevant for display in TYPO3, such as page IDs for 
     * link targets.
     *
     * @param string $flexform Flexform data from the TYPO3 backend
     * @return An associative array containing all TYPO3 relevant parameters.
     */
    function loadTypo3Parameters($flexform) {
        $contentType = $this->pi_getFFvalue($flexform, "contentType");
        // Where do we come from?
        if (t3lib_div::_GET("source"))
            $source = t3lib_div::_GET("source");
        else
            $source = $this->cObj->data["uid"];
        /*
         * Person detail page requested, so it could be we have to jump to 
         * another page
         */
        if ($this->pi_getFFvalue($flexform, "personDetailTarget")) {
            $persondetailTarget["ext"] = $this->pi_getFFvalue($flexform, 
                "personDetailTarget");
            $persondetailTarget["page"] = 
                $this->getTargetPageID($persondetailTarget["ext"]);
        } else {
            if (!$this->pi_getFFvalue($flexform, "contentType")) {
                $persondetailTarget["ext"] = $_SESSION["typo3"]["tx_importstudip"][$source]["persondetailTarget"]["ext"];
                $persondetailTarget["page"] = $_SESSION["typo3"]["tx_importstudip"][$source]["persondetailTarget"]["page"];
            } else {
                $persondetailTarget["ext"] = $this->cObj->data["uid"];
                $persondetailTarget["page"] = $GLOBALS["TSFE"]->id;
            }
        }
        /*
         * Course detail page requested, so it could be we have to jump to 
         * another page
         */
        if ($this->pi_getFFvalue($flexform, "lectureDetailTarget")) {
            $lecturedetailTarget["ext"] = $this->pi_getFFvalue($flexform, 
                "lectureDetailTarget");
            $lecturedetailTarget["page"] = 
                $this->getTargetPageID($lecturedetailTarget["ext"]);
        } else {
            if (!$this->pi_getFFvalue($flexform, "contentType")) {
                $lecturedetailTarget["ext"] = $_SESSION["typo3"]["tx_importstudip"][$source]["lecturedetailTarget"]["ext"];
                $lecturedetailTarget["page"] = $_SESSION["typo3"]["tx_importstudip"][$source]["lecturedetailTarget"]["page"];
            } else {
                $lecturedetailTarget["ext"] = $this->cObj->data["uid"];
                $lecturedetailTarget["page"] = $GLOBALS["TSFE"]->id;
            }
        }
        /*
         * News detail page requested, so it could be we have to jump to 
         * another page
         */
        if ($this->pi_getFFvalue($flexform, "newsDetailTarget")) {
            $newsdetailTarget["ext"] = $this->pi_getFFvalue($flexform, 
                "newsDetailTarget");
            $newsdetailTarget["page"] = 
                $this->getTargetPageID($newsdetailTarget["ext"]);
        } else {
            if (!$this->pi_getFFvalue($flexform, "contentType")) {
                $newsdetailTarget["ext"] = $_SESSION["typo3"]["tx_importstudip"][$source]["newsdetailTarget"]["ext"];
                $newsdetailTarget["page"] = $_SESSION["typo3"]["tx_importstudip"][$source]["newsdetailTarget"]["page"];
            } else {
                $newsdetailTarget["ext"] = $this->cObj->data["uid"];
                $newsdetailTarget["page"] = $GLOBALS["TSFE"]->id;
            }
        }
        // Predefine values only relevant for special configuration options.
        $linkText = "";
        $linkFormat = array("start" => "", "end" => "");
        $linkTarget = array("ext" => "", "page" => "");
        $preselectedInstitute = "";
        if ($contentType == "link") {
            /*
             * A link generated by the extension was clicked, so it could 
             * be we have to jump to another page
             */
            if ($this->pi_getFFvalue($flexform, "linkTarget")) {
                $linkTarget["ext"] = $this->pi_getFFvalue($flexform, 
                    "linkTarget");
                $linkTarget["page"] = 
                    $this->getTargetPageID($linkTarget["ext"]);
            } else {
                $linkTarget["ext"] = $this->cObj->data["uid"];
                $linkTarget["page"] = $GLOBALS["TSFE"]->id;
            }
            /*
             * Check if a text for a generated link was given, if not, just 
             * write out "Link"
             */
            if ($this->pi_getFFvalue($flexform, "linkText")) {
                $linkText = $this->pi_getFFvalue($flexform, "linkText");
            } else {
                $linkText = "Link";
            }
            // Check if the link text should have a special format
            if ($this->pi_getFFvalue($flexform, "linkFormat")) {
                $linkFormat["start"] = "<".$this->pi_getFFvalue($flexform, "linkFormat").">";
                $linkFormat["end"] = "</".$this->pi_getFFvalue($flexform, "linkFormat").">";
            } else {
                $linkFormat["start"] = "";
                $linkFormat["end"] = "";
            }
        } else if ($contentType == "lecturessearch") {
            // Check if an institute should be preselected in search forms
            if ($this->pi_getFFvalue($flexform, "institutes")) {
                $preselectedInstitute = $this->pi_getFFvalue($flexform, "institutes");
            }
        }
        // Build an array from the parameters
        $result = array(
                "contentType"          => $contentType,
                "persondetailTarget"   => $persondetailTarget,
                "lecturedetailTarget"  => $lecturedetailTarget,
                "newsdetailTarget"     => $newsdetailTarget,
                "linkTarget"           => $linkTarget,
                "linkText"             => $linkText,
                "linkFormat"           => $linkFormat,
                "source"               => $source,
                "preselectedInstitute" => $preselectedInstitute
            );
        $_SESSION["typo3"]["tx_importstudip"][$this->cObj->data["uid"]] = $result;
        return $result;
    }
    
    /**
     * Can be used to pass special headers to a reverse proxy server. Not 
     * really implemented yet.
     *
     * @param string $flexform Flexform data from the TYPO3 backend
     */
    function loadCacheParameters($flexform) {
        
    }
    
    /**
     * Retrieves the page ID for a given content part ID.
     *
     * @param int $uid content part ID
     * @return The ID of the TYPO3 page containing the given content part.
     */
    function getTargetPageID($uid) {
        // Query table "tt_content"
        $dbResult = $GLOBALS["TYPO3_DB"]->exec_SELECTquery("pid", 
            "tt_content", "uid='".$uid."'");
        $dbData = mysql_fetch_assoc($dbResult);
        return $dbData["pid"];
    }
    
    /**
     * Splits the "additional parameters" string into name/value pairs.
     *
     * @param string $string GET-parameter string, like "n1=v1&n2=v2"
     * @return An associative array containing the extracted parameters.
     */
    function parseAdditionalParametersString($string) {
        // Split up by the "&" character
        $nameValuePairs = explode("&", $string);
        $result = array();
        foreach ($nameValuePairs as $item) {
            // Split name and value, separated by "="
            $parts = explode("=", $item);
            $name = $parts[0];
            $value = $parts[1];
            $result[$name] = $value;
        }
        return $result;
    }

    /**
     * Get an external page from the Stud.IP server.
     *
     * @return The external page from Stud.IP as string.
     */
    function getStudIPExternalPage() {
        $studipQueryString = $this->buildStudIPQueryString();

        $this->studipQueryString = $studipQueryString;

        $errno = 0;
        $errstring = "";
        // Using cache?
        if ($this->connector->useCache()) {
            // Cache = 2 (try cache, if not working, continue on Stud.IP server)
            if ($this->connector->getDirectConnectOnError()) {
                $connection = @fsockopen($this->connector->getCacheServer(), 80, 
                    $errno, $errstring, $this->connector->getTimeoutWait());
                $serverAddress = $this->connector->getCacheServer();
                if (!$connection) {
                    if ($this->connector->isSecureProtocol()) {
                        $protocol = "ssl://";
                        $port = 443;
                    } else {
                        $protocol = "";
                        $port = 80;
                    }
                    $connection = 
                        fsockopen($protocol.$this->connector->getStudIPServer(), 
                        $port, $errno, $errstring, 
                        $this->connector->getTimeoutWait());
                    $serverAddress = $this->connector->getStudIPServer();
                }
            // Only try cache server.
            } else {
                $connection = fsockopen($this->connector->getCacheServer(), 80, 
                    $errno, $errstring, $this->connector->getTimeoutWait());
                $serverAddress = $this->connector->getCacheServer();
            }
        // No cache, just using Stud.IP server.
        } else {
            if ($this->connector->isSecureProtocol()) {
                $protocol = "ssl://";
                $port = 443;
            } else {
                $protocol = "";
                $port = 80;
            }
            $connection = fsockopen($protocol.$this->connector->getStudIPServer(), $port, 
                $errno, $errstring, $this->connector->getTimeoutWait());
            $serverAddress = $this->connector->getStudIPServer();
        }
        if ($connection) {
            if ($this->debug) {
                t3lib_utility_Debug::debug('Connected to <b>'.$serverAddress.'</b>');
            }
            // Retrieve external page
            fputs ($connection, "GET ".checkStartSlash(
                $this->connector->getStudIPExternPHP())."?".$studipQueryString.
                " HTTP/1.0\r\n");

            if ($this->debug) {
                t3lib_utility_Debug::debug("Calling <b>".checkStartSlash($this->connector->getStudIPExternPHP())."?".$studipQueryString."</b>");
            }
                
            fputs ($connection, "Host: ".$this->connector->getStudIPServer()."\r\n");
            fputs ($connection, "Connection: close\r\n\r\n");
            $studipContent = "";
            if ($connection) {
                $header = true;
                // Is Transfer-Encoding set to chunked (HTTP 1.1)?
                $chunked = false;
                $chunkSize = 1;
                // Content-Length header on non-chunked response.
                $contentLength = 1;
                $i = 1;
                while (!feof($connection)) {
                    if ($header) {
                        $current = fgets($connection);
                        if ($current == "Transfer-Encoding: chunked\r\n") {
                            $chunked = true;
                        }
                        if (strpos("Content-length:", $current) !== false) {
                            $chunkSize = substr($current, strlen("Content-length: "));
                        }
                    } else {
                        $current = fread($connection, $chunkSize);
                        if ($chunked) {
                            // Get next chunk size.
                            $chunkData = fgets($connection);
                            // Decode chunk size.
                            $chunkSize = hexdec($chunkData);
                        } else if ($chunkSize == 1) {
                            $chunkSize = 4096;
                        }
                    }
                    // Header or chunk end
                    if ($current == "\r\n") {
                        $header = false;
                        if ($chunked) {
                            $chunkData = fgets($connection);
                            // Decode chunk size.
                            $chunkSize = hexdec($chunkData);
                        }
                    }
                    // Only add non-header content
                    if (!$header) {
                        $studipContent .= $current;
                    }
                    $i++;
                }
                fclose($connection);                
            }
            return $this->rewriteStudIPLinks($studipContent);
        } else {
            return "Wegen technischer Probleme sind momentan keine Daten ".
                "abrufbar. Bitte versuchen Sie es sp&auml;ter nochmals!";
        }
    }
    
    /**
     * Generates a link to content from Stud.IP. Clicking the link will 
     * retrieve the corresponding external page.
     *
     * @param array $data data for creating the link, such as text and parameters.
     * @return A link with the specified text to the target page/element.
     */
    function makeLink($data=false) {
        // Data given
        if ($data) {
            $linkText = $data["text"];
            $linkParameters = $data["parameters"];
        // No data given, read from local parameters
        } else { 
            $linkText = utf8_decode($this->parameters["typo3"]["linkText"]);
            $linkParameters = array_merge($this->parameters["studip"], array(
                "target" => $this->parameters["typo3"]["linkTarget"]["ext"]));
        }
        $linkParameters['hash'] = $this->makeSignature($linkParameters);
        // Check whether to stay on the current page.
        if ($this->parameters["typo3"]["linkTarget"]["page"]) {
            $linkTarget = $this->parameters["typo3"]["linkTarget"]["page"];
        } else {
            $linkTarget = $GLOBALS["TSFE"]->id;
        }
        // Format tag for link
        $link = $this->parameters["typo3"]["linkFormat"]["start"];
        // Create a TYPO3 link to the target page
        $link .= $this->pi_linkToPage($linkText, $linkTarget, "", 
            $linkParameters);
        // Close format tag
        $link .= $this->parameters["typo3"]["linkFormat"]["end"];
        return $link;
    }
    
    /**
     * Checks whether configuration parameters are passed in via GET. This 
     * happens when the user clicks on a link in generated content, e.g. a 
     * course title in a course list. The function also checks if the 
     * necessary parameters are complete - it only returns "true" if
     * module name, config ID and range ID are set.
     *
     * @return true or false
     */
    function urlParametersSet() {
        $parametersSet = false;
        if ((t3lib_div::_GET("module") || t3lib_div::_GET("initial")) && t3lib_div::_GET("config_id") && t3lib_div::_GET("range_id"))
            $parametersSet = true;
        return $parametersSet;
    }
    
    /**
     * Creates the query string for calling a Stud.IP external page. All 
     * parameters from the local Stud.IP parameter array are used.
     *
     * @return A String containing all Stud.IP parameters for a GET query.
     */
    function buildStudIPQueryString() {
        $studipQueryString = "";
        foreach ($this->parameters["studip"] as $name => $value) {
            if ($studipQueryString)
                $studipQueryString .= "&";
            $studipQueryString .= $name."=".$value;
        }
        return $studipQueryString;
    }
    
    /**
     * Rewrites links in external pages so that they don't point to the 
     * Stud.IP server but to TYPO3.
     *
     * @param string $data the external page
     * @return The external page (= $data) with rewritten links.
     */
    function rewriteStudIPLinks($data) {
        if ($this->debug) {
            t3lib_utility_Debug::debug('Now rewriting links in Stud.IP external page');
        }
        
        $current_query_hash = md5($this->studipQueryString);
        
        $filename = "fileadmin/cache/".$current_query_hash;

        if (file_exists($filename) && (filemtime($filename)+1800) > time() && !$GLOBALS['TSFE']->beUserLogin) {
            $file = fopen($filename, "r");
            $data = utf8_decode(fread($file, filesize($filename)));
            fclose($file);
            
            $cache = " Aus dem Cache geladen (".date("H:i:s",filemtime($filename)).")";
        } else {
            $cache = " Live generiert";
            
            $oldLink = $this->connector->getFullExternPHPLink();
            if ($this->debug) {
                t3lib_utility_Debug::debug("&nbsp;&nbsp;from <b>".$oldLink."</b>");
            }
            $newLink = $this->connector->getTypo3SitePath();
            if ($this->debug) {
                t3lib_utility_Debug::debug("&nbsp;&nbsp;to <b>".$newLink."</b>");
            }
            // Rewrite links to person details
            $data = $this->rewriteDetailLink("persondetail", $oldLink, $newLink, $data);
            // Rewrite links to course details
            $data = $this->rewriteDetailLink("lecturedetail", $oldLink, $newLink, $data);
            // Rewrite links to news details
            $data = $this->rewriteDetailLink("newsdetail", $oldLink, $newLink, $data);
            // Rewrite links for browsing.
            $data = $this->rewriteBrowsingLink($oldLink, $newLink, $data);
            /*
             * All external files have got a wrong domain name, replace with
             * correct one if necessary
             */
            //$data = str_replace($this->connector->getStudIPSendfilePHP().'?',
            //        'http://'.$this->connector->getStudIPServer().
            //        $this->connector->getStudIPSendfilePHP().'?',$data);
            /*
             * All external pages have "http://" as protocol, replace with
            * "https://" if necessary
            */
            $data = str_replace("http://".$this->connector->getStudIPServer(),
                    $this->connector->getStudIPProtocol()."://".
                    $this->connector->getStudIPServer(), $data);
            
            if (file_exists($filename)) {
                unlink($filename);
            }
            
            $file = fopen($filename, "a");
            fwrite($file, utf8_encode($data));
            fclose($file);
        }
       
        return "<!-- Cache hash: ".$current_query_hash.";".$cache." -->".$data;
    }
    
    /**
     * Rewrites a link to a detail page so that the link will point to the 
     * target page specified in the extension configuration
     *
     * @param string $kind what type of detail? Valid values are 
     *     "persondetail", "lecturedetail" and "newsdetail"
     * @param string $oldAddress the original link target
     * @param string $newAddress the new link target
     * @param string $data the external page
     * @return The external page with rewritten detail links.
     */
    function rewriteDetailLink($kind, $oldAddress, $newAddress, $data) {
        // Set module name
        if ($kind != "newsdetail") {
            $moduleName = ucfirst($kind)."s";
        } else {
            $moduleName = "News";
        }
        // Is there already a GET parameter in the page link?
        if (strpos($this->pi_getPageLink(
                $this->parameters["typo3"][$kind."Target"]["page"]), "?"))
            $separator = "&";
        else
            $separator = "?";
        // Generate new link...
        $newLink = $this->pi_getPageLink(
            $this->parameters["typo3"][$kind."Target"]["page"], "", 
            array(
                "module" => $moduleName,
                "target" => $this->parameters["typo3"][$kind."Target"]["ext"],
                "source" => $this->parameters["typo3"]["source"]));
        // ... and replace into external page
        
        $data = str_replace($this->connector->getStudIPProtocol().'://'.$oldAddress."?module=".$moduleName, 
                            checkEndSlash($this->connector->getTypo3Protocol().'://'.$newAddress).$newLink, $data);            
        
        // Extract get parameters
        $pattern = checkEndSlash($newAddress).$newLink;
        $pattern = str_replace("/", "\/", $pattern);
        $pattern = str_replace("?", "\?", $pattern);
        $pattern = str_replace("&", "\&", $pattern);
        
        $pattern = "/".$pattern."[^\"]*/";
        preg_match_all($pattern, $data, $url);

        $url[0] = array_values(array_unique($url[0]));
        
        for ( $i = 0; $i < count($url[0]); $i++ ) {
            // Get query string 
            $queryString = explode("?", $url[0][$i]);

            // Split string into get params
            $getParams = array();
            parse_str($queryString[count($queryString)-1], $getParams);

            // Create hash value
            $hash = $this->makeSignature($getParams);

            $data = str_replace($url[0][$i], $url[0][$i]."&hash=".$hash, $data);
            $count = 0;
        }

        return $data;
    }

    /**
     * Rewrites a link for browsing that the link will point to the
     * target page specified in the extension configuration
     *
     * @param string $oldAddress the original link target
     * @param string $newAddress the new link target
     * @param string $data the external page
     * @return The external page with rewritten links.
     */
    function rewriteBrowsingLink($oldAddress, $newAddress, $data) {
        // Is there already a GET parameter in the page link?
        if (strpos($this->pi_getPageLink($this->parameters["typo3"]["persondetailTarget"]["page"]), "?") !== false)
            $separator = "&";
        else
            $separator = "?";
        // Generate new link...
        $newLink = $this->pi_getPageLink($this->parameters["typo3"]["persondetailTarget"]["page"], "",
            array(
                'source' => $this->cObj->data["uid"],
                'target' => $this->parameters["typo3"]["persondetailTarget"]["ext"],
                'hash'   => $this->makeSignature($linkParameters)
            ));
        // ... and replace into external page

        $search = array(
            $this->connector->getStudIPProtocol().'://'.$oldAddress.'?',
            'ext_templatepersbrowse[initiale]',
            'ext_templatepersbrowse[item_id]'
        );
        $replace = array(
            checkEndSlash($this->connector->getTypo3Protocol().'://'.$newAddress).$newLink.'&',
            'initial',
            'item_id'
        );
        $data = str_replace($search, $replace, $data);
        return $data;
    }

    /**
     * Generates a HTML form so that students can search for Stud.IP courses
     * in TYPO3. There are several filter criteria such as semester, course
     * type or institute.
     *
     * @return HTML code for displaying the form.
     */
    function showLectureSearchForm() {
        global $LANG;
        $LANG->includeLLFile('EXT:importstudip/locallang.xml');
        $result = "";
        $result .= "<form action=\"".$this->pi_getPageLink($GLOBALS["TSFE"]->id)."\" method=\"POST\">\n";
        $result .= "<table class=\"lectures_search\">\n";
        // Search result will be opened in same page and element.
        $result .= "<tr>\n";
        $result .= "<td>\n";
        $result .= "<label for=\"tx_is_txt\">".$this->pi_getLL('LLL:EXT:importstudip/locallang.xml:pi_importstudip.search.searchterm', 'Suchbegriff:', true)."</label><br/>\n";
        // Search text
        $result .= "<input type=\"text\" name=\"tx_is_txt\" size=\"40\" maxlength=\"255\"";
        if (t3lib_div::_GET("tx_is_txt"))
            $result .= " value=\"".t3lib_div::_GET("tx_is_txt")."\"";
        $result .=  "/>\n";
        $result .= "</td>\n";
        $result .= "<td>\n";
        $result .= "<label for=\"tx_is_for\">".$this->pi_getLL('LLL:EXT:importstudip/locallang.xml:pi_importstudip.search.search_in', 'Suche in:', true)."</label><br/>\n";
        // Search in course title, lecturer, description or everything
        $result .= "<select name=\"tx_is_for\">\n";
        $result .= "<option value=\"all\">".$this->pi_getLL('LLL:EXT:importstudip/locallang.xml:pi_importstudip.search.search_all', 'alles', true)."</option>\n";
        $result .= "<option value=\"title\">".$this->pi_getLL('LLL:EXT:importstudip/locallang.xml:pi_importstudip.search.search_title', 'Titel', true)."</option>\n";
        $result .= "<option value=\"lecturer\">".$this->pi_getLL('LLL:EXT:importstudip/locallang.xml:pi_importstudip.search.search_lecturer', 'Dozierende', true)."</option>\n";
        $result .= "<option value=\"description\">".$this->pi_getLL('LLL:EXT:importstudip/locallang.xml:pi_importstudip.search.search_description', 'Beschreibung', true)."</option>\n";
        $result .= "<option value=\"number\">".$this->pi_getLL('LLL:EXT:importstudip/locallang.xml:pi_importstudip.search.search_number', 'Veranstaltungsnummer', true)."</option>\n";
        $result .= "</select>\n";
        $result .= "</td>\n";
        $result .= "</tr>\n";
        $result .= "<tr>\n";
        $result .= "<td>\n";
        $result .= "<label for=\"tx_is_sem\">".$this->pi_getLL('LLL:EXT:importstudip/locallang.xml:pi_importstudip.search.semester', 'Semester:', true)."</label><br/>\n";
        // Search only in given semester
        $result .= "<select name=\"tx_is_sem\">\n";
        $result .= "<option value=\"\">".$this->pi_getLL('LLL:EXT:importstudip/locallang.xml:pi_importstudip.search.all', 'alle', true)."</option>\n";
        $extSoap = new tx_importstudip_soap();
        $semesters = $extSoap->getSemesters();
        $currentTime = time();
        foreach ($semesters as $name => $value) {
            if ($currentTime >= $value["beginn"] && $currentTime <= $value["ende"])
                $selected = " selected=\"selected\"";
            else
                $selected = ""; 
            $result .= "<option value=\"".$value["beginn"]."\"".$selected.">".$value["name"]."</option>\n";
        }
        $result .= "</select>\n";
        $result .= "</td>\n";
        $result .= "<td>\n";
        $result .= "<label for=\"tx_is_type\">".$this->pi_getLL('LLL:EXT:importstudip/locallang.xml:pi_importstudip.search.coursetype', 'Veranstaltungstyp:', true)."</label><br/>\n";
        // Search only courses of the given type
        $result .= "<select name=\"tx_is_type\">\n";
        $result .= "<option value=\"\">".$this->pi_getLL('LLL:EXT:importstudip/locallang.xml:pi_importstudip.all', 'alle', true)."</option>\n";
        $extSoap = new tx_importstudip_soap();
        $lectureTypes = $extSoap->getLectureTypes();
        foreach ($lectureTypes as $name => $value) {
            $result .= "<optgroup label=\"".$name."\">\n";
            for ($i=0 ; $i<sizeof($value) ; $i++) {
                $result .= "<option value=\"".$value[$i]["index"]."\">".utf8_decode($value[$i]["name"])."</option>\n";
            }
            $result .= "</optgroup>\n";
        }
        $result .= "</select>\n";
        $result .= "</td>\n";
        $result .= "</tr>\n";
        $result .= "<tr>\n"; 
        $result .= "<td colspan=\"2\">\n";
        $result .= "<label for=\"tx_is_inst\">".$this->pi_getLL('LLL:EXT:importstudip/locallang.xml:pi_importstudip.search.institute', 'Einrichtung:', true)."</label><br/>\n";
        // Search only courses at the given institute
        $extSoap = new tx_importstudip_soap();
        $result .= $extSoap->getInstitutes(array(), array(), $this->parameters["typo3"]["preselectedInstitute"]);
        $result .= "</tr>";
        $result .= "<tr>";
        $result .= "<td colspan=\"2\" align=\"center\">\n";
        // Handle TYPO3 frameset templates
        if (t3lib_div::_GET("id"))
            $result .= "<input type=\"hidden\" name=\"id\" value=\"".t3lib_div::_GET("id")."\"/>\n";
        if (t3lib_div::_GET("type"))
            $result .= "<input type=\"hidden\" name=\"type\" value=\"".t3lib_div::_GET("type")."\"/>\n";
        $result .= "<input type=\"submit\" name=\"tx_is_do_search\" value=\"".$this->pi_getLL('LLL:EXT:importstudip/locallang.xml:pi_importstudip.search.submit', 'Abschicken', true)."\"/>\n";
        $result .= "<input type=\"submit\" value=\"".$this->pi_getLL('LLL:EXT:importstudip/locallang.xml:pi_importstudip.search.reset', 'Neue Suche', true)."\"/>\n";
        $result .= "</td>\n";
        $result .= "</tr>\n";
        $result .= "</table>\n";
        $result .= "</form>\n";
        return $result;
    }
    
    /**
     * Displays all courses that match the given criteria from the 
     * search form. 
     *
     * @param array $data an array of courses
     * @return HTML code for displaying the search result.
     */
    function showLectureSearchResult($data) {
        $result = "";
        if (sizeof($data) > 0) {
            $result .= "<table border=\"0\" width=\"100%\">\n";
            $result .= "<tr>\n";
            $result .= "<th></th>\n";
            $result .= "<th>Name</th>\n";
            $result .= "<th>Semester</th>\n";
            $result .= "<th>Dozierende</th>\n";
            $result .= "</tr>\n";
            // For each course...
            foreach ($data as $course) {
                $result .= "<tr>\n";
                $result .= "<td>".$course["lno"]."</td>\n";
                // ... create a link to course detail page ...
                $linkData = array(
                    "text" => utf8_decode($course["name"])." (".utf8_decode($course["type"]).")",
                    "parameters" => array (        
                            "module" => "Lecturedetails", 
                            "config_id" => "232ee5ad516ac92bf590f99ac8c2baa8",
                            "range_id" => $course["institute"],
                            "seminar_id" => $course["id"],
                            "target" => $this->parameters["typo3"]["lecturedetailTarget"]["ext"],
                        )
                    );
                $linkData['parameters']['hash'] = $this->makeSignature($linkData['parameters']);
                $result .= "<td>".$this->makeLink($linkData)."</td>\n";
                $result .= "<td>".$course["semester"]."</td>\n";
                $lecturers = "";
                // ... and list all course lecturers...
                for ($i=0 ; $i<sizeof($course["lecturers"]) ; $i++) {
                    // ... with links to person detail page
                    $linkData = array(
                        "text" => utf8_decode($course["lecturers"][$i]["name"]),
                        "parameters" => array (
                                "module" => "Persondetails",
                                "config_id" => "7159c8d0d2d665b0640649fe924a184b",
                                "range_id" => $course["institute"],
                                "username" => $course["lecturers"][$i]["username"],
                                "target" => $this->parameters["typo3"]["lecturedetailTarget"]["ext"],
                                "hash" => crc32("7159c8d0d2d665b0640649fe924a184b"."Persondetails".$course["institute"].$this->parameters["typo3"]["lecturedetailTarget"]["ext"].$course["lecturers"][$i]["username"])
                            )
                        );
                    $lecturers .= $this->makeLink($linkData);
                    if ($i < sizeof($lecture["lecturers"])-1)
                        $lecturers .= ", ";
                }
                $result .= "<td>".$lecturers."</td>\n";
                $result .= "</tr>\n";
            }
            $result .= "</table>\n";
        } else {
            $result .= "<p align=\"center\"><i>Kein Suchergebnis gefunden.</i></p>\n";
        }
        return $result;
    }

    function validReferrer() {
        if (stripos($_SERVER['HTTP_REFERER'], $this->connector->getTypo3SitePath()) !== false) {
            return true;
        } else {
            return false;
        }
    }
    
    function validSignature() {
        // Create hash out of get params
        $hash = crc32(t3lib_div::_GET("aggregation_level").
            t3lib_div::_GET("config_id").
            t3lib_div::_GET("home_faculty_sem_tree_id").
            t3lib_div::_GET("module").
            t3lib_div::_GET("onecol").
            t3lib_div::_GET("range_id").
            t3lib_div::_GET("seminar_id").
            t3lib_div::_GET("seminews").
            t3lib_div::_GET("showid").
            t3lib_div::_GET("source").
            t3lib_div::_GET("target").
            t3lib_div::_GET("username")
            );
        
        if (t3lib_div::_GET("aggregation_level") != "" || 
            t3lib_div::_GET("config_id") != "" ||
            t3lib_div::_GET("home_faculty_sem_tree_id") != "" ||
            t3lib_div::_GET("module") != "" ||
            t3lib_div::_GET("onecol") != "" ||
            t3lib_div::_GET("range_id") != "" ||
            t3lib_div::_GET("seminar_id") != "" ||
            t3lib_div::_GET("seminews") != "" ||
            t3lib_div::_GET("showid") != "" ||
            t3lib_div::_GET("source") != "" ||
            t3lib_div::_GET("target") != "" ||
            t3lib_div::_GET("username") != "") {
            if ($hash == t3lib_div::_GET("hash")) {
                return true;
            } else {
                return false;
            }        
        } else {
            return true;
        }
    }

    function makeSignature($parameters) {
        return crc32($parameters["aggregation_level"].
            $parameters["config_id"].
            $parameters["home_faculty_sem_tree_id"].
            $parameters["module"].
            $parameters["onecol"].
            $parameters["range_id"].
            $parameters["seminar_id"].
            $parameters["seminews"].
            $parameters["showid"].
            $parameters["source"].
            $parameters["target"].
            $parameters["username"]
            );
    }

}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/importstudip/pi1/class.tx_importstudip_pi1.php"])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/importstudip/pi1/class.tx_importstudip_pi1.php"]);
}

?>
