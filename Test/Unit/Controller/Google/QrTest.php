<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Test\Unit\Controller\Google;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultFactory;
use Magetarian\CustomerTwoFactorAuth\Model\Provider\Engine\Google;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magetarian\CustomerTwoFactorAuth\Controller\Google\Qr;

/**
 * Class QrTest
 * Test for QrTest class
 */
class QrTest extends TestCase
{

    /** @var Qr object */
    private $object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $google;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $customerRepository;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $customerSession;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $resultFactory;

    /**
     * @dataProvider dataProviderExecute
     */
    public function testExecute(?int $customerId)
    {
        $resultRaw  = $this->getMockBuilder(Raw::class)
                           ->disableOriginalConstructor()
                           ->getMock();
        $customer  = $this->getMockBuilder(CustomerInterface::class)
                           ->disableOriginalConstructor()
                           ->getMock();

        $this->customerSession->expects($this->atLeastOnce())->method('getCustomerId')->willReturn($customerId);
        if (!$customerId) {
            $this->customerSession->expects($this->atLeastOnce())->method('getTwoFaCustomerId')
                                                                 ->willReturn(1);
        } else {
            $this->customerSession->expects($this->never())->method('getTwoFaCustomerId');
        }
        $this->customerRepository->expects($this->atLeastOnce())->method('getById')->willReturn($customer);
        $resultRaw->expects($this->atLeastOnce())->method('setHttpResponseCode')->willReturn($resultRaw);
        $resultRaw->expects($this->atLeastOnce())->method('setHeader')->willReturn($resultRaw);
        $resultRaw->expects($this->atLeastOnce())->method('setContents')->willReturn($resultRaw);
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($resultRaw);
         $this->assertEquals($resultRaw, $this->object->execute());
    }

    /**
     * @return array
     */
    public function dataProviderExecute(): array
    {
        return [
            [1],
            [null],
        ];
    }

    /**
     *
     */
    protected function setUp(): void
    {
        $this->context = $this->getMockBuilder(Context::class)
                              ->disableOriginalConstructor()
                              ->getMock();
        $this->resultFactory = $this->getMockBuilder(ResultFactory::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->google = $this->getMockBuilder(Google::class)
                              ->disableOriginalConstructor()
                              ->getMock();
        $this->customerRepository = $this->getMockBuilder(CustomerRepositoryInterface::class)
                              ->disableOriginalConstructor()
                              ->getMock();
        $this->customerSession = $this->getMockBuilder(Session::class)
                                      ->disableOriginalConstructor()
                                      ->setMethods(['getTwoFaCustomerId', 'getCustomerId'])
                                      ->getMockForAbstractClass();
        $this->context->expects($this->any())->method('getResultFactory')
                      ->willReturn($this->resultFactory);

        $this->object = (new ObjectManager($this))->getObject(
            Qr::class,
            [
                'context' => $this->context,
                'google' => $this->google,
                'customerRepository' => $this->customerRepository,
                'customerSession' => $this->customerSession,
            ]
        );
    }
}
