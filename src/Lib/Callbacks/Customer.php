<?php
declare(strict_types=1);
namespace Ashsmith\Bugsnag\Lib\Callbacks;

use Bugsnag\Report;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Manager as EventManager;

class Customer implements CallbackInterface
{
    private $sessionFactory;
    private $customerGroupRepository;
    private $eventManager;

    public function __construct(
        SessionFactory $sessionFactory,
        GroupRepositoryInterface $customerGroupRepository,
        EventManager $eventManager
    ) {
        $this->sessionFactory = $sessionFactory;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->eventManager = $eventManager;
    }

    /**
     * @param \Bugsnag\Report $report
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function report(Report $report)
    {
        /** @var \Magento\Customer\Model\Session $session */
        if (!($session = $this->getSession())) {
            // If a session cannot be retrieved then dont report any user data.
            return;
        }

        // Create data object so when the event is dispatched developers can modify the object instance
        $data = new DataObject([
            'id' => $session->getSessionId(),
            'is_logged_in' => $session->isLoggedIn(),
        ]);

        // Sensible defaults for logged in customers.
        if ($session->isLoggedIn()) {
            $group = $this->customerGroupRepository->getById($session->getCustomerGroupId());
            $data->setData([
                'id' => $session->getCustomerId(),
                'customer_group' => $group->getCode(),
            ]);
        }

        // Integration point to add more detailed customer information
        $this->eventManager->dispatch('bugsnag_add_customer_data', ['data' => $data]);
        $report->setUser($data->toArray());
    }

    private function getSession(): ?Session
    {
        try {
            return $this->sessionFactory->create();
        } catch (\Magento\Framework\Exception\SessionException $sessionException) {
            // Exception is thrown when the app area code hasn't been set.
            return null;
        }
    }
}
