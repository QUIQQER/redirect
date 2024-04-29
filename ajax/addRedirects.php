<?php

use QUI\Package\PackageNotLicensedException;
use QUI\Redirect\Manager;
use QUI\Redirect\Project;
use QUI\System\Log;

/**
 * Adds the given redirects to the system.
 *
 * @param Array<Array> $redirects - Array of arrays with a "source" and "target" properties.
 * @param string $projectName
 * @param string $projectLanguage
 *
 * @return boolean
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_redirect_ajax_addRedirects',
    function ($redirects, $projectName = "", $projectLanguage = "") {
        try {
            $success = true;
            $Project = Project::getFromParameters($projectName, $projectLanguage);

            if (!$Project) {
                return false;
            }

            $redirects = json_decode($redirects, true);

            foreach ($redirects as $redirect) {
                $result = Manager::addRedirect($redirect['source'], $redirect['target'], $Project);

                if (!$result) {
                    $success = false;
                }
            }
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
            // TODO: add individual message informing which redirects couldn't be added
            QUI::getMessagesHandler()->addInformation(
                QUI::getLocale()->get('quiqqer/redirect', 'site.move.error')
            );
        }

        return $success;
    },
    ['redirects', 'projectName', 'projectLanguage'],
    'Permission::checkAdminUser'
);
