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
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magetarian\CustomerTwoFactorAuth\Model\Provider\Engine\DuoSecurity;
use Magento\TwoFactorAuth\Model\Provider\Engine\DuoSecurity as MagentoDuoSecurity;

/**
 * Class DuoSecurityTest
 * Test for DuoSecurity class
 */
class DuoSecurityTest extends TestCase
{

    /** @var DuoSecurity object */
    private $object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfig;

    /**
     *
     */
    public function testGetAdditionalConfig()
    {
        $customer = $this->getMockBuilder(CustomerInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $this->scopeConfig->expects($this->atLeastOnce())->method('getValue')->willReturn('test');
        $customer->expects($this->atLeastOnce())->method('getEmail')->willReturn('test');
        $customer->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $this->assertArrayHasKey('signature', $this->object->getAdditionalConfig($customer));
    }

    /**
     *
     */
    public function testGetCode()
    {
        $this->assertEquals(MagentoDuoSecurity::CODE, $this->object->getCode());
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
     *
     */
    public function testVerify()
    {
        $customer = $this->getMockBuilder(CustomerInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $request = $this->getMockBuilder(DataObject::class)
                        ->disableOriginalConstructor()
                        ->getMock();
        $request->expects($this->atLeastOnce())->method('getData')->willReturn('test|test|test:test|test|test');
        $customer->expects($this->atLeastOnce())->method('getEmail')->willReturn('test');
        $customer->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $this->scopeConfig->expects($this->atLeastOnce())->method('getValue')->willReturn('test|test|test');
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
        $this->object = (new ObjectManager($this))->getObject(
            DuoSecurity::class,
            [
                'scopeConfig' => $this->scopeConfig
            ]
        );
    }
}
