<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Test\Unit\Plugin\Customer\Controller\Ajax;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Json\Helper\Data;
use Magetarian\CustomerTwoFactorAuth\Api\CustomerProvidersManagerInterface;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderPoolInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\DataObjectFactory;

class LoginPluginTest extends TestCase
{

    /** @var LoginPlugin object */
    private $object;

    private $customerAccountManagement;

    private $resultFactory;

    private $providerPool;

    private $jsonHelper;

    private $dataObjectFactory;

    private $customerProvidersManager;

    public function testAroundExecute()
    {

    }

    protected function setUp()
    {
        $this->providerPool = $this->getMockBuilder(ProviderPoolInterface::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();
        $this->customerAccountManagement = $this->getMockBuilder(AccountManagementInterface::class)
                                                ->disableOriginalConstructor()
                                                ->getMock();
        $this->customerProvidersManager = $this->getMockBuilder(CustomerProvidersManagerInterface::class)
                                               ->disableOriginalConstructor()
                                               ->getMock();
        $this->dataObjectFactory = $this->getMockBuilder(DataObjectFactory::class)
                                        ->disableOriginalConstructor()
                                        ->getMock();
        $this->jsonHelper = $this->getMockBuilder(Data::class)
                                        ->disableOriginalConstructor()
                                        ->getMock();
        $this->resultFactory = $this->getMockBuilder(ResultFactory::class)
                                        ->disableOriginalConstructor()
                                        ->getMock();
        $this->object = (new ObjectManager($this))->getObject(
            LoginPlugin::class,
            [
                'customerAccountManagement' => $this->customerAccountManagement,
                'resultFactory' => $this->resultFactory,
                'providerPool' => $this->providerPool,
                'dataObjectFactory' => $this->dataObjectFactory,
                'jsonHelper' => $this->jsonHelper,
                'customerProvidersManager' => $this->customerProvidersManager,
            ]
        );
    }
}
