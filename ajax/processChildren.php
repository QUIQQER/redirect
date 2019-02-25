<?php

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
    'package_quiqqer_redirect_ajax_processChildren',
    function ($sourceUrl, $targetUrl, $skipChildren) {

        $skipChildren = QUI\Utils\BoolHelper::JSBool($skipChildren);

        $childrenUrls = \QUI\Redirect\Session::getChildrenUrlsFromSession($sourceUrl);

        if ($skipChildren) {
            foreach ($childrenUrls as $childUrl) {
                try {
                    \QUI\Redirect\Handler::addRedirect($childUrl, $targetUrl);
                } catch (\QUI\Exception $Exception) {
                    // TODO: show that something went wrong
                    continue;
                }
            }

            return;
        }

        foreach ($childrenUrls as $childUrl) {
            \QUI\Redirect\Frontend::showAddRedirectDialog($childUrl, false, false);
        }
    },
    ['sourceUrl', 'targetUrl', 'skipChildren'],
    'Permission::checkAdminUser'
);
