<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Test\Unit\Block\Adminhtml\Customer\Edit;

use Magetarian\CustomerTwoFactorAuth\Api\ProviderPoolInterface;
use Magetarian\CustomerTwoFactorAuth\Block\Adminhtml\Customer\Edit\ResetTFAButton;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Phrase;

/**
 * Class ResetTFAButtonTest
 * Test for ResetTFAButtonTest class
 */
class ResetTFAButtonTest extends TestCase
{

    /** @var ResetTFAButton object */
    private $object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $urlBuilder;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $registry;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $context;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $providerPool;

    /**
     *
     */
    public function testGetResetUrl()
    {
        $result = 'customer_tfa/customer/reset/customer_id/1';

        $this->registry->expects($this->atLeastOnce())->method('registry')->willReturn(1);
        $this->urlBuilder->expects($this->atLeastOnce())->method('getUrl')->willReturn($result);

        $this->assertEquals($result, $this->object->getResetUrl());
    }

    /**
     *
     */
    public function testGetButtonData()
    {
        $result = [
            'label' => new Phrase('Reset TFA'),
            'id' => 'customer-reset-tfa-button',
            'on_click' => "location.href = 'test';",
            'class' => 'add',
            'aclResource' => 'Magetarian_CustomerTwoFactorAuth::reset_tfa',
            'sort_order' => 100
        ];

        $this->registry->expects($this->atLeastOnce())->method('registry')->willReturn(1);
        $this->urlBuilder->expects($this->atLeastOnce())->method('getUrl')->willReturn('test');
        $this->providerPool->expects($this->atLeastOnce())->method('getEnabledProviders')->willReturn(['test']);

        $this->assertEquals($result, $this->object->getButtonData());
    }

    /**
     *
     */
    protected function setUp(): void
    {
        $this->registry = $this->getMockBuilder(Registry::class)
                                 ->disableOriginalConstructor()
                                 ->getMock();
        $this->context = $this->getMockBuilder(Context::class)
                               ->disableOriginalConstructor()
                               ->getMock();
        $this->providerPool = $this->getMockBuilder(ProviderPoolInterface::class)
                              ->disableOriginalConstructor()
                              ->getMock();
        $this->urlBuilder = $this->getMockBuilder(UrlInterface::class)
                           ->disableOriginalConstructor()
                           ->getMock();
        $this->context->expects($this->atLeastOnce())->method('getUrlBuilder')->willReturn($this->urlBuilder);

        $this->object = (new ObjectManager($this))->getObject(
            ResetTFAButton::class,
            [
                'context' => $this->context,
                'registry' => $this->registry,
                'providerPool' => $this->providerPool
            ]
        );
    }
}
