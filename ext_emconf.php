<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "importstudip".
 *
 * Auto generated 10-07-2014 11:07
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Daten aus Stud.IP',
    'description' => 'Fügt Daten aus Stud.IP ein, wie Veranstaltungs- oder Personenverzeichnisse.',
    'category' => 'plugin',
    'constraints' => array(
        'depends' => array(
            'typo3' => '8.0.0-8.99.99'
        ),
        'conflicts' => array(
        ),
        'suggests' => array(
        ),
    ),
    'state' => 'stable',
    'clearCacheOnLoad' => 1,
    'author' => 'Thomas Hackl',
    'author_email' => 'thomas.hackl@uni-passau.de',
    'author_company' => 'University of Passau',
    'version' => '3.3.2.0',
    'autoload' => array(
        'classmap' => array(
            'Classes',
            'Classes/Controller',
            'Classes/Utility',
            'Classes/ViewHelpers',
            'Resources/Private/PHP/restclient'
        )
    )
);
