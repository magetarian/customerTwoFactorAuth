<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Test\Unit\Model;

use Magetarian\CustomerTwoFactorAuth\Api\CustomerConfigManagerInterface;
use Magetarian\CustomerTwoFactorAuth\Api\EngineInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class ProviderTest
 * Test for Provider class
 */
class ProviderTest extends TestCase
{

    /** @var Provider object */
    private $object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $engine;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $customerConfigManager;

    /**
     * @var string
     */
    private $code = 'test';

    /**
     * @var string
     */
    private $name = 'testName';

    /**
     *
     */
    public function testIsEnabled()
    {
        $result = true;
        $this->engine->expects($this->atLeastOnce())->method('isEnabled')->willReturn($result);
        $this->assertEquals($result, $this->object->isEnabled());
    }

    /**
     *
     */
    public function testGetEngine()
    {
        $this->assertEquals($this->engine, $this->object->getEngine());
    }

    /**
     * @dataProvider dataProviderIsConfigured
     */
    public function testIsConfigured(?array $configured, bool $result)
    {
        $customerId = 1;
        $this->customerConfigManager->expects($this->atLeastOnce())->method('getProviderConfig')->willReturn($configured);
        $this->assertEquals($result, $this->object->isConfigured($customerId));
    }

    /**
     * @return array|array[]
     */
    public function dataProviderIsConfigured(): array
    {
        return [
            [['test'], true],
            [[], true],
            [null, false],
        ];
    }

    /**
     *
     */
    public function testGetCode()
    {
        $this->assertEquals($this->code, $this->object->getCode());
    }

    /**
     *
     */
    public function testGetName()
    {
        $this->assertEquals($this->name, $this->object->getName());
    }

    /**
     *
     */
    public function testResetConfiguration()
    {
        $customerId = 1;
        $this->assertEquals($this->object, $this->object->resetConfiguration($customerId));
    }

    /**
     *
     */
    protected function setUp()
    {
        $this->engine = $this->getMockBuilder(EngineInterface::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->customerConfigManager = $this->getMockBuilder(CustomerConfigManagerInterface::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->object = (new ObjectManager($this))->getObject(
            Provider::class,
            [
                'engine' => $this->engine,
                'customerConfigManager' => $this->customerConfigManager,
                'code' => $this->code,
                'name' => $this->name,
            ]
        );
    }
}
