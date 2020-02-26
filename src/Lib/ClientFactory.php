<?php
declare(strict_types=1);
namespace Ashsmith\Bugsnag\Lib;

/**
 * Lightweight wrapper around \Bugsnag\Client,
 * mostly for easy mocking in tests.
 */
class ClientFactory
{
    /**
     * Makes an instance of the Bugsnag client, configured with api key and notification endpoint.
     *
     * @param string $apiKey
     * @param string|null $endpoint
     * @return \Bugsnag\Client
     */
    public function make(string $apiKey, string $endpoint = null): \Bugsnag\Client
    {
        return \Bugsnag\Client::make($apiKey, $endpoint);
    }
}
