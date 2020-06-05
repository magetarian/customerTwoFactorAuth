<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Test\Unit\Model\Provider\Engine;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magetarian\CustomerTwoFactorAuth\Api\CustomerConfigManagerInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\HTTP\Client\CurlFactory;
use MSP\TwoFactorAuth\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;

class AuthyTest extends TestCase
{

    /** @var Authy object */
    private $object;

    private $scopeConfig;

    private $customerConfigManager;

    private $curlFactory;

    private $json;

    private $countryCollectionFactory;

    public function testGetAdditionalConfig()
    {

    }

    public function testRequestToken()
    {

    }

    public function testGetCode()
    {

    }

    public function testIsEnabled()
    {

    }

    public function testRequestEnroll()
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
        $this->customerConfigManager = $this->getMockBuilder(CustomerConfigManagerInterface::class)
                                  ->disableOriginalConstructor()
                                  ->getMock();
        $this->curlFactory = $this->getMockBuilder(CurlFactory::class)
                                  ->disableOriginalConstructor()
                                  ->getMock();
        $this->json = $this->getMockBuilder(Json::class)
                                  ->disableOriginalConstructor()
                                  ->getMock();
        $this->countryCollectionFactory = $this->getMockBuilder(CountryCollectionFactory::class)
                                  ->disableOriginalConstructor()
                                  ->getMock();
        $this->object = (new ObjectManager($this))->getObject(
            Authy::class,
            [
                'scopeConfig' => $this->scopeConfig,
                'customerConfigManager' => $this->customerConfigManager,
                'curlFactory' => $this->curlFactory,
                'json' => $this->json,
                'countryCollectionFactory' => $this->countryCollectionFactory
            ]
        );
    }
}
