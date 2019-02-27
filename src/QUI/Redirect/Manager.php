<?php

/**
 * @author PCSG (Jan Wennrich)
 */

namespace QUI\Redirect;

use QUI\Exception;
use QUI\Projects\Project;
use QUI\Projects\Site;
use QUI\System\Log;

/**
 * Class Manager
 * @package QUI\Redirect
 */
class Manager
{
    /**
     * Attempts to redirect a given URL from the given project to a stored location
     *
     * @param $url
     * @param Project $Project
     *
     * @return boolean
     */
    public static function attemptRedirect($url, Project $Project)
    {
        $redirectUrl = static::getRedirectForUrl($url, $Project);

        if (!$redirectUrl) {
            return false;
        }

        // Append the query string
        $requestUri = \QUI::getRequest()->getRequestUri();
        $query      = Url::getQueryString($requestUri);

        if (!empty($query)) {
            $redirectUrl .= '?' . $query;
        }

        if (!$Project->hasVHost()) {
            $redirectUrl = '/' . $Project->getLang() . $redirectUrl;
        }

        // TODO: check/ask if 302 redirect in development is okay
        $code = 301;
        if (DEVELOPMENT) {
            $code = 302;
        }

        return \QUI::getRewrite()->showErrorHeader($code, $redirectUrl);
    }


    /**
     * Returns the redirect path for an URL and project from the database.
     * If no entry is found false is returned.
     *
     * @param string $url
     * @param Project $Project
     *
     * @return bool|string URL on success, false on missing entry
     */
    public static function getRedirectForUrl($url, Project $Project)
    {
        try {
            $redirectData = \QUI::getDataBase()->fetch([
                'from'  => Database::getTableName($Project),
                'where' => [
                    Database::COLUMN_ID => md5($url)
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
     * Adds a redirect with the given source- and target-URL to the given project
     *
     * @param string $sourceUrl - The url to add a redirect for
     * @param string $targetUrl - The target of the redirect site's project
     * @param Project $Project - The project
     *
     * @return bool
     */
    public static function addRedirect($sourceUrl, $targetUrl, Project $Project)
    {
        try {
            $sourceUrl = Url::prepareSourceUrl($sourceUrl);

            // Internal URL?
            if (Url::isInternal($targetUrl)) {
                // Get the pretty-printed URL
                $targetUrl = Site\Utils::getSiteByLink($targetUrl)->getUrlRewritten();
                $targetUrl = Url::prepareInternalTargetUrl($targetUrl);
            }

            \QUI::getDataBase()->replace(
                Database::getTableName($Project),
                [
                    Database::COLUMN_ID         => md5($sourceUrl),
                    Database::COLUMN_SOURCE_URL => $sourceUrl,
                    Database::COLUMN_TARGET_URL => $targetUrl,
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
            $oldUrl  = Session::getOldUrlFromSession($Site->getId());
            $Project = $Site->getProject();

            static::addRedirect($oldUrl, $Site->getUrlRewritten(), $Project);

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

                    $isChildAddRedirectSuccessful = static::addRedirect(
                        $childOldUrl,
                        $ChildSite->getUrlRewritten(),
                        $Project
                    );

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


    /**
     * Returns an array of all redirects for a given project.
     * The array keys are 'source_url' and 'target_url'
     *
     * @param Project $Project
     *
     * @return array
     */
    public static function getRedirects(Project $Project)
    {
        try {
            return \QUI::getDataBase()->fetch([
                'select' => Database::COLUMN_SOURCE_URL . ',' . Database::COLUMN_TARGET_URL,
                'from'   => Database::getTableName($Project)
            ]);
        } catch (\QUI\Database\Exception $Exception) {
            Log::writeException($Exception);

            return [];
        }
    }


    /**
     * Removes the redirect with the given source URL from the given project
     *
     * @param string $sourceUrl
     * @param Project $Project
     *
     * @return boolean - Removal successful?
     */
    public static function deleteRedirect($sourceUrl, Project $Project)
    {
        try {
            \QUI::getDataBase()->delete(
                Database::getTableName($Project),
                [
                    Database::COLUMN_ID => md5($sourceUrl)
                ]
            );
        } catch (\QUI\Database\Exception $Exception) {
            Log::writeException($Exception);

            return false;
        }
    }
}