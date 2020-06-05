<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magetarian\CustomerTwoFactorAuth\Api\CustomerConfigManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magetarian\CustomerTwoFactorAuth\Setup\Patch\Data\CreateCustomerTFAAttributes;
use Magetarian\CustomerTwoFactorAuth\Model\Config\ConfigProvider;

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
     * @var null
     */
    private $customer = null;

    /**
     * CustomerConfigManager constructor.
     *
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
    }

    /**
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
     * @param int $customerId
     * @param string $providerCode
     * @param array|null $config
     *
     * @return mixed|void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
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
    }

    /**
     * @param int $customerId
     *
     * @return array
     */
    private function getCustomerProvidersConfiguration(int $customerId): array
    {
        if (!$this->getCustomer($customerId)->getCustomAttribute(CreateCustomerTFAAttributes::CONFIG)) {
            return [];
        }

        return $this->getCustomer($customerId)
                    ->getCustomAttribute(CreateCustomerTFAAttributes::CONFIG)
                    ->getValue();
    }

    /**
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
        $this->getCustomer($customerId)->setCustomAttribute(CreateCustomerTFAAttributes::CONFIG, [$config]);
        $this->customer = $this->customerRepository->save($this->getCustomer($customerId));
        return $this;
    }

    /**
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
