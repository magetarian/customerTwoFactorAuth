<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Test\Unit\Model\Attribute\Backend;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class TwoFaEncodedConfigTest extends TestCase
{

    /** @var TwoFaEncodedConfig object */
    private $object;

    private $encryptor;

    private $serializer;

    public function testAfterLoad()
    {

    }

    public function testBeforeSave()
    {

    }

    protected function setUp()
    {
        $this->encryptor = $this->getMockBuilder(EncryptorInterface::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();
        $this->serializer = $this->getMockBuilder(SerializerInterface::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();
        $this->object = (new ObjectManager($this))->getObject(
            TwoFaEncodedConfig::class,
            [
                'encryptor' => $this->encryptor,
                'serializer' => $this->serializer,
            ]
        );
    }
}
