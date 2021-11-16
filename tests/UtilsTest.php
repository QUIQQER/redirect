<?php

namespace QUI\Redirect\Test;

use PHPUnit\Framework\TestCase;
use QUI\Redirect\TemporaryStorage;
use QUI\Redirect\TestUtil;
use QUI\Redirect\Url;

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
}
