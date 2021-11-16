<?php

namespace QUI\Redirect;

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
}
