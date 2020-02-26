<?php
declare(strict_types=1);
namespace Ashsmith\Bugsnag\Test\Integration\Lib;

use Magento\Framework\App\DeploymentConfig;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Ashsmith\Bugsnag\Lib\Config;

class ConfigTest extends TestCase
{
    private $objectManager;
    private $deploymentConfigMock;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        parent::setUp();
    }

    public function testGetApiKeyFromEnvVar()
    {
        $_ENV['BUGSNAG_API_KEY'] = '123';
        putenv('BUGSNAG_API_KEY=123');

        $this->deploymentConfigMock->expects($this->any())
            ->method('get')
            ->with(Config::CONFIG_PATH_API_KEY)
            ->willReturn(false);
        /** @var Config $config */
        $config = $this->objectManager->create(Config::class, ['deploymentConfig' => $this->deploymentConfigMock]);
        $this->assertEquals('123', $config->getApiKey());
    }

    public function testGetApiKeyFromConfig()
    {
        $this->deploymentConfigMock->method('get')->with(Config::CONFIG_PATH_API_KEY)->willReturn('1234');
        /** @var Config $config */
        $config = $this->objectManager->create(Config::class, ['deploymentConfig' => $this->deploymentConfigMock]);
        $this->assertEquals('1234', $config->getApiKey());
    }

    public function testGetApiKeyReturnsNullWhenUndefined()
    {
        $this->deploymentConfigMock->method('get')->with(Config::CONFIG_PATH_API_KEY)->willReturn(false);
        /** @var Config $config */
        $config = $this->objectManager->create(Config::class, ['deploymentConfig' => $this->deploymentConfigMock]);
        $this->assertNull($config->getApiKey());
    }

    public function testGetEndpointFromEnvVar()
    {
        $_ENV['BUGSNAG_ENDPOINT'] = 'some_endpoint';
        putenv('BUGSNAG_ENDPOINT=some_endpoint');

        $this->deploymentConfigMock->expects($this->any())
            ->method('get')
            ->with(Config::CONFIG_PATH_ENDPOINT)
            ->willReturn(false);
        /** @var Config $config */
        $config = $this->objectManager->create(Config::class, ['deploymentConfig' => $this->deploymentConfigMock]);
        $this->assertEquals('some_endpoint', $config->getEndpoint());
    }

    public function testGetEndpointFromConfig()
    {
        $this->deploymentConfigMock->method('get')->with(Config::CONFIG_PATH_ENDPOINT)->willReturn('endpoint');
        /** @var Config $config */
        $config = $this->objectManager->create(Config::class, ['deploymentConfig' => $this->deploymentConfigMock]);
        $this->assertEquals('endpoint', $config->getEndpoint());
    }

    public function testGetEndpointReturnsNullWhenUndefined()
    {
        $this->deploymentConfigMock->method('get')->with(Config::CONFIG_PATH_ENDPOINT)->willReturn(false);
        /** @var Config $config */
        $config = $this->objectManager->create(Config::class, ['deploymentConfig' => $this->deploymentConfigMock]);
        $this->assertNull($config->getEndpoint());
    }

    public function testGetReleaseStageFromEnvVar()
    {
        $_ENV['BUGSNAG_RELEASE_STAGE'] = 'tests';
        putenv('BUGSNAG_RELEASE_STAGE=tests');

        $this->deploymentConfigMock->expects($this->any())
            ->method('get')
            ->with(Config::CONFIG_PATH_RELEASE_STAGE)
            ->willReturn(false);
        /** @var Config $config */
        $config = $this->objectManager->create(Config::class, ['deploymentConfig' => $this->deploymentConfigMock]);
        $this->assertEquals('tests', $config->getReleaseStage());
    }

    public function testGetReleaseStageFromConfig()
    {
        $this->deploymentConfigMock->method('get')->with(Config::CONFIG_PATH_RELEASE_STAGE)->willReturn('endpoint');
        /** @var Config $config */
        $config = $this->objectManager->create(Config::class, ['deploymentConfig' => $this->deploymentConfigMock]);
        $this->assertEquals('endpoint', $config->getReleaseStage());
    }

    public function testGetReleaseStageReturnsDeveloperWhenNotSet()
    {
        $this->deploymentConfigMock->method('get')->with(Config::CONFIG_PATH_RELEASE_STAGE)->willReturn(false);
        /** @var Config $config */
        $config = $this->objectManager->create(Config::class, ['deploymentConfig' => $this->deploymentConfigMock]);
        $this->assertEquals('developer', $config->getReleaseStage());
    }

    protected function tearDown()
    {
        unset($_ENV['BUGSNAG_API_KEY'], $_ENV['BUGSNAG_ENDPOINT'], $_ENV['BUGSNAG_RELEASE_STAGE']);
        putenv('BUGSNAG_API_KEY');
        putenv('BUGSNAG_ENDPOINT');
        putenv('BUGSNAG_RELEASE_STAGE');

        parent::tearDown();
    }
}
