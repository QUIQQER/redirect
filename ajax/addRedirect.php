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
    function ($sourceUrl, $targetUrl) {
        try {
            return \QUI\Redirect\Handler::addRedirect($sourceUrl, $targetUrl);
        } catch (\QUI\Exception $Exception) {
            \QUI\System\Log::writeException($Exception);

            return false;
        }
    },
    ['sourceUrl', 'targetUrl'],
    'Permission::checkAdminUser'
);
