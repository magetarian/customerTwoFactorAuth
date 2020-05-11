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
use Magetarian\CustomerTwoFactorAuth\Setup\Patch\Data\CreateCustomerTFAAttributes;
use Magetarian\CustomerTwoFactorAuth\Model\Config\ConfigProvider;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderPoolInterface;

/**
 * Class CustomerProvidersManager
 * The class return list of specific providers for a csutomer
 */
class CustomerProvidersManager implements CustomerProvidersManagerInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ProviderPoolInterface
     */
    private $providerPool;

    /**
     * CustomerProvidersManager constructor.
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param ConfigProvider $configProvider
     * @param ProviderPoolInterface $providerPool
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        ConfigProvider $configProvider,
        ProviderPoolInterface $providerPool
    ) {
        $this->customerRepository = $customerRepository;
        $this->configProvider = $configProvider;
        $this->providerPool = $providerPool;
    }

    /**
     * @param int $customerId
     *
     * @return array
     */
    public function getCustomerProviders(int $customerId): array
    {
        $customer = $this->getCustomer($customerId);

        $customerProviders = [];
        $enabledProviders = $this->providerPool->getEnabledProviders();
        $customerProvidersAttribute = $customer->getCustomAttribute(CreateCustomerTFAAttributes::PROVIDERS);
        foreach ($enabledProviders as $provider) {
            if ($customer->getCustomAttribute(CreateCustomerTFAAttributes::PROVIDERS)) {
                $selectedProvidersArray = [];
                $selectedProviders = $customer->getCustomAttribute(CreateCustomerTFAAttributes::PROVIDERS)
                                              ->getValue();
                if ($selectedProviders) {
                    $selectedProvidersArray = explode(',', $selectedProviders);
                }

                $customerProviders[] = $provider;
            } elseif ($this->configProvider->getIsTfaForced()) {
                $customerProviders[] = $provider;
            }
        }
        return $customerProviders;
    }

    /**
     * @param $customerId
     *
     * @return CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCustomer($customerId): CustomerInterface
    {
        return $this->customerRepository->getById($customerId);
    }
}
