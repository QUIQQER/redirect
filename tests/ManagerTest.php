<?php

use PHPUnit\Framework\TestCase;
use QUI\Redirect\Database;
use QUI\Redirect\Manager;
use QUI\Redirect\TestUtil;

final class ManagerTest extends TestCase
{
    public function testAttemptRedirect(): void
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testGetRedirectForUrlExisting(): void
    {
        $url    = TestUtil::getRandomPath();
        $target = TestUtil::getRandomPath();

        $Project = TestUtil::getDefaultProject();

        Manager::addRedirect($url, $target, $Project);

        $this->assertEquals(Manager::getRedirectForUrl($url, $Project), $target);

        Manager::deleteRedirect($url, $Project);
    }

    public function testGetRedirectForUrlNonExisting(): void
    {
        $this->assertFalse(Manager::getRedirectForUrl(
            TestUtil::getRandomPath(),
            TestUtil::getDefaultProject()
        ));
    }

    public function testAddRedirect(): void
    {
        $source  = TestUtil::getRandomPath();
        $target  = TestUtil::getRandomPath();
        $Project = TestUtil::getDefaultProject();

        Manager::addRedirect($source, $target, $Project);

        $this->assertEquals($target, Manager::getRedirectForUrl($source, $Project));

        Manager::deleteRedirect($source, $Project);
    }

    public function testCheckLicense(): void
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testGetRedirectsContainsExistingRedirect(): void
    {
        $source  = TestUtil::getRandomPath();
        $target  = TestUtil::getRandomPath();
        $Project = TestUtil::getDefaultProject();

        Manager::addRedirect($source, $target, $Project);

        $this->assertContains([
            Database::COLUMN_SOURCE_URL => $source,
            Database::COLUMN_TARGET_URL => $target
        ], Manager::getRedirects($Project));

        Manager::deleteRedirect($source, $Project);
    }

    public function testGetRedirectsDoesNotContainsNonExistingRedirect(): void
    {
        $this->assertNotContains([
            Database::COLUMN_SOURCE_URL => TestUtil::getRandomPath(),
            Database::COLUMN_TARGET_URL => TestUtil::getRandomPath()
        ], Manager::getRedirects(TestUtil::getDefaultProject()));
    }

    public function testGetRedirectCount(): void
    {
        $source  = TestUtil::getRandomPath();
        $target  = TestUtil::getRandomPath();
        $Project = TestUtil::getDefaultProject();

        $before = Manager::getRedirectCount($Project);
        $after = Manager::getRedirectCount($Project);

        $this->assertEquals($before, $after, 'Redirect count changed when it should not.');

        Manager::addRedirect($source, $target, $Project);

        $after = Manager::getRedirectCount($Project);

        $this->assertGreaterThan($before, $after, 'Redirect count did not increase when adding redirect.');

        $before = $after;
        Manager::deleteRedirect($source, $Project);

        $after = Manager::getRedirectCount($Project);

        $this->assertLessThan($before, $after, 'Redirect count did not decrease when removing redirect.');
    }

    public function testDeleteRedirect(): void
    {
        $source  = TestUtil::getRandomPath();
        $target  = TestUtil::getRandomPath();
        $Project = TestUtil::getDefaultProject();

        Manager::addRedirect($source, $target, $Project);
        Manager::deleteRedirect($source, $Project);

        $this->assertFalse(Manager::getRedirectForUrl($source, $Project));
    }
}
