<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Test\Unit\Model\Provider\Engine;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;
use Magetarian\CustomerTwoFactorAuth\Api\CustomerConfigManagerInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magetarian\CustomerTwoFactorAuth\Model\Provider\Engine\Google;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Class GoogleTest
 * Test for Google class
 */
class GoogleTest extends TestCase
{

    /** @var Google object */
    private $object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfig;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $customerConfigManager;

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetAdditionalConfig()
    {
        $result = ['secretCode'=> '123'];
        $customer = $this->getMockBuilder(CustomerInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $customer->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $this->customerConfigManager
            ->expects($this->atLeastOnce())
            ->method('getProviderConfig')
            ->willReturn(['secret'=> '123']);
        $this->assertEquals($result, $this->object->getAdditionalConfig($customer));
    }

    /**
     *
     */
    public function testIsEnabled()
    {
        $this->scopeConfig->expects($this->atLeastOnce())->method('getValue')->willReturn('test');
        $this->assertTrue($this->object->isEnabled());
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetSecretCode()
    {
        $this->customerConfigManager->expects($this->atLeastOnce())->method('getProviderConfig')->willReturn([]);
        $this->assertNotNull($this->object->getSecretCode(1));
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testVerify()
    {
        $customer = $this->getMockBuilder(CustomerInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $request = $this->getMockBuilder(DataObject::class)
                        ->disableOriginalConstructor()
                        ->getMock();
        $request->expects($this->atLeastOnce())->method('getData')->willReturn('123');
        $this->assertFalse($this->object->verify($customer, $request));
    }

    /**
     *
     */
    protected function setUp(): void
    {
        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
                                  ->disableOriginalConstructor()
                                  ->getMock();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
                                  ->disableOriginalConstructor()
                                  ->getMock();
        $this->customerConfigManager = $this->getMockBuilder(CustomerConfigManagerInterface::class)
                                  ->disableOriginalConstructor()
                                  ->getMock();
        $this->object = (new ObjectManager($this))->getObject(
            Google::class,
            [
                'storeManager' => $this->storeManager,
                'scopeConfig' => $this->scopeConfig,
                'customerConfigManager' => $this->customerConfigManager
            ]
        );
    }
}
