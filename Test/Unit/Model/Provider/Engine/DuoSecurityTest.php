<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Test\Unit\Model\Provider\Engine;

use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DuoSecurityTest extends TestCase
{

    /** @var DuoSecurity object */
    private $object;

    private $scopeConfig;

    public function testGetAdditionalConfig()
    {

    }

    public function testGetCode()
    {

    }

    public function testGetRequestSignature()
    {

    }

    public function testIsEnabled()
    {

    }

    public function testGetApiHostname()
    {

    }

    public function testVerify()
    {

    }

    protected function setUp()
    {
        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
                                  ->disableOriginalConstructor()
                                  ->getMock();
        $this->object = (new ObjectManager($this))->getObject(
            DuoSecurity::class,
            [
                'scopeConfig' => $this->scopeConfig
            ]
        );
    }
}
