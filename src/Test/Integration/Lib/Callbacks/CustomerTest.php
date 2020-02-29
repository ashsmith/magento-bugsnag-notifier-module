<?php
namespace Ashsmith\Bugsnag\Test\Integration\Lib\Callbacks;

use Ashsmith\Bugsnag\Lib\Callbacks\Customer;
use Ashsmith\Bugsnag\Lib\Callbacks\Magento;
use Bugsnag\Report;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Exception\SessionException;
use Magento\Framework\Phrase;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

class CustomerTest extends TestCase
{
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        parent::setUp();
    }

    public function testLocalizedExceptionIsNotUnhandled()
    {
        $sessionFactory = $this->getMockBuilder(SessionFactory::class)->disableOriginalConstructor()->getMock();
        $sessionException = new SessionException(
            new Phrase(
                'Area code not set: Area code must be set before starting a session.'
            )
        );
        $sessionFactory->method('create')->willThrowException($sessionException);

        /** @var Magento $magentoCallback */
        $magentoCallback = $this->objectManager->create(Customer::class, ['sessionFactory' => $sessionFactory]);
        $reportMock = $this->getMockBuilder(Report::class)->disableOriginalConstructor()->getMock();
        $reportMock->expects($this->never())->method('setUser')->willReturnSelf();
        $magentoCallback->report($reportMock);
    }
}
