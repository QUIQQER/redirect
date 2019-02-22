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

        return \QUI::getRewrite()->showErrorHeader(302, $redirectUrl);
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
                'from'  => DatabaseHelper::getTableName(),
                'where' => [
                    DatabaseHelper::COLUMN_SOURCE_URL => $url
                ],
                'limit' => 1
            ]);

            if (!isset($redirectData[0])) {
                return false;
            }

            $targetUrl = $redirectData[0][DatabaseHelper::COLUMN_TARGET_URL];

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
     * @param Site $TargetSite - The target of the redirect site's project
     *
     * @return bool
     */
    public static function addRedirect($url, Site $TargetSite)
    {
        try {
            \QUI::getDataBase()->replace(
                DatabaseHelper::getTableName(),
                [
                    DatabaseHelper::COLUMN_SOURCE_URL => $url,
                    DatabaseHelper::COLUMN_TARGET_URL => $TargetSite->getUrlRewritten(),
                ]
            );
        } catch (Exception $Exception) {
            Log::writeException($Exception);

            return false;
        }

        return true;
    }
}