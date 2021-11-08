<?php

/**
 * @author PCSG (Jan Wennrich)
 */

namespace QUI\Redirect;

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
}
