<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Test\Unit\Plugin\Customer\Controller\Ajax;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Json\Helper\Data;
use Magetarian\CustomerTwoFactorAuth\Api\CustomerProvidersManagerInterface;
use Magetarian\CustomerTwoFactorAuth\Api\EngineInterface;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderPoolInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\DataObject;
use Magetarian\CustomerTwoFactorAuth\Plugin\Customer\Controller\Ajax\LoginPlugin;
use Magento\Customer\Controller\Ajax\Login;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\Json;

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

    /**
     * @dataProvider dataProviderExecute
     */
    public function testAroundExecute(bool $result, array $customerProviders, bool $verify)
    {
        $isProceedCalled = false;
        $subject =  $this->getMockBuilder(Login::class)
                        ->disableOriginalConstructor()
                        ->getMock();
        $request =  $this->getMockBuilder(RequestInterface::class)
                         ->disableOriginalConstructor()
                         ->setMethods(['getContent', 'getMethod', 'isXmlHttpRequest'])
                         ->getMockForAbstractClass();
        $customer = $this->getMockBuilder(CustomerInterface::class)
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
        $resultJson = $this->getMockBuilder(Json::class)
                           ->disableOriginalConstructor()
                           ->getMock();
        $this->jsonHelper
            ->expects($this->atLeastOnce())
            ->method('jsonDecode')
            ->willReturn(['username'=> 'test', 'password' => 'test']);
        $customer->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $this->customerAccountManagement->expects($this->atLeastOnce())->method('authenticate')->willReturn($customer);
        $this->customerProvidersManager
            ->expects($this->atLeastOnce())
            ->method('getCustomerProviders')
            ->willReturn($customerProviders);
        $this->dataObjectFactory->expects($this->any())->method('create')->willReturn($dataObject);
        if (!count($customerProviders)) {
            $engine->expects($this->atLeastOnce())->method('verify')->willReturn($verify);
            $provider->expects($this->atLeastOnce())->method('getEngine')->willReturn($engine);
            $this->providerPool->expects($this->atLeastOnce())->method('getProviderByCode')->willReturn($provider);
        }
        $request->expects($this->atLeastOnce())->method('getContent')->willReturn('test');
        $request->expects($this->atLeastOnce())->method('getMethod')->willReturn('POST');
        $request->expects($this->atLeastOnce())->method('isXmlHttpRequest')->willReturn(true);
        $subject->expects($this->atLeastOnce())->method('getRequest')->willReturn($request);
        if (!$result) {
            $resultJson->expects($this->atLeastOnce())->method('setData')->willReturn($resultJson);
            $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($resultJson);
        }
        // @SuppressWarnings(PHPMD.UnusedFormalParameter)
        $proceed = function () use (&$isProceedCalled) {
            $isProceedCalled = true;
        };

        $this->object->aroundExecute(
            $subject,
            $proceed
        );

        $this->assertEquals($result, $isProceedCalled);
        if (!$result) {
            $this->assertEquals($resultJson, $this->object->aroundExecute($subject, $proceed));
        }
    }

    public function dataProviderExecute(): array
    {
        return [
            [true , [], true],
            [false , ['test'], true],
            [false , [], false],
            [true , [], false],
        ];
    }

    public function testAroundExecuteJsonException()
    {
        $isProceedCalled = false;
        $subject =  $this->getMockBuilder(Login::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $request =  $this->getMockBuilder(RequestInterface::class)
                         ->disableOriginalConstructor()
                         ->setMethods(['getContent'])
                         ->getMockForAbstractClass();
        $resultRaw = $this->getMockBuilder(Raw::class)
                          ->disableOriginalConstructor()
                          ->getMock();
        $this->jsonHelper
            ->expects($this->atLeastOnce())
            ->method('jsonDecode')
            ->willThrowException(new \Exception('test'));
        $this->customerAccountManagement->expects($this->never())->method('authenticate');
        $resultRaw->expects($this->atLeastOnce())->method('setHttpResponseCode')->willReturn($resultRaw);
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($resultRaw);
        $subject->expects($this->atLeastOnce())->method('getRequest')->willReturn($request);
        // @SuppressWarnings(PHPMD.UnusedFormalParameter)
        $proceed = function () use (&$isProceedCalled) {
            $isProceedCalled = true;
        };

        $this->object->aroundExecute(
            $subject,
            $proceed
        );

        $this->assertFalse($isProceedCalled);
        $this->assertEquals($resultRaw, $this->object->aroundExecute($subject, $proceed));
    }

    public function testAroundExecuteNotPost()
    {
        $isProceedCalled = false;
        $subject =  $this->getMockBuilder(Login::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $request =  $this->getMockBuilder(RequestInterface::class)
                         ->disableOriginalConstructor()
                         ->setMethods(['getContent', 'getMethod'])
                         ->getMockForAbstractClass();
        $resultRaw = $this->getMockBuilder(Raw::class)
                          ->disableOriginalConstructor()
                          ->getMock();
        $this->jsonHelper
            ->expects($this->atLeastOnce())
            ->method('jsonDecode')
            ->willReturn(['username'=> 'test', 'password' => 'test']);
        $this->customerAccountManagement->expects($this->never())->method('authenticate');
        $resultRaw->expects($this->atLeastOnce())->method('setHttpResponseCode')->willReturn($resultRaw);
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($resultRaw);
        $request->expects($this->atLeastOnce())->method('getMethod')->willReturn('GET');
        $subject->expects($this->atLeastOnce())->method('getRequest')->willReturn($request);
        // @SuppressWarnings(PHPMD.UnusedFormalParameter)
        $proceed = function () use (&$isProceedCalled) {
            $isProceedCalled = true;
        };

        $this->object->aroundExecute(
            $subject,
            $proceed
        );

        $this->assertFalse($isProceedCalled);
        $this->assertEquals($resultRaw, $this->object->aroundExecute($subject, $proceed));
    }

    public function testAroundExecuteException()
    {
        $isProceedCalled = false;
        $subject =  $this->getMockBuilder(Login::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $request =  $this->getMockBuilder(RequestInterface::class)
                         ->disableOriginalConstructor()
                         ->setMethods(['getContent', 'getMethod', 'isXmlHttpRequest'])
                         ->getMockForAbstractClass();
        $this->jsonHelper
            ->expects($this->atLeastOnce())
            ->method('jsonDecode')
            ->willReturn(['username'=> 'test', 'password' => 'test']);
        $request->expects($this->atLeastOnce())->method('getContent')->willReturn('test');
        $request->expects($this->atLeastOnce())->method('getMethod')->willReturn('POST');
        $request->expects($this->atLeastOnce())->method('isXmlHttpRequest')->willReturn(true);
        $subject->expects($this->atLeastOnce())->method('getRequest')->willReturn($request);
        $this->customerAccountManagement
            ->expects($this->atLeastOnce())
            ->method('authenticate')
            ->willThrowException(new \Exception('test'));
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
