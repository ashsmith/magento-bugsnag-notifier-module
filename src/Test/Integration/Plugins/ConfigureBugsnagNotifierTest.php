<?php
declare(strict_types=1);
namespace Ashsmith\Bugsnag\Test\Integration\Plugins;

use Ashsmith\Bugsnag\Plugins\ConfigureBugsnagNotifier;
use Magento\Framework\AppInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Interception\PluginList;
use PHPUnit\Framework\TestCase;

class ConfigureBugsnagNotifierTest extends TestCase
{
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        parent::setUp();
    }

    public function testBeforeCatchExceptionIsRegistered()
    {
        /** @var PluginList $pluginList */
        $pluginList = $this->objectManager->create(PluginList::class);
        $pluginInfo = $pluginList->get(AppInterface::class);
        $this->assertSame(ConfigureBugsnagNotifier::class, $pluginInfo['bugsnag_setup']['instance']);
    }
}
