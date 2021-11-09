<?php

namespace QUI\Redirect\Test;

use PHPUnit\Framework\TestCase;
use QUI\Redirect\Url;

final class UrlTest extends TestCase
{
    public function testPrepareSourceUrl(): void
    {
        $this->assertEquals(
            '/',
            Url::prepareSourceUrl('https://example.xy'),
            'Missing path does not return empty string'
        );

        $this->assertEquals(
            '/testPath/testFile.html#testFragment?testQueryA=testValueA&testQueryB=testValueB',
            Url::prepareSourceUrl('https://quiqqer-redirect.de/testPath/testFile.html#testFragment?testQueryA=testValueA&testQueryB=testValueB')
        );

        $this->assertEquals(
            '/testPath/testFile.html?testQueryA=testValueA&testQueryB=testValueB#testFragment',
            Url::prepareSourceUrl('https://quiqqer-redirect.de/testPath/testFile.html?testQueryA=testValueA&testQueryB=testValueB#testFragment')
        );

        $this->assertEquals(
            '/testPath/testFile.html?testQueryA=testValueA&testQueryB=testValueB#testFragment',
            Url::prepareSourceUrl('https://quiqqer-redirect.de/en/testPath/testFile.html?testQueryA=testValueA&testQueryB=testValueB#testFragment')
        );

        $this->assertEquals(
            '/testPath/testFile.html?testQueryA=testValueA&testQueryB=testValueB',
            Url::prepareSourceUrl('https://quiqqer-redirect.de/testPath/testFile.html?testQueryA=testValueA&testQueryB=testValueB')
        );

        $this->assertEquals(
            '/testPath/testFile.html',
            Url::prepareSourceUrl('https://quiqqer-redirect.de/testPath/testFile.html')
        );

        $this->assertEquals(
            '/testPath/testFile.html',
            Url::prepareSourceUrl('https://quiqqer-redirect.de/en/testPath/testFile.html')
        );

        $this->assertEquals(
            '/testPath/testFile.html#testFragment',
            Url::prepareSourceUrl('https://quiqqer-redirect.de/testPath/testFile.html#testFragment')
        );

        $this->assertEquals(
            '/testPath/testFile.html#testFragment',
            Url::prepareSourceUrl('/testPath/testFile.html#testFragment')
        );
    }

    public function testGetRelevantPartsFromUrl(): void
    {
        $this->assertEmpty(
            Url::getRelevantPartsFromUrl('https://example.xy'),
            'Missing path does not return empty string'
        );

        $this->assertEquals(
            '/testPath/testFile.html#testFragment?testQueryA=testValueA&testQueryB=testValueB',
            Url::getRelevantPartsFromUrl('https://quiqqer-redirect.de/testPath/testFile.html#testFragment?testQueryA=testValueA&testQueryB=testValueB')
        );

        $this->assertEquals(
            '/testPath/testFile.html?testQueryA=testValueA&testQueryB=testValueB#testFragment',
            Url::getRelevantPartsFromUrl('https://quiqqer-redirect.de/testPath/testFile.html?testQueryA=testValueA&testQueryB=testValueB#testFragment')
        );

        $this->assertEquals(
            '/testPath/testFile.html?testQueryA=testValueA&testQueryB=testValueB',
            Url::getRelevantPartsFromUrl('https://quiqqer-redirect.de/testPath/testFile.html?testQueryA=testValueA&testQueryB=testValueB')
        );

        $this->assertEquals(
            '/testPath/testFile.html',
            Url::getRelevantPartsFromUrl('https://quiqqer-redirect.de/testPath/testFile.html')
        );

        $this->assertEquals(
            '/testPath/testFile.html#testFragment',
            Url::getRelevantPartsFromUrl('https://quiqqer-redirect.de/testPath/testFile.html#testFragment')
        );

        $this->assertEquals(
            '/testPath/testFile.html#testFragment',
            Url::getRelevantPartsFromUrl('/testPath/testFile.html#testFragment')
        );
    }

    public function testStripLanguageFromPath(): void
    {
        $this->assertEquals(
            '/foo/bar',
            Url::stripLanguageFromPath('/foo/bar')
        );

        $this->assertEquals(
            '/foo/bar',
            Url::stripLanguageFromPath('/en/foo/bar')
        );

        $this->assertEquals(
            '/',
            Url::stripLanguageFromPath('/')
        );
    }

    public function testPrepareInternalTargetUrl(): void
    {
        $this->assertEquals(
            '/',
            Url::prepareSourceUrl('https://example.xy'),
            'Missing path does not return empty string'
        );

        $this->assertEquals(
            '/testPath/testFile.html#testFragment?testQueryA=testValueA&testQueryB=testValueB',
            Url::prepareSourceUrl('https://quiqqer-redirect.de/testPath/testFile.html#testFragment?testQueryA=testValueA&testQueryB=testValueB')
        );

        $this->assertEquals(
            '/testPath/testFile.html?testQueryA=testValueA&testQueryB=testValueB#testFragment',
            Url::prepareSourceUrl('https://quiqqer-redirect.de/testPath/testFile.html?testQueryA=testValueA&testQueryB=testValueB#testFragment')
        );

        $this->assertEquals(
            '/testPath/testFile.html?testQueryA=testValueA&testQueryB=testValueB#testFragment',
            Url::prepareSourceUrl('https://quiqqer-redirect.de/en/testPath/testFile.html?testQueryA=testValueA&testQueryB=testValueB#testFragment')
        );

        $this->assertEquals(
            '/testPath/testFile.html?testQueryA=testValueA&testQueryB=testValueB',
            Url::prepareSourceUrl('https://quiqqer-redirect.de/testPath/testFile.html?testQueryA=testValueA&testQueryB=testValueB')
        );

        $this->assertEquals(
            '/testPath/testFile.html',
            Url::prepareSourceUrl('https://quiqqer-redirect.de/testPath/testFile.html')
        );

        $this->assertEquals(
            '/testPath/testFile.html',
            Url::prepareSourceUrl('https://quiqqer-redirect.de/en/testPath/testFile.html')
        );

        $this->assertEquals(
            '/testPath/testFile.html#testFragment',
            Url::prepareSourceUrl('https://quiqqer-redirect.de/testPath/testFile.html#testFragment')
        );

        $this->assertEquals(
            '/testPath/testFile.html#testFragment',
            Url::prepareSourceUrl('/testPath/testFile.html#testFragment')
        );
    }

    public function testIsInternal(): void
    {
        $this->assertTrue(Url::isInternal('index.php?id=123'));
        $this->assertTrue(Url::isInternal('/index.php?id=123'));
        $this->assertFalse(Url::isInternal('https://foo.bar/index.php?id=123'));
        $this->assertFalse(Url::isInternal('foo.bar/index.php?id=123'));
    }

    public function testGetPath(): void
    {
        $this->assertEquals('/', Url::getPath('https://example.xy'));

        $this->assertEquals(
            '/testPath/testFile.html',
            Url::getPath('https://quiqqer-redirect.de/testPath/testFile.html#testFragment?testQueryA=testValueA&testQueryB=testValueB')
        );

        $this->assertEquals(
            '/testPath/testFile.html',
            Url::getPath('https://quiqqer-redirect.de/testPath/testFile.html?testQueryA=testValueA&testQueryB=testValueB#testFragment')
        );

        $this->assertEquals(
            '/testPath/testFile.html',
            Url::getPath('https://quiqqer-redirect.de/testPath/testFile.html?testQueryA=testValueA&testQueryB=testValueB')
        );

        $this->assertEquals(
            '/testPath/testFile.html',
            Url::getPath('https://quiqqer-redirect.de/testPath/testFile.html')
        );

        $this->assertEquals(
            '/testPath/testFile.html',
            Url::getPath('https://quiqqer-redirect.de/testPath/testFile.html#testFragment')
        );

        $this->assertEquals(
            '/testPath/testFile.html',
            Url::getPath('/testPath/testFile.html#testFragment')
        );
    }

    public function testGetQueryString(): void
    {
        $this->assertNull(Url::getQueryString('https://example.xy'));

        $this->assertNull(
            Url::getQueryString('https://quiqqer-redirect.de/testPath/testFile.html#testFragment?testQueryA=testValueA&testQueryB=testValueB'),
            'Query after fragment is not allowed (apparently), so it should not be returned'
        );

        $this->assertEquals(
            'testQueryA=testValueA&testQueryB=testValueB',
            Url::getQueryString('https://quiqqer-redirect.de/testPath/testFile.html?testQueryA=testValueA&testQueryB=testValueB#testFragment')
        );

        $this->assertEquals(
            'testQueryA=testValueA&testQueryB=testValueB',
            Url::getQueryString('https://quiqqer-redirect.de/testPath/testFile.html?testQueryA=testValueA&testQueryB=testValueB')
        );

        $this->assertNull(Url::getQueryString('https://quiqqer-redirect.de/testPath/testFile.html'));

        $this->assertNull(Url::getQueryString('https://quiqqer-redirect.de/testPath/testFile.html#testFragment'));

        $this->assertNull(Url::getQueryString('/testPath/testFile.html#testFragment'));
    }
}
