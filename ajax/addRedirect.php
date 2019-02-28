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
\QUI::$Ajax->registerFunction(
    'package_quiqqer_redirect_ajax_addRedirect',
    function ($sourceUrl, $targetUrl, $projectName = "", $projectLanguage = "") {
        try {
            $Project = \QUI\Redirect\Project::getFromParameters($projectName, $projectLanguage);

            if (!$Project) {
                return false;
            }

            return \QUI\Redirect\Manager::addRedirect($sourceUrl, $targetUrl, $Project);
        } catch (\QUI\Exception $Exception) {
            \QUI\System\Log::writeException($Exception);

            return false;
        }
    },
    ['sourceUrl', 'targetUrl', 'projectName', 'projectLanguage'],
    'Permission::checkAdminUser'
);
