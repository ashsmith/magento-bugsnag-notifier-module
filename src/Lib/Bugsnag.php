<?php
declare(strict_types=1);
namespace Ashsmith\Bugsnag\Lib;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Event\Manager as EventManager;
use Bugsnag\Client;

class Bugsnag
{
    const CONFIG_PATH_API_KEY = 'bugsnag/api_key';
    const CONFIG_PATH_ENDPOINT = 'bugsnag/endpoint';
    const CONFIG_PATH_RELEASE_STAGE = 'bugsnag/release_stage';

    private $clientFactory;
    private $deploymentConfig;
    private $eventManager;
    private $customerCallback;
    private $magentoCallback;

    /** @var \Bugsnag\Client */
    private $client;

    public function __construct(
        ClientFactory $clientFactory,
        DeploymentConfig $deploymentConfig,
        EventManager $eventManager,
        Callbacks\Customer $customerCallback,
        Callbacks\Magento $magentoCallback
    ) {
        $this->clientFactory = $clientFactory;
        $this->deploymentConfig = $deploymentConfig;
        $this->eventManager = $eventManager;
        $this->customerCallback = $customerCallback;
        $this->magentoCallback = $magentoCallback;
    }

    /**
     * @return \Bugsnag\Client
     * @throws \Exception
     */
    public function init(): Client
    {
        if (!$this->client) {
            $apiKey = $this->deploymentConfig->get(self::CONFIG_PATH_API_KEY) ?? getenv('BUGSNAG_API_KEY');
            $endpoint = $this->deploymentConfig->get(self::CONFIG_PATH_ENDPOINT) ?? getenv('BUGSNAG_ENDPOINT');
            if (!$apiKey) {
                throw new \Exception('No bugsnag configuration has been provided.');
            }

            $bugsnag = $this->clientFactory->make($apiKey, $endpoint);
            $this->setReleaseStage($bugsnag);

            $bugsnag->registerCallback([$this->customerCallback, 'report'])
                ->registerCallback([$this->magentoCallback, 'report'])
                ->startSession();

            // Custom event to allow developers to extend default bugsnag configuration
            $this->eventManager->dispatch('bugsnag_init', ['client' => $bugsnag]);
            $this->client = $bugsnag;
        }

        return $this->client;
    }

    private function setReleaseStage(Client $client)
    {
        // Default to setting the release stage to either production or developer
        $mageMode = getenv('MAGE_MODE') ?? $this->deploymentConfig->get('MAGE_MODE') ?? 'developer';
        $client->setReleaseStage($mageMode == 'developer' ? 'developer' : 'production');

        // If config or env var was provided, use that instead.
        $releaseStage = $this->deploymentConfig->get(self::CONFIG_PATH_RELEASE_STAGE) ?? getenv('BUGSNAG_RELEASE_STAGE');
        if ($releaseStage) {
            $client->setReleaseStage($releaseStage);
        }
    }
}
