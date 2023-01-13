<?php

/**
 * @author PCSG (Jan Wennrich)
 */

namespace QUI\Redirect;

use QUI\Exception;
use QUI\Package\PackageNotLicensedException;
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
     * Array that contains IDs of sites that were already processed onSiteSaveBefore.
     * This is required to ensure that every site is only processed once per request.
     * Otherwise, the old and new URLs can get messed up, showing the add-redirect-dialog multiple times or never.
     *
     * @var array
     */
    protected static array $sitesProcessedOnSiteSaveBefore = [];

    /**
     * Array that contains IDs of sites that were already processed onSiteSave.
     * This is required to ensure that every site is only processed once per request.
     * Otherwise, the old and new URLs can get messed up, showing the add-redirect-dialog multiple times or never.
     *
     * @var array
     */
    protected static array $sitesProcessedOnSiteSave = [];

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
            $Site = new Site\Edit($Project, $siteId);

            if (!\QUI\Redirect\Site::isActive($Site)) {
                return;
            }

            $sourceUrl = Url::prepareSourceUrl($Site->getUrlRewritten());

            $Project = $Site->getProject();

            $ParentSite = $Site->getParent();
            $targetUrl  = '';

            if ($ParentSite) {
                $targetUrl = Url::prepareInternalTargetUrl($ParentSite->getUrlRewritten());
            }

            $childrenUrls = Utils::makeChildrenArrayForAddRedirectDialog(
                \QUI\Redirect\Site::getChildrenUrlsRecursive($Site),
                $sourceUrl,
                $targetUrl
            );

            Frontend::showAddRedirectDialog(
                $sourceUrl,
                $targetUrl,
                $Project->getName(),
                $Project->getLang(),
                $childrenUrls
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
            $sourceUrl = Url::prepareSourceUrl($Site->getUrlRewritten());

            $Project = $Site->getProject();

            $ParentSite = $Site->getParent();
            $targetUrl  = '';

            if ($ParentSite) {
                $targetUrl = Url::prepareInternalTargetUrl($ParentSite->getUrlRewritten());
            }

            $childrenUrls = Utils::makeChildrenArrayForAddRedirectDialog(
                \QUI\Redirect\Site::getChildrenUrlsRecursive($Site),
                $sourceUrl,
                $targetUrl
            );

            Frontend::showAddRedirectDialog(
                $sourceUrl,
                $targetUrl,
                $Project->getName(),
                $Project->getLang(),
                $childrenUrls
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

        try {
            TemporaryStorage::removeAllUrls();
            TemporaryStorage::storeUrl($Site);
        } catch (Exception $Exception) {
            Log::writeException($Exception);
            return;
        }
    }


    /**
     * Called as an event when a site is moved to a new location.
     * Add redirects for the sites' new URLs.
     *
     * @param Site\Edit $Site - The site moved
     * @param int $parentId - The new parent id
     */
    public static function onSiteMoveAfter(Site\Edit $Site, $parentId)
    {
        static::handleOnSiteMoveOrSave($Site);
    }


    /**
     * Called as an event before a site is saved.
     * Stores the sites' old URLs in the session.
     *
     * @param Site\Edit $Site - The saved site
     */
    public static function onSiteSaveBefore(Site\Edit $Site)
    {
        $siteId = $Site->getId();

        // Make sure this is only executed/called once per site per request
        if (isset(self::$sitesProcessedOnSiteSaveBefore[$siteId])) {
            return;
        }

        // Store that the given site was processed for this request
        self::$sitesProcessedOnSiteSaveBefore[$siteId] = true;

        if ($siteId == 1) {
            return;
        }

        if (!\QUI\Redirect\Site::isActive($Site)) {
            return;
        }

        try {
            TemporaryStorage::removeAllUrls();
            TemporaryStorage::storeUrl($Site);
        } catch (Exception $Exception) {
            Log::writeException($Exception);
        }
    }


    /**
     * Called as an event when a site is saved.
     * Add redirects from URLs stored in the session.
     *
     * @param Site\Edit $Site - The saved site
     */
    public static function onSiteSave(Site\Edit $Site)
    {
        $siteId = $Site->getId();

        // Make sure this is only executed/called once per site per request
        if (isset(self::$sitesProcessedOnSiteSave[$siteId])) {
            return;
        }

        // Store that the given site was processed for this request
        self::$sitesProcessedOnSiteSave[$siteId] = true;

        if ($siteId == 1) {
            return;
        }

        static::handleOnSiteMoveOrSave($Site);
    }

    protected static function handleOnSiteMoveOrSave(Site\Edit $Site)
    {
        if (!\QUI\Redirect\Site::isActive($Site)) {
            return;
        }

        // Add redirects from old urls to new urls
        try {
            $siteOldUrl = Url::prepareSourceUrl(TemporaryStorage::getUrl($Site));
            $siteNewUrl = Url::prepareInternalTargetUrl($Site->getUrlRewritten());

            if ($siteOldUrl == $siteNewUrl) {
                return;
            }

            Manager::addRedirectForSiteAndChildren($Site, $siteOldUrl, $siteNewUrl);
        } catch (PackageNotLicensedException $Exception) {
            // Maximum number of redirects for the system's license reached
            \QUI::getMessagesHandler()->addAttention(\QUI::getLocale()->get(
                'quiqqer/redirect',
                'site.move.error_license',
                ['error' => $Exception->getMessage()]
            ));

            // No need to try adding the redirects for the children -> just exit.
            return;
        } catch (Exception $Exception) {
            Log::writeException($Exception);
        }

        try {
            TemporaryStorage::removeUrl($Site);
        } catch (Exception $Exception) {
            Log::writeException($Exception);
        }

        // Redirect add completed
        \QUI::getMessagesHandler()->addInformation(
            \QUI::getLocale()->get('quiqqer/redirect', 'site.move.info')
        );
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
