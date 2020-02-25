<?php
declare(strict_types=1);
namespace Ashsmith\Bugsnag\Lib\Callbacks;

use Bugsnag\Report;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class Magento implements CallbackInterface
{
    private $metadata;
    private $appState;
    private $storeManager;

    public function __construct(
        ProductMetadataInterface $metadata,
        State $appState,
        StoreManagerInterface $storeManager
    ) {
        $this->metadata = $metadata;
        $this->appState = $appState;
        $this->storeManager = $storeManager;
    }

    public function report(Report $report)
    {
        $report->setMetaData([
            'magento' => [
                'version' => $this->metadata->getVersion(),
                'edition' => $this->metadata->getEdition(),
                'area_code' => $this->getAreaCode(),
                'store_code' => $this->getStoreCode(),
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

    private function getStoreCode(): string
    {
        try {
            return $this->storeManager->getStore()->getCode();
        } catch (NoSuchEntityException $exception) {
            return 'no such store found';
        }
    }
}
