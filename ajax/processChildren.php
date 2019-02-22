<?php

/**
 * Processes the children of a site.
 * Adding redirects for each one or showing new dialogs to add custom redirects
 *
 * @param string $url - URL for the redirect
 * @param string $targetProjectName - Project of the redirect's target
 * @param string $targetProjectLanguage - Project language of the redirect's target
 * @param int $targetSiteId - Site ID of the redirect's target
 * @param {boolean} skipChildren - Skip showing a dialog for each child
 *
 * @return boolean
 */
\QUI::$Ajax->registerFunction(
    'package_quiqqer_redirect_ajax_processChildren',
    function ($sourceUrl, $targetProjectName, $targetProjectLanguage, $targetSiteId, $skipChildren) {

        $skipChildren = QUI\Utils\BoolHelper::JSBool($skipChildren);

        $Project = QUI\Projects\Manager::getProject($targetProjectName, $targetProjectLanguage);
        $Site    = new \QUI\Projects\Site($Project, $targetSiteId);

        $children = \QUI\Redirect\Session::getChildrenUrlsFromSession($sourceUrl);

        if ($skipChildren) {
            foreach ($children as $childUrl) {
                \QUI\Redirect\Handler::addRedirect($childUrl, $Site);
            }

            return;
        }

        foreach ($children as $childUrl) {
            \QUI\Redirect\Frontend::showAddRedirectDialog($childUrl, false);
        }
    },
    ['sourceUrl', 'targetProjectName', 'targetProjectLanguage', 'targetSiteId', 'skipChildren'],
    'Permission::checkAdminUser'
);
