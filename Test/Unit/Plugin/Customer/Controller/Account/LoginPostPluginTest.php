<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Test\Unit\Plugin\Customer\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magetarian\CustomerTwoFactorAuth\Api\CustomerProvidersManagerInterface;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderPoolInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\DataObjectFactory;
use Magetarian\CustomerTwoFactorAuth\Plugin\Customer\Controller\Account\LoginPostPlugin;
use Magento\Customer\Controller\Account\LoginPost;

class LoginPostPluginTest extends TestCase
{

    /** @var LoginPostPlugin object */
    private $object;

    private $customerAccountManagement;

    private $resultRedirectFactory;

    private $providerPool;

    private $messageManager;

    private $dataObjectFactory;

    private $customerProvidersManager;

    public function testAroundExecute()
    {
//        $isProceedCalled = false;
//        $object =  $this->getMockBuilder(LoginPost::class)
//                        ->disableOriginalConstructor()
//                        ->getMock();
//        // @SuppressWarnings(PHPMD.UnusedFormalParameter)
//        $proceed = function () use (&$isProceedCalled) {
//            $isProceedCalled = true;
//        };
//
//        $this->object->aroundExecute(
//            $object,
//            $proceed
//        );
//        $this->assertTrue($isProceedCalled);
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
        $this->resultRedirectFactory = $this->getMockBuilder(RedirectFactory::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();
        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();
        $this->object = (new ObjectManager($this))->getObject(
            LoginPostPlugin::class,
            [
                'customerAccountManagement' => $this->customerAccountManagement,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                'providerPool' => $this->providerPool,
                'messageManager' => $this->messageManager,
                'dataObjectFactory' => $this->dataObjectFactory,
                'customerProvidersManager' => $this->customerProvidersManager,
            ]
        );
    }
}
