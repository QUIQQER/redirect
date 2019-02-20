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
        // TODO: rekursives löschen behandeln (?)
        try {
            $Site = new Site($Project, $siteId);
            $url  = $Site->getUrlRewritten();
            \QUI::getAjax()->triggerGlobalJavaScriptCallback(
                'redirectOnSiteDelete',
                $url
            );
        } catch (Exception $Exception) {
            Log::writeException($Exception);
        }
    }


    /**
     * Called as an event when a site is moved to a new location
     *
     * @param Site\Edit $Site - The site moved
     * @param int $parentId - The new parent id
     */
    public static function onSiteMove(Site\Edit $Site, $parentId)
    {
        // TODO: popup zeigen und nachfragen ob redirect erstellt werden soll (?)

        try {
            Handler::addRedirect($Site->getUrlRewritten(), $Site);

            foreach ($Site->getChildren([], true) as $ChildSite) {
                /** @var Site $ChildSite */
                Handler::addRedirect($ChildSite->getUrlRewritten(), $ChildSite);
            }
        } catch (Exception $Exception) {
            $successful = false;
        }

        if (!$successful) {
            // TODO: tell the user that redirect couldn't be added
        }
    }


    /**
     * Called as an event on package install
     *
     * @param Package $Package - The package being installed
     *
     * @throws \QUI\Database\Exception - redirects table couldn't be updated
     */
    public static function onInstall(Package $Package)
    {
        if ($Package->getName() != "quiqqer/redirect") {
            return;
        }

        $table = Handler::getTableName();
        \QUI::getDataBase()->fetchSQL("
            ALTER TABLE `$table` ADD PRIMARY KEY (`url`(80));
        ");
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