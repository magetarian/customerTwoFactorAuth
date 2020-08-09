<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Test\Unit\ViewModel\Customer;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderPoolInterface;
use Magetarian\CustomerTwoFactorAuth\ViewModel\Customer\Configuration;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Customer\Model\Data\AttributeMetadata;
use Magento\Customer\Model\Customer;
use Magento\Framework\Api\AttributeInterface;

/**
 * Class ConfigurationTest
 * Test for ConfigurationTest class
 */
class ConfigurationTest extends TestCase
{

    /** @var Configuration object */
    private $object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $customerMetadata;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $providerPool;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $customerSession;

    /**
     *
     */
    public function testGetEnabledProviders()
    {
        $this->providerPool->expects($this->once())->method('getEnabledProviders')->willReturn(['test']);
        $this->assertTrue($this->object->isEnabled());
        $this->assertTrue($this->object->isEnabled());
    }

    /**
     * @dataProvider dataProviderSelectedProviders
     */
    public function testGetSelectedProviders(string $providers, array $result)
    {
        $customer = $this->getMockBuilder(Customer::class)
                          ->disableOriginalConstructor()
                          ->getMock();
        $customerDataModel = $this->getMockBuilder(CustomerInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $attribute = $this->getMockBuilder(AttributeInterface::class)
                                  ->disableOriginalConstructor()
                                  ->getMock();
        if ($providers) {
            $customerDataModel->expects($this->atLeastOnce())->method('getCustomAttribute')->willReturn($attribute);
            $attribute->expects($this->atLeastOnce())->method('getValue')->willReturn($providers);
        } else {
            $customerDataModel->expects($this->atLeastOnce())->method('getCustomAttribute')->willReturn(null);
        }
        $customer->expects($this->atLeastOnce())->method('getDataModel')->willReturn($customerDataModel);
        $this->customerSession->expects($this->atLeastOnce())->method('getCustomer')->willReturn($customer);
        $this->assertEquals($result, $this->object->getSelectedProviders());
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetProviderConfigAttribute()
    {
        $attribute = $this->getMockBuilder(AttributeMetadata::class)
                                      ->disableOriginalConstructor()
                                      ->getMock();
        $this->customerMetadata->expects($this->atLeastOnce())->method('getAttributeMetadata')->willReturn($attribute);
        $this->assertEquals($attribute, $this->object->getProviderConfigAttribute());
    }

    /**
     * @dataProvider dataProviderEnabled
     */
    public function testIsEnabled(array $providers, bool $result)
    {
        $this->providerPool->expects($this->atLeastOnce())->method('getEnabledProviders')->willReturn($providers);
        $this->assertEquals($result, $this->object->isEnabled());
    }

    /**
     * @return array|array[]
     */
    public function dataProviderEnabled(): array
    {
        return [
            [['test'], true],
            [[], false],
        ];
    }

    /**
     * @return array|array[]
     */
    public function dataProviderSelectedProviders(): array
    {
        return [
            ['test,test2', ['test', 'test2']],
            ['', []],
        ];
    }

    /**
     *
     */
    protected function setUp(): void
    {
        $this->customerSession = $this->getMockBuilder(Session::class)
                               ->disableOriginalConstructor()
                               ->getMock();
        $this->customerMetadata = $this->getMockBuilder(CustomerMetadataInterface::class)
                                      ->disableOriginalConstructor()
                                      ->getMock();
        $this->providerPool = $this->getMockBuilder(ProviderPoolInterface::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();
        $this->object = (new ObjectManager($this))->getObject(
            Configuration::class,
            [
                'providerPool' => $this->providerPool,
                'customerSession' => $this->customerSession,
                'customerMetadata' => $this->customerMetadata,
            ]
        );
    }
}
