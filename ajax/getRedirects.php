<?php

/**
 * Add a redirect to the system
 *
 * @param string $url - URL for the redirect
 * @param string $targetUrl - Target of the redirect
 * @param {boolean} skipChildren - Skip showing a dialog for each child
 *
 * @return boolean
 */

use QUI\Redirect\Manager;
use QUI\System\Log;

\QUI::$Ajax->registerFunction(
    'package_quiqqer_redirect_ajax_getRedirects',
    function ($projectName, $projectLanguage) {
        try {
            $Project = QUI::getProject($projectName, $projectLanguage);

            return Manager::getRedirects($Project);
        } catch (\QUI\Exception $Exception) {
            Log::writeException($Exception);

            return [];
        }
    },
    ['projectName', 'projectLanguage'],
    'Permission::checkAdminUser'
);
