<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magetarian\CustomerTwoFactorAuth\Api\CustomerConfigManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magetarian\CustomerTwoFactorAuth\Setup\Patch\Data\CreateCustomerTFAAttributes;

/**
 * Class CustomerConfigManager
 * The model sets/updates/reads customer tfa configuration
 */
class CustomerConfigManager implements CustomerConfigManagerInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var null
     */
    private $customer = null;

    /**
     * CustomerConfigManager constructor.
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param EncryptorInterface $encryptor
     * @param SerializerInterface $serializer
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        EncryptorInterface $encryptor,
        SerializerInterface $serializer
    ) {
        $this->customerRepository = $customerRepository;
        $this->encryptor = $encryptor;
        $this->serializer = $serializer;
    }

    /**
     * Get provider config
     *
     * @param int $customerId
     * @param string $providerCode
     *
     * @return array|null
     */
    public function getProviderConfig(int $customerId, string $providerCode): ?array
    {
        $providersConfig = $this->getCustomerProvidersConfiguration($customerId);

        if (!isset($providersConfig[$providerCode])) {
            return null;
        }

        return $providersConfig[$providerCode];
    }

    /**
     * Set provider config
     *
     * @param int $customerId
     * @param string $providerCode
     * @param array|null $config
     *
     * @return $this|mixed
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function setProviderConfig(int $customerId, string $providerCode, ?array $config)
    {
        $providersConfig = $this->getCustomerProvidersConfiguration($customerId);
        if ($config === null) {
            if (isset($providersConfig[$providerCode])) {
                unset($providersConfig[$providerCode]);
            }
        } else {
            $providersConfig[$providerCode] = $config;
        }

        $this->setCustomerProvidersConfiguration($customerId, $providersConfig);
        return $this;
    }

    /**
     * Get providers configuration
     *
     * @param int $customerId
     *
     * @return array
     */
    private function getCustomerProvidersConfiguration(int $customerId): array
    {
        if (!$this->getCustomer($customerId)->getCustomAttribute(CreateCustomerTFAAttributes::CONFIG)) {
            return [];
        }
        $config = $this->getCustomer($customerId)
                       ->getCustomAttribute(CreateCustomerTFAAttributes::CONFIG)
                       ->getValue();
        return $this->serializer->unserialize($this->encryptor->decrypt($config));
    }

    /**
     * Set providers configuration
     *
     * @param int $customerId
     * @param array $config
     *
     * @return $this
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    private function setCustomerProvidersConfiguration(int $customerId, array $config)
    {
        $encodedConfig = $this->encryptor->encrypt($this->serializer->serialize($config));
        $this->getCustomer($customerId)->setCustomAttribute(CreateCustomerTFAAttributes::CONFIG, $encodedConfig);
        $this->customer = $this->customerRepository->save($this->getCustomer($customerId));
        return $this;
    }

    /**
     * Get customer
     *
     * @param int $customerId
     *
     * @return CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCustomer(int $customerId): CustomerInterface
    {
        if (!$this->customer) {
            $this->customer = $this->customerRepository->getById($customerId);
        }
        return $this->customer;
    }
}
