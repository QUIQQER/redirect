<?php

/**
 * @author PCSG (Jan Wennrich)
 */

namespace QUI\Redirect;

use QUI;
use QUI\Exception;

/**
 * Class Project
 * @package redirect\src\QUI\Redirect
 */
class Project
{
    /**
     * Returns the project with the given name and language.
     * If no name is supplied the project is returned via \QUI::getRewrite()->getProject()
     *
     * @param string $projectName
     * @param string $projectLanguage
     *
     * @return bool|QUI\Projects\Project
     */
    public static function getFromParameters(
        string $projectName = "",
        string $projectLanguage = ""
    ): QUI\Projects\Project|bool {
        try {
            if ($projectName) {
                return QUI::getProject($projectName, $projectLanguage);
            }

            return QUI::getProjectManager()->get();
        } catch (Exception) {
            return false;
        }
    }
}
