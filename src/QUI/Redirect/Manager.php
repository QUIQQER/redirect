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
use QUI\Package\PackageNotLicensedException;

use function current;

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
    public static function attemptRedirect($url, Project $Project): bool
    {
        $redirectUrl = static::getRedirectForUrl($url, $Project);

        if (!$redirectUrl) {
            return false;
        }

        // Append the query string
        $requestUri = QUI::getRequest()->getRequestUri();
        $query = Url::getQueryString($requestUri);

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

        return QUI::getRewrite()->showErrorHeader($code, $redirectUrl);
    }


    /**
     * Returns the redirect path for a URL and project from the database.
     * If no entry is found false is returned.
     *
     * @param string $url
     * @param Project $Project
     *
     * @return bool|string URL on success, false on missing entry
     */
    public static function getRedirectForUrl(string $url, Project $Project): bool|string
    {
        try {
            $redirectData = QUI::getDataBase()->fetch([
                'from' => Database::getTableName($Project),
                'where' => [
                    Database::COLUMN_ID => md5(urldecode($url))
                ],
                'limit' => 1
            ]);

            if (!isset($redirectData[0])) {
                return false;
            }

            return $redirectData[0][Database::COLUMN_TARGET_URL];
        } catch (Exception) {
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
     * @throws PackageNotLicensedException
     */
    public static function addRedirect(string $sourceUrl, string $targetUrl, Project $Project): bool
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

            QUI::getDataBase()->replace(
                Database::getTableName($Project),
                [
                    Database::COLUMN_ID => md5(urldecode($sourceUrl)),
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
     * Automatically adds redirects for the given site and its children based on the site's source and target URL.
     *
     * @param QUI\Interfaces\Projects\Site $Site
     * @param string $sourceUrl
     * @param string $targetUrl
     *
     * @return void
     *
     * @throws PackageNotLicensedException
     */
    public static function addRedirectForSiteAndChildren(
        QUI\Interfaces\Projects\Site $Site,
        string $sourceUrl,
        string $targetUrl
    ): void {
        $Project = $Site->getProject();

        Manager::addRedirect($sourceUrl, $targetUrl, $Project);

        // Add redirects for children
        foreach (\QUI\Redirect\Site::getChildrenRecursive($Site) as $ChildSite) {
            // We need a try-catch inside the loop to continue adding redirects for other children, if one throws an error
            try {
                // When moving a site the cache is cleared only for the parent and not it's children.
                // This causes child-URLs to return the old URL instead of the new one.
                // So we have to manually delete the cache before querying the new URL.
                $ChildSite->deleteCache();

                $childTargetUrl = Url::prepareInternalTargetUrl($ChildSite->getUrlRewritten());

                /** @var Site $ChildSite */
                $childOldUrl = Utils::generateChildSourceUrlFromParentRedirectUrls(
                    $childTargetUrl,
                    $sourceUrl,
                    $targetUrl
                );

                if (!$childOldUrl) {
                    continue;
                }

                Manager::addRedirect($childOldUrl, $childTargetUrl, $Project);
            } catch (PackageNotLicensedException $Exception) {
                // Maximum number of redirects for the system's license reached
                throw $Exception;
            } catch (\Exception $Exception) {
                Log::writeException($Exception);
                continue;
            }
        }
    }

    /**
     * Check license requirements for quiqqer/redirect usage.
     *
     * @return void
     * @throws PackageNotLicensedException
     */
    protected static function checkLicense(): void
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
        $url = null;

        if (!empty($urls[$lang])) {
            $url = $urls[$lang];
        }

        throw new PackageNotLicensedException(
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
     * Returns an array of all redirects for a given project.
     * The array keys are 'source_url' and 'target_url'
     *
     * @param Project $Project
     *
     * @return array
     */
    public static function getRedirects(Project $Project): array
    {
        try {
            return QUI::getDataBase()->fetch([
                'select' => Database::COLUMN_SOURCE_URL . ',' . Database::COLUMN_TARGET_URL,
                'from' => Database::getTableName($Project)
            ]);
        } catch (QUI\Database\Exception $Exception) {
            Log::writeException($Exception);

            return [];
        }
    }

    /**
     * Get number of redirects in the system
     *
     * @param Project|null $Project $Project (optional) - Get number of redirects of a specific project [default: cross-project redirect count]
     * @return int
     *
     * @throws QUI\Database\Exception
     */
    public static function getRedirectCount(Project $Project = null): int
    {
        if (!empty($Project)) {
            $projects = [$Project];
        } else {
            $projects = QUI::getProjectManager()->getProjects(true);
        }

        $redirectCount = 0;

        /** @var Project $Project */
        foreach ($projects as $Project) {
            $result = QUI::getDataBase()->fetch([
                'count' => 1,
                'from' => Database::getTableName($Project)
            ]);

            $redirectCount += (int)current(current($result));
        }

        return $redirectCount;
    }

    /**
     * Removes the redirect with the given source URL from the given project.
     * Throws a Permission Exception if the current user has insufficient permission.
     *
     * @param string $sourceUrl
     * @param Project $Project
     *
     * @return boolean - Removal successful?
     *
     * @throws QUI\Permissions\Exception
     */
    public static function deleteRedirect(string $sourceUrl, Project $Project): bool
    {
        QUI\Permissions\Permission::checkPermission(Permission::REDIRECT_DELETE);

        try {
            QUI::getDataBase()->delete(
                Database::getTableName($Project),
                [
                    Database::COLUMN_ID => md5(urldecode($sourceUrl))
                ]
            );
        } catch (QUI\Database\Exception $Exception) {
            Log::writeException($Exception);

            return false;
        }

        return true;
    }
}
