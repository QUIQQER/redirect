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
        if ($code = 404) {
            Manager::attemptRedirect(\QUI::getRequest()->getRequestUri());
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
            $url  = $Site->getUrlRewritten();

            $ParentSite = $Site->getParent();

            // TODO: show notification if store in session failed (?)
            Session::storeChildrenUrlsInSession($Site);

            Frontend::showAddRedirectDialog(
                $url,
                $ParentSite->getUrlRewritten(),
                true
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
            $url = $Site->getUrlRewritten();

            $ParentSite = $Site->getParent();

            // TODO: show notification if store in session failed (?)
            Session::storeChildrenUrlsInSession($Site);

            Frontend::showAddRedirectDialog(
                $url,
                $ParentSite->getUrlRewritten(),
                true
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
            if ($Site->getUrlRewritten() == Session::getOldUrlFromSession($Site->getId())) {
                return;
            }

            Manager::addRedirectsFromSession($Site);
        } catch (Exception $Exception) {
            Log::writeException($Exception);
        }
    }


    /**
     * Called as an event on package install
     *
     * @param Package $Package - The package being installed
     *
     * @throws \QUI\Database\Exception - redirects table couldn't be setup
     */
    public static function onInstall(Package $Package)
    {
        if ($Package->getName() != "quiqqer/redirect") {
            return;
        }

        Database::setupDatabase();
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