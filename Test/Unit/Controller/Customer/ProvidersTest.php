<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Test\Unit\Controller\Customer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\Serializer\Json;
use Magetarian\CustomerTwoFactorAuth\Api\CustomerProvidersManagerInterface;
use Magetarian\CustomerTwoFactorAuth\Api\EngineInterface;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Controller\Result\Json as ResultJson;

/**
 * Class ProvidersTest
 * Test for ProvidersTest class
 */
class ProvidersTest extends TestCase
{

    /** @var Providers object */
    private $object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $context;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $resultFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $customerAccountManagement;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $customerSession;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $json;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $customerProvidersManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $formKey;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $request;

    /**
     * @throws LocalizedException
     */
    public function testExecute()
    {
        $requestData = [
            'form_key' => '123',
            'username' => '123',
            'password' => '123',
        ];
        $resultJson  = $this->getMockBuilder(ResultJson::class)
                            ->disableOriginalConstructor()
                            ->getMock();
        $customer  = $this->getMockBuilder(CustomerInterface::class)
                          ->disableOriginalConstructor()
                          ->getMock();
        $provider  = $this->getMockBuilder(ProviderInterface::class)
                          ->disableOriginalConstructor()
                          ->getMock();
        $providerEngine  = $this->getMockBuilder(EngineInterface::class)
                          ->disableOriginalConstructor()
                          ->getMock();
        $this->request->expects($this->atLeastOnce())->method('getContent')->willReturn('{}');
        $this->request->expects($this->atLeastOnce())->method('getMethod')->willReturn('POST');
        $this->request->expects($this->atLeastOnce())->method('isXmlHttpRequest')->willReturn(true);
        $this->json->expects($this->atLeastOnce())->method('unserialize')->willReturn($requestData);
        $this->formKey->expects($this->atLeastOnce())->method('getFormKey')->willReturn($requestData['form_key']);
        $resultJson->expects($this->atLeastOnce())->method('setData')->willReturn($resultJson);
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($resultJson);
        $this->customerAccountManagement->expects($this->atLeastOnce())->method('authenticate')->willReturn($customer);
        $provider->expects($this->atLeastOnce())->method('getCode')->willReturn('test');
        $provider->expects($this->atLeastOnce())->method('getName')->willReturn('test');
        $provider->expects($this->atLeastOnce())->method('isConfigured')->willReturn(1);
        $providerEngine->expects($this->atLeastOnce())->method('getAdditionalConfig')->willReturn([]);
        $provider->expects($this->atLeastOnce())->method('getEngine')->willReturn($providerEngine);
        $this->customerProvidersManager
            ->expects($this->atLeastOnce())
            ->method('getCustomerProviders')
            ->willReturn([$provider]);

        $this->assertEquals($resultJson, $this->object->execute());
    }

    /**
     * @throws LocalizedException
     */
    public function testExecuteLocalizedException()
    {
        $requestData = [
            'form_key' => '123',
            'username' => '123',
            'password' => '123',
        ];
        $resultJson  = $this->getMockBuilder(ResultJson::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $this->request->expects($this->atLeastOnce())->method('getContent')->willReturn('{}');
        $this->request->expects($this->atLeastOnce())->method('getMethod')->willReturn('POST');
        $this->request->expects($this->atLeastOnce())->method('isXmlHttpRequest')->willReturn(true);
        $this->json->expects($this->atLeastOnce())->method('unserialize')->willReturn($requestData);
        $this->formKey->expects($this->atLeastOnce())->method('getFormKey')->willReturn($requestData['form_key']);
        $resultJson->expects($this->atLeastOnce())->method('setData')->willReturn($resultJson);
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($resultJson);
        $this->customerAccountManagement
            ->expects($this->atLeastOnce())
            ->method('authenticate')
            ->willThrowException(new LocalizedException(new Phrase('test')));
        $this->customerSession->expects($this->never())->method('setTwoFaCustomerId');

        $this->assertEquals($resultJson, $this->object->execute());
    }

    /**
     * @throws LocalizedException
     */
    public function testExecuteException()
    {
        $requestData = [
            'form_key' => '123',
            'username' => '123',
            'password' => '123',
        ];
        $resultJson  = $this->getMockBuilder(ResultJson::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $this->request->expects($this->atLeastOnce())->method('getContent')->willReturn('{}');
        $this->request->expects($this->atLeastOnce())->method('getMethod')->willReturn('POST');
        $this->request->expects($this->atLeastOnce())->method('isXmlHttpRequest')->willReturn(true);
        $this->json->expects($this->atLeastOnce())->method('unserialize')->willReturn($requestData);
        $this->formKey->expects($this->atLeastOnce())->method('getFormKey')->willReturn($requestData['form_key']);
        $resultJson->expects($this->atLeastOnce())->method('setData')->willReturn($resultJson);
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($resultJson);
        $this->customerAccountManagement->expects($this->atLeastOnce())->method('authenticate')
                                        ->willThrowException(new \Exception('test'));
        $this->customerSession->expects($this->never())->method('setTwoFaCustomerId');

        $this->assertEquals($resultJson, $this->object->execute());
    }

    /**
     * @throws LocalizedException
     */
    public function testExecuteInvalidJson()
    {
        $resultRaw  = $this->getMockBuilder(Raw::class)
                           ->disableOriginalConstructor()
                           ->getMock();
        $this->request->expects($this->atLeastOnce())->method('getContent')->willReturn('{}');
        $resultRaw->expects($this->atLeastOnce())->method('setHttpResponseCode')->willReturn($resultRaw);
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($resultRaw);
        $this->json->expects($this->atLeastOnce())->method('unserialize')
                   ->willThrowException(new \InvalidArgumentException('test'));
        $this->assertEquals($resultRaw, $this->object->execute());
    }

    /**
     * @throws LocalizedException
     */
    public function testExecuteInvalidValidation()
    {
        $resultRaw  = $this->getMockBuilder(Raw::class)
                           ->disableOriginalConstructor()
                           ->getMock();
        $this->request->expects($this->atLeastOnce())->method('getContent')->willReturn('{}');
        $resultRaw->expects($this->atLeastOnce())->method('setHttpResponseCode')->willReturn($resultRaw);
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($resultRaw);
        $this->json->expects($this->atLeastOnce())->method('unserialize')->willReturn([]);
        $this->assertEquals($resultRaw, $this->object->execute());
    }

    /**
     *
     */
    protected function setUp()
    {
        $this->context = $this->getMockBuilder(Context::class)
                              ->disableOriginalConstructor()
                              ->getMock();
        $this->resultFactory = $this->getMockBuilder(ResultFactory::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->customerAccountManagement = $this->getMockBuilder(AccountManagementInterface::class)
                                                ->disableOriginalConstructor()
                                                ->getMock();
        $this->customerSession = $this->getMockBuilder(Session::class)
                                      ->disableOriginalConstructor()
                                      ->setMethods(['setTwoFaCustomerId'])
                                      ->getMockForAbstractClass();
        $this->json = $this->getMockBuilder(Json::class)
                                                ->disableOriginalConstructor()
                                                ->getMock();
        $this->customerProvidersManager = $this->getMockBuilder(CustomerProvidersManagerInterface::class)
                                                ->disableOriginalConstructor()
                                                ->getMock();
        $this->formKey = $this->getMockBuilder(FormKey::class)
                                               ->disableOriginalConstructor()
                                               ->getMock();
        $this->request = $this->getMockBuilder(RequestInterface::class)
                              ->disableOriginalConstructor()
                              ->setMethods(['getContent', 'getMethod', 'isXmlHttpRequest'])
                              ->getMockForAbstractClass();
        $this->context->expects($this->any())->method('getRequest')->willReturn($this->request);
        $this->context->expects($this->any())->method('getResultFactory')
                      ->willReturn($this->resultFactory);
        $this->object = (new ObjectManager($this))->getObject(
            Providers::class,
            [
                'context' => $this->context,
                'customerAccountManagement' => $this->customerAccountManagement,
                'customerSession' => $this->customerSession,
                'json' => $this->json,
                'customerProvidersManager' => $this->customerProvidersManager,
                'formKey' => $this->formKey,
            ]
        );
    }
}
