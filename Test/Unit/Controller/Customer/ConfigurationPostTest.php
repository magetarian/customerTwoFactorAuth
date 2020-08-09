<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Test\Unit\Controller\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magetarian\CustomerTwoFactorAuth\Controller\Customer\ConfigurationPost;

/**
 * Class ConfigurationPostTest
 * Test for ConfigurationPostTest class
 */
class ConfigurationPostTest extends TestCase
{

    /** @var ConfigurationPost object */
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
    private $messageManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $request;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $customerSession;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $formKeyValidator;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $customerRepository;

    /**
     *
     */
    public function testExecute()
    {
        $resultRedirect = $this->getMockBuilder(Redirect::class)
                               ->disableOriginalConstructor()
                               ->getMock();
        $customer = $this->getMockBuilder(Customer::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $customerDataModel = $this->getMockBuilder(CustomerInterface::class)
                                  ->disableOriginalConstructor()
                                  ->getMock();
        $this->formKeyValidator->expects($this->atLeastOnce())->method('validate')->willReturn(true);
        $this->request->expects($this->atLeastOnce())->method('getParam')->willReturn('test');
        $customerDataModel->expects($this->atLeastOnce())->method('setCustomAttribute')->willReturn($customerDataModel);
        $customer->expects($this->atLeastOnce())->method('getDataModel')->willReturn($customerDataModel);
        $this->customerSession->expects($this->atLeastOnce())->method('getCustomer')->willReturn($customer);
        $this->customerRepository->expects($this->atLeastOnce())->method('save');
        $resultRedirect->expects($this->atLeastOnce())->method('setPath')->willReturn($resultRedirect);
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($resultRedirect);
        $this->messageManager->expects($this->never())->method('addExceptionMessage');
        $this->messageManager->expects($this->atLeastOnce())->method('addSuccessMessage');
        $this->messageManager->expects($this->never())->method('addErrorMessage');
        $this->assertEquals($resultRedirect, $this->object->execute());
    }

    /**
     *
     */
    public function testExecuteException()
    {
        $resultRedirect = $this->getMockBuilder(Redirect::class)
                               ->disableOriginalConstructor()
                               ->getMock();
        $this->request->expects($this->atLeastOnce())->method('getParam')->willThrowException(new \Exception('test'));
        $this->formKeyValidator->expects($this->atLeastOnce())->method('validate')->willReturn(true);
        $this->messageManager->expects($this->atLeastOnce())->method('addExceptionMessage');
        $this->messageManager->expects($this->never())->method('addSuccessMessage');
        $this->messageManager->expects($this->never())->method('addErrorMessage');
        $resultRedirect->expects($this->atLeastOnce())->method('setPath')->willReturn($resultRedirect);
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->object->execute());
    }

    /**
     *
     */
    public function testExecuteFormKeyInvalid()
    {
        $resultRedirect = $this->getMockBuilder(Redirect::class)
                               ->disableOriginalConstructor()
                               ->getMock();
        $this->formKeyValidator->expects($this->atLeastOnce())->method('validate')->willReturn(false);
        $this->messageManager->expects($this->atLeastOnce())->method('addErrorMessage');
        $this->messageManager->expects($this->never())->method('addSuccessMessage');
        $this->messageManager->expects($this->never())->method('addExceptionMessage');
        $resultRedirect->expects($this->atLeastOnce())->method('setPath')->willReturn($resultRedirect);
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->object->execute());
    }

    /**
     *
     */
    protected function setUp(): void
    {
        $this->context = $this->getMockBuilder(Context::class)
                              ->disableOriginalConstructor()
                              ->getMock();
        $this->resultFactory = $this->getMockBuilder(ResultFactory::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)
                                     ->disableOriginalConstructor()
                                     ->getMock();
        $this->request = $this->getMockBuilder(RequestInterface::class)
                              ->disableOriginalConstructor()
                              ->getMock();
        $this->customerSession = $this->getMockBuilder(Session::class)
                                      ->disableOriginalConstructor()
                                      ->getMock();
        $this->formKeyValidator = $this->getMockBuilder(Validator::class)
                                      ->disableOriginalConstructor()
                                      ->getMock();
        $this->customerRepository = $this->getMockBuilder(CustomerRepositoryInterface::class)
                                      ->disableOriginalConstructor()
                                      ->getMock();
        $this->context->expects($this->any())->method('getMessageManager')->willReturn($this->messageManager);
        $this->context->expects($this->any())->method('getResultFactory')
                      ->willReturn($this->resultFactory);
        $this->context->expects($this->any())->method('getRequest')->willReturn($this->request);
        $this->object = (new ObjectManager($this))->getObject(
            ConfigurationPost::class,
            [
                'context' => $this->context,
                'customerSession' => $this->customerSession,
                'formKeyValidator' => $this->formKeyValidator,
                'customerRepository' => $this->customerRepository,
            ]
        );
    }
}
