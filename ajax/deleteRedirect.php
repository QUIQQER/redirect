<?php

/**
 * Deletes a redirect from the system
 *
 * @param string $sourceUrl - The url of the redirect to be removed
 *
 * @return boolean
 */
\QUI::$Ajax->registerFunction(
    'package_quiqqer_redirect_ajax_deleteRedirect',
    function ($sourceUrl) {
        try {
            return \QUI\Redirect\Manager::deleteRedirect($sourceUrl);
        } catch (\QUI\Exception $Exception) {
            \QUI\System\Log::writeException($Exception);

            return false;
        }
    },
    ['sourceUrl'],
    'Permission::checkAdminUser'
);
