<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Test\Unit\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magetarian\CustomerTwoFactorAuth\Controller\Customer\Configuration;

/**
 * Class ConfigurationTest
 * Test for ConfigurationTest class
 */
class ConfigurationTest extends TestCase
{

    /** @var Configuration object */
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
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function testExecute()
    {
        $resultPage = $this->getMockBuilder(Page::class)
                           ->disableOriginalConstructor()
                           ->getMock();
        $pageConfig = $this->getMockBuilder(Config::class)
                           ->disableOriginalConstructor()
                           ->getMock();
        $pageTitle = $this->getMockBuilder(Title::class)
                           ->disableOriginalConstructor()
                           ->getMock();
        $pageTitle->expects($this->atLeastOnce())->method('set');
        $pageConfig->expects($this->atLeastOnce())->method('getTitle')->willReturn($pageTitle);
        $resultPage->expects($this->atLeastOnce())->method('getConfig')->willReturn($pageConfig);
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($resultPage);
        $this->assertEquals($resultPage, $this->object->execute());
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
        $this->context->expects($this->any())->method('getResultFactory')
                      ->willReturn($this->resultFactory);
        $this->object = (new ObjectManager($this))->getObject(
            Configuration::class,
            [
                'context' => $this->context,
            ]
        );
    }
}
