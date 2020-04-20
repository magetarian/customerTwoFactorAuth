<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\ViewModel\Customer;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magetarian\CustomerTwoFactorAuth\Setup\Patch\Data\CreateCustomerTwoFactorAuthAttributes;
use MSP\TwoFactorAuth\Model\ProviderPool;
use Magetarian\CustomerTwoFactorAuth\Model\Config\ConfigProvider;

/**
 * Class Configuration
 * ViewModel for customer account
 */
class Configuration implements ArgumentInterface
{
    /**
     * @var CustomerMetadataInterface
     */
    protected $customerMetadata;

    /**
     * @var ProviderPool
     */
    private $providerPool;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var array
     */
    private $enabledProviders = [];

    /**
     * Configuration constructor.
     *
     * @param CustomerMetadataInterface $customerMetadata
     * @param ProviderPool $providerPool
     * @param ConfigProvider $configProvider
     * @param Session $customerSession
     */
    public function __construct(
        CustomerMetadataInterface $customerMetadata,
        ProviderPool $providerPool,
        ConfigProvider $configProvider,
        Session $customerSession
    ) {
        $this->providerPool     = $providerPool;
        $this->configProvider   = $configProvider;
        $this->customerSession  = $customerSession;
        $this->customerMetadata = $customerMetadata;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool) (
            $this->configProvider->isEnabled()
        ) && (
            count($this->getAvailableProviders())
        );
    }

    /**
     * @return array
     */
    private function getEnabledProviders(): array
    {
        if (!count($this->enabledProviders)) {
            foreach ($this->providerPool->getProviders() as $provider) {
                if ($provider->isEnabled()) {
                    $this->enabledProviders[$provider->getCode()] = $provider->getName();
                }
            }
        }
        return $this->enabledProviders;
    }

    /**
     * @return array
     */
    public function getAvailableProviders(): array
    {
        $forcedProviders = $this->configProvider->getForcedProviders();
        $providers = $this->getEnabledProviders();
        if (!count($forcedProviders)) {
            return $providers;
        }
        return array_intersect_key($providers, array_flip($forcedProviders));
    }

    /**
     * @return array
     */
    public function getSelectedProviders(): array
    {
        $selectedProviders = $this->customerSession
            ->getCustomer()
            ->getDataModel()
            ->getCustomAttribute(CreateCustomerTwoFactorAuthAttributes::PROVIDERS);
        if (!$selectedProviders) {
            return [];
        }
        $selectedProvidersValue = $selectedProviders->getValue();
        return explode(',', $selectedProvidersValue);
    }


    /**
     * @todo  should replace getAvailableProviders or removed
     * Get 2FA Provider Attribute
     *
     * @return \Magento\Customer\Model\Data\AttributeMetadata
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProviderConfigAttribute()
    {
        /**
         * @var $attribute \Magento\Customer\Model\Data\AttributeMetadata
         */
        $attribute = $this->customerMetadata->getAttributeMetadata(
            CreateCustomerTwoFactorAuthAttributes::PROVIDERS
        );

        return $attribute;
    }
}
