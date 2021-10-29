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
     * There is a separate folder for each user/session.
     *
     * @return string
     *
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


    /**
     * Returns the path to the file that stores the queue (URLs to process).
     *
     * @return string
     *
     * @throws Exception
     */
    protected static function getQueueFile(): string
    {
        return static::getDirectory() . 'queue.json';
    }


    /**
     * Returns the path to the file that stores old URLs.
     *
     * @return string
     *
     * @throws Exception
     */
    protected static function getOldUrlsFile(): string
    {
        return static::getDirectory() . 'old_urls.json';
    }


    /**
     * Stores the URLs to process later (in the queue file).
     *
     * @param string[] $urls
     *
     * @throws Exception
     */
    public static function setUrlsToProcess(array $urls): void
    {
        file_put_contents(static::getQueueFile(), json_encode($urls));
    }


    /**
     * Returns all URL that are left to process (in the queue).
     *
     * @return array
     *
     * @throws Exception
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
     * Clears the list of URLs to process.
     *
     * @throws Exception
     */
    public static function removeAllUrlsToProcess(): void
    {
        static::setUrlsToProcess([]);
    }


    /**
     * Removes the given URL from the list of URLs to process.
     *
     * @param string $url
     *
     * @return string[]
     *
     * @throws Exception
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


    /**
     * Sets the list of URLs to process.
     *
     * @param array $urls
     *
     * @throws Exception
     */
    public static function setOldUrls(array $urls): void
    {
        file_put_contents(static::getOldUrlsFile(), json_encode($urls));
    }


    /**
     * Sets the list of old URLs from a given site and it's children.
     *
     * @param \QUI\Interfaces\Projects\Site $Site
     *
     * @return bool
     *
     * @throws Exception
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


    /**
     * Returns the list/array of old URLs.
     * The site id is the array's index and the old URL is the corresponding value.
     *
     * @return array
     *
     * @throws Exception
     */
    public static function getOldUrls(): array
    {
        $oldUrls = json_decode(File::getFileContent(static::getOldUrlsFile()), true);

        if (!$oldUrls) {
            $oldUrls = [];
        }

        return $oldUrls;
    }


    /**
     * Returns the old URL for a given site id.
     * If the old URL is not found, false is returned.
     *
     * @param int $siteId
     *
     * @return string
     *
     * @throws Exception
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
     * Removes the old URL of a given site ID from the list of old URLs.
     *
     * @param int $siteId
     *
     * @throws Exception
     */
    public static function removeOldUrlForSiteId(int $siteId): void
    {
        $oldUrls = static::getOldUrls();

        unset($oldUrls[$siteId]);

        static::setOldUrls($oldUrls);
    }
}
