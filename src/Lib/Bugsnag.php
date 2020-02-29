<?php
declare(strict_types=1);
namespace Ashsmith\Bugsnag\Lib;

use Ashsmith\Bugsnag\Exception\InvalidConfiguration;
use Magento\Framework\Event\Manager as EventManager;
use Bugsnag\Client;

class Bugsnag
{
    private $clientFactory;
    private $config;
    private $eventManager;
    private $customerCallback;
    private $magentoCallback;

    /** @var \Bugsnag\Client */
    private $client;

    public function __construct(
        ClientFactory $clientFactory,
        Config $config,
        EventManager $eventManager,
        Callbacks\Customer $customerCallback,
        Callbacks\Magento $magentoCallback
    ) {
        $this->clientFactory = $clientFactory;
        $this->config = $config;
        $this->eventManager = $eventManager;
        $this->customerCallback = $customerCallback;
        $this->magentoCallback = $magentoCallback;
    }

    /**
     * @return \Bugsnag\Client
     * @throws \Ashsmith\Bugsnag\Exception\InvalidConfiguration
     */
    public function init(): Client
    {
        if (!$this->client) {
            if (!$this->config->getApiKey()) {
                throw new InvalidConfiguration('No bugsnag configuration has been provided.');
            }

            $bugsnag = $this->clientFactory->make($this->config->getApiKey(), $this->config->getEndpoint());
            $bugsnag->setReleaseStage($this->config->getReleaseStage());

            // Sending sessions to bugsnag requires their Standard plan.
            if ($this->config->canSendSessions()) {
                $bugsnag->startSession();
            }

            // Custom event to allow developers to extend default bugsnag configuration
            $this->eventManager->dispatch('bugsnag_init', ['client' => $bugsnag]);
            $this->client = $bugsnag;
        }

        return $this->client;
    }

    public function registerCallbacks($isHttpRequest = false)
    {
        $this->client->registerCallback([$this->magentoCallback, 'report']);
        if ($isHttpRequest) {
            $this->client->registerCallback([$this->customerCallback, 'report']);
        }
    }
}
