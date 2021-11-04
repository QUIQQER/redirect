<?php

use QUI\Package\PackageNotLicensedException;

/**
 * Adds the given redirects to the system.
 *
 * @param Array<Array> $redirects - Array of arrays with a "source" and "target" properties.
 * @param string $projectName
 * @param string $projectLanguage
 *
 * @return boolean
 */
\QUI::$Ajax->registerFunction(
    'package_quiqqer_redirect_ajax_addRedirects',
    function ($redirects, $projectName = "", $projectLanguage = "") {
        try {
            $success = true;

            $Project = \QUI\Redirect\Project::getFromParameters($projectName, $projectLanguage);

            if (!$Project) {
                return false;
            }

            $redirects = json_decode($redirects, true);

            foreach ($redirects as $redirect) {
                $result = \QUI\Redirect\Manager::addRedirect($redirect['source'], $redirect['target'], $Project);

                if (!$result) {
                    $success = false;
                }
            }
        } catch (PackageNotLicensedException $Exception) {
            throw $Exception;
        } catch (\QUI\Exception $Exception) {
            \QUI\System\Log::writeException($Exception);

            return false;
        }

        if ($success) {
            \QUI::getMessagesHandler()->addInformation(
                \QUI::getLocale()->get('quiqqer/redirect', 'site.move.info')
            );
        } else {
            // TODO: add individual message informing which redirects couldn't be added
            \QUI::getMessagesHandler()->addInformation(
                \QUI::getLocale()->get('quiqqer/redirect', 'site.move.error')
            );
        }

        return $success;
    },
    ['redirects', 'projectName', 'projectLanguage'],
    'Permission::checkAdminUser'
);
