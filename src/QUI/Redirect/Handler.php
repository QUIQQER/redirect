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
     * Returns the name of the redirect table
     *
     * @return string
     */
    public static function getTableName()
    {
        return \QUI::getDBTableName('redirects');
    }


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
                'from'  => static::getTableName(),
                'where' => [
                    'url' => $url
                ],
                'limit' => 1
            ]);

            if (!isset($redirectData[0])) {
                return false;
            }

            $redirectData = $redirectData[0];

            $Project = \QUI\Projects\Manager::getProject($redirectData['project'], $redirectData['language']);
            $Site    = new Site($Project, $redirectData['site_id']);

            return $Site->getUrlRewrittenWithHost();
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
        $Project = $TargetSite->getProject();

        try {
            return static::insertRedirectDataIntoDatabase(
                $url,
                $Project->getName(),
                $Project->getLang(),
                $TargetSite->getId()
            );
        } catch (Exception $Exception) {
            return false;
        }
    }


    /**
     * Inserts the given data into the database.
     * Returns if the operation was successful.
     *
     * @param string $url
     * @param string $projectName
     * @param string $language
     * @param int $siteId
     *
     * @return boolean
     */
    protected static function insertRedirectDataIntoDatabase($url, $projectName, $language, $siteId)
    {
        try {
            \QUI::getDataBase()->insert(
                static::getTableName(),
                [
                    'url'      => $url,
                    'project'  => $projectName,
                    'language' => $language,
                    'site_id'  => $siteId,
                ]
            );
        } catch (\QUI\Database\Exception $Exception) {
            // TODO: some sort of error handling (maybe?)
            Log::writeException($Exception);

            return false;
        }

        return true;
    }
}