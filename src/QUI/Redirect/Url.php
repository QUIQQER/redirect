<?php

/**
 * @author PCSG (Jan Wennrich)
 */

namespace QUI\Redirect;


/**
 * Class Url
 * @package redirect\src\QUI\Redirect
 */
class Url
{
    /**
     * Prepares an URL to be used as a source URL.
     * This means only leaving the URLs' path behind and prepending a slash
     *
     * @param string $url - The URL to be prepared
     *
     * @return string
     */
    public static function prepareSourceUrl($url)
    {
        $urlPath = parse_url($url, PHP_URL_PATH);

        if (strpos($urlPath, "/") !== 0) {
            $urlPath = "/" . $urlPath;
        }

        return $urlPath;
    }


    /**
     * Prepares an URL to be used as an internal target URL.
     * This means only leaving the URLs' path behind and prepending a slash.
     *
     * @param string $url - The URL to be prepared
     *
     * @return string
     */
    public static function prepareInternalTargetUrl($url)
    {
        $urlPath = parse_url($url, PHP_URL_PATH);

        if (strpos($urlPath, "/") !== 0) {
            $urlPath = "/" . $urlPath;
        }

        return $urlPath;
    }


    /**
     * Returns if a given URL is an internal URL (starting with "index.php?id=")
     *
     * @param string $url - The URL to test
     *
     * @return boolean
     */
    public static function isInternal($url)
    {
        return strpos($url, 'index.php?id=') === 0;
    }
}
