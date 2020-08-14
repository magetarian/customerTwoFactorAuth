<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Test\Unit\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\AttributeInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magetarian\CustomerTwoFactorAuth\Model\CustomerConfigManager;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class CustomerConfigManagerTest
 * Test for CustomerConfigManager class
 */
class CustomerConfigManagerTest extends TestCase
{

    /** @var CustomerConfigManager object */
    private $object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $customerRepository;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $encryptor;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $serializer;

    /**
     * @dataProvider dataProviderGetProviderConfig
     */
    public function testGetProviderConfig(bool $customAttributeExist, ?array $result)
    {
        $customer = $this->getMockBuilder(CustomerInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $customAttribute = $this->getMockBuilder(AttributeInterface::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        if ($customAttributeExist) {
            $customAttribute->expects($this->atLeastOnce())->method('getValue')->willReturn('test');
            $customer->expects($this->atLeastOnce())->method('getCustomAttribute')->willReturn($customAttribute);
        } else {
            $customAttribute->expects($this->never())->method('getValue');
        }
        $this->encryptor->expects($this->any())->method('decrypt')->willReturn('test');
        $this->serializer->expects($this->any())->method('unserialize')->willReturn(['test'=>['config']]);
        $this->customerRepository->expects($this->atLeastOnce())->method('getById')->willReturn($customer);
        $this->assertEquals($result, $this->object->getProviderConfig(1, 'test'));
    }

    /**
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function testSetProviderConfig()
    {
        $customer = $this->getMockBuilder(CustomerInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $customAttribute = $this->getMockBuilder(AttributeInterface::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->encryptor->expects($this->any())->method('decrypt')->willReturn('test');
        $this->serializer->expects($this->any())->method('unserialize')->willReturn(['test'=>['config']]);
        $this->encryptor->expects($this->any())->method('encrypt')->willReturn('test');
        $this->serializer->expects($this->any())->method('serialize')->willReturn('{}');

        $customAttribute->expects($this->atLeastOnce())->method('getValue')->willReturn('test');
        $customer->expects($this->atLeastOnce())->method('getCustomAttribute')->willReturn($customAttribute);
        $customer->expects($this->atLeastOnce())->method('setCustomAttribute');
        $this->customerRepository->expects($this->atLeastOnce())->method('getById')->willReturn($customer);
        $this->customerRepository->expects($this->atLeastOnce())->method('save');
        $this->assertEquals($this->object, $this->object->setProviderConfig(1, 'test', []));
        $this->assertEquals($this->object, $this->object->setProviderConfig(1, 'test', null));
    }

    /**
     * @return array|array[]
     */
    public function dataProviderGetProviderConfig(): array
    {
        return [
            [true , ['config']],
            [false, null],
        ];
    }

    /**
     *
     */
    protected function setUp(): void
    {
        $this->customerRepository = $this->getMockBuilder(CustomerRepositoryInterface::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->encryptor = $this->getMockBuilder(EncryptorInterface::class)
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $this->serializer = $this->getMockBuilder(SerializerInterface::class)
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $this->object = (new ObjectManager($this))->getObject(
            CustomerConfigManager::class,
            [
                'customerRepository' => $this->customerRepository,
                'encryptor' => $this->encryptor,
                'serializer' => $this->serializer,
            ]
        );
    }
}
