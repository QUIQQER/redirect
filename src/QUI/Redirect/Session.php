<?php

/**
 * @author PCSG (Jan Wennrich)
 */

namespace QUI\Redirect;

use QUI\Exception;
use QUI\Projects\Site;

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
     * The key to be used when storing the URLs to be processed in the session
     *
     * @var string
     */
    const KEY_URLS_TO_PROCESS = "redirect_urls_to_process";


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
     * Stores the URLs to process
     *
     * @param string[] $urls - The URLs to store
     */
    public static function storeUrlsToProcess($urls)
    {
        \QUI::getSession()->set(static::KEY_URLS_TO_PROCESS, json_encode($urls));
    }


    /**
     * Returns a site's children from the current user's session
     *
     * @return array|false
     */
    public static function getUrlsToProcess()
    {
        $rawChildrenData = \QUI::getSession()->get(static::KEY_URLS_TO_PROCESS);

        if (!$rawChildrenData) {
            return [];
        }

        return json_decode($rawChildrenData, true);
    }


    /**
     * Removes a site's children from the current user's session
     */
    public static function removeAllUrlsToProcess()
    {
        \QUI::getSession()->remove(static::KEY_URLS_TO_PROCESS);
    }


    /**
     * Removes an URL from the URLs to process
     *
     * @param string $url - The URL to remove
     *
     * @return string[] - The URLs without the removed URL
     */
    public static function removeUrlToProcess($url)
    {
        $urls = static::getUrlsToProcess();

        $urlKey = array_search($url, $urls);
        if ($urlKey !== false) {
            unset($urls[$urlKey]);

            // use array_values() to reset array indizes
            $urls = array_values($urls);

            static::storeUrlsToProcess($urls);
        }

        return $urls;
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
     * Adds a site's old url to the session
     *
     * @param \QUI\Interfaces\Projects\Site $Site - The site to store the URL for
     */
    public static function addUrlsRecursive(\QUI\Interfaces\Projects\Site $Site)
    {
        $isTotalAddUrlSuccessful = true;
        try {
            static::addUrl($Site->getId(), Url::prepareSourceUrl($Site->getUrlRewritten()));
        } catch (Exception $Exception) {
            $isTotalAddUrlSuccessful = false;
        }

        foreach (\QUI\Redirect\Site::getChildrenRecursive($Site) as $ChildSite) {
            /** @var Site $ChildSite */
            // Use a separate try to continue on error
            try {
                $isChildAddUrlSuccessful = true;
                static::addUrl($ChildSite->getId(), Url::prepareSourceUrl($ChildSite->getUrlRewritten()));
            } catch (Exception $Exception) {
                $isChildAddUrlSuccessful = false;
            }

            if (!$isChildAddUrlSuccessful) {
                $isTotalAddUrlSuccessful = false;
            }
        }
        // Store URLs in session completed
//        \QUI::getMessagesHandler()->addInformation(
//            \QUI::getLocale()->get('quiqqer/redirect', 'site.move.info')
//        );
//
//        if (!$isTotalAddUrlSuccessful) {
//            // Something went wrong storing (at least) one URL
//            \QUI::getMessagesHandler()->addAttention(
//                \QUI::getLocale()->get('quiqqer/redirect', 'site.move.error')
//            );
//        }
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
