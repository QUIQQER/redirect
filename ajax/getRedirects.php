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
    'package_quiqqer_redirect_ajax_getRedirects',
    function () {
        try {
            return \QUI\Redirect\Handler::getRedirects();
        } catch (\QUI\Exception $Exception) {
            \QUI\System\Log::writeException($Exception);

            return [];
        }
    },
    [],
    'Permission::checkAdminUser'
);
