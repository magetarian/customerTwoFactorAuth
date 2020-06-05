<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ProviderPoolTest extends TestCase
{

    /** @var ProviderPool object */
    private $object;

    private $providers;

    public function testGetEnabledProviders()
    {

    }

    public function testGetProviderByCode()
    {

    }

    public function testGetProviders()
    {

    }

    protected function setUp()
    {
        $this->object = (new ObjectManager($this))->getObject(
            ProviderPool::class,
            [
                'providers' => $this->providers
            ]
        );
    }
}
