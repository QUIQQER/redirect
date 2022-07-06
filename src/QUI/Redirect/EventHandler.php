<?php

/**
 * @author PCSG (Jan Wennrich)
 */

namespace QUI\Redirect;

use QUI\Exception;
use QUI\Package\Package;
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

        $Project = $Site->getProject();

        // Add redirects from old urls to new urls
        try {
            $siteId     = $Site->getId();
            $siteOldUrl = Url::prepareSourceUrl(TemporaryStorage::getOldUrlForSiteId($siteId));
            $siteNewUrl = Url::prepareInternalTargetUrl($Site->getUrlRewritten());

            Manager::addRedirect($siteOldUrl, $siteNewUrl, $Project);
            TemporaryStorage::removeOldUrlForSiteId($siteId);
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

        // Add redirects for children
        foreach (\QUI\Redirect\Site::getChildrenRecursive($Site) as $ChildSite) {
            // We need a try-catch inside the loop to continue adding redirects for other children, if one throws an error
            try {
                /** @var Site $ChildSite */
                $childSiteId = $ChildSite->getId();

                $childOldUrl = Url::prepareSourceUrl(TemporaryStorage::getOldUrlForSiteId($childSiteId));
                if (!$childOldUrl) {
                    continue;
                }

                // When moving a site the cache is cleared only for the parent and not it's children.
                // This causes child-URLs to return the old URL instead of the new one.
                // So we have to manually delete the cache before querying the new URL.
                $ChildSite->deleteCache();
                $childNewUrl = Url::prepareInternalTargetUrl($ChildSite->getUrlRewritten());

                Manager::addRedirect($childOldUrl, $childNewUrl, $Project);

                TemporaryStorage::removeOldUrlForSiteId($childSiteId);
            } catch (PackageNotLicensedException $Exception) {
                // Maximum number of redirects for the system's license reached
                \QUI::getMessagesHandler()->addAttention(\QUI::getLocale()->get(
                    'quiqqer/redirect',
                    'site.move.error_license',
                    ['error' => $Exception->getMessage()]
                ));

                // No need to try adding the redirects for the children -> just exit.
                return;
            } catch (\Exception $Exception) {
                Log::writeException($Exception);
                continue;
            }
        }

        // Redirect add completed
        \QUI::getMessagesHandler()->addInformation(
            \QUI::getLocale()->get('quiqqer/redirect', 'site.move.info')
        );
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

            TemporaryStorage::setOldUrlsRecursivelyFromSite($Site);
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

            $oldUrl = TemporaryStorage::getOldUrlForSiteId($Site->getId());
            $newUrl = Url::prepareSourceUrl($Site->getUrlRewritten());

            if ($newUrl == $oldUrl) {
                return;
            }

            $Project = $Site->getProject();

            $childrenUrls = Utils::makeChildrenArrayForAddRedirectDialog(
                \QUI\Redirect\Site::getChildrenUrlsRecursive($Site),
                $oldUrl,
                $newUrl
            );

            Frontend::showAddRedirectDialog(
                $oldUrl,
                $newUrl,
                $Project->getName(),
                $Project->getLang(),
                $childrenUrls
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
