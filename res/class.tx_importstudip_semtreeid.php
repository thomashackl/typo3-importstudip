<?php

require_once('class.tx_importstudip_soap.php');

class tx_importstudip_semtreeid {
	
	function getSemTreeID($PA, $fobj) {
		$isSOAP = new tx_importstudip_soap();
		$parameters = array('row' => array('pi_flexform' => $PA['row']['pi_flexform']));
		$semTreeID = $isSOAP->getSemTreeID($parameters);
		$result = '<input type="hidden" name="'.$PA['itemFormElName'].'" value="'.$semTreeID.'"/>';
		return $result;
	}

}

?>