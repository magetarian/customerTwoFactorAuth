<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Test\Unit\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class ConfigProviderTest
 * Test for ConfigProvider class
 */
class ConfigProviderTest extends TestCase
{

    /** @var ConfigProvider object */
    private $object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfig;

    /**
     *
     */
    public function testIsTfaForced()
    {
        $result = false;
        $this->assertFalse($result, $this->object->isTfaForced());
    }

    /**
     *
     */
    protected function setUp()
    {
        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();
        $this->object = (new ObjectManager($this))->getObject(
            ConfigProvider::class,
            [
                'scopeConfig' => $this->scopeConfig
            ]
        );
    }
}
