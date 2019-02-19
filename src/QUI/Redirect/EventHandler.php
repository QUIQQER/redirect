<?php

/**
 * @author PCSG (Jan Wennrich)
 */

namespace QUI\Redirect;

use QUI\Exception;
use QUI\Package\Package;
use QUI\Projects\Project;
use QUI\Projects\Site;

/**
 * Class EventHandler
 * @package \QUI\Redirect
 */
class EventHandler
{
    /**
     * Called as an event (onErrorHeaderShowBefore)
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
     * Called as an event
     *
     * @param $siteId
     * @param Project $Project
     */
    public static function onSiteDelete($siteId, Project $Project)
    {
        // TODO: popup zeigen und nachfragen ob und wohin redirect erstellt werden soll

        try {
            $Site       = new Site($Project, $siteId);
            $successful = Handler::addRedirect($Site->getUrlRewritten(), $Site);
        } catch (Exception $Exception) {
            $successful = false;
        }

        if (!$successful) {
            // TODO: tell the user that redirect couldn't be added
        }
    }


    /**
     * Called as an event
     *
     * @param Site\Edit $Site - The site moved
     * @param int $parentId - The new parent id
     */
    public static function onSiteMove(Site\Edit $Site, $parentId)
    {
        // TODO: popup zeigen und nachfragen ob redirect erstellt werden soll

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
}