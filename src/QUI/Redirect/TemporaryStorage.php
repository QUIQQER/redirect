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
     * If the old URL is not found, an empty string is returned.
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
            return '';
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
