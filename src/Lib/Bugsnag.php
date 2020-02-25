<?php
declare(strict_types=1);
namespace Ashsmith\Bugsnag\Lib;

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
     * @throws \Exception
     */
    public function init(): Client
    {
        if (!$this->client) {
            if (!$this->config->getApiKey()) {
                throw new \Exception('No bugsnag configuration has been provided.');
            }

            $bugsnag = $this->clientFactory->make($this->config->getApiKey(), $this->config->getEndpoint());
            $bugsnag->registerCallback([$this->customerCallback, 'report'])
                ->registerCallback([$this->magentoCallback, 'report'])
                ->setReleaseStage($this->config->getReleaseStage())
                ->startSession();

            // Custom event to allow developers to extend default bugsnag configuration
            $this->eventManager->dispatch('bugsnag_init', ['client' => $bugsnag]);
            $this->client = $bugsnag;
        }

        return $this->client;
    }
}
