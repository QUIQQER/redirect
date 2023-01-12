<?php

namespace QUI\Redirect\Test;

use PHPUnit\Framework\TestCase;
use QUI\Redirect\TemporaryStorage;
use QUI\Redirect\TestUtil;
use QUI\Redirect\Url;
use QUI\Redirect\Utils;

final class UtilsTest extends TestCase
{
    public function testMakeChildrenArrayForAddRedirectDialog(): void
    {
        $parentSourceUrl = '/foo';
        $parentTargetUrl = '/bar';

        $childrenUrls = [
            '/foo/a',
            '/foo/b/c',
            '/foo/b/c/foo/d',
        ];

        $childrenArray = \QUI\Redirect\Utils::makeChildrenArrayForAddRedirectDialog(
            $childrenUrls,
            $parentSourceUrl,
            $parentTargetUrl
        );

        $this->assertCount(3, $childrenArray);

        $this->assertContains([
            'source' =>  '/foo/a',
            'target' =>  '/bar/a'
        ], $childrenArray);
        $this->assertContains([
            'source' =>  '/foo/b/c',
            'target' =>  '/bar/b/c'
        ], $childrenArray);
        $this->assertContains([
            'source' =>  '/foo/b/c/foo/d',
            'target' =>  '/bar/b/c/foo/d'
        ], $childrenArray);
    }

    public function testGenerateChildSourceUrlFromParentRedirectUrls(): void
    {
        $this->assertEquals(
            '/hello/world',
            Utils::generateChildSourceUrlFromParentRedirectUrls('/hi/world', '/hello', '/hi')
        );

        $this->assertEquals(
            '/hello/world/test',
            Utils::generateChildSourceUrlFromParentRedirectUrls('/hi/world/test', '/hello/world', '/hi/world')
        );

        $this->assertEquals(
            '/hello/world/test/123',
            Utils::generateChildSourceUrlFromParentRedirectUrls('/hi/world/test/123', '/hello/world/test', '/hi/world/test')
        );

        $this->assertEquals(
            '/hello/hello',
            Utils::generateChildSourceUrlFromParentRedirectUrls('/hi/hello', '/hello', '/hi')
        );

        $this->assertEquals(
            '/hi/hello',
            Utils::generateChildSourceUrlFromParentRedirectUrls('/hello/hello', '/hi', '/hello')
        );

        $this->assertEquals(
            '/hello/world/test',
            Utils::generateChildSourceUrlFromParentRedirectUrls('/world/test', '/hello/world', '/world')
        );

        $this->assertEquals(
            '/hello/world',
            Utils::generateChildSourceUrlFromParentRedirectUrls('/hi/world', '/hello', '/hi')
        );
    }
}
