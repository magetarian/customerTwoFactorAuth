<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Test\Unit\Model\Provider\Engine;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magetarian\CustomerTwoFactorAuth\Api\CustomerConfigManagerInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class GoogleTest extends TestCase
{

    /** @var Google object */
    private $object;

    private $scopeConfig;

    private $storeManager;

    private $customerConfigManager;

    public function testGetAdditionalConfig()
    {

    }

    public function testIsEnabled()
    {

    }

    public function testGetQrCodeAsPng()
    {

    }

    public function testGetCode()
    {

    }

    public function testGetSecretCode()
    {

    }

    public function testVerify()
    {

    }

    protected function setUp()
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
