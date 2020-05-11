<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Model\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class TwoFaEncodedConfig
 * Backend model for tfa configuration attribute
 */
class TwoFaEncodedConfig extends AbstractBackend
{
    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * TwoFaEncodedConfig constructor.
     *
     * @param EncryptorInterface $encryptor
     * @param SerializerInterface $serializer
     */
    public function __construct(
        EncryptorInterface $encryptor,
        SerializerInterface $serializer
    ) {
        $this->encryptor = $encryptor;
        $this->serializer = $serializer;
    }

    /**
     * Prepare data for save
     *
     * @param \Magento\Framework\DataObject $object
     * @return \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $data = $object->getData($attributeCode);

        if (is_array($data) && isset($data[0])) {
            $object->setData($attributeCode, $this->encrypt($data));
        }
        return parent::beforeSave($object);
    }

    /**
     * Prepare data after load
     * @param \Magento\Framework\DataObject $object
     *
     * @return TwoFaEncodedConfig
     */
    public function afterLoad($object)
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $data = $object->getData($attributeCode);
        if (is_string($data)) {
            $object->setData($attributeCode, $this->decrypt($data));
        } else {
            $object->setData($attributeCode, []);
        }
        return parent::afterLoad($object);
    }

    /**
     * @param array $data
     *
     * @return string
     */
    private function encrypt(array $data): string
    {
        return $this->encryptor->encrypt($this->serializer->serialize($data[0]));
    }

    /**
     * @param string $data
     *
     * @return array
     */
    private function decrypt(string $data): array
    {
        return $this->serializer->unserialize($this->encryptor->decrypt($data));
    }
}
