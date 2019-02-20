<?php

/**
 * Add a redirect to the system
 *
 * @param string $url - URL for the redirect
 * @param string $targetProjectName - Project of the redirect's target
 * @param string $targetProjectLanguage - Project language of the redirect's target
 * @param int $targetSiteId - Site ID of the redirect's target
 *
 * @return boolean
 */
\QUI::$Ajax->registerFunction(
    'package_quiqqer_redirect_ajax_addRedirect',
    function ($sourceUrl, $targetProjectName, $targetProjectLanguage, $targetSiteId) {
        try {
            $Project = QUI\Projects\Manager::getProject($targetProjectName, $targetProjectLanguage);
            $Site    = new \QUI\Projects\Site($Project, $targetSiteId);

            return \QUI\Redirect\Handler::addRedirect($sourceUrl, $Site);
        } catch (\QUI\Exception $Exception) {
            \QUI\System\Log::writeException($Exception);
            return false;
        }
    },
    ['sourceUrl', 'targetProjectName', 'targetProjectLanguage', 'targetSiteId'],
    'Permission::checkAdminUser'
);
