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
use \Symfony\Component\HttpFoundation\Response;

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
        // We only care about 404 and 303 codes
        if ($code != Response::HTTP_NOT_FOUND && $code != Response::HTTP_SEE_OTHER) {
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
            $Site = $Project->get($siteId);

            if (!\QUI\Redirect\Site::isActive($Site)) {
                return;
            }

            $url = Url::prepareSourceUrl($Site->getUrlRewritten());

            $Project = $Site->getProject();

            $ParentSite = $Site->getParent();
            $parentUrl  = false;

            if ($ParentSite) {
                $parentUrl = Url::prepareInternalTargetUrl($ParentSite->getUrlRewritten());
            }

            // TODO: show notification if store in session failed (?)
            $childrenUrls = \QUI\Redirect\Site::getChildrenUrlsRecursive($Site, ['active' => '0&1'], true);
            TemporaryStorage::setUrlsToProcess($childrenUrls);

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
        // Sites restored from trash are moved and then deactivated.
        // Their status (before deactivation) is "-1".
        // Therefore checking if the site is active, prevents adding redirects for sites moved from trash.
        // related: quiqqer/redirect#11
        if (!\QUI\Redirect\Site::isActive($Site)) {
            return;
        }

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
            TemporaryStorage::setUrlsToProcess($childrenUrls);

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
        if (!\QUI\Redirect\Site::isActive($Site)) {
            return;
        }

        // Bug in quiqqer/quiqqer:
        // The rewritten URL cache is not emptied when a site's URL changes.
        // Therefore we have to do it manually.
        // TODO: Remove this when quiqqer/quiqqer#1099 is resolved
        \QUI::getRewrite()->getOutput()->removeRewrittenUrlCache($Site);

        TemporaryStorage::setOldUrlsRecursivelyFromSite($Site);
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
        if (!\QUI\Redirect\Site::isActive($Site)) {
            return;
        }

        // Bug in quiqqer/quiqqer:
        // The rewritten URL cache is not emptied when a site's URL changes.
        // Therefore we have to do it manually.
        // TODO: Remove this when quiqqer/quiqqer#1099 is resolved
        \QUI::getRewrite()->getOutput()->removeRewrittenUrlCache($Site);

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
        if (!\QUI\Redirect\Site::isActive($Site)) {
            return;
        }

        try {
            if ($Site->getId() == 1) {
                return;
            }

            $Site->setAttribute('redirectOldUrl', $Site->getUrlRewritten());
        } catch (Exception $Exception) {
            Log::writeException($Exception);
        }
    }


    /**
     * Called as an event when a site is saved.
     * Adds redirects from URLs stored in the session.
     *
     * @param Site\Edit $Site - The saved site
     */
    public static function onSiteSave(Site\Edit $Site)
    {
        if (!\QUI\Redirect\Site::isActive($Site)) {
            return;
        }

        try {
            if ($Site->getId() == 1) {
                return;
            }

            $oldUrl = Url::prepareSourceUrl($Site->getAttribute('redirectOldUrl'));
            $newUrl = Url::prepareSourceUrl($Site->getUrlRewritten());

            if ($newUrl == $oldUrl) {
                return;
            }

            $Project      = $Site->getProject();
            $childrenUrls = \QUI\Redirect\Site::getChildrenUrlsRecursive($Site, ['active' => '0&1'], true);
            TemporaryStorage::setUrlsToProcess($childrenUrls);

            Frontend::showAddRedirectDialog(
                $oldUrl,
                $newUrl,
                true,
                $Project->getName(),
                $Project->getLang()
            );
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
        $jsFile = URL_OPT_DIR.'quiqqer/redirect/bin/onAdminLoadFooter.js';
        echo '<script src="'.$jsFile.'"></script>';
    }
}
