<?php

use PHPUnit\Framework\TestCase;
use QUI\Redirect\TestUtil;

final class DatabaseTest extends TestCase
{
    public function testOnErrorHeaderShow(): void
    {
        $this->assertIsString(\QUI\Redirect\Database::getTableName(TestUtil::getDefaultProject()));
    }
}
