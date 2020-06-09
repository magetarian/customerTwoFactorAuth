<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Test\Unit\Plugin\Customer\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\DataObject;
use Magento\Framework\Message\ManagerInterface;
use Magetarian\CustomerTwoFactorAuth\Api\CustomerProvidersManagerInterface;
use Magetarian\CustomerTwoFactorAuth\Api\EngineInterface;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderPoolInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\DataObjectFactory;
use Magetarian\CustomerTwoFactorAuth\Plugin\Customer\Controller\Account\LoginPostPlugin;
use Magento\Customer\Controller\Account\LoginPost;
use Magento\Framework\App\RequestInterface;

/**
 * Class LoginPostPluginTest
 * Test for LoginPostPlugin class
 */
class LoginPostPluginTest extends TestCase
{

    /** @var LoginPostPlugin object */
    private $object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $customerAccountManagement;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $providerPool;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $messageManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $dataObjectFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $customerProvidersManager;

    /**
     * @dataProvider dataProviderExecute
     */
    public function testAroundExecute(bool $isProceedCalled, array $customerProviders, bool $verify)
    {
        $subject =  $this->getMockBuilder(LoginPost::class)
                        ->disableOriginalConstructor()
                        ->getMock();
        $customer = $this->getMockBuilder(CustomerInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $request =  $this->getMockBuilder(RequestInterface::class)
                         ->disableOriginalConstructor()
                         ->setMethods(['isPost', 'getPost', 'getParams'])
                         ->getMockForAbstractClass();
        $redirect =  $this->getMockBuilder(Redirect::class)
                          ->disableOriginalConstructor()
                          ->getMock();
        $provider = $this->getMockBuilder(ProviderInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $engine = $this->getMockBuilder(EngineInterface::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $dataObject = $this->getMockBuilder(DataObject::class)
                           ->disableOriginalConstructor()
                           ->getMock();
        $request->expects($this->atLeastOnce())->method('isPost')->willReturn(true);
        $request->expects($this->any())->method('getParams')->willReturn(['test']);
        $request->expects($this->at(1))
                ->method('getPost')
                ->willReturn(['username'=> 'test', 'password' => 'test']);
        $request->expects($this->at(2))
                ->method('getPost')
                ->willReturn('test');
        if (!count($customerProviders)) {
            $request->expects($this->at(3))
                    ->method('getPost')
                    ->willReturn('test');
        }
        $request->expects($this->atLeastOnce())->method('isPost')->willReturn(true);
        $subject->expects($this->atLeastOnce())->method('getRequest')->willReturn($request);
        $customer->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $this->customerAccountManagement->expects($this->atLeastOnce())->method('authenticate')->willReturn($customer);
        $this->customerProvidersManager
            ->expects($this->atLeastOnce())
            ->method('getCustomerProviders')
            ->willReturn($customerProviders);
        if (count($customerProviders)) {
            $this->messageManager->expects($this->atLeastOnce())->method('addWarningMessage');
            $this->messageManager->expects($this->never())->method('addErrorMessage');
        } elseif (!$verify) {
            $this->messageManager->expects($this->atLeastOnce())->method('addErrorMessage');
            $this->messageManager->expects($this->never())->method('addWarningMessage');
        } else {
            $this->messageManager->expects($this->never())->method('addWarningMessage');
            $this->messageManager->expects($this->never())->method('addErrorMessage');
        }
        if (!count($customerProviders)) {
            $engine->expects($this->atLeastOnce())->method('verify')->willReturn($verify);
            $provider->expects($this->atLeastOnce())->method('getEngine')->willReturn($engine);
            $this->providerPool->expects($this->atLeastOnce())->method('getProviderByCode')->willReturn($provider);
        }
        if (!$isProceedCalled) {
            $redirect->expects($this->atLeastOnce())->method('setPath')->willReturn($redirect);
        } else {
            $redirect->expects($this->never())->method('setPath');
        }
        $this->dataObjectFactory->expects($this->any())->method('create')->willReturn($dataObject);
        $this->resultRedirectFactory->expects($this->atLeastOnce())->method('create')->willReturn($redirect);
        // @SuppressWarnings(PHPMD.UnusedFormalParameter)
        $proceed = function () use (&$isProceedCalled) {
            $isProceedCalled = true;
        };
        $this->object->aroundExecute(
            $subject,
            $proceed
        );
        if ($isProceedCalled) {
            $this->assertTrue($isProceedCalled);
        }
        if (!$isProceedCalled) {
            $this->assertEquals($redirect, $this->object->aroundExecute($subject, $proceed));
        }
    }

    /**
     * @return array|array[]
     */
    public function dataProviderExecute(): array
    {
        return [
            [true , [], true],
            [false , ['test'], true],
            [false , [], false],
        ];
    }


    /**
     *
     */
    public function testAroundExecuteIsNotPost()
    {
        $subject =  $this->getMockBuilder(LoginPost::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $request =  $this->getMockBuilder(RequestInterface::class)
                         ->disableOriginalConstructor()
                         ->setMethods(['isPost'])
                         ->getMockForAbstractClass();
        $isProceedCalled = false;
        $request->expects($this->atLeastOnce())->method('isPost')->willReturn(false);
        $subject->expects($this->atLeastOnce())->method('getRequest')->willReturn($request);
        // @SuppressWarnings(PHPMD.UnusedFormalParameter)
        $proceed = function () use (&$isProceedCalled) {
            $isProceedCalled = true;
        };

        $this->object->aroundExecute(
            $subject,
            $proceed
        );
        $this->assertTrue($isProceedCalled);
    }

    /**
     *
     */
    public function testAroundExecuteException()
    {
        $subject =  $this->getMockBuilder(LoginPost::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $request =  $this->getMockBuilder(RequestInterface::class)
                         ->disableOriginalConstructor()
                         ->setMethods(['isPost', 'getPost'])
                         ->getMockForAbstractClass();
        $redirect =  $this->getMockBuilder(Redirect::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $isProceedCalled = false;
        $request->expects($this->atLeastOnce())->method('isPost')->willReturn(true);
        $request->expects($this->atLeastOnce())->method('getPost')->willReturn(['username'=> 'test', 'password' => 'test']);
        $this->resultRedirectFactory->expects($this->atLeastOnce())->method('create')->willReturn($redirect);
        $subject->expects($this->atLeastOnce())->method('getRequest')->willReturn($request);
        $this->customerAccountManagement->expects($this->atLeastOnce())
                                        ->method('authenticate')
                                        ->willThrowException(new \Exception('test'));
        $this->customerProvidersManager->expects($this->never())->method('getCustomerProviders');
        // @SuppressWarnings(PHPMD.UnusedFormalParameter)
        $proceed = function () use (&$isProceedCalled) {
            $isProceedCalled = true;
        };

        $this->object->aroundExecute(
            $subject,
            $proceed
        );
        $this->assertTrue($isProceedCalled);
    }

    /**
     *
     */
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
