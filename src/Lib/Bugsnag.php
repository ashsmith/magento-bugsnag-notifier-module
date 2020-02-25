<?php
declare(strict_types=1);
namespace Ashsmith\Bugsnag\Lib;

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
            if (!$this->getApiKey()) {
                throw new \Exception('No bugsnag configuration has been provided.');
            }

            $bugsnag = $this->clientFactory->make($this->getApiKey(), $this->getEndpoint());
            $bugsnag->registerCallback([$this->customerCallback, 'report'])
                ->registerCallback([$this->magentoCallback, 'report'])
                ->setReleaseStage($this->getReleaseStage())
                ->startSession();

            // Custom event to allow developers to extend default bugsnag configuration
            $this->eventManager->dispatch('bugsnag_init', ['client' => $bugsnag]);
            $this->client = $bugsnag;
        }

        return $this->client;
    }

    private function getApiKey(): ?string
    {
        if ($this->deploymentConfig->get(self::CONFIG_PATH_API_KEY)) {
            return $this->deploymentConfig->get(self::CONFIG_PATH_API_KEY);
        }

        return getenv('BUGSNAG_API_KEY') ?: null;
    }

    private function getEndpoint(): ?string
    {
        if ($this->deploymentConfig->get(self::CONFIG_PATH_ENDPOINT)) {
            return $this->deploymentConfig->get(self::CONFIG_PATH_ENDPOINT);
        }

        return getenv('BUGSNAG_ENDPOINT') ?: null;
    }

    private function getReleaseStage()
    {
        // Default to setting the release stage to either production or developer
        $mageMode = getenv('MAGE_MODE') ?? $this->deploymentConfig->get('MAGE_MODE') ?? 'developer';
        $stage = $mageMode == 'developer' ? 'developer' : 'production';

        if ($this->deploymentConfig->get(self::CONFIG_PATH_RELEASE_STAGE)) {
            return $this->deploymentConfig->get(self::CONFIG_PATH_RELEASE_STAGE);
        }

        return getenv('BUGSNAG_RELEASE_STAGE') ?: $stage;
    }
}
