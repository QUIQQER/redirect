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
     * Returns ALL (grand-)children of a site.
     * This means that even children of children are returned.
     *
     * Be careful about performance when using this!
     *
     * @param \QUI\Interfaces\Projects\Site $Site
     * @param array $params - Parameters to query children
     *                      $params['where']
     *                      $params['limit']
     * @param boolean $loadChildren - Load the children entirely
     *
     * @return \QUI\Interfaces\Projects\Site[]
     */
    public static function getChildrenRecursive(
        \QUI\Interfaces\Projects\Site $Site,
        $params = [],
        $loadChildren = false
    ) {
        try {
            $children = $Site->getChildren($params, $loadChildren);
        } catch (Exception $Exception) {
            return [];
        }

        $grandChildren = [];
        foreach ($children as $Child) {
            /** @var \QUI\Interfaces\Projects\Site $Child */
            $grandChildren = array_merge(
                $grandChildren,
                static::getChildrenRecursive($Child, $loadChildren)
            );
        }

        return array_merge($children, $grandChildren);
    }


    /**
     * Returns ALL (grand-)childrens' URLs of a site.
     * This means that even URLs from children of children are returned.
     *
     * Be careful about performance when using this!
     *
     * @param \QUI\Interfaces\Projects\Site $Site
     * @param array $params - Parameters to query children
     *                      $params['where']
     *                      $params['limit']
     * @param boolean $stripLanguage - Strip the language from the URL
     *
     * @return string[]
     */
    public static function getChildrenUrlsRecursive(
        \QUI\Interfaces\Projects\Site $Site,
        $params = [],
        $stripLanguage = false
    ) {
        // A basic Site-object (not SiteEdit) can be used to retrieve URLs
        if (!is_subclass_of($Site, \QUI\Projects\Site::class)) {
            try {
                $Site = new \QUI\Projects\Site($Site->getProject(), $Site->getId());
            } catch (Exception $Exception) {
                Log::writeException($Exception);
            }
        }

        $children = static::getChildrenRecursive($Site, $params);

        $result = [];
        foreach ($children as $child) {
            $urlRewritten = $child->getUrlRewritten();

            if ($stripLanguage) {
                $urlRewritten = Url::stripLanguageFromPath($urlRewritten);
            }

            $result[] = $urlRewritten;
        }

        return $result;
    }
}