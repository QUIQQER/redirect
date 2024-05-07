<?php

/**
 * This file contains package_quiqqer_redirect_ajax_getRedirectsForGrid
 */

use QUI\Redirect\Manager;
use QUI\System\Log;
use QUI\Utils\Grid;

/**
 * Get all redirects formatted for the grid.
 * The result can be influenced by providing a page, the amount of entries per page and a search string.
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_redirect_ajax_getRedirectsForGrid',
    function (
        $projectName,
        $projectLanguage,
        $page = 1,
        $perPage = 25,
        $searchString = ''
    ) {
        if (!is_numeric($page) || !is_numeric($perPage)) {
            return [];
        }

        $page = (int)$page;
        $perPage = (int)$perPage;

        try {
            $Project = QUI::getProject($projectName, $projectLanguage);

            $redirects = Manager::getRedirects($Project);

            // Filter redirects by search string, if it's set
            if (!empty($searchString)) {
                $redirects = array_filter($redirects, function ($redirect) use ($searchString) {
                    return str_contains($redirect['source_url'], $searchString)
                        || str_contains($redirect['target_url'], $searchString);
                });
            }

            return Grid::getResult($redirects, $page, $perPage);
        } catch (QUI\Exception $Exception) {
            Log::writeException($Exception);

            return [];
        }
    },
    ['projectName', 'projectLanguage', 'page', 'perPage', 'searchString'],
    'Permission::checkAdminUser'
);
