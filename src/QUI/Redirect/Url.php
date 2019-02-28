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
     * This means:
     *   - only leaving the URLs' path behind
     *   - prepending a slash
     *   - removing languages from the path
     *
     * @param string $url - The URL to be prepared
     *
     * @return string
     */
    public static function prepareSourceUrl($url)
    {
        $urlPath = parse_url($url, PHP_URL_PATH);

        $urlPath = static::stripLanguageFromPath($urlPath);

        return $urlPath;
    }


    public static function stripLanguageFromPath($url)
    {
        // Remove all slashes from the beginning and end of the path
        $url = trim($url, '/');

        // Strip language parts from the URL (e.g. "/en/mysite" becomes "/mysite"
        // Check strlen = 2 if we have a root URL (e.g. "/en/" becomes just "en" on this check)
        // Since site-names can't be two characters long, it's definitely a language
        if (strpos($url, '/') === 2 || strlen($url) === 2) {
            $url = substr($url, 3);
        }

        // Append a slash
        $url = "/" . $url;

        return $url;
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
        $url = self::stripLanguageFromPath($url);

        return static::getPath($url);
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


    /**
     * Returns the path of an URL with a prepended slash.
     * When the second parameter is set to false no slash will be prepended.
     *
     * @param $url - The URL which's path should be returned
     * @param $prependSlash - Return the path with a prepended slash
     *
     * @return string
     */
    public static function getPath($url, $prependSlash = true)
    {
        $url = parse_url($url, PHP_URL_PATH);

        if ($prependSlash && strpos($url, "/") !== 0) {
            $url = "/" . $url;
        }

        if (!$prependSlash && strpos($url, "/") === 0) {
            $url = substr_replace($url, '', 0, 1);
        }

        return $url;
    }


    public static function getQueryString($url)
    {
        return parse_url($url, PHP_URL_QUERY);
    }
}
