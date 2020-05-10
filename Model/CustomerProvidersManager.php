<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magetarian\CustomerTwoFactorAuth\Api\CustomerProvidersManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magetarian\CustomerTwoFactorAuth\Setup\Patch\Data\CreateCustomerTwoFactorAuthAttributes;
use Magetarian\CustomerTwoFactorAuth\Model\Config\ConfigProvider;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderPoolInterface;

class CustomerProvidersManager implements CustomerProvidersManagerInterface
{
    private $customerRepository;

    private $configProvider;

    private $providerPool;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        ConfigProvider $configProvider,
        ProviderPoolInterface $providerPool
    ) {
        $this->customerRepository = $customerRepository;
        $this->configProvider = $configProvider;
        $this->providerPool = $providerPool;
    }

    public function getCustomerProviders(int $customerId): array
    {
        $customer = $this->getCustomer($customerId);

        $customerProviders = [];
        $allProviders = $this->providerPool->getProviders();
        $customerProvidersAttribute = $customer->getCustomAttribute(CreateCustomerTwoFactorAuthAttributes::PROVIDERS);
        foreach ($allProviders as $provider) {
            if (!$provider->isEnabled())
                 continue;

            if ($customer->getCustomAttribute(CreateCustomerTwoFactorAuthAttributes::PROVIDERS)) {
                $selectedProvidersArray = [];
                $selectedProviders = $customer->getCustomAttribute(CreateCustomerTwoFactorAuthAttributes::PROVIDERS)
                                              ->getValue();
                if ($selectedProviders) {
                    $selectedProvidersArray = explode(',',$selectedProviders);
                }

                $customerProviders[] = $provider;
            } elseif ($this->configProvider->getIsTfaForced()) {
                $customerProviders[] = $provider;
            }
        }
        return $customerProviders;
    }

    private function getCustomer($customerId): CustomerInterface
    {
        return $this->customerRepository->getById($customerId);
    }
}
