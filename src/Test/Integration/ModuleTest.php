<?php
declare(strict_types=1);
namespace Ashsmith\Bugsnag\Test\Integration;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Module\ModuleList;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class ModuleTest extends TestCase
{
    public function testModuleIsRegistered()
    {
        $registrar = new ComponentRegistrar();
        $paths = $registrar->getPaths(ComponentRegistrar::MODULE);
        $this->assertArrayHasKey('Ashsmith_Bugsnag', $paths);
    }

    public function testTheModuleIsKnownAndEnabled()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var ModuleList $moduleList */
        $moduleList = $objectManager->create(ModuleList::class);
        $this->assertTrue(
            $moduleList->has('Ashsmith_Bugsnag'),
            'The module Ashsmith_Bugsnag is not enabled'
        );
    }
}
