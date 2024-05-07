<?php

namespace QUI\Redirect;

use QUI;
use QUI\Exception;
use Random\RandomException;

use function count;
use function random_int;

class TestUtil
{
    /**
     * @throws Exception
     */
    public static function getDefaultProject(): ?\QUI\Projects\Project
    {
        return QUI::getProjectManager()->getStandard();
    }

    /**
     * @throws RandomException
     */
    public static function getRandomPath(): string
    {
        return '/' . static::getRandomNumber();
    }

    /**
     * @throws RandomException
     */
    public static function getRandomNumber(): int
    {
        return random_int(0, PHP_INT_MAX);
    }

    public static function getRandomSite(): \QUI\Interfaces\Projects\Site
    {
        $DefaultProject = static::getDefaultProject();

        $defaultSiteIds = $DefaultProject->getSitesIds(['active' => '1']);

        print_r($defaultSiteIds);

        $randomIndex = random_int(1, count($defaultSiteIds) - 1);

        echo "index: $randomIndex\n";

        $randomSiteId = $defaultSiteIds[$randomIndex]['id'];

        return $DefaultProject->get($randomSiteId);
    }
}
