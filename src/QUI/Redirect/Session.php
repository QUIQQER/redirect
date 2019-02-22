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
class Session
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
    protected static function getChildrenUrlsSessionKey($url)
    {
        return static::SESSION_KEY_CHILDREN_PREFIX . md5($url);
    }


    /**
     * Returns a key to be used when storing a site's old URL in the session.
     *
     * @param int $id - The site's id
     *
     * @return string
     */
    protected static function getOldUrlSessionKey($id)
    {
        return "redirect_delete_$id";
    }


    /**
     * Stores the children of a site in the current user's session.
     *
     * @param \QUI\Interfaces\Projects\Site $Site
     *
     * @return bool
     */
    public static function storeChildrenUrlsInSession(\QUI\Interfaces\Projects\Site $Site)
    {
        $successfull  = false;
        $childrenUrls = [];

        $children = \QUI\Redirect\Site::getChildrenRecursive($Site, ['active' => '0&1']);

        foreach ($children as $Child) {
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
    public static function getChildrenUrlsFromSession($url)
    {
        $rawChildrenData = \QUI::getSession()->get(self::getChildrenUrlsSessionKey($url));

        return json_decode($rawChildrenData, true);
    }


    /**
     * Removes a site's children from the current user's session
     *
     * @param $url
     *
     */
    public static function removeChildrenUrlsFromSession($url)
    {
        \QUI::getSession()->remove(self::getChildrenUrlsSessionKey($url));
    }


    /**
     * Adds a site's old url to the session
     *
     * @param int $pageId - The site's id
     * @param string $url - The site's old URL
     * @param boolean $recursive - Also add URLs for children
     */
    public static function addUrl($pageId, $url, $recursive = false)
    {
        \QUI::getSession()->set(
            static::getOldUrlSessionKey($pageId),
            $url
        );
    }


    /**
     * Returns a site's old URL from the current user's session
     *
     * @param int $pageId - The site's ID
     *
     * @return string|false
     */
    public static function getOldUrlFromSession($pageId)
    {
        return \QUI::getSession()->get(self::getOldUrlSessionKey($pageId));
    }


    /**
     * Removes a site's old URL from the current user's session
     *
     * @param int $pageId
     *
     */
    public static function removeOldUrlFromSession($pageId)
    {
        \QUI::getSession()->remove(self::getOldUrlSessionKey($pageId));
    }
}