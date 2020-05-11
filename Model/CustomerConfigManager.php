<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magetarian\CustomerTwoFactorAuth\Api\CustomerConfigManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magetarian\CustomerTwoFactorAuth\Setup\Patch\Data\CreateCustomerTFAAttributes;
use Magetarian\CustomerTwoFactorAuth\Model\Config\ConfigProvider;

class CustomerConfigManager implements CustomerConfigManagerInterface
{
    private $customerRepository;

    private $customer = null;

    private $customerConfig = null;

    public function __construct(
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
    }

    public function getProviderConfig(int $customerId, string $providerCode): ?array
    {
        $providersConfig = $this->getCustomerProvidersConfiguration($customerId);

        if (!isset($providersConfig[$providerCode])) {
            return null;
        }

        return $providersConfig[$providerCode];
    }

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

    private function getCustomerProvidersConfiguration(int $customerId): array
    {
        if (!$this->getCustomer($customerId)->getCustomAttribute(CreateCustomerTFAAttributes::CONFIG))
            return [];

        return $this->getCustomer($customerId)
                    ->getCustomAttribute(CreateCustomerTFAAttributes::CONFIG)
                    ->getValue();
    }

    private function setCustomerProvidersConfiguration(int $customerId, array $config)
    {
        $this->getCustomer($customerId)->setCustomAttribute(CreateCustomerTFAAttributes::CONFIG, [$config]);
        $this->customer = $this->customerRepository->save($this->getCustomer($customerId));
        $this->customerConfig = $config;
    }

    private function getCustomer(int $customerId): CustomerInterface
    {
        if (is_null($this->customer)) {
            $this->customer = $this->customerRepository->getById($customerId);
        }
        return $this->customer;
    }
}
