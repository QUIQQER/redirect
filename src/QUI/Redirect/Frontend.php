<?php

/**
 * @author PCSG (Jan Wennrich)
 */

namespace QUI\Redirect;


/**
 * Class with various helper-methods for the redirect module
 *
 * All this session stuff is needed in order to skip adding redirects for children.
 * This way it's not necessary to overwrite system's site delete methods
 *
 * @package redirect\src\QUI\Redirect
 */
class Frontend
{
    /**
     * Triggers the (frontend) JavaScript on site delete callback
     *
     * @param string $sourceUrl - The url of the deleted site
     * @param string $targetUrl - The redirect's target URL
     * @param boolean $showSkip - Should the skip checkbox be shown
     * @param string $projectName - The project's name
     * @param string $projectLanguage - The project's language
     */
    public static function showAddRedirectDialog(
        $sourceUrl,
        $targetUrl = "",
        $showSkip = false,
        $projectName = "",
        $projectLanguage = ""
    ) {
        \QUI::getAjax()->triggerGlobalJavaScriptCallback(
            'redirectShowAddRedirectDialog',
            [
                'sourceUrl'       => $sourceUrl,
                'targetUrl'       => $targetUrl,
                'showSkip'        => $showSkip,
                'projectName'     => $projectName,
                'projectLanguage' => $projectLanguage
            ]
        );
    }
}