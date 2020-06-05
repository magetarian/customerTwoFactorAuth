<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Test\Unit\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CustomerConfigManagerTest extends TestCase
{

    /** @var CustomerConfigManager object */
    private $object;

    private $customerRepository;

    public function testGetProviderConfig()
    {

    }

    public function testSetProviderConfig()
    {

    }

    protected function setUp()
    {
        $this->customerRepository = $this->getMockBuilder(CustomerRepositoryInterface::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->object = (new ObjectManager($this))->getObject(
            CustomerConfigManager::class,
            [
                'customerRepository' => $this->customerRepository
            ]
        );
    }
}
