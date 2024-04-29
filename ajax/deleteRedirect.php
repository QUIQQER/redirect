<?php

/**
 * Deletes a redirect from the system
 *
 * @param string $sourceUrl - The url of the redirect to be removed
 *
 * @return boolean
 */

use QUI\Redirect\Manager;
use QUI\Redirect\Permission;
use QUI\System\Log;

QUI::$Ajax->registerFunction(
    'package_quiqqer_redirect_ajax_deleteRedirect',
    function ($sourceUrl, $projectName, $projectLanguage) {
        try {
            $Project = QUI::getProject($projectName, $projectLanguage);

            return Manager::deleteRedirect($sourceUrl, $Project);
        } catch (QUI\Exception $Exception) {
            Log::writeException($Exception);

            return false;
        }
    },
    ['sourceUrl', 'projectName', 'projectLanguage'],
    Permission::REDIRECT_DELETE
);
