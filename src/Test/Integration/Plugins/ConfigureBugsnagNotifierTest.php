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
    public function testBeforeCatchExceptionIsRegistered()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var PluginList $pluginList */
        $pluginList = $objectManager->create(PluginList::class);
        $pluginInfo = $pluginList->get(AppInterface::class);
        $this->assertSame(ConfigureBugsnagNotifier::class, $pluginInfo['bugsnag_setup']['instance']);
    }
}
