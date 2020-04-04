<?php
declare(strict_types=1);
namespace Ashsmith\Bugsnag\Lib;

use Magento\Framework\App\DeploymentConfig;

class Config
{
    const CONFIG_PATH_ENABLED = 'bugsnag/enabled';
    const CONFIG_PATH_API_KEY = 'bugsnag/api_key';
    const CONFIG_PATH_ENDPOINT = 'bugsnag/endpoint';
    const CONFIG_PATH_RELEASE_STAGE = 'bugsnag/release_stage';
    const CONFIG_PATH_SESSION_TRACKING = 'bugsnag/session_tracking';

    private $deploymentConfig;

    public function __construct(DeploymentConfig $deploymentConfig)
    {
        $this->deploymentConfig = $deploymentConfig;
    }

    public function isEnabled(): bool
    {
        if ($this->deploymentConfig->get(self::CONFIG_PATH_ENABLED)) {
            return (bool) $this->deploymentConfig->get(self::CONFIG_PATH_ENABLED);
        }

        return (bool) getenv('BUGSNAG_ENABLED') ?: false;
    }

    public function getApiKey(): ?string
    {
        if ($this->deploymentConfig->get(self::CONFIG_PATH_API_KEY)) {
            return $this->deploymentConfig->get(self::CONFIG_PATH_API_KEY);
        }

        return getenv('BUGSNAG_API_KEY') ?: null;
    }

    public function getEndpoint(): ?string
    {
        if ($this->deploymentConfig->get(self::CONFIG_PATH_ENDPOINT)) {
            return $this->deploymentConfig->get(self::CONFIG_PATH_ENDPOINT);
        }

        return getenv('BUGSNAG_ENDPOINT') ?: null;
    }

    public function getReleaseStage(): ?string
    {
        // Default to setting the release stage to either production or developer
        $mageMode = getenv('MAGE_MODE') ?? $this->deploymentConfig->get('MAGE_MODE') ?? 'developer';
        $stage = $mageMode == 'developer' ? 'developer' : 'production';

        if ($this->deploymentConfig->get(self::CONFIG_PATH_RELEASE_STAGE)) {
            return $this->deploymentConfig->get(self::CONFIG_PATH_RELEASE_STAGE);
        }

        return getenv('BUGSNAG_RELEASE_STAGE') ?: $stage;
    }

    public function canSendSessions(): bool
    {
        if ($this->deploymentConfig->get(self::CONFIG_PATH_SESSION_TRACKING)) {
            return $this->deploymentConfig->get(self::CONFIG_PATH_SESSION_TRACKING) == 'enabled';
        }

        return getenv('BUGSNAG_SESSION_TRACKING')  == 'enabled';
    }
}
