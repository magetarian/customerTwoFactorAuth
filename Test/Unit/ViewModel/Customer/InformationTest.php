<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Test\Unit\ViewModel\Customer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderPoolInterface;
use Magetarian\CustomerTwoFactorAuth\ViewModel\Customer\Information;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magetarian\CustomerTwoFactorAuth\Api\EngineInterface;

/**
 * Class InformationTest
 * Test for InformationTest class
 */
class InformationTest extends TestCase
{

    /** @var Information object */
    private $object;

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
    public function testGetProviderName()
    {
        $result = 'test';
        $provider = $this->getMockBuilder(ProviderInterface::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $this->providerPool->expects($this->atLeastOnce())->method('getProviderByCode')->willReturn($provider);
        $provider->expects($this->atLeastOnce())->method('getName')->willReturn($result);
        $this->assertEquals($result, $this->object->getProviderName($result));
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetAdditionalConfig()
    {
        $result = [];
        $provider = $this->getMockBuilder(ProviderInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $providerEngine = $this->getMockBuilder(EngineInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $customerDataModel = $this->getMockBuilder(CustomerInterface::class)
                                  ->disableOriginalConstructor()
                                  ->getMock();
        $this->customerSession->expects($this->atLeastOnce())->method('getCustomerData')
                                                             ->willReturn($customerDataModel);
        $providerEngine->expects($this->atLeastOnce())->method('getAdditionalConfig')->willReturn($result);
        $provider->expects($this->atLeastOnce())->method('getEngine')->willReturn($providerEngine);
        $this->providerPool->expects($this->atLeastOnce())->method('getProviderByCode')->willReturn($provider);

        $this->assertEquals($result, $this->object->getAdditionalConfig('test'));
    }

    /**
     *
     */
    public function testIsEnabled()
    {
        $result = true;
        $provider = $this->getMockBuilder(ProviderInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $this->providerPool->expects($this->atLeastOnce())->method('getProviderByCode')->willReturn($provider);
        $provider->expects($this->atLeastOnce())->method('isEnabled')->willReturn($result);
        $this->assertEquals($result, $this->object->isEnabled('test'));
    }

    /**
     *
     */
    protected function setUp(): void
    {
        $this->customerSession = $this->getMockBuilder(Session::class)
                                      ->disableOriginalConstructor()
                                      ->getMock();
        $this->providerPool = $this->getMockBuilder(ProviderPoolInterface::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();

        $this->object = (new ObjectManager($this))->getObject(
            Information::class,
            [
                'providerPool' => $this->providerPool,
                'customerSession' => $this->customerSession,
            ]
        );
    }
}
