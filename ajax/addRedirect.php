<?php

use QUI\Package\PackageNotLicensedException;
use QUI\Redirect\Manager;
use QUI\Redirect\Project;
use QUI\System\Log;

/**
 * Add a redirect to the system
 *
 * @param string $url - URL for the redirect
 * @param string $targetUrl - Target of the redirect
 * @param {boolean} skipChildren - Skip showing a dialog for each child
 *
 * @return boolean
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_redirect_ajax_addRedirect',
    function ($sourceUrl, $targetUrl, $projectName = "", $projectLanguage = "") {
        try {
            $Project = Project::getFromParameters($projectName, $projectLanguage);

            if (!$Project) {
                return false;
            }

            $success = Manager::addRedirect($sourceUrl, $targetUrl, $Project);
        } catch (PackageNotLicensedException $Exception) {
            throw $Exception;
        } catch (\QUI\Exception $Exception) {
            Log::writeException($Exception);

            return false;
        }

        if ($success) {
            QUI::getMessagesHandler()->addInformation(
                QUI::getLocale()->get('quiqqer/redirect', 'site.move.info')
            );
        } else {
            QUI::getMessagesHandler()->addInformation(
                QUI::getLocale()->get('quiqqer/redirect', 'site.move.error')
            );
        }

        return $success;
    },
    ['sourceUrl', 'targetUrl', 'projectName', 'projectLanguage'],
    'Permission::checkAdminUser'
);
