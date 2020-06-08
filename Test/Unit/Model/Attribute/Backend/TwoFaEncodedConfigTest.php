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
use Magetarian\CustomerTwoFactorAuth\Model\Attribute\Backend\TwoFaEncodedConfig;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\DataObject;

/**
 * Class TwoFaEncodedConfigTest
 * Test for TwoFaEncodedConfig class
 */
class TwoFaEncodedConfigTest extends TestCase
{

    /** @var TwoFaEncodedConfig object */
    private $object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $encryptor;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $serializer;

    /**
     * @dataProvider dataProviderAfterLoad
     */
    public function testAfterLoad(?string $data)
    {
        $attribute = $this->getMockBuilder(AbstractAttribute::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $object = $this->getMockBuilder(DataObject::class)
                          ->disableOriginalConstructor()
                          ->getMock();
        if ($data) {
            $this->encryptor->expects($this->atLeastOnce())->method('decrypt')->willReturn('test');
            $this->serializer->expects($this->atLeastOnce())->method('unserialize')->willReturn(['test']);
        } else {
            $this->encryptor->expects($this->never())->method('decrypt');
            $this->serializer->expects($this->never())->method('unserialize');
        }
        $attribute->expects($this->atLeastOnce())->method('getAttributeCode')->willReturn('test');
        $object->expects($this->atLeastOnce())->method('getData')->willReturn($data);
        $object->expects($this->atLeastOnce())->method('setData');
        $this->object->setAttribute($attribute);
        $this->assertEquals($this->object, $this->object->afterLoad($object));
    }

    /**
     * @return array
     */
    public function dataProviderAfterLoad(): array
    {
        return [
            [null],
            ['test'],
        ];
    }

    /**
     *
     */
    public function testBeforeSave()
    {
        $attribute = $this->getMockBuilder(AbstractAttribute::class)
                          ->disableOriginalConstructor()
                          ->getMock();
        $object = $this->getMockBuilder(DataObject::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $this->encryptor->expects($this->atLeastOnce())->method('encrypt')->willReturn('test');
        $this->serializer->expects($this->atLeastOnce())->method('serialize')->willReturn('test');
        $attribute->expects($this->atLeastOnce())->method('getAttributeCode')->willReturn('test');
        $object->expects($this->atLeastOnce())->method('getData')->willReturn(['test']);
        $this->object->setAttribute($attribute);
        $this->assertEquals($this->object, $this->object->beforeSave($object));
    }

    /**
     *
     */
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
