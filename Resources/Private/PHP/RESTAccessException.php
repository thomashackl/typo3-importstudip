<?php

namespace UniPassau\ImportStudip;

use \TYPO3\Flow\Exception;

class RESTAccessException extends Exception {

    /**
     * @var integer
     */
    protected $statusCode = 400;

}
