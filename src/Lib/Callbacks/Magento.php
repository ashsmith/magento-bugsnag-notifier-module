<?php
declare(strict_types=1);
namespace Ashsmith\Bugsnag\Lib\Callbacks;

use Bugsnag\Report;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

class Magento implements CallbackInterface
{
    private $metadata;
    private $appState;
    private $runMode;
    private $scopeCode;

    public function __construct(
        ProductMetadataInterface $metadata,
        State $appState,
        $runMode = ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        $this->metadata = $metadata;
        $this->appState = $appState;
        $this->runMode = $runMode;
        $this->scopeCode = $scopeCode;
    }

    public function report(Report $report)
    {
        $report->setMetaData([
            'magento' => [
                'version' => $this->metadata->getVersion(),
                'edition' => $this->metadata->getEdition(),
                'area_code' => $this->getAreaCode(),
                'run_mode' => $this->runMode,
                'store_code' => $this->scopeCode,
            ],
        ]);
    }

    private function getAreaCode(): string
    {
        try {
            return $this->appState->getAreaCode();
        } catch (LocalizedException $exception) {
            // The application didn't make it far enough to set the area code!
            return 'not set';
        }
    }
}
