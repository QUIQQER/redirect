<?php

namespace QUI\Redirect\Test;

use PHPUnit\Framework\TestCase;
use QUI\Redirect\TemporaryStorage;
use QUI\Redirect\TestUtil;

final class TemporaryStorageTest extends TestCase
{
    public function testSetOldUrls(): void
    {
        $urls = [
            TestUtil::getRandomNumber() => TestUtil::getRandomPath(),
            TestUtil::getRandomNumber() => TestUtil::getRandomPath(),
            TestUtil::getRandomNumber() => TestUtil::getRandomPath(),
            TestUtil::getRandomNumber() => TestUtil::getRandomPath(),
            TestUtil::getRandomNumber() => TestUtil::getRandomPath()
        ];

        TemporaryStorage::setOldUrls($urls);

        $this->assertEquals($urls, TemporaryStorage::getOldUrls());

        TemporaryStorage::setOldUrls([]);

        $this->assertEmpty(TemporaryStorage::getOldUrls());
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

    public function testGetOldUrls(): void
    {
        $this->markTestIncomplete(
            'This test would do the same as testSetOldUrls.
            Therefore it has not been implemented.
            If you know a better way on how to test this, feel free to implement it.'
        );
    }

    public function testGetOldUrlForSiteId(): void
    {
        $siteId = TestUtil::getRandomNumber();
        $path   = TestUtil::getRandomPath();

        $urls = [
            TestUtil::getRandomNumber() => TestUtil::getRandomPath(),
            TestUtil::getRandomNumber() => TestUtil::getRandomPath(),
            $siteId => $path,
            TestUtil::getRandomNumber() => TestUtil::getRandomPath(),
            TestUtil::getRandomNumber() => TestUtil::getRandomPath()
        ];

        TemporaryStorage::setOldUrls($urls);

        $this->assertEquals($path, TemporaryStorage::getOldUrlForSiteId($siteId));

        TemporaryStorage::setOldUrls([]);

        $this->assertEmpty(TemporaryStorage::getOldUrlForSiteId(TestUtil::getRandomNumber()));
    }

    public function testRemoveOldUrlForSiteId(): void
    {
        $siteIdToDelete = TestUtil::getRandomNumber();
        $pathToDelete   = TestUtil::getRandomPath();

        $siteIdToKeep = TestUtil::getRandomNumber();
        $pathToKeep   = TestUtil::getRandomPath();

        $urls = [
            TestUtil::getRandomNumber() => TestUtil::getRandomPath(),
            TestUtil::getRandomNumber() => TestUtil::getRandomPath(),
            $siteIdToDelete => $pathToDelete,
            $siteIdToKeep => $pathToKeep,
            TestUtil::getRandomNumber() => TestUtil::getRandomPath()
        ];

        TemporaryStorage::setOldUrls($urls);
        TemporaryStorage::removeOldUrlForSiteId($siteIdToDelete);

        $this->assertEmpty(TemporaryStorage::getOldUrlForSiteId(TestUtil::getRandomNumber()), 'URL was not removed from temporary storage');
        $this->assertEquals($pathToKeep, TemporaryStorage::getOldUrlForSiteId($siteIdToKeep), 'Other URLs were removed from the temporary storage');

        TemporaryStorage::setOldUrls([]);
    }
}
