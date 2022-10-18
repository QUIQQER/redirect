<?php

namespace QUI\Redirect;

use function count;
use function random_int;

class TestUtil
{
    public static function getDefaultProject(): ?\QUI\Projects\Project
    {
        return \QUI::getProjectManager()->getStandard();
    }

    public static function getRandomPath(): string
    {
        return '/' . static::getRandomNumber();
    }

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
