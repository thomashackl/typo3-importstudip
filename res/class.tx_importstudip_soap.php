<?php

require_once(t3lib_extMgm::extPath("importstudip")."/res/class.tx_importstudip_studipconnector.php");

class tx_importstudip_soap {

    /**
     * Retrieves the institute hierarchy defined in Stud.IP
     *
     * @param array $parameters content already specified for the select box 
     * that will contain the institute entries (e.g. "please select" entry)
     * @return An array containing all entries from the Stud.IP institute 
     * hierarchy combined with optional entries that were already specified 
     * before.
     */
    function getInstitutes($PA, $fobj, $selectedInstitute='') {
        global $LANG;
        $LANG->includeLLFile('EXT:importstudip/locallang.xml');
        // Get the institute hierarchy
        $data = $this->executeSOAPRequest("getInstitutes", array());
        // Usage in TYPO3 backend.
        if ($PA) {
            $result = '<select name="'.$PA['itemFormElName'].'" size="1" onchange="'.$PA['fieldChangeFunc']['TBE_EDITOR_fieldChanged'].';'.$PA['fieldChangeFunc']['alert'].'">';
            $result .= '<option value="">'.$LANG->getLL('tx_importstudip.pi_flexform.select', 1).'</option>';
            // Create appropriate structure
            $result .= $this->buildInstitutesSelectList($data, $PA['itemFormElValue'], true);
            $result .= '</select>';
        // Usage in frontend course search form.
        } else {
            $result = "<select name=\"tx_is_inst\">\n";
            $result .= "<option value=\"\">".$LANG->getLL('tx_importstudip.search.all', 1)."</option>\n";
            $result .= $this->buildInstitutesSelectList($data, $selectedInstitute, false);
            $result .= '</select>';
        }
        return $result;
    }
    
    /**
     * Retrieves all configurations for external pages at the given institute.
     *
     * @param array $parameters content already specified for the select box 
     * that will contain the configuration entries (e.g. "please select" entry)
     * @return An array containing all external page configurations combined 
     * with optional entries that were already specified before.
     */
    function getExternalConfigurations($parameters) {
        global $LANG;
        // Get current range ID
        $rangeID = $this->extractRangeID($parameters);
        // Get external configurations for selected institute
        $data = $this->executeSOAPRequest("getExternConfigurations", array("rangeID" => $rangeID));
        // Sort configurations by their type.
        $entries = array();
        foreach ($data as $config) {
            $entries[$config['module']][$config['id']] = $config['name'];
        }
        $configurations = array();
        foreach ($entries as $type => $configs) {
            $configurations[] = array(
                $LANG->getLL('tx_importstudip.pi_flexform.config_type.'.$type, 1), 
                '--div--', '');
            foreach ($configs as $id => $name) {
                $configurations[] = array($name, $type.';'.$id);
            }
        }
        $parameters["items"] = array_merge($parameters["items"], $configurations);
        return $parameters;
    }

    /**
     * Retrieve persons registered at the current institute.
     *
     * @param array $parameters content already specified for the select box 
     * that will contain the configuration entries (e.g. "please select" entry)
     * @return array An array containing all persons combined 
     * with optional entries that were already specified before.
     */
    function getPersonsAtInstitute($parameters) {
        $rangeID = $this->extractRangeID($parameters);
        $data = $this->executeSOAPRequest("getPersonsAtInstitute", array("rangeID" => $rangeID));
        $persons = $this->buildPersonsSelectList($data);
        $parameters["items"] = array_merge($parameters["items"], $persons);
        return $parameters;
    }

    /**
     * Gets all courses that have the given institute as home or participating institute.
     * @param array $parameters content already specified for the select box 
     * that will contain the configuration entries (e.g. "please select" entry)
     * @return array An array containing all courses combined 
     * with optional entries that were already specified before.
     */
    function getLecturesAtInstitute($parameters) {
        $rangeID = $this->extractRangeID($parameters);
        $data = $this->executeSOAPRequest("getLecturesAtInstitute", array("rangeID" => $rangeID));
        $lectures = $this->buildLecturesSelectList($data);
        $parameters["items"] = array_merge($parameters["items"], $lectures);
        return $parameters;
    }
    
    function getLectureSearchResult($searchtxt, $mode, $semester, $type, $institute) {
        $data = $this->executeSOAPRequest("searchLectures", 
            array(
                "searchtxt" => utf8_encode($searchtxt), 
                "mode" => $mode, 
                "semester" => $semester, 
                "type" => $type, 
                "institute" => $institute));
        return $data;
    }
    
    function getLectureTypes($parameters=NULL) {
        $data = $this->executeSOAPRequest("getLectureTypes", array());
        if ($parameters) {
            $items = array();
            foreach ($data as $category => $entries) {
                $items[] = array(
                    0 => $category,
                    1 => '--div--'
                );
                foreach ($entries as $entry) {
                    $items[] = array(
                        0 => $entry['name'],
                        1 => $entry['index']
                    );
                }
            }
            $data = $parameters;
            $data['items'] = array_merge($parameters['items'], $items);
        }
        return $data;
    }
    
    function getSemesters() {
        $data = $this->executeSOAPRequest("getSemesters", array());
        return $data;
    }
        
    function getStudycourses($parameters) {
        $data = $this->executeSOAPRequest("getStudycourses", array());
        $subjectAreas = $this->buildSubjectAreasSelectList($data);
        $parameters["items"] = array_merge($parameters["items"], $subjectAreas);
        return $data;
    }
    
    function getSubjectAreasForInstitute($parameters) {
        $rangeID = $this->extractRangeID($parameters);
        $mainSubjectArea = $this->extractMainSubjectArea($parameters);
        $data = $this->executeSOAPRequest("getSubjectAreasForInstitute", array("rangeID" => null, "mainSubjectArea" => $mainSubjectArea));
        $subjectAreas = $this->buildSubjectAreasSelectList($data);
        $parameters["items"] = array_merge($parameters["items"], $subjectAreas);
                                
        return $data;
    }
    
    function getMainSubjectAreasForInstitute($parameters) {
        $rangeID = $this->extractRangeID($parameters);
        $data = $this->executeSOAPRequest("getMainSubjectAreasForInstitute", array("rangeID" => $rangeID));
        $subjectAreas = $this->buildSubjectAreasSelectList($data);
        $parameters["items"] = array_merge($parameters["items"], $subjectAreas);
        return $data;
    }

    function getGroupsForInstitute($parameters) {
        $rangeID = $this->extractRangeID($parameters);
        $data = $this->executeSOAPRequest("getGroupsForInstitute", array("rangeID" => $rangeID));
        $groups = $this->buildGroupsSelectList($data);
        $parameters["items"] = array_merge($parameters["items"], $groups);
        return $data;
    }

    function getSemTreeID($parameters) {
        $rangeID = $this->extractRangeID($parameters);
        $data = $this->executeSOAPRequest("getSemTreeID", array("rangeID" => $rangeID));
        return $data[0];
    }

    /**
     * Executes a SOAP request by calling the given method with given parameters.
     *
     * @param string $methodName method to call on server
     * @param array $parameters optional parameters for the method to call
     * @return The server response wich should be an array.
     */
    function executeSOAPRequest($methodName, $parameters) {
        $data = array();
        try {
            // Create connector with server address etc.
            $connector = new tx_importstudip_studipconnector();
            // Open new SOAP connection to server
            $client = new soapclient(null, 
                array("location" => $connector->studipSOAPConnector, 
                    "uri" => "urn:studip_typo3", "connection_timeout" => 10));
            // Call the given method
            $response = $client->__soapCall($methodName, $parameters);
            // Response is a serialized array, so unserialize it
            $data = unserialize(utf8_decode($response));
            if (!is_array($data)) {
                $data = array();
            }
        } catch (Exception $e) {
            echo 'Fehler beim Herstellen der Verbindung zum Stud.IP-Server!<br/>';
            t3lib_utility_Debug::debug($e->getMessage(), 'Error');
        }
        return $data;
    }
    
    /**
     * Extracts the currently selected institute ID from the flexform 
     * configuration. The whole TYPO3 backend configuration is stored 
     * in an array, the flexform config we need is in XML form.
     *
     * @param array $data current TYPO3 backend config
     * @return string The ID of the currently selected institute.
     */
    function extractRangeID($data) {
        // Get Flexform data
        $flexformData = $data["row"]["pi_flexform"];
        try {
            $xml = new SimpleXMLElement($flexformData);
            // Navigate to the range ID value
            $rangeXML = $xml->xpath("//field[@index='institutes']/value");
            $rangeValue = $rangeXML[0];
            // Workaround for getting the value itself instead of an XML element
            ob_start();
            echo $rangeValue;
            $rangeID = ob_get_contents();
            ob_end_clean();
        } catch (Exception $e) {
            t3lib_utility_Debug::debug($e->getMessage(), 'Error');
            t3lib_utility_Debug::debug($e->getFile().', Line '.$e->getLine(), 'Location');
        }
        return $rangeID;
    }
    
    /**
     * Extracts the currently selected main subject area from the flexform 
     * configuration. The whole TYPO3 backend configuration is stored 
     * in an array, the flexform config we need is in XML form.
     *
     * @param array $data current TYPO3 backend config
     * @return string The ID of the currently selected main subject area.
     */
    function extractMainSubjectArea($data) {
        // Get Flexform data
        $flexformData = $data["row"]["pi_flexform"];
        try {
            $xml = new SimpleXMLElement($flexformData);
            // Navigate to the range ID value
            $rangeXML = $xml->xpath("//field[@index='mainSubjectAreas']/value");
            $rangeValue = $rangeXML[0];
            // Workaround for getting the value itself instead of an XML element
            ob_start();
            echo $rangeValue;
            $rangeID = ob_get_contents();
            ob_end_clean();
        } catch (Exception $e) {
            t3lib_utility_Debug::debug($e->getMessage(), 'Error');
            t3lib_utility_Debug::debug($e->getFile().', Line '.$e->getLine(), 'Location');
        }
        return $rangeID;
    }
    
    function buildInstitutesSelectList($data, $selectedInstitute, $utf=true, $indent=0) {
        $result = '';
        $optgroup_open = false;
        foreach ($data as $institute) {
            if (strlen($institute["name"]) > 100) {
                $name = substr($institute["name"], 0, 96)." [...]";
            } else {
                $name = $institute["name"];
            }
            if (!$utf) {
                $name = utf8_decode($name);
            }
            if ($institute['id']) {
                $result .= '<option value="'.$institute["id"].'" style="padding: 0px; padding-left: '.$indent.'px; margin: 0px;"';
                if ($institute['id'] == $selectedInstitute) {
                    $result .= ' selected="selected"';
                }
                $result .= '>'.$name.'</option>'."\n";
            } else {
                $result .= '<optgroup style="font-style: italic; font-weight: bold; padding-left: '.$indent.'px;" label="'.$name.'">'."\n";
                $optgroup_open = true;
            }
            if ($institute["subInstitutes"]) {
                $result .= $this->buildInstitutesSelectList($institute["subInstitutes"], $selectedInstitute, $utf, $indent+5);
            }
            if ($optgroup_open) {
                $result .= '</optgroup>'."\n";
            }
        }
        return $result;
    }
    
    function buildConfigurationsSelectList($data) {
        $result = array();
        foreach ($data as $configuration) {
            $result[] = array(0 => $configuration["name"],
                              1 => $configuration["module"].";".$configuration["id"]);
        }
        return $result;
    }

    function buildPersonsSelectList($data) {
        $result = array();
        foreach ($data as $person) {
            $result[] = array(0 => $person["name"],
                              1 => $person["username"]);
        }
        return $result;
    }

    function buildLecturesSelectList($data) {
        $result = array();
        foreach ($data as $lecture) {
            $result[] = array(0 => $lecture["name"]." (".
                                    $lecture["type"].
                                    ", ".$lecture["semester"].")",
                              1 => $lecture["id"]);
        }
        return $result;
    }
    
    function buildSubjectAreasSelectList($data, $level=0) {
        $result = array();
        foreach ($data as $subjectArea) {
            $indent = '';
            for ($i=0 ; $i<=$level*2 ; $i++) {
                $indent .= '&nbsp;';
            }
            if (strlen($subjectArea['name'])+$level*2 > 100) {
                $name = $indent.substr($subjectArea['name'], 0, 96-$level*2)."[...]";
            } else {
                $name = $indent.$subjectArea['name'];
            }
            $result[] = array(0 => $name,
                              1 => $subjectArea['id']);
            if ($subjectArea['children']) {
                $result = array_merge($result, $this->buildSubjectAreasSelectList($subjectArea['children'], $level+1)); 
            }
        }
        return $result;
    }

    function buildGroupsSelectList($data) {
        $result = array();
        foreach($data as $group) {
            $result[] = array(0 => $group["name"],
                              1 => $group['id']); 
        }
        return $result;
    }
}

?>