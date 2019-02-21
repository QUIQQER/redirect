<?php

/**
 * @author PCSG (Jan Wennrich)
 */

namespace QUI\Redirect;

use QUI\Exception;
use QUI\Projects\Site;
use QUI\System\Log;

/**
 * Class with various helper-methods for the redirect module
 *
 * All this session stuff is needed in order to skip adding redirects for children.
 * This way it's not necessary to overwrite system's site delete methods
 *
 * @package redirect\src\QUI\Redirect
 */
class Helper
{
    /**
     * Prefix for the session key which stores the children of a site on delete.
     * Should be appended with a md5 hash of the sites URL
     */
    const SESSION_KEY_CHILDREN_PREFIX = "redirect_delete_";

    /**
     * Returns a key to be used when storing a sites children URLs in the session
     *
     * @param string $url
     *
     * @return string
     */
    public static function getChildrenUrlsSessionKey($url)
    {
        return self::SESSION_KEY_CHILDREN_PREFIX . md5($url);
    }


    /**
     * Stores the children of a site in the current user's session.
     *
     * @param Site $Site
     *
     * @return bool
     */
    public static function storeChildrenInSession(Site $Site)
    {
        $successfull  = false;
        $childrenUrls = [];
        try {
            $Children = $Site->getChildren(['active' => '0&1', true]);
        } catch (Exception $Exception) {
            Log::writeException($Exception);

            return false;
        }

        foreach ($Children as $Child) {
            try {
                /** @var Site $Child */
                $childrenUrls[] = $Child->getUrlRewritten();
            } catch (Exception $Exception) {
                $successfull = false;
                continue;
            }
        }

        try {
            \QUI::getSession()->set(
                static::getChildrenUrlsSessionKey($Site->getUrlRewritten()),
                json_encode($childrenUrls)
            );
        } catch (Exception $Exception) {
            Log::writeException($Exception);

            return false;
        }

        return $successfull;
    }


    /**
     * Returns a site's children from the current user's session
     *
     * @param $url
     *
     * @return array|false
     */
    public static function getChildrenFromSession($url)
    {
        $rawChildrenData = \QUI::getSession()->get(self::getChildrenUrlsSessionKey($url));

        return json_decode($rawChildrenData, true);
    }


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


    /**
     * Removes a site's children from the current user's session
     *
     * @param $url
     *
     */
    public static function removeChildrenFromSession($url)
    {
        \QUI::getSession()->remove(self::getChildrenUrlsSessionKey($url));
    }
}