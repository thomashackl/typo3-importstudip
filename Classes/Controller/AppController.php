<?php

namespace \UniPassau\ImportStudip;

class AppController extends \TYPO3\CMS\Extbase\MVC\Controller\ActionController {

    public function initializeAction() {
    }

    public function indexAction() {
        $this->view->assign('studipcontent', 'TEST');
    }

    public function handleAjax($params = array(), \TYPO3\CMS\Core\Http\AjaxRequestHandler &$ajaxObj = NULL) {
        $action = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('action');
        if (method_exists('\\UniPassau\\ImportStudip\\AjaxAction', $action)) {
            \UniPassau\ImportStudip\AjaxController::$action();
        } else {
            $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.message.rest_access_error', 'importstudip').' '.$response->response,
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.message.error', 'importstudip'),
                \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
            );
            return $message->render;
        }
    }

}
