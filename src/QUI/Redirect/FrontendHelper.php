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
class FrontendHelper
{
    /**
     * Triggers the (frontend) JavaScript on site delete callback
     *
     * @param string $url - The url of the deleted site
     * @param $showSkip - Should the skip checkbox be shown
     */
    public static function triggerJavaScriptDeleteCallback($url, $showSkip)
    {
        \QUI::getAjax()->triggerGlobalJavaScriptCallback(
            'redirectOnSiteDelete',
            [
                'url'      => $url,
                'showSkip' => $showSkip
            ]
        );
    }
}