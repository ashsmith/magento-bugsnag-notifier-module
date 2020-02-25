<?php
declare(strict_types=1);
namespace Ashsmith\Bugsnag\Plugins;

use Ashsmith\Bugsnag\Lib\Bugsnag;
use Bugsnag\Report;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Http;
use Magento\Framework\App\StaticResource;
use Magento\Framework\AppInterface;
use Magento\MediaStorage\App\Media;

class ConfigureBugsnagNotifier
{
    /** @var \Ashsmith\Bugsnag\Lib\Bugsnag */
    private $bugsnag;

    /**
     * @param \Ashsmith\Bugsnag\Lib\Bugsnag $bugsnag
     */
    public function __construct(Bugsnag $bugsnag) {
        $this->bugsnag = $bugsnag;
    }

    /**
     * @param \Magento\Framework\AppInterface $instance
     * @throws \Exception
     */
    public function beforeLaunch(AppInterface $instance)
    {
        // Cron handles exceptions and saves them against the cron_schedule table and prints out to log files
        // currently there is no way of accessing the exception to be tracked in Bugsnag.
        if ($instance instanceof \Magento\Framework\App\Cron) {
            return;
        }
        $client = $this->bugsnag->init();
        $client->setMetaData([
            'app' => [
                'request_type' => $this->getRequestType($instance),
            ],
        ]);
    }

    /**
     * @param \Magento\Framework\AppInterface $instance
     * @param \Magento\Framework\App\Bootstrap $bootstrap
     * @param \Exception $exception
     * @throws \Exception
     */
    public function beforeCatchException(AppInterface $instance, Bootstrap $bootstrap, \Exception $exception)
    {
        $client = $this->bugsnag->init();
        $report = Report::fromPHPThrowable($client->getConfig(), $exception);
        $report->setSeverity('error');
        $report->setUnhandled(true);
        $report->setSeverityReason(['type' => 'unhandledException']);
        $client->notify($report);
    }

    private function getRequestType(AppInterface $app)
    {
        switch (true) {
            case $app instanceof Http:
                return 'http';
            case $app instanceof Media:
                return 'media';
            case $app instanceof StaticResource:
                return 'static_resource';
            default:
                return get_class($app);
        }
    }
}
