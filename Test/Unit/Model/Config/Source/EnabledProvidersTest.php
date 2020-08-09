<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Test\Unit\Model\Config\Source;

use Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderPoolInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magetarian\CustomerTwoFactorAuth\Model\Config\Source\EnabledProviders;

/**
 * Class EnabledProvidersTest
 * Test for EnabledProviders class
 */
class EnabledProvidersTest extends TestCase
{

    /** @var EnabledProviders object */
    private $object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $providerPool;

    /**
     *
     */
    public function testToArray()
    {
        $result = [['value' => 'test', 'label' => 'test2']];
        $provider = $this->getMockBuilder(ProviderInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $provider->expects($this->atLeastOnce())->method('getCode')->willReturn('test');
        $provider->expects($this->atLeastOnce())->method('getName')->willReturn('test2');
        $this->providerPool->expects($this->atLeastOnce())->method('getEnabledProviders')->willReturn([$provider]);
        $this->assertEquals($result, $this->object->toArray());
    }

    /**
     *
     */
    public function testGetAllOptions()
    {
        $result = [['value' => 'test', 'label' => 'test2']];
        $provider = $this->getMockBuilder(ProviderInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $provider->expects($this->atLeastOnce())->method('getCode')->willReturn('test');
        $provider->expects($this->atLeastOnce())->method('getName')->willReturn('test2');
        $this->providerPool->expects($this->atLeastOnce())->method('getEnabledProviders')->willReturn([$provider]);
        $this->assertEquals($result, $this->object->getAllOptions());
    }

    /**
     *
     */
    protected function setUp(): void
    {
        $this->providerPool = $this->getMockBuilder(ProviderPoolInterface::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();

        $this->object = (new ObjectManager($this))->getObject(
            EnabledProviders::class,
            [
                'providerPool' => $this->providerPool,
            ]
        );
    }
}
