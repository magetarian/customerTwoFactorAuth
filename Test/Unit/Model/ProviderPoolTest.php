<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface;
use Magetarian\CustomerTwoFactorAuth\Model\ProviderPool;

/**
 * Class ProviderPoolTest
 * Test for ProviderPool class
 */
class ProviderPoolTest extends TestCase
{

    /** @var ProviderPool object */
    private $object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $provider;

    /**
     *
     */
    public function testGetEnabledProviders()
    {
        $result = [$this->provider];
        $this->provider->expects($this->atLeastOnce())->method('isEnabled')->willReturn(true);
        $this->assertEquals($result, $this->object->getEnabledProviders());
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetProviderByCode()
    {
        $this->assertEquals($this->provider, $this->object->getProviderByCode('test'));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Unknown provider test2
     */
    public function testGetProviderByCodeException()
    {
        $this->object->getProviderByCode('test2');
    }

    /**
     *
     */
    public function testGetProviders()
    {
        $result = ['test' => $this->provider];
        $this->assertEquals($result, $this->object->getProviders());
    }

    /**
     *
     */
    protected function setUp()
    {
        $this->provider = $this->getMockBuilder(ProviderInterface::class)
                             ->disableOriginalConstructor()
                             ->getMock();
        $this->object = (new ObjectManager($this))->getObject(
            ProviderPool::class,
            [
                'providers' => ['test' => $this->provider]
            ]
        );
    }
}
