<?php

/**
 * @author PCSG (Jan Wennrich)
 */

namespace QUI\Redirect;

use QUI\Exception;
use QUI\Projects\Site;
use QUI\Utils\System\File;

class TemporaryStorage
{
    /**
     * Returns the path to the directory where the data is temporarily stored.
     *
     * @return string
     * @throws Exception
     */
    protected static function getDirectory(): string
    {
        $directory = \QUI::getPackage('quiqqer/redirect')->getVarDir() . \QUI::getUserBySession()->getId() . '/';

        if (!is_dir($directory)) {
            File::mkdir($directory);
        }

        return $directory;
    }

    protected static function getQueueFile(): string
    {
        return static::getDirectory() . 'queue.json';
    }

    protected static function getOldUrlsFile(): string
    {
        return static::getDirectory() . 'old_urls.json';
    }

    /**
     * Stores the URLs to process
     *
     * @param string[] $urls - The URLs to store
     */
    public static function setUrlsToProcess(array $urls): void
    {
        file_put_contents(static::getQueueFile(), json_encode($urls));
    }


    /**
     * Returns a site's children from the current user's session
     *
     * @return array
     */
    public static function getUrlsToProcess(): array
    {
        $rawData = File::getFileContent(static::getQueueFile());

        if (!$rawData) {
            return [];
        }

        return json_decode($rawData, true);
    }


    /**
     * Removes a site's children from the current user's session
     */
    public static function removeAllUrlsToProcess(): void
    {
        static::setUrlsToProcess([]);
    }


    /**
     * Removes an URL from the URLs to process
     *
     * @param string $url - The URL to remove
     *
     * @return string[] - The URLs without the removed URL
     */
    public static function removeUrlToProcess(string $url): array
    {
        $urls = static::getUrlsToProcess();

        $urlKey = array_search($url, $urls);
        if ($urlKey !== false) {
            unset($urls[$urlKey]);

            // use array_values() to reset array indizes
            $urls = array_values($urls);

            static::setUrlsToProcess($urls);
        }

        return $urls;
    }


    public static function setOldUrls(array $urls): void
    {
        file_put_contents(static::getOldUrlsFile(), json_encode($urls));
    }


    /**
     * Adds a site's old url to the session
     *
     * @param \QUI\Interfaces\Projects\Site $Site - The site to store the URL for
     */
    public static function setOldUrlsRecursivelyFromSite(\QUI\Interfaces\Projects\Site $Site): bool
    {
        $isTotalAddUrlSuccessful = true;

        $oldUrls = [];

        try {
            $oldUrls[$Site->getId()] = Url::prepareSourceUrl($Site->getUrlRewritten());
        } catch (Exception $Exception) {
            $isTotalAddUrlSuccessful = false;
        }

        foreach (\QUI\Redirect\Site::getChildrenRecursive($Site) as $ChildSite) {
            /** @var Site $ChildSite */
            // Use a separate try to continue on error
            try {
                $isChildAddUrlSuccessful      = true;
                $oldUrls[$ChildSite->getId()] = Url::prepareSourceUrl($ChildSite->getUrlRewritten());
            } catch (Exception $Exception) {
                $isChildAddUrlSuccessful = false;
            }

            if (!$isChildAddUrlSuccessful) {
                $isTotalAddUrlSuccessful = false;
            }
        }

        static::setOldUrls($oldUrls);

        return $isTotalAddUrlSuccessful;
    }

    public static function getOldUrls(): array
    {
        $oldUrls = json_decode(File::getFileContent(static::getOldUrlsFile()), true);

        if (!$oldUrls) {
            $oldUrls = [];
        }

        return $oldUrls;
    }

    /**
     * Returns a site's old URL from the current user's session
     *
     * @param int $siteId - The site's ID
     *
     * @return string|false
     */
    public static function getOldUrlForSiteId(int $siteId): string
    {
        $oldUrls = static::getOldUrls();

        if (!isset($oldUrls[$siteId])) {
            return false;
        }

        return $oldUrls[$siteId];
    }


    /**
     * Removes a site's old URL from the current user's session
     *
     * @param int $siteId
     *
     */
    public static function removeOldUrlForSiteId(int $siteId): void
    {
        $oldUrls = static::getOldUrls();

        unset($oldUrls[$siteId]);

        static::setOldUrls($oldUrls);
    }
}
