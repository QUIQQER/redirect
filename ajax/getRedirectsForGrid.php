<?php

use QUI\Utils\Grid;

/**
 * Get all redirects formatted for the grid.
 * The result can be influenced by providing a page and the amount of entries per page.
 */
\QUI::$Ajax->registerFunction(
    'package_quiqqer_redirect_ajax_getRedirectsForGrid',
    function (
        $projectName,
        $projectLanguage,
        $page = 1,
        $perPage = 25
    ) {
        if (!is_numeric($page) || !is_numeric($perPage)) {
            return [];
        }

        $page    = (int)$page;
        $perPage = (int)$perPage;

        try {
            $Project = QUI::getProject($projectName, $projectLanguage);

            $redirects = \QUI\Redirect\Manager::getRedirects($Project);

            return Grid::getResult($redirects, $page, $perPage);
        } catch (\QUI\Exception $Exception) {
            \QUI\System\Log::writeException($Exception);

            return [];
        }
    },
    ['projectName', 'projectLanguage', 'page', 'perPage'],
    'Permission::checkAdminUser'
);
