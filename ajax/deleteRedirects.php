<?php

/**
 * Deletes multiple redirects from the system
 *
 * @param string[] $sourceUrl - An array of the to be removed redirects' source-URLs
 */
\QUI::$Ajax->registerFunction(
    'package_quiqqer_redirect_ajax_deleteRedirects',
    function ($sourceUrls) {
        $sourceUrls = json_decode($sourceUrls);

        foreach ($sourceUrls as $sourceUrl) {
            \QUI\Redirect\Manager::deleteRedirect($sourceUrl);
        }
    },
    ['sourceUrls'],
    'Permission::checkAdminUser'
);
