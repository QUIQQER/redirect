<?php

/**
 * @author PCSG (Jan Wennrich)
 */

namespace QUI\Redirect;

use QUI\Exception;
use QUI\Projects\Site;
use QUI\Utils\System\File;

use function file_put_contents;
use function is_dir;

class TemporaryStorage
{
    /**
     * Sets the list of old URLs from a given site and it's children.
     *
     * @param \QUI\Interfaces\Projects\Site $Site
     *
     * @return bool
     */
    public static function storeUrlsRecursivelyFromSite(\QUI\Interfaces\Projects\Site $Site): bool
    {
        $isTotalAddUrlSuccessful = true;

        try {
            static::storeUrl($Site);
        } catch (Exception $Exception) {
            $isTotalAddUrlSuccessful = false;
        }

        foreach (\QUI\Redirect\Site::getChildrenRecursive($Site) as $ChildSite) {
            /** @var Site $ChildSite */
            // Use a separate try to continue on error
            try {
                $isChildAddUrlSuccessful      = true;
                static::storeUrl($ChildSite);
            } catch (Exception $Exception) {
                $isChildAddUrlSuccessful = false;
            }

            if (!$isChildAddUrlSuccessful) {
                $isTotalAddUrlSuccessful = false;
            }
        }

        return $isTotalAddUrlSuccessful;
    }

    /**
     * Generates a key that can be used to identify the old url in the temporary storage.
     *
     * @param \QUI\Interfaces\Projects\Site $Site
     *
     * @return string
     */
    protected static function generateKey(\QUI\Interfaces\Projects\Site $Site): string
    {
        return "{$Site->getProject()->getName()}_{$Site->getProject()->getLang()}_{$Site->getId()}";
    }

    /**
     * Stores the URL of the given site in the temporary storage.
     *
     * @param \QUI\Interfaces\Projects\Site $Site
     *
     * @return void
     *
     * @throws Exception
     */
    public static function storeUrl(\QUI\Interfaces\Projects\Site $Site): void
    {
        $path = static::getFilePath($Site);

        $url = Url::prepareSourceUrl($Site->getUrlRewritten());

        file_put_contents($path, $url);
    }

    /**
     * Returns the path of the file used to store the old url for the given site.
     *
     * @param \QUI\Interfaces\Projects\Site $Site
     *
     * @return string
     *
     * @throws Exception
     */
    protected static function getFilePath(\QUI\Interfaces\Projects\Site $Site): string
    {
        $directory = \QUI::getPackage('quiqqer/redirect')->getVarDir() . \QUI::getUserBySession()->getId() . '/';

        if (!is_dir($directory)) {
            File::mkdir($directory);
        }

        return $directory . static::generateKey($Site);
    }

    /**
     * Returns the old url for a given site.
     * Throws an exception, if no old url exists.
     *
     * @param \QUI\Interfaces\Projects\Site $Site
     *
     * @return string
     *
     * @throws Exception
     */
    public static function getUrl(\QUI\Interfaces\Projects\Site $Site): string
    {
        $filePath = static::getFilePath($Site);

        $url = File::getFileContent($filePath);

        if (empty($url)) {
            throw new Exception('URL for this site does not exist.');
        }

        return $url;
    }

    /**
     * Removes the entry/file for the given site.
     *
     * @param \QUI\Interfaces\Projects\Site $Site
     *
     * @return void
     *
     * @throws Exception
     */
    public static function removeUrl(\QUI\Interfaces\Projects\Site $Site): void
    {
        File::unlink(static::getFilePath($Site));
    }
}
