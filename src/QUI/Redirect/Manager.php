<?php

/**
 * @author PCSG (Jan Wennrich)
 */

namespace QUI\Redirect;

use QUI;
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
     * Number of redirects that can created without requiring a license
     *
     * @var int
     */
    const FREE_REDIRECTS = 50;

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
            $redirectUrl .= '?'.$query;
        }

        if (!$Project->hasVHost()) {
            $redirectUrl = '/'.$Project->getLang().$redirectUrl;
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
     *
     * @throws QUI\Package\PackageNotLicensedException
     */
    public static function addRedirect($sourceUrl, $targetUrl, Project $Project)
    {
        // Check license requirements
        self::checkLicense();

        try {
            $sourceUrl = Url::prepareSourceUrl($sourceUrl);

            // Internal URL?
            if (Url::isInternal($targetUrl)) {
                // Get the pretty-printed URL
                $TargetSite = Site\Utils::getSiteByLink($targetUrl);

                // URL of another project?
                if ($TargetSite->getProject() === $Project) {
                    $targetUrl = $TargetSite->getUrlRewritten();
                    $targetUrl = Url::prepareInternalTargetUrl($targetUrl);
                } else {
                    $targetUrl = $TargetSite->getUrlRewrittenWithHost();
                }
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
     * Check license requirements for quiqqer/redirect usage.
     *
     * @return void
     * @throws QUI\Package\PackageNotLicensedException
     */
    protected static function checkLicense()
    {
        try {
            if (self::getRedirectCount() < self::FREE_REDIRECTS) {
                return;
            }
        } catch (\Exception $Exception) {
            QUI\System\Log::writeException($Exception);
            return;
        }

        if (QUI::getPackageManager()->hasLicense('quiqqer/redirect')) {
            return;
        }

        $urls = QUI::getPackageManager()->getPackageStoreUrls('quiqqer/redirect');
        $lang = QUI::getLocale()->getCurrent();
        $url  = null;

        if (!empty($urls[$lang])) {
            $url = $urls[$lang];
        }

        throw new QUI\Package\PackageNotLicensedException(
            'quiqqer/redirect',
            [
                'quiqqer/redirect',
                'PackageNotLicensedException.message',
                [
                    'freeRedirects' => self::FREE_REDIRECTS
                ]
            ],
            $url
        );
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
            $oldUrl  = Url::prepareSourceUrl(Session::getOldUrlFromSession($Site->getId()));
            $Project = $Site->getProject();

            static::addRedirect($oldUrl, Url::prepareInternalTargetUrl($Site->getUrlRewritten()), $Project);

            foreach (\QUI\Redirect\Site::getChildrenRecursive($Site) as $ChildSite) {
                /** @var Site $ChildSite */
                // Use a separate try to continue on error
                try {
                    $childSiteId = $ChildSite->getId();
                    $childOldUrl = Url::prepareSourceUrl(Session::getOldUrlFromSession($childSiteId));

                    if (!$childOldUrl) {
                        // Escape this try
                        throw new Exception();
                    }

                    $isChildAddRedirectSuccessful = static::addRedirect(
                        $childOldUrl,
                        Url::prepareInternalTargetUrl($ChildSite->getUrlRewritten()),
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
                'select' => Database::COLUMN_SOURCE_URL.','.Database::COLUMN_TARGET_URL,
                'from'   => Database::getTableName($Project)
            ]);
        } catch (\QUI\Database\Exception $Exception) {
            Log::writeException($Exception);

            return [];
        }
    }

    /**
     * Get number of redirects in the system
     *
     * @param Project $Project (optional) - Get number of redirects of a specific project [default: cross-project redirect count]
     * @return int
     *
     * @throws QUI\Exception
     */
    public static function getRedirectCount(Project $Project = null)
    {
        if (!empty($Project)) {
            $projects = [$Project];
        } else {
            $projects = QUI::getProjectManager()->getProjects(true);
        }

        $redirectCount = 0;

        /** @var Project $Project */
        foreach ($projects as $Project) {
            $result = \QUI::getDataBase()->fetch([
                'count' => 1,
                'from'  => Database::getTableName($Project)
            ]);

            $redirectCount += (int)\current(\current($result));
        }

        return $redirectCount;
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
