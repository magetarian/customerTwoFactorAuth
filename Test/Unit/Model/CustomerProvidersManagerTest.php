<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Test\Unit\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderPoolInterface;
use Magetarian\CustomerTwoFactorAuth\Model\Config\ConfigProvider;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CustomerProvidersManagerTest extends TestCase
{

    /** @var CustomerProvidersManager object */
    private $object;

    private $customerRepository;

    private $configProvider;

    private $providerPool;

    public function testGetCustomerProviders()
    {

    }

    protected function setUp()
    {
        $this->customerRepository = $this->getMockBuilder(CustomerRepositoryInterface::class)
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $this->configProvider = $this->getMockBuilder(ConfigProvider::class)
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $this->providerPool = $this->getMockBuilder(ProviderPoolInterface::class)
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $this->object = (new ObjectManager($this))->getObject(
            CustomerProvidersManager::class,
            [
                'customerRepository' => $this->customerRepository,
                'configProvider' => $this->configProvider,
                'providerPool' => $this->providerPool,
            ]
        );
    }
}
