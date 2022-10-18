<?php

namespace QUI\Redirect\Test;

use PHPUnit\Framework\TestCase;
use QUI\Redirect\TemporaryStorage;
use QUI\Redirect\TestUtil;
use QUI\Redirect\Url;

final class TemporaryStorageTest extends TestCase
{
    public function testStoreAndGetUrl(): void
    {
        $RandomSite = TestUtil::getRandomSite();

        $randomUrl = Url::prepareSourceUrl($RandomSite->getUrlRewritten());

        TemporaryStorage::storeUrl($RandomSite);

        $this->assertEquals($randomUrl, TemporaryStorage::getUrl($RandomSite));

        TemporaryStorage::removeUrl($RandomSite);
    }

    public function testSetOldUrlsRecursivelyFromSite(): void
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.
            It is very cumbersome to test with QUIQQER sites.
            There are no mocking objects yet so you would have to create and delete all sites.
            This test should be implemented later when mocking objects are available.'
        );
    }

    public function testRemoveUrl(): void
    {
        $RandomSite = TestUtil::getRandomSite();

        TemporaryStorage::storeUrl($RandomSite);
        TemporaryStorage::removeUrl($RandomSite);

        $this->assertEmpty(TemporaryStorage::getUrl($RandomSite));
    }
}
