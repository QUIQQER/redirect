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
    'package_quiqqer_redirect_ajax_getRewrittenUrl',
    function ($paramUrl) {
        try {
            $Site = \QUI\Projects\Site\Utils::getSiteByLink($paramUrl);

            return $Site->getUrlRewritten();
        } catch (\QUI\Exception $Exception) {
            return false;
        }
    },
    ['paramUrl'],
    'Permission::checkAdminUser'
);