<?php

    require_once(t3lib_extMgm::extPath('importstudip').'/res/functions.inc.php');

    class tx_importstudip_studipconnector {
        var $studipProtocol       = '';
        var $studipServer         = '';
        var $studipExternPHP      = '';
        var $studipSendfilePHP    = '';
        var $studipSOAPConnector  = '';
        var $typo3Protocol        = '';
        var $typo3SitePath        = '';
        var $timeoutWait          = 1;
        var $useCache             = false;
        var $cacheServer          = '';
        var $directConnectOnError = false;
        var $debug                = false;

        function tx_importstudip_studipconnector() {
            $config = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['importstudip']);
            // Debugging output?
            if ($config['debug']) {
                $this->debug = true;
            }
            // Protocol for Stud.IP server (http or https)
            if ($config['studip_protocol'])
                $this->studipProtocol = $config['studip_protocol'];
            else
                echo '<b>Fehler:</b><br/>Kein Zugriffsprotokoll für den Aufruf '.
                    'von Stud.IP angegeben (http oder https).<br/>';
            // Stud.IP server URL
            if ($config['studip_url'])
                $this->studipServer = $config['studip_url'];
            else
                echo '<b>Fehler:</b><br/>Keine Serveradresse für den Aufruf '.
                    'von Stud.IP angegeben.<br/>';
            // Path to extern.php (normally "/studip/extern.php")
            if ($config['studip_externphp'])
                $this->studipExternPHP = $config['studip_externphp'];
            else
                echo '<b>Fehler:</b><br/>Kein Pfad zur extern.php '. 
                    'angegeben.<br/>';
            // Path to sendfile.php (normally "/studip/sendfile.php")
            if ($config['studip_sendfilephp'])
                $this->studipSendfilePHP = $config['studip_sendfilephp'];
            else
                echo '<b>Fehler:</b><br/>Kein Pfad zur sendfile.php '.
                    'angegeben.<br/>';
            // Path to the Stud.IP-TYPO3-Plugin
            if ($config['studip_pluginaddress'])
                $this->studipSOAPConnector = 
                    $config['studip_protocol'].'://'.
                    $config['studip_url'].
                    $config['studip_pluginaddress'];
            else
                echo '<b>Fehler:</b><br/>Kein Pfad zum '.
                    'Stud.IP-Typo3-Plugin angegeben.<br/>';
            // Get currently used site protocol
            if ($config['typo3_protocol'])
                $this->typo3Protocol = $config['typo3_protocol'];
            else
                $this->typo3Protocol = strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, (strpos($_SERVER['SERVER_PROTOCOL'], '/') ? strpos($_SERVER['SERVER_PROTOCOL'], '/') : 0)));
            // Get path to currently opened page (only path, without page name)
            if ($config['typo3_url']) {
                $this->typo3SitePath = $config['typo3_url'];
                if ($this->debug) {
                    echo 'Using TYPO3 server address from extension configuration: <b>'.$this->typo3SitePath.'</b>.<br/>';
                }
            } else {
                $this->typo3SitePath = $_SERVER['HTTP_HOST'];
                if ($this->debug) {
                    echo 'No TYPO3 server address in extension configuration, automagically generated: <b>'.$this->typo3SitePath.'</b>.<br/>';
                }
            }
            if ($config['typo3_timeoutwait'])
                $this->timeoutWait = intval($config['typo3_timeoutwait']);
            else
                $this->timeoutWait = 5;
            if ($config['cache_use']) {
                $this->useCache = true;
                $this->cacheServer = $config['cache_address'];
                $this->directConnectOnError = true;
            }
        }
        
        function getStudIPProtocol() {
            return $this->studipProtocol;
        }
        
        function getStudIPServer() {
            return $this->studipServer;
        }
        
        function getStudIPExternPHP() {
            return $this->studipExternPHP;
        }
        
        function getStudIPSendfilePHP() {
            return $this->studipSendfilePHP;
        }

        function getStudIPSOAPConnector() {
            return $this->studipSOAPConnector;
        }
        
        function getFullExternPHPLink() {
            return $this->studipServer.
                checkStartSlash($this->studipExternPHP);
        }
        
        function getTypo3Protocol() {
            return $this->typo3Protocol;
        }
        
        function getTypo3SitePath() {
            return $this->typo3SitePath;
        }
        
        function getTimeoutWait() {
            return $this->timeoutWait;
        }
        
        function getCacheServer() {
            return $this->cacheServer;
        }
        
        function useCache() {
            return $this->useCache;
        }
        
        function isSecureProtocol() {
            if ($this->studipProtocol == 'https')
                return true;
            else
                return false;
        }
        
        function getDirectConnectOnError() {
            return $this->directConnectOnError;
        }
    }

?>