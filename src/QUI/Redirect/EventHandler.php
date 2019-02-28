<?php

/**
 * @author PCSG (Jan Wennrich)
 */

namespace QUI\Redirect;

use QUI\Exception;
use QUI\Package\Package;
use QUI\Projects\Project;
use QUI\Projects\Site;
use QUI\System\Log;

/**
 * Class EventHandler
 * @package \QUI\Redirect
 */
class EventHandler
{
    /**
     * Called as an event when an error code/header is shown/returned
     *
     * @param $code
     * @param $url
     */
    public static function onErrorHeaderShow($code, $url)
    {
        if ($code != 404) {
            return;
        }

        try {
            $path = Url::prepareSourceUrl(\QUI::getRequest()->getRequestUri());
            Manager::attemptRedirect(
                $path,
                \QUI::getRewrite()->getProject()
            );
        } catch (Exception $Exception) {
            // TODO: Error Handling ?
        }
    }


    /**
     * Called as an event when a site is deleted
     *
     * @param $siteId
     * @param Project $Project
     */
    public static function onSiteDelete($siteId, Project $Project)
    {
        try {
            $Site = new Site($Project, $siteId);
            $url  = Url::prepareSourceUrl($Site->getUrlRewritten());

            $Project = $Site->getProject();

            $ParentSite = $Site->getParent();
            $parentUrl  = false;

            if ($ParentSite) {
                $parentUrl = Url::prepareInternalTargetUrl($ParentSite->getUrlRewritten());
            }

            // TODO: show notification if store in session failed (?)
            $childrenUrls = \QUI\Redirect\Site::getChildrenUrlsRecursive($Site, ['active' => '0&1'], true);
            Session::storeUrlsToProcess($childrenUrls);

            Frontend::showAddRedirectDialog(
                $url,
                $parentUrl,
                true,
                $Project->getName(),
                $Project->getLang()
            );
        } catch (Exception $Exception) {
            Log::writeException($Exception);
        }
    }


    /**
     * Called as an event when a site is deactivated
     *
     * @param \QUI\Interfaces\Projects\Site $Site - The deactivated site
     */
    public static function onSiteDeactivate(\QUI\Interfaces\Projects\Site $Site)
    {
        try {
            $url = Url::prepareSourceUrl($Site->getUrlRewritten());

            $Project = $Site->getProject();

            $ParentSite = $Site->getParent();
            $parentUrl  = false;

            if ($ParentSite) {
                $parentUrl = Url::prepareInternalTargetUrl($ParentSite->getUrlRewritten());
            }

            // TODO: show notification if store in session failed (?)
            $childrenUrls = \QUI\Redirect\Site::getChildrenUrlsRecursive($Site, ['active' => '0&1'], true);
            Session::storeUrlsToProcess($childrenUrls);

            Frontend::showAddRedirectDialog(
                $url,
                $parentUrl,
                true,
                $Project->getName(),
                $Project->getLang()
            );
        } catch (Exception $Exception) {
            Log::writeException($Exception);
        }
    }


    /**
     * Called as an event when a site is moved to a new location.
     * Stores the sites' old URLs in the session
     *
     * @param Site\Edit $Site - The site moved
     * @param int $parentId - The new parent id
     */
    public static function onSiteMoveBefore(Site\Edit $Site, $parentId)
    {
        Session::addUrlsRecursive($Site);
    }


    /**
     * Called as an event when a site is moved to a new location.
     * Adds redirects fire the sites' new URLs.
     *
     * @param Site\Edit $Site - The site moved
     * @param int $parentId - The new parent id
     */
    public static function onSiteMoveAfter(Site\Edit $Site, $parentId)
    {
        Manager::addRedirectsFromSession($Site);
    }


    /**
     * Called as an event before a site is saved.
     * Stores the sites' old URLs in the session.
     *
     * @param Site\Edit $Site - The saved site
     */
    public static function onSiteSaveBefore(Site\Edit $Site)
    {
        Session::addUrlsRecursive($Site);
    }


    /**
     * Called as an event when a site is saved.
     * Adds redirects from URLs stored in the session.
     *
     * @param Site\Edit $Site - The saved site
     */
    public static function onSiteSave(Site\Edit $Site)
    {
        try {
            $newUrl = $Site->getUrlRewritten();
            $oldUrl = Session::getOldUrlFromSession($Site->getId());
            if (Url::prepareSourceUrl($newUrl) == $oldUrl) {
                return;
            }

            Manager::addRedirectsFromSession($Site);
        } catch (Exception $Exception) {
            Log::writeException($Exception);
        }
    }


    /**
     * Called as an event when the admin footer is loaded
     *
     * Injects JS code into it
     */
    public static function onAdminLoadFooter()
    {
        $jsFile = URL_OPT_DIR . 'quiqqer/redirect/bin/onAdminLoadFooter.js';
        echo '<script src="' . $jsFile . '"></script>';
    }
}