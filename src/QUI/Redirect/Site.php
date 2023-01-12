<?php

/**
 * @author PCSG (Jan Wennrich)
 */

namespace QUI\Redirect;

use QUI\Exception;
use QUI\System\Log;

/**
 * Class with various helper-methods for the redirect module
 *
 * All this session stuff is needed in order to skip adding redirects for children.
 * This way it's not necessary to overwrite system's site delete methods
 *
 * @package \QUI\Redirect
 */
class Site
{
    /**
     * Returns ALL (grand-)children of a site as a generator.
     * This means that even children of children are returned.
     *
     * @param \QUI\Interfaces\Projects\Site $Site
     * @param array $params - Parameters to query children
     *                      $params['where']
     *                      $params['limit']
     *
     * @return \Generator
     */
    public static function getChildrenRecursive(\QUI\Interfaces\Projects\Site $Site, array $params = []): \Generator
    {
        $Project = $Site->getProject();

        foreach ($Site->getChildrenIdsRecursive($params) as $childId) {
            try {
                yield $Project->get($childId);
            } catch (Exception $Exception) {
                Log::writeException($Exception);
                continue;
            };
        }
    }


    /**
     * Returns ALL (grand-)childrens' URLs of a site.
     * This means that even URLs from children of children are returned.
     *
     * Be careful about performance when using this!
     *
     * @param \QUI\Interfaces\Projects\Site $Site
     *
     * @return string[]
     */
    public static function getChildrenUrlsRecursive(\QUI\Interfaces\Projects\Site $Site): array
    {
        // A basic Site-object (not SiteEdit) can be used to retrieve URLs
        if (!is_subclass_of($Site, \QUI\Projects\Site::class)) {
            try {
                $Site = new \QUI\Projects\Site($Site->getProject(), $Site->getId());
            } catch (Exception $Exception) {
                Log::writeException($Exception);
            }
        }

        $children = static::getChildrenRecursive($Site, ['active' => '0&1']);

        $result = [];
        foreach ($children as $child) {
            $urlRewritten = $child->getUrlRewritten();

            $urlRewritten = Url::stripLanguageFromPath($urlRewritten);

            $result[] = $urlRewritten;
        }

        return $result;
    }

    /**
     * Returns true if the given site is activated.
     *
     * @param \QUI\Interfaces\Projects\Site $Site
     *
     * @return bool
     */
    public static function isActive(\QUI\Interfaces\Projects\Site $Site): bool
    {
        return $Site->getAttribute('active') == 1;
    }
}
