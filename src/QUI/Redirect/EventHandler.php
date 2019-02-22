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
            Handler::attemptRedirect(\QUI::getRequest()->getRequestUri());
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

            // TODO: show notification if store in session failed (?)
            Session::storeChildrenUrlsInSession($Site);

            Frontend::showAddRedirectDialog($url, true);
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
        $totalMoveSuccessful = true;
        try {
            Session::addOldUrlToSession($Site->getId(), $Site->getUrlRewritten());
        } catch (Exception $Exception) {
            $totalMoveSuccessful = false;
        }

        foreach (\QUI\Redirect\Site::getChildrenRecursive($Site) as $ChildSite) {
            /** @var Site $ChildSite */
            // Use a separate try to continue on error
            try {
                $childMoveSuccessful = true;
                Session::addOldUrlToSession($ChildSite->getId(), $ChildSite->getUrlRewritten());
            } catch (Exception $Exception) {
                $childMoveSuccessful = false;
            }

            if (!$childMoveSuccessful) {
                $totalMoveSuccessful = false;
            }
        }

        // Move completed
        \QUI::getMessagesHandler()->addInformation(
            \QUI::getLocale()->get('quiqqer/redirect', 'site.move.info')
        );

        if (!$totalMoveSuccessful) {
            // Something went wrong moving (at least) one site
            \QUI::getMessagesHandler()->addAttention(
                \QUI::getLocale()->get('quiqqer/redirect', 'site.move.error')
            );
        }
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
        // TODO: clean up this mess (e.g. Notify the user in addRedirect())
        $totalMoveSuccessful = true;
        try {
            $oldUrl = Session::getOldUrlFromSession($Site->getId());
            Handler::addRedirect($oldUrl, $Site);

            foreach (\QUI\Redirect\Site::getChildrenRecursive($Site) as $ChildSite) {
                /** @var Site $ChildSite */
                // Use a separate try to continue on error
                try {
                    $childSiteId = $ChildSite->getId();
                    $childOldUrl = Session::getOldUrlFromSession($childSiteId);

                    if (!$childOldUrl) {
                        // Escape this try
                        throw new Exception();
                    }

                    $childMoveSuccessful = Handler::addRedirect($childOldUrl, $ChildSite);
                    Session::removeOldUrlFromSession($childSiteId);
                } catch (Exception $Exception) {
                    $childMoveSuccessful = false;
                }

                if (!$childMoveSuccessful) {
                    $totalMoveSuccessful = false;
                }
            }
        } catch (Exception $Exception) {
            $totalMoveSuccessful = false;
        }

        // Move completed
        \QUI::getMessagesHandler()->addInformation(
            \QUI::getLocale()->get('quiqqer/redirect', 'site.move.info')
        );

        if (!$totalMoveSuccessful) {
            // Something went wrong moving (at least) one site
            \QUI::getMessagesHandler()->addAttention(
                \QUI::getLocale()->get('quiqqer/redirect', 'site.move.error')
            );
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