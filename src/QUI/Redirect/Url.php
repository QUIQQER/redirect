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
     * Prepares a URL to be used as a source URL.
     * This means:
     *   - only leaving the URLs' path behind
     *   - prepending a slash
     *   - removing languages from the path
     *
     * @param string $url - The URL to be prepared
     *
     * @return string
     */
    public static function prepareSourceUrl(string $url): string
    {
        $url = static::getRelevantPartsFromUrl($url);

        return static::stripLanguageFromPath($url);
    }

    /**
     * Returns the parts from a given url that are relevant for a redirect.
     * This may include the path, the query and the fragment.
     *
     * @param $url
     *
     * @return string
     */
    public static function getRelevantPartsFromUrl($url): string
    {
        $urlParts = parse_url($url);

        if (!isset($urlParts['path'])) {
            return '';
        }

        $result = '/' . trim($urlParts['path'], '/');

        if (isset($urlParts['query'])) {
            $result .= '?' . $urlParts['query'];
        }

        if (isset($urlParts['fragment'])) {
            $result .= '#' . $urlParts['fragment'];
        }

        return $result;
    }


    public static function stripLanguageFromPath(string $url): string
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
        return "/" . $url;
    }


    /**
     * Prepares a URL to be used as an internal target URL.
     * This means only leaving the URLs' path behind and prepending a slash.
     *
     * @param string $url - The URL to be prepared
     *
     * @return string
     */
    public static function prepareInternalTargetUrl(string $url): string
    {
        $path = static::getPath($url);
        return self::stripLanguageFromPath($path);
    }


    /**
     * Returns if a given URL is an internal URL (starting with "index.php?id=")
     *
     * @param string $url - The URL to test
     * @return boolean
     */
    public static function isInternal(string $url): bool
    {
        // Remove all slashes from the beginning and end of the path
        $url = trim($url, '/');

        return str_starts_with($url, 'index.php?id=');
    }


    /**
     * Returns the path of a URL with a prepended slash.
     * When the second parameter is set to false no slash will be prepended.
     *
     * @param $url - The URL which path should be returned
     *
     * @return string
     */
    public static function getPath($url): string
    {
        $url = parse_url($url, PHP_URL_PATH);

        // Prepend slash if it does not exist
        if (!str_starts_with($url, "/")) {
            $url = "/" . $url;
        }

        return $url;
    }


    public static function getQueryString($url): bool|array|int|string|null
    {
        return parse_url($url, PHP_URL_QUERY);
    }
}
