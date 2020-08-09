<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Test\Unit\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderPoolInterface;
use Magetarian\CustomerTwoFactorAuth\Model\Config\ConfigProvider;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magetarian\CustomerTwoFactorAuth\Model\CustomerProvidersManager;
use Magento\Framework\Api\AttributeInterface;

/**
 * Class CustomerProvidersManagerTest
 * Test for CustomerProvidersManager class
 */
class CustomerProvidersManagerTest extends TestCase
{

    /** @var CustomerProvidersManager object */
    private $object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $customerRepository;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $configProvider;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $providerPool;

    /**
     *
     */
    public function testGetCustomerProviders()
    {
        $customer = $this->getMockBuilder(CustomerInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $customAttribute = $this->getMockBuilder(AttributeInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $provider1 = $this->getMockBuilder(ProviderInterface::class)
                               ->disableOriginalConstructor()
                               ->getMock();
        $provider2 = $this->getMockBuilder(ProviderInterface::class)
                               ->disableOriginalConstructor()
                               ->getMock();
        $result = [$provider1, $provider2];

        $customAttribute->expects($this->atLeastOnce())->method('getValue')->willReturn('test,test2');
        $customer->expects($this->atLeastOnce())->method('getCustomAttribute')->willReturn($customAttribute);
        $this->customerRepository->expects($this->atLeastOnce())->method('getById')->willReturn($customer);
        $this->providerPool->expects($this->atLeastOnce())->method('getEnabledProviders')
                                                          ->willReturn([$provider1, $provider2]);
        $this->configProvider->expects($this->never())->method('isTfaForced');

        $this->assertEquals($result, $this->object->getCustomerProviders(1));
    }

    /**
     *
     */
    public function testGetCustomerProvidersNoCustomAttributes()
    {
        $customer = $this->getMockBuilder(CustomerInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $provider1 = $this->getMockBuilder(ProviderInterface::class)
                          ->disableOriginalConstructor()
                          ->getMock();
        $provider2 = $this->getMockBuilder(ProviderInterface::class)
                          ->disableOriginalConstructor()
                          ->getMock();
        $result = [$provider1, $provider2];

        $customer->expects($this->atLeastOnce())->method('getCustomAttribute')->willReturn(null);
        $this->customerRepository->expects($this->atLeastOnce())->method('getById')->willReturn($customer);
        $this->providerPool->expects($this->atLeastOnce())->method('getEnabledProviders')
                           ->willReturn([$provider1, $provider2]);
        $this->configProvider->expects($this->atLeastOnce())->method('isTfaForced')->willReturn(true);

        $this->assertEquals($result, $this->object->getCustomerProviders(1));
    }

    /**
     *
     */
    protected function setUp(): void
    {
        $this->customerRepository = $this->getMockBuilder(CustomerRepositoryInterface::class)
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $this->configProvider = $this->getMockBuilder(ConfigProvider::class)
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $this->providerPool = $this->getMockBuilder(ProviderPoolInterface::class)
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $this->object = (new ObjectManager($this))->getObject(
            CustomerProvidersManager::class,
            [
                'customerRepository' => $this->customerRepository,
                'configProvider' => $this->configProvider,
                'providerPool' => $this->providerPool,
            ]
        );
    }
}
