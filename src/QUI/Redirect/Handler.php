<?php

/**
 * @author PCSG (Jan Wennrich)
 */

namespace QUI\Redirect;

use QUI\Exception;
use QUI\Projects\Site;
use QUI\System\Log;

/**
 * Class Handler
 * @package QUI\Redirect
 */
class Handler
{
    /**
     * Attempts to redirect a given URL to a stored location
     *
     * @param $url
     *
     * @return boolean
     */
    public static function attemptRedirect($url)
    {
        $redirectUrl = static::getRedirectForUrl($url);

        if (!$redirectUrl) {
            return false;
        }

        // TODO: check/ask if 302 redirect in development is okay
        $code = 301;
        if (DEVELOPMENT) {
            $code = 302;
        }

        return \QUI::getRewrite()->showErrorHeader($code, $redirectUrl);
    }


    /**
     * Returns the redirect path for an URL from the database.
     * If no entry is found false is returned.
     *
     * @param string $url
     *
     * @return bool|string URL on success, false on missing entry
     */
    public static function getRedirectForUrl($url)
    {
        try {
            $redirectData = \QUI::getDataBase()->fetch([
                'from'  => Database::getTableName(),
                'where' => [
                    Database::COLUMN_SOURCE_URL => $url
                ],
                'limit' => 1
            ]);

            if (!isset($redirectData[0])) {
                return false;
            }

            $targetUrl = $redirectData[0][Database::COLUMN_TARGET_URL];

            return $targetUrl;
        } catch (Exception $Exception) {
            return false;
        }
    }


    /**
     * Adds a redirect for a given site and it's children.
     * If the last parameter is set to false, a redirect will only be added for the given site
     *
     * @param string $url - The url to add a redirect for
     * @param \QUI\Interfaces\Projects\Site $TargetSite - The target of the redirect site's project
     *
     * @return bool
     */
    public static function addRedirect($url, \QUI\Interfaces\Projects\Site $TargetSite)
    {
        try {
            \QUI::getDataBase()->replace(
                Database::getTableName(),
                [
                    Database::COLUMN_SOURCE_URL => $url,
                    Database::COLUMN_TARGET_URL => $TargetSite->getUrlRewritten(),
                ]
            );
        } catch (Exception $Exception) {
            Log::writeException($Exception);

            return false;
        }

        return true;
    }


    /**
     * Add redirects to the system for a page (and it's children).
     * Their URLs have to be stored in the session before.
     *
     * @param \QUI\Interfaces\Projects\Site $Site - The site to add the redirects for
     */
    public static function addRedirectsFromSession(\QUI\Interfaces\Projects\Site $Site)
    {
        // TODO: clean up this mess (e.g. Notify the user in addRedirect())
        $isTotalAddRedirectSuccessful = true;
        try {
            $oldUrl = Session::getOldUrlFromSession($Site->getId());
            static::addRedirect($oldUrl, $Site);

            foreach (\QUI\Redirect\Site::getChildrenRecursive($Site) as $ChildSite) {
                /** @var Site $ChildSite */
                // Use a separate try to continue on error
                try {
                    $childSiteId = $ChildSite->getId();
                    $childOldUrl = Session::getOldUrlFromSession($childSiteId);

                    if (!$childOldUrl) {
                        // Escape this try
                        throw new Exception();
                    }

                    $isChildAddRedirectSuccessful = Handler::addRedirect($childOldUrl, $ChildSite);
                    Session::removeOldUrlFromSession($childSiteId);
                } catch (Exception $Exception) {
                    $isChildAddRedirectSuccessful = false;
                }

                if (!$isChildAddRedirectSuccessful) {
                    $isTotalAddRedirectSuccessful = false;
                }
            }
        } catch (Exception $Exception) {
            $isTotalAddRedirectSuccessful = false;
        }

        // Redirect completed
        \QUI::getMessagesHandler()->addInformation(
            \QUI::getLocale()->get('quiqqer/redirect', 'site.move.info')
        );

        if (!$isTotalAddRedirectSuccessful) {
            // Something went wrong adding a redirect for (at least) one site
            \QUI::getMessagesHandler()->addAttention(
                \QUI::getLocale()->get('quiqqer/redirect', 'site.move.error')
            );
        }
    }
}