<?php

	function checkEndSlash($string) {
		if (substr($string, -1) == "/")
			return $string;
		else
			return $string."/"; 
	}
	
	function checkStartSlash($string) {
		if (substr($string, 0, 1) == "/")
			return $string;
		else
			return "/".$string;
	}
	
?>
