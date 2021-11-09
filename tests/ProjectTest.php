<?php

namespace QUI\Redirect\Test;

use PHPUnit\Framework\TestCase;
use QUI\Redirect\TestUtil;

final class ProjectTest extends TestCase
{
    public function testGetFromParametersValid(): void
    {
        $StandardProject = TestUtil::getDefaultProject();
        $name            = $StandardProject->getName();
        $language        = $StandardProject->getLang();

        $this->assertEquals($StandardProject, \QUI\Redirect\Project::getFromParameters($name, $language));
    }

    public function testGetFromParametersInvalidName(): void
    {
        $this->assertFalse(\QUI\Redirect\Project::getFromParameters(TestUtil::getRandomNumber(), TestUtil::getRandomNumber()));
    }

    public function testGetFromParametersNoName(): void
    {
        $this->assertEquals(
            \QUI::getRewrite()->getProject(),
            \QUI\Redirect\Project::getFromParameters('', TestUtil::getRandomNumber())
        );
    }
}
