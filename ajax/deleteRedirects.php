<?php

/**
 * Deletes multiple redirects from the system
 *
 * @param string[] $sourceUrl - An array of the to be removed redirects' source-URLs
 */

use QUI\Redirect\Manager;
use QUI\Redirect\Permission;

\QUI::$Ajax->registerFunction(
    'package_quiqqer_redirect_ajax_deleteRedirects',
    function ($sourceUrls, $projectName, $projectLanguage) {
        $sourceUrls = json_decode($sourceUrls);

        try {
            $Project = QUI::getProject($projectName, $projectLanguage);
        } catch (\QUI\Exception) {
            return false;
        }

        foreach ($sourceUrls as $sourceUrl) {
            Manager::deleteRedirect($sourceUrl, $Project);
        }

        return true;
    },
    ['sourceUrls', 'projectName', 'projectLanguage'],
    Permission::REDIRECT_DELETE
);
