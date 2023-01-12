<?php

/**
 * @author PCSG (Jan Wennrich)
 */

namespace QUI\Redirect;

use function strlen;
use function substr;
use function substr_replace;

/**
 * Class Url
 * @package redirect\src\QUI\Redirect
 */
class Utils
{
    public static function makeChildrenArrayForAddRedirectDialog(
        array $childrenUrls,
        string $parentSourceUrl = '',
        string $parentTargetUrl = ''
    ): array {
        $result = [];

        foreach ($childrenUrls as $childUrl) {
            $target = '';

            if ($parentSourceUrl && $parentTargetUrl) {
                // Check if $childUrl begins with $parentSourceUrl
                if (substr($childUrl, 0, strlen($parentSourceUrl)) === $parentSourceUrl) {
                    $replacement = $parentTargetUrl;

                    // Replacing with just a slash, results in double slashes in the beginning -> replace with nothing
                    if ($replacement === '/') {
                        $replacement = '';
                    }

                    // Replace the beginning of $childUrl with $parentTargetUrl
                    // We can't use a "global" str_replace as this might replace parts at the end of the url too
                    $target = substr_replace(
                        $childUrl,
                        $replacement,
                        0,
                        strlen($parentSourceUrl)
                    );
                }
            }

            $result[] = [
                'source' => $childUrl,
                'target' => $target
            ];
        }

        return $result;
    }

    /**
     * Generates a child source URL from the child target URL and its parent source and target URLs.
     * Using this method the "old" child URL (source URL for the redirect) does not have to be stored.
     * It can be generated on the fly which requires less overhead.
     *
     * @param string $childTargetUrl
     * @param string $parentSourceUrl
     * @param string $parentTargetUrl
     *
     * @return string
     */
    public static function generateChildSourceUrlFromParentRedirectUrls(
        string $childTargetUrl,
        string $parentSourceUrl,
        string $parentTargetUrl
    ): string {
        $replacement = $parentSourceUrl;

        // Replacing with just a slash, results in double slashes in the beginning -> replace with nothing
        if ($replacement === '/') {
            $replacement = '';
        }

        // Replace the beginning of $childUrl with $parentSourceUrl
        // We can't use a "global" str_replace as this might replace parts at the end of the url too
        // Therefore we replace just the length of $parentTargetUrl
        // Example:
        // Parent Source: /hello
        // Parent Target: /hi
        // Child Target: /hi/world
        // -> Child Source: /hello/world
        return substr_replace(
            $childTargetUrl,
            $replacement,
            0,
            strlen($parentTargetUrl)
        );
    }
}
