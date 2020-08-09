<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Test\Unit\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action\Context;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderPoolInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface;
use Magetarian\CustomerTwoFactorAuth\Controller\Adminhtml\Customer\Reset;

/**
 * Class ResetTest
 * Test for ResetTest class
 */
class ResetTest extends TestCase
{

    /** @var Reset object */
    private $object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $providerPool;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $context;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $request;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $messageManager;

    /**
     * @dataProvider dataProviderExecute
     */
    public function testExecute($customerId)
    {
        $resultRedirect = $this->getMockBuilder(Redirect::class)
                               ->disableOriginalConstructor()
                               ->getMock();
        $provider = $this->getMockBuilder(ProviderInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $resultRedirect->expects($this->atLeastOnce())->method('setPath')->willReturn($resultRedirect);
        $this->resultRedirectFactory->expects($this->atLeastOnce())->method('create')->willReturn($resultRedirect);
        $this->request->expects($this->atLeastOnce())->method('getParam')->willReturn($customerId);
        if ($customerId) {
            $provider->expects($this->atLeastOnce())->method('resetConfiguration');
            $this->providerPool->expects($this->atLeastOnce())->method('getProviders')->willReturn([$provider]);
            $this->messageManager->expects($this->atLeastOnce())->method('addSuccessMessage');
        } else {
            $this->providerPool->expects($this->never())->method('getProviders');
            $this->messageManager->expects($this->never())->method('addSuccessMessage');
        }

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
        $resultRedirect->expects($this->atLeastOnce())->method('setPath')->willReturn($resultRedirect);
        $this->resultRedirectFactory->expects($this->atLeastOnce())->method('create')->willReturn($resultRedirect);

        $this->request->expects($this->atLeastOnce())->method('getParam')->willReturn(1);
        $this->providerPool->expects($this->atLeastOnce())->method('getProviders')
                                                          ->willThrowException(new \Exception('test'));
        $this->messageManager->expects($this->atLeastOnce())->method('addExceptionMessage');
        $this->messageManager->expects($this->never())->method('addSuccessMessage');

        $this->assertEquals($resultRedirect, $this->object->execute());
    }

    /**
     * @return array
     */
    public function dataProviderExecute(): array
    {
        return [
            [1],
            [false],
        ];
    }

    /**
     *
     */
    protected function setUp(): void
    {
        $this->providerPool = $this->getMockBuilder(ProviderPoolInterface::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();
        $this->context = $this->getMockBuilder(Context::class)
                              ->disableOriginalConstructor()
                              ->getMock();
        $this->resultRedirectFactory = $this->getMockBuilder(RedirectFactory::class)
                                      ->disableOriginalConstructor()
                                      ->getMock();
        $this->request = $this->getMockBuilder(RequestInterface::class)
                        ->disableOriginalConstructor()
                        ->getMock();
        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)
                               ->disableOriginalConstructor()
                               ->getMock();
        $this->context->expects($this->any())->method('getResultRedirectFactory')
                      ->willReturn($this->resultRedirectFactory);
        $this->context->expects($this->any())->method('getRequest')->willReturn($this->request);
        $this->context->expects($this->any())->method('getMessageManager')->willReturn($this->messageManager);
        $this->object = (new ObjectManager($this))->getObject(
            Reset::class,
            [
                'context' => $this->context,
                'providerPool' => $this->providerPool,
            ]
        );
    }
}
