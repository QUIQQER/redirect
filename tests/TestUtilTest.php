<?php

namespace QUI\Redirect\Test;

use PHPUnit\Framework\TestCase;
use QUI\Redirect\TemporaryStorage;
use QUI\Redirect\TestUtil;

final class TestUtilTest extends TestCase
{
    public function testGetDefaultProject(): void
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.
            I do not know how to test this properly.
            '
        );
    }

    public function testGetRandomPath(): void
    {
        $pathA = TestUtil::getRandomPath();
        $pathB = TestUtil::getRandomPath();

        $this->assertStringStartsWith('/', $pathA);
        $this->assertStringStartsWith('/', $pathB);

        $this->assertNotEquals($pathA, $pathB);
    }

    public function testGetRandomNumber(): void
    {
        $numberA = TestUtil::getRandomNumber();
        $numberB = TestUtil::getRandomNumber();

        $this->assertIsNumeric($numberA);
        $this->assertIsNumeric($numberB);

        $this->assertNotEquals($numberA, $numberB);
    }
}
