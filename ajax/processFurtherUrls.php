<?php

use \QUI\Redirect\Session;
use \QUI\Redirect\TemporaryStorage;

/**
 * Processes the children of a site.
 * Adding redirects for each one or showing new dialogs to add custom redirects
 *
 * @param string $sourceUrl - URL for the redirect
 * @param string $targetUrl - Project of the redirect's target
 * @param {boolean} skipChildren - Skip showing a dialog for each child
 *
 * @return boolean
 */
\QUI::$Ajax->registerFunction(
    'package_quiqqer_redirect_ajax_processFurtherUrls',
    function ($sourceUrl, $targetUrl, $skipChildren, $projectName = "", $projectLanguage = "") {

        $skipChildren = QUI\Utils\BoolHelper::JSBool($skipChildren);

        $urlsToProcess = TemporaryStorage::getUrlsToProcess();

        if ($skipChildren) {
            if (!$targetUrl || !$projectName) {
                TemporaryStorage::removeAllUrlsToProcess();

                return true;
            }

            $Project = \QUI\Redirect\Project::getFromParameters($projectName, $projectLanguage);

            if (!$Project) {
                return false;
            }

            foreach ($urlsToProcess as $url) {
                try {
                    \QUI\Redirect\Manager::addRedirect($url, $targetUrl, $Project);
                } catch (\QUI\Exception $Exception) {
                    // TODO: show that something went wrong
                    continue;
                }
            }

            TemporaryStorage::removeAllUrlsToProcess();

            return true;
        }

        $urlsToProcess = TemporaryStorage::removeUrlToProcess($sourceUrl);

        // More URLs to process?
        if (count($urlsToProcess) > 0) {
            \QUI\Redirect\Frontend::showAddRedirectDialog(
                $urlsToProcess[0],
                $targetUrl,
                true,
                $projectName,
                $projectLanguage
            );
        }

        return true;
    },
    ['sourceUrl', 'targetUrl', 'skipChildren', 'projectName', 'projectLanguage'],
    'Permission::checkAdminUser'
);
